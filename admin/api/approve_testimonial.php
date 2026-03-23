<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) { http_response_code(401); echo json_encode(['success'=>false]); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$id   = (int)($body['id'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['success'=>false]); exit; }

$fields = [];
$params = [];

if (array_key_exists('approved', $body)) {
    $fields[] = 'is_approved = ?';
    $params[]  = $body['approved'] ? 1 : 0;
}
if (array_key_exists('featured', $body)) {
    $fields[] = 'is_featured = ?';
    $params[]  = $body['featured'] ? 1 : 0;
}

if (empty($fields)) { echo json_encode(['success'=>false,'error'=>'Nothing to update']); exit; }

$fields[] = 'updated_at = NOW()';
$params[]  = $id;

$pdo->prepare("UPDATE testimonials SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
echo json_encode(['success' => true]);
