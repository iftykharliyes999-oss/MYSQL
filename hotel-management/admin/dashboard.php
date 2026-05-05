<?php
require_once __DIR__ . '/../config/config.php';
requireRole('admin');
$pdo = (new Database())->connect();

$stats = [
  'users'    => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
  'rooms'    => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
  'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
  'revenue'  => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='paid'")->fetchColumn(),
];
$recent = $pdo->query("SELECT b.*, u.name AS uname, r.room_number FROM bookings b
    JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
    ORDER BY b.created_at DESC LIMIT 8")->fetchAll();
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <h3 class="mb-4">Dashboard Overview</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-1 me-3"><i class="fa-solid fa-users"></i></div>
      <div><small class="text-muted">Total Users</small><h4 class="mb-0"><?= $stats['users'] ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-2 me-3"><i class="fa-solid fa-bed"></i></div>
      <div><small class="text-muted">Total Rooms</small><h4 class="mb-0"><?= $stats['rooms'] ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-3 me-3"><i class="fa-solid fa-calendar-check"></i></div>
      <div><small class="text-muted">Bookings</small><h4 class="mb-0"><?= $stats['bookings'] ?></h4></div>
    </div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-4 me-3"><i class="fa-solid fa-money-bill-trend-up"></i></div>
      <div><small class="text-muted">Revenue</small><h5 class="mb-0"><?= formatCurrency($stats['revenue']) ?></h5></div>
    </div></div></div>
  </div>

  <div class="card">
    <div class="card-header"><strong>Recent Bookings</strong></div>
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>#</th><th>Customer</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $b): ?>
          <tr>
            <td>#<?= $b['id'] ?></td>
            <td><?= htmlspecialchars($b['uname']) ?></td>
            <td><?= htmlspecialchars($b['room_number']) ?></td>
            <td><?= formatDate($b['check_in_date']) ?></td>
            <td><?= formatDate($b['check_out_date']) ?></td>
            <td><?= formatCurrency($b['total_amount']) ?></td>
            <td><?= statusBadge($b['status']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$recent): ?><tr><td colspan="7" class="text-center text-muted">No bookings yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
