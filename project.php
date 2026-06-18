<?php
require_once 'config.php';
require_once 'headers.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

// GET single project
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   COALESCE(d.name, p.department) as department,
                   COALESCE(s.name, p.supervisor) as supervisor
            FROM projects p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN supervisors s ON p.supervisor_id = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $project]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}

// DELETE project
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Verify token
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? '';
    if (!validateToken($auth)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        // Delete PDF file if it exists
        $stmt = $pdo->prepare("SELECT pdf_path FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if ($project && $project['pdf_path']) {
            $filePath = __DIR__ . '/uploads/' . $project['pdf_path'];
            if (file_exists($filePath)) unlink($filePath);
        }

        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'data' => 'Project deleted']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}