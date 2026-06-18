<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM supervisors ORDER BY name ASC");
    $supervisors = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $supervisors]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}