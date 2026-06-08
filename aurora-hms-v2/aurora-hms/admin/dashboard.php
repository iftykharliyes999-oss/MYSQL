<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('admin');
$pageTitle = 'Admin Dashboard';

$kpi = [
  'rooms' => (int)db()->query('SELECT COUNT(*) FROM rooms')->fetchColumn(),
  'occupied' => (int)db()->query("SELECT COUNT(*) FROM rooms WHERE status='occupied'")->fetchColumn(),
  'available' => (int)db()->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn(),
  'bookings' => (int)db()->query('SELECT COUNT(*) FROM bookings WHERE deleted_at IS NULL')->fetchColumn(),
  'revenue' => (float)db()->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn(),
  'customers' => (int)db()->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
  'staff' => (int)db()->query('SELECT COUNT(*) FROM staff')->fetchColumn(),
  'pending' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(),
];
$occupancy = $kpi['rooms'] ? round($kpi['occupied']*100/$kpi['rooms']) : 0;

$monthly = db()->query("SELECT DATE_FORMAT(paid_at,'%b') AS m, SUM(amount) AS t FROM payments WHERE status='paid' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(paid_at,'%Y-%m') ORDER BY paid_at")->fetchAll();
$statusCount = db()->query("SELECT status, COUNT(*) c FROM bookings WHERE deleted_at IS NULL GROUP BY status")->fetchAll();
$recent = db()->query("SELECT b.code,b.check_in,b.total,b.status,u.name FROM bookings b JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id WHERE b.deleted_at IS NULL ORDER BY b.created_at DESC LIMIT 6")->fetchAll();

// Recent activity
$recentNotifs = [];
try {
  $recentNotifs = db()->query("SELECT * FROM notifications WHERE role='admin' OR type='booking' ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {}
$openMessages = 0;
try { $openMessages = (int)db()->query("SELECT COUNT(*) FROM message_threads WHERE status='open'")->fetchColumn(); } catch (Throwable $e) {}

include __DIR__ . '/../includes/dash-header.php';
?>
<div class="grid grid-4" style="margin-bottom:28px">
  <div class="kpi"><div class="label">Total Rooms</div><div class="value"><?= $kpi['rooms'] ?></div><div class="sub"><?= $kpi['available'] ?> available · <?= $kpi['occupied'] ?> occupied</div><i class="fa-solid fa-bed"></i></div>
  <div class="kpi"><div class="label">Bookings</div><div class="value"><?= $kpi['bookings'] ?></div><div class="sub"><?= $kpi['pending'] ?> pending approval</div><i class="fa-solid fa-calendar-check"></i></div>
  <div class="kpi"><div class="label">Revenue</div><div class="value"><?= money($kpi['revenue']) ?></div><div class="sub">All-time collected</div><i class="fa-solid fa-money-bill-trend-up"></i></div>
  <div class="kpi"><div class="label">Open Inquiries</div><div class="value"><?= $openMessages ?></div><div class="sub"><?= $occupancy ?>% occupancy · <?= $kpi['customers'] ?> customers</div><i class="fa-solid fa-comments"></i></div>
</div>

<div class="grid grid-2" style="margin-bottom:28px">
  <div class="card" style="padding:24px"><h3 style="margin-bottom:18px">Monthly Revenue</h3><canvas id="revChart" height="120"></canvas></div>
  <div class="card" style="padding:24px"><h3 style="margin-bottom:18px">Booking Status</h3><canvas id="statusChart" height="120"></canvas></div>
</div>

<div class="grid grid-2">
  <div class="card" style="padding:24px">
    <h3 style="margin-bottom:18px">Recent Bookings</h3>
    <div class="table-wrap" style="box-shadow:none;border:none">
      <table><thead><tr><th>Code</th><th>Guest</th><th>Check-in</th><th>Total</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($recent as $r): ?>
        <tr><td><strong><?= e($r['code']) ?></strong></td><td><?= e($r['name']) ?></td><td><?= e($r['check_in']) ?></td><td><?= money($r['total']) ?></td><td><span class="pill pill-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>
  <div class="card" style="padding:24px">
    <h3 style="margin-bottom:18px">Live Activity</h3>
    <?php if (!$recentNotifs): ?><p class="muted">No recent activity.</p><?php endif; ?>
    <?php foreach ($recentNotifs as $n): ?>
      <a href="<?= base_url($n['link'] ?: 'admin/notifications.php') ?>" style="display:block;padding:12px 0;border-bottom:1px solid #f1f5f9;text-decoration:none;color:inherit">
        <strong><?= e($n['title']) ?></strong>
        <p class="muted" style="font-size:.85rem;margin-top:4px"><?= e($n['body']) ?></p>
        <small style="color:#94a3b8"><?= e(date('M j, H:i', strtotime($n['created_at']))) ?></small>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<script>
const months = <?= json_encode(array_column($monthly, 'm')) ?>;
const totals = <?= json_encode(array_map('floatval', array_column($monthly, 't'))) ?>;
new Chart(document.getElementById('revChart'), {
  type:'line',
  data:{labels:months.length?months:['No data'], datasets:[{label:'Revenue (<?= CURRENCY ?>)', data:totals.length?totals:[0], borderColor:'#d4a857', backgroundColor:'rgba(212,168,87,.15)', tension:.4, fill:true, borderWidth:3}]},
  options:{plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
const sLabels = <?= json_encode(array_column($statusCount,'status')) ?>;
const sData = <?= json_encode(array_map('intval', array_column($statusCount,'c'))) ?>;
new Chart(document.getElementById('statusChart'), {
  type:'doughnut',
  data:{labels:sLabels.length?sLabels:['No data'], datasets:[{data:sData.length?sData:[1], backgroundColor:['#f59e0b','#16a34a','#2563eb','#475569','#dc2626']}]},
  options:{plugins:{legend:{position:'bottom'}}}
});
</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
