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

$filename = basename(parse_url($url, PHP_URL_PATH));

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache');

readfile($url);
exit;