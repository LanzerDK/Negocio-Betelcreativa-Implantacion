<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Infrastructure\UserRepository;

header('Content-Type: application/json');
SessionHelpers::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Método HTTP no permitido.', 405);
}

SessionHelpers::requireAuth();
CsrfHelper::validateRequestOrFail();

$userId = (int)SessionHelpers::get('user_id');

// Validar que se haya enviado un archivo
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['avatar']['error'] ?? -1;
    ApiResponse::error("Error al subir el archivo (código: $code).");
}

$file = $_FILES['avatar'];

// Validar tipo MIME
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    ApiResponse::error('Solo se permiten imágenes JPEG, PNG, GIF y WebP.');
}

// Validar tamaño (máx 2 MB)
$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    ApiResponse::error('La imagen no debe superar los 2 MB.');
}

// Crear directorio si no existe
$uploadDir = __DIR__ . '/../uploads/avatars';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generar nombre único
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$destPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    ApiResponse::error('Error al mover el archivo subido.', 500);
}

// Guardar ruta relativa en la BD
$relativePath = 'uploads/avatars/' . $filename;
$repo = new UserRepository();

if ($repo->updateAvatar($userId, $relativePath)) {
    SessionHelpers::set('user_avatar', $relativePath);
    ApiResponse::success(['avatar_url' => $relativePath], 'Avatar actualizado correctamente.');
} else {
    // Limpiar archivo huérfano
    unlink($destPath);
    ApiResponse::error('Error al actualizar el avatar en la base de datos.', 500);
}
