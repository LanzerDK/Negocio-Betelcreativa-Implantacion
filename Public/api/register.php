<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\ControllerRegister;

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
