<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false]); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$id     = (int)($body['id']     ?? 0);
$status = trim($body['status']  ?? '');
$notes  = trim($body['notes']   ?? '');

$allowed = ['new','contacted','qualified','converted','lost'];
if (!$id || !in_array($status, $allowed, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// If notes not provided in payload, keep existing
if ($notes === '') {
    $pdo->prepare("UPDATE leads SET status=?, updated_at=NOW() WHERE id=?")->execute([$status, $id]);
} else {
    $pdo->prepare("UPDATE leads SET status=?, notes=?, updated_at=NOW() WHERE id=?")->execute([$status, $notes, $id]);
}

echo json_encode(['success' => true]);
