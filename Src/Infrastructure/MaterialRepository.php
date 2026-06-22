<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\MaterialModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class MaterialRepository
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
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials ORDER BY material_id DESC"
            );
            $materials = [];
            while ($row = $stmt->fetch()) {
                $materials[] = new MaterialModel($row);
            }
            return $materials;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    public function findById(int $id): ?MaterialModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials WHERE material_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new MaterialModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    public function findByCategory(int $categoryId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials WHERE category_id = :categoryId ORDER BY material_id DESC"
            );
            $stmt->execute([':categoryId' => $categoryId]);
            $materials = [];
            while ($row = $stmt->fetch()) {
                $materials[] = new MaterialModel($row);
            }
            return $materials;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM materials WHERE material_code = :code";
            $params = [':code' => $code];
            if ($excludeId !== null) {
                $sql .= " AND material_id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }

    public function save(MaterialModel $material): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO materials (material_code, name, price, cost_type, wholesale_qty, current_stock, category_id, supplier_id, current_location_id) 
                    VALUES (:code, :name, :price, :cost_type, :wholesale_qty, :stock, :category_id, :supplier_id, :location_id)";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':stock' => $material->getStock(),
                ':category_id' => $material->getCategoryId(),
                ':supplier_id' => $material->getSupplierId(),
                ':location_id' => $material->getLocationId()
            ]);

            if ($ok && $material->getStock() > 0 && $material->getLocationId()) {
                $materialId = $this->db->lastInsertId();
                $this->db->prepare(
                    "INSERT INTO material_stock_locations (material_id, location_id, quantity)
                     VALUES (:mid, :lid, :qty)"
                )->execute([
                    ':mid' => $materialId,
                    ':lid' => $material->getLocationId(),
                    ':qty' => $material->getStock()
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function update(MaterialModel $material): bool
    {
        try {
            $sql = "UPDATE materials SET material_code = :code, name = :name, price = :price, cost_type = :cost_type, wholesale_qty = :wholesale_qty, current_stock = :stock, category_id = :category_id, supplier_id = :supplier_id, current_location_id = :location_id, is_active = :is_active WHERE material_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $material->getId(),
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':stock' => $material->getStock(),
                ':category_id' => $material->getCategoryId(),
                ':supplier_id' => $material->getSupplierId(),
                ':location_id' => $material->getLocationId(),
                ':is_active' => $material->getIsActive() ? 1 : 0
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM materials WHERE material_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
