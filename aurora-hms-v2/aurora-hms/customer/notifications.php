<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('customer');
$pageTitle = 'Notifications';
$u = current_user();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  if (($_POST['action'] ?? '') === 'read_all') {
    db()->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? OR (user_id IS NULL AND role=?)")
        ->execute([$u['id'], $u['role']]);
    flash('All marked as read.');
  } elseif (($_POST['action'] ?? '') === 'read') {
    mark_notification_read((int)$_POST['id'], $u);
  }
  header('Location: ' . base_url($u['role'].'/notifications.php')); exit;
}

$all = db()->prepare("SELECT * FROM notifications
  WHERE user_id=? OR (user_id IS NULL AND role=?)
  ORDER BY created_at DESC LIMIT 200");
$all->execute([$u['id'], $u['role']]); $all = $all->fetchAll();

include __DIR__ . '/../includes/dash-header.php';
?>
<form method="post" style="margin-bottom:18px"><?= csrf_field() ?><input type="hidden" name="action" value="read_all">
  <button class="btn btn-dark"><i class="fa-solid fa-check-double"></i> Mark all as read</button>
</form>
<div class="card" style="padding:0;overflow:hidden">
  <?php if (!$all): ?><div style="padding:40px;text-align:center;color:#94a3b8">No notifications yet</div><?php endif; ?>
  <?php foreach ($all as $n): ?>
    <div style="padding:18px 22px;border-bottom:1px solid #f1f5f9;display:flex;gap:14px;align-items:center;<?= $n['is_read']?'opacity:.6':'background:#fffbeb' ?>">
      <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#b45309">
        <i class="fa-solid fa-<?= $n['type']==='booking'?'calendar-check':($n['type']==='message'?'envelope':'bell') ?>"></i>
      </div>
      <div style="flex:1">
        <strong><?= e($n['title']) ?></strong>
        <p class="muted" style="margin-top:4px;font-size:.88rem"><?= e($n['body']) ?></p>
        <small style="color:#94a3b8"><?= e(date('M j, Y H:i', strtotime($n['created_at']))) ?></small>
      </div>
      <?php if ($n['link']): ?><a class="btn btn-sm btn-dark" href="<?= base_url($n['link']) ?>">Open</a><?php endif; ?>
      <?php if (!$n['is_read']): ?>
        <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= $n['id'] ?>">
          <button class="btn btn-sm btn-primary"><i class="fa-solid fa-check"></i></button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
