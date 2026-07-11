<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\LocationModel;
use BetelCreativa\Infrastructure\LocationRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class LocationController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new LocationRepository();

        switch ($method) {
            case 'GET':
                if (isset($_GET['zones'])) {
                    $zones = $repo->findZones();
                    ApiResponse::success($zones);
                } elseif (isset($_GET['id'])) {
                    $loc = $repo->findById((int)$_GET['id']);
                    if ($loc) {
                        ApiResponse::success(self::toArray($loc));
                    } else {
                        ApiResponse::error('Ubicación no encontrada', 404);
                    }
                } else {
                    $locations = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $locations));
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();

                $name = trim($input['name'] ?? '');
                $description = trim($input['description'] ?? '');
                $warehouseId = !empty($input['warehouse_id']) ? (int)$input['warehouse_id'] : null;
                $maxCapacity = (int)($input['max_capacity'] ?? 200);

                if (empty($name)) {
                    ApiResponse::error('El nombre de la ubicación es obligatorio.');
                }
                if ($maxCapacity < 1 || $maxCapacity > 200) {
                    ApiResponse::error('La capacidad máxima debe estar entre 1 y 200.', 400);
                }

                if ($repo->existsByName($name)) {
                    ApiResponse::error('Ya existe una ubicación con ese nombre.');
                }

                if ($warehouseId) {
                    $whRepo = new \BetelCreativa\Infrastructure\WarehouseRepository();
                    $wh = $whRepo->findById($warehouseId);
                    if (!$wh) {
                        ApiResponse::error('El almacén seleccionado no existe.', 400);
                    }
                    $shelfCount = $whRepo->getShelfCount($warehouseId);
                    if ($shelfCount >= $wh->getMaxShelves()) {
                        ApiResponse::error('El almacén ha alcanzado su límite máximo de estantes (' . $wh->getMaxShelves() . ').', 400);
                    }
                }

                $location = new LocationModel([
                    'name' => $name,
                    'warehouse_id' => $warehouseId,
                    'max_capacity' => $maxCapacity,
                    'description' => $description
                ]);

                if ($repo->save($location)) {
                    ApiResponse::success(null, 'Ubicación creada exitosamente.');
                } else {
                    ApiResponse::error('Error al crear la ubicación.', 500);
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
                    ApiResponse::error('ID de ubicación requerido.');
                }
                $loc = $repo->findById($id);
                if (!$loc) {
                    ApiResponse::error('Ubicación no encontrada.', 404);
                }
                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Ubicación eliminada exitosamente.');
                } else {
                    ApiResponse::error('Error al eliminar la ubicación.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    private static function toArray(LocationModel $l): array
    {
        return [
            'id' => $l->getId(),
            'name' => $l->getName(),
            'warehouse_id' => $l->getWarehouseId(),
            'max_capacity' => $l->getMaxCapacity(),
            'description' => $l->getDescription()
        ];
    }
}
