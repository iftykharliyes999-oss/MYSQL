<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');
$pageTitle = 'My Dashboard';
$u = current_user();
$cust = db()->prepare('SELECT id, loyalty_points FROM customers WHERE user_id=?');
$cust->execute([$u['id']]); $cust = $cust->fetch();
$cid = (int)($cust['id'] ?? 0);
$kpi = [
  'total' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE customer_id=$cid AND deleted_at IS NULL")->fetchColumn(),
  'upcoming' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE customer_id=$cid AND status IN ('pending','confirmed') AND deleted_at IS NULL")->fetchColumn(),
  'stayed' => (int)db()->query("SELECT COUNT(*) FROM bookings WHERE customer_id=$cid AND status='checked-out'")->fetchColumn(),
  'loyalty' => (int)($cust['loyalty_points'] ?? 0),
];
$recent = db()->prepare("SELECT b.*, r.number, rt.name AS type FROM bookings b JOIN rooms r ON r.id=b.room_id JOIN room_types rt ON rt.id=r.type_id WHERE b.customer_id=? AND b.deleted_at IS NULL ORDER BY b.created_at DESC LIMIT 5");
$recent->execute([$cid]); $recent = $recent->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="card" style="padding:30px;margin-bottom:24px;background:linear-gradient(135deg,var(--navy),var(--navy-2));color:#fff">
  <h2 style="color:#fff">Welcome back, <?= e($u['name']) ?> 👋</h2>
  <p style="opacity:.85;margin-top:6px">Enjoy your premium experience at <?= e(HOTEL_NAME) ?></p>
</div>
<div class="grid grid-4">
  <div class="kpi"><div class="label">Total Bookings</div><div class="value"><?= $kpi['total'] ?></div><i class="fa-solid fa-calendar"></i></div>
  <div class="kpi"><div class="label">Upcoming</div><div class="value"><?= $kpi['upcoming'] ?></div><i class="fa-solid fa-plane-departure"></i></div>
  <div class="kpi"><div class="label">Completed Stays</div><div class="value"><?= $kpi['stayed'] ?></div><i class="fa-solid fa-circle-check"></i></div>
  <div class="kpi"><div class="label">Loyalty Points</div><div class="value"><?= $kpi['loyalty'] ?></div><i class="fa-solid fa-star"></i></div>
</div>
<div class="card" style="padding:24px;margin-top:24px"><h3 style="margin-bottom:18px">Recent Bookings</h3>
  <?php if (!$recent): ?><p class="muted">No bookings yet. <a href="<?= base_url('rooms.php') ?>" style="color:var(--gold-2)">Browse rooms →</a></p>
  <?php else: ?>
  <div class="table-wrap" style="box-shadow:none;border:none"><table><thead><tr><th>Code</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($recent as $b): ?>
    <tr><td><strong><?= e($b['code']) ?></strong></td><td><?= e($b['type']) ?> #<?= e($b['number']) ?></td><td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td><td><?= money($b['total']) ?></td><td><span class="pill pill-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
    <td><a href="<?= base_url('customer/invoice.php?id='.$b['id']) ?>" class="btn btn-sm btn-dark"><i class="fa-solid fa-file-invoice"></i></a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
