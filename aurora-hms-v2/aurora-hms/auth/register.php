<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  $name = trim((string)($_POST['name'] ?? ''));
  $email = clean_email($_POST['email'] ?? '');
  $phone = trim((string)($_POST['phone'] ?? ''));
  $pw = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');
  if (strlen($name) < 2) $error = 'Name is too short.';
  elseif (!$email) $error = 'Invalid email.';
  elseif (strlen($pw) < 6) $error = 'Password must be at least 6 characters.';
  elseif ($pw !== $pw2) $error = 'Passwords do not match.';
  else {
    $chk = db()->prepare('SELECT 1 FROM users WHERE email=?');
    $chk->execute([$email]);
    if ($chk->fetch()) $error = 'Email already registered.';
    else {
      $hash = password_hash($pw, PASSWORD_BCRYPT);
      db()->beginTransaction();
      try {
        $ins = db()->prepare('INSERT INTO users (name,email,phone,password_hash,role_id) VALUES (?,?,?,?,3)');
        $ins->execute([$name,$email,$phone,$hash]);
        $uid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO customers (user_id) VALUES (?)')->execute([$uid]);
        db()->commit();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>$uid,'name'=>$name,'email'=>$email,'role'=>'customer'];
        audit('register');
        flash('Welcome to Aurora Grand!');
        header('Location: ' . base_url('customer/dashboard.php')); exit;
      } catch (Throwable $e) { db()->rollBack(); $error = 'Registration failed.'; }
    }
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register · <?= e(HOTEL_NAME) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<div class="auth-split">
  <div class="auth-visual"><div><h2>Join Aurora Grand</h2><p>Create your account and unlock exclusive member rates, loyalty rewards, and priority bookings.</p></div></div>
  <div class="auth-side">
    <div class="form-card">
      <h2>Create Account</h2>
      <p class="sub">Begin your journey with us</p>
      <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post"><?= csrf_field() ?>
        <div class="form-group"><label>Full Name</label><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></div>
        <div class="form-row">
          <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
          <div class="form-group"><label>Confirm</label><input type="password" name="password2" minlength="6" required></div>
        </div>
        <button class="btn btn-primary" style="width:100%;justify-content:center">Create Account</button>
      </form>
      <p style="text-align:center;margin-top:18px;font-size:.9rem">Have an account? <a href="<?= base_url('auth/login.php') ?>" style="color:var(--gold-2);font-weight:600">Sign in</a></p>
    </div>
  </div>
</div></body></html>
