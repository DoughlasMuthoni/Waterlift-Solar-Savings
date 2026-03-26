<?php
// ── Production  credentials ─────────────────────────
// define('DB_HOST',    'localhost');
// define('DB_NAME',    'waterlif_waterliftsolar_savings');
// define('DB_USER',    'waterlif_savings');
// define('DB_PASS',    'WaterliftsolarSavings');  
// define('DB_CHARSET', 'utf8mb4');

// Local host credentials
define('DB_HOST',    'localhost');
define('DB_NAME',    'waterlift_db');
define('DB_USER',    'root');
define('DB_PASS',    'mwalatvc'); 
define('DB_CHARSET', 'utf8mb4');

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('[Waterlift] DB connection failed: ' . $e->getMessage());
}
