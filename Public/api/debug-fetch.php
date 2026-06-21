<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;

header('Content-Type: application/json');

$userId = SessionHelpers::get('user_id');
if ($userId === null) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

echo json_encode([
    'success' => true,
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'post' => $_POST,
]);
