<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!validateToken($auth)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'data' => 'Department deleted']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}