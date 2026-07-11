<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Infrastructure\SettingsRepository;

header('Content-Type: application/json');
SessionHelpers::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Método HTTP no permitido.', 405);
}

SessionHelpers::requireAuth();

$role = SessionHelpers::get('user_role');
if ($role !== 'super_admin') {
    ApiResponse::error('Solo el Super Admin puede cambiar el logo del sistema.', 403);
}

CsrfHelper::validateRequestOrFail();

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['logo']['error'] ?? -1;
    ApiResponse::error("Error al subir el archivo (código: $code).");
}

$file = $_FILES['logo'];

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

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'logo.' . $ext;
$destPath = $uploadDir . '/' . $filename;

// Eliminar logo anterior si existe
if (is_file($destPath)) {
    unlink($destPath);
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    ApiResponse::error('Error al mover el archivo subido.', 500);
}

$relativePath = 'uploads/' . $filename;

$repo = new SettingsRepository();
$repo->set('system_logo', $relativePath);

$_SESSION['system_logo'] = $relativePath;

ApiResponse::success(['logo_url' => $relativePath], 'Logo del sistema actualizado correctamente.');
