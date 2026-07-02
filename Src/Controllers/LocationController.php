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
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                $name = trim($input['name'] ?? '');
                $description = trim($input['description'] ?? '');
                if ($description !== '') {
                    $description = mb_convert_case($description, MB_CASE_TITLE, 'UTF-8');
                }

                if (empty($name)) {
                    ApiResponse::error('El nombre de la ubicación es obligatorio.');
                }

                if ($repo->existsByName($name)) {
                    ApiResponse::error('Ya existe una ubicación con ese nombre.');
                }

                $location = new LocationModel([
                    'name' => $name,
                    'description' => $description
                ]);

                if ($repo->save($location)) {
                    ApiResponse::success(null, 'Ubicación creada exitosamente.');
                } else {
                    ApiResponse::error('Error al crear la ubicación.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_GET;
                $id = (int)($input['id'] ?? 0);
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
            'description' => $l->getDescription()
        ];
    }
}
