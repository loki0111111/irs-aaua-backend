<?php
require_once 'headers.php';

$url = $_GET['url'] ?? '';
if (!$url) {
    http_response_code(400);
    exit;
}

header('Location: ' . $url);
exit;