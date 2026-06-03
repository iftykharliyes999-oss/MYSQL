<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $room = (int)$_POST['room_id'];
    $status = $_POST['status'] ?? 'available';
    $allowed = ['available','occupied','dirty','maintenance'];
    if (in_array($status, $allowed, true)) {
        db()->prepare('UPDATE rooms SET status=:s WHERE id=:i')->execute([':s' => $status, ':i' => $room]);
        // log
        $sid = (int)db()->prepare('SELECT id FROM staff WHERE user_id=:u')->execute([':u' => current_user()['id']]);
    }
    header('Location: housekeeping.php');
    exit;
}

$rooms = db()->query('SELECT * FROM rooms ORDER BY number')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Housekeeping</title></head><body>
<h1>Housekeeping floor</h1>
<?php foreach ($rooms as $r): ?>
<div style="display:inline-block;border:1px solid #ccc;padding:8px;margin:4px;min-width:140px">
  <strong>#<?= clean($r['number']) ?></strong> · <?= clean($r['status']) ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>">
    <select name="status">
      <option value="available">Clean</option><option value="dirty">Dirty</option>
      <option value="occupied">Occupied</option><option value="maintenance">Maintenance</option>
    </select>
    <button>Update</button>
  </form>
</div>
<?php endforeach; ?>
</body></html>