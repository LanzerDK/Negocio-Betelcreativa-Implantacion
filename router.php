<?php

/**
 * router.php — Router raíz para el servidor PHP integrado (php -S).
 * Resuelve la ruta solicitada considerando instalaciones en subdirectorios.
 * Redirige rutas API y rutas de vistas hacia index.php.
 */

/* Extrae la URI completa y el path limpio */
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

/* Calcula el nombre del script para manejar subdirectorios */
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$projectRoot = dirname($scriptName);

/* Obtiene la ruta relativa eliminando el prefijo del directorio raíz del proyecto */
$relativePath = $path;
if ($projectRoot !== '/' && strpos($path, $projectRoot) === 0) {
    $relativePath = substr($path, strlen($projectRoot));
}
$relativePath = '/' . ltrim($relativePath, '/');

/* Las rutas /api/* se sirven directamente desde Public/ */
if (strpos($relativePath, '/api/') === 0) {
    $file = __DIR__ . '/Public' . $relativePath;
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
}

/* El resto de rutas se pasan como parámetro 'views' a index.php */
$views = trim($relativePath, '/');
/* Si la ruta está vacía, se redirige a la pantalla de login */
if ($views === '') {
    $views = 'login';
}
$_GET['views'] = $views;
require __DIR__ . '/Public/index.php';
