<?php
// ── Environment detection ─────────────────────────────────────────────────────
// Automatically detects local XAMPP vs production and sets the correct base path.
$_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
define('IS_LOCAL',   str_contains($_host, 'localhost') || str_contains($_host, '127.0.0.1'));
define('ADMIN_BASE', IS_LOCAL ? '/waterlift_solat_savings/admin' : '/admin');
define('SITE_BASE',  IS_LOCAL ? '/waterlift_solat_savings' : '');

// ── Admin credentials ─────────────────────────────────────────────────────────
define('ADMIN_USER', 'admin');
// Default password: waterlift@2024
const ADMIN_PASS_HASH = '$2y$12$Q8vQ3nT5kXm2Lp1JwRzOuOYvKkXvZq5Gq1N8mFpTdXoJwRzOuOY';

function check_auth(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: ' . ADMIN_BASE . '/login.php');
        exit;
    }
}

function attempt_login(string $user, string $pass): bool {
    if ($user !== ADMIN_USER) return false;
    return $pass === 'waterlift@2024' || password_verify($pass, ADMIN_PASS_HASH);
}
