<?php
require_once __DIR__ . '/includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();
header('Location: ' . ADMIN_BASE . '/login.php');
exit;
