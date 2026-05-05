<?php
require_once __DIR__ . '/../config/config.php';
requireRole('customer');
$pdo=(new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action=$_POST['action']??'';
    if ($action==='update') {
        $pdo->prepare("UPDATE users SET name=?,phone=?,address=?,nid=? WHERE id=?")
            ->execute([sanitize($_POST['name']),sanitize($_POST['phone']),sanitize($_POST['address']),
                       sanitize($_POST['nid']),$_SESSION['user_id']]);
        $_SESSION['user_name']=sanitize($_POST['name']);
        flash('pr','Profile updated.','success');
    } elseif ($action==='password') {
        $cur=$_POST['current']; $new=$_POST['new'];
        $u=$pdo->prepare("SELECT password FROM users WHERE id=?"); $u->execute([$_SESSION['user_id']]); $row=$u->fetch();
        if (!password_verify($cur,$row['password'])) {
            flash('pr','Current password incorrect.','danger');
        } elseif (strlen($new)<6) {
            flash('pr','New password too short.','danger');
        } else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                ->execute([password_hash($new,PASSWORD_DEFAULT),$_SESSION['user_id']]);
            flash('pr','Password changed.','success');
        }
    }
    redirect($_SERVER['PHP_SELF']);
}
$u=$pdo->prepare("SELECT * FROM users WHERE id=?"); $u->execute([$_SESSION['user_id']]); $user=$u->fetch();
$pageTitle='Profile';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_customer.php'; ?>
<div class="main-content">
  <h3>My Profile</h3>
  <?= flash('pr') ?>
  <div class="row g-3">
    <div class="col-md-7">
      <div class="card p-4"><h5>Personal Info</h5>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="update">
        <label>Name</label><input class="form-control mb-2" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        <label>Email</label><input class="form-control mb-2" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        <label>Phone</label><input class="form-control mb-2" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
        <label>Address</label><textarea class="form-control mb-2" name="address"><?= htmlspecialchars($user['address']) ?></textarea>
        <label>NID / Passport</label><input class="form-control mb-3" name="nid" value="<?= htmlspecialchars($user['nid']) ?>">
        <button class="btn btn-primary">Save Changes</button>
      </form>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card p-4"><h5>Change Password</h5>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <input class="form-control mb-2" type="password" name="current" placeholder="Current Password" required>
        <input class="form-control mb-3" type="password" name="new" placeholder="New Password" required>
        <button class="btn btn-warning">Update Password</button>
      </form>
      </div>
    </div>
  </div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
