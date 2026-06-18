<?php
require_once 'config.php';
require_once 'headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Generate JWT manually (no library needed)
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'id' => $admin['id'],
        'username' => $admin['username'],
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ]));
    $signature = base64_encode(hash_hmac(
        'sha256',
        "$header.$payload",
        'IRS_AAUA_SECRET_KEY',
        true
    ));
    $token = "$header.$payload.$signature";

    echo json_encode(['success' => true, 'data' => ['token' => $token]]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}