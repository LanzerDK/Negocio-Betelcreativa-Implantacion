<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Controllers\ControllerLogin;

SessionHelpers::start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControllerLogin::handleLogin();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    ControllerLogin::handleLogout();
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido. Use POST para iniciar sesión o DELETE para cerrar sesión.'
]);
exit;
