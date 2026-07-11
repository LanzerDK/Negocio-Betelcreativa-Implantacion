<?php

// Endpoint API para la administracion de proveedores
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../Config/app.php';

use BetelCreativa\Controllers\SupplierController;

// Configura respuesta JSON y delega al controlador de proveedores
header('Content-Type: application/json');

SupplierController::handleRequest();
