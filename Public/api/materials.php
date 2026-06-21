<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\MaterialController;

header('Content-Type: application/json');

MaterialController::handleRequest();
