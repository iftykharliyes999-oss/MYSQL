<?php
require_once __DIR__ . '/../config/config.php';
requireRole('staff');
$pdo = (new Database())->connect();

$stats = [
  'today_checkins'  => $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in_date=CURDATE() AND status IN ('confirmed','checked_in')")->fetchColumn(),
  'today_checkouts' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_out_date=CURDATE() AND status='checked_in'")->fetchColumn(),
  'pending_tasks'   => $pdo->prepare("SELECT COUNT(*) FROM housekeeping WHERE staff_id=? AND status!='done'"),
  'available_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn(),
];
$stats['pending_tasks']->execute([$_SESSION['user_id']]);
$pendingCount = $stats['pending_tasks']->fetchColumn();

$pageTitle='Staff Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_staff.php'; ?>
<div class="main-content">
  <h3 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-1 me-3"><i class="fa-solid fa-right-to-bracket"></i></div>
      <div><small class="text-muted">Today's Check-ins</small><h4><?= $stats['today_checkins'] ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-2 me-3"><i class="fa-solid fa-right-from-bracket"></i></div>
      <div><small class="text-muted">Today's Check-outs</small><h4><?= $stats['today_checkouts'] ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-3 me-3"><i class="fa-solid fa-broom"></i></div>
      <div><small class="text-muted">My Pending Tasks</small><h4><?= $pendingCount ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-4 me-3"><i class="fa-solid fa-bed"></i></div>
      <div><small class="text-muted">Available Rooms</small><h4><?= $stats['available_rooms'] ?></h4></div>
    </div></div></div>
  </div>
  <div class="alert alert-info">
    Use the sidebar to manage <a href="bookings.php">bookings</a>, perform <a href="checkin.php">check-ins/outs</a>,
    and complete <a href="housekeeping.php">housekeeping tasks</a>.
  </div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
