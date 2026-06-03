<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = db()->prepare('INSERT INTO rooms (number,type_id,status,price,floor) VALUES (:n,:t,:s,:p,:f)');
        $stmt->execute([
            ':n' => clean($_POST['number']),
            ':t' => (int)$_POST['type_id'],
            ':s' => $_POST['status'],
            ':p' => (float)$_POST['price'],
            ':f' => (int)$_POST['floor'],
        ]);
    } elseif ($action === 'update') {
        $stmt = db()->prepare('UPDATE rooms SET status=:s, price=:p WHERE id=:i');
        $stmt->execute([':s' => $_POST['status'], ':p' => (float)$_POST['price'], ':i' => (int)$_POST['id']]);
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM rooms WHERE id=:i');
        $stmt->execute([':i' => (int)$_POST['id']]);
    }
    header('Location: rooms.php');
    exit;
}

$rooms = db()->query('SELECT r.*, t.name AS type_name FROM rooms r JOIN room_types t ON t.id = r.type_id ORDER BY r.number')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Rooms · Aurora Grand</title></head><body>
<h1>Room Management</h1>
<table border="1" cellpadding="6"><tr><th>#</th><th>Type</th><th>Status</th><th>Price</th><th></th></tr>
<?php foreach ($rooms as $r): ?>
<tr>
  <td><?= clean($r['number']) ?></td><td><?= clean($r['type_name']) ?></td>
  <td><?= clean($r['status']) ?></td><td><?= number_format($r['price']) ?></td>
  <td>
    <form method="post" style="display:inline">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <button onclick="return confirm('Delete?')">Delete</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
<p><a href="dashboard.php">← Dashboard</a></p>
</body></html>