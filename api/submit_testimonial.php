<?php
require_once __DIR__ . '/../admin/includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$name    = trim($body['name']    ?? '');
$message = trim($body['message'] ?? '');
$stars   = max(1, min(5, (int)($body['stars'] ?? 5)));
$loc     = trim($body['location']      ?? '');
$pkg     = trim($body['packageLabel']  ?? '');

if (!$name || !$message) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Name and message are required']);
    exit;
}

$pdo->prepare(
    "INSERT INTO testimonials (name, location, package_label, stars, message, is_approved)
     VALUES (?, ?, ?, ?, ?, 0)"
)->execute([$name, $loc ?: null, $pkg ?: null, $stars, $message]);

echo json_encode(['success' => true]);
