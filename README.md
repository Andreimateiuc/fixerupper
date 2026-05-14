# FixerUpper

Secure e-commerce prototype for a hardware appliances shop, built for CMP214 Secure Website Development.

## Folder Structure

```text
fixerupper/
  assets/css/styles.css
  sql/schema.sql
  auth.php
  cart.php
  checkout.php
  confirm_order.php
  db.php
  footer.php
  functions.php
  header.php
  index.php
  login.php
  logout.php
  order_success.php
  register.php
```

## Setup with XAMPP or WAMP

1. Copy the `fixerupper` folder into your web server directory:
   - XAMPP: `htdocs/fixerupper`
   - WAMP: `www/fixerupper`
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `sql/schema.sql`.
4. Check database settings in `db.php`. Defaults are:
   - host: `127.0.0.1`
   - database: `fixerupper`
   - username: `root`
   - password: empty string
5. Open `http://localhost/fixerupper/index.php`.

## Security Features Demonstrated

- Password theft defence: `auth.php` stores passwords with PHP `password_hash(..., PASSWORD_BCRYPT)` and verifies them with `password_verify()`.
- SQL Injection defence: all database access uses PDO prepared statements with parameterised queries.
- Session hijacking/session fixation defence: `functions.php` configures HttpOnly cookies, SameSite cookies, HTTPS-only Secure cookies when served over HTTPS, a 30-minute inactivity timeout, and `auth.php` regenerates the session ID after login/register.
- XSS defence: dynamic output is escaped with `htmlspecialchars()` through the shared `e()` helper. Inputs are validated and sanitised server-side before use.
- CSRF defence: state-changing forms include CSRF tokens, even though this was beyond the base requirements.
- Payment safety: this prototype does not collect, process, or store payment card details.

## Notes for Review

Use HTTPS in production so the Secure cookie flag is active. On plain local HTTP, PHP cannot set a usable Secure session cookie, so the code enables the Secure flag automatically when the request is HTTPS.

Product photos are stored locally under `assets/img/products/` so the catalogue does not depend on placeholder image URLs.

For publishing instructions, see `DEPLOYMENT.md`.
