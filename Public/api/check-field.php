<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Infrastructure\UserRepository;
use BetelCreativa\Helpers\ApiResponse;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Método no permitido.');
}

CsrfHelper::validateRequestOrFail();

$field = $_POST['field'] ?? '';
$value = trim($_POST['value'] ?? '');

if (empty($field) || empty($value)) {
    echo json_encode(['valid' => true]);
    exit;
}

$repo = new UserRepository();
$response = ['valid' => true, 'message' => ''];

switch ($field) {
    case 'usuario':
        if ($repo->existsByUsername($value)) {
            $response = ['valid' => false, 'message' => 'El nombre de usuario ya está registrado.'];
        }
        break;
    case 'correo':
        if ($repo->existsByEmail($value)) {
            $response = ['valid' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }
        break;
    case 'cedula':
        $tipoCedula = $_POST['tipo_cedula'] ?? '';
        $cedulaCompleta = $tipoCedula ? $tipoCedula . '-' . $value : $value;
        if ($repo->existsByIdNumber($cedulaCompleta)) {
            $response = ['valid' => false, 'message' => 'La cédula ya está registrada.'];
        }
        break;
}

echo json_encode($response);
