<?php
require_once __DIR__ . '/../config/config.php';
requireRole('customer');
$pdo=(new Database())->connect();

$stats=[
  'total'  => $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=?"),
  'active' => $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status IN ('confirmed','checked_in','pending')"),
  'spent'  => $pdo->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN bookings b ON b.id=p.booking_id WHERE b.user_id=? AND p.payment_status='paid'"),
];
foreach ($stats as $k=>$s) { $s->execute([$_SESSION['user_id']]); $stats[$k]=$s->fetchColumn(); }

$recent=$pdo->prepare("SELECT b.*, r.room_number, r.room_type FROM bookings b
    JOIN rooms r ON r.id=b.room_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5");
$recent->execute([$_SESSION['user_id']]); $recent=$recent->fetchAll();

$pageTitle='My Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_customer.php'; ?>
<div class="main-content">
  <h3>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h3>
  <div class="row g-3 my-3">
    <div class="col-md-4"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-1 me-3"><i class="fa-solid fa-calendar"></i></div>
      <div><small class="text-muted">Total Bookings</small><h4><?= $stats['total'] ?></h4></div>
    </div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-2 me-3"><i class="fa-solid fa-bed"></i></div>
      <div><small class="text-muted">Active</small><h4><?= $stats['active'] ?></h4></div>
    </div></div></div>
    <div class="col-md-4"><div class="card stat-card p-3"><div class="d-flex">
      <div class="icon bg-3 me-3"><i class="fa-solid fa-money-bill"></i></div>
      <div><small class="text-muted">Total Paid</small><h5><?= formatCurrency($stats['spent']) ?></h5></div>
    </div></div></div>
  </div>
  <div class="card"><div class="card-header"><strong>Recent Bookings</strong></div>
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>#</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Amount</th><th>Status</th><th>Invoice</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $b): ?>
      <tr><td>#<?= $b['id'] ?></td><td><?= htmlspecialchars($b['room_number']) ?> (<?= $b['room_type'] ?>)</td>
        <td><?= formatDate($b['check_in_date']) ?></td><td><?= formatDate($b['check_out_date']) ?></td>
        <td><?= formatCurrency($b['total_amount']) ?></td><td><?= statusBadge($b['status']) ?></td>
        <td><a href="<?= BASE_URL ?>/admin/invoice.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-file-invoice"></i></a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="7" class="text-center text-muted">No bookings yet. <a href="book_room.php">Book now!</a></td></tr><?php endif; ?>
    </tbody>
  </table></div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
