<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\MaterialModel;
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

                $material = new MaterialModel([
                    'code' => trim($input['code'] ?? ''),
                    'name' => trim($input['name'] ?? ''),
                    'price' => (float)($input['price'] ?? 0),
                    'costType' => $input['cost_type'] ?? 'unit',
                    'wholesaleQty' => isset($input['wholesale_qty']) ? (int)$input['wholesale_qty'] : null,
                    'stock' => (int)($input['stock'] ?? 0),
                    'categoryId' => !empty($input['category_id']) ? (int)$input['category_id'] : null,
                    'supplierId' => !empty($input['supplier_id']) ? (int)$input['supplier_id'] : null,
                    'locationId' => !empty($input['location_id']) ? (int)$input['location_id'] : null
                ]);

                if (empty($material->getName()) || empty($material->getCode())) {
                    ApiResponse::error('El nombre y código del material son obligatorios.');
                }

                if ($material->getPrice() < 0) {
                    ApiResponse::error('El precio no puede ser negativo.');
                }

                if ($material->getStock() < 0) {
                    ApiResponse::error('El stock no puede ser negativo.');
                }

                if ($repo->existsByCode($material->getCode())) {
                    ApiResponse::error('Ya existe un material con ese código.');
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

                $newCode = trim($input['code'] ?? $existing->getCode());
                $material = new MaterialModel([
                    'id' => $id,
                    'code' => $newCode,
                    'name' => trim($input['name'] ?? $existing->getName()),
                    'price' => array_key_exists('price', $input) ? (float)$input['price'] : $existing->getPrice(),
                    'costType' => $input['cost_type'] ?? $existing->getCostType(),
                    'wholesaleQty' => array_key_exists('wholesale_qty', $input) ? (!empty($input['wholesale_qty']) ? (int)$input['wholesale_qty'] : null) : $existing->getWholesaleQty(),
                    'stock' => array_key_exists('stock', $input) ? (int)$input['stock'] : $existing->getStock(),
                    'isActive' => array_key_exists('is_active', $input) ? (bool)$input['is_active'] : $existing->getIsActive(),
                    'categoryId' => array_key_exists('category_id', $input) ? (!empty($input['category_id']) ? (int)$input['category_id'] : null) : $existing->getCategoryId(),
                    'supplierId' => array_key_exists('supplier_id', $input) ? (!empty($input['supplier_id']) ? (int)$input['supplier_id'] : null) : $existing->getSupplierId(),
                    'locationId' => array_key_exists('location_id', $input) ? (!empty($input['location_id']) ? (int)$input['location_id'] : null) : $existing->getLocationId()
                ]);

                if ($material->getPrice() < 0) {
                    ApiResponse::error('El precio no puede ser negativo.');
                }

                if ($material->getStock() < 0) {
                    ApiResponse::error('El stock no puede ser negativo.');
                }

                if ($repo->existsByCode($newCode, $id)) {
                    ApiResponse::error('Ya existe otro material con ese código.');
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
            'imageUrl' => $m->getImageUrl(),
            'category_id' => $m->getCategoryId(),
            'supplier_id' => $m->getSupplierId(),
            'location_id' => $m->getLocationId(),
            'is_active' => $m->getIsActive()
        ];
    }
}
