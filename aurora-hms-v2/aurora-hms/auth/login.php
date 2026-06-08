<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf'] ?? null);
  $email = clean_email($_POST['email'] ?? '');
  $pw = (string)($_POST['password'] ?? '');
  if (!$email || strlen($pw) < 6) {
    $error = 'Invalid email or password.';
  } else {
    $s = db()->prepare('SELECT u.id,u.name,u.email,u.password_hash,r.name AS role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=? AND u.is_active=1 AND u.deleted_at IS NULL LIMIT 1');
    $s->execute([$email]);
    $u = $s->fetch();
    if ($u && password_verify($pw, $u['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['user'] = ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
      audit('login');
      $dest = match($u['role']) { 'admin'=>'admin/dashboard.php','staff'=>'staff/dashboard.php', default=>'customer/dashboard.php' };
      header('Location: ' . base_url($dest)); exit;
    }
    $error = 'Invalid email or password.';
  }
}
$pageTitle = 'Sign In';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In · <?= e(HOTEL_NAME) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<div class="auth-split">
  <div class="auth-visual"><div><h2>Welcome back to Aurora Grand</h2><p>Sign in to manage your bookings, browse rooms, and enjoy a seamless luxury stay.</p></div></div>
  <div class="auth-side">
    <div class="form-card">
      <h2>Sign In</h2>
      <p class="sub">Access your Aurora Grand account</p>
      <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post"><?= csrf_field() ?>
        <div class="form-group"><label>Email</label><input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center">Sign In</button>
      </form>
      <p style="text-align:center;margin-top:18px;font-size:.9rem">No account? <a href="<?= base_url('auth/register.php') ?>" style="color:var(--gold-2);font-weight:600">Register</a></p>
      <div class="demo-creds">
        <strong>Demo logins:</strong>
        <div class="row"><span>Admin</span><span>admin@aurora.com / admin123</span></div>
        <div class="row"><span>Staff</span><span>sarah@aurora.com / staff123</span></div>
        <div class="row"><span>Customer</span><span>guest@aurora.com / guest123</span></div>
      </div>
      <p style="text-align:center;margin-top:14px"><a href="<?= base_url('index.php') ?>" class="muted">← Back to home</a></p>
    </div>
  </div>
</div></body></html>
