<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerUser;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

header('Content-Type: application/json');
SessionHelpers::start();

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

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
