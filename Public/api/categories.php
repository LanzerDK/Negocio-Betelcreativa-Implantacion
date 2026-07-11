<?php

// API de Categorías
// Punto de entrada HTTP para todas las operaciones CRUD de categorías de materiales
// Delega en CategoryController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\CategoryController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP
CategoryController::handleRequest();
