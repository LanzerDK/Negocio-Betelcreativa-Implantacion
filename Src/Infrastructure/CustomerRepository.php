<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\CustomerModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// =============================================
// Repositorio de Clientes
// Capa de acceso a datos para la tabla `customers`
// Traduce columnas de la BD (snake_case) a alias
// que el modelo entiende (camelCase)
// =============================================
class CustomerRepository
{
    private PDO $db;

    // Inicializa la conexión PDO usando el singleton de Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Obtiene todos los clientes activos, ordenados del más reciente al más antiguo
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT customer_id AS id, first_name AS firstName, last_name AS lastName,
                        email, phone, address, client_type AS clientType, source,
                        notes, preferences, avatar, is_active AS isActive
                 FROM customers
                 ORDER BY customer_id DESC"
            );
            $customers = [];
            while ($row = $stmt->fetch()) {
                $customers[] = new CustomerModel($row);
            }
            return $customers;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    // Busca un cliente por su ID
    public function findById(int $id): ?CustomerModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT customer_id AS id, first_name AS firstName, last_name AS lastName,
                        email, phone, address, client_type AS clientType, source,
                        notes, preferences, avatar, is_active AS isActive
                 FROM customers WHERE customer_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new CustomerModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    // Inserta un nuevo cliente en la BD y devuelve true si tuvo éxito
    public function save(CustomerModel $customer): bool
    {
        try {
            $sql = "INSERT INTO customers (first_name, last_name, email, phone, address,
                                           client_type, source, notes, preferences)
                    VALUES (:firstName, :lastName, :email, :phone, :address,
                            :clientType, :source, :notes, :preferences)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':firstName'    => $customer->getFirstName(),
                ':lastName'     => $customer->getLastName(),
                ':email'        => $customer->getEmail(),
                ':phone'        => $customer->getPhone(),
                ':address'      => $customer->getAddress(),
                ':clientType'   => $customer->getClientType(),
                ':source'       => $customer->getSource(),
                ':notes'        => $customer->getNotes(),
                ':preferences'  => $customer->getPreferences()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Actualiza los datos de un cliente existente identificado por su ID
    public function update(CustomerModel $customer): bool
    {
        try {
            $sql = "UPDATE customers SET
                        first_name = :firstName, last_name = :lastName,
                        email = :email, phone = :phone, address = :address,
                        client_type = :clientType, source = :source,
                        notes = :notes, preferences = :preferences,
                        is_active = :isActive
                    WHERE customer_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'           => $customer->getId(),
                ':firstName'    => $customer->getFirstName(),
                ':lastName'     => $customer->getLastName(),
                ':email'        => $customer->getEmail(),
                ':phone'        => $customer->getPhone(),
                ':address'      => $customer->getAddress(),
                ':clientType'   => $customer->getClientType(),
                ':source'       => $customer->getSource(),
                ':notes'        => $customer->getNotes(),
                ':preferences'  => $customer->getPreferences(),
                ':isActive'     => $customer->isActive() ? 1 : 0
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Elimina un cliente de la BD por su ID
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM customers WHERE customer_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
