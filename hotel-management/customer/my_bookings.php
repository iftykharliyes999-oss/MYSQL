<?php
require_once __DIR__ . '/../config/config.php';
requireRole('customer');
$pdo=(new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $id=(int)$_POST['id'];
    // only allow cancel of own pending/confirmed bookings
    $stmt=$pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status IN ('pending','confirmed')");
    $stmt->execute([$id,$_SESSION['user_id']]);
    flash('mb','Booking cancelled.','success');
    redirect($_SERVER['PHP_SELF']);
}
$rows=$pdo->prepare("SELECT b.*, r.room_number, r.room_type FROM bookings b
    JOIN rooms r ON r.id=b.room_id WHERE b.user_id=? ORDER BY b.created_at DESC");
$rows->execute([$_SESSION['user_id']]); $rows=$rows->fetchAll();
$pageTitle='My Bookings';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_customer.php'; ?>
<div class="main-content">
  <h3>My Bookings</h3>
  <?= flash('mb') ?>
  <div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $b): ?>
      <tr>
        <td>#<?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['room_number']) ?> (<?= $b['room_type'] ?>)</td>
        <td><?= formatDate($b['check_in_date']) ?></td>
        <td><?= formatDate($b['check_out_date']) ?></td>
        <td><?= $b['total_nights'] ?></td>
        <td><?= formatCurrency($b['total_amount']) ?></td>
        <td><?= statusBadge($b['status']) ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/admin/invoice.php?booking_id=<?= $b['id'] ?>"><i class="fa fa-file-invoice"></i></a>
          <?php if (in_array($b['status'],['pending','confirmed'])): ?>
            <form method="POST" class="d-inline" onsubmit="return confirmDelete('Cancel this booking?')">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= $b['id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted">No bookings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
