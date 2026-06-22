<?php

// =============================================
// API de Citas (Appointments)
// Punto de entrada HTTP para todas las operaciones
// CRUD de citas. Delega en AppointmentController.
// =============================================

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Config/app.php';

use BetelCreativa\Controllers\AppointmentController;

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

AppointmentController::handleRequest();
