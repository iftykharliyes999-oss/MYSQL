<?php
require_once __DIR__ . '/../config/config.php';
requireRole('admin');
$pdo = (new Database())->connect();

$daily = $pdo->query("SELECT DATE(paid_at) AS day, SUM(amount) AS total
    FROM payments WHERE payment_status='paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(paid_at) ORDER BY day DESC")->fetchAll();

$monthly = $pdo->query("SELECT DATE_FORMAT(paid_at,'%Y-%m') AS month, SUM(amount) AS total
    FROM payments WHERE payment_status='paid'
    GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();

$occupancy = $pdo->query("SELECT
    (SELECT COUNT(*) FROM rooms WHERE status='booked') AS booked,
    (SELECT COUNT(*) FROM rooms WHERE status='available') AS available,
    (SELECT COUNT(*) FROM rooms WHERE status='maintenance') AS maintenance,
    (SELECT COUNT(*) FROM rooms) AS total")->fetch();

$bookingStats = $pdo->query("SELECT status, COUNT(*) AS cnt FROM bookings GROUP BY status")->fetchAll();

$pageTitle='Reports';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <h3 class="mb-4">Reports & Analytics</h3>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card"><div class="card-header bg-primary text-white"><strong>Room Occupancy</strong></div>
        <div class="card-body">
          <p>Total Rooms: <strong><?= $occupancy['total'] ?></strong></p>
          <p>Available: <strong class="text-success"><?= $occupancy['available'] ?></strong></p>
          <p>Booked: <strong class="text-warning"><?= $occupancy['booked'] ?></strong></p>
          <p>Maintenance: <strong class="text-danger"><?= $occupancy['maintenance'] ?></strong></p>
          <?php $rate = $occupancy['total'] ? round(($occupancy['booked']/$occupancy['total'])*100,1) : 0; ?>
          <div class="progress" style="height:24px">
            <div class="progress-bar bg-warning" style="width:<?= $rate ?>%"><?= $rate ?>% Occupied</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card"><div class="card-header bg-success text-white"><strong>Booking Statistics</strong></div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <?php foreach ($bookingStats as $s): ?>
              <tr><td><?= statusBadge($s['status']) ?></td><td class="text-end"><strong><?= $s['cnt'] ?></strong></td></tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4"><div class="card-header bg-info text-white"><strong>Daily Income (Last 30 Days)</strong></div>
    <div class="table-responsive">
      <table class="table mb-0"><thead><tr><th>Date</th><th class="text-end">Income</th></tr></thead>
        <tbody>
          <?php foreach ($daily as $d): ?>
            <tr><td><?= formatDate($d['day']) ?></td><td class="text-end"><strong><?= formatCurrency($d['total']) ?></strong></td></tr>
          <?php endforeach; ?>
          <?php if (!$daily): ?><tr><td colspan="2" class="text-center text-muted">No paid invoices yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card"><div class="card-header bg-dark text-white"><strong>Monthly Income</strong></div>
    <div class="table-responsive">
      <table class="table mb-0"><thead><tr><th>Month</th><th class="text-end">Income</th></tr></thead>
        <tbody>
          <?php foreach ($monthly as $m): ?>
            <tr><td><?= htmlspecialchars($m['month']) ?></td><td class="text-end"><strong><?= formatCurrency($m['total']) ?></strong></td></tr>
          <?php endforeach; ?>
          <?php if (!$monthly): ?><tr><td colspan="2" class="text-center text-muted">No data.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
