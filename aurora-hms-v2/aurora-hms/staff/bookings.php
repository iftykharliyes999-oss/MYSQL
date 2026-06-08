<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('staff');
$pageTitle = 'Check-in / Check-out';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  $id = (int)$_POST['id']; $a = $_POST['action'];
  if ($a === 'checkin') {
    db()->prepare("UPDATE bookings SET status='checked-in' WHERE id=?")->execute([$id]);
    $b = db()->prepare('SELECT room_id FROM bookings WHERE id=?'); $b->execute([$id]); $rid = (int)$b->fetchColumn();
    db()->prepare("UPDATE rooms SET status='occupied' WHERE id=?")->execute([$rid]);
    flash('Guest checked in.');
  } elseif ($a === 'checkout') {
    db()->prepare("UPDATE bookings SET status='checked-out' WHERE id=?")->execute([$id]);
    $b = db()->prepare('SELECT room_id FROM bookings WHERE id=?'); $b->execute([$id]); $rid = (int)$b->fetchColumn();
    db()->prepare("UPDATE rooms SET status='dirty' WHERE id=?")->execute([$rid]);
    // ensure invoice
    $chk = db()->prepare('SELECT id FROM invoices WHERE booking_id=?'); $chk->execute([$id]);
    if (!$chk->fetch()) {
      $bk = db()->prepare('SELECT total FROM bookings WHERE id=?'); $bk->execute([$id]); $tot = (float)$bk->fetchColumn();
      $tax = $tot * TAX_RATE; $svc = $tot * SERVICE_RATE;
      db()->prepare('INSERT INTO invoices (booking_id,subtotal,tax,service,total) VALUES (?,?,?,?,?)')->execute([$id,$tot,$tax,$svc,$tot+$tax+$svc]);
    }
    flash('Guest checked out. Invoice generated.');
  }
  header('Location: ' . base_url('staff/bookings.php')); exit;
}

$rows = db()->query("SELECT b.*, u.name AS guest, r.number AS room_no FROM bookings b JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id JOIN rooms r ON r.id=b.room_id WHERE b.status IN ('confirmed','checked-in') AND b.deleted_at IS NULL ORDER BY b.check_in")->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="table-wrap"><table><thead><tr><th>Code</th><th>Guest</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach ($rows as $b): ?>
  <tr><td><strong><?= e($b['code']) ?></strong></td><td><?= e($b['guest']) ?></td><td>#<?= e($b['room_no']) ?></td><td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td><td><?= money($b['total']) ?></td><td><span class="pill pill-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
  <td><form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $b['id'] ?>">
    <?php if ($b['status']==='confirmed'): ?><button name="action" value="checkin" class="btn btn-sm btn-primary"><i class="fa-solid fa-arrow-right-to-bracket"></i> Check-in</button>
    <?php else: ?><button name="action" value="checkout" class="btn btn-sm btn-dark"><i class="fa-solid fa-arrow-right-from-bracket"></i> Check-out</button><?php endif; ?>
  </form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
