<?php
/**
 * TEMPORARY DIAGNOSTIC SCRIPT
 * Visit: https://savings.waterliftsolar.africa/api/check.php
 * DELETE THIS FILE once everything is confirmed working.
 */

// Only allow access with a secret key to prevent public exposure
$secret = $_GET['key'] ?? '';
if ($secret !== 'wl-check-2024') {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden — add ?key=wl-check-2024 to the URL']));
}

header('Content-Type: application/json');

$report = [
    'php_version'   => PHP_VERSION,
    'php_ok'        => version_compare(PHP_VERSION, '8.0', '>='),
    'pdo_loaded'    => extension_loaded('pdo'),
    'pdo_mysql'     => extension_loaded('pdo_mysql'),
    'db_host'       => null,
    'db_name'       => null,
    'db_connect'    => false,
    'tables'        => [],
    'row_counts'    => [],
    'errors'        => [],
];

require_once __DIR__ . '/db_connect.php';

$report['db_host'] = DB_HOST;
$report['db_name'] = DB_NAME;

if (!$pdo) {
    $report['errors'][] = 'PDO connection failed — check DB_USER, DB_PASS, DB_NAME in api/db_connect.php';
    echo json_encode($report, JSON_PRETTY_PRINT);
    exit;
}

$report['db_connect'] = true;

// List tables
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $report['tables'] = $tables;

    foreach (['leads', 'packages', 'use_cases', 'testimonials'] as $tbl) {
        if (in_array($tbl, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
            $report['row_counts'][$tbl] = (int)$count;
        } else {
            $report['errors'][] = "Table '$tbl' does not exist — run waterlift_db.sql in phpMyAdmin";
        }
    }
} catch (PDOException $e) {
    $report['errors'][] = 'Query error: ' . $e->getMessage();
}

echo json_encode($report, JSON_PRETTY_PRINT);
