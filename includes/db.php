<?php
declare(strict_types=1);

function app_config(): array
{
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/../config/config.php';
        $local = __DIR__ . '/../config/config.local.php';
        if (is_file($local)) {
            $config = array_replace_recursive($config, require $local);
        }
        $envUrl = getenv('SITE_URL');
        if (is_string($envUrl) && $envUrl !== '') {
            $config['site_url'] = $envUrl;
        } else {
            $detected = request_base_url();
            if ($detected !== null) {
                $config['site_url'] = $detected;
            }
        }
    }
    return $config;
}

function request_base_url(): ?string
{
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return null;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = rtrim(dirname($script), '/');
    if ($dir === '' || $dir === '.' || $dir === '\\') {
        $dir = '';
    }
    return $scheme . '://' . $host . $dir;
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config()['db'];
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['port'], $config['name'], $config['charset']);
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
