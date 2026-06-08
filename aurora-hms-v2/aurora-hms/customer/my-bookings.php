<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');
$pageTitle = 'My Bookings';
$u = current_user();
$cid = (int)(db()->query('SELECT id FROM customers WHERE user_id='.(int)$u['id'])->fetchColumn() ?: 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  if (($_POST['action'] ?? '') === 'cancel') {
    $id = (int)$_POST['id'];
    db()->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND customer_id=?")->execute([$id, $cid]);
    flash('Booking cancelled.');
  }
  header('Location: ' . base_url('customer/my-bookings.php')); exit;
}

$rows = db()->prepare("SELECT b.*, r.number, rt.name AS type FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN room_types rt ON rt.id=r.type_id WHERE b.customer_id=? AND b.deleted_at IS NULL ORDER BY b.check_in DESC");
$rows->execute([$cid]); $rows = $rows->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="table-wrap"><table><thead><tr><th>Code</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $b): ?>
  <tr><td><strong><?= e($b['code']) ?></strong></td><td><?= e($b['type']) ?> #<?= e($b['number']) ?></td><td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td><td><?= money($b['total']) ?></td><td><span class="pill pill-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
  <td style="display:flex;gap:6px"><a href="<?= base_url('customer/invoice.php?id='.$b['id']) ?>" class="btn btn-sm btn-dark"><i class="fa-solid fa-file-invoice"></i> Invoice</a>
  <?php if (in_array($b['status'], ['pending','confirmed'])): ?>
    <form method="post" onsubmit="return confirm('Cancel this booking?')"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-xmark"></i></button></form>
  <?php endif; ?></td></tr>
<?php endforeach; if(!$rows): ?><tr><td colspan="6" style="text-align:center;color:var(--slate-2);padding:40px">No bookings yet. <a href="<?= base_url('rooms.php') ?>" style="color:var(--gold-2)">Browse rooms →</a></td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
