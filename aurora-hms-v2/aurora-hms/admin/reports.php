<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');
$pageTitle = 'Reports & Analytics';

$byType = db()->query("SELECT rt.name, COUNT(b.id) c, COALESCE(SUM(b.total),0) revenue FROM room_types rt LEFT JOIN rooms r ON r.type_id=rt.id LEFT JOIN bookings b ON b.room_id=r.id AND b.deleted_at IS NULL GROUP BY rt.id ORDER BY revenue DESC")->fetchAll();
$byMonth = db()->query("SELECT DATE_FORMAT(check_in,'%Y-%m') ym, COUNT(*) c FROM bookings WHERE deleted_at IS NULL AND check_in >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym")->fetchAll();
$topCustomers = db()->query("SELECT u.name, COUNT(b.id) bookings, COALESCE(SUM(b.total),0) spent FROM customers c JOIN users u ON u.id=c.user_id LEFT JOIN bookings b ON b.customer_id=c.id GROUP BY c.id ORDER BY spent DESC LIMIT 5")->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>

<div class="grid grid-2" style="margin-bottom:24px">
  <div class="card" style="padding:24px"><h3 style="margin-bottom:18px">Bookings by Room Type</h3><canvas id="byType"></canvas></div>
  <div class="card" style="padding:24px"><h3 style="margin-bottom:18px">Monthly Booking Volume</h3><canvas id="byMonth"></canvas></div>
</div>
<div class="card" style="padding:24px"><h3 style="margin-bottom:18px">Top Customers</h3>
  <div class="table-wrap" style="box-shadow:none;border:none"><table><thead><tr><th>Name</th><th>Bookings</th><th>Total Spent</th></tr></thead><tbody>
  <?php foreach ($topCustomers as $t): ?><tr><td><strong><?= e($t['name']) ?></strong></td><td><?= (int)$t['bookings'] ?></td><td><?= money($t['spent']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>
<script>
new Chart(document.getElementById('byType'), {type:'bar',data:{labels:<?= json_encode(array_column($byType,'name')) ?>,datasets:[{label:'Revenue', data:<?= json_encode(array_map('floatval',array_column($byType,'revenue'))) ?>, backgroundColor:'#d4a857'}]},options:{plugins:{legend:{display:false}}}});
new Chart(document.getElementById('byMonth'), {type:'bar',data:{labels:<?= json_encode(array_column($byMonth,'ym')) ?>,datasets:[{label:'Bookings', data:<?= json_encode(array_map('intval',array_column($byMonth,'c'))) ?>, backgroundColor:'#0b1426'}]},options:{plugins:{legend:{display:false}}}});
</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
