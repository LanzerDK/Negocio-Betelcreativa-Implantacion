<?php

// API de Materiales (Inventario)
// Punto de entrada HTTP para todas las operaciones CRUD de materiales
// Delega en MaterialController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\MaterialController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP y la acción solicitada
MaterialController::handleRequest();
