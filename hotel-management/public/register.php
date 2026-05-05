<?php
require_once __DIR__ . '/../config/config.php';
if (isLoggedIn()) redirect(roleHomeUrl($_SESSION['user_role']));

$pdo = (new Database())->connect();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $name  = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $pass  = $_POST['password'];
    $cpass = $_POST['cpassword'];

    if (strlen($pass) < 6) {
        flash('reg','Password must be at least 6 characters.','danger');
    } elseif ($pass !== $cpass) {
        flash('reg','Passwords do not match.','danger');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:e");
        $stmt->execute([':e'=>$email]);
        if ($stmt->fetch()) {
            flash('reg','Email already registered.','danger');
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name,email,password,role,phone) VALUES (?,?,?,'customer',?)");
            $ins->execute([$name,$email,$hash,$phone]);
            flash('login','Registration successful! Please login.','success');
            redirect(BASE_URL . '/public/login.php');
        }
    }
}
$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<div class="container">
  <div class="card auth-card">
    <div class="card-body p-4">
      <h3 class="text-center mb-1"><i class="fa-solid fa-user-plus text-primary"></i> Create Account</h3>
      <?= flash('reg') ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-2"><label>Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label>Email</label><input type="email" class="form-control" name="email" required></div>
        <div class="mb-2"><label>Phone</label><input class="form-control" name="phone"></div>
        <div class="mb-2"><label>Password</label><input type="password" class="form-control" name="password" required></div>
        <div class="mb-3"><label>Confirm Password</label><input type="password" class="form-control" name="cpassword" required></div>
        <button class="btn btn-primary w-100">Register</button>
        <p class="text-center mt-3 small">Already have an account? <a href="login.php">Login</a></p>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
