# Shared-hosting configuration

Set these values in `config.php` on the hosting account. Keep the file outside public version control if your host supports environment variables.

- `site_url`: full domain, for example `https://cassavaforge.com`
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`: cPanel MySQL credentials
- `ADMIN_EMAIL`: admin login email
- `ADMIN_PASSWORD_HASH`: output from `php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"`
