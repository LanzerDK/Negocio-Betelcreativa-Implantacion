<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Helpers\SessionHelpers;

header('Content-Type: application/json');

$userId = SessionHelpers::get('user_id');

echo json_encode([
    'success'  => true,
    'status'   => 'ok',
    'app_url'  => APP_URL,
    'app_name' => APP_NAME,
    'session'  => $userId !== null,
]);
