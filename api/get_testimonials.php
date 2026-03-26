<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../admin/includes/db.php';

if (!$pdo) { echo '[]'; exit; }

try {
    $rows = $pdo->query(
        "SELECT id, name, location, package_label, stars, message, avatar_url
         FROM testimonials
         WHERE is_approved = 1
         ORDER BY is_featured DESC, sort_order ASC, id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('[Waterlift] get_testimonials error: ' . $e->getMessage());
    echo '[]'; exit;
}

foreach ($rows as &$r) {
    $r['stars'] = (int) $r['stars'];
}

echo json_encode(array_values($rows));
