<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../admin/includes/db.php';

if (!$pdo) { echo '[]'; exit; }

try {
    $rows = $pdo->query(
        "SELECT id, title, tag, description, stat_label, image_url
         FROM use_cases
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('[Waterlift] get_use_cases error: ' . $e->getMessage());
    echo '[]'; exit;
}

echo json_encode(array_values($rows));
