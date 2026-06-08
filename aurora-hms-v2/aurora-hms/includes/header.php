<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/security.php';
$user = current_user();
$role = $user['role'] ?? null;
$flash = take_flash();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? HOTEL_NAME) ?> · <?= e(HOTEL_NAME) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="topbar">
  <div class="container nav">
    <a class="brand" href="<?= base_url('index.php') ?>">
      <i class="fa-solid fa-hotel"></i> <span><?= e(HOTEL_NAME) ?></span>
    </a>
    <nav class="nav-links">
      <a href="<?= base_url('index.php') ?>">Home</a>
      <a href="<?= base_url('rooms.php') ?>">Rooms</a>
      <a href="<?= base_url('about.php') ?>">About</a>
      <a href="<?= base_url('contact.php') ?>">Contact</a>
      <?php if ($role === 'admin'): ?>
        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn-gold">Admin</a>
      <?php elseif ($role === 'staff'): ?>
        <a href="<?= base_url('staff/dashboard.php') ?>" class="btn-gold">Staff</a>
      <?php elseif ($role === 'customer'): ?>
        <a href="<?= base_url('customer/dashboard.php') ?>" class="btn-gold">My Account</a>
      <?php endif; ?>
      <?php if ($user): ?>
        <a href="<?= base_url('auth/logout.php') ?>" class="btn-ghost"><i class="fa-solid fa-right-from-bracket"></i></a>
      <?php else: ?>
        <a href="<?= base_url('auth/login.php') ?>" class="btn-ghost">Login</a>
        <a href="<?= base_url('auth/register.php') ?>" class="btn-gold">Sign Up</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['t']) ?>"><div class="container"><?= e($flash['m']) ?></div></div>
<?php endif; ?>
<main>
