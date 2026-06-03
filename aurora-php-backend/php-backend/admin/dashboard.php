<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');

$rooms = db()->query('SELECT status, COUNT(*) c FROM rooms GROUP BY status')->fetchAll();
$revenue = (float)db()->query("SELECT COALESCE(SUM(total),0) t FROM bookings WHERE status <> 'cancelled'")->fetchColumn();
$bookings = (int)db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin · Aurora Grand</title></head>
<body>
  <h1>Admin Dashboard</h1>
  <p>Signed in as <?= clean(current_user()['name']) ?> (<a href="../auth/logout.php">logout</a>)</p>
  <ul>
    <li>Total revenue: <strong>BDT <?= number_format($revenue) ?></strong></li>
    <li>Total bookings: <strong><?= $bookings ?></strong></li>
    <?php foreach ($rooms as $r): ?>
      <li>Rooms <?= clean($r['status']) ?>: <?= (int)$r['c'] ?></li>
    <?php endforeach; ?>
  </ul>
  <p><a href="rooms.php">Manage rooms</a> · <a href="bookings.php">Bookings</a> · <a href="staff.php">Staff</a></p>
</body></html>