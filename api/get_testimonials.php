<?php
require_once __DIR__ . '/../admin/includes/db.php';
header('Content-Type: application/json');
header('Cache-Control: public, max-age=60');

$rows = $pdo->query(
    "SELECT id, name, location, package_label, stars, message, avatar_url
     FROM testimonials
     WHERE is_approved = 1
     ORDER BY is_featured DESC, sort_order ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Cast stars to int
foreach ($rows as &$r) {
    $r['stars'] = (int) $r['stars'];
}

echo json_encode(array_values($rows));
