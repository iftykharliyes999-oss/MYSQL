<?php
require_once __DIR__ . '/../config/config.php';
requireRole('admin');
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action==='create') {
        $name=sanitize($_POST['name']); $email=sanitize($_POST['email']);
        $role=$_POST['role']; $phone=sanitize($_POST['phone']);
        $hash=password_hash($_POST['password'],PASSWORD_DEFAULT);
        try {
          $pdo->prepare("INSERT INTO users (name,email,password,role,phone) VALUES (?,?,?,?,?)")
              ->execute([$name,$email,$hash,$role,$phone]);
          flash('u','User created.','success');
        } catch(Exception $e){ flash('u','Error: '.$e->getMessage(),'danger'); }
    } elseif ($action==='update') {
        $id=(int)$_POST['id'];
        $pdo->prepare("UPDATE users SET name=?,email=?,role=?,phone=?,status=? WHERE id=?")
            ->execute([sanitize($_POST['name']),sanitize($_POST['email']),$_POST['role'],
                       sanitize($_POST['phone']),$_POST['status'],$id]);
        flash('u','User updated.','success');
    } elseif ($action==='delete') {
        $id=(int)$_POST['id'];
        if ($id != $_SESSION['user_id']) {
          $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
          flash('u','User deleted.','success');
        } else flash('u','Cannot delete your own account.','danger');
    }
    redirect(BASE_URL . '/admin/users.php');
}
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle='Manage Users';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Users</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUser"><i class="fa fa-plus"></i> New User</button>
  </div>
  <?= flash('u') ?>
  <div class="card"><div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>#<?= $u['id'] ?></td><td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge bg-secondary text-uppercase"><?= $u['role'] ?></span></td>
          <td><?= htmlspecialchars($u['phone']) ?></td>
          <td><?= $u['status']==='active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
          <td><?= formatDate($u['created_at']) ?></td>
          <td>
            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#edit<?= $u['id'] ?>"><i class="fa fa-edit"></i></button>
            <form method="POST" class="d-inline" onsubmit="return confirmDelete('Delete this user?')">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <!-- Edit modal -->
        <div class="modal fade" id="edit<?= $u['id'] ?>"><div class="modal-dialog"><div class="modal-content">
          <form method="POST">
            <?= csrf_field() ?><input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <div class="modal-header"><h5>Edit User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input class="form-control mb-2" name="name" value="<?= htmlspecialchars($u['name']) ?>" required>
              <input class="form-control mb-2" type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
              <input class="form-control mb-2" name="phone" value="<?= htmlspecialchars($u['phone']) ?>">
              <select class="form-control mb-2" name="role">
                <?php foreach (['admin','staff','customer'] as $r): ?>
                  <option <?= $u['role']==$r?'selected':'' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
              <select class="form-control" name="status">
                <option value="active" <?= $u['status']==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $u['status']==='inactive'?'selected':'' ?>>Inactive</option>
              </select>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div></div>

<!-- New User Modal -->
<div class="modal fade" id="newUser"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <div class="modal-header"><h5>New User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input class="form-control mb-2" name="name" placeholder="Name" required>
      <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
      <input class="form-control mb-2" name="phone" placeholder="Phone">
      <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
      <select class="form-control" name="role">
        <option value="customer">Customer</option>
        <option value="staff">Staff</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
  </form>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
