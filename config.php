<?php
$host     = getenv('DB_HOST')     ?: 'localhost';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'irs_aaua';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'Rema123456@';
$sslmode  = getenv('DB_SSLMODE')  ?: 'disable';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

function validateToken($authHeader) {
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) return false;
    $token = substr($authHeader, 7);
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;
    $expectedSig = base64_encode(hash_hmac('sha256', "$header.$payload", 'IRS_AAUA_SECRET_KEY', true));
    if (!hash_equals($expectedSig, $signature)) return false;
    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return false;
    return true;
}