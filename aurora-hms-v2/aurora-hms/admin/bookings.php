<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');
$pageTitle = 'All Bookings';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  if (($_POST['action'] ?? '') === 'status') {
    $id = (int)$_POST['id']; $st = $_POST['status'];
    db()->prepare('UPDATE bookings SET status=? WHERE id=?')->execute([$st, $id]);
    audit('booking_status_'.$st, 'bookings', $id);
    flash('Status updated.');
  }
  header('Location: ' . base_url('admin/bookings.php')); exit;
}

$bookings = db()->query('SELECT b.*, u.name AS guest, r.number AS room_no FROM bookings b JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id JOIN rooms r ON r.id=b.room_id WHERE b.deleted_at IS NULL ORDER BY b.created_at DESC')->fetchAll();
include __DIR__ . '/../includes/dash-header.php';
?>
<div class="table-wrap">
  <table><thead><tr><th>Code</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Change</th></tr></thead><tbody>
  <?php foreach ($bookings as $b): ?>
    <tr>
      <td><strong><?= e($b['code']) ?></strong></td><td><?= e($b['guest']) ?></td><td>#<?= e($b['room_no']) ?></td>
      <td><?= e($b['check_in']) ?></td><td><?= e($b['check_out']) ?></td><td><?= money($b['total']) ?></td>
      <td><span class="pill pill-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
      <td>
        <form method="post" style="display:flex;gap:6px"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $b['id'] ?>">
          <select name="status" style="padding:6px 10px;border:1px solid var(--line);border-radius:6px">
            <?php foreach (['pending','confirmed','checked-in','checked-out','cancelled'] as $s): ?>
              <option <?= $b['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-dark">Save</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
