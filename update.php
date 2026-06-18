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

$id            = (int)($_POST['id'] ?? 0);
$title         = trim($_POST['title'] ?? '');
$authors       = trim($_POST['authors'] ?? '');
$department_id = (int)($_POST['department_id'] ?? 0);
$supervisor_id = (int)($_POST['supervisor_id'] ?? 0);
$year          = (int)($_POST['year'] ?? 0);
$abstract      = trim($_POST['abstract'] ?? '');
$keywords      = trim($_POST['keywords'] ?? '');

if (!$id || !$title || !$authors || !$department_id || !$year) {
    echo json_encode(['success' => false, 'message' => 'ID, title, authors, department and year are required']);
    exit;
}

try {
    // Check project exists
    $stmt = $pdo->prepare("SELECT pdf_path FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    $pdfPath = $existing['pdf_path'];

    // Handle new PDF upload if provided
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']);
            exit;
        }

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Delete old PDF
        if ($pdfPath && file_exists($uploadDir . $pdfPath)) {
            unlink($uploadDir . $pdfPath);
        }

        $filename = uniqid('proj_') . '.pdf';
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $pdfPath = $filename;
        }
    }

    $stmt = $pdo->prepare("
    UPDATE projects
    SET title = ?, authors = ?, department_id = ?, supervisor_id = ?,
        year = ?, abstract = ?, keywords = ?, pdf_path = ?
    WHERE id = ?
    ");
    $stmt->execute([
        $title, $authors,
        $department_id ?: null,
        $supervisor_id ?: null,
        $year, $abstract, $keywords, $pdfPath, $id
    ]);

    echo json_encode(['success' => true, 'data' => 'Project updated']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}