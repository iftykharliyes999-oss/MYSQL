<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('staff');
$pageTitle = 'Staff Dashboard';
$kpi = [
  'dirty' => (int)db()->query("SELECT COUNT(*) FROM rooms WHERE status='dirty'")->fetchColumn(),
  'maint' => (int)db()->query("SELECT COUNT(*) FROM rooms WHERE status='maintenance'")->fetchColumn(),
  'checkin' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed' AND check_in=CURDATE()")->fetchColumn(),
  'checkout' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='checked-in' AND check_out=CURDATE()")->fetchColumn(),
];
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="grid grid-4">
  <div class="kpi"><div class="label">Rooms to Clean</div><div class="value"><?= $kpi['dirty'] ?></div><i class="fa-solid fa-broom"></i></div>
  <div class="kpi"><div class="label">Under Maintenance</div><div class="value"><?= $kpi['maint'] ?></div><i class="fa-solid fa-screwdriver-wrench"></i></div>
  <div class="kpi"><div class="label">Today Check-ins</div><div class="value"><?= $kpi['checkin'] ?></div><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
  <div class="kpi"><div class="label">Today Check-outs</div><div class="value"><?= $kpi['checkout'] ?></div><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
</div>
<div class="grid grid-2" style="margin-top:24px">
  <a href="<?= base_url('staff/housekeeping.php') ?>" class="card" style="padding:30px;text-align:center;text-decoration:none"><i class="fa-solid fa-broom" style="font-size:2.4rem;color:var(--gold)"></i><h3 style="margin-top:14px">Manage Housekeeping</h3><p class="muted" style="margin-top:6px">Update room cleaning status</p></a>
  <a href="<?= base_url('staff/bookings.php') ?>" class="card" style="padding:30px;text-align:center;text-decoration:none"><i class="fa-solid fa-calendar-check" style="font-size:2.4rem;color:var(--gold)"></i><h3 style="margin-top:14px">Check-in / Check-out</h3><p class="muted" style="margin-top:6px">Process guest arrivals & departures</p></a>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
