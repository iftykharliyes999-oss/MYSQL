<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');

$rows = db()->query('SELECT s.id, u.name, u.email, s.department, s.shift
                     FROM staff s JOIN users u ON u.id = s.user_id ORDER BY u.name')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Staff · Aurora Grand</title></head><body>
<h1>Staff</h1>
<table border="1" cellpadding="6"><tr><th>Name</th><th>Email</th><th>Department</th><th>Shift</th></tr>
<?php foreach ($rows as $s): ?>
<tr><td><?= clean($s['name']) ?></td><td><?= clean($s['email']) ?></td>
<td><?= clean($s['department']) ?></td><td><?= clean($s['shift']) ?></td></tr>
<?php endforeach; ?>
</table>
</body></html>