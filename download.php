<?php
require_once 'headers.php';

$filename = basename($_GET['file'] ?? '');
if (!$filename) {
    http_response_code(400);
    exit;
}

$filepath = __DIR__ . '/uploads/' . $filename;
if (!file_exists($filepath)) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;