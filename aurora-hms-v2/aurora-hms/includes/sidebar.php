<?php
// Dashboard sidebar — used by admin/staff/customer
$role = current_user()['role'] ?? '';
$menu = [
  'admin' => [
    ['admin/dashboard.php', 'fa-gauge-high', 'Dashboard'],
    ['admin/rooms.php', 'fa-bed', 'Rooms'],
    ['admin/room-types.php', 'fa-images', 'Room Types'],
    ['admin/bookings.php', 'fa-calendar-check', 'Bookings'],
    ['admin/messages.php', 'fa-comments', 'Messages'],
    ['admin/notifications.php', 'fa-bell', 'Notifications'],
    ['admin/staff.php', 'fa-users-gear', 'Staff'],
    ['admin/customers.php', 'fa-users', 'Customers'],
    ['admin/payments.php', 'fa-money-bill-wave', 'Payments'],
    ['admin/reports.php', 'fa-chart-line', 'Reports'],
  ],
  'staff' => [
    ['staff/dashboard.php', 'fa-gauge-high', 'Dashboard'],
    ['staff/housekeeping.php', 'fa-broom', 'Housekeeping'],
    ['staff/bookings.php', 'fa-calendar-check', 'Check-in/out'],
  ],
  'customer' => [
    ['customer/dashboard.php', 'fa-gauge-high', 'Dashboard'],
    ['rooms.php', 'fa-bed', 'Browse Rooms'],
    ['customer/my-bookings.php', 'fa-calendar', 'My Bookings'],
    ['customer/messages.php', 'fa-comments', 'Messages'],
    ['customer/notifications.php', 'fa-bell', 'Notifications'],
    ['customer/profile.php', 'fa-user', 'Profile'],
  ],
];
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="sidebar">
  <div class="side-brand"><i class="fa-solid fa-hotel"></i> <?= e(HOTEL_NAME) ?></div>
  <nav>
    <?php foreach (($menu[$role] ?? []) as [$href,$icon,$label]):
      $active = str_ends_with($href, $current) ? 'active' : ''; ?>
      <a class="<?= $active ?>" href="<?= base_url($href) ?>"><i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?></a>
    <?php endforeach; ?>
    <a href="<?= base_url('index.php') ?>"><i class="fa-solid fa-house"></i> Public Site</a>
    <a href="<?= base_url('auth/logout.php') ?>" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </nav>
</aside>
