<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Infrastructure\AppointmentRepository;
use BetelCreativa\Infrastructure\CustomerRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// =============================================
// Controlador de Citas (Appointments)
// Maneja peticiones REST para crear, leer, actualizar
// y eliminar citas. Incluye verificación de Auth y CSRF.
// =============================================
class AppointmentController
{
    // Punto de entrada único: analiza el método HTTP y delega
    public static function handleRequest(): void
    {
        // Solo usuarios autenticados pueden acceder
        SessionHelpers::requireAuth();

        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new AppointmentRepository();

        switch ($method) {
            case 'GET':
                // GET /api/appointments.php           -> lista todas las citas
                // GET /api/appointments.php?id=5      -> una cita específica
                // GET /api/appointments.php?customer_id=3 -> citas de un cliente
                if (isset($_GET['cancelled'])) {
                    // GET /api/appointments.php?cancelled=1 -> citas canceladas
                    $appointments = $repo->findCancelled();
                    ApiResponse::success(array_map([self::class, 'toArray'], $appointments));
                    return;
                } elseif (isset($_GET['all'])) {
                    // GET /api/appointments.php?all=1 -> todas (incluye canceladas) para calendario
                    $appointments = $repo->findAllWithCancelled();
                    ApiResponse::success(array_map([self::class, 'toArray'], $appointments));
                    return;
                } elseif (isset($_GET['id'])) {
                    $appointment = $repo->findById((int)$_GET['id']);
                    if ($appointment) {
                        // Incluye datos del cliente en la respuesta para FullCalendar
                        $data = self::toArrayEnriched($appointment);
                        ApiResponse::success($data);
                    } else {
                        ApiResponse::error('Cita no encontrada.', 404);
                    }
                } elseif (isset($_GET['customer_id'])) {
                    $appointments = $repo->findByCustomerId((int)$_GET['customer_id']);
                    ApiResponse::success(array_map([self::class, 'toArray'], $appointments));
                } else {
                    $appointments = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $appointments));
                }
                break;

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                $appointment = new AppointmentModel([
                    'customerId' => (int)($input['customerId'] ?? 0),
                    'date'       => trim($input['date'] ?? ''),
                    'startTime'  => trim($input['startTime'] ?? ''),
                    'endTime'    => trim($input['endTime'] ?? ''),
                    'eventType'  => trim($input['eventType'] ?? ''),
                    'location'   => trim($input['location'] ?? ''),
                    'status'     => trim($input['status'] ?? 'pending'),
                    'notes'      => trim($input['notes'] ?? '')
                ]);

                // Validaciones obligatorias
                if (!$appointment->getCustomerId()) {
                    ApiResponse::error('Debe seleccionar un cliente.');
                    return;
                }
                if (empty($appointment->getDate())) {
                    ApiResponse::error('La fecha es obligatoria.');
                    return;
                }
                if (empty($appointment->getStartTime())) {
                    ApiResponse::error('La hora de inicio es obligatoria.');
                    return;
                }
                if (empty($appointment->getEndTime())) {
                    ApiResponse::error('La hora de fin es obligatoria.');
                    return;
                }

                if (strtotime($appointment->getEndTime()) <= strtotime($appointment->getStartTime())) {
                    ApiResponse::error('La hora de fin debe ser posterior a la hora de inicio.');
                    return;
                }

                // Validar conflicto de horario
                if ($repo->hasTimeConflict(
                    $appointment->getCustomerId(),
                    $appointment->getDate(),
                    $appointment->getStartTime(),
                    $appointment->getEndTime()
                )) {
                    ApiResponse::error('El cliente ya tiene una cita programada en ese horario.');
                    return;
                }

                if ($repo->save($appointment)) {
                    ApiResponse::success(null, 'Cita creada exitosamente.');
                } else {
                    ApiResponse::error('Error al crear la cita.', 500);
                }
                break;

            case 'PUT':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

                if (!$id) {
                    ApiResponse::error('ID de cita requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Cita no encontrada.', 404);
                }

                $appointment = new AppointmentModel([
                    'id'         => $id,
                    'customerId' => (int)($input['customerId'] ?? $existing->getCustomerId()),
                    'date'       => trim($input['date'] ?? $existing->getDate()),
                    'startTime'  => trim($input['startTime'] ?? $existing->getStartTime()),
                    'endTime'    => trim($input['endTime'] ?? $existing->getEndTime()),
                    'eventType'  => trim($input['eventType'] ?? $existing->getEventType()),
                    'location'   => trim($input['location'] ?? $existing->getLocation()),
                    'status'     => trim($input['status'] ?? $existing->getStatus()),
                    'notes'      => trim($input['notes'] ?? $existing->getNotes())
                ]);

                if (!empty($appointment->getStartTime()) && !empty($appointment->getEndTime())
                    && strtotime($appointment->getEndTime()) <= strtotime($appointment->getStartTime())) {
                    ApiResponse::error('La hora de fin debe ser posterior a la hora de inicio.');
                    return;
                }

                // Validar conflicto de horario (excluyendo la cita actual)
                if ($repo->hasTimeConflict(
                    $appointment->getCustomerId(),
                    $appointment->getDate(),
                    $appointment->getStartTime(),
                    $appointment->getEndTime(),
                    $id
                )) {
                    ApiResponse::error('El cliente ya tiene una cita programada en ese horario.');
                    return;
                }

                if ($repo->update($appointment)) {
                    ApiResponse::success(null, 'Cita actualizada exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar la cita.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de cita requerido.');
                    return;
                }
                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Cita no encontrada.', 404);
                    return;
                }
                // En lugar de eliminar físicamente, marcamos como cancelada
                $appointment = new AppointmentModel([
                    'id'         => $id,
                    'customerId' => $existing->getCustomerId(),
                    'date'       => $existing->getDate(),
                    'startTime'  => $existing->getStartTime(),
                    'endTime'    => $existing->getEndTime(),
                    'eventType'  => $existing->getEventType(),
                    'location'   => $existing->getLocation(),
                    'status'     => 'cancelled',
                    'notes'      => $existing->getNotes()
                ]);
                if ($repo->update($appointment)) {
                    ApiResponse::success(null, 'Cita cancelada exitosamente.');
                } else {
                    ApiResponse::error('Error al cancelar la cita.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    // Convierte AppointmentModel a array para la respuesta JSON
    private static function toArray(AppointmentModel $a): array
    {
        return [
            'id'         => $a->getId(),
            'customerId' => $a->getCustomerId(),
            'date'       => $a->getDate(),
            'startTime'  => $a->getStartTime(),
            'endTime'    => $a->getEndTime(),
            'eventType'  => $a->getEventType(),
            'location'   => $a->getLocation(),
            'status'     => $a->getStatus(),
            'notes'      => $a->getNotes(),
            'isActive'   => $a->isActive()
        ];
    }

    // Versión enriquecida que incluye firstName + lastName del cliente
    // Útil para FullCalendar que muestra el nombre del cliente en el evento
    private static function toArrayEnriched(AppointmentModel $a): array
    {
        $data = self::toArray($a);
        // Obtiene el nombre del cliente para mostrarlo en el calendario
        $customerRepo = new CustomerRepository();
        $customer = $customerRepo->findById($a->getCustomerId());
        $data['customerName'] = $customer
            ? $customer->getFirstName() . ' ' . $customer->getLastName()
            : 'Cliente #' . $a->getCustomerId();
        $data['customerAvatar'] = $customer ? $customer->getAvatar() : null;
        return $data;
    }
}
