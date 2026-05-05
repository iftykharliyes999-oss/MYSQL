<?php
require_once __DIR__ . '/../config/config.php';
requireRole('staff');
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $id=(int)$_POST['booking_id']; $action=$_POST['action'];
    if ($action==='checkin') {
        $pdo->prepare("UPDATE bookings SET status='checked_in' WHERE id=?")->execute([$id]);
        $r=$pdo->prepare("SELECT room_id FROM bookings WHERE id=?"); $r->execute([$id]); $row=$r->fetch();
        $pdo->prepare("UPDATE rooms SET status='booked' WHERE id=?")->execute([$row['room_id']]);
        flash('c','Guest checked in.','success');
    } elseif ($action==='checkout') {
        $pdo->prepare("UPDATE bookings SET status='checked_out' WHERE id=?")->execute([$id]);
        $r=$pdo->prepare("SELECT room_id FROM bookings WHERE id=?"); $r->execute([$id]); $row=$r->fetch();
        $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([$row['room_id']]);
        // auto-create cleaning task
        $pdo->prepare("INSERT INTO housekeeping (room_id,staff_id,task_type,notes) VALUES (?,?,'cleaning','Auto-created on checkout')")
            ->execute([$row['room_id'],$_SESSION['user_id']]);
        flash('c','Guest checked out. Cleaning task created.','success');
    }
    redirect($_SERVER['PHP_SELF']);
}

$arrivals=$pdo->query("SELECT b.*, u.name AS uname, u.phone, r.room_number FROM bookings b
    JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
    WHERE b.status IN ('pending','confirmed') AND b.check_in_date<=CURDATE()
    ORDER BY b.check_in_date")->fetchAll();
$departures=$pdo->query("SELECT b.*, u.name AS uname, u.phone, r.room_number FROM bookings b
    JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
    WHERE b.status='checked_in' ORDER BY b.check_out_date")->fetchAll();

$pageTitle='Check-In/Out';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_staff.php'; ?>
<div class="main-content">
  <h3>Check-In / Check-Out</h3>
  <?= flash('c') ?>

  <div class="card mb-4"><div class="card-header bg-primary text-white"><strong>Arrivals (Pending Check-In)</strong></div>
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($arrivals as $b): ?>
      <tr><td>#<?= $b['id'] ?></td><td><?= htmlspecialchars($b['uname']) ?></td><td><?= htmlspecialchars($b['phone']) ?></td>
        <td><?= htmlspecialchars($b['room_number']) ?></td>
        <td><?= formatDate($b['check_in_date']) ?></td><td><?= formatDate($b['check_out_date']) ?></td>
        <td><form method="POST"><?= csrf_field() ?>
          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
          <input type="hidden" name="action" value="checkin">
          <button class="btn btn-sm btn-success">Check In</button>
        </form></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$arrivals): ?><tr><td colspan="7" class="text-center text-muted">No pending arrivals.</td></tr><?php endif; ?>
    </tbody>
  </table></div></div>

  <div class="card"><div class="card-header bg-warning"><strong>Currently Checked-In Guests</strong></div>
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>#</th><th>Customer</th><th>Room</th><th>Check-Out Due</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($departures as $b): ?>
      <tr><td>#<?= $b['id'] ?></td><td><?= htmlspecialchars($b['uname']) ?></td>
        <td><?= htmlspecialchars($b['room_number']) ?></td>
        <td><?= formatDate($b['check_out_date']) ?></td>
        <td><form method="POST"><?= csrf_field() ?>
          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
          <input type="hidden" name="action" value="checkout">
          <button class="btn btn-sm btn-danger">Check Out</button>
        </form></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$departures): ?><tr><td colspan="5" class="text-center text-muted">No active stays.</td></tr><?php endif; ?>
    </tbody>
  </table></div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
