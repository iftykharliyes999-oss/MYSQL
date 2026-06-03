<?php
/** Aurora Grand · Customer registration (always role = customer) */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $name     = clean($_POST['name'] ?? '');
    $email    = clean_email($_POST['email'] ?? '');
    $phone    = clean($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (strlen($name) < 2 || !$email || strlen($password) < 6) {
        $error = 'Please fill all required fields correctly.';
    } else {
        $check = db()->prepare('SELECT 1 FROM users WHERE email = :e LIMIT 1');
        $check->execute([':e' => $email]);
        if ($check->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo  = db();
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare('INSERT INTO users (name,email,phone,password_hash,role_id) VALUES (:n,:e,:p,:h,3)');
                $ins->execute([':n' => $name, ':e' => $email, ':p' => $phone, ':h' => $hash]);
                $uid = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO customers (user_id) VALUES (:u)')->execute([':u' => $uid]);
                $pdo->commit();
                header('Location: login.php?registered=1');
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = 'Registration failed.';
            }
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register · Aurora Grand</title></head>
<body>
  <h1>Create guest account</h1>
  <?php if ($error): ?><p style="color:#c00"><?= clean($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <p><label>Full name <input name="name" required></label></p>
    <p><label>Email <input type="email" name="email" required></label></p>
    <p><label>Phone <input name="phone"></label></p>
    <p><label>Password (6+ chars) <input type="password" name="password" minlength="6" required></label></p>
    <button>Create account</button>
  </form>
</body></html>