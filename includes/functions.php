<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
    $html = str_replace(['/images/', 'href="#"'], [$base . '/assets/images/', 'href="/"'], $html);
    $html = preg_replace_callback('/<a([^>]*?)data-path="([^"]+)"([^>]*)>/i', static function (array $match): string {
        $routes = ['home' => '/', 'about' => '/about', 'products' => '/products', 'impact' => '/impact', 'blog' => '/blog', 'contact' => '/contact'];
        $href = $routes[$match[2]] ?? '/';
        return '<a' . $match[1] . 'href="' . $href . '"' . $match[3] . '>';
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

function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('admin/login');
    }
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $value = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $value;
}
