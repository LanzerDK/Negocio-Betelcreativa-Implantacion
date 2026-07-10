<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\ApiResponse;

header('Content-Type: application/json');
SessionHelpers::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Método HTTP no permitido.', 405);
}

SessionHelpers::requireAuth();
CsrfHelper::validateRequestOrFail();

if (!isset($_FILES['category_image']) || $_FILES['category_image']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['category_image']['error'] ?? -1;
    ApiResponse::error("Error al subir el archivo (código: $code).");
}

$file = $_FILES['category_image'];

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    ApiResponse::error('Solo se permiten imágenes JPEG, PNG, GIF y WebP.');
}

$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    ApiResponse::error('La imagen no debe superar los 2 MB.');
}

$uploadDir = __DIR__ . '/../uploads/categories';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'category_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    ApiResponse::error('Error al mover el archivo subido.', 500);
}

$relativePath = 'uploads/categories/' . $filename;
$publicPath = 'Public/' . $relativePath;
$absoluteUrl = rtrim(APP_URL, '/') . '/' . $publicPath;

ApiResponse::success(['url' => $absoluteUrl, 'path' => $relativePath, 'publicPath' => $publicPath], 'Imagen subida correctamente.');
