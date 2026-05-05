<?php
/**
 * Authentication & Role Authorization
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        flash('login', 'Please login to continue.', 'warning');
        redirect(BASE_URL . '/public/login.php');
    }
}

function requireRole($roles) {
    requireLogin();
    if (!is_array($roles)) $roles = [$roles];
    if (!in_array($_SESSION['user_role'], $roles)) {
        http_response_code(403);
        die('<h2>403 Forbidden</h2><p>You do not have permission to access this page.</p>');
    }
}

function login($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

function logout() {
    session_unset();
    session_destroy();
}

function roleHomeUrl($role) {
    switch ($role) {
        case 'admin':    return BASE_URL . '/admin/dashboard.php';
        case 'staff':    return BASE_URL . '/staff/dashboard.php';
        case 'customer': return BASE_URL . '/customer/dashboard.php';
        default:         return BASE_URL . '/public/index.php';
    }
}
