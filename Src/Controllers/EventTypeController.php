<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\EventTypeModel;
use BetelCreativa\Infrastructure\EventTypeRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// EventTypeController — CRUD de tipos de evento
// Tipos como "Boda", "XV Años", "Corporativo", etc. asociados a citas
class EventTypeController
{
    // Punto de entrada: enruta según método HTTP (GET/POST/PUT/DELETE)
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new EventTypeRepository();

        switch ($method) {
            case 'GET':
                // GET con ?id — detalle de un tipo; sin parámetros — lista completa
                if (isset($_GET['id'])) {
                    $et = $repo->findById((int)$_GET['id']);
                    if ($et) {
                        ApiResponse::success(self::toArray($et));
                    } else {
                        ApiResponse::error('Tipo de evento no encontrado.', 404);
                    }
                } else {
                    $types = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $types));
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();

                // Validación: nombre obligatorio y único
                $name = trim($input['name'] ?? '');
                if (empty($name)) {
                    ApiResponse::error('El nombre del tipo de evento es obligatorio.');
                }

                if ($repo->existsByName($name)) {
                    ApiResponse::error('Ya existe un tipo de evento con este nombre.');
                }

                $type = new EventTypeModel(['name' => $name]);
                if ($repo->save($type)) {
                    ApiResponse::success(null, 'Tipo de evento creado exitosamente.');
                } else {
                    ApiResponse::error('Error al crear el tipo de evento.', 500);
                }
                break;

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

                if (!$id) {
                    ApiResponse::error('ID de tipo de evento requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Tipo de evento no encontrado.', 404);
                }

                $newName = trim($input['name'] ?? $existing->getName());
                // Validación: nombre único excluyendo el ID actual
                if ($repo->existsByName($newName, $id)) {
                    ApiResponse::error('Ya existe otro tipo de evento con este nombre.');
                }

                $type = new EventTypeModel([
                    'id' => $id,
                    'name' => $newName
                ]);

                if ($repo->update($type)) {
                    ApiResponse::success(null, 'Tipo de evento actualizado exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar el tipo de evento.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de tipo de evento requerido.');
                }

                // Protección: no eliminar si hay citas asociadas
                if ($repo->hasAppointments($id)) {
                    ApiResponse::error('No se puede eliminar: hay citas asociadas a este tipo de evento.');
                }

                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Tipo de evento eliminado exitosamente.');
                } else {
                    ApiResponse::error('Error al eliminar el tipo de evento.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    // Convierte un EventTypeModel a array asociativo para respuesta JSON
    private static function toArray(EventTypeModel $et): array
    {
        return [
            'id' => $et->getId(),
            'name' => $et->getName(),
            'isActive' => $et->isActive()
        ];
    }
}
