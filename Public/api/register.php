<?php
/**
 * API de Registro — Permite registrar nuevos usuarios mediante un código de seguridad.
 * 
 * Solo los usuarios con rol 'super_admin' pueden acceder.
 * POST /api/register.php → Registrar usuario (ControllerRegister::registerSecurityCodeFromPost)
 * Otros métodos → Responde con error 405 "Método no permitido"
 */

// Cargar el autoloader de Composer y la configuración de la aplicación
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Controllers\ControllerRegister;

// Iniciar la sesión PHP para validar permisos del usuario
SessionHelpers::start();

// --- Verificar que el usuario autenticado tenga permisos de super_admin ---
if (($_SESSION['user_role'] ?? '') !== 'super_admin') {
    ApiResponse::error('Acceso denegado.', 403);
}

// --- Manejar solicitud de registro de usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControllerRegister::registerSecurityCodeFromPost();
    exit;
}

// --- Método HTTP no soportado: responder con error ---
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido. Use POST.'
]);
exit;
