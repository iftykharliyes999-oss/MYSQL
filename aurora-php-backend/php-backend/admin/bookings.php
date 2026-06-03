<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');

$rows = db()->query('SELECT b.*, u.name AS guest, r.number AS room_no
                     FROM bookings b
                     JOIN customers c ON c.id = b.customer_id
                     JOIN users u ON u.id = c.user_id
                     JOIN rooms r ON r.id = b.room_id
                     ORDER BY b.created_at DESC')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Bookings · Aurora Grand</title></head><body>
<h1>All Bookings</h1>
<table border="1" cellpadding="6"><tr><th>Code</th><th>Guest</th><th>Room</th><th>Stay</th><th>Total</th><th>Status</th></tr>
<?php foreach ($rows as $b): ?>
<tr><td><?= clean($b['code']) ?></td><td><?= clean($b['guest']) ?></td>
<td>#<?= clean($b['room_no']) ?></td><td><?= clean($b['check_in']) ?> → <?= clean($b['check_out']) ?></td>
<td>BDT <?= number_format($b['total']) ?></td><td><?= clean($b['status']) ?></td></tr>
<?php endforeach; ?>
</table>
</body></html>
