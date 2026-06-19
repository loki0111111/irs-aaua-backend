<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify token
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!validateToken($auth)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$title         = trim($_POST['title'] ?? '');
$authors       = trim($_POST['authors'] ?? '');
$department_id = (int)($_POST['department_id'] ?? 0);
$supervisor_id = (int)($_POST['supervisor_id'] ?? 0);
$year = trim($_POST['year'] ?? '');
$abstract      = trim($_POST['abstract'] ?? '');
$keywords      = trim($_POST['keywords'] ?? '');

if (!$title || !$authors || !$department_id || !$year) {
        echo json_encode(['success' => false, 'message' => 'Title, authors, department and year are required']);
    exit;
}

$pdfPath = trim($_POST['pdf_url'] ?? '');

try {
    $stmt = $pdo->prepare("
    INSERT INTO projects (title, authors, department_id, supervisor_id, year, abstract, keywords, pdf_path)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $title, $authors,
        $department_id ?: null,
        $supervisor_id ?: null,
        $year, $abstract, $keywords, $pdfPath
    ]);
    $newId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'data' => ['id' => $newId]]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}