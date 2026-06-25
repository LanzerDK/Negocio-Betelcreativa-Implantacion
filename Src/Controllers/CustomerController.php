<?php

namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\CustomerModel;
use BetelCreativa\Infrastructure\CustomerRepository;
use BetelCreativa\Helpers\ApiResponse;
use BetelCreativa\Helpers\CsrfHelper;
use BetelCreativa\Helpers\SessionHelpers;

// =============================================
// Controlador de Clientes
// Maneja todas las peticiones REST (GET/POST/PUT/DELETE)
// para la gestión de clientes. Verifica autenticación
// y token CSRF en operaciones de escritura.
// =============================================
class CustomerController
{
    // Punto de entrada: lee el método HTTP y ejecuta la acción correspondiente
    public static function handleRequest(): void
    {
        // Verifica que el usuario tenga sesión activa
        SessionHelpers::requireAuth();

        $method = $_SERVER['REQUEST_METHOD'];
        $repo = new CustomerRepository();

        switch ($method) {
            case 'GET':
                // GET /api/customers.php?list -> devuelve todos los clientes
                // GET /api/customers.php?id=5  -> devuelve un cliente específico
                if (isset($_GET['id'])) {
                    $customer = $repo->findById((int)$_GET['id']);
                    if ($customer) {
                        ApiResponse::success(self::toArray($customer));
                    } else {
                        ApiResponse::error('Cliente no encontrado.', 404);
                    }
                } else {
                    $customers = $repo->findAll();
                    ApiResponse::success(array_map([self::class, 'toArray'], $customers));
                }
                break;

            case 'POST':
                // Valida el token CSRF para evitar ataques de falsificación
                CsrfHelper::validateRequestOrFail();
                // Lee el cuerpo JSON de la petición o los datos POST normales
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

                // Crea el modelo con los datos recibidos
                $customer = new CustomerModel([
                    'firstName' => trim($input['firstName'] ?? ''),
                    'lastName'  => trim($input['lastName'] ?? ''),
                    'email'     => trim($input['email'] ?? ''),
                    'phone'     => trim($input['phone'] ?? ''),
                    'address'   => trim($input['address'] ?? ''),
                    'clientType'=> trim($input['clientType'] ?? 'Regular'),
                    'source'    => trim($input['source'] ?? 'Other'),
                    'notes'     => trim($input['notes'] ?? ''),
                    'preferences'=> trim($input['preferences'] ?? '')
                ]);

                // Validación: nombre y apellido son obligatorios
                if (empty($customer->getFirstName()) || empty($customer->getLastName())) {
                    ApiResponse::error('El nombre y apellido son obligatorios.');
                }

                // Validación: email duplicado
                $email = $customer->getEmail();
                if ($email && $repo->existsByEmail($email)) {
                    ApiResponse::error('Ya existe un cliente con ese correo electrónico.');
                }

                // Validación: teléfono duplicado
                $phone = $customer->getPhone();
                if ($phone && $repo->existsByPhone($phone)) {
                    ApiResponse::error('Ya existe un cliente con ese número de teléfono.');
                }

                if ($repo->save($customer)) {
                    ApiResponse::success(null, 'Cliente creado exitosamente.');
                } else {
                    ApiResponse::error('Error al crear el cliente.', 500);
                }
                break;

            case 'PUT':
                CsrfHelper::validateRequestOrFail();
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

                if (!$id) {
                    ApiResponse::error('ID de cliente requerido.');
                }

                // Verifica que el cliente exista antes de actualizar
                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Cliente no encontrado.', 404);
                }

                // Validación: email duplicado (excluyendo este cliente)
                $email = trim($input['email'] ?? '');
                if ($email && $email !== $existing->getEmail() && $repo->existsByEmail($email, $id)) {
                    ApiResponse::error('Ya existe un cliente con ese correo electrónico.');
                }

                // Validación: teléfono duplicado (excluyendo este cliente)
                $phone = trim($input['phone'] ?? '');
                if ($phone && $phone !== $existing->getPhone() && $repo->existsByPhone($phone, $id)) {
                    ApiResponse::error('Ya existe un cliente con ese número de teléfono.');
                }

                // Crea el modelo con los datos nuevos, manteniendo los existentes como fallback
                $customer = new CustomerModel([
                    'id'         => $id,
                    'firstName'  => trim($input['firstName'] ?? $existing->getFirstName()),
                    'lastName'   => trim($input['lastName'] ?? $existing->getLastName()),
                    'email'      => trim($input['email'] ?? $existing->getEmail()),
                    'phone'      => trim($input['phone'] ?? $existing->getPhone()),
                    'address'    => trim($input['address'] ?? $existing->getAddress()),
                    'clientType' => trim($input['clientType'] ?? $existing->getClientType()),
                    'source'     => trim($input['source'] ?? $existing->getSource()),
                    'notes'      => trim($input['notes'] ?? $existing->getNotes()),
                    'preferences'=> trim($input['preferences'] ?? $existing->getPreferences()),
                    'isActive'   => array_key_exists('is_active', $input) ? (bool)$input['is_active'] : $existing->isActive()
                ]);

                if ($repo->update($customer)) {
                    ApiResponse::success(null, 'Cliente actualizado exitosamente.');
                } else {
                    ApiResponse::error('Error al actualizar el cliente.', 500);
                }
                break;

            case 'DELETE':
                CsrfHelper::validateRequestOrFail();
                $id = (int)($_GET['id'] ?? 0);
                if (!$id) {
                    ApiResponse::error('ID de cliente requerido.');
                }
                $existing = $repo->findById($id);
                if (!$existing) {
                    ApiResponse::error('Cliente no encontrado.', 404);
                }
                $customer = new CustomerModel([
                    'id'         => $id,
                    'firstName'  => $existing->getFirstName(),
                    'lastName'   => $existing->getLastName(),
                    'email'      => $existing->getEmail(),
                    'phone'      => $existing->getPhone(),
                    'address'    => $existing->getAddress(),
                    'clientType' => $existing->getClientType(),
                    'source'     => $existing->getSource(),
                    'notes'      => $existing->getNotes(),
                    'preferences'=> $existing->getPreferences(),
                    'isActive'   => false
                ]);
                if ($repo->update($customer)) {
                    ApiResponse::success(null, 'Cliente deshabilitado exitosamente.');
                } else {
                    ApiResponse::error('Error al deshabilitar el cliente.', 500);
                }
                break;

            default:
                ApiResponse::error('Método no permitido.', 405);
        }
    }

    // Convierte un CustomerModel a un array asociativo para la respuesta JSON
    private static function toArray(CustomerModel $c): array
    {
        return [
            'id'          => $c->getId(),
            'firstName'   => $c->getFirstName(),
            'lastName'    => $c->getLastName(),
            'email'       => $c->getEmail(),
            'phone'       => $c->getPhone(),
            'address'     => $c->getAddress(),
            'clientType'  => $c->getClientType(),
            'source'      => $c->getSource(),
            'notes'       => $c->getNotes(),
            'preferences' => $c->getPreferences(),
            'avatar'      => $c->getAvatar(),
            'isActive'    => $c->isActive()
        ];
    }
}
