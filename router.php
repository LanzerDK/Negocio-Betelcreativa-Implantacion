<?php
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$projectRoot = dirname($scriptName);

// Extract the relative path after the project root to handle subdirectory installs
$relativePath = $path;
if ($projectRoot !== '/' && strpos($path, $projectRoot) === 0) {
    $relativePath = substr($path, strlen($projectRoot));
}
$relativePath = '/' . ltrim($relativePath, '/');

// Let API files be served directly
if (strpos($relativePath, '/api/') === 0) {
    $file = __DIR__ . '/Public' . $relativePath;
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
}

// For all other requests, use index.php as router
require __DIR__ . '/Public/index.php';
