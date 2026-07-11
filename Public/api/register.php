<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Controllers\ControllerRegister;

SessionHelpers::start();

if (($_SESSION['user_role'] ?? '') !== 'super_admin') {
    ApiResponse::error('Acceso denegado.', 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControllerRegister::registerSecurityCodeFromPost();
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido. Use POST.'
]);
exit;
