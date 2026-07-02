<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = __DIR__;

// Serve existing static files directly from Public/
$localPath = $publicDir . $uri;
if ($uri !== '/' && is_file($localPath)) {
    // Only serve non-PHP files as static
    $ext = pathinfo($localPath, PATHINFO_EXTENSION);
    if ($ext !== 'php') {
        return false;
    }
}

// API routes → require the PHP file
if (preg_match('#^/api/([\w-]+\.php)$#', $uri, $m)) {
    $apiFile = $publicDir . $uri;
    if (is_file($apiFile)) {
        require $apiFile;
        return true;
    }
    // API file not found
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
    return true;
}

// Everything else → index.php
$_GET['views'] = trim($uri, '/');
require $publicDir . '/index.php';
