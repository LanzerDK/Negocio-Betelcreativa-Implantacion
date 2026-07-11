<?php

// API de Tareas
// Punto de entrada HTTP para todas las operaciones CRUD de tareas
// Delega en TaskController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\TaskController;

// Configura respuesta como JSON y desactiva caché
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// El controlador gestiona el enrutamiento interno según el método HTTP y la acción solicitada
TaskController::handleRequest();
