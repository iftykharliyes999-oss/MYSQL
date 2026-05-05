<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['admin','staff']);
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action=$_POST['action']??'';
    if ($action==='assign') {
        $pdo->prepare("INSERT INTO housekeeping (room_id,staff_id,task_type,notes) VALUES (?,?,?,?)")
            ->execute([(int)$_POST['room_id'],(int)$_POST['staff_id'],$_POST['task_type'],sanitize($_POST['notes'])]);
        if ($_POST['task_type']==='maintenance')
            $pdo->prepare("UPDATE rooms SET status='maintenance' WHERE id=?")->execute([(int)$_POST['room_id']]);
        flash('h','Task assigned.','success');
    } elseif ($action==='update') {
        $id=(int)$_POST['id']; $status=$_POST['status'];
        $completed = $status==='done' ? date('Y-m-d H:i:s') : null;
        $pdo->prepare("UPDATE housekeeping SET status=?, completed_at=? WHERE id=?")
            ->execute([$status,$completed,$id]);
        if ($status==='done') {
            $row=$pdo->prepare("SELECT room_id,task_type FROM housekeeping WHERE id=?");
            $row->execute([$id]); $r=$row->fetch();
            if ($r['task_type']==='cleaning' || $r['task_type']==='maintenance')
                $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([$r['room_id']]);
        }
        flash('h','Task status updated.','success');
    }
    redirect($_SERVER['PHP_SELF']);
}
$tasks=$pdo->query("SELECT hk.*, r.room_number, s.name AS staff_name FROM housekeeping hk
                    JOIN rooms r ON r.id=hk.room_id JOIN users s ON s.id=hk.staff_id
                    ORDER BY hk.assigned_at DESC")->fetchAll();
$rooms=$pdo->query("SELECT * FROM rooms ORDER BY room_number")->fetchAll();
$staff=$pdo->query("SELECT * FROM users WHERE role='staff' AND status='active'")->fetchAll();
$pageTitle='Housekeeping';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <div class="d-flex justify-content-between mb-3">
    <h3>Housekeeping</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignTask"><i class="fa fa-plus"></i> Assign Task</button>
  </div>
  <?= flash('h') ?>
  <div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Room</th><th>Staff</th><th>Task</th><th>Status</th><th>Notes</th><th>Assigned</th><th>Completed</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($tasks as $t): ?>
        <tr>
          <td>#<?= $t['id'] ?></td>
          <td><?= htmlspecialchars($t['room_number']) ?></td>
          <td><?= htmlspecialchars($t['staff_name']) ?></td>
          <td class="text-capitalize"><?= $t['task_type'] ?></td>
          <td><?= statusBadge($t['status']) ?></td>
          <td><small><?= htmlspecialchars($t['notes']) ?></small></td>
          <td><?= formatDateTime($t['assigned_at']) ?></td>
          <td><?= $t['completed_at'] ? formatDateTime($t['completed_at']) : '—' ?></td>
          <td>
            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#hk<?= $t['id'] ?>"><i class="fa fa-edit"></i></button>
          </td>
        </tr>
        <div class="modal fade" id="hk<?= $t['id'] ?>"><div class="modal-dialog"><div class="modal-content">
          <form method="POST">
            <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $t['id'] ?>">
            <div class="modal-header"><h5>Update Task</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <select class="form-control" name="status">
                <?php foreach (['pending','in_progress','done'] as $s): ?>
                  <option <?= $t['status']==$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
        <?php if (!$tasks): ?><tr><td colspan="9" class="text-center text-muted">No tasks.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>

<div class="modal fade" id="assignTask"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <?= csrf_field() ?><input type="hidden" name="action" value="assign">
    <div class="modal-header"><h5>Assign Task</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label>Room</label>
      <select class="form-control mb-2" name="room_id" required>
        <?php foreach ($rooms as $r): ?>
          <option value="<?= $r['id'] ?>">Room <?= htmlspecialchars($r['room_number']) ?> (<?= $r['status'] ?>)</option>
        <?php endforeach; ?>
      </select>
      <label>Staff</label>
      <select class="form-control mb-2" name="staff_id" required>
        <?php foreach ($staff as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Task Type</label>
      <select class="form-control mb-2" name="task_type">
        <option value="cleaning">Cleaning</option>
        <option value="maintenance">Maintenance</option>
        <option value="inspection">Inspection</option>
      </select>
      <textarea class="form-control" name="notes" placeholder="Notes (optional)"></textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Assign</button></div>
  </form>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
