# CassavaForge PHP

CassavaForge is a server-rendered PHP website for shared hosting. It uses PHP 8.1+, MySQL 8 or MariaDB, PDO, Bootstrap 5, custom CSS, and standard HTML. It does not require Node.js, Next.js, Supabase, TypeScript, React, or a build step.

## Local setup

1. Create a MySQL database named `cassavaforge`.
2. Import `schema.sql`, then `migrations/2026_02_accounts_orders.sql`, then `seed.sql`.
3. Copy `config/config.local.php.example` to `config/config.local.php` and set local values.
4. Generate an admin password hash:

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
```

5. Put the hash in `admin_password_hash` and open `http://localhost/cassavaforge/`.

## Hostinger + Namecheap

Use an empty PHP website (not WordPress). Upload the project files into `public_html` so `index.php` and `.htaccess` sit in that folder.

### 1. Point the Namecheap domain at Hostinger

1. In Hostinger, add the domain to the hosting plan and copy the nameservers from Plan details or Connect domain.
2. In Namecheap: Domain List → Manage → Nameservers → Custom DNS. Enter both Hostinger nameservers and save.
3. Wait for DNS, then turn on free SSL in hPanel.

If the domain’s email stays at Namecheap, copy the Namecheap MX (and any SPF/DKIM) records into Hostinger DNS after the nameserver change. Otherwise mail will stop until those records exist on Hostinger.

### 2. Database

1. In hPanel → Databases, create a MySQL database and user. Grant the user all privileges.
2. Open phpMyAdmin, select that database, and import `install/hostinger.sql` once. Do not import `schema.sql` on Hostinger (it tries to create a local database named `cassavaforge`).

### 3. PHP and files

1. Set PHP to 8.2 or 8.3 in hPanel.
2. Upload the site into `public_html`. Skip `.git`, `config/config.local.php`, and any local zip leftovers.
3. In File Manager, copy `config/config.local.php.example` to `config/config.local.php`.
4. Edit `config.local.php` with `https://your-domain.com`, the Hostinger database name/user/password, the owner email, and the password hash.
5. Make `assets/uploads` writable (`755` or `775`).

### 4. Check

- `https://your-domain.com/` loads the public site
- `https://your-domain.com/signin` signs in the owner and opens `/admin`
- Products, journal, contact, and an image upload all work

Sign-in is `/signin` for staff and partners. The old `/admin/login` address redirects there.

## Main routes

- `/`, `/about`, `/products`, `/impact`, `/blog`, `/contact`
- `/signin`, `/admin`, `/admin/products`, `/admin/blog`, `/admin/messages`, `/admin/orders`
- `/portal`, `/portal/register`
