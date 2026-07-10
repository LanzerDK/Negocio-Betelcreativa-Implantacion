<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\CategoryModel;
use BetelCreativa\Infrastructure\CategoryRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

class CategoryController
{
    public static function handleRequest(): void
    {
        SessionHelpers::requireAuth();
        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new CategoryRepository();

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $c = $repo->findById((int)$_GET['id']);
                    if ($c) {
                        ApiResponse::success(self::toArray($c));
                    } else {
                        ApiResponse::error('Categoría no encontrada', 404);
                    }
                } else {
                    $categories = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $categories));
                }
                break;

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true) ?? [];
                if (!empty($input['csrf_token'])) {
                    $_POST['csrf_token'] = $input['csrf_token'];
                }
                CsrfHelper::validateRequestOrFail();

                $category = new CategoryModel([
                    'name' => trim($input['name'] ?? ''),
                    'description' => trim($input['description'] ?? ''),
                    'status' => trim($input['status'] ?? 'Active')
                ]);

                if (empty($category->getName())) {
                    ApiResponse::error('El nombre de la categoría es obligatorio.');
                }

                if ($repo->existsByName($category->getName())) {
                    ApiResponse::error('Ya existe una categoría con este nombre.');
                }

                if ($repo->save($category)) {
                    ApiResponse::success(null, 'Categoría creada exitosamente.');
                } else {
                    ApiResponse::error('Error al crear la categoría.', 500);
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
                    ApiResponse::error('ID de categoría requerido.');
                }

                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Categoría no encontrada.', 404);
                }

                $newName = trim($input['name'] ?? $existing->getName());
                $newStatus = trim($input['status'] ?? $existing->getStatus());

                if ($newStatus === 'Inactive' && $existing->getStatus() === 'Active') {
                    if ($repo->countMaterials($id) > 0) {
                        ApiResponse::error('No se puede Deshabilitar esta Categoría, Tiene Materiales Vinculados');
                    }
                }

                $category = new CategoryModel([
                    'id' => $id,
                    'name' => $newName,
                    'description' => trim($input['description'] ?? $existing->getDescription()),
                    'status' => $newStatus
                ]);

                if ($repo->existsByName($newName, $id)) {
                    ApiResponse::error('Ya existe otra categoría con este nombre.');
                }

                if ($repo->update($category)) {
                    ApiResponse::success(null, 'Categoría actualizada exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar la categoría.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de categoría requerido.');
                }
                if ($repo->delete($id)) {
                    ApiResponse::success(null, 'Categoría eliminada exitosamente.');
                } else {
                    ApiResponse::error('Error al eliminar la categoría.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    private static function toArray(CategoryModel $c): array
    {
        return [
            'id' => $c->getId(),
            'name' => $c->getName(),
            'description' => $c->getDescription(),
            'status' => $c->getStatus(),
            'imageUrl' => $c->getImageUrl()
        ];
    }
}
