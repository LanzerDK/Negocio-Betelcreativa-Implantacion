<?php

// =============================================
// API de Clientes
// Punto de entrada HTTP para todas las operaciones
// CRUD de clientes. Delega en CustomerController.
// =============================================

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\CustomerController;

header('Content-Type: application/json');

CustomerController::handleRequest();
