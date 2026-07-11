<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\WarehouseController;

header('Content-Type: application/json');

WarehouseController::handleRequest();
