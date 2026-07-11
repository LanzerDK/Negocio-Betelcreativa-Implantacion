<?php

/**
 * router.php — Router interno para el servidor PHP integrado (php -S).
 * Maneja el enrutamiento de peticiones del lado del servidor:
 * archivos estáticos, rutas API (/api/*.php) y vistas (index.php).
 */

/* Extrae la ruta de la petición actual */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = __DIR__;

/* Si la ruta apunta a un archivo estático existente, se sirve directamente */
$localPath = $publicDir . $uri;
if ($uri !== '/' && is_file($localPath)) {
    /* Solo sirve archivos no-PHP como estáticos (los .php se ejecutan) */
    $ext = pathinfo($localPath, PATHINFO_EXTENSION);
    if ($ext !== 'php') {
        return false;
    }
}

/* Rutas API (/api/archivo.php) → requiere el archivo PHP directamente */
if (preg_match('#^/api/([\w-]+\.php)$#', $uri, $m)) {
    $apiFile = $publicDir . $uri;
    if (is_file($apiFile)) {
        require $apiFile;
        return true;
    }
    /* Si el archivo de la API no existe, devuelve 404 JSON */
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
    return true;
}

/* Todas las demás rutas → se pasan como parámetro 'views' a index.php */
$_GET['views'] = trim($uri, '/');
require $publicDir . '/index.php';
