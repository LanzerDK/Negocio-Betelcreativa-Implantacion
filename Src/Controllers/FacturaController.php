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
                    if (!in_array($status, ['abiertas', 'pendientes', 'pagadas'], true)) {
                        ApiResponse::error('Estado no válido. Use abiertas, pendientes o pagadas.');
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
                        "SELECT `value` FROM settings WHERE `key` = 'terminos_condiciones'"
                    );
                    $stmt->execute();
                    $terminos = $stmt->fetchColumn();
                    ApiResponse::success(['terminos' => $terminos ?: '']);

                } elseif ($action === 'tasa') {
                    $tasaService = new ExchangeRateService();
                    ApiResponse::success(['tasa' => $tasaService->getEffectiveRate()]);

                } elseif ($action === 'metodos-pago') {
                    $stmt = \Betelcreativa\Config\Database::getConnection()->prepare(
                        "SELECT `value` FROM settings WHERE `key` = 'metodos_pago'"
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
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $action = $input['action'] ?? '';

                if ($action === 'crear') {
                    $citaId = (int)($input['cita_id'] ?? 0);
                    $costoServicio = (float)($input['costo_servicio'] ?? 0);
                    $descripcionServicio = trim($input['descripcion_servicio'] ?? '') ?: null;
                    $planTipo = $input['plan_tipo'] ?? 'contado';
                    $planCuotasTotal = isset($input['plan_cuotas_total']) ? (int)$input['plan_cuotas_total'] : null;
                    $createdByName = $_SESSION['user_name'] ?? 'Usuario';

                    if (!$citaId) {
                        ApiResponse::error('ID de cita requerido.');
                        return;
                    }

                    if (!in_array($planTipo, ['contado', 'cuotas'], true)) {
                        ApiResponse::error('Tipo de plan no válido.');
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

                    $planMontoCuotaSugerido = null;
                    if ($planTipo === 'cuotas') {
                        if (!$planCuotasTotal || $planCuotasTotal < 2) {
                            ApiResponse::error('Indique el número de cuotas (mínimo 2).');
                            return;
                        }
                        $totales = FacturaCalculadora::calcularTotales($costoServicio, $detalle['materiales'] ?? []);
                        $planMontoCuotaSugerido = round($totales['total'] / $planCuotasTotal, 2);
                    }

                    $db = Database::getConnection();
                    $db->beginTransaction();
                    try {
                        // #2: check for existing factura INSIDE transaction with FOR UPDATE
                        $lockStmt = $db->prepare("SELECT id FROM facturas WHERE cita_id = :cid FOR UPDATE");
                        $lockStmt->execute([':cid' => $citaId]);
                        if ($lockStmt->fetch()) {
                            throw new \RuntimeException('La cita ya tiene una factura.');
                        }

                        $id = $repo->crearFactura($citaId, $costoServicio, '', $createdByName, $descripcionServicio, $planTipo, $planCuotasTotal, $planMontoCuotaSugerido, (int)($_SESSION['user_id'] ?? 0));

                        if ($planTipo === 'contado') {
                            $metodoPago = trim($input['metodo_pago'] ?? 'Efectivo');
                            if ($metodoPago === '') $metodoPago = 'Efectivo';
                            $esDivisa = stripos($metodoPago, 'divisa') !== false || (float)($input['monto_usd'] ?? 0) > 0;
                            $tasa = (float)($input['tasa_usada'] ?? 0);
                            if ($tasa <= 0) {
                                $tasaService = new ExchangeRateService();
                                $tasa = $tasaService->getEffectiveRate();
                            }
                            if ($tasa <= 0) {
                                throw new \RuntimeException('Tasa BCV no disponible. Intente de nuevo o especifique una tasa manualmente.');
                            }
                            // Re-read factura with lock to get exact total
                            $fLock = $db->prepare("SELECT total_factura AS totalFactura FROM facturas WHERE id = :fid FOR UPDATE");
                            $fLock->execute([':fid' => $id]);
                            $fRow = $fLock->fetch();
                            $montoTotal = $fRow ? (float)$fRow['totalFactura'] : 0;
                            if ($esDivisa) {
                                $montoUsd = (float)($input['monto_usd'] ?? 0);
                                if ($montoUsd <= 0) {
                                    throw new \RuntimeException('Debe especificar un monto en USD mayor a cero para pagos en divisas.');
                                }
                            } else {
                                $montoUsd = $tasa > 0 ? $montoTotal / $tasa : $montoTotal;
                            }
                            if ($montoUsd * $tasa > $montoTotal + 0.01) {
                                throw new \RuntimeException('El monto del pago supera el total de la factura.');
                            }
                            // manageTransaction=false — outer transaction handles commit/rollback
                            $reciboId = $repo->registrarPago($id, $montoUsd, $metodoPago, $tasa, false);
                            $repo->cambiarEstado($id, 'cerrada', (int)($_SESSION['user_id'] ?? 0));
                        }

                        $db->commit();

                        if ($planTipo === 'contado') {
                            ApiResponse::success(['id' => $id, 'planTipo' => 'contado', 'reciboId' => $reciboId], 'Factura creada y pagada exitosamente.');
                        } else {
                            ApiResponse::success(['id' => $id, 'planTipo' => 'cuotas', 'planCuotasTotal' => $planCuotasTotal, 'planMontoCuotaSugerido' => $planMontoCuotaSugerido], 'Plan de cuotas creado exitosamente.');
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

                        $totalPagadoVes = $repo->getTotalPagadoVes($facturaId);
                        if ($totalPagadoVes + ($monto * $tasaUsada) > (float)$factura['totalFactura'] + 0.01) {
                            throw new \RuntimeException('El monto del pago supera el saldo pendiente de la factura.');
                        }

                        $reciboId = $repo->registrarPago($facturaId, $monto, $metodoPago, $tasaUsada, false);
                        $db->commit();

                        $pagos = $repo->getPagosByFacturaId($facturaId);
                        $totalPagado = 0;
                        foreach ($pagos as $p) $totalPagado += (float)$p['monto'];
                        ApiResponse::success([
                            'totalPagado' => $totalPagado,
                            'pagos'       => $pagos,
                            'reciboId'    => $reciboId
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

                        if ($action === 'cerrar') {
                            $totalPagadoVes = $repo->getTotalPagadoVes($facturaId);
                            if ($totalPagadoVes < (float)$factura['totalFactura'] - 0.01) {
                                throw new \RuntimeException('No se puede cerrar una factura con saldo pendiente.');
                            }
                        } else {
                            // anular: revertir stock si la cita estaba Finalizada
                            $citaRepo = new \BetelCreativa\Infrastructure\AppointmentRepository();
                            $cita = $citaRepo->findById((int)$factura['citaId']);
                            if ($cita && $cita['estado'] === 'Finalizada') {
                                $matRepo = new CitaMaterialRepository();
                                $matRepo->reverseDeductionOnAnulacion((int)$factura['citaId'], (int)$_SESSION['user_id'], $facturaId, false);
                            }
                        }

                        $repo->cambiarEstado($facturaId, $nuevoEstado, (int)($_SESSION['user_id'] ?? 0));
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
