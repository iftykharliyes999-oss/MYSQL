<?php
require_once __DIR__ . '/../config/config.php';
if (isLoggedIn()) redirect(roleHomeUrl($_SESSION['user_role']));

$pdo = (new Database())->connect();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $email = sanitize($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=:e AND status='active' LIMIT 1");
    $stmt->execute([':e'=>$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['password'])) {
        login($u);
        redirect(roleHomeUrl($u['role']));
    } else {
        flash('login', 'Invalid email or password.', 'danger');
    }
}
$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<div class="container">
  <div class="card auth-card">
    <div class="card-body p-4">
      <h3 class="text-center mb-1"><i class="fa-solid fa-right-to-bracket text-primary"></i> Login</h3>
      <p class="text-center text-muted small">Sign in to your account</p>
      <?= flash('login') ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Email</label>
          <input class="form-control" name="email" type="email" required></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <input class="form-control" name="password" type="password" required></div>
        <button class="btn btn-primary w-100">Login</button>
        <p class="text-center mt-3 small">No account? <a href="register.php">Register</a></p>
        <div class="alert alert-info small mt-2 mb-0">
          <strong>Demo Logins (password: <code>password123</code>):</strong><br>
          admin@hotel.com · staff@hotel.com · customer@hotel.com
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
