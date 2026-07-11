<?php

// API de Clientes
// Punto de entrada HTTP para todas las operaciones CRUD de clientes
// Delega en CustomerController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\CustomerController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP
CustomerController::handleRequest();
