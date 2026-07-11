<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\LocationModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// LocationRepository — Acceso a datos de la tabla `locations`
// CRUD con verificación de stock asociado antes de eliminar
class LocationRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Obtiene todas las ubicaciones ordenadas por ID ascendente
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT location_id AS id, location_name AS name, warehouse_id, max_capacity, description FROM locations ORDER BY location_id ASC"
            );
            $locations = [];
            while ($row = $stmt->fetch()) {
                $locations[] = new LocationModel($row);
            }
            return $locations;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    // Busca una ubicación por su ID
    public function findById(int $id): ?LocationModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT location_id AS id, location_name AS name, warehouse_id, max_capacity, description FROM locations WHERE location_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new LocationModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    // Obtiene las zonas disponibles (descripciones únicas)
    public function findZones(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT DISTINCT description AS zone FROM locations WHERE description IS NOT NULL ORDER BY description ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    // Inserta una nueva ubicación
    public function save(LocationModel $location): bool
    {
        try {
            $sql = "INSERT INTO locations (location_name, warehouse_id, max_capacity, description) VALUES (:name, :warehouse_id, :max_capacity, :description)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $location->getName(),
                ':warehouse_id' => $location->getWarehouseId(),
                ':max_capacity' => $location->getMaxCapacity(),
                ':description' => $location->getDescription()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Verifica si ya existe una ubicación con el mismo nombre
    public function existsByName(string $name): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM locations WHERE location_name = :name");
            $stmt->execute([':name' => $name]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Verifica si una ubicación tiene stock asignado (evita eliminación si tiene)
    public function hasStock(int $locationId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM material_stock_locations WHERE location_id = :id AND quantity > 0"
            );
            $stmt->execute([':id' => $locationId]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Elimina una ubicación y limpia referencias en otras tablas
    // No permite eliminar si tiene stock > 0 asociado
    public function delete(int $id): bool
    {
        try {
            // Verifica stock existente antes de eliminar
            $check = $this->db->prepare(
                "SELECT COUNT(*) FROM material_stock_locations WHERE location_id = :id AND quantity > 0"
            );
            $check->execute([':id' => $id]);
            if ($check->fetchColumn() > 0) {
                return false;
            }

            $this->db->beginTransaction();

            // Limpia referencias en tablas relacionadas
            $this->db->prepare("DELETE FROM material_stock_locations WHERE location_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE materials SET current_location_id = NULL WHERE current_location_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE inventory_movements SET origin_location_id = NULL WHERE origin_location_id = :id")->execute([':id' => $id]);
            $this->db->prepare("UPDATE inventory_movements SET destination_location_id = NULL WHERE destination_location_id = :id")->execute([':id' => $id]);

            // Elimina la ubicación
            $stmt = $this->db->prepare("DELETE FROM locations WHERE location_id = :id");
            $ok = $stmt->execute([':id' => $id]);

            $this->db->commit();
            return $ok;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
