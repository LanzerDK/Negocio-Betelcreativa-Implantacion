<?php
/**
 * Endpoint de verificación de salud (Health Check).
 * 
 * Devuelve información básica del estado de la aplicación: URL activa,
 * nombre del sistema y si existe una sesión de usuario activa.
 * Útil para monitoreo, balanceadores de carga y diagnóstico del servidor.
 */

// Cargar el autoloader de Composer y la configuración de la aplicación
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;

// Establecer el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Obtener el ID del usuario desde la sesión (null si no hay sesión activa)
$userId = SessionHelpers::get('user_id');

// Responder con el estado de la aplicación y la sesión
echo json_encode([
    'success'  => true,
    'status'   => 'ok',
    'app_url'  => APP_URL,
    'app_name' => APP_NAME,
    'session'  => $userId !== null,
]);
