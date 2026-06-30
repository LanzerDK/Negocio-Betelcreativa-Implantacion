<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;
use BetelCreativa\Infrastructure\FacturaRepository;
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

                if ($action === 'detalle' && isset($_GET['cita_id'])) {
                    $detalle = $repo->getDetalleByCitaId((int)$_GET['cita_id']);
                    if (!$detalle) {
                        ApiResponse::error('Cita no encontrada.', 404);
                        return;
                    }
                    $tasaService = new ExchangeRateService();
                    $detalle['tasaBcv'] = $tasaService->getEffectiveRate();
                    ApiResponse::success($detalle);

                } elseif ($action === 'tasa') {
                    $tasaService = new ExchangeRateService();
                    ApiResponse::success(['tasa' => $tasaService->getEffectiveRate()]);

                } else {
                    ApiResponse::error('Acción no válida. Use ?action=detalle&cita_id=X o ?action=tasa.');
                }
                break;

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $action = $input['action'] ?? '';

                if ($action === 'crear') {
                    $citaId = (int)($input['cita_id'] ?? 0);
                    $costoServicio = (float)($input['costo_servicio'] ?? 0);
                    $notasCuota = trim($input['notas_cuota'] ?? '');

                    if (!$citaId) {
                        ApiResponse::error('ID de cita requerido.');
                        return;
                    }

                    $existente = $repo->getFacturaByCitaId($citaId);
                    if ($existente) {
                        ApiResponse::error('La cita ya tiene una factura.', 400);
                        return;
                    }

                    $id = $repo->crearFactura($citaId, $costoServicio, $notasCuota);
                    if ($id) {
                        ApiResponse::success(['id' => $id], 'Factura creada exitosamente.');
                    } else {
                        ApiResponse::error('Error al crear la factura.', 500);
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
                    $allowed = ['divisas', 'efectivo', 'pagomovil'];
                    if (!in_array($metodoPago, $allowed, true)) {
                        ApiResponse::error('Método de pago no válido.');
                        return;
                    }
                    if ($tasaUsada <= 0) {
                        $tasaService = new ExchangeRateService();
                        $tasaUsada = $tasaService->getEffectiveRate();
                    }

                    if ($repo->registrarPago($facturaId, $monto, $metodoPago, $tasaUsada)) {
                        $totalPagado = 0;
                        $pagos = $repo->getPagosByFacturaId($facturaId);
                        foreach ($pagos as $p) $totalPagado += (float)$p['monto'];
                        ApiResponse::success([
                            'totalPagado' => $totalPagado,
                            'pagos'       => $pagos
                        ], 'Pago registrado exitosamente.');
                    } else {
                        ApiResponse::error('Error al registrar el pago.', 500);
                    }

                } elseif ($action === 'cerrar' || $action === 'anular') {
                    $facturaId = (int)($input['factura_id'] ?? 0);
                    if (!$facturaId) {
                        ApiResponse::error('ID de factura requerido.');
                        return;
                    }
                    $nuevoEstado = $action === 'cerrar' ? 'cerrada' : 'anulada';
                    if ($repo->cambiarEstado($facturaId, $nuevoEstado)) {
                        ApiResponse::success(null, 'Factura ' . ($action === 'cerrar' ? 'cerrada' : 'anulada') . ' exitosamente.');
                    } else {
                        ApiResponse::error('Error al cambiar estado.', 500);
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
