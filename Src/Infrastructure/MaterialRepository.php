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
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, material_type AS materialType, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials ORDER BY material_id DESC"
            );
            $materials = [];
            while ($row = $stmt->fetch()) {
                $materials[] = new MaterialModel($row);
            }
            return $materials;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findById(int $id): ?MaterialModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, material_type AS materialType, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials WHERE material_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new MaterialModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function findByCategory(int $categoryId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT material_id AS id, material_code AS code, name, price, cost_type AS costType, wholesale_qty AS wholesaleQty, current_stock AS stock, image_url AS imageUrl, category_id AS categoryId, material_type AS materialType, supplier_id AS supplierId, current_location_id AS locationId, is_active AS isActive FROM materials WHERE category_id = :categoryId ORDER BY material_id DESC"
            );
            $stmt->execute([':categoryId' => $categoryId]);
            $materials = [];
            while ($row = $stmt->fetch()) {
                $materials[] = new MaterialModel($row);
            }
            return $materials;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
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
            return false;
        }
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM materials WHERE name = :name";
            $params = [':name' => $name];
            if ($excludeId !== null) {
                $sql .= " AND material_id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function save(MaterialModel $material): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO materials (material_code, name, price, cost_type, wholesale_qty, current_stock, category_id, material_type, supplier_id, current_location_id) 
                    VALUES (:code, :name, :price, :cost_type, :wholesale_qty, :stock, :category_id, :material_type, :supplier_id, :location_id)";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':stock' => $material->getStock(),
                ':category_id' => $material->getCategoryId(),
                ':material_type' => $material->getMaterialType(),
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
            $this->db->beginTransaction();

            $stmtOld = $this->db->prepare(
                "SELECT current_stock, current_location_id FROM materials WHERE material_id = :id"
            );
            $stmtOld->execute([':id' => $material->getId()]);
            $old = $stmtOld->fetch();

            $sql = "UPDATE materials SET material_code = :code, name = :name, price = :price, cost_type = :cost_type, wholesale_qty = :wholesale_qty, current_stock = :stock, category_id = :category_id, material_type = :material_type, supplier_id = :supplier_id, current_location_id = :location_id, is_active = :is_active WHERE material_id = :id";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':id' => $material->getId(),
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':stock' => $material->getStock(),
                ':category_id' => $material->getCategoryId(),
                ':material_type' => $material->getMaterialType(),
                ':supplier_id' => $material->getSupplierId(),
                ':location_id' => $material->getLocationId(),
                ':is_active' => $material->getIsActive() ? 1 : 0
            ]);

            if ($ok) {
                $newStock = $material->getStock();
                $newLocId = $material->getLocationId();
                $oldLocId = $old ? (int)$old['current_location_id'] : null;
                $oldStock = $old ? (int)$old['current_stock'] : 0;

                if ($oldLocId && $newLocId && $newLocId !== $oldLocId) {
                    // Location changed: old location loses all, new location gets all
                    $this->upsertStockLocation($material->getId(), $oldLocId, -$oldStock);
                    $this->upsertStockLocation($material->getId(), $newLocId, $newStock);
                } elseif ($oldLocId && $newStock !== $oldStock) {
                    // Same location, stock changed
                    $this->upsertStockLocation($material->getId(), $oldLocId, $newStock - $oldStock);
                } elseif (!$oldLocId && $newLocId) {
                    // New location assigned (had none before)
                    $this->upsertStockLocation($material->getId(), $newLocId, $newStock);
                }

                $delStmt = $this->db->prepare(
                    "DELETE FROM material_stock_locations WHERE material_id = :id AND quantity <= 0"
                );
                $delStmt->execute([':id' => $material->getId()]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    private function upsertStockLocation(int $materialId, int $locationId, int $quantityChange): void
    {
        $existing = $this->db->prepare(
            "SELECT quantity FROM material_stock_locations WHERE material_id = :material_id AND location_id = :location_id"
        );
        $existing->execute([':material_id' => $materialId, ':location_id' => $locationId]);
        $row = $existing->fetch();

        if ($row) {
            $newQty = max(0, (int)$row['quantity'] + $quantityChange);
            $this->db->prepare(
                "UPDATE material_stock_locations SET quantity = :qty WHERE material_id = :material_id AND location_id = :location_id"
            )->execute([':qty' => $newQty, ':material_id' => $materialId, ':location_id' => $locationId]);
        } else {
            $this->db->prepare(
                "INSERT INTO material_stock_locations (material_id, location_id, quantity) VALUES (:material_id, :location_id, :qty)"
            )->execute([':material_id' => $materialId, ':location_id' => $locationId, ':qty' => max(0, $quantityChange)]);
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
