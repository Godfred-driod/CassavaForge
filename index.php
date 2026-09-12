<?php
declare(strict_types=1);

// Let PHP's built-in server deliver CSS, images, and other public files directly.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$staticFile = __DIR__ . '/' . ltrim($requestPath, '/');
if ($requestPath !== '/' && is_file($staticFile)) {
    return false;
}

session_start();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/site-sections.php';

$path = trim($requestPath, '/');
$path = preg_replace('#^php-app/?#', '', $path);
$path = trim($path, '/');

function render_start(string $title): void
{
    $GLOBALS['pageTitle'] = $title;
    require __DIR__ . '/includes/header.php';
}

function render_end(): void
{
    require __DIR__ . '/includes/footer.php';
}

function uuid(): string
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
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

function public_visitor(): void
{
    if (is_admin() || !empty($_COOKIE['cf_visitor'])) {
        return;
    }
    $visitor = uuid();
    setcookie('cf_visitor', $visitor, ['expires' => time() + 31536000, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
    safe_query(function () use ($visitor): void {
        $stmt = db()->prepare('INSERT IGNORE INTO site_visitors (visitor_id) VALUES (?)');
        $stmt->execute([$visitor]);
    });
}

public_visitor();

if ($path === 'admin/logout') {
    $_SESSION = [];
    session_destroy();
    redirect('admin/login');
}

if ($path === 'admin/login') {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $config = app_config();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === $config['admin_email'] && $config['admin_password_hash'] !== '' && password_verify($password, $config['admin_password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $email;
            redirect('admin');
        }
        $error = 'The email or password was not recognized.';
    }
    render_start('Admin sign in'); ?>
    <section class="section"><div class="container" style="max-width:520px">
      <p class="eyebrow">Private workspace</p><h1 class="display-4">Sign in.</h1>
      <?php if ($error): ?><div class="alert alert-danger sans"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="card p-4 mt-4"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label class="form-label sans">Email</label><input class="form-control mb-3" type="email" name="email" required>
        <label class="form-label sans">Password</label><input class="form-control mb-4" type="password" name="password" required>
        <button class="btn btn-primary sans" type="submit">Sign in</button>
      </form>
    </div></section>
    <?php render_end(); exit;
}

if ($path === 'admin') {
    require_admin();
    $products = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(), 0);
    $posts = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn(), 0);
    $messages = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn(), 0);
    $visitors = safe_query(fn() => (int) db()->query('SELECT COUNT(*) FROM site_visitors')->fetchColumn(), 0);
    render_start('Admin dashboard'); ?>
    <section class="section"><div class="container">
      <div class="d-flex justify-content-between align-items-center mb-5"><div><p class="eyebrow">Workspace</p><h1>Dashboard</h1></div><a class="btn btn-outline-dark sans" href="<?= e(url('admin/logout')) ?>">Sign out</a></div>
      <div class="row g-4">
        <?php foreach ([['Products', $products, 'admin/products'], ['Journal posts', $posts, 'admin/blog'], ['Unread messages', $messages, 'admin/messages'], ['Unique visits', $visitors, 'admin']] as $stat): ?><div class="col-md-3"><a class="card p-4 text-decoration-none text-dark d-block h-100" href="<?= e(url($stat[2])) ?>"><p class="eyebrow"><?= e($stat[0]) ?></p><strong class="display-5"><?= e((string) $stat[1]) ?></strong></a></div><?php endforeach; ?>
      </div>
    </div></section>
    <?php render_end(); exit;
}

if ($path === 'contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email_address'] ?? ''));
    $message = trim((string) ($_POST['message_body'] ?? ''));
    if ($name === '' || strlen($name) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || $message === '' || strlen($message) > 5000) {
        flash('Please complete all fields with a valid email address.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO contact_messages (id, full_name, email_address, message_body) VALUES (?, ?, ?, ?)');
            $stmt->execute([uuid(), $name, $email, $message]);
            flash('Thanks. Your message is with our team.');
        } catch (Throwable $error) {
            error_log($error->getMessage());
            flash('We could not save your message. Please try again.');
        }
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
        <section class="section"><div class="container"><div class="d-flex justify-content-between mb-4"><h1>Messages</h1><a class="btn btn-outline-dark sans" href="<?= e(url('admin')) ?>">Dashboard</a></div><div class="table-responsive"><table class="table align-middle sans"><thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th></th></tr></thead><tbody><?php foreach ($messages as $message): ?><tr class="<?= empty($message['is_read']) ? 'fw-bold' : '' ?>"><td><?= e($message['full_name']) ?></td><td><?= e($message['email_address']) ?></td><td><?= e($message['message_body']) ?></td><td><?= e($message['created_at']) ?></td><td><form method="post" class="d-flex gap-1"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($message['id']) ?>"><button class="btn btn-sm btn-outline-success" name="action" value="read">Read</button><button class="btn btn-sm btn-outline-danger" name="action" value="delete">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></section>
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
        $id = trim((string) ($_POST['id'] ?? '')) ?: uuid();
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? $_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? $_POST['excerpt'] ?? ''));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || $name === '') {
            flash('Use a valid lowercase slug and provide a name or title.');
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
        redirect($path);
    }
    if (isset($_GET['delete'])) {
        db()->prepare("DELETE FROM {$table} WHERE id = ?")->execute([(string) $_GET['delete']]);
        redirect($path);
    }
    $items = safe_query(fn() => db()->query("SELECT * FROM {$table} ORDER BY updated_at DESC")->fetchAll(), []);
    render_start($isProducts ? 'Manage products' : 'Manage journal'); ?>
    <section class="section"><div class="container"><div class="d-flex justify-content-between mb-4"><h1><?= $isProducts ? 'Products' : 'Journal' ?></h1><a class="btn btn-outline-dark sans" href="<?= e(url('admin')) ?>">Dashboard</a></div><div class="row g-4"><div class="col-lg-7"><div class="table-responsive"><table class="table sans"><thead><tr><th>Name</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item[$isProducts ? 'name' : 'title']) ?></td><td><?= !empty($item['published']) ? 'Published' : 'Draft' ?></td><td><a class="btn btn-sm btn-outline-dark" href="<?= e(url($path . '?edit=' . $item['id'])) ?>">Edit</a> <a class="btn btn-sm btn-outline-danger" href="<?= e(url($path . '?delete=' . $item['id'])) ?>" onclick="return confirm('Delete this item?')">Delete</a></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-lg-5"><form method="post" enctype="multipart/form-data" class="card p-4"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>"><h2 class="h4"><?= $editing ? 'Edit' : 'Add' ?> <?= $isProducts ? 'product' : 'post' ?></h2><label class="form-label sans">Name / title</label><input class="form-control mb-3" name="<?= $isProducts ? 'name' : 'title' ?>" value="<?= e($editing[$isProducts ? 'name' : 'title'] ?? '') ?>" required><label class="form-label sans">Slug</label><input class="form-control mb-3" name="slug" value="<?= e($editing['slug'] ?? '') ?>" required><label class="form-label sans">Category</label><input class="form-control mb-3" name="category" value="<?= e($editing['category'] ?? ($isProducts ? 'packaging' : 'Field note')) ?>"><?php if ($isProducts): ?><label class="form-label sans">Series code</label><input class="form-control mb-3" name="series_code" value="<?= e($editing['series_code'] ?? '') ?>"><label class="form-label sans">Application grade</label><input class="form-control mb-3" name="application_grade" value="<?= e($editing['application_grade'] ?? '') ?>"><label class="form-label sans">Display order</label><input class="form-control mb-3" type="number" name="display_order" value="<?= e((string) ($editing['display_order'] ?? 0)) ?>"><?php endif; ?><label class="form-label sans"><?= $isProducts ? 'Description' : 'Excerpt' ?></label><textarea class="form-control mb-3" name="<?= $isProducts ? 'description' : 'excerpt' ?>" rows="4"><?= e($editing[$isProducts ? 'description' : 'excerpt'] ?? '') ?></textarea><?php if (!$isProducts): ?><label class="form-label sans">Content</label><textarea class="form-control mb-3" name="content" rows="7"><?= e($editing['content'] ?? '') ?></textarea><label class="form-label sans">Author</label><input class="form-control mb-3" name="author" value="<?= e($editing['author'] ?? 'CassavaForge') ?>"><label class="form-label sans">Read minutes</label><input class="form-control mb-3" type="number" name="read_minutes" value="<?= e((string) ($editing['read_minutes'] ?? 3)) ?>"><?php endif; ?><label class="form-label sans">Image URL or upload</label><input class="form-control mb-2" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>"><input class="form-control mb-3" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="published" <?= !isset($editing['published']) || $editing['published'] ? 'checked' : '' ?>><label class="form-check-label sans">Published</label></div><button class="btn btn-primary sans">Save</button><?php if ($editing): ?> <a class="btn btn-link sans" href="<?= e(url($path)) ?>">Cancel</a><?php endif; ?></form></div></div></div></section>
    <?php render_end(); exit;
}

if (in_array($path, ['', 'about', 'products', 'impact', 'blog', 'contact'], true)) {
    render_start($path === '' ? 'Home' : ucfirst($path));
    render_original_page($path === '' ? 'home' : $path);
    render_end();
    exit;
}

if ($path === 'products' || str_starts_with($path, 'products/')) {
    $slug = str_starts_with($path, 'products/') ? substr($path, 9) : null;
    $products = safe_query(function () use ($slug): array {
        if ($slug) { $stmt = db()->prepare('SELECT * FROM products WHERE slug = ? AND published = 1 LIMIT 1'); $stmt->execute([$slug]); return [$stmt->fetch() ?: null]; }
        $category = trim((string) ($_GET['category'] ?? ''));
        if (in_array($category, ['packaging', 'films', 'cutlery'], true)) {
            $stmt = db()->prepare('SELECT * FROM products WHERE published = 1 AND category = ? ORDER BY display_order, name');
            $stmt->execute([$category]);
            return $stmt->fetchAll();
        }
        return db()->query('SELECT * FROM products WHERE published = 1 ORDER BY display_order, name')->fetchAll();
    }, []);
    if ($slug && empty($products[0])) { http_response_code(404); $products = []; }
    render_start($slug ? 'Product detail' : 'Products'); ?>
        <section class="image-hero hero py-5" style="background-image:url('<?= e(url('assets/images/products-minimal-studio-still-life-photography-of-1.jpg')) ?>')"><div class="container py-5"><p class="eyebrow">Material systems</p><h1 class="display-3 mb-5">High-Performance Bioplastics for a<br>Better Future.</h1></div></section><section class="section"><div class="container">
            <?php if (!$slug): ?><div class="d-flex flex-wrap gap-2 mb-4 sans"><a class="btn btn-sm <?= empty($_GET['category']) ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e(url('products')) ?>">All</a><?php foreach (['packaging','films','cutlery'] as $category): ?><a class="btn btn-sm <?= ($_GET['category'] ?? '') === $category ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e(url('products?category=' . $category)) ?>"><?= e(ucfirst($category)) ?></a><?php endforeach; ?></div><?php endif; ?>
      <?php if ($slug && !empty($products[0])): $product = $products[0]; ?><div class="row g-5 align-items-center"><div class="col-md-6"><div class="ratio ratio-4x3 bg-success-subtle"><img class="product-image" src="<?= e(asset_url($product['image_url'] ?? null)) ?>" alt="<?= e($product['name']) ?>"></div></div><div class="col-md-6"><span class="badge text-bg-success sans mb-3"><?= e($product['category']) ?></span><h2><?= e($product['name']) ?></h2><p class="lead"><?= e($product['description']) ?></p><a class="btn btn-outline-dark sans" href="<?= e(url('products')) ?>">Back to products</a></div></div>
      <?php else: ?><div class="row g-4"><?php foreach ($products as $product): ?><div class="col-md-4"><article class="card h-100 overflow-hidden"><img class="product-image" src="<?= e(asset_url($product['image_url'] ?? null)) ?>" alt="<?= e($product['name']) ?>"><div class="p-4"><p class="eyebrow"><?= e($product['category']) ?></p><h2 class="h4"><?= e($product['name']) ?></h2><p><?= e($product['description']) ?></p><?php if (!empty($product['slug'])): ?><a class="btn btn-sm btn-outline-dark sans" href="<?= e(url('products/' . $product['slug'])) ?>">View material</a><?php endif; ?></div></article></div><?php endforeach; ?></div><?php if (!$products): ?><p class="sans">Products will appear here after the MySQL seed data is imported.</p><?php endif; ?><?php endif; ?></div></section>
    <?php if (!$slug) { render_offset_calculator(); render_faq(); render_specs_modal(); } render_end(); exit;
}

if ($path === 'blog' || str_starts_with($path, 'blog/')) {
    $slug = str_starts_with($path, 'blog/') ? substr($path, 5) : null;
    $posts = safe_query(function () use ($slug): array {
        if ($slug) { $stmt = db()->prepare('SELECT * FROM blog_posts WHERE slug = ? AND published = 1 LIMIT 1'); $stmt->execute([$slug]); return [$stmt->fetch() ?: null]; }
        return db()->query('SELECT * FROM blog_posts WHERE published = 1 ORDER BY published_at DESC')->fetchAll();
    }, []);
    render_start($slug ? 'Journal article' : 'Journal'); ?>
    <section class="image-hero hero py-5" style="background-image:url('<?= e(url('assets/images/blog-background-7.jpg')) ?>')"><div class="container py-5"><p class="eyebrow">Field notes</p><h1 class="display-3 mb-5">Ideas. Updates.<br>Progress.</h1></div></section><section class="section"><div class="container"><?php if ($slug && !empty($posts[0])): $post = $posts[0]; ?><article style="max-width:800px"><p class="eyebrow"><?= e($post['category']) ?></p><h2 class="display-5"><?= e($post['title']) ?></h2><p class="lead"><?= e($post['excerpt']) ?></p><div class="mt-5" style="white-space:pre-line"><?= e($post['content']) ?></div></article><?php else: ?><div class="row g-4"><?php foreach ($posts as $post): ?><div class="col-md-6"><article class="card h-100 p-4"><p class="eyebrow"><?= e($post['category']) ?></p><h2><?= e($post['title']) ?></h2><p><?= e($post['excerpt']) ?></p><a class="btn btn-sm btn-outline-dark sans" href="<?= e(url('blog/' . $post['slug'])) ?>">Read article</a></article></div><?php endforeach; ?></div><?php if (!$posts): ?><p class="sans">Journal posts will appear here after the MySQL seed data is imported.</p><?php endif; ?><?php endif; ?><div class="card p-4 mt-5"><h2 class="h4">Biomaterial breakthroughs delivered straight to your inbox.</h2><form class="row g-2 mt-2"><div class="col-sm-8"><input class="form-control" type="email" placeholder="Email address" aria-label="Email address"></div><div class="col-sm-4"><button class="btn btn-success w-100 sans" type="button">Subscribe</button></div></form></div></div></section>
    <?php render_end(); exit;
}

$static = [
    '' => ['Home', '<section class="image-hero hero py-5" style="background-image:url(\'' . e(url('assets/images/home-background-1.jpg')) . '\')"><div class="container py-5"><p class="eyebrow">Plant-based materials, engineered for real life</p><h1 class="my-4">Better materials<br>begin at the root.</h1><p class="lead col-lg-7">CassavaForge turns renewable cassava starch into practical, high-performance alternatives for packaging, films, and molded products.</p><a class="btn btn-light btn-lg sans mt-3" href="' . e(url('products')) . '">Explore materials</a></div></section><section class="section"><div class="container"><div class="row g-5"><div class="col-md-6"><p class="eyebrow">A different starting point</p><h2 class="display-5">We make the useful choice easier.</h2></div><div class="col-md-6"><p class="lead">Our work connects agricultural potential with the exacting demands of modern product design. Every material starts with a better question: what should happen after use?</p></div></div></div></section>'],
    'about' => ['About', '<section class="image-hero hero py-5" style="background-image:url(\'' . e(url('assets/images/about-background-3.jpg')) . '\')"><div class="container py-5"><p class="eyebrow">Our approach</p><h1 class="display-3">Innovative Materials.<br>Regenerative Future.</h1><p class="lead col-lg-7">We are building a materials company around cassava, a resilient crop with the potential to support rural livelihoods and replace conventional plastics.</p></div></section>'],
    'impact' => ['Impact', '<section class="image-hero hero py-5" style="background-image:url(\'' . e(url('assets/images/impact-background-1.jpg')) . '\')"><div class="container py-5"><p class="eyebrow">Our impact</p><h1 class="display-3">People. Planet.<br>Progress.</h1><p class="lead col-lg-7">We are designing a better material system that creates value for farmers, reduces plastic waste, and keeps useful resources moving through a circular economy.</p></div></section>'],
    'contact' => ['Contact', '<section class="image-hero hero py-5" style="background-image:url(\'' . e(url('assets/images/contact-background-1.jpg')) . '\')"><div class="container py-5"><p class="eyebrow">Inquiries &amp; Partnerships</p><h1 class="display-4">Let us build a sustainable future together.</h1><p class="lead col-lg-7">Whether you are exploring a new material, planning a pilot, or looking for a long-term supply partner, we would like to hear from you.</p></div></section><section class="section"><div class="container"><div class="row g-5"><div class="col-md-7"><h2 class="h3 mb-4">Send Us a Message</h2>' . (($message = flash()) ? '<div class="alert alert-success sans">' . e($message) . '</div>' : '') . '<form method="post" class="card p-4"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><label class="form-label sans">Name</label><input class="form-control mb-3" name="full_name" maxlength="200" required><label class="form-label sans">Email</label><input class="form-control mb-3" type="email" name="email_address" maxlength="254" required><label class="form-label sans">Message</label><textarea class="form-control mb-4" name="message_body" maxlength="5000" rows="6" required></textarea><button class="btn btn-primary sans" type="submit">Send inquiry</button></form></div></div></div></section>'],
        '' => ['Home', '<section class="hero image-hero py-5" style="background-image:url(\'assets/images/home-background-1.jpg\')"><div class="container py-5"><p class="eyebrow">Plant-based materials, engineered for real life</p><h1 class="my-4">Better materials<br>begin at the root.</h1><p class="lead col-lg-7">CassavaForge turns renewable cassava starch into practical, high-performance alternatives for packaging, films, and molded products.</p><a class="btn btn-light btn-lg sans mt-3" href="' . e(url('products')) . '">Explore materials</a></div></section><section class="section"><div class="container"><div class="row g-5 align-items-center"><div class="col-md-6"><p class="eyebrow">A different starting point</p><h2 class="display-5">We make the useful choice easier.</h2></div><div class="col-md-6 image-panel rounded-3" style="background-image:url(\'assets/images/home-background-2.jpg\')"></div></div></div></section>'],
        'about' => ['About', '<section class="hero image-hero py-5" style="background-image:url(\'assets/images/about-background-3.jpg\')"><div class="container py-5"><p class="eyebrow">Our approach</p><h1 class="display-3">Innovative Materials.<br>Regenerative Future.</h1><p class="lead col-lg-7">We are building a materials company around cassava, a resilient crop with the potential to support rural livelihoods and replace conventional plastics.</p></div></section>'],
        'impact' => ['Impact', '<section class="hero image-hero py-5" style="background-image:url(\'assets/images/impact-background-1.jpg\')"><div class="container py-5"><p class="eyebrow">Our impact</p><h1 class="display-3">People. Planet.<br>Progress.</h1><p class="lead col-lg-7">We are designing a better material system that creates value for farmers, reduces plastic waste, and keeps useful resources moving through a circular economy.</p></div></section>'],
        'contact' => ['Contact', '<section class="hero image-hero py-5" style="background-image:url(\'assets/images/contact-background-1.jpg\')"><div class="container py-5"><div class="row"><div class="col-lg-8"><p class="eyebrow">Inquiries &amp; Partnerships</p><h1>Let us build a sustainable future together.</h1><p class="lead">Whether you are exploring a new material, planning a pilot, or looking for a long-term supply partner, we would like to hear from you.</p></div></div></div></section><section class="section"><div class="container"><div class="row g-5"><div class="col-md-5"><h2 class="h3">Send Us a Message</h2></div><div class="col-md-7">' . (($message = flash()) ? '<div class="alert alert-success sans">' . e($message) . '</div>' : '') . '<form method="post" class="card p-4"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><label class="form-label sans">Name</label><input class="form-control mb-3" name="full_name" maxlength="200" required><label class="form-label sans">Email</label><input class="form-control mb-3" type="email" name="email_address" maxlength="254" required><label class="form-label sans">Message</label><textarea class="form-control mb-4" name="message_body" maxlength="5000" rows="6" required></textarea><button class="btn btn-primary sans" type="submit">Send inquiry</button></form></div></div></div></section>'],
];

[$title, $body] = $static[$path] ?? ['Page not found', '<section class="section"><div class="container"><h1>Page not found.</h1><a href="' . e(url('')) . '">Return home</a></div></section>'];
if (!isset($static[$path])) { http_response_code(404); }
render_start($title);
echo $body;
if ($path === '') {
    render_collaboration_sections();
}
if ($path === 'about') {
    render_about_sections();
}
if ($path === 'impact') {
    render_impact_sections();
}
if ($path === 'contact') {
    render_contact_sections();
}
render_end();
