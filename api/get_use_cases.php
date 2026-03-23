<?php
require_once __DIR__ . '/../admin/includes/db.php';
header('Content-Type: application/json');
header('Cache-Control: public, max-age=60');

$rows = $pdo->query(
    "SELECT id, title, tag, description, stat_label, image_url
     FROM use_cases
     WHERE is_active = 1
     ORDER BY sort_order ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array_values($rows));
