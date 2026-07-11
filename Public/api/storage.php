<?php

// API de Almacén (Inventario y Movimientos)
// Punto de entrada HTTP para ajustes de stock, movimientos y consultas de inventario
// Delega en StorageController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\StorageController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP y la acción solicitada
StorageController::handleRequest();
