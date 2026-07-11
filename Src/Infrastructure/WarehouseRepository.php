<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\WarehouseModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class WarehouseRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT warehouse_id, code, name, location, max_shelves, created_at FROM warehouses ORDER BY name ASC"
            );
            $warehouses = [];
            while ($row = $stmt->fetch()) {
                $warehouses[] = new WarehouseModel($row);
            }
            return $warehouses;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findById(int $id): ?WarehouseModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT warehouse_id, code, name, location, max_shelves, created_at FROM warehouses WHERE warehouse_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new WarehouseModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function existsByCode(string $code): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM warehouses WHERE code = :code");
            $stmt->execute([':code' => $code]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM warehouses WHERE name = :name";
            $params = [':name' => $name];
            if ($excludeId) {
                $sql .= " AND warehouse_id != :exclude";
                $params[':exclude'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getNextCode(): string
    {
        try {
            $stmt = $this->db->query("SELECT code FROM warehouses WHERE code LIKE 'ALM-%' ORDER BY warehouse_id DESC LIMIT 1");
            $last = $stmt->fetchColumn();
            if (!$last) return 'ALM-001';
            $num = (int)substr($last, 4) + 1;
            return 'ALM-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            return 'ALM-001';
        }
    }

    public function save(WarehouseModel $warehouse): bool
    {
        try {
            $sql = "INSERT INTO warehouses (code, name, location, max_shelves) VALUES (:code, :name, :location, :max_shelves)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':code' => $warehouse->getCode(),
                ':name' => $warehouse->getName(),
                ':location' => $warehouse->getLocation(),
                ':max_shelves' => $warehouse->getMaxShelves()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $shelves = $this->db->prepare("SELECT COUNT(*) FROM locations WHERE warehouse_id = :id");
            $shelves->execute([':id' => $id]);
            if ($shelves->fetchColumn() > 0) {
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM warehouses WHERE warehouse_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getShelfCount(int $warehouseId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM locations WHERE warehouse_id = :id");
            $stmt->execute([':id' => $warehouseId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
