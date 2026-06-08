<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('staff');
$pageTitle = 'Housekeeping';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  $rid = (int)$_POST['room_id']; $st = $_POST['status']; $note = trim($_POST['note'] ?? '');
  $uid = current_user()['id'];
  $stmt = db()->prepare('SELECT id FROM staff WHERE user_id=?'); $stmt->execute([$uid]);
  $sid = (int)($stmt->fetchColumn() ?: 0);
  db()->prepare('UPDATE rooms SET status=? WHERE id=?')->execute([$st === 'clean' ? 'available' : $st, $rid]);
  if ($sid) db()->prepare('INSERT INTO housekeeping_logs (room_id,staff_id,status_set,note) VALUES (?,?,?,?)')->execute([$rid,$sid,$st,$note]);
  flash('Room status updated.');
  header('Location: ' . base_url('staff/housekeeping.php')); exit;
}
$rooms = db()->query('SELECT r.*, rt.name AS type_name FROM rooms r JOIN room_types rt ON rt.id=r.type_id ORDER BY r.floor, r.number')->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="grid grid-3">
<?php foreach ($rooms as $r): ?>
  <div class="card" style="padding:20px;border-left:5px solid <?php echo ['available'=>'#16a34a','occupied'=>'#2563eb','dirty'=>'#f59e0b','maintenance'=>'#dc2626'][$r['status']]; ?>">
    <div style="display:flex;justify-content:space-between;align-items:start">
      <div><h3>Room #<?= e($r['number']) ?></h3><p class="muted"><?= e($r['type_name']) ?> · Floor <?= (int)$r['floor'] ?></p></div>
      <span class="pill pill-<?= e($r['status']) ?>"><?= e($r['status']) ?></span>
    </div>
    <form method="post" style="margin-top:14px"><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= $r['id'] ?>">
      <div class="form-group" style="margin-bottom:10px"><select name="status"><option value="clean">Mark Clean (available)</option><option value="dirty">Mark Dirty</option><option value="maintenance">Maintenance</option></select></div>
      <div class="form-group" style="margin-bottom:10px"><input name="note" placeholder="Note (optional)"></div>
      <button class="btn btn-dark btn-sm" style="width:100%;justify-content:center">Update</button>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
