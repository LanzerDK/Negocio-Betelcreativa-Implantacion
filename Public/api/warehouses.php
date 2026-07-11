<?php

// API de Almacenes (Bodegas)
// Punto de entrada HTTP para todas las operaciones CRUD de almacenes
// Delega en WarehouseController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\WarehouseController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP y los parámetros recibidos
WarehouseController::handleRequest();
