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
    echo json_encode(['success' => false, 'message' => 'Supervisor ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM supervisors WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'data' => 'Supervisor deleted']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function validateToken($authHeader) {
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) return false;

    $token = substr($authHeader, 7);
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$header, $payload, $signature] = $parts;

    $expectedSig = base64_encode(hash_hmac(
        'sha256',
        "$header.$payload",
        'IRS_AAUA_SECRET_KEY',
        true
    ));

    if (!hash_equals($expectedSig, $signature)) return false;

    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return false;

    return true;
}