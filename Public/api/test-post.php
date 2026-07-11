<?php

// Endpoint de prueba para verificar que POST + CSRF + Sesión funcionan correctamente
// Devuelve los datos recibidos y el ID de sesión actual

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Use POST']);
    exit;
}

CsrfHelper::validateRequestOrFail();

echo json_encode([
    'success' => true,
    'message' => 'POST + CSRF + Sesión funcionan correctamente',
    'post_data' => $_POST,
    'session_id' => session_id(),
]);
