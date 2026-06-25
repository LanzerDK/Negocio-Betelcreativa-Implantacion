<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\TaskController;

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

TaskController::handleRequest();
