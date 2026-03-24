<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file received.']);
    exit;
}

$file  = $_FILES['image'];
$error = $file['error'];

if ($error !== UPLOAD_ERR_OK) {
    $msgs = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    ];
    http_response_code(400);
    echo json_encode(['error' => $msgs[$error] ?? 'Upload error.']);
    exit;
}

// Validate MIME type by reading file header (not trusting $_FILES['type'])
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mimeType, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Only JPG, PNG, WebP and GIF images are allowed.']);
    exit;
}

// Max 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Image must be under 5 MB.']);
    exit;
}

// Determine upload folder — two levels up from admin/api/ → project root → images/use-cases/
$uploadDir = realpath(__DIR__ . '/../../') . '/images/use-cases/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Safe unique filename: timestamp + random + sanitised original name
$ext      = match($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
};
$basename = pathinfo($file['name'], PATHINFO_FILENAME);
$basename = preg_replace('/[^a-z0-9_-]/i', '-', $basename);
$basename = strtolower(substr($basename, 0, 40));
$filename = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '-' . $basename . '.' . $ext;
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not save the file. Check folder permissions.']);
    exit;
}

echo json_encode(['url' => '/images/use-cases/' . $filename]);
