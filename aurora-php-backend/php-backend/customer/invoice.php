<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT b.*, r.number AS room_no, t.name AS type_name, u.name AS guest, i.subtotal, i.tax, i.service, i.total AS inv_total
                       FROM bookings b
                       JOIN rooms r ON r.id = b.room_id
                       JOIN room_types t ON t.id = r.type_id
                       JOIN customers c ON c.id = b.customer_id
                       JOIN users u ON u.id = c.user_id
                       LEFT JOIN invoices i ON i.booking_id = b.id
                       WHERE b.id = :i LIMIT 1');
$stmt->execute([':i' => $id]);
$b = $stmt->fetch();
if (!$b) exit('Invoice not found.');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Invoice <?= clean($b['code']) ?></title></head><body>
<h1>Aurora Grand · Invoice <?= clean($b['code']) ?></h1>
<p>Guest: <?= clean($b['guest']) ?></p>
<p>Room #<?= clean($b['room_no']) ?> · <?= clean($b['type_name']) ?></p>
<p><?= clean($b['check_in']) ?> → <?= clean($b['check_out']) ?> · <?= (int)$b['nights'] ?> nights</p>
<table border="1" cellpadding="6">
  <tr><td>Subtotal</td><td>BDT <?= number_format($b['subtotal']) ?></td></tr>
  <tr><td>VAT (15%)</td><td>BDT <?= number_format($b['tax']) ?></td></tr>
  <tr><td>Service (5%)</td><td>BDT <?= number_format($b['service']) ?></td></tr>
  <tr><td><strong>Total</strong></td><td><strong>BDT <?= number_format($b['inv_total']) ?></strong></td></tr>
</table>
<p><button onclick="window.print()">Print / Save PDF</button></p>
</body></html>