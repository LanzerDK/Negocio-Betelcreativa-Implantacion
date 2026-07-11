<?php

// API de Ubicaciones (Almacenes / Depósitos)
// Punto de entrada HTTP para todas las operaciones CRUD de ubicaciones
// Delega en LocationController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\LocationController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP
LocationController::handleRequest();
