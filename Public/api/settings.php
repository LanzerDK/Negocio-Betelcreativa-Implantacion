<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerSystem;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

header('Content-Type: application/json');
SessionHelpers::start();

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

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
