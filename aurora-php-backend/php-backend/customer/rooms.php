<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');

$rooms = db()->query("SELECT r.*, t.name AS type_name FROM rooms r JOIN room_types t ON t.id = r.type_id WHERE r.status = 'available' ORDER BY r.price")->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Browse rooms</title></head><body>
<h1>Available rooms</h1>
<?php foreach ($rooms as $r): ?>
<div style="border:1px solid #ccc;padding:12px;margin:8px;display:inline-block">
  <strong>#<?= clean($r['number']) ?></strong> · <?= clean($r['type_name']) ?><br>
  BDT <?= number_format($r['price']) ?> / night<br>
  <a href="booking.php?room=<?= (int)$r['id'] ?>">Book</a>
</div>
<?php endforeach; ?>
</body></html>