<?php
/**
 * Aurora Grand · Hardened session + security helpers
 * - Session fixation guard
 * - HttpOnly + SameSite cookies
 * - XSS sanitiser (htmlspecialchars)
 * - Role-based access control
 * - CSRF tokens
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
    if (!isset($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
    }
}

function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function clean_email(string $v): string {
    $v = filter_var(trim($v), FILTER_SANITIZE_EMAIL);
    return filter_var($v, FILTER_VALIDATE_EMAIL) ? strtolower($v) : '';
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): void {
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('CSRF token invalid.');
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: /php-backend/auth/login.php');
        exit;
    }
}

function require_role(string ...$roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        header('Location: /php-backend/unauthorized.php');
        exit;
    }
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}