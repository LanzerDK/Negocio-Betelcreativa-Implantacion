<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\MaterialRepository;
use BetelCreativa\Infrastructure\StorageRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// StorageController — Gestión de almacén: ajustes de stock y movimientos entre ubicaciones
// Entrada/salida con conversión de unidades (paquete ↔ unidad) y validación de capacidad
class StorageController
{
    // Punto de entrada: enruta según método HTTP (GET para consultas, POST para acciones)
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new StorageRepository();

        switch ($method) {
            case 'GET':
                $action = $_GET['action'] ?? '';

                // Resumen general del almacén
                if ($action === 'summary') {
                    $summary = $repo->getSummary();
                    ApiResponse::success($summary);
                // Historial paginado de movimientos
                } elseif ($action === 'history') {
                    $page = max(1, (int)($_GET['page'] ?? 1));
                    $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 15)));
                    $result = $repo->getHistory($page, $perPage);
                    ApiResponse::success($result);
                // Stock por ubicación
                } elseif ($action === 'locations-stock') {
                    $stock = $repo->getAllLocationsWithStock();
                    ApiResponse::success($stock);
                // Stock de un material o ubicación específica
                } elseif ($action === 'stock') {
                    $materialId = (int)($_GET['material_id'] ?? 0);
                    $locationId = (int)($_GET['location_id'] ?? 0);
                    if ($materialId) {
                        $stock = $repo->getStockByMaterial($materialId);
                    } elseif ($locationId) {
                        $stock = $repo->getStockByLocation($locationId);
                    } else {
                        ApiResponse::error('ID de material o ubicación requerido.');
                    }
                    ApiResponse::success($stock);
                } else {
                    ApiResponse::error('Acción no especificada. Use ?action=summary, history o stock.');
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $action = $input['action'] ?? '';
                $userId = (int)($_SESSION['user_id'] ?? 0);

                // Motivos permitidos para movimientos de inventario
                $allowedReasons = ['compra', 'venta', 'devolucion', 'perdida', 'ajuste', 'reorganizacion', 'preparacion', 'optimizacion', 'otro'];

                // Ajuste de stock: entrada o salida con conversión de unidades
                if ($action === 'adjust') {
                    $materialId = (int)($input['material_id'] ?? 0);
                    $type = $input['type'] ?? '';
                    $tipoIngreso = $input['tipo_ingreso'] ?? 'Unitario';
                    $cantidadIngresada = (int)($input['cantidad_ingresada'] ?? 0);
                    $reason = trim($input['reason'] ?? '');
                    $notes = trim($input['notes'] ?? '');
                    $supplier = trim($input['supplier'] ?? '');
                    $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;
                    $purchasePrice = !empty($input['purchase_price']) ? (float)$input['purchase_price'] : null;
                    $locationId = !empty($input['location_id']) ? (int)$input['location_id'] : null;

                    // Validaciones de campos obligatorios
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

                    // Motor de conversión: si es Paquete, multiplica por factor de conversión
                    $matRepo = new MaterialRepository();
                    $material = $matRepo->findById($materialId);
                    if (!$material) {
                        ApiResponse::error('Material no encontrado.', 404);
                    }
                    $factorConversion = $material->getFactorConversion();
                    $quantity = $tipoIngreso === 'Paquete'
                        ? $cantidadIngresada * $factorConversion
                        : $cantidadIngresada;

                    // Validar capacidad máxima de la ubicación de destino
                    if ($type === 'entry' && $locationId) {
                        $locRepo = new \BetelCreativa\Infrastructure\LocationRepository();
                        $destLoc = $locRepo->findById($locationId);
                        if ($destLoc) {
                            $maxCap = $destLoc->getMaxCapacity();
                            $currentDestStock = $repo->getTotalStockAtLocation($locationId);
                            if (($currentDestStock + $quantity) > $maxCap) {
                                ApiResponse::error("La ubicación no tiene capacidad suficiente. Máx: {$maxCap}, ocupado: {$currentDestStock}, nuevo: {$quantity}.", 400);
                            }
                        }
                    }

                    // Determina el tipo de referencia para trazabilidad
                    $tipoReferencia = match ($reason) {
                        'compra'      => 'compra',
                        'venta', 'devolucion' => 'venta',
                        default       => 'ajuste'
                    };

                    // Metadatos adicionales (proveedor, precio de compra)
                    $extraMeta = [];
                    if ($supplier) $extraMeta['supplier'] = $supplier;
                    if ($purchasePrice !== null) $extraMeta['purchase_price'] = $purchasePrice;
                    $extraNote = !empty($extraMeta) ? json_encode(['notes' => $notes, 'meta' => $extraMeta]) : $notes;

                    if ($repo->recordAdjustment($materialId, $userId, $type, $quantity, $reason, $extraNote, $locationId, $tipoReferencia)) {
                        // Actualiza el proveedor del material si se seleccionó uno
                        if ($supplierId && $matRepo) {
                            $existingMaterial = $matRepo->findById($materialId);
                            if ($existingMaterial) {
                                $db2 = \BetelCreativa\Config\Database::getConnection();
                                $db2->prepare("UPDATE materials SET supplier_id = :sid WHERE material_id = :mid")
                                    ->execute([':sid' => $supplierId, ':mid' => $materialId]);
                            }
                        }
                        ApiResponse::success(null, 'Ajuste registrado exitosamente.');
                    } else {
                        ApiResponse::error('Error al registrar el ajuste.', 500);
                    }

                // Movimiento de material entre ubicaciones
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

                    // Validar capacidad de la ubicación de destino
                    $locRepo = new \BetelCreativa\Infrastructure\LocationRepository();
                    $destLoc = $locRepo->findById($toLocationId);
                    if (!$destLoc) {
                        ApiResponse::error('Ubicación de destino no encontrada.', 404);
                    }
                    $maxCap = $destLoc->getMaxCapacity();
                    $currentDestStock = $repo->getTotalStockAtLocation($toLocationId);
                    if (($currentDestStock + $quantity) > $maxCap) {
                        ApiResponse::error("La ubicación de destino no tiene capacidad suficiente. Capacidad máxima: {$maxCap}, ocupado actual: {$currentDestStock}, intentando mover: {$quantity}.", 400);
                    }

                    if ($repo->recordMove($materialId, $userId, $fromLocationId, $toLocationId, $quantity, $reason, $notes)) {
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
