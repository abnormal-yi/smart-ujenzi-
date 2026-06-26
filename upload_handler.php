<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

if (!$requestId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request ID']);
    exit;
}

if (in_array($role, ['client', 'customer'])) {
    $req = runQuery("SELECT id FROM customer_requests WHERE id = ? AND customer_id = ?", [$requestId, $userId]);
    if (!$req) {
        http_response_code(403);
        echo json_encode(['error' => 'Not your request']);
        exit;
    }
}

if (empty($_FILES['documents'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No files uploaded']);
    exit;
}

$uploadDir = __DIR__ . "/uploads/requests/$requestId";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'dwg', 'dxf'];
$maxSize = 20 * 1024 * 1024;
$uploaded = [];

$files = $_FILES['documents'];
$fileCount = is_array($files['name']) ? count($files['name']) : 1;

for ($i = 0; $i < $fileCount; $i++) {
    $origName = $fileCount > 1 ? $files['name'][$i] : $files['name'];
    $tmpName = $fileCount > 1 ? $files['tmp_name'][$i] : $files['tmp_name'];
    $size = $fileCount > 1 ? $files['size'][$i] : $files['size'];
    $error = $fileCount > 1 ? $files['error'][$i] : $files['error'];

    if ($error !== UPLOAD_ERR_OK) continue;
    if ($size > $maxSize) continue;

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) continue;

    $safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = "$uploadDir/$safeName";

    if (move_uploaded_file($tmpName, $dest)) {
        executeQuery(
            "INSERT INTO request_documents (request_id, file_name, original_name, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)",
            [$requestId, $safeName, $origName, $size, $ext, $userId]
        );
        $uploaded[] = ['name' => $origName, 'file' => $safeName];
    }
}

echo json_encode(['success' => true, 'uploaded' => $uploaded]);
