<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\CustomerModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// CustomerRepository — Acceso a datos de la tabla `customers`
// CRUD completo con verificación de unicidad por email y teléfono
class CustomerRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
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
                        id_number AS idNumber, email, phone, address, client_type AS clientType,
                        source, notes, preferences, avatar, is_active AS isActive
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
            return [];
        }
    }

    // Busca un cliente por su ID
    public function findById(int $id): ?CustomerModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT customer_id AS id, first_name AS firstName, last_name AS lastName,
                        id_number AS idNumber, email, phone, address, client_type AS clientType,
                        source, notes, preferences, avatar, is_active AS isActive
                 FROM customers WHERE customer_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new CustomerModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    // Inserta un nuevo cliente en la BD
    public function save(CustomerModel $customer): bool
    {
        try {
            $sql = "INSERT INTO customers (first_name, last_name, id_number, email, phone,
                                           address, client_type, source, notes, preferences,
                                           avatar, is_active)
                    VALUES (:firstName, :lastName, :idNumber, :email, :phone,
                            :address, :clientType, :source, :notes, :preferences,
                            :avatar, :isActive)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':firstName'    => $customer->getFirstName(),
                ':lastName'     => $customer->getLastName(),
                ':idNumber'     => $customer->getIdNumber(),
                ':email'        => $customer->getEmail(),
                ':phone'        => $customer->getPhone(),
                ':address'      => $customer->getAddress(),
                ':clientType'   => $customer->getClientType(),
                ':source'       => $customer->getSource(),
                ':notes'        => $customer->getNotes(),
                ':preferences'  => $customer->getPreferences(),
                ':avatar'       => $customer->getAvatar(),
                ':isActive'     => $customer->isActive() ? 1 : 0
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
                        id_number = :idNumber, email = :email, phone = :phone,
                        address = :address, client_type = :clientType,
                        source = :source, notes = :notes, preferences = :preferences,
                        avatar = :avatar, is_active = :isActive
                    WHERE customer_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'           => $customer->getId(),
                ':firstName'    => $customer->getFirstName(),
                ':lastName'     => $customer->getLastName(),
                ':idNumber'     => $customer->getIdNumber(),
                ':email'        => $customer->getEmail(),
                ':phone'        => $customer->getPhone(),
                ':address'      => $customer->getAddress(),
                ':clientType'   => $customer->getClientType(),
                ':source'       => $customer->getSource(),
                ':notes'        => $customer->getNotes(),
                ':preferences'  => $customer->getPreferences(),
                ':avatar'       => $customer->getAvatar(),
                ':isActive'     => $customer->isActive() ? 1 : 0
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Verifica si ya existe un cliente con el mismo email (excluyendo el ID opcional para edición)
    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM customers WHERE email = :email";
            $params = [':email' => $email];
            if ($excludeId) {
                $sql .= " AND customer_id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Verifica si ya existe un cliente con el mismo teléfono (excluyendo el ID opcional para edición)
    public function existsByPhone(string $phone, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM customers WHERE phone = :phone";
            $params = [':phone' => $phone];
            if ($excludeId) {
                $sql .= " AND customer_id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

}
