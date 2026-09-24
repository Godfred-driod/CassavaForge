<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function uuid(): string
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
}

function svg_path(string $value): string
{
    return e(str_replace(['0 0 0-6', '0 0-6'], ['0 0 0 -6', '0 0 -6'], $value));
}

function url(string $path = ''): string
{
    $base = rtrim((string) app_config()['site_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function asset_url(?string $path): string
{
    if (!$path) {
        return url('assets/images/packaging.jpg');
    }
    return preg_match('#^(https?://|/)#', $path) ? $path : url($path);
}

function original_page_html(string $page): string
{
    $file = __DIR__ . '/../content/' . $page . '.html';
    $html = is_file($file) ? (string) file_get_contents($file) : '';
    $base = rtrim((string) app_config()['site_url'], '/');
    $html = str_replace('/images/', $base . '/assets/images/', $html);
    $routes = ['home' => '', 'about' => 'about', 'products' => 'products', 'impact' => 'impact', 'blog' => 'blog', 'contact' => 'contact', 'privacy' => 'privacy'];
    $html = preg_replace_callback('/<a\b([^>]*)>/i', static function (array $match) use ($routes): string {
        $attrs = $match[1];
        if (!preg_match('/\bdata-path="([^"]+)"/i', $attrs, $pathMatch)) {
            return $match[0];
        }
        $href = url($routes[$pathMatch[1]] ?? '');
        $attrs = preg_replace('/\s*data-path="[^"]*"/i', '', $attrs) ?? $attrs;
        $attrs = preg_replace('/\s*href="[^"]*"/i', '', $attrs) ?? $attrs;
        return '<a href="' . e($href) . '"' . $attrs . '>';
    }, $html) ?? $html;
    return preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
}

function save_upload(array $file, string $folder): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return null;
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    if (!isset($types[$mime])) {
        return null;
    }
    $directory = __DIR__ . '/../assets/uploads/' . preg_replace('/[^a-z0-9_-]/i', '', $folder);
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
        return null;
    }
    return 'assets/uploads/' . $folder . '/' . $filename;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), $token)) {
        http_response_code(419);
        exit('Invalid form token. Please go back and try again.');
    }
}

function flash(?string $message = null, string $type = 'success'): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        $_SESSION['flash_type'] = $type === 'error' ? 'error' : 'success';
        return null;
    }
    $value = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $value;
}

function take_flash(): ?array
{
    $message = trim((string) ($_SESSION['flash'] ?? ''));
    $type = ($_SESSION['flash_type'] ?? 'success') === 'error' ? 'error' : 'success';
    unset($_SESSION['flash'], $_SESSION['flash_type']);
    if ($message === '') {
        return null;
    }
    return ['message' => $message, 'type' => $type];
}

function login_is_locked(): bool
{
    return (int) ($_SESSION['login_lock_until'] ?? 0) > time();
}

function login_register_failure(): void
{
    $_SESSION['login_failures'] = (int) ($_SESSION['login_failures'] ?? 0) + 1;
    if ((int) $_SESSION['login_failures'] >= 5) {
        $_SESSION['login_lock_until'] = time() + 15 * 60;
        $_SESSION['login_failures'] = 0;
    }
}

function login_clear_failures(): void
{
    unset($_SESSION['login_failures'], $_SESSION['login_lock_until']);
}

function inquiry_topics(): array
{
    return [
        'general' => 'General inquiry',
        'sample' => 'Sample request',
        'partnership' => 'Partnership',
        'investor' => 'Investor',
    ];
}

function page_meta(?string $title = null, ?string $path = null): array
{
    $path = $path ?? (string) ($GLOBALS['currentPath'] ?? '');
    $descriptions = [
        '' => 'CassavaForge develops cassava-based bioplastic materials in Ghana for packaging, films, and molded products. Open for pilots and partners.',
        'about' => 'CassavaForge is a Ghana materials team turning cassava starch into practical bioplastic alternatives.',
        'products' => 'Explore cassava-based materials in development for packaging, films, and molded forms. Request a converting trial.',
        'impact' => 'How CassavaForge aims to support Ghanaian growers and reduce persistent plastic waste — with claims tied to evidence.',
        'blog' => 'Notes from CassavaForge on cassava materials, partnerships, and field work.',
        'contact' => 'Contact CassavaForge in Accra for samples, partnerships, or investor conversations.',
        'privacy' => 'How CassavaForge collects and uses contact messages and first-party visit data.',
        'portal' => 'CassavaForge partner portal — request samples, trials, or quotes and track them.',
        'signin' => 'Sign in to your CassavaForge account.',
        'portal/login' => 'Sign in to your CassavaForge account.',
        'portal/register' => 'Create a CassavaForge partner account to request and track material work.',
    ];
    $description = $descriptions[''];
    foreach ($descriptions as $key => $text) {
        if ($path === $key || ($key !== '' && str_starts_with($path, $key . '/'))) {
            $description = $text;
            break;
        }
    }
    $pageTitle = $title ?: 'CassavaForge';
    $site = (string) app_config()['site_name'];
    $fullTitle = $path === '' || $pageTitle === $site ? $site . ' | Cassava bioplastics from Ghana' : $pageTitle . ' | ' . $site;
    return [
        'title' => $fullTitle,
        'description' => $description,
        'canonical' => url($path === 'home' ? '' : $path),
        'image' => url('assets/images/home-background-1.jpg'),
    ];
}

function notify_new_inquiry(string $name, string $email, string $topic, string $message): void
{
    $to = trim((string) (app_config()['admin_email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $host = parse_url((string) app_config()['site_url'], PHP_URL_HOST) ?: 'cassavaforge.test';
    $subject = 'CassavaForge inquiry (' . $topic . ') from ' . $name;
    $body = "Name: {$name}\nEmail: {$email}\nType: {$topic}\n\n{$message}\n";
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: CassavaForge <noreply@' . $host . '>',
        'Reply-To: ' . $email,
        'X-Mailer: CassavaForge',
    ]);
    $smtp = (string) (ini_get('SMTP') ?: '127.0.0.1');
    $port = (int) (ini_get('smtp_port') ?: 25);
    $errno = 0;
    $errstr = '';
    $socket = @fsockopen($smtp, $port, $errno, $errstr, 0.4);
    if (!is_resource($socket)) {
        return;
    }
    fclose($socket);
    $previousTimeout = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', '2');
    try {
        @mail($to, $subject, $body, $headers);
    } catch (Throwable $error) {
        error_log('inquiry mail failed: ' . $error->getMessage());
    } finally {
        if ($previousTimeout !== false) {
            ini_set('default_socket_timeout', (string) $previousTimeout);
        }
    }
}

function sitemap_xml(): string
{
    $urls = ['', 'about', 'products', 'impact', 'blog', 'contact', 'privacy'];
    $products = safe_query(fn() => db()->query("SELECT slug, updated_at FROM products WHERE published = 1")->fetchAll(), []);
    $posts = safe_query(fn() => db()->query("SELECT slug, updated_at FROM blog_posts WHERE published = 1")->fetchAll(), []);
    foreach ($products as $product) {
        $urls[] = 'products/' . $product['slug'];
    }
    foreach ($posts as $post) {
        $urls[] = 'blog/' . $post['slug'];
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $path) {
        $xml .= '  <url><loc>' . e(url((string) $path)) . '</loc></url>' . "\n";
    }
    $xml .= '</urlset>';
    return $xml;
}

function robots_txt(): string
{
    $adminPath = parse_url(url('admin'), PHP_URL_PATH) ?: '/admin';
    $portalPath = parse_url(url('portal'), PHP_URL_PATH) ?: '/portal';
    $signinPath = parse_url(url('signin'), PHP_URL_PATH) ?: '/signin';
    return "User-agent: *\nAllow: /\nDisallow: {$adminPath}\nDisallow: {$portalPath}\nDisallow: {$signinPath}\nSitemap: " . url('sitemap.xml') . "\n";
}
