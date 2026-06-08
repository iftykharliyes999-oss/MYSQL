# 🏨 Aurora Grand Hotel — Hotel Management System

Premium hotel management system built with **PHP 8 + MySQL** using PDO prepared statements, RBAC, CSRF protection, and a luxury Midnight Navy + Gold UI.

## 🚀 Quick Start (XAMPP / WAMP / LAMP)

1. **Copy the project** into your web root:
   - XAMPP (Windows): `C:\xampp\htdocs\aurora-hms`
   - WAMP: `C:\wamp64\www\aurora-hms`
   - LAMP (Linux/Mac): `/var/www/html/aurora-hms`

2. **Start Apache + MySQL** from the XAMPP control panel.

3. **Import the database**:
   - Open phpMyAdmin → click **Import** → choose `database.sql` → Go.
   - This creates the `aurora_hms` database with **15 tables** + seed data.

4. **Configure DB credentials** in `config/db.php` (default works for XAMPP):
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'aurora_hms');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Open in browser**: http://localhost/aurora-hms/

## 🔑 Demo Logins

| Role     | Email              | Password   |
|----------|--------------------|------------|
| Admin    | admin@aurora.com   | admin123   |
| Staff    | sarah@aurora.com   | staff123   |
| Customer | guest@aurora.com   | guest123   |

## 📂 Folder Structure

```
aurora-hms/
├── index.php              ← Public landing page (hotel showcase)
├── rooms.php              ← Browse rooms with filters
├── room.php               ← Room details + book
├── about.php  contact.php unauthorized.php
├── database.sql           ← Full 15-table MySQL schema + seed
├── config/
│   └── db.php             ← MySQL connection (edit credentials here)
├── includes/
│   ├── security.php       ← Session, CSRF, RBAC, sanitisation
│   ├── header.php  footer.php       ← Public layout
│   ├── dash-header.php  dash-footer.php  ← Dashboard layout
│   └── sidebar.php        ← Role-based sidebar nav
├── auth/
│   ├── login.php  register.php  logout.php
├── admin/
│   ├── dashboard.php      ← KPI + Chart.js graphs
│   ├── rooms.php          ← Add/Edit/Delete rooms
│   ├── bookings.php       ← All bookings + status change
│   ├── staff.php          ← Manage staff
│   ├── customers.php  payments.php  reports.php
├── staff/
│   ├── dashboard.php  housekeeping.php  bookings.php (check-in/out)
├── customer/
│   ├── dashboard.php  booking.php  my-bookings.php  profile.php  invoice.php
└── assets/
    ├── css/style.css      ← Premium design system
    └── images/            ← 8 luxury hotel images
```

## 🔐 Security Features

- **PDO prepared statements** everywhere → SQL injection proof
- **password_hash() / password_verify()** (bcrypt cost 12)
- **CSRF tokens** on every POST form
- **htmlspecialchars()** XSS guard on every output (`e()` helper)
- **Session regeneration** on login (fixation guard)
- **HttpOnly + SameSite** cookies
- **Role-based access control** via `require_role()`
- **Audit logging** of every login/logout/create/update

## 🎨 Design System

- **Colors**: Midnight Navy (#0b1426) + Gold (#d4a857) + Soft Slate (#f7f6f1)
- **Fonts**: Playfair Display (headings) + Inter (body)
- **Icons**: Font Awesome 6
- **Charts**: Chart.js (Revenue, Booking Status, Reports)

## ⚙️ Features by Role

### Admin
- Live KPI dashboard (rooms, bookings, revenue, occupancy)
- Revenue + booking-status charts (last 6 months)
- Full CRUD on rooms, staff
- Booking status changer (pending → confirmed → checked-in → checked-out → cancelled)
- Customer list, payments ledger, analytics reports

### Staff
- Today check-in / check-out counts
- 15-room housekeeping grid (clean / dirty / maintenance)
- Auto-generates invoice on check-out

### Customer
- Browse rooms with **type + price filters**
- 3-step booking flow → payment (Card / bKash / Cash) → invoice
- My Bookings (cancel pending, view/print invoice)
- Profile editor + password change

## 🖨️ Printable Invoices

Every booking auto-generates a styled invoice (`customer/invoice.php?id=…`) — opens in browser and prints to PDF via `window.print()`.

## 📞 Support

If `index.php` shows "Database connection failed":
- Verify MySQL is running.
- Re-import `database.sql`.
- Check credentials in `config/db.php`.

Enjoy your luxury hotel management system! 🥂
