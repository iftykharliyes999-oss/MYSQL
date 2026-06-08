<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');
$pageTitle = 'My Profile';
$u = current_user();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  db()->prepare('UPDATE users SET name=?, phone=? WHERE id=?')->execute([$_POST['name'], $_POST['phone'], $u['id']]);
  db()->prepare('UPDATE customers SET address=?, nid=? WHERE user_id=?')->execute([$_POST['address'], $_POST['nid'], $u['id']]);
  if (!empty($_POST['password']) && strlen($_POST['password']) >= 6) {
    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $u['id']]);
  }
  $_SESSION['user']['name'] = $_POST['name'];
  flash('Profile updated.');
  header('Location: ' . base_url('customer/profile.php')); exit;
}
$me = db()->prepare('SELECT u.*, c.address, c.nid, c.loyalty_points FROM users u LEFT JOIN customers c ON c.user_id=u.id WHERE u.id=?');
$me->execute([$u['id']]); $me = $me->fetch();
include __DIR__ . '/../includes/dash-header.php'; ?>
<div class="form-card" style="margin:0;max-width:640px">
  <h2>My Profile</h2>
  <form method="post"><?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group"><label>Name</label><input name="name" required value="<?= e($me['name']) ?>"></div>
      <div class="form-group"><label>Email</label><input value="<?= e($me['email']) ?>" disabled></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Phone</label><input name="phone" value="<?= e($me['phone']) ?>"></div>
      <div class="form-group"><label>NID</label><input name="nid" value="<?= e($me['nid']) ?>"></div>
    </div>
    <div class="form-group"><label>Address</label><textarea name="address" rows="2"><?= e($me['address']) ?></textarea></div>
    <div class="form-group"><label>New Password (leave blank to keep)</label><input type="password" name="password" minlength="6"></div>
    <button class="btn btn-primary">Save Changes</button>
  </form>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
