# Shared-hosting configuration

Copy `config.local.php.example` to `config.local.php` on the server and fill in Hostinger values. That file is gitignored and overrides `config.php`. Do not upload the Laragon `config.local.php`.

## Hostinger values

- `site_url`: `https://your-domain.com` with no trailing slash
- `db.host`: `localhost`
- `db.name`, `db.user`, `db.password`: from hPanel → Databases (they are prefixed, for example `u123456789_cassava`)
- `admin_email`: owner login email
- `admin_password_hash`: output of

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
```

The first request after import creates the owner account if `users` is empty.

## Namecheap domain → Hostinger

1. In Hostinger hPanel, add the domain to the website (or use Connect domain).
2. Copy the two nameservers from Plan details or the connect-domain screen.
3. In Namecheap: Domain List → Manage → Nameservers → Custom DNS. Paste both Hostinger nameservers and save.
4. Wait for DNS (often minutes, up to 24 hours).
5. In hPanel, enable free SSL for the domain.
