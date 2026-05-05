<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['admin','staff']);
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $id=(int)$_POST['id']; $status=$_POST['status'];
    $allowed=['pending','confirmed','checked_in','checked_out','cancelled'];
    if (in_array($status,$allowed)) {
        $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$status,$id]);
        // Sync room status
        $b = $pdo->prepare("SELECT room_id FROM bookings WHERE id=?");
        $b->execute([$id]); $row=$b->fetch();
        if ($status==='checked_in')   $pdo->prepare("UPDATE rooms SET status='booked' WHERE id=?")->execute([$row['room_id']]);
        if (in_array($status,['checked_out','cancelled'])) $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([$row['room_id']]);
        flash('b','Booking updated.','success');
    }
    redirect(BASE_URL . (currentUser()['role']==='admin'?'/admin':'/staff').'/bookings.php');
}
$rows=$pdo->query("SELECT b.*, u.name AS uname, u.phone, r.room_number, r.room_type
                   FROM bookings b JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
                   ORDER BY b.created_at DESC")->fetchAll();
$pageTitle='Bookings';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <h3>All Bookings</h3>
  <?= flash('b') ?>
  <div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $b): ?>
      <tr>
        <td>#<?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['uname']) ?></td>
        <td><?= htmlspecialchars($b['phone']) ?></td>
        <td><?= htmlspecialchars($b['room_number']) ?> <small class="text-muted text-capitalize">(<?= $b['room_type'] ?>)</small></td>
        <td><?= formatDate($b['check_in_date']) ?></td>
        <td><?= formatDate($b['check_out_date']) ?></td>
        <td><?= $b['total_nights'] ?></td>
        <td><?= formatCurrency($b['total_amount']) ?></td>
        <td><?= statusBadge($b['status']) ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/admin/invoice.php?booking_id=<?= $b['id'] ?>"><i class="fa fa-file-invoice"></i></a>
          <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#st<?= $b['id'] ?>"><i class="fa fa-edit"></i></button>
        </td>
      </tr>
      <div class="modal fade" id="st<?= $b['id'] ?>"><div class="modal-dialog"><div class="modal-content">
        <form method="POST">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $b['id'] ?>">
          <div class="modal-header"><h5>Update Status</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <select class="form-control" name="status">
              <?php foreach (['pending','confirmed','checked_in','checked_out','cancelled'] as $s): ?>
                <option <?= $b['status']==$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="modal-footer"><button class="btn btn-primary">Update</button></div>
        </form>
      </div></div></div>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="10" class="text-center text-muted">No bookings.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
