<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Infrastructure\AppointmentRepository;
use BetelCreativa\Infrastructure\CitaMaterialRepository;
use BetelCreativa\Infrastructure\CustomerRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// AppointmentController — Lógica de negocio para el módulo de citas/eventos
// CRUD completo, evaluación automática de estados, cancelación, restauración,
// gestión de materiales asociados y sincronización con facturación
class AppointmentController
{
    // Crea un modelo AppointmentModel desde un array del repositorio
    private static function modelFromRow(array $row): AppointmentModel
    {
        return new AppointmentModel($row);
    }

    // Obtiene la conexión PDO singleton
    private static function getDb(): \PDO
    {
        return \BetelCreativa\Config\Database::getConnection();
    }

    // Convierte un modelo a array asociativo para respuesta JSON
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
            'notas'                  => $c->getNotas(),
            'motivoSinMateriales'    => $c->getMotivoSinMateriales()
        ];
    }

    // Evalúa y cambia el estado de una cita según la hora actual y las reglas de negocio
    private static function evaluarEstado(array &$row): void
    {
        $ahora = date('Y-m-d H:i:s');
        $modelo = new AppointmentModel($row);
        $estado = $modelo->getEstado();

        // Transición automática: En Proceso → En Progreso cuando llega la hora de inicio
        if ($estado === 'En Proceso' && $row['fechaHoraInicio'] && $ahora >= $row['fechaHoraInicio']) {
            if ($modelo->canTransitionTo('En Progreso')) {
                $row['estado'] = 'En Progreso';
            }
        // Transición automática: En Progreso → Finalizada cuando pasa la hora de fin
        } elseif ($estado === 'En Progreso' && $row['fechaHoraFin'] && $ahora >= $row['fechaHoraFin']) {
            if ($modelo->canTransitionTo('Finalizada')) {
                $row['estado'] = 'Finalizada';
            }
        // Cancelación automática: Pendiente vencida sin pago
        } elseif ($estado === 'Pendiente' && $row['fechaHoraInicio'] && $ahora >= $row['fechaHoraInicio']) {
            $row['estado'] = 'Cancelado';
            $row['fechaHoraCancelacion'] = $ahora;
            $row['motivoCancelacion'] = 'No se realizó el pago a tiempo';
        }
    }

    // Persiste en BD los cambios de estado evaluados, con lógica transaccional para
    // cancelaciones automáticas (libera reservas) y finalización (deducción de stock solo si factura cerrada)
    private static function persistirEvaluacionEstado(AppointmentRepository $repo, array &$row): void
    {
        $estadoOriginal = $row['estado'];
        self::evaluarEstado($row);
        if ($row['estado'] !== $estadoOriginal) {
            $db = \BetelCreativa\Config\Database::getConnection();
            $ownTx = !$db->inTransaction();
            if ($ownTx) $db->beginTransaction();
            try {
                // Cancelación automática: no auto-cancelar si existe factura asociada
                if ($row['estado'] === 'Cancelado' && $estadoOriginal === 'Pendiente') {
                    $factRepo = new \BetelCreativa\Infrastructure\FacturaRepository();
                    $factura = $factRepo->getFacturaByCitaId((int)$row['id']);
                    if ($factura) {
                        $row['estado'] = $estadoOriginal;
                        if ($ownTx) $db->rollBack();
                        return;
                    }
                    $repo->update((int)$row['id'], [
                        'estado'               => 'Cancelado',
                        'fechaHoraCancelacion' => $row['fechaHoraCancelacion'] ?? date('Y-m-d H:i:s'),
                        'motivoCancelacion'    => $row['motivoCancelacion'] ?? 'No se realizó el pago a tiempo'
                    ]);
                    $matRepo = new CitaMaterialRepository();
                    $matRepo->cancelReservations((int)$row['id'], 'Cancelado', (int)($_SESSION['user_id'] ?? 0));
                } else {
                    $repo->actualizarEstado((int)$row['id'], $row['estado']);
                }
                // Solo deducir stock si la factura de la cita está cerrada (pagada)
                if ($row['estado'] === 'Finalizada') {
                    $factRepo = new \BetelCreativa\Infrastructure\FacturaRepository();
                    $factura = $factRepo->getFacturaByCitaId((int)$row['id']);
                    if ($factura && $factura['estado'] === 'cerrada') {
                        $matRepo = new CitaMaterialRepository();
                        $matRepo->executeDeductionOnCompleted((int)$row['id'], (int)($_SESSION['user_id'] ?? 0));
                    }
                }
                if ($ownTx) $db->commit();
            } catch (\Throwable $e) {
                if ($ownTx && $db->inTransaction()) $db->rollBack();
            }
        }
    }

    // Punto de entrada: enruta según método HTTP (GET/POST/PUT) y ejecuta la acción correspondiente
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new AppointmentRepository();

        switch ($method) {
            case 'GET':
                // GET con ?historial&id — historial de cambios de materiales
                if (isset($_GET['historial']) && isset($_GET['id'])) {
                    $matRepo = new CitaMaterialRepository();
                    $historial = $matRepo->obtenerHistorial((int)$_GET['id']);
                    ApiResponse::success($historial);
                    return;
                // GET con ?canceladas — lista solo citas canceladas
                } elseif (isset($_GET['canceladas'])) {
                    $filas = $repo->findCanceladas();
                    foreach ($filas as &$f) self::evaluarEstado($f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                // GET con ?id — detalle de una cita incluyendo cliente, materiales y estado de factura
                } elseif (isset($_GET['id'])) {
                    $fila = $repo->findById((int)$_GET['id']);
                    if (!$fila) { ApiResponse::error('Cita no encontrada.', 404); return; }
                    self::evaluarEstado($fila);
                    $modelo = self::modelFromRow($fila);
                    $data = self::toArray($modelo);
                    $customerRepo = new CustomerRepository();
                    $cliente = $customerRepo->findById($modelo->getClienteId());
                    $data['customerName'] = $cliente ? $cliente->getFirstName() . ' ' . $cliente->getLastName() : 'Cliente #' . $modelo->getClienteId();
                    $data['customerAvatar'] = $cliente ? $cliente->getAvatar() : null;
                    $matRepo = new CitaMaterialRepository();
                    $data['materiales'] = $matRepo->findByCitaId($modelo->getId());
                    
                    $facturaRepo = new \BetelCreativa\Infrastructure\FacturaRepository();
                    $fact = $facturaRepo->getFacturaByCitaId($modelo->getId());
                    $data['facturaEstado'] = $fact ? $fact['estado'] : null;
                    
                    ApiResponse::success($data);
                    return;
                // GET con ?cliente_id — citas de un cliente específico
                } elseif (isset($_GET['cliente_id'])) {
                    $filas = $repo->findByClienteId((int)$_GET['cliente_id']);
                    foreach ($filas as &$f) self::evaluarEstado($f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                // GET con ?todos — incluye canceladas en el listado
                } elseif (isset($_GET['todos'])) {
                    $filas = $repo->findAllWithCanceladas();
                    foreach ($filas as &$f) self::evaluarEstado($f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                // GET sin parámetros — lista de citas activas
                } else {
                    $filas = $repo->findAll();
                    foreach ($filas as &$f) self::evaluarEstado($f);
                    ApiResponse::success(array_map(function ($f) {
                        return self::toArray(self::modelFromRow($f));
                    }, $filas));
                    return;
                }

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();

                // Acción 'evaluate': evalúa y persiste estados de todas las citas (útil para cron o llamada manual)
                $action = $input['action'] ?? '';
                if ($action === 'evaluate') {
                    $filas = $repo->findAll();
                    $cambios = 0;
                    foreach ($filas as &$f) {
                        $antes = $f['estado'];
                        self::persistirEvaluacionEstado($repo, $f);
                        $despues = $f['estado'];
                        if ($antes !== $despues) $cambios++;
                    }
                    ApiResponse::success(['cambios' => $cambios], "Evaluación completada. $cambios cita(s) cambiaron de estado.");
                    return;
                }

                // Validaciones de campos obligatorios
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

                // Arma array de datos iniciales para la nueva cita
                $datos = [
                    'clienteId'       => (int)$input['clienteId'],
                    'fechaHoraInicio' => $input['fechaHoraInicio'],
                    'fechaHoraFin'    => $input['fechaHoraFin'],
                    'eventTypeId'     => !empty($input['eventTypeId']) ? (int)$input['eventTypeId'] : null,
                    'ubicacion'            => trim($input['ubicacion'] ?? '') ?: null,
                    'estado'                => 'Pendiente',
                    'notas'                 => trim($input['notas'] ?? '') ?: null,
                    'motivoSinMateriales'   => trim($input['motivoSinMateriales'] ?? '') ?: null
                ];

                // Verifica conflictos de horario con el mismo cliente
                if ($repo->hasTimeConflict($datos['clienteId'], $datos['fechaHoraInicio'], $datos['fechaHoraFin'])) {
                    ApiResponse::error('El cliente ya tiene una cita en ese horario.'); return;
                }

                $db = self::getDb();
                $db->beginTransaction();

                // Inserta la cita
                $id = $repo->save($datos);
                if (!$id) {
                    $db->rollBack();
                    ApiResponse::error('Error al crear la cita.', 500);
                }

                // Si hay materiales, los asigna y reserva stock atómicamente
                $materiales = $input['materiales'] ?? [];
                if (!empty($materiales)) {
                    $matRepo = new CitaMaterialRepository();
                    if (!$matRepo->syncMaterialsWithReservation($id, $materiales, 'Pendiente', (int)$_SESSION['user_id'], 'Asignado', false, false)) {
                        $db->rollBack();
                        return;
                    }
                }

                $db->commit();
                ApiResponse::success(['id' => $id], 'Cita creada exitosamente.');
                break;

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
                if (!$id) { ApiResponse::error('ID de cita requerido.'); return; }

                $existente = $repo->findById($id);
                if (!$existente) { ApiResponse::error('Cita no encontrada.', 404); return; }
                $estadoActual = $existente['estado'];
                $modelo = self::modelFromRow($existente);

                // Cancelación: requiere motivo, valida transición, libera reservas y auto-anula factura activa
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
                    $matRepo = new CitaMaterialRepository();
                    $matRepo->cancelReservations($id, 'Cancelado', (int)$_SESSION['user_id']);
                    $factRepo = new \BetelCreativa\Infrastructure\FacturaRepository();
                    $factura = $factRepo->getFacturaByCitaId($id);
                    if ($factura && ($factura['estado'] ?? '') === 'activa') {
                        $factRepo->cambiarEstado((int)$factura['id'], 'anulada', (int)$_SESSION['user_id'], 'Anulación automática por cancelación de cita #' . $id);
                    }
                    ApiResponse::success(null, 'Cita cancelada exitosamente.');
                    return;
                }

                // Restauración: valida plazo de 3 días hábiles, re-evalúa estado por tiempo, restaura reservas y factura
                if (!empty($input['restaurar'])) {
                    if (!AppointmentModel::esRestaurable($modelo->getFechaHoraCancelacion(), $modelo->getFechaHoraInicio())) {
                        ApiResponse::error('El plazo de 3 días hábiles para restaurar ha expirado o la fecha de la cita ya pasó.'); return;
                    }
                    $estadoRestaurado = $modelo->getEstadoPrevioCancelacion() ?: 'Pendiente';
                    try {
                        $modelo->validarTransicion($estadoRestaurado);
                    } catch (\DomainException $e) {
                        ApiResponse::error($e->getMessage()); return;
                    }
                    $inicio = $modelo->getFechaHoraInicio();
                    $fin = $modelo->getFechaHoraFin();
                    $ahora = date('Y-m-d H:i:s');
                    if ($estadoRestaurado === 'Pendiente' && $inicio && $ahora >= $inicio) {
                        $estadoRestaurado = 'En Progreso';
                    }
                    if ($estadoRestaurado === 'En Progreso' && $fin && $ahora >= $fin) {
                        $estadoRestaurado = 'Finalizada';
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
                    $factRepo = new \BetelCreativa\Infrastructure\FacturaRepository();
                    $factura = $factRepo->getFacturaByCitaId($id);
                    if ($factura && ($factura['estado'] ?? '') === 'anulada') {
                        $factRepo->cambiarEstado((int)$factura['id'], 'activa', (int)$_SESSION['user_id'], 'Restauración automática por restauración de cita #' . $id);
                    }
                    $mensaje = $stockOk
                        ? 'Cita restaurada exitosamente.'
                        : 'Cita restaurada, pero algunos materiales no tenían stock suficiente y fueron removidos. Debe reasignar los materiales manualmente.';
                    ApiResponse::success(null, $mensaje);
                    return;
                }

                // Actualización normal de campos (cliente, fechas, ubicación, notas, materiales)
                $datosUpdate = [];
                if (isset($input['clienteId'])) $datosUpdate['clienteId'] = (int)$input['clienteId'];
                if (isset($input['fechaHoraInicio'])) $datosUpdate['fechaHoraInicio'] = $input['fechaHoraInicio'];
                if (isset($input['fechaHoraFin'])) $datosUpdate['fechaHoraFin'] = $input['fechaHoraFin'];
                if (isset($input['eventTypeId'])) $datosUpdate['eventTypeId'] = !empty($input['eventTypeId']) ? (int)$input['eventTypeId'] : null;
                if (isset($input['ubicacion'])) $datosUpdate['ubicacion'] = trim($input['ubicacion']) ?: null;
                if (isset($input['notas'])) $datosUpdate['notas'] = trim($input['notas']) ?: null;
                if (isset($input['motivoSinMateriales'])) $datosUpdate['motivoSinMateriales'] = trim($input['motivoSinMateriales']) ?: null;

                // Si se intenta cambiar el estado, valida la transición permitida
                if (array_key_exists('estado', $datosUpdate)) {
                    try {
                        $modelo->validarTransicion($datosUpdate['estado']);
                    } catch (\DomainException $e) {
                        ApiResponse::error($e->getMessage()); return;
                    }
                }

                if (!empty($datosUpdate)) {
                    $inicio = $datosUpdate['fechaHoraInicio'] ?? $existente['fechaHoraInicio'];
                    $fin    = $datosUpdate['fechaHoraFin'] ?? $existente['fechaHoraFin'];
                    if ($inicio >= $fin) {
                        ApiResponse::error('La fecha de fin debe ser posterior a la de inicio.'); return;
                    }
                    $clienteId = $datosUpdate['clienteId'] ?? (int)$existente['clienteId'];
                    if ($repo->hasTimeConflict($clienteId, $inicio, $fin, $id)) {
                        ApiResponse::error('El cliente ya tiene una cita en ese horario.'); return;
                    }
                    if ($repo->update($id, $datosUpdate)) {
                        // Reemplazar materiales si se enviaron (incluso array vacío = eliminar todos)
                        if (array_key_exists('materiales', $input)) {
                            $materiales = $input['materiales'] ?? [];
                            $matRepo = new CitaMaterialRepository();
                            if (!$matRepo->syncMaterialsWithReservation($id, $materiales, $estadoActual, (int)$_SESSION['user_id'], 'Modificado')) {
                                return;
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
