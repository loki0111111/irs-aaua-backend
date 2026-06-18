<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$query      = trim($_GET['q'] ?? '');
$department = trim($_GET['department'] ?? '');
$year       = trim($_GET['year'] ?? '');

try {
    $conditions = [];
    $params = [];
    $tsquery = null;

    if ($query) {
        // Split into words and add :* for prefix matching (live search)
        $words = preg_split('/\s+/', $query);
        $words = array_filter($words, fn($w) => strlen($w) > 0);
        $tsquery = implode(' & ', array_map(fn($w) => $w . ':*', $words));

        $conditions[] = "search_vector @@ to_tsquery('english', ?)";
        $params[] = $tsquery;
    }

    if ($department) {
    $conditions[] = "(d.name = ? OR p.department = ?)";
    $params[] = $department;
    $params[] = $department;
}

    if ($year) {
        $conditions[] = "year = ?";
        $params[] = (int)$year;
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $rankSelect = $tsquery
        ? ", ts_rank(search_vector, to_tsquery('english', ?)) AS rank"
        : '';
    $orderBy = $tsquery ? 'ORDER BY rank DESC' : 'ORDER BY year DESC';

    if ($tsquery) {
        array_unshift($params, $tsquery);
    }

    $sql = "SELECT p.id, p.title, p.authors, p.year, p.abstract, p.supervisor_id, p.department_id,
        COALESCE(d.name, p.department) as department,
        COALESCE(s.name, p.supervisor) as supervisor
        $rankSelect
        FROM projects p
        LEFT JOIN departments d ON p.department_id = d.id
        LEFT JOIN supervisors s ON p.supervisor_id = s.id
        $where
        $orderBy
        LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $results]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}