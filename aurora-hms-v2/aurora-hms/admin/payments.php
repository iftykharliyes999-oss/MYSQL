<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');
$pageTitle = 'Payments';
$rows = db()->query('SELECT p.*, b.code, u.name FROM payments p JOIN bookings b ON b.id=p.booking_id JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id ORDER BY p.paid_at DESC')->fetchAll();
$total = array_sum(array_column($rows, 'amount'));
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="kpi" style="margin-bottom:24px;max-width:340px"><div class="label">Total Collected</div><div class="value"><?= money($total) ?></div><i class="fa-solid fa-money-bill-trend-up"></i></div>
<div class="table-wrap"><table><thead><tr><th>Booking</th><th>Guest</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th><th>Paid</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
  <tr><td><strong><?= e($r['code']) ?></strong></td><td><?= e($r['name']) ?></td><td style="text-transform:uppercase"><?= e($r['method']) ?></td><td><code><?= e($r['reference']) ?></code></td>
  <td><strong><?= money($r['amount']) ?></strong></td><td><span class="pill pill-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td><td><?= e(date('M d, Y', strtotime($r['paid_at']))) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
