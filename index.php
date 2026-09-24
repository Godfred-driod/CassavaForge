<?php
declare(strict_types=1);

$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
if (
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
) {
    $_SERVER['HTTPS'] = 'on';
}
if ($host !== '' && !preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/i', $host)) {
    ini_set('display_errors', '0');
}

// Let PHP's built-in server deliver CSS, images, and other public files directly.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$staticFile = __DIR__ . '/' . ltrim($requestPath, '/');
if ($requestPath !== '/' && is_file($staticFile)) {
    return false;
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/orders.php';
require __DIR__ . '/includes/site-sections.php';
require __DIR__ . '/includes/analytics.php';
require __DIR__ . '/includes/admin-landers.php';
require __DIR__ . '/includes/admin-orders.php';
require __DIR__ . '/includes/admin-people.php';
require __DIR__ . '/includes/portal.php';
ensure_owner_user();

$path = trim($requestPath, '/');
$path = preg_replace('#^(php-app|cassavaforge)/?#', '', $path);
$path = trim($path, '/');
$GLOBALS['currentPath'] = $path;

function page_layout(): string
{
    $path = (string) ($GLOBALS['currentPath'] ?? '');
    if (in_array($path, ['signin', 'admin/login', 'portal/login', 'portal/register'], true)) {
        return 'login';
    }
    if (str_starts_with($path, 'admin')) {
        return 'admin';
    }
    if (str_starts_with($path, 'portal')) {
        return 'portal';
    }
    return 'public';
}

function render_start(string $title): void
{
    $pageTitle = $title;
    $layout = page_layout();
    if ($layout === 'login') {
        require __DIR__ . '/includes/admin-login-header.php';
        return;
    }
    if ($layout === 'admin') {
        require __DIR__ . '/includes/admin-header.php';
        return;
    }
    if ($layout === 'portal') {
        require __DIR__ . '/includes/portal-header.php';
        return;
    }
    require __DIR__ . '/includes/header.php';
}

function render_end(): void
{
    $layout = page_layout();
    if ($layout === 'login') {
        require __DIR__ . '/includes/admin-login-footer.php';
        return;
    }
    if ($layout === 'admin' || $layout === 'portal') {
        require __DIR__ . '/includes/admin-footer.php';
        return;
    }
    require __DIR__ . '/includes/footer.php';
}

function safe_query(callable $query, mixed $fallback = []): mixed
{
    try {
        return $query();
    } catch (Throwable $error) {
        error_log($error->getMessage());
        return $fallback;
    }
}

function current_visitor_id(): ?string
{
    if (is_admin()) {
        return null;
    }
    $existing = trim((string) ($_COOKIE['cf_visitor'] ?? ''));
    if ($existing !== '') {
        return $existing;
    }
    $visitor = uuid();
    $_COOKIE['cf_visitor'] = $visitor;
    setcookie('cf_visitor', $visitor, ['expires' => time() + 31536000, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
    safe_query(function () use ($visitor): void {
        $stmt = db()->prepare('INSERT IGNORE INTO site_visitors (visitor_id) VALUES (?)');
        $stmt->execute([$visitor]);
    });
    return $visitor;
}

track_current_request($path);

if ($path === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    echo sitemap_xml();
    exit;
}

if ($path === 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo robots_txt();
    exit;
}

if (in_array($path, ['logout', 'admin/logout', 'portal/logout'], true)) {
    logout_user();
    redirect('signin');
}

if (in_array($path, ['admin/login', 'portal/login'], true)) {
    redirect('signin');
}

if ($path === 'signin') {
    handle_signin();
}

if ($path === 'portal/register') {
    handle_portal_register();
}

if ($path === 'portal' || $path === 'portal/new' || $path === 'portal/account' || $path === 'portal/orders' || str_starts_with($path, 'portal/orders/')) {
    handle_portal_pages($path);
}

if ($path === 'admin') {
    require_admin();
    $products = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(), 0);
    $posts = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn(), 0);
    $messages = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn(), 0);
    $visitors = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM site_visitors')->fetchColumn(), 0);
    $pageViews = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM page_views')->fetchColumn(), 0);
    $openOrders = safe_query(fn() => (int) db()->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('closed','declined')")->fetchColumn(), 0);
    $recentOrders = safe_query(fn() => db()->query('SELECT o.reference, o.type, o.status, o.created_at, u.full_name AS partner_name, u.company_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 8')->fetchAll(), []);
    $staff = current_user();
    $stats = [
        ['Open orders', $openOrders, 'admin/orders', 'receipt_long', 'Awaiting action'],
        ['Products', $products, 'admin/products', 'inventory_2', 'In the catalog'],
        ['Journal posts', $posts, 'admin/blog', 'article', 'Drafts and live notes'],
        ['Unread messages', $messages, 'admin/messages', 'mail', 'Contact inbox'],
        ['Landing views', $pageViews, 'admin/landers', 'monitoring', 'Tracked page views'],
        ['Unique visits', $visitors, 'admin', 'group', 'First-party visitors'],
    ];
    render_start('Dashboard'); ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Overview</p>
          <h1>Hello, <?= e((string) ($staff['full_name'] ?? 'team')) ?></h1>
          <p class="dash-lead">A clear view of orders, content, and traffic. Open a card to go straight to the work.</p>
        </div>
        <a class="btn btn-primary sans" href="<?= e(url('admin/orders')) ?>">Review orders</a>
      </div>
      <div class="dash-grid">
        <?php foreach ($stats as $stat): ?>
          <a class="dash-card" href="<?= e(url($stat[2])) ?>">
            <span class="dash-card-icon"><span class="material-symbols-outlined"><?= e($stat[3]) ?></span></span>
            <p class="dash-card-label"><?= e($stat[0]) ?></p>
            <strong class="dash-card-value"><?= e((string) $stat[1]) ?></strong>
            <span class="dash-card-hint"><?= e($stat[4]) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="dash-panel">
        <div class="dash-panel-head">
          <h2>Recent orders</h2>
          <a href="<?= e(url('admin/orders')) ?>">View all</a>
        </div>
        <?php if (!$recentOrders): ?>
          <p class="dash-empty sans mb-0">No orders yet.</p>
        <?php else: ?>
          <div class="table-responsive mb-0">
            <table class="table align-middle sans mb-0">
              <thead><tr><th>Reference</th><th>Partner</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach ($recentOrders as $row): ?>
                <tr>
                  <td><a href="<?= e(url('admin/orders/' . $row['reference'])) ?>"><?= e($row['reference']) ?></a></td>
                  <td><?= e($row['partner_name']) ?><?= !empty($row['company_name']) ? ' · ' . e($row['company_name']) : '' ?></td>
                  <td><?= e(order_types()[$row['type']] ?? $row['type']) ?></td>
                  <td><span class="portal-status"><?= e(order_statuses()[$row['status']] ?? $row['status']) ?></span></td>
                  <td><?= e(date('j M Y', strtotime((string) $row['created_at']))) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php render_end(); exit;
}

if ($path === 'admin/people') {
    handle_admin_people();
}

if ($path === 'admin/orders' || str_starts_with($path, 'admin/orders/')) {
    handle_admin_orders($path);
}

if ($path === 'admin/account') {
    require_admin();
    $account = current_user();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $current = (string) ($_POST['current_password'] ?? '');
        $next = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $hash = (string) ($account['password_hash'] ?? '');
        if ($hash === '' || !password_verify($current, $hash)) {
            flash('The current password was not recognized.', 'error');
        } elseif (strlen($next) < 8) {
            flash('Use a new password of at least 8 characters.', 'error');
        } elseif ($next !== $confirm) {
            flash('The new password and confirmation did not match.', 'error');
        } else {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($next, PASSWORD_DEFAULT), $account['id']]);
            session_regenerate_id(true);
            $_SESSION['user_seen'] = time();
            flash('Password updated.');
        }
        redirect('admin/account');
    }
    render_start('Account'); ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Workspace</p>
          <h1>Account</h1>
          <p class="dash-lead">Signed in as <?= e((string) ($account['email'] ?? '')) ?>. Sessions expire after 8 hours idle.</p>
        </div>
      </div>
      <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
      <div class="work-split">
        <div class="work-form">
          <h2>Profile</h2>
          <p class="work-form-lead">This is the account used for the staff workspace.</p>
          <div class="work-fields">
            <p class="work-field">Name<strong><?= e((string) ($account['full_name'] ?? 'Owner')) ?></strong></p>
            <p class="work-field">Email<strong><?= e((string) ($account['email'] ?? '')) ?></strong></p>
            <p class="work-field">Role<strong><?= e(ucfirst((string) ($account['role'] ?? 'staff'))) ?></strong></p>
            <p class="work-field">Session<strong>Ends after 8 hours idle</strong></p>
          </div>
        </div>
        <form method="post" class="work-form">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <h2>Change password</h2>
          <p class="work-form-lead">Use at least 8 characters.</p>
          <div class="work-fields">
            <label class="work-field is-wide" for="current_password">Current password
              <input class="form-control" id="current_password" type="password" name="current_password" autocomplete="current-password" required>
            </label>
            <label class="work-field" for="new_password">New password
              <input class="form-control" id="new_password" type="password" name="new_password" autocomplete="new-password" minlength="8" required>
            </label>
            <label class="work-field" for="confirm_password">Confirm new password
              <input class="form-control" id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
            </label>
          </div>
          <div class="work-actions">
            <button class="btn btn-primary sans" type="submit">Update password</button>
          </div>
        </form>
      </div>
    </section>
    <?php render_end(); exit;
}

if ($path === 'admin/landers') {
    require_admin();
    render_start('Landing pages');
    render_landers_report();
    render_end();
    exit;
}

if ($path === 'contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email_address'] ?? ''));
    $message = trim((string) ($_POST['message_body'] ?? ''));
    $topics = inquiry_topics();
    $topicKey = (string) ($_POST['inquiry_type'] ?? 'general');
    $topicKey = isset($topics[$topicKey]) ? $topicKey : 'general';
    $topicLabel = $topics[$topicKey];
    if ($name === '' || strlen($name) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || $message === '' || strlen($message) > 5000) {
        flash('Please complete all fields with a valid email address.', 'error');
        redirect('contact');
    }
    $partner = current_user();
    if ($partner && ($partner['role'] ?? '') === 'partner') {
        try {
            $reference = create_work_order((string) $partner['id'], inquiry_to_order_type($topicKey), $message, null, '', '', (string) $partner['id']);
            flash('Request ' . $reference . ' is in your portal.');
            redirect('portal/orders/' . $reference);
        } catch (Throwable $error) {
            error_log($error->getMessage());
            flash('We could not save your request. Please try again.', 'error');
            redirect('contact');
        }
    }
    $saved = false;
    try {
        $stored = '[' . $topicLabel . "]\n\n" . $message;
        $stmt = db()->prepare('INSERT INTO contact_messages (id, full_name, email_address, message_body) VALUES (?, ?, ?, ?)');
        $stmt->execute([uuid(), $name, $email, $stored]);
        $saved = true;
        flash('Thanks. Your message is with our team.');
    } catch (Throwable $error) {
        error_log($error->getMessage());
        flash('We could not save your message. Please try again.', 'error');
    }
    if ($saved) {
        notify_new_inquiry($name, $email, $topicLabel, $message);
    }
    redirect('contact');
}

if (in_array($path, ['admin/products', 'admin/blog', 'admin/messages'], true)) {
    require_admin();
    if ($path === 'admin/messages') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $id = (string) ($_POST['id'] ?? '');
            $action = (string) ($_POST['action'] ?? 'read');
            $stmt = db()->prepare($action === 'delete' ? 'DELETE FROM contact_messages WHERE id = ?' : 'UPDATE contact_messages SET is_read = 1 WHERE id = ?');
            $stmt->execute([$id]);
            redirect('admin/messages');
        }
        $messages = safe_query(fn() => db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll(), []);
        render_start('Messages'); ?>
        <section class="admin-panel">
          <div class="dash-head">
            <div>
              <p class="eyebrow">Inbox</p>
              <h1>Messages</h1>
              <p class="dash-lead">Notes sent from the public contact form.</p>
            </div>
          </div>
          <div class="dash-panel">
            <div class="table-responsive mb-0">
              <table class="table align-middle sans mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($messages as $message): ?>
                  <tr class="<?= empty($message['is_read']) ? 'fw-bold' : '' ?>">
                    <td><?= e($message['full_name']) ?></td>
                    <td><?= e($message['email_address']) ?></td>
                    <td><?= e($message['message_body']) ?></td>
                    <td><?= e(date('j M Y H:i', strtotime((string) $message['created_at']))) ?></td>
                    <td>
                      <form method="post" class="d-flex gap-1">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e($message['id']) ?>">
                        <button class="btn btn-sm btn-outline-success" name="action" value="read">Read</button>
                        <button class="btn btn-sm btn-outline-danger" name="action" value="delete">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$messages): ?><tr><td colspan="5"><p class="dash-empty mb-0">No messages yet.</p></td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
        <?php render_end(); exit;
    }

    $table = $path === 'admin/products' ? 'products' : 'blog_posts';
    $isProducts = $table === 'products';
    $editing = null;
    if (isset($_GET['edit'])) {
        $stmt = db()->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([(string) $_GET['edit']]);
        $editing = $stmt->fetch() ?: null;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        if (($_POST['action'] ?? '') === 'delete') {
            $id = trim((string) ($_POST['id'] ?? ''));
            if ($id !== '') {
                db()->prepare("DELETE FROM {$table} WHERE id = ?")->execute([$id]);
                flash('Item deleted.');
            }
            redirect($path);
        }
        $id = trim((string) ($_POST['id'] ?? '')) ?: uuid();
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? $_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? $_POST['excerpt'] ?? ''));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || $name === '') {
            flash('Use a valid lowercase slug and provide a name or title.', 'error');
            redirect($path);
        }
        $image = trim((string) ($_POST['image_url'] ?? ''));
        if (!empty($_FILES['image']['tmp_name'])) {
            $upload = save_upload($_FILES['image'], $isProducts ? 'products' : 'blog');
            if ($upload) { $image = $upload; }
        }
        if ($isProducts) {
            $sql = 'INSERT INTO products (id, slug, name, series_code, category, application_grade, description, image_url, icon, spec1_label, spec1_value, spec2_label, spec2_value, display_order, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), series_code=VALUES(series_code), category=VALUES(category), application_grade=VALUES(application_grade), description=VALUES(description), image_url=VALUES(image_url), icon=VALUES(icon), spec1_label=VALUES(spec1_label), spec1_value=VALUES(spec1_value), spec2_label=VALUES(spec2_label), spec2_value=VALUES(spec2_value), display_order=VALUES(display_order), published=VALUES(published)';
            $values = [$id, $slug, $name, trim((string) ($_POST['series_code'] ?? '')), $_POST['category'] ?? 'packaging', trim((string) ($_POST['application_grade'] ?? '')), $description, $image, trim((string) ($_POST['icon'] ?? '')), trim((string) ($_POST['spec1_label'] ?? '')), trim((string) ($_POST['spec1_value'] ?? '')), trim((string) ($_POST['spec2_label'] ?? '')), trim((string) ($_POST['spec2_value'] ?? '')), (int) ($_POST['display_order'] ?? 0), isset($_POST['published']) ? 1 : 0];
        } else {
            $sql = 'INSERT INTO blog_posts (id, slug, title, excerpt, content, image_url, author, category, read_minutes, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), excerpt=VALUES(excerpt), content=VALUES(content), image_url=VALUES(image_url), author=VALUES(author), category=VALUES(category), read_minutes=VALUES(read_minutes), published=VALUES(published)';
            $values = [$id, $slug, $name, $description, trim((string) ($_POST['content'] ?? '')), $image, trim((string) ($_POST['author'] ?? '')), trim((string) ($_POST['category'] ?? '')), (int) ($_POST['read_minutes'] ?? 0) ?: null, isset($_POST['published']) ? 1 : 0];
        }
        db()->prepare($sql)->execute($values);
        flash($editing ? 'Changes saved.' : 'Item saved.');
        redirect($path);
    }
    $items = safe_query(fn() => db()->query("SELECT * FROM {$table} ORDER BY updated_at DESC")->fetchAll(), []);
    render_start($isProducts ? 'Products' : 'Journal'); ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Workspace</p>
          <h1><?= $isProducts ? 'Products' : 'Journal' ?></h1>
          <p class="dash-lead"><?= $isProducts ? 'Materials shown on the public products page.' : 'Notes published in the journal.' ?></p>
        </div>
      </div>
      <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
      <div class="work-split">
        <div class="dash-panel">
          <div class="dash-panel-head">
            <h2><?= $isProducts ? 'Catalog' : 'Posts' ?></h2>
            <span class="sans text-muted"><?= e((string) count($items)) ?></span>
          </div>
          <div class="table-responsive mb-0">
            <table class="table align-middle sans mb-0">
              <thead><tr><th>Name</th><th>Status</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><?= e($item[$isProducts ? 'name' : 'title']) ?></td>
                  <td><span class="portal-status"><?= !empty($item['published']) ? 'Published' : 'Draft' ?></span></td>
                  <td class="text-nowrap">
                    <a class="btn btn-sm btn-outline-dark" href="<?= e(url($path . '?edit=' . $item['id'])) ?>">Edit</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this item?')">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                      <button class="btn btn-sm btn-outline-danger" name="action" value="delete">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$items): ?><tr><td colspan="3"><p class="dash-empty mb-0">Nothing saved yet.</p></td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <form method="post" enctype="multipart/form-data" class="work-form">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
          <h2><?= $editing ? 'Edit' : 'Add' ?> <?= $isProducts ? 'product' : 'post' ?></h2>
          <p class="work-form-lead"><?= $editing ? 'Update the selected item, then save.' : 'Fill the fields and save to add it to the list.' ?></p>
          <div class="work-fields">
            <label class="work-field">Name / title
              <input class="form-control" name="<?= $isProducts ? 'name' : 'title' ?>" value="<?= e($editing[$isProducts ? 'name' : 'title'] ?? '') ?>" required>
            </label>
            <label class="work-field">Slug
              <input class="form-control" name="slug" value="<?= e($editing['slug'] ?? '') ?>" required>
            </label>
            <label class="work-field">Category
              <input class="form-control" name="category" value="<?= e($editing['category'] ?? ($isProducts ? 'packaging' : 'Field note')) ?>">
            </label>
            <?php if ($isProducts): ?>
              <label class="work-field">Series code
                <input class="form-control" name="series_code" value="<?= e($editing['series_code'] ?? '') ?>">
              </label>
              <label class="work-field">Application grade
                <input class="form-control" name="application_grade" value="<?= e($editing['application_grade'] ?? '') ?>">
              </label>
              <label class="work-field">Icon
                <input class="form-control" name="icon" value="<?= e($editing['icon'] ?? '') ?>" placeholder="eco">
              </label>
              <label class="work-field">Spec 1 label
                <input class="form-control" name="spec1_label" value="<?= e($editing['spec1_label'] ?? '') ?>">
              </label>
              <label class="work-field">Spec 1 value
                <input class="form-control" name="spec1_value" value="<?= e($editing['spec1_value'] ?? '') ?>">
              </label>
              <label class="work-field">Spec 2 label
                <input class="form-control" name="spec2_label" value="<?= e($editing['spec2_label'] ?? '') ?>">
              </label>
              <label class="work-field">Spec 2 value
                <input class="form-control" name="spec2_value" value="<?= e($editing['spec2_value'] ?? '') ?>">
              </label>
              <label class="work-field">Display order
                <input class="form-control" type="number" name="display_order" value="<?= e((string) ($editing['display_order'] ?? 0)) ?>">
              </label>
            <?php else: ?>
              <label class="work-field">Author
                <input class="form-control" name="author" value="<?= e($editing['author'] ?? 'CassavaForge') ?>">
              </label>
              <label class="work-field">Read minutes
                <input class="form-control" type="number" name="read_minutes" value="<?= e((string) ($editing['read_minutes'] ?? 3)) ?>">
              </label>
            <?php endif; ?>
            <label class="work-field is-wide"><?= $isProducts ? 'Description' : 'Excerpt' ?>
              <textarea class="form-control" name="<?= $isProducts ? 'description' : 'excerpt' ?>" rows="4"><?= e($editing[$isProducts ? 'description' : 'excerpt'] ?? '') ?></textarea>
            </label>
            <?php if (!$isProducts): ?>
              <label class="work-field is-wide">Content
                <textarea class="form-control" name="content" rows="7"><?= e($editing['content'] ?? '') ?></textarea>
              </label>
            <?php endif; ?>
            <label class="work-field">Image URL
              <input class="form-control" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>">
            </label>
            <label class="work-field">Upload image
              <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input>
            </label>
            <div class="work-field is-wide">
              <img class="img-fluid rounded" style="max-height:160px" alt="" data-image-preview src="<?= e(asset_url($editing['image_url'] ?? null)) ?>"<?= empty($editing['image_url']) ? ' hidden' : '' ?>>
              <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="published" id="published" <?= !isset($editing['published']) || $editing['published'] ? 'checked' : '' ?>><label class="form-check-label sans" for="published">Published</label></div>
            </div>
          </div>
          <div class="work-actions">
            <button class="btn btn-primary sans">Save</button>
            <?php if ($editing): ?><a class="btn btn-link sans" href="<?= e(url($path)) ?>">Cancel</a><?php endif; ?>
          </div>
        </form>
      </div>
      <script>
      (function () {
        var input = document.querySelector('[data-image-input]');
        var preview = document.querySelector('[data-image-preview]');
        if (!input || !preview) { return; }
        input.addEventListener('change', function () {
          var file = input.files && input.files[0];
          if (!file) { return; }
          preview.src = URL.createObjectURL(file);
          preview.hidden = false;
        });
      })();
      </script>
    </section>
    <?php render_end(); exit;
}

if (str_starts_with($path, 'blog/') && strlen($path) > 5) {
    render_start('Journal article');
    render_blog_article(substr($path, 5));
    render_end();
    exit;
}

if ($path === 'privacy') {
    render_start('Privacy');
    render_privacy();
    render_end();
    exit;
}

if (in_array($path, ['', 'about', 'products', 'impact', 'blog', 'contact'], true)) {
    render_start($path === '' ? 'Home' : ucfirst($path));
    render_original_page($path === '' ? 'home' : $path);
    render_end();
    exit;
}

if (str_starts_with($path, 'products/') && strlen($path) > 9) {
    render_product_page(substr($path, 9));
    exit;
}

http_response_code(404);
render_start('Page not found');
render_not_found();
render_end();
