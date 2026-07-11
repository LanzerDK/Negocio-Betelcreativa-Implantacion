<?php

// API de Usuarios
// Punto de entrada HTTP para la gestión de perfiles de usuario
// Delega en ControllerUser y ControllerAdminUsers según la acción solicitada

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerUser;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

// Configura respuesta como JSON e inicia sesión
header('Content-Type: application/json');
SessionHelpers::start();

// Obtiene el método HTTP y la acción solicitada
$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

// Enruta según el método HTTP y delega al controlador correspondiente
switch ($method) {
    case 'GET':
        match ($action) {
            'profile'      => ControllerUser::getProfile(),
            'preferences'  => ControllerUser::getPreferences(),
            default        => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    case 'PUT':
        match ($action) {
            'profile'         => ControllerUser::updateProfile(),
            'change-password' => ControllerUser::changePassword(),
            'preferences'     => ControllerUser::savePreferences(),
            default           => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    default:
        ApiResponse::error('Método HTTP no permitido.', 405);
}
