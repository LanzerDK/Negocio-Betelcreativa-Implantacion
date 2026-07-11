<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Config\Database;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\FacturaCalculadora;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Infrastructure\FacturaRepository;
use BetelCreativa\Infrastructure\CitaMaterialRepository;
use BetelCreativa\Infrastructure\AppointmentRepository;
use BetelCreativa\Services\ExchangeRateService;
use PDO;

class FacturaController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new FacturaRepository();

        switch ($method) {
            case 'GET':
                $action = $_GET['action'] ?? '';

                if ($action === 'list' && isset($_GET['status'])) {
                    $status = $_GET['status'];
                    if (!in_array($status, ['abiertas', 'canceladas', 'pendientes', 'pagadas'], true)) {
                        ApiResponse::error('Estado no válido. Use abiertas, canceladas, pendientes o pagadas.');
                        return;
                    }
                    $facturas = $repo->listFacturasByStatus($status);
                    ApiResponse::success($facturas);

                } elseif ($action === 'detalle-factura' && isset($_GET['factura_id'])) {
                    $detalle = $repo->getDetalleByFacturaId((int)$_GET['factura_id']);
                    if (!$detalle) {
                        ApiResponse::error('Factura no encontrada.', 404);
                        return;
                    }
                    $tasaService = new ExchangeRateService();
                    $detalle['tasaBcv'] = $tasaService->getEffectiveRate();
                    ApiResponse::success($detalle);

                } elseif ($action === 'detalle' && isset($_GET['cita_id'])) {
                    $detalle = $repo->getDetalleByCitaId((int)$_GET['cita_id']);
                    if (!$detalle) {
                        ApiResponse::error('Cita no encontrada.', 404);
                        return;
                    }
                    $tasaService = new ExchangeRateService();
                    $detalle['tasaBcv'] = $tasaService->getEffectiveRate();
                    ApiResponse::success($detalle);

                } elseif ($action === 'terminos') {
                    $stmt = \BetelCreativa\Config\Database::getConnection()->prepare(
                        "SELECT setting_value FROM settings WHERE setting_key = 'terminos_condiciones'"
                    );
                    $stmt->execute();
                    $terminos = $stmt->fetchColumn();
                    ApiResponse::success(['terminos' => $terminos ?: '']);

                } elseif ($action === 'tasa') {
                    $tasaService = new ExchangeRateService();
                    ApiResponse::success(['tasa' => $tasaService->getEffectiveRate()]);

                } elseif ($action === 'metodos-pago') {
                    $stmt = \BetelCreativa\Config\Database::getConnection()->prepare(
                        "SELECT setting_value FROM settings WHERE setting_key = 'metodos_pago'"
                    );
                    $stmt->execute();
                    $raw = $stmt->fetchColumn();
                    $metodos = $raw ? array_filter(array_map('trim', explode(',', $raw))) : ['Efectivo', 'PagoMóvil', 'Divisas'];
                    ApiResponse::success(array_values($metodos));

                } else {
                    ApiResponse::error('Acción no válida.');
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $action = $input['action'] ?? '';

                if ($action === 'crear') {
                    $citaId = (int)($input['cita_id'] ?? 0);
                    $costoServicio = (float)($input['costo_servicio'] ?? 0);
                    $descripcionServicio = trim($input['descripcion_servicio'] ?? '') ?: null;
                    $createdByName = $_SESSION['user_name'] ?? 'Usuario';
                    $metodoPago = trim($input['metodo_pago'] ?? 'efectivo');
                    $tasa = (float)($input['tasa_usada'] ?? 0);

                    if (!$citaId) {
                        ApiResponse::error('ID de cita requerido.');
                        return;
                    }

                    $detalle = $repo->getDetalleByCitaId($citaId);
                    if (!$detalle || empty($detalle['general'])) {
                        ApiResponse::error('Cita no encontrada.', 404);
                        return;
                    }

                    $citaEstado = $detalle['general']['citaEstado'] ?? '';
                    if ($citaEstado === 'cancelada') {
                        ApiResponse::error('No se puede facturar una cita cancelada.', 400);
                        return;
                    }

                    if ($costoServicio < 0) {
                        ApiResponse::error('El costo del servicio no puede ser negativo.');
                        return;
                    }

                    $totales = FacturaCalculadora::calcularTotales($costoServicio, $detalle['materiales'] ?? []);
                    $totalFacturaCalculado = $totales['total'];

                    // Obtener el monto a pagar (siempre en Bs)
                    $montoPagoBs = (float)($input['monto_pago_bs'] ?? 0);
                    if ($montoPagoBs <= 0) {
                        ApiResponse::error('Debe especificar un monto de pago.');
                        return;
                    }

                    // Validar mínimo 50%
                    $minimo = $totalFacturaCalculado * 0.50;
                    if ($montoPagoBs < $minimo - 0.01) {
                        ApiResponse::error("El anticipo mínimo obligatorio es del 50% ({$minimo} Bs).");
                        return;
                    }

                    // Tasa BCV
                    if ($tasa <= 0) {
                        $tasaService = new ExchangeRateService();
                        $tasa = $tasaService->getEffectiveRate();
                    }
                    if ($tasa <= 0) {
                        ApiResponse::error('Tasa BCV no disponible. Intente de nuevo o especifique una tasa manualmente.');
                        return;
                    }

                    // Determinar si es pago completo o parcial
                    $esPagoCompleto = $montoPagoBs >= $totalFacturaCalculado - 0.01;
                    $planTipo = $esPagoCompleto ? 'contado' : 'cuotas';
                    $planMontoCuotaSugerido = $esPagoCompleto ? null : round($totalFacturaCalculado / 2, 2);

                    $db = Database::getConnection();
                    $db->beginTransaction();
                    try {
                        // Check for existing factura with FOR UPDATE
                        $lockStmt = $db->prepare("SELECT id FROM facturas WHERE cita_id = :cid FOR UPDATE");
                        $lockStmt->execute([':cid' => $citaId]);
                        if ($lockStmt->fetch()) {
                            throw new \RuntimeException('La cita ya tiene una factura.');
                        }

                        $id = $repo->crearFactura($citaId, $costoServicio, '', $createdByName, $descripcionServicio, $planTipo, null, $planMontoCuotaSugerido, (int)($_SESSION['user_id'] ?? 0));

                        // Re-read factura to get exact total from DB
                        $fLock = $db->prepare("SELECT total_factura AS totalFactura FROM facturas WHERE id = :fid FOR UPDATE");
                        $fLock->execute([':fid' => $id]);
                        $fRow = $fLock->fetch();
                        $montoTotal = $fRow ? (float)$fRow['totalFactura'] : $totalFacturaCalculado;

                        // Registrar el pago
                        $esDivisa = stripos($metodoPago, 'divisa') !== false || stripos($metodoPago, 'dolar') !== false || stripos($metodoPago, '$') !== false;
                        if ($esDivisa) {
                            $montoUsd = $montoPagoBs / $tasa;
                        } else {
                            $montoUsd = $montoPagoBs / $tasa;
                        }

                        $refPagoMovil = trim($input['ref_pago_movil'] ?? '') ?: null;
                        $repo->registrarPrimerPago($id, $montoUsd, $metodoPago, $tasa, $refPagoMovil);

                        if ($esPagoCompleto) {
                            $repo->cambiarEstado($id, 'cerrada', (int)($_SESSION['user_id'] ?? 0));
                        }

                        $db->commit();

                        if ($esPagoCompleto) {
                            ApiResponse::success(['id' => $id, 'planTipo' => 'contado', 'reciboId' => $id], 'Factura creada y pagada exitosamente.');
                        } else {
                            $saldoPendiente = round($totalFacturaCalculado - $montoPagoBs, 2);
                            ApiResponse::success([
                                'id' => $id,
                                'planTipo' => 'cuotas',
                                'planMontoCuotaSugerido' => $planMontoCuotaSugerido,
                                'saldoPendiente' => $saldoPendiente
                            ], "Anticipo del " . round($montoPagoBs / $totalFacturaCalculado * 100) . "% registrado. Saldo pendiente: {$saldoPendiente} Bs.");
                        }
                    } catch (\Throwable $e) {
                        $db->rollBack();
                        ApiResponse::error($e->getMessage(), 500);
                    }

                } elseif ($action === 'pagar') {
                    $facturaId = (int)($input['factura_id'] ?? 0);
                    $monto = (float)($input['monto'] ?? 0);
                    $metodoPago = trim($input['metodo_pago'] ?? '');
                    $tasaUsada = (float)($input['tasa_usada'] ?? 0);

                    if (!$facturaId || $monto <= 0) {
                        ApiResponse::error('Factura ID y monto requeridos.');
                        return;
                    }
                    if ($metodoPago === '') {
                        ApiResponse::error('Método de pago requerido.');
                        return;
                    }
                    if ($tasaUsada <= 0) {
                        $tasaService = new ExchangeRateService();
                        $tasaUsada = $tasaService->getEffectiveRate();
                    }
                    if ($tasaUsada <= 0) {
                        ApiResponse::error('Tasa BCV no disponible. Intente de nuevo o especifique una tasa manualmente.');
                        return;
                    }

                    // #3: wrap payment in transaction with FOR UPDATE on factura row
                    $db = Database::getConnection();
                    $db->beginTransaction();
                    try {
                        // Lock the factura row to prevent concurrent payments
                        $lockStmt = $db->prepare(
                            "SELECT id, total_factura AS totalFactura, estado FROM facturas WHERE id = :fid FOR UPDATE"
                        );
                        $lockStmt->execute([':fid' => $facturaId]);
                        $factura = $lockStmt->fetch(PDO::FETCH_ASSOC);
                        if (!$factura) {
                            throw new \RuntimeException('Factura no encontrada.');
                        }
                        if ($factura['estado'] !== 'activa') {
                            throw new \RuntimeException('No se pueden registrar pagos en una factura ' . $factura['estado'] . '.');
                        }

                        // Prevent overpayment: check if already fully paid
                        $paidStmt = $db->prepare(
                            "SELECT COALESCE(SUM(p.monto * p.tasa_usada), 0)
                             FROM pagos_factura p
                             LEFT JOIN facturas r ON p.factura_id = r.id
                             WHERE p.factura_id = :fid1 OR r.factura_origen_id = :fid2"
                        );
                        $paidStmt->execute([':fid1' => $facturaId, ':fid2' => $facturaId]);
                        $totalPagadoVes = (float)$paidStmt->fetchColumn();
                        $totalFacturaVes = (float)$factura['totalFactura'];
                        if ($totalPagadoVes >= $totalFacturaVes - 0.01) {
                            throw new \RuntimeException('La factura ya está totalmente pagada.');
                        }

                        $refPagoMovil = trim($input['ref_pago_movil'] ?? '') ?: null;
                        $reciboId = $repo->registrarPago($facturaId, $monto, $metodoPago, $tasaUsada, false, $refPagoMovil);
                        $db->commit();

                        $pagos = $repo->getPagosByFacturaId($facturaId);
                        $totalPagado = 0;
                        foreach ($pagos as $p) $totalPagado += (float)$p['monto'];
                        // Determine si la factura quedó completamente pagada
                        $estadoFactura = $factura['estado'];
                        if ($totalPagadoVes + ($monto * $tasaUsada) >= $totalFacturaVes - 0.01) {
                            $estadoFactura = 'cerrada';
                        }
                        ApiResponse::success([
                            'totalPagado'   => $totalPagado,
                            'pagos'         => $pagos,
                            'reciboId'      => $reciboId,
                            'estadoFactura' => $estadoFactura
                        ], 'Pago registrado exitosamente.');
                    } catch (\Throwable $e) {
                        $db->rollBack();
                        ApiResponse::error($e->getMessage(), 500);
                    }

                } elseif ($action === 'cerrar' || $action === 'anular') {
                    $facturaId = (int)($input['factura_id'] ?? 0);
                    if (!$facturaId) {
                        ApiResponse::error('ID de factura requerido.');
                        return;
                    }

                    // #4, #5: wrap cerrar/anular in transaction with FOR UPDATE
                    $db = Database::getConnection();
                    $db->beginTransaction();
                    try {
                        // Lock factura row
                        $lockStmt = $db->prepare(
                            "SELECT id, cita_id AS citaId, total_factura AS totalFactura, estado FROM facturas WHERE id = :fid FOR UPDATE"
                        );
                        $lockStmt->execute([':fid' => $facturaId]);
                        $factura = $lockStmt->fetch(PDO::FETCH_ASSOC);
                        if (!$factura) {
                            throw new \RuntimeException('Factura no encontrada.');
                        }
                        if ($factura['estado'] !== 'activa') {
                            throw new \RuntimeException('La factura no está en estado activa.');
                        }

                        $nuevoEstado = $action === 'cerrar' ? 'cerrada' : 'anulada';
                        $motivo = '';

                        if ($action === 'cerrar') {
                            $totalPagadoVes = $repo->getTotalPagadoVes($facturaId);
                            if ($totalPagadoVes < (float)$factura['totalFactura'] - 0.01) {
                                throw new \RuntimeException('No se puede cerrar una factura con saldo pendiente.');
                            }
                        } else {
                            // anular: validar motivo obligatorio
                            $motivo = trim($input['motivo'] ?? '');
                            if (empty($motivo)) {
                                throw new \RuntimeException('Debe indicar el motivo de anulación.');
                            }

                            // Verificar pagos: bloquear si hay pagos y la cita no está cancelada
                            $totalPagadoVes = $repo->getTotalPagadoVes($facturaId);
                            if ($totalPagadoVes > 0.01) {
                                $citaRepo = new \BetelCreativa\Infrastructure\AppointmentRepository();
                                $cita = $citaRepo->findById((int)$factura['citaId']);
                                $citaEstado = $cita['estado'] ?? '';
                                if ($citaEstado !== 'Cancelado') {
                                    throw new \RuntimeException('No se puede anular una factura con pagos. Debe cancelar la cita primero.');
                                }
                            }

                            // revertir stock si la cita estaba Finalizada
                            $citaRepo = new \BetelCreativa\Infrastructure\AppointmentRepository();
                            $cita = $citaRepo->findById((int)$factura['citaId']);
                            if ($cita && $cita['estado'] === 'Finalizada') {
                                $matRepo = new CitaMaterialRepository();
                                $matRepo->reverseDeductionOnAnulacion((int)$factura['citaId'], (int)$_SESSION['user_id'], $facturaId, false);
                            }
                        }

                        $repo->cambiarEstado($facturaId, $nuevoEstado, (int)($_SESSION['user_id'] ?? 0), $motivo ?? '');
                        $db->commit();
                        ApiResponse::success(null, 'Factura ' . ($action === 'cerrar' ? 'cerrada' : 'anulada') . ' exitosamente.');
                    } catch (\Throwable $e) {
                        $db->rollBack();
                        ApiResponse::error($e->getMessage(), 500);
                    }
                } else {
                    ApiResponse::error('Acción no válida. Use crear, pagar, cerrar o anular.');
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }
}
