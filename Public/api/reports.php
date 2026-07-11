<?php

// API de Reportes
// Punto de entrada HTTP para generar reportes del sistema
// Delega en ControllerReport según la acción solicitada

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerReport;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;

// Configura respuesta como JSON e inicia sesión
header('Content-Type: application/json');
SessionHelpers::start();

// Solo usuarios con rol super_admin pueden acceder a los reportes
if (($_SESSION['user_role'] ?? '') !== 'super_admin') {
    ApiResponse::error('Acceso denegado.', 403);
}

// Solo acepta peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido.', 405);
}

// Obtiene la acción solicitada y delega al controlador correspondiente
$action = trim($_GET['action'] ?? '');

match ($action) {
    'inventory'  => ControllerReport::inventory(),
    'movements'  => ControllerReport::movements(),
    'income'     => ControllerReport::income(),
    'purchases'  => ControllerReport::purchases(),
    default      => ApiResponse::error('Acción no válida.', 400),
};
