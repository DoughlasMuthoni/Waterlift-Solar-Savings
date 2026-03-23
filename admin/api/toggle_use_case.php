<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) { http_response_code(401); echo json_encode(['success'=>false]); exit; }

$body   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($body['id']     ?? 0);
$active = (int)($body['active'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['success'=>false]); exit; }

$pdo->prepare("UPDATE use_cases SET is_active = ?, updated_at = NOW() WHERE id = ?")->execute([$active ? 1 : 0, $id]);
echo json_encode(['success' => true]);
