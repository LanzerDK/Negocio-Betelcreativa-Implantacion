<?php

// API de Tipos de Evento
// Punto de entrada HTTP para todas las operaciones CRUD de tipos de evento
// Delega en EventTypeController

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\EventTypeController;

// Configura respuesta como JSON
header('Content-Type: application/json');

// El controlador gestiona el enrutamiento interno según el método HTTP
EventTypeController::handleRequest();
