<?php

// Endpoint API para la administracion de usuarios del sistema
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../Config/app.php';

use BetelCreativa\Controllers\ControllerAdminUsers;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

// Configura respuesta JSON e inicia sesion
header('Content-Type: application/json');
SessionHelpers::start();

// Obtiene metodo HTTP y accion solicitada
$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

// Enrutador que delega segun metodo y accion al controlador de administracion de usuarios
switch ($method) {
    case 'GET':
        match ($action) {
            'list' => ControllerAdminUsers::list(),
            'get'  => ControllerAdminUsers::get(),
            default => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    case 'POST':
        match ($action) {
            'create' => ControllerAdminUsers::create(),
            default  => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    case 'PUT':
        match ($action) {
            'role'          => ControllerAdminUsers::updateRole(),
            'toggle-active' => ControllerAdminUsers::toggleActive(),
            default         => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    default:
        ApiResponse::error('Método HTTP no permitido.', 405);
}
