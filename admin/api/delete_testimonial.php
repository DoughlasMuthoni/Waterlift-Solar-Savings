<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) { http_response_code(401); echo json_encode(['success'=>false]); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$id   = (int)($body['id'] ?? 0);
if (!$id) { http_response_code(422); echo json_encode(['success'=>false]); exit; }

$pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
echo json_encode(['success' => true]);
