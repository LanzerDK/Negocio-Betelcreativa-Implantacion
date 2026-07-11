<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\WarehouseModel;
use BetelCreativa\Infrastructure\WarehouseRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// WarehouseController — CRUD de almacenes (bodegas)
// Cada almacén tiene un código único, capacidad máxima de estantes y ubicación física
class WarehouseController
{
    // Punto de entrada: enruta según método HTTP (GET/POST/DELETE)
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new WarehouseRepository();

        switch ($method) {
            case 'GET':
                // GET con ?id — detalle con conteo de estantes
                // GET sin parámetros — lista completa con conteo de estantes por almacén
                if (isset($_GET['id'])) {
                    $wh = $repo->findById((int)$_GET['id']);
                    if ($wh) {
                        $data = self::toArray($wh);
                        $data['shelf_count'] = $repo->getShelfCount($wh->getId());
                        ApiResponse::success($data);
                    } else {
                        ApiResponse::error('Almacén no encontrado', 404);
                    }
                } else {
                    $warehouses = $repo->findAll();
                    $result = array_map(function ($w) use ($repo) {
                        $data = self::toArray($w);
                        $data['shelf_count'] = $repo->getShelfCount($w->getId());
                        return $data;
                    }, $warehouses);
                    ApiResponse::success($result);
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();

                $code = trim($input['code'] ?? '');
                $name = trim($input['name'] ?? '');
                $location = trim($input['location'] ?? '');
                $maxShelves = (int)($input['max_shelves'] ?? 100);

                // Auto-genera código si no se especifica
                if (empty($code)) {
                    $code = $repo->getNextCode();
                }
                // Validaciones de campos
                if (empty($name)) {
                    ApiResponse::error('El nombre del almacén es obligatorio.');
                }
                if (empty($location)) {
                    ApiResponse::error('La ubicación del almacén es obligatoria.');
                }
                if ($maxShelves < 1 || $maxShelves > 100) {
                    ApiResponse::error('El máximo de estantes debe estar entre 1 y 100.');
                }

                // Validación de unicidad
                if ($repo->existsByCode($code)) {
                    ApiResponse::error('Ya existe un almacén con ese código.');
                }
                if ($repo->existsByName($name)) {
                    ApiResponse::error('Ya existe un almacén con ese nombre.');
                }

                $warehouse = new WarehouseModel([
                    'code' => $code,
                    'name' => $name,
                    'location' => $location ?: null,
                    'max_shelves' => $maxShelves
                ]);

                if ($repo->save($warehouse)) {
                    ApiResponse::success(null, 'Almacén creado exitosamente.');
                } else {
                    ApiResponse::error('Error al crear el almacén.', 500);
                }
                break;

            case 'DELETE':
                $raw = file_get_contents('php://input');
                $input = json_decode($raw, true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de almacén requerido.');
                }
                $wh = $repo->findById($id);
                if (!$wh) {
                    ApiResponse::error('Almacén no encontrado.', 404);
                }
                // No permite eliminar si tiene estantes asignados
                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Almacén eliminado exitosamente.');
                } else {
                    ApiResponse::error('No se puede eliminar: el almacén tiene estantes asignados.', 400);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    // Convierte un WarehouseModel a array asociativo para respuesta JSON
    private static function toArray(WarehouseModel $w): array
    {
        return [
            'id' => $w->getId(),
            'code' => $w->getCode(),
            'name' => $w->getName(),
            'location' => $w->getLocation(),
            'max_shelves' => $w->getMaxShelves()
        ];
    }
}
