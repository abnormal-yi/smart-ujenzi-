<?php
require_once __DIR__ . '/includes/functions.php';

$docId = (int)($_GET['id'] ?? 0);
if (!$docId) die('Invalid document');

$doc = runQuery("SELECT rd.*, cr.customer_id FROM request_documents rd JOIN customer_requests cr ON rd.request_id = cr.id WHERE rd.id = ?", [$docId]);
if (!$doc) die('Document not found');

$doc = $doc[0];
$userId = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';

$allowed = in_array($role, ['super_admin', 'admin', 'project_manager'])
    || ($role === 'client' && $doc['customer_id'] === $userId);

if (!$allowed) die('Access denied');

$filePath = __DIR__ . "/uploads/requests/{$doc['request_id']}/{$doc['file_name']}";
if (!file_exists($filePath)) die('File not found');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $doc['original_name'] . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
