# CassavaForge PHP

CassavaForge is a server-rendered PHP website for shared hosting. It uses PHP 8.1+, MySQL 8 or MariaDB, PDO, Bootstrap 5, custom CSS, and standard HTML. It does not require Node.js, Next.js, Supabase, TypeScript, React, or a build step.

## Local setup

1. Create a MySQL database named `cassavaforge`.
2. Import `schema.sql`, then import `seed.sql`.
3. Configure the credentials in `config/config.php`.
4. Generate an admin password hash:

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
```

5. Put the generated hash in `admin_password_hash` and start the site:

```bash
php -S localhost:8000 index.php
```

## cPanel shared hosting

1. Create a MySQL database and user in cPanel and grant the user all privileges.
2. Import `schema.sql` and `seed.sql` through phpMyAdmin.
3. Upload the PHP files into `public_html`.
4. Edit `config/config.php` with the cPanel database credentials, real domain, admin email, and password hash.
5. Ensure `assets/uploads` is writable by PHP, usually permission `755`.
6. Open `/admin/login` and verify the admin dashboard, uploads, products, posts, messages, and visitor count.

## Main routes

- `/`, `/about`, `/products`, `/impact`, `/blog`, `/contact`
- `/admin/login`, `/admin`, `/admin/products`, `/admin/blog`, `/admin/messages`

The contact form, visitor counter, admin sessions, CSRF validation, product/blog publishing, and image uploads are implemented with PHP and MySQL.
