<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\SupplierModel;
use BetelCreativa\Infrastructure\SupplierRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class SupplierController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new SupplierRepository();

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $s = $repo->findById((int)$_GET['id']);
                    if ($s) {
                        ApiResponse::success(self::toArray($s));
                    } else {
                        ApiResponse::error('Proveedor no encontrado', 404);
                    }
                } else {
                    $suppliers = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $suppliers));
                }
                break;

            case 'POST':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                $supplierType = $input['supplier_type'] ?? 'fijo';
                $supplier = new SupplierModel([
                    'company_name' => trim($input['company_name'] ?? ''),
                    'contact_name' => trim($input['contact_name'] ?? ''),
                    'phone' => trim($input['phone'] ?? ''),
                    'email' => trim($input['email'] ?? ''),
                    'address' => trim($input['address'] ?? ''),
                    'notes' => $supplierType === 'comodin' ? trim($input['notes'] ?? '') : null,
                    'supplier_type' => $supplierType,
                    'subtype' => $supplierType === 'comodin' ? trim($input['subtype'] ?? '') : null
                ]);

                if (empty($supplier->getCompanyName())) {
                    ApiResponse::error('El nombre de la empresa es obligatorio.');
                }

                if ($repo->existsByName($supplier->getCompanyName())) {
                    ApiResponse::error('Ya existe un proveedor con este nombre.');
                }

                if ($repo->save($supplier)) {
                    ApiResponse::success(null, 'Proveedor creado exitosamente.');
                } else {
                    ApiResponse::error('Error al crear el proveedor.', 500);
                }
                break;

            case 'PUT':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

                if (!$id) {
                    ApiResponse::error('ID de proveedor requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Proveedor no encontrado.', 404);
                }

                $newName = trim($input['company_name'] ?? $existing->getCompanyName());
                $newStatus = array_key_exists('is_active', $input) ? (int)$input['is_active'] : ($existing->getIsActive() ? 1 : 0);

                if ($newStatus === 0 && $existing->getIsActive()) {
                    if ($repo->countMaterials($id) > 0) {
                        ApiResponse::error('No se puede deshabilitar este proveedor, tiene materiales vinculados.');
                    }
                }

                $newSupplierType = $input['supplier_type'] ?? $existing->getSupplierType();
                $supplier = new SupplierModel([
                    'id' => $id,
                    'company_name' => $newName,
                    'contact_name' => trim($input['contact_name'] ?? $existing->getContactName()),
                    'phone' => trim($input['phone'] ?? $existing->getPhone()),
                    'email' => trim($input['email'] ?? $existing->getEmail()),
                    'address' => trim($input['address'] ?? $existing->getAddress()),
                    'notes' => $newSupplierType === 'comodin'
                        ? trim($input['notes'] ?? $existing->getNotes() ?? '')
                        : null,
                    'supplier_type' => $newSupplierType,
                    'subtype' => $newSupplierType === 'comodin'
                        ? trim($input['subtype'] ?? $existing->getSubtype() ?? '')
                        : null,
                    'is_active' => $newStatus
                ]);

                if ($repo->existsByName($newName, $id)) {
                    ApiResponse::error('Ya existe otro proveedor con este nombre.');
                }

                if ($repo->update($supplier)) {
                    ApiResponse::success(null, 'Proveedor actualizado exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar el proveedor.', 500);
                }
                break;

            case 'DELETE':
                ApiResponse::error('La eliminación física no está permitida. Use la inhabilitación.', 405);
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    private static function toArray(SupplierModel $s): array
    {
        return [
            'id' => $s->getId(),
            'company_name' => $s->getCompanyName(),
            'contact_name' => $s->getContactName(),
            'phone' => $s->getPhone(),
            'email' => $s->getEmail(),
            'address' => $s->getAddress(),
            'notes' => $s->getNotes(),
            'supplier_type' => $s->getSupplierType(),
            'subtype' => $s->getSubtype(),
            'is_active' => $s->getIsActive() ? 1 : 0
        ];
    }
}
