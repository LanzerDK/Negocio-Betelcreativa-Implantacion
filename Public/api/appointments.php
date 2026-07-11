<?php

// API de Citas (Appointments)
// Punto de entrada HTTP para todas las operaciones CRUD de citas
// Delega en AppointmentController según el método y acción recibidos

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\AppointmentController;

// Configura respuesta como JSON y desactiva caché para datos en tiempo real
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// El controlador maneja internamente la lógica de enrutamiento según método HTTP y acción
AppointmentController::handleRequest();
