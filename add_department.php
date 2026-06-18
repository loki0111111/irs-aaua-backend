<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!validateToken($auth)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Department name required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->execute([$name]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name]]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Department already exists']);
}