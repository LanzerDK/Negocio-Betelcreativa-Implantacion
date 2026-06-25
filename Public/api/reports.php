<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerReport;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

header('Content-Type: application/json');
SessionHelpers::start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido.', 405);
}

$action = trim($_GET['action'] ?? '');

match ($action) {
    'inventory'  => ControllerReport::inventory(),
    'movements'  => ControllerReport::movements(),
    'income'     => ControllerReport::income(),
    'purchases'  => ControllerReport::purchases(),
    default      => ApiResponse::error('Acción no válida.', 400),
};
