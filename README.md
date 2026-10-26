# ML Command Center — Portfolio Website

A full-stack PHP + MySQL portfolio for a Machine Learning Engineer, styled as an
"AI Operations Dashboard" instead of a normal stacked portfolio.

## Stack
- PHP 8+ (PDO, prepared statements only)
- MySQL / MariaDB
- Vanilla HTML5 / CSS3 / JavaScript (no framework — fast load, full control)

## Folder structure
```
ml_command_center/
├── admin/              Admin panel (login-protected)
│   ├── includes/        auth guard + shared layout
│   ├── dashboard.php    stats overview
│   ├── projects.php     Project Vault CRUD (images + video)
│   ├── skills.php       Skills Matrix CRUD
│   ├── experience.php   Experience Timeline CRUD
│   ├── certificates.php Certifications CRUD (image + PDF)\n│   ├── research.php    Research paper CRUD (PDF + optional external link)\n│   ├── private_notes.php Password-protected private PDF notes
│   ├── resume.php       Resume upload
│   ├── settings.php     About / contact info / links + profile photo
│   └── messages.php     Contact Hub inbox
├── assets/css/style.css Dark glassmorphism theme
├── assets/js/main.js    Particles, counters, dock nav, terminal, modal, constellation
├── config/db.php        DB credentials (EDIT THIS)
├── includes/            functions.php, header.php, footer.php
├── uploads/              project images/video, certificates, resume (write-protected from PHP execution)
├── index.php             the public one-page dashboard site
├── contact_submit.php    AJAX contact form handler
├── download_resume.php   resume download + counter
└── database.sql          full schema + seed data
```

## 1. Requirements
- PHP 8.0+ with the `pdo_mysql`, `fileinfo`, and `mbstring` extensions enabled
- MySQL 5.7+/8+ or MariaDB
- A local server stack: **XAMPP**, **MAMP**, **Laragon**, or plain `php -S`

## 2. Run it locally (fastest way — PHP's built-in server)

1. Install PHP 8+ if you don't have it (php.net, or `brew install php` / `sudo apt install php php-mysql php-sqlite3 php-mbstring`).
2. Install MySQL/MariaDB and start it.
3. Create the database:
   ```bash
   mysql -u root -p < database.sql
   ```
   This creates the `ml_command_center` database, all tables, seed skills, and a
   default admin account.
4. Open `config/db.php` and set your DB username/password (defaults to
   `root` / empty password, which matches a fresh XAMPP/MAMP install).
5. From the `ml_command_center/` folder, start PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
6. Visit:
   - Public site: **http://localhost:8000**
   - Admin panel: **http://localhost:8000/admin/login.php**

## 2b. Run it with XAMPP/MAMP/Laragon instead
1. Copy the whole `ml_command_center/` folder into `htdocs` (XAMPP) or `www` (MAMP/Laragon).
2. Start Apache + MySQL from the control panel.
3. Import `database.sql` via phpMyAdmin (or the `mysql` CLI command above).
4. Edit `config/db.php` with your DB credentials.
5. Visit `http://localhost/ml_command_center/`.

## 3. Default admin login
```
Username: admin
Password: ChangeMe123!
```
**Change this immediately.** Easiest way: log in, then run this once via the
MySQL CLI or phpMyAdmin (replace `NEW_PASSWORD`):
```php
<?php echo password_hash('NEW_PASSWORD', PASSWORD_DEFAULT); ?>
```
Then:
```sql
UPDATE admin_users SET password_hash = '<paste hash here>' WHERE username = 'admin';
```

## 4. First steps after installing
1. Log into `/admin/`.
2. Go to **Site Info** → fill in your real name, tagline, about text, GitHub/LinkedIn, email, phone, location.
3. Go to **Projects** → add your real projects (thumbnail + optional gallery + optional MP4 demo).
4. Go to **Skills**, **Experience**, **Certificates**, **Resume** → fill in your real data.
5. Refresh the public site — everything on the dashboard (module counts, skills matrix, constellation, timeline, terminal mode data) is pulled live from the database.

## 5. Security features already built in
- PDO prepared statements everywhere (no raw SQL concatenation)
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- CSRF tokens on every admin form + the login form
- Output escaped with `htmlspecialchars()` (XSS protection)
- Session hardened (httponly cookies, regenerated ID on login, 30-min idle timeout, basic login throttling)
- Upload validation: extension allow-list **and** real MIME-type check via `finfo`, per-file size limits, randomized filenames
- `uploads/.htaccess` blocks script execution in uploads; `uploads/private_notes/.htaccess` denies direct access to private PDFs, which are streamed only after password verification
- Contact form has server-side validation + basic per-session rate limiting

## 6. Notes on the "Command Center" UI
- The **Command Dock** (bottom nav) smooth-scrolls between the one-page sections and highlights the active section as you scroll.
- **Terminal Mode** (top-right `>_` button) is a fake CLI — type `help`, `skills`, `projects`, `experience`, `certifications`, `resume`, `contact`, or `clear`. It reads live data injected from PHP via `window.SITE_DATA`.
- **Tech Constellation** is a plain SVG network built client-side from your skills data — no charting library needed.
- **Data Stream background** is a lightweight `<canvas>` particle system (no external libraries).
- Everything degrades gracefully on mobile: the orbiting hero modules become a simple stat grid, and the dock nav becomes a horizontally scrollable bar.

## 7. Going to production later
- Set real DB credentials and a strong DB user (not root) in `config/db.php`.
- For a fresh checkout, copy `config/db.example.php` to `config/db.php`; the
  database settings can also be supplied with `DB_HOST`, `DB_NAME`, `DB_USER`,
  and `DB_PASS` environment variables.
- Serve over HTTPS so the `session.cookie_secure` flag applies.
- Increase `upload_max_filesize` / `post_max_size` in `php.ini` if you want larger demo videos.
- Consider moving `uploads/` outside the web root if your host allows serving files via a PHP script instead of direct links (extra hardening, optional).

## 8. GitHub repository
The source code is available at:
https://github.com/Rayhan043/Portfolio_Website

GitHub stores the source repository but does not run PHP or MySQL. To make the
portfolio live, deploy these files to a PHP/MySQL host such as XAMPP, shared
hosting, or a PHP platform, then configure the database and import
`database.sql`. Private notes and local resume uploads are intentionally
excluded from the repository.

## 9. Updating an already-hosted FreeProHost site
1. Back up the current files and database first.
2. Upload the contents of the ZIP's `htdocs/` folder to your hosting document root, replacing the old files but keeping your existing `config/db.php` credentials.
3. In phpMyAdmin, select the same database used by this site and import `database_migration_research_profile_notes.sql` once. Do not import the full `database.sql` into an already-populated production database unless you know how to handle existing tables and seed rows.
4. Make sure `uploads/profile/`, `uploads/research/`, and `uploads/private_notes/` can be created/written by PHP. Keep `uploads/private_notes/.htaccess` in place.
5. Check the host's PHP upload limits (`upload_max_filesize`, `post_max_size`) and ensure the server supports PHP 8+, PDO MySQL, and `fileinfo`.
6. Test admin login, the existing sections, a research PDF, profile-photo removal, and a private note using an incorrect and then correct password.

**Important:** This package can be syntax-checked, but live operation still depends on your host's database credentials, schema migration, PHP version, upload limits and `.htaccess` support. Never publish your private-note PDFs anywhere outside the protected folder.
