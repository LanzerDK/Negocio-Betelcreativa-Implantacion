<?php

// API de Configuración del Sistema
// Punto de entrada HTTP para obtener y actualizar ajustes del sistema
// Delega en ControllerSystem según el método HTTP y la acción solicitada

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerSystem;
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
            'list' => ControllerSystem::list(),
            'get'  => ControllerSystem::get(),
            default => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    case 'PUT':
        match ($action) {
            'update'       => ControllerSystem::update(),
            'batch-update' => ControllerSystem::batchUpdate(),
            default        => ApiResponse::error('Acción no válida.', 400),
        };
        break;

    default:
        ApiResponse::error('Método HTTP no permitido.', 405);
}
