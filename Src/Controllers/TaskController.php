<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Infrastructure\TaskRepository;
use BetelCreativa\Domain\TaskModel;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// TaskController — CRUD de tareas operativas
// Lista pendientes/completadas, permite cambio de estado y prioridad
class TaskController
{
    // Punto de entrada: enruta según método HTTP (GET/POST/PUT/DELETE)
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new TaskRepository();

        switch ($method) {
            case 'GET':
                // GET con ?completed=1 — tareas completadas; sin parámetro — pendientes
                if (($_GET['completed'] ?? '') === '1') {
                    $tasks = $repo->findAllCompleted();
                } else {
                    $tasks = $repo->findAllPending();
                }
                ApiResponse::success(array_map(fn($t) => self::toArray($t), $tasks));
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $title = trim($input['title'] ?? '');
                $priority = $input['priority'] ?? 'medium';

                // Validaciones
                if (!$title) {
                    ApiResponse::error('El título de la tarea es requerido.');
                }
                if (strlen($title) > 255) {
                    ApiResponse::error('El título no puede exceder los 255 caracteres.');
                }
                if (!in_array($priority, ['low', 'medium', 'high'])) {
                    ApiResponse::error('Prioridad inválida.');
                }

                $task = new TaskModel([
                    'title' => $title,
                    'priority' => $priority,
                    'status' => 'pending',
                ]);

                if ($repo->save($task)) {
                    ApiResponse::success(null, 'Tarea creada exitosamente.');
                } else {
                    ApiResponse::error('Error al crear la tarea.', 500);
                }
                break;

            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de tarea requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Tarea no encontrada.', 404);
                }

                // Cambio de estado: pending ↔ completed
                $newStatus = $input['status'] ?? $existing->getStatus();

                if (!in_array($newStatus, ['pending', 'completed'])) {
                    ApiResponse::error('Estado inválido.');
                }

                $existing->setStatus($newStatus);

                if ($repo->update($existing)) {
                    ApiResponse::success(null, 'Tarea actualizada.');
                } else {
                    ApiResponse::error('Error al actualizar la tarea.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de tarea requerido.');
                }
                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Tarea eliminada.');
                } else {
                    ApiResponse::error('Error al eliminar la tarea.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no soportado.', 405);
        }
    }

    // Convierte un TaskModel a array asociativo para respuesta JSON
    private static function toArray(TaskModel $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'priority' => $task->getPriority(),
            'status' => $task->getStatus(),
            'createdAt' => $task->getCreatedAt(),
            'completedAt' => $task->getCompletedAt(),
        ];
    }
}
