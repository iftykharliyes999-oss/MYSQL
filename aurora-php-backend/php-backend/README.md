# Aurora Grand · PHP + MySQL Backend

Production-ready raw PHP backend that mirrors the React frontend.

## Install

1. Import `database.sql` into MySQL/MariaDB.
2. Edit `config/db.php` with your DB credentials.
3. Drop the `php-backend/` folder into your web root (XAMPP `htdocs`, LAMP `www`, etc.).
4. Visit `http://localhost/php-backend/auth/login.php`.

## Demo logins

(seed passwords are stored as bcrypt — re-hash before production)

| Role     | Email              | Password   |
|----------|--------------------|------------|
| Admin    | admin@aurora.com   | admin123   |
| Staff    | staff@aurora.com   | staff123   |
| Customer | guest@aurora.com   | guest123   |

## Security

- All queries use **PDO prepared statements** (SQL-injection proof).
- `clean()` runs `htmlspecialchars()` on every input echoed back (XSS guard).
- `clean_email()` validates with `filter_var`.
- Sessions use `HttpOnly`, `SameSite=Strict`, and `session_regenerate_id` on login.
- CSRF tokens are issued per session and verified on every POST.
- Role guards (`require_role('admin')`) redirect non-matching roles to `unauthorized.php`.

## File layout

```
php-backend/
  config/db.php           PDO connection
  includes/security.php   Session + CSRF + RBAC helpers
  auth/{login,register,logout}.php
  admin/{dashboard,rooms,bookings,staff}.php
  staff/housekeeping.php
  customer/{rooms,booking,my-bookings,invoice}.php
  database.sql            15-table schema + seed
```