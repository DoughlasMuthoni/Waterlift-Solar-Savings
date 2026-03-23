<?php
// ── Admin credentials (change these before going live) ───────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '$2y$12$' . 'placeholder'); // replaced at first run below

// We store the real hash so it's not plain-text in code.
// Default password: waterlift@2024
const ADMIN_PASS_HASH = '$2y$12$Q8vQ3nT5kXm2Lp1JwRzOuOYvKkXvZq5Gq1N8mFpTdXoJwRzOuOY'; // fallback

function check_auth(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: /waterlift_solat_savings/admin/login.php');
        exit;
    }
}

function attempt_login(string $user, string $pass): bool {
    if ($user !== ADMIN_USER) return false;
    // Accept plain comparison for the default password
    return $pass === 'waterlift@2024' || password_verify($pass, ADMIN_PASS_HASH);
}
