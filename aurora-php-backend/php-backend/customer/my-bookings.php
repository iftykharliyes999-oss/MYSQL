<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');

$stmt = db()->prepare('SELECT b.*, r.number AS room_no FROM bookings b
                       JOIN customers c ON c.id = b.customer_id
                       JOIN rooms r ON r.id = b.room_id
                       WHERE c.user_id = :u ORDER BY b.created_at DESC');
$stmt->execute([':u' => current_user()['id']]);
$rows = $stmt->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>My bookings</title></head><body>
<h1>My bookings</h1>
<table border="1" cellpadding="6"><tr><th>Code</th><th>Room</th><th>Stay</th><th>Total</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $b): ?>
<tr><td><?= clean($b['code']) ?></td><td>#<?= clean($b['room_no']) ?></td>
<td><?= clean($b['check_in']) ?> → <?= clean($b['check_out']) ?></td>
<td>BDT <?= number_format($b['total']) ?></td><td><?= clean($b['status']) ?></td>
<td><a href="invoice.php?id=<?= (int)$b['id'] ?>">Invoice</a></td></tr>
<?php endforeach; ?>
</table>
</body></html>