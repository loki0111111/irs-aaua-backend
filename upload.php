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

$pdfPath = null;

// Handle PDF upload
if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']);
        exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = uniqid('proj_') . '.pdf';
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        $pdfPath = $filename;
    }
}

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