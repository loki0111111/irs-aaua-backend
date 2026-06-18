<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!validateToken($auth)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.authors, p.year, p.abstract, p.pdf_path,
               p.department_id, p.supervisor_id,
               COALESCE(d.name, p.department) as department,
               COALESCE(s.name, p.supervisor) as supervisor,
               p.created_at
        FROM projects p
        LEFT JOIN departments d ON p.department_id = d.id
        LEFT JOIN supervisors s ON p.supervisor_id = s.id
        ORDER BY p.created_at DESC
    ");
    $projects = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $projects]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}