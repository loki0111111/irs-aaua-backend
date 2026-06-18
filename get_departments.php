<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
    $departments = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $departments]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}