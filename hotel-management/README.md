# 🏨 Grand Royal Hotel - Management System

A full-featured Hotel Management System built with **PHP 7.4+**, **MySQL**, **Bootstrap 5**.

## ✨ Features

| Module | Description |
|--------|-------------|
| **Roles** | Admin · Staff · Customer (separate panels & permissions) |
| **Auth** | Secure login, registration, password change (bcrypt + CSRF) |
| **Rooms** | Add/edit/delete, types (single/double/suite/deluxe), price, image, status |
| **Bookings** | Online booking, availability check, status workflow, cancellation |
| **Check-In/Out** | Staff workflow, auto room status sync, auto cleaning task |
| **Payments** | Cash / Card, Paid / Due / Partial, transaction reference |
| **Invoices** | Print/PDF-ready invoices with payment history |
| **Housekeeping** | Task assignment (cleaning/maintenance/inspection), status tracking |
| **Reports** | Daily & monthly income, occupancy, booking statistics |
| **Public Site** | Home, Rooms, About, Contact, responsive Bootstrap UI |

## 📂 Project Structure

```
hotel-management/
├── config/
│   ├── config.php          # Global config (constants, session, includes)
│   └── database.php        # PDO MySQL connection
├── includes/
│   ├── auth.php            # Authentication & role helpers
│   ├── functions.php       # Helpers (sanitize, csrf, formatters, availability)
│   ├── header.php          # Common <head> + Bootstrap/FA
│   ├── footer.php          # Common footer + JS
│   ├── navbar_public.php   # Public-site navbar
│   ├── sidebar_admin.php   # Admin sidebar
│   ├── sidebar_staff.php   # Staff sidebar
│   └── sidebar_customer.php
├── public/
│   ├── index.php           # Landing page
│   ├── rooms.php           # Public rooms
│   ├── about.php
│   ├── contact.php
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── rooms.php
│   ├── bookings.php
│   ├── payments.php
│   ├── housekeeping.php
│   ├── reports.php
│   └── invoice.php         # Shared invoice page (admin/staff/customer)
├── staff/
│   ├── dashboard.php
│   ├── bookings.php
│   ├── checkin.php
│   └── housekeeping.php
├── customer/
│   ├── dashboard.php
│   ├── book_room.php
│   ├── my_bookings.php
│   └── profile.php
├── assets/
│   ├── css/style.css
│   └── js/script.js
├── uploads/rooms/          # Uploaded room images (writable)
├── database/
│   └── schema.sql          # Database schema + seed data
├── index.php               # Redirects to /public
└── README.md
```

## 🚀 Installation (XAMPP / WAMP / LAMP)

1. **Copy** the `hotel-management/` folder into your web server document root
   (e.g. `C:\xampp\htdocs\hotel-management` or `/var/www/html/hotel-management`).

2. **Start** Apache + MySQL from XAMPP control panel.

3. **Create the database**:
   - Open `http://localhost/phpmyadmin`
   - Click **Import** → choose `database/schema.sql` → **Go**.
   - This creates the `hotel_management` database and seeds 3 demo users + 6 rooms.

4. **Configure DB credentials** (if not default):
   - Edit `config/database.php`:
     ```php
     private $host = 'localhost';
     private $db_name = 'hotel_management';
     private $username = 'root';
     private $password = '';
     ```

5. **Set BASE_URL** in `config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/hotel-management');
   ```

6. **Make uploads writable**:
   ```bash
   chmod -R 775 uploads/
   ```

7. **Open** `http://localhost/hotel-management` in your browser.

## 🔑 Demo Logins

All demo passwords: **`password123`**

| Role | Email |
|------|-------|
| Admin | `admin@hotel.com` |
| Staff | `staff@hotel.com` |
| Customer | `customer@hotel.com` |

## 🛡️ Security

- ✅ **PDO prepared statements** (SQL injection safe)
- ✅ **Password hashing** with `password_hash` (bcrypt)
- ✅ **CSRF tokens** on all POST forms
- ✅ **Role-based access control** (`requireRole()`)
- ✅ **HTML escaping** (`htmlspecialchars` + `sanitize()`)
- ✅ **File upload validation** (whitelist extensions)
- ✅ **Session-based auth**

## 💼 Business Logic Highlights

- **Availability check** — `isRoomAvailable()` rejects overlapping bookings
- **Date validation** — past dates blocked, checkout > checkin
- **Auto room status sync** — `checked_in` → room=booked, `checked_out`/`cancelled` → room=available
- **Auto housekeeping** — cleaning task auto-created on checkout
- **Outstanding balance** — payments page shows only bookings with due amount
- **Self-cancel** — customers can cancel only their own pending/confirmed bookings
- **Self-protection** — admin cannot delete their own account

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+ (PDO MySQL)
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend:** Bootstrap 5.3, Font Awesome 6
- **No external dependencies** — pure PHP, no Composer required

## 📝 Notes

- Email/SMS notifications are stubbed (use SMTP/Twilio integration to enable).
- Payment is cash/card recorded manually — no payment gateway by design.
- Currency: Bangladeshi Taka (৳). Change in `formatCurrency()` if needed.

---

© Grand Royal Hotel · Built with ❤️
