<?php
/** Aurora Grand · Session, CSRF, RBAC, sanitisation helpers */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
    if (!isset($_SESSION['_init'])) { session_regenerate_id(true); $_SESSION['_init'] = true; }
}

function e(string $v): string { return htmlspecialchars(trim($v), ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
function clean_email(string $v): string {
    $v = filter_var(trim($v), FILTER_SANITIZE_EMAIL);
    return filter_var($v, FILTER_VALIDATE_EMAIL) ? strtolower($v) : '';
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(?string $token): void {
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) { http_response_code(419); exit('Invalid CSRF token.'); }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!current_user()) { header('Location: ' . base_url('auth/login.php')); exit; }
}
function require_role(string ...$roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        header('Location: ' . base_url('unauthorized.php')); exit;
    }
}
function flash(string $msg, string $type = 'success'): void { $_SESSION['flash'] = ['m' => $msg, 't' => $type]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function money($n): string { return CURRENCY . number_format((float)$n, 2); }
function audit(string $action, ?string $entity = null, ?int $entity_id = null): void {
    try {
        $u = current_user();
        $s = db()->prepare('INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address) VALUES (?,?,?,?,?)');
        $s->execute([$u['id'] ?? null, $action, $entity, $entity_id, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Throwable $e) {}
}
