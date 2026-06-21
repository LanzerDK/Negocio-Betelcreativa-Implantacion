<?php
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Let API files be served directly
if (strpos($path, '/api/') === 0) {
    $file = __DIR__ . '/Public' . $path;
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
}

// For all other requests, use index.php as router
require __DIR__ . '/Public/index.php';
