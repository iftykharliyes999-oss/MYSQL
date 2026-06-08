<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');
$pageTitle = 'Customers';
$rows = db()->query('SELECT c.*, u.name, u.email, u.phone, u.created_at FROM customers c JOIN users u ON u.id=c.user_id WHERE u.deleted_at IS NULL ORDER BY u.created_at DESC')->fetchAll();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Loyalty</th><th>Joined</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
  <tr><td><strong><?= e($r['name']) ?></strong></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td>
  <td><span class="pill pill-confirmed"><?= (int)$r['loyalty_points'] ?> pts</span></td><td><?= e(date('M d, Y', strtotime($r['created_at']))) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
