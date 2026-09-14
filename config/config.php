<?php
declare(strict_types=1);

return [
    'site_name' => 'CassavaForge',
    'site_url' => getenv('SITE_URL') ?: 'http://localhost:8000',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'cassavaforge',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
    'admin_password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '$2y$12$E0hr2NGbGasVpcWIGQGT9e5ZmJH8c3BbI6J36XI5fdrlaDKt1co3G',
];
