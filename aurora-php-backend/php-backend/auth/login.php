<?php
/** Aurora Grand · Login endpoint (PDO prepared, hashed passwords) */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $email = clean_email($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!$email || strlen($password) < 6) {
        $error = 'Invalid credentials.';
    } else {
        $stmt = db()->prepare('SELECT u.id, u.name, u.email, u.password_hash, r.name AS role
                               FROM users u JOIN roles r ON r.id = u.role_id
                               WHERE u.email = :e AND u.is_active = 1 AND u.deleted_at IS NULL LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int)$user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            // audit log
            $log = db()->prepare('INSERT INTO audit_logs (user_id, action, ip_address) VALUES (:u, :a, :ip)');
            $log->execute([':u' => $user['id'], ':a' => 'login', ':ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

            $dest = match ($user['role']) {
                'admin'    => '/php-backend/admin/dashboard.php',
                'staff'    => '/php-backend/staff/housekeeping.php',
                default    => '/php-backend/customer/rooms.php',
            };
            header('Location: ' . $dest);
            exit;
        }
        $error = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Login · Aurora Grand</title></head>
<body>
  <h1>Aurora Grand · Sign in</h1>
  <?php if ($error): ?><p style="color:#c00"><?= clean($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <p><label>Email <input type="email" name="email" required></label></p>
    <p><label>Password <input type="password" name="password" minlength="6" required></label></p>
    <p><button type="submit">Sign in</button></p>
  </form>
  <p><a href="register.php">Create account</a></p>
</body></html>