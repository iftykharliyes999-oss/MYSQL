<?php
require_once __DIR__ . '/../config/config.php';
requireRole('staff');
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $id=(int)$_POST['id']; $status=$_POST['status'];
    if (in_array($status,['pending','in_progress','done'])) {
        $completed = $status==='done' ? date('Y-m-d H:i:s') : null;
        $pdo->prepare("UPDATE housekeeping SET status=?, completed_at=? WHERE id=? AND staff_id=?")
            ->execute([$status,$completed,$id,$_SESSION['user_id']]);
        if ($status==='done') {
            $r=$pdo->prepare("SELECT room_id,task_type FROM housekeeping WHERE id=?");
            $r->execute([$id]); $row=$r->fetch();
            if ($row && in_array($row['task_type'],['cleaning','maintenance']))
                $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([$row['room_id']]);
        }
        flash('h','Task updated.','success');
    }
    redirect($_SERVER['PHP_SELF']);
}
$tasks=$pdo->prepare("SELECT hk.*, r.room_number FROM housekeeping hk
    JOIN rooms r ON r.id=hk.room_id WHERE hk.staff_id=? ORDER BY hk.assigned_at DESC");
$tasks->execute([$_SESSION['user_id']]);
$tasks=$tasks->fetchAll();
$pageTitle='My Tasks';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_staff.php'; ?>
<div class="main-content">
  <h3>My Housekeeping Tasks</h3>
  <?= flash('h') ?>
  <div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Room</th><th>Task</th><th>Notes</th><th>Status</th><th>Assigned</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($tasks as $t): ?>
      <tr>
        <td>#<?= $t['id'] ?></td><td><?= htmlspecialchars($t['room_number']) ?></td>
        <td class="text-capitalize"><?= $t['task_type'] ?></td>
        <td><small><?= htmlspecialchars($t['notes']) ?></small></td>
        <td><?= statusBadge($t['status']) ?></td>
        <td><?= formatDateTime($t['assigned_at']) ?></td>
        <td>
          <form method="POST" class="d-flex gap-2"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <select class="form-select form-select-sm" name="status">
              <?php foreach (['pending','in_progress','done'] as $s): ?>
                <option <?= $t['status']==$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-primary">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$tasks): ?><tr><td colspan="7" class="text-center text-muted">No tasks assigned.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
