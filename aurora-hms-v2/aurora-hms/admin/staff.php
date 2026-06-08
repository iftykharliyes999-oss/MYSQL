<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');
$pageTitle = 'Staff Management';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  $a = $_POST['action'] ?? '';
  try {
    if ($a === 'add') {
      $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
      db()->beginTransaction();
      $s = db()->prepare('INSERT INTO users (name,email,phone,password_hash,role_id) VALUES (?,?,?,?,2)');
      $s->execute([$_POST['name'], clean_email($_POST['email']), $_POST['phone'], $hash]);
      $uid = (int)db()->lastInsertId();
      db()->prepare('INSERT INTO staff (user_id,department,shift,hired_on,salary) VALUES (?,?,?,?,?)')
          ->execute([$uid, $_POST['department'], $_POST['shift'], $_POST['hired_on'] ?: date('Y-m-d'), (float)$_POST['salary']]);
      db()->commit();
      flash('Staff added.');
    } elseif ($a === 'delete') {
      db()->prepare('DELETE FROM users WHERE id=?')->execute([(int)$_POST['user_id']]);
      flash('Staff removed.');
    }
  } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); flash('Error: '.$e->getMessage(),'error'); }
  header('Location: ' . base_url('admin/staff.php')); exit;
}

$staff = db()->query('SELECT s.*, u.name,u.email,u.phone,u.id AS user_id FROM staff s JOIN users u ON u.id=s.user_id WHERE u.deleted_at IS NULL ORDER BY s.id DESC')->fetchAll();
include __DIR__ . '/../includes/dash-header.php';
?>
<details class="card" style="padding:24px;margin-bottom:24px"><summary style="cursor:pointer;font-weight:600;font-size:1.05rem"><i class="fa-solid fa-user-plus"></i> Add New Staff</summary>
  <form method="post" style="margin-top:18px"><?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="form-row">
      <div class="form-group"><label>Name</label><input name="name" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Phone</label><input name="phone"></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Department</label><select name="department"><option>Housekeeping</option><option>Front Desk</option><option>Maintenance</option><option>Concierge</option></select></div>
      <div class="form-group"><label>Shift</label><select name="shift"><option>Morning</option><option>Evening</option><option>Night</option></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Hired On</label><input type="date" name="hired_on" value="<?= date('Y-m-d') ?>"></div>
      <div class="form-group"><label>Salary</label><input type="number" step="0.01" name="salary" value="25000"></div>
    </div>
    <button class="btn btn-primary">Add Staff</button>
  </form>
</details>
<div class="table-wrap">
  <table><thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Shift</th><th>Salary</th><th></th></tr></thead><tbody>
  <?php foreach ($staff as $s): ?>
    <tr>
      <td><strong><?= e($s['name']) ?></strong><br><small class="muted"><?= e($s['phone']) ?></small></td>
      <td><?= e($s['email']) ?></td><td><?= e($s['department']) ?></td><td><?= e($s['shift']) ?></td><td><?= money($s['salary']) ?></td>
      <td><form method="post" onsubmit="return confirm('Remove this staff member?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= $s['user_id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
