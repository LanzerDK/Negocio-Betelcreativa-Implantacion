<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\MaterialRepository;
use BetelCreativa\Infrastructure\StorageRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class StorageController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new StorageRepository();

        switch ($method) {
            case 'GET':
                $action = $_GET['action'] ?? '';

                if ($action === 'summary') {
                    $summary = $repo->getSummary();
                    ApiResponse::success($summary);
                } elseif ($action === 'history') {
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 15)));
                    $result = $repo->getHistory($page, $perPage);
                    ApiResponse::success($result);
                } elseif ($action === 'stock') {
                    $materialId = (int)($_GET['material_id'] ?? 0);
                    if (!$materialId) {
                        ApiResponse::error('ID de material requerido.');
                    }
                    $stock = $repo->getStockByMaterial($materialId);
                    ApiResponse::success($stock);
                } else {
                    ApiResponse::error('Acción no especificada. Use ?action=summary, history o stock.');
                }
                break;

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $action = $input['action'] ?? '';
                $userId = (int)($_SESSION['user_id'] ?? 0);

                $allowedReasons = ['compra', 'venta', 'devolucion', 'perdida', 'ajuste', 'reorganizacion', 'preparacion', 'optimizacion', 'otro'];

                if ($action === 'adjust') {
                    $materialId = (int)($input['material_id'] ?? 0);
                    $type = $input['type'] ?? '';
                    $tipoIngreso = $input['tipo_ingreso'] ?? 'Unitario';
                    $cantidadIngresada = (int)($input['cantidad_ingresada'] ?? 0);
                    $reason = trim($input['reason'] ?? '');
                    $notes = trim($input['notes'] ?? '');
                    $supplier = trim($input['supplier'] ?? '');
                    $purchasePrice = !empty($input['purchase_price']) ? (float)$input['purchase_price'] : null;
                    $locationId = !empty($input['location_id']) ? (int)$input['location_id'] : null;

                    if (!$materialId) {
                        ApiResponse::error('Material requerido.');
                    }
                    if (!in_array($type, ['entry', 'exit'])) {
                        ApiResponse::error('Tipo debe ser entry o exit.');
                    }
                    if ($cantidadIngresada <= 0) {
                        ApiResponse::error('La cantidad debe ser mayor a 0.');
                    }
                    if (!in_array($reason, $allowedReasons, true)) {
                        ApiResponse::error('Motivo no válido.');
                    }

                    // Backend Conversion Engine
                    $matRepo = new MaterialRepository();
                    $material = $matRepo->findById($materialId);
                    if (!$material) {
                        ApiResponse::error('Material no encontrado.', 404);
                    }
                    $factorConversion = $material->getFactorConversion();
                    $quantity = $tipoIngreso === 'Paquete'
                        ? $cantidadIngresada * $factorConversion
                        : $cantidadIngresada;

                    $tipoReferencia = match ($reason) {
                        'compra'      => 'compra',
                        'venta', 'devolucion' => 'venta',
                        default       => 'ajuste'
                    };

                    $extraMeta = [];
                    if ($supplier) $extraMeta['supplier'] = $supplier;
                    if ($purchasePrice !== null) $extraMeta['purchase_price'] = $purchasePrice;
                    $extraNote = !empty($extraMeta) ? json_encode(['notes' => $notes, 'meta' => $extraMeta]) : $notes;

                    if ($repo->recordAdjustment($materialId, $userId, $type, $quantity, $reason, $extraNote, $locationId, $tipoReferencia)) {
                        ApiResponse::success(null, 'Ajuste registrado exitosamente.');
                    } else {
                        ApiResponse::error('Error al registrar el ajuste.', 500);
                    }

                } elseif ($action === 'move') {
                    $materialId = (int)($input['material_id'] ?? 0);
                    $fromLocationId = (int)($input['from_location_id'] ?? 0);
                    $toLocationId = (int)($input['to_location_id'] ?? 0);
                    $quantity = (int)($input['quantity'] ?? 0);
                    $reason = trim($input['reason'] ?? '');
                    $notes = trim($input['notes'] ?? '');

                    if (!$materialId || !$fromLocationId || !$toLocationId) {
                        ApiResponse::error('Material, ubicación origen y destino requeridos.');
                    }
                    if ($fromLocationId === $toLocationId) {
                        ApiResponse::error('La ubicación de destino debe ser diferente a la actual.');
                    }
                    if ($quantity <= 0) {
                        ApiResponse::error('La cantidad debe ser mayor a 0.');
                    }
                    if (!in_array($reason, $allowedReasons, true)) {
                        ApiResponse::error('Motivo no válido.');
                    }

                    if ($repo->recordMove($materialId, $userId, $fromLocationId, $toLocationId, $quantity, $reason, $notes, 'transferencia')) {
                        ApiResponse::success(null, 'Material movido exitosamente.');
                    } else {
                        ApiResponse::error('Error al mover el material.', 500);
                    }

                } else {
                    ApiResponse::error('Acción no válida. Use adjust o move.');
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }
}
