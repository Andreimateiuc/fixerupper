# FixerUpper Deployment Guide

## GitHub

The project is a normal PHP/MySQL website. Commit it from the project root:

```powershell
git add .
git commit -m "Update deployment files"
```

Create a new empty GitHub repository named `fixerupper`, then connect and push:

```powershell
git remote add origin https://github.com/Andreimateiuc/fixerupper.git
git push -u origin main
```

## Important: Vercel

Vercel is not a good target for this version because the project uses classic PHP sessions and MySQL through PDO. Vercel does not run a normal PHP/Apache application like XAMPP/cPanel.

For this assignment, use a PHP/MySQL host instead:

- InfinityFree
- AwardSpace
- Hostinger/cPanel
- any university PHP/MySQL server

## PHP/MySQL Hosting Steps

1. Upload the project files into `public_html` or the hosting web root.
2. Create a MySQL database in the hosting control panel.
3. Import `sql/schema.sql` using phpMyAdmin.
4. Edit `db.php` with the hosting database host, database name, username, and password.
5. Visit the site URL in the browser.

## InfinityFree Steps

1. Log in to InfinityFree.
2. Create a free hosting account/site.
3. Open the site control panel.
4. Go to **MySQL Databases** and create a database.
5. Copy these values from InfinityFree:
   - MySQL hostname
   - MySQL database name
   - MySQL username
   - MySQL password
6. Open **phpMyAdmin** from InfinityFree and import `sql/schema.sql`.
7. Open `db.php` and replace the local XAMPP values:

```php
$dbHost = 'INFINITYFREE_MYSQL_HOST';
$dbName = 'INFINITYFREE_DATABASE_NAME';
$dbUser = 'INFINITYFREE_DATABASE_USER';
$dbPass = 'INFINITYFREE_DATABASE_PASSWORD';
```

8. Upload the contents of the deployment ZIP into the InfinityFree `htdocs` folder.
9. Visit the InfinityFree website URL.

Do not upload the whole parent folder if it creates `htdocs/fixerupper/index.php`. The files such as `index.php`, `login.php`, and `assets/` should be directly inside `htdocs`.

## Security Deployment Notes

- Do not commit real hosting passwords. Keep `db.php` generic before pushing to GitHub.
- Use HTTPS in production so Secure session cookies are active.
- The included `.htaccess` blocks direct browser access to `runtime/`, `storage/`, and `sql/`.
- The site does not collect or store card/payment details.
