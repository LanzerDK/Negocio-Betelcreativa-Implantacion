<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Infrastructure\AppointmentRepository;
use BetelCreativa\Infrastructure\CitaMaterialRepository;
use BetelCreativa\Infrastructure\CustomerRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class AppointmentController
{
    private static function modelFromRow(array $row): AppointmentModel
    {
        return new AppointmentModel($row);
    }

    private static function toArray(AppointmentModel $c): array
    {
        return [
            'id'                     => $c->getId(),
            'clienteId'              => $c->getClienteId(),
            'fechaHoraInicio'        => $c->getFechaHoraInicio(),
            'fechaHoraFin'           => $c->getFechaHoraFin(),
            'eventType'              => $c->getEventType() ?? '—',
            'eventTypeId'            => $c->getEventTypeId(),
            'ubicacion'              => $c->getUbicacion(),
            'estado'                 => $c->getEstado(),
            'estadoPrevioCancelacion'=> $c->getEstadoPrevioCancelacion(),
            'fechaHoraCancelacion'   => $c->getFechaHoraCancelacion(),
            'motivoCancelacion'      => $c->getMotivoCancelacion(),
            'notas'                  => $c->getNotas()
        ];
    }

    private static function evaluarEstado(array &$row): void
    {
        $ahora = date('Y-m-d H:i:s');
        $modelo = new AppointmentModel($row);

        if ($modelo->getEstado() === 'Pendiente' && $row['fechaHoraInicio'] && $ahora >= $row['fechaHoraInicio']) {
            if ($modelo->canTransitionTo('En Progreso')) {
                $row['estado'] = 'En Progreso';
            }
        } elseif ($modelo->getEstado() === 'En Progreso' && $row['fechaHoraFin'] && $ahora >= $row['fechaHoraFin']) {
            if ($modelo->canTransitionTo('Terminado')) {
                $row['estado'] = 'Terminado';
            }
        }
    }

    private static function persistirEvaluacionEstado(AppointmentRepository $repo, array &$row): void
    {
        $estadoOriginal = $row['estado'];
        self::evaluarEstado($row);
        if ($row['estado'] !== $estadoOriginal) {
            $repo->actualizarEstado((int)$row['id'], $row['estado']);
            if ($row['estado'] === 'Terminado') {
                $matRepo = new CitaMaterialRepository();
                $matRepo->executeDeductionOnCompleted((int)$row['id'], (int)$_SESSION['user_id']);
            }
        }
    }

    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new AppointmentRepository();

        switch ($method) {
            case 'GET':
                if (isset($_GET['historial']) && isset($_GET['id'])) {
                    $matRepo = new CitaMaterialRepository();
                    $historial = $matRepo->obtenerHistorial((int)$_GET['id']);
                    ApiResponse::success($historial);
                    return;
                } elseif (isset($_GET['canceladas'])) {
                    $filas = $repo->findCanceladas();
                    foreach ($filas as &$f) self::persistirEvaluacionEstado($repo, $f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                } elseif (isset($_GET['id'])) {
                    $fila = $repo->findById((int)$_GET['id']);
                    if (!$fila) { ApiResponse::error('Cita no encontrada.', 404); return; }
                    self::persistirEvaluacionEstado($repo, $fila);
                    $modelo = self::modelFromRow($fila);
                    $data = self::toArray($modelo);
                    $customerRepo = new CustomerRepository();
                    $cliente = $customerRepo->findById($modelo->getClienteId());
                    $data['customerName'] = $cliente ? $cliente->getFirstName() . ' ' . $cliente->getLastName() : 'Cliente #' . $modelo->getClienteId();
                    $data['customerAvatar'] = $cliente ? $cliente->getAvatar() : null;
                    $matRepo = new CitaMaterialRepository();
                    $data['materiales'] = $matRepo->findByCitaId($modelo->getId());
                    ApiResponse::success($data);
                    return;
                } elseif (isset($_GET['cliente_id'])) {
                    $filas = $repo->findByClienteId((int)$_GET['cliente_id']);
                    foreach ($filas as &$f) self::persistirEvaluacionEstado($repo, $f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                } else {
                    $filas = $repo->findAll();
                    foreach ($filas as &$f) self::persistirEvaluacionEstado($repo, $f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                }

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                if (empty($input['clienteId'])) {
                    ApiResponse::error('Debe seleccionar un cliente.'); return;
                }
                if (empty($input['fechaHoraInicio'])) {
                    ApiResponse::error('La fecha y hora de inicio es obligatoria.'); return;
                }
                if (empty($input['fechaHoraFin'])) {
                    ApiResponse::error('La fecha y hora de fin es obligatoria.'); return;
                }
                if ($input['fechaHoraInicio'] >= $input['fechaHoraFin']) {
                    ApiResponse::error('La fecha de fin debe ser posterior a la de inicio.'); return;
                }

                $datos = [
                    'clienteId'       => (int)$input['clienteId'],
                    'fechaHoraInicio' => $input['fechaHoraInicio'],
                    'fechaHoraFin'    => $input['fechaHoraFin'],
                    'eventTypeId'     => !empty($input['eventTypeId']) ? (int)$input['eventTypeId'] : null,
                    'ubicacion'       => trim($input['ubicacion'] ?? ''),
                    'estado'          => 'En Proceso',
                    'notas'           => trim($input['notas'] ?? '')
                ];

                if ($repo->hasTimeConflict($datos['clienteId'], $datos['fechaHoraInicio'], $datos['fechaHoraFin'])) {
                    ApiResponse::error('El cliente ya tiene una cita en ese horario.'); return;
                }

                $id = $repo->save($datos);
                if (!$id) {
                    ApiResponse::error('Error al crear la cita.', 500); return;
                }

                // Asignar materiales con reserva de stock
                $materiales = $input['materiales'] ?? [];
                if (!empty($materiales)) {
                    $matRepo = new CitaMaterialRepository();
                    if (!$matRepo->syncMaterialsWithReservation($id, $materiales, 'En Proceso', (int)$_SESSION['user_id'], 'Asignado')) {
                        return; // ApiResponse::error ya fue llamada
                    }
                }

                ApiResponse::success(['id' => $id], 'Cita creada exitosamente.');
                break;

            case 'PUT':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
                if (!$id) { ApiResponse::error('ID de cita requerido.'); return; }

                $existente = $repo->findById($id);
                if (!$existente) { ApiResponse::error('Cita no encontrada.', 404); return; }
                $estadoActual = $existente['estado'];
                $modelo = self::modelFromRow($existente);

                // ---- C A N C E L A C I Ó N ----
                if (isset($input['motivoCancelacion']) && trim($input['motivoCancelacion']) !== '') {
                    try {
                        $modelo->validarTransicion('Cancelado');
                    } catch (\DomainException $e) {
                        ApiResponse::error($e->getMessage()); return;
                    }
                    $repo->update($id, [
                        'estadoPrevioCancelacion' => $estadoActual,
                        'estado'                  => 'Cancelado',
                        'fechaHoraCancelacion'    => date('Y-m-d H:i:s'),
                        'motivoCancelacion'       => trim($input['motivoCancelacion'])
                    ]);
                    // Liberar reservas de materiales (mantiene cita_materiales para posible restauración)
                    $matRepo = new CitaMaterialRepository();
                    $matRepo->cancelReservations($id, 'Cancelado', (int)$_SESSION['user_id']);
                    ApiResponse::success(null, 'Cita cancelada exitosamente.');
                    return;
                }

                // ---- R E S T A U R A C I Ó N ----
                if (!empty($input['restaurar'])) {
                    if (!AppointmentModel::esRestaurable($modelo->getFechaHoraCancelacion(), $modelo->getFechaHoraInicio())) {
                        ApiResponse::error('El plazo de 3 días hábiles para restaurar ha expirado o la fecha de la cita ya pasó.'); return;
                    }
                    $estadoRestaurado = $modelo->getEstadoPrevioCancelacion() ?: 'En Proceso';
                    try {
                        $modelo->validarTransicion($estadoRestaurado);
                    } catch (\DomainException $e) {
                        ApiResponse::error($e->getMessage()); return;
                    }
                    // Re-evaluar estado por tiempo transcurrido
                    $inicio = $modelo->getFechaHoraInicio();
                    $fin = $modelo->getFechaHoraFin();
                    $ahora = date('Y-m-d H:i:s');
                    if ($estadoRestaurado === 'Pendiente' && $inicio && $ahora >= $inicio) {
                        $estadoRestaurado = 'En Progreso';
                    }
                    if ($estadoRestaurado === 'En Progreso' && $fin && $ahora >= $fin) {
                        $estadoRestaurado = 'Terminado';
                    }
                    $matRepo = new CitaMaterialRepository();
                    $stockOk = $matRepo->restoreReservations($id, $estadoRestaurado, (int)$_SESSION['user_id']);
                    if (!$stockOk) {
                        $matRepo->deleteByCitaId($id);
                    }
                    $repo->update($id, [
                        'estado'                  => $estadoRestaurado,
                        'estadoPrevioCancelacion' => null,
                        'fechaHoraCancelacion'    => null,
                        'motivoCancelacion'       => null
                    ]);
                    $mensaje = $stockOk
                        ? 'Cita restaurada exitosamente.'
                        : 'Cita restaurada, pero algunos materiales no tenían stock suficiente y fueron removidos. Debe reasignar los materiales manualmente.';
                    ApiResponse::success(null, $mensaje);
                    return;
                }

                // ---- A C T U A L I Z A C I Ó N   N O R M A L ----
                $datosUpdate = [];
                if (isset($input['clienteId'])) $datosUpdate['clienteId'] = (int)$input['clienteId'];
                if (isset($input['fechaHoraInicio'])) $datosUpdate['fechaHoraInicio'] = $input['fechaHoraInicio'];
                if (isset($input['fechaHoraFin'])) $datosUpdate['fechaHoraFin'] = $input['fechaHoraFin'];
                if (isset($input['eventTypeId'])) $datosUpdate['eventTypeId'] = !empty($input['eventTypeId']) ? (int)$input['eventTypeId'] : null;
                if (isset($input['ubicacion'])) $datosUpdate['ubicacion'] = trim($input['ubicacion']);
                if (isset($input['notas'])) $datosUpdate['notas'] = trim($input['notas']);

                if (array_key_exists('estado', $datosUpdate)) {
                    try {
                        $modelo->validarTransicion($datosUpdate['estado']);
                    } catch (\DomainException $e) {
                        ApiResponse::error($e->getMessage()); return;
                    }
                }

                if (!empty($datosUpdate)) {
                    if (isset($datosUpdate['fechaHoraInicio']) && isset($datosUpdate['fechaHoraFin'])) {
                        if ($datosUpdate['fechaHoraInicio'] >= $datosUpdate['fechaHoraFin']) {
                            ApiResponse::error('La fecha de fin debe ser posterior a la de inicio.'); return;
                        }
                    }
                    if ($repo->update($id, $datosUpdate)) {
                        // Reemplazar materiales si se enviaron (reserva atómica)
                        // Usamos array_key_exists para que vacío (eliminar todos) también dispare sync
                        if (array_key_exists('materiales', $input)) {
                            $materiales = $input['materiales'] ?? [];
                            $matRepo = new CitaMaterialRepository();
                            if (!$matRepo->syncMaterialsWithReservation($id, $materiales, $estadoActual, (int)$_SESSION['user_id'], 'Modificado')) {
                                return; // ApiResponse::error ya fue llamada
                            }
                        }
                        ApiResponse::success(null, 'Cita actualizada exitosamente.');
                    } else {
                        ApiResponse::error('Error al actualizar la cita.', 500);
                    }
                } else {
                    ApiResponse::success(null, 'Sin cambios.');
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }
}
