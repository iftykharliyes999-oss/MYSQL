# Aurora Grand · v2 Upgrade Notes

## কী যোগ হয়েছে

1. **Room ছবি Upload** — `admin/rooms.php` ও নতুন `admin/room-types.php` থেকে JPG/PNG/WEBP upload হবে। `uploads/rooms/` ফোল্ডারে সংরক্ষিত হয়।
2. **Public site sync** — Public `rooms.php` ও `room.php` Room Types টেবিল পড়ে। নতুন Type যোগ/edit করলে cover image সহ public site-এ দেখা যাবে।
3. **Online Booking Flow** — `customer/booking.php` এখন overlap-check করে available room খুঁজে, transaction-based booking + payment + invoice তৈরি করে।
4. **Notifications** — `notifications` টেবিল। Booking হলে admin-কে notify, customer-কেও confirmation। Topbar-এ bell icon + badge। `admin/notifications.php` ও `customer/notifications.php` page।
5. **Messaging/Chat** — `message_threads` + `messages` টেবিল। Customer থেকে chat thread, admin reply, real-time look। Contact form-ও এখানে আসবে।
6. **Admin Dashboard** — Live activity feed + open inquiries KPI।

## চালু করার ধাপ

```bash
# 1. Database migration (existing DB-তে নতুন টেবিল)
mysql -u root -p aurora_hms < migrations.sql

# 2. uploads ফোল্ডারে write permission
chmod -R 0775 uploads/

# 3. সব file আগের মত project-এ replace করে দিন
```

## নতুন/আপডেট হওয়া file list

- `migrations.sql` (নতুন — অবশ্যই run করুন)
- `includes/helpers.php` (নতুন)
- `includes/dash-header.php` (bell + msg icon)
- `includes/sidebar.php` (Room Types, Messages, Notifications)
- `admin/rooms.php` (image upload + edit modal)
- `admin/room-types.php` (নতুন — public categories manage)
- `admin/messages.php` (নতুন — chat reply)
- `admin/notifications.php` (নতুন)
- `admin/dashboard.php` (live activity)
- `customer/booking.php` (full flow + notify)
- `customer/messages.php` (নতুন)
- `customer/notifications.php` (নতুন)
- `rooms.php`, `room.php` (uploaded image support)
- `contact.php` (inquiry → DB + admin notify)

## Test credentials
- Admin: admin@aurora.com / admin123
- Customer: guest@aurora.com / guest123
