<?php
require_once __DIR__ . '/../includes/security.php';
logout();
header('Location: login.php');
exit;