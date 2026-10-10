<?php
require_once 'headers.php';

$url = $_GET['url'] ?? '';
if (!$url) {
    echo json_encode(['success' => false, 'message' => 'No URL provided']);
    exit;
}

if (strpos($url, 'res.cloudinary.com') === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid URL']);
    exit;
}

// Use the project name if one was sent, otherwise fall back to the Cloudinary file name
$name = trim($_GET['name'] ?? '');
if ($name !== '') {
    // Remove characters that are not allowed in file names
    $name = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]/', '', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name));
    $name = mb_substr($name, 0, 150);
}

if ($name === '') {
    $name = basename(parse_url($url, PHP_URL_PATH));
}

if (!preg_match('/\.pdf$/i', $name)) {
    $name .= '.pdf';
}

// Plain ASCII fallback for old browsers, plus the proper UTF-8 version
$asciiName = preg_replace('/[^\x20-\x7E]/', '_', $name);

header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . rawurlencode($name));
header('Cache-Control: no-cache');

readfile($url);
exit;