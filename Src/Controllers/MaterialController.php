<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\MaterialModel;
use BetelCreativa\Infrastructure\CategoryRepository;
use BetelCreativa\Infrastructure\MaterialRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class MaterialController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new MaterialRepository();

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $m = $repo->findById((int)$_GET['id']);
                    if ($m) {
                        ApiResponse::success(self::toArray($m));
                    } else {
                        ApiResponse::error('Material no encontrado', 404);
                    }
                } elseif (isset($_GET['category_id'])) {
                    $materials = $repo->findByCategory((int)$_GET['category_id']);
                    ApiResponse::success(array_map([self::class, 'toArray'], $materials));
                } else {
                    $materials = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $materials));
                }
                break;

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                $locationId = !empty($input['location_id']) ? (int)$input['location_id'] : null;
                if (!$locationId) {
                    $locDb = \BetelCreativa\Config\Database::getConnection();
                    $locStmt = $locDb->prepare("SELECT location_id FROM locations ORDER BY location_id ASC LIMIT 1");
                    $locStmt->execute();
                    $locationId = (int)$locStmt->fetchColumn() ?: null;
                }

                $supplierId = !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null;

                $material = new MaterialModel([
                    'code' => trim($input['code'] ?? ''),
                    'name' => trim($input['name'] ?? ''),
                    'price' => (float)($input['price'] ?? 0),
                    'costType' => $input['cost_type'] ?? 'unit',
                    'wholesaleQty' => isset($input['wholesale_qty']) ? (int)$input['wholesale_qty'] : null,
                    'stock' => 0,
                    'categoryId' => !empty($input['category_id']) ? (int)$input['category_id'] : null,
                    'materialType' => $input['material_type'] ?? 'consumible',
                    'supplierId' => $supplierId,
                    'locationId' => $locationId,
                    'unidadCompra' => $input['unidad_compra'] ?? 'Unidad',
                    'unidadConsumo' => $input['unidad_consumo'] ?? 'Unidad',
                    'factorConversion' => max(1, (int)($input['factor_conversion'] ?? 1))
                ]);

                if (empty($material->getName()) || empty($material->getCode())) {
                    ApiResponse::error('El nombre y código del material son obligatorios.');
                }

                if ($material->getPrice() < 0) {
                    ApiResponse::error('El precio no puede ser negativo.');
                }

                if ($material->getCategoryId()) {
                    $catRepo = new CategoryRepository();
                    $cat = $catRepo->findById($material->getCategoryId());
                    if (!$cat || $cat->getStatus() !== 'Active') {
                        ApiResponse::error('La categoría seleccionada no está disponible.');
                    }
                }

                if ($supplierId !== null) {
                    $supDb = \BetelCreativa\Config\Database::getConnection();
                    $supStmt = $supDb->prepare("SELECT COUNT(*) FROM suppliers WHERE supplier_id = :id AND is_active = 1");
                    $supStmt->execute([':id' => $supplierId]);
                    if ((int)$supStmt->fetchColumn() === 0) {
                        ApiResponse::error('El proveedor seleccionado no está disponible.');
                    }
                }

                if ($repo->existsByCode($material->getCode())) {
                    ApiResponse::error('Ya existe un material con ese código.');
                }

                if ($repo->existsByName($material->getName())) {
                    ApiResponse::error('Ya existe un material con ese nombre.');
                }

                if ($repo->save($material)) {
                    ApiResponse::success(null, 'Material creado exitosamente.');
                } else {
                    ApiResponse::error('Error al crear el material.', 500);
                }
                break;

            case 'PUT':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

                if (!$id) {
                    ApiResponse::error('ID de material requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Material no encontrado.', 404);
                }

                $supplierId = array_key_exists('supplier_id', $input)
                    ? (!empty($input['supplier_id']) ? (int)$input['supplier_id'] : null)
                    : $existing->getSupplierId();

                $material = new MaterialModel([
                    'id' => $id,
                    'code' => $existing->getCode(),
                    'name' => trim($input['name'] ?? $existing->getName()),
                    'price' => array_key_exists('price', $input) ? (float)$input['price'] : $existing->getPrice(),
                    'costType' => $input['cost_type'] ?? $existing->getCostType(),
                    'wholesaleQty' => array_key_exists('wholesale_qty', $input) ? (!empty($input['wholesale_qty']) ? (int)$input['wholesale_qty'] : null) : $existing->getWholesaleQty(),
                    'stock' => $existing->getStock(),
                    'isActive' => array_key_exists('is_active', $input) ? (int)$input['is_active'] : $existing->getIsActive(),
                    'categoryId' => array_key_exists('category_id', $input) ? (!empty($input['category_id']) ? (int)$input['category_id'] : null) : $existing->getCategoryId(),
                    'materialType' => $existing->getMaterialType(),
                    'supplierId' => $supplierId,
                    'locationId' => $existing->getLocationId(),
                    'unidadCompra' => $input['unidad_compra'] ?? $existing->getUnidadCompra(),
                    'unidadConsumo' => $input['unidad_consumo'] ?? $existing->getUnidadConsumo(),
                    'factorConversion' => array_key_exists('factor_conversion', $input) ? max(1, (int)$input['factor_conversion']) : $existing->getFactorConversion()
                ]);

                if ($material->getPrice() < 0) {
                    ApiResponse::error('El precio no puede ser negativo.');
                }

                $newCategoryId = $material->getCategoryId();
                if ($newCategoryId !== null && $newCategoryId !== $existing->getCategoryId()) {
                    $catRepo = new CategoryRepository();
                    $cat = $catRepo->findById($newCategoryId);
                    if (!$cat || $cat->getStatus() !== 'Active') {
                        ApiResponse::error('La categoría seleccionada no está disponible.');
                    }
                }

                if ($supplierId !== null && array_key_exists('supplier_id', $input)) {
                    $supDb = \BetelCreativa\Config\Database::getConnection();
                    $supStmt = $supDb->prepare("SELECT COUNT(*) FROM suppliers WHERE supplier_id = :id AND is_active = 1");
                    $supStmt->execute([':id' => $supplierId]);
                    if ((int)$supStmt->fetchColumn() === 0) {
                        ApiResponse::error('El proveedor seleccionado no está disponible.');
                    }
                }

                if ($material->getIsActive() === 0 && $existing->getStock() > 0) {
                    ApiResponse::error('No se puede deshabilitar: el material tiene existencia (' . $existing->getStock() . ' unidades).');
                }

                $newName = $material->getName();
                if ($repo->existsByName($newName, $id)) {
                    ApiResponse::error('Ya existe otro material con ese nombre.');
                }

                if ($repo->update($material)) {
                    ApiResponse::success(null, 'Material actualizado exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar el material.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de material requerido.');
                }
                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Material eliminado exitosamente.');
                } else {
                    ApiResponse::error('Error al eliminar el material.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    private static function toArray(MaterialModel $m): array
    {
        return [
            'id' => $m->getId(),
            'code' => $m->getCode(),
            'name' => $m->getName(),
            'price' => $m->getPrice(),
            'cost_type' => $m->getCostType(),
            'wholesale_qty' => $m->getWholesaleQty(),
            'stock' => $m->getStock(),
            'reservedStock' => $m->getReservedStock(),
            'imageUrl' => $m->getImageUrl(),
            'category_id' => $m->getCategoryId(),
            'material_type' => $m->getMaterialType(),
            'supplier_id' => $m->getSupplierId(),
            'location_id' => $m->getLocationId(),
            'is_active' => $m->getIsActive(),
            'unidad_compra' => $m->getUnidadCompra(),
            'unidad_consumo' => $m->getUnidadConsumo(),
            'factor_conversion' => $m->getFactorConversion()
        ];
    }
}
