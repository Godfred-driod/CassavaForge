<?php
declare(strict_types=1);

function handle_signin(): void
{
    $person = current_user();
    if ($person) {
        redirect(after_login_path($person));
    }

    $error = null;
    if (login_is_locked()) {
        $error = 'Too many sign-in attempts. Please wait a few minutes and try again.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = authenticate_user($email, $password, ['owner', 'staff', 'partner']);
        if ($user) {
            login_clear_failures();
            login_user($user);
            redirect(after_login_path($user));
        }
        login_register_failure();
        $error = login_is_locked()
            ? 'Too many sign-in attempts. Please wait a few minutes and try again.'
            : 'The email or password was not recognized.';
    }

    $GLOBALS['loginEyebrow'] = 'Account';
    $GLOBALS['loginHeading'] = 'Sign in';
    render_start('Sign in');
    ?>
    <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger sans"><?= e($error) ?></div><?php endif; ?>
    <p class="text-muted sans mb-4">Staff and partners sign in here.</p>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label class="form-label sans" for="email">Email</label>
      <input class="form-control mb-3" id="email" type="email" name="email" autocomplete="username" required <?= login_is_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="password">Password</label>
      <input class="form-control mb-4" id="password" type="password" name="password" autocomplete="current-password" required <?= login_is_locked() ? 'disabled' : '' ?>>
      <button class="btn btn-primary w-100 sans" type="submit" <?= login_is_locked() ? 'disabled' : '' ?>>Sign in</button>
    </form>
    <p class="admin-login-back">Need a partner account? <a href="<?= e(url('portal/register')) ?>">Create one</a></p>
    <?php
    render_end();
    exit;
}

function handle_portal_register(): void
{
    $person = current_user();
    if ($person) {
        redirect(after_login_path($person));
    }

    $error = null;
    if (register_failures_locked()) {
        $error = 'Too many registration attempts. Please wait a few minutes.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $company = trim((string) ($_POST['company_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error = 'Provide your name, a valid email, and a password of at least 8 characters.';
            register_record_failure();
        } elseif ($password !== $confirm) {
            $error = 'The password and confirmation did not match.';
            register_record_failure();
        } elseif (find_user_by_email($email)) {
            $error = 'That email already has an account. Sign in instead.';
            register_record_failure();
        } else {
            db()->prepare('INSERT INTO users (id, email, password_hash, full_name, role, status, company_name, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([
                uuid(), $email, password_hash($password, PASSWORD_DEFAULT), $name, 'partner', 'active', $company !== '' ? $company : null, $phone !== '' ? $phone : null,
            ]);
            $user = find_user_by_email($email);
            if ($user) {
                login_clear_failures();
                login_user($user);
                redirect('portal');
            }
            $error = 'The account could not be created. Please try again.';
        }
    }

    $GLOBALS['loginEyebrow'] = 'Partner portal';
    $GLOBALS['loginHeading'] = 'Create an account';
    render_start('Create partner account');
    ?>
    <?php if ($error): ?><div class="alert alert-danger sans"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label class="form-label sans" for="full_name">Name</label>
      <input class="form-control mb-3" id="full_name" name="full_name" maxlength="200" required <?= register_failures_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="company_name">Company</label>
      <input class="form-control mb-3" id="company_name" name="company_name" maxlength="200" <?= register_failures_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="phone">Phone</label>
      <input class="form-control mb-3" id="phone" name="phone" maxlength="40" <?= register_failures_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="email">Email</label>
      <input class="form-control mb-3" id="email" type="email" name="email" maxlength="254" required <?= register_failures_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="password">Password</label>
      <input class="form-control mb-3" id="password" type="password" name="password" minlength="8" required <?= register_failures_locked() ? 'disabled' : '' ?>>
      <label class="form-label sans" for="confirm_password">Confirm password</label>
      <input class="form-control mb-4" id="confirm_password" type="password" name="confirm_password" minlength="8" required <?= register_failures_locked() ? 'disabled' : '' ?>>
      <button class="btn btn-primary w-100 sans" type="submit" <?= register_failures_locked() ? 'disabled' : '' ?>>Create account</button>
    </form>
    <p class="admin-login-back">Already have an account? <a href="<?= e(url('signin')) ?>">Sign in</a></p>
    <?php
    render_end();
    exit;
}

function partner_orders(string $userId): array
{
    return safe_query(function () use ($userId): array {
        $stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }, []);
}

function handle_portal_pages(string $path): void
{
    require_partner();
    $person = current_user();

    if ($path === 'portal/new') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $types = order_types();
            $type = (string) ($_POST['type'] ?? 'sample');
            $type = isset($types[$type]) ? $type : 'sample';
            $productId = trim((string) ($_POST['product_id'] ?? ''));
            $productId = $productId === '' ? null : $productId;
            $quantity = trim((string) ($_POST['quantity'] ?? ''));
            $unit = trim((string) ($_POST['unit'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            if ($message === '' || strlen($message) > 5000) {
                flash('Describe what you need in the message field.', 'error');
                redirect('portal/new');
            }
            $reference = create_work_order((string) $person['id'], $type, $message, $productId, $quantity, $unit, (string) $person['id']);
            flash('Request ' . $reference . ' is with the team.');
            redirect('portal/orders/' . $reference);
        }
        $products = published_products();
        render_start('New request');
        ?>
        <section class="admin-panel">
          <div class="dash-head">
            <div>
              <p class="eyebrow">Portal</p>
              <h1>New request</h1>
              <p class="dash-lead">Ask for a sample, converting trial, or quote. The team will follow up here.</p>
            </div>
          </div>
          <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
          <form method="post" class="work-form">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="work-fields">
              <label class="work-field" for="type">Type
                <select class="form-select" id="type" name="type">
                  <?php foreach (order_types() as $value => $label): ?>
                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label class="work-field" for="product_id">Material
                <select class="form-select" id="product_id" name="product_id">
                  <option value="">Not sure yet</option>
                  <?php foreach ($products as $product): ?>
                    <option value="<?= e($product['id']) ?>"><?= e($product['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label class="work-field" for="quantity">Quantity
                <input class="form-control" id="quantity" name="quantity" maxlength="80" placeholder="e.g. 20">
              </label>
              <label class="work-field" for="unit">Unit
                <input class="form-control" id="unit" name="unit" maxlength="40" placeholder="units, kg, rolls">
              </label>
              <label class="work-field is-wide" for="message">What do you need?
                <textarea class="form-control" id="message" name="message" rows="6" maxlength="5000" required></textarea>
              </label>
            </div>
            <div class="work-actions">
              <button class="btn btn-primary sans" type="submit">Submit request</button>
            </div>
          </form>
        </section>
        <?php
        render_end();
        exit;
    }

    if ($path === 'portal/account') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $current = (string) ($_POST['current_password'] ?? '');
            $next = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');
            $hash = (string) ($person['password_hash'] ?? '');
            if ($hash === '' || !password_verify($current, $hash)) {
                flash('The current password was not recognized.', 'error');
            } elseif (strlen($next) < 8) {
                flash('Use a new password of at least 8 characters.', 'error');
            } elseif ($next !== $confirm) {
                flash('The new password and confirmation did not match.', 'error');
            } else {
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($next, PASSWORD_DEFAULT), $person['id']]);
                session_regenerate_id(true);
                $_SESSION['user_seen'] = time();
                flash('Password updated.');
            }
            redirect('portal/account');
        }
        render_start('Account');
        ?>
        <section class="admin-panel">
          <div class="dash-head">
            <div>
              <p class="eyebrow">Portal</p>
              <h1>Account</h1>
              <p class="dash-lead">Signed in as <?= e((string) $person['email']) ?>. Sessions expire after 8 hours idle.</p>
            </div>
          </div>
          <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
          <div class="work-split">
            <div class="work-form">
              <h2>Profile</h2>
              <p class="work-form-lead">The details on this partner account.</p>
              <div class="work-fields">
                <p class="work-field">Name<strong><?= e((string) $person['full_name']) ?></strong></p>
                <p class="work-field">Email<strong><?= e((string) $person['email']) ?></strong></p>
                <?php if (!empty($person['company_name'])): ?><p class="work-field">Company<strong><?= e((string) $person['company_name']) ?></strong></p><?php endif; ?>
                <?php if (!empty($person['phone'])): ?><p class="work-field">Phone<strong><?= e((string) $person['phone']) ?></strong></p><?php endif; ?>
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
        <?php
        render_end();
        exit;
    }

    if (str_starts_with($path, 'portal/orders/') && strlen($path) > 14) {
        $reference = strtoupper(trim(substr($path, 14)));
        $order = fetch_order_by_reference($reference);
        if (!$order || $order['user_id'] !== $person['id']) {
            http_response_code(404);
            render_start('Request not found');
            echo '<section class="admin-panel"><h1 class="h3">Request not found</h1><p><a href="' . e(url('portal/orders')) . '">Back to requests</a></p></section>';
            render_end();
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $body = trim((string) ($_POST['message'] ?? ''));
            if ($body === '' || strlen($body) > 5000) {
                flash('Write a short follow-up message.', 'error');
            } else {
                add_order_event((string) $order['id'], 'message', $body, false, (string) $person['id']);
                flash('Message added.');
            }
            redirect('portal/orders/' . $order['reference']);
        }
        $events = fetch_order_events((string) $order['id'], false);
        render_start($order['reference']);
        ?>
        <section class="admin-panel">
          <p class="eyebrow"><a href="<?= e(url('portal/orders')) ?>">Requests</a></p>
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
              <h1 class="h3 mb-1"><?= e($order['reference']) ?></h1>
              <p class="mb-0 text-muted sans"><?= e(order_types()[$order['type']] ?? $order['type']) ?><?php if (!empty($order['product_name'])): ?> · <?= e($order['product_name']) ?><?php endif; ?></p>
            </div>
            <span class="portal-status"><?= e(order_statuses()[$order['status']] ?? $order['status']) ?></span>
          </div>
          <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="card p-4 mb-4">
                <p class="eyebrow mb-2">Request</p>
                <p class="mb-0"><?= nl2br(e($order['message'])) ?></p>
                <?php if (!empty($order['quantity'])): ?><p class="small text-muted mt-3 mb-0">Qty: <?= e($order['quantity']) ?><?= !empty($order['unit']) ? ' ' . e($order['unit']) : '' ?></p><?php endif; ?>
              </div>
              <div class="card p-4">
                <h2 class="h5 mb-3">Updates</h2>
                <?php if (!$events): ?><p class="text-muted sans mb-0">No updates yet.</p><?php endif; ?>
                <?php foreach ($events as $event): ?>
                  <div class="border-bottom py-3">
                    <div class="small text-muted"><?= e($event['created_at']) ?><?= !empty($event['actor_name']) ? ' · ' . e($event['actor_name']) : '' ?></div>
                    <div><?= e(ucfirst((string) $event['event'])) ?><?php if (!empty($event['body'])): ?> — <?= nl2br(e($event['body'])) ?><?php endif; ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-lg-5">
              <form method="post" class="card p-4">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <h2 class="h5 mb-3">Add a follow-up</h2>
                <label class="form-label sans" for="message">Message</label>
                <textarea class="form-control mb-3" id="message" name="message" rows="5" maxlength="5000" required></textarea>
                <button class="btn btn-primary sans" type="submit">Send</button>
              </form>
            </div>
          </div>
        </section>
        <?php
        render_end();
        exit;
    }

    $orders = partner_orders((string) $person['id']);
    if ($path === 'portal/orders') {
        render_start('Requests');
        ?>
        <section class="admin-panel">
          <div class="dash-head">
            <div>
              <p class="eyebrow">Portal</p>
              <h1>Requests</h1>
              <p class="dash-lead">Every sample, trial, and quote you have sent.</p>
            </div>
            <a class="btn btn-primary sans" href="<?= e(url('portal/new')) ?>">New request</a>
          </div>
          <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
          <div class="dash-panel">
            <div class="table-responsive mb-0">
              <table class="table align-middle sans mb-0">
                <thead><tr><th>Reference</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $row): ?>
                  <tr>
                    <td><a href="<?= e(url('portal/orders/' . $row['reference'])) ?>"><?= e($row['reference']) ?></a></td>
                    <td><?= e(order_types()[$row['type']] ?? $row['type']) ?></td>
                    <td><span class="portal-status"><?= e(order_statuses()[$row['status']] ?? $row['status']) ?></span></td>
                    <td><?= e(date('j M Y', strtotime((string) $row['created_at']))) ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="4"><p class="dash-empty mb-0">No requests yet. <a href="<?= e(url('portal/new')) ?>">Start one</a>.</p></td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
        <?php
        render_end();
        exit;
    }

    $open = 0;
    $active = 0;
    $done = 0;
    foreach ($orders as $row) {
        if (in_array((string) $row['status'], ['closed', 'declined'], true)) {
            $done++;
        } else {
            $open++;
            if (in_array((string) $row['status'], ['reviewing', 'in_progress', 'shipped', 'quoted'], true)) {
                $active++;
            }
        }
    }
    $recent = array_slice($orders, 0, 8);
    render_start('Dashboard');
    ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Overview</p>
          <h1>Hello, <?= e((string) $person['full_name']) ?></h1>
          <p class="dash-lead">Request a sample, trial, or quote, then track every update from this portal.</p>
        </div>
        <a class="btn btn-primary sans" href="<?= e(url('portal/new')) ?>">New request</a>
      </div>
      <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
      <div class="dash-grid is-4">
        <a class="dash-card" href="<?= e(url('portal/orders')) ?>">
          <span class="dash-card-icon"><span class="material-symbols-outlined">receipt_long</span></span>
          <p class="dash-card-label">Open</p>
          <strong class="dash-card-value"><?= e((string) $open) ?></strong>
          <span class="dash-card-hint">Still with the team</span>
        </a>
        <a class="dash-card" href="<?= e(url('portal/orders')) ?>">
          <span class="dash-card-icon"><span class="material-symbols-outlined">progress_activity</span></span>
          <p class="dash-card-label">In progress</p>
          <strong class="dash-card-value"><?= e((string) $active) ?></strong>
          <span class="dash-card-hint">Being reviewed or prepared</span>
        </a>
        <a class="dash-card" href="<?= e(url('portal/orders')) ?>">
          <span class="dash-card-icon"><span class="material-symbols-outlined">task_alt</span></span>
          <p class="dash-card-label">Closed</p>
          <strong class="dash-card-value"><?= e((string) $done) ?></strong>
          <span class="dash-card-hint">Finished or declined</span>
        </a>
        <a class="dash-card is-accent" href="<?= e(url('portal/new')) ?>">
          <span class="dash-card-icon"><span class="material-symbols-outlined">add_circle</span></span>
          <p class="dash-card-label">Start</p>
          <strong class="dash-card-value is-text">New request</strong>
          <span class="dash-card-hint">Sample, trial, or quote</span>
        </a>
      </div>
      <div class="dash-panel">
        <div class="dash-panel-head">
          <h2>Recent requests</h2>
          <a href="<?= e(url('portal/orders')) ?>">View all</a>
        </div>
        <?php if (!$recent): ?>
          <p class="dash-empty sans mb-0">No requests yet. <a href="<?= e(url('portal/new')) ?>">Start one</a>.</p>
        <?php else: ?>
          <div class="table-responsive mb-0">
            <table class="table align-middle sans mb-0">
              <thead><tr><th>Reference</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach ($recent as $row): ?>
                <tr>
                  <td><a href="<?= e(url('portal/orders/' . $row['reference'])) ?>"><?= e($row['reference']) ?></a></td>
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
    <?php
    render_end();
    exit;
}
