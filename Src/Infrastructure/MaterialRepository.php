<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\MaterialModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// MaterialRepository — Acceso a datos de la tabla `materials` y `material_stock_locations`
// CRUD con gestión de stock por ubicación, validación de unicidad y búsqueda por categoría
class MaterialRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Obtiene todos los materiales con stock calculado desde material_stock_locations
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT m.material_id AS id, m.material_code AS code, m.name, m.price, m.cost_type AS costType, m.wholesale_qty AS wholesaleQty, COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS stock, m.reserved_stock AS reservedStock, m.image_url AS imageUrl, m.category_id AS categoryId, m.material_type AS materialType, m.supplier_id AS supplierId, m.detalle_comodin AS detalleComodin, m.current_location_id AS locationId, m.is_active AS isActive, m.unidad_compra AS unidadCompra, m.unidad_consumo AS unidadConsumo, m.factor_conversion AS factorConversion FROM materials m ORDER BY m.material_id DESC"
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

    // Busca un material por su ID
    public function findById(int $id): ?MaterialModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.material_id AS id, m.material_code AS code, m.name, m.price, m.cost_type AS costType, m.wholesale_qty AS wholesaleQty, COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS stock, m.reserved_stock AS reservedStock, m.image_url AS imageUrl, m.category_id AS categoryId, m.material_type AS materialType, m.supplier_id AS supplierId, m.detalle_comodin AS detalleComodin, m.current_location_id AS locationId, m.is_active AS isActive, m.unidad_compra AS unidadCompra, m.unidad_consumo AS unidadConsumo, m.factor_conversion AS factorConversion FROM materials m WHERE m.material_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new MaterialModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    // Busca materiales por categoría
    public function findByCategory(int $categoryId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.material_id AS id, m.material_code AS code, m.name, m.price, m.cost_type AS costType, m.wholesale_qty AS wholesaleQty, COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS stock, m.reserved_stock AS reservedStock, m.image_url AS imageUrl, m.category_id AS categoryId, m.material_type AS materialType, m.supplier_id AS supplierId, m.detalle_comodin AS detalleComodin, m.current_location_id AS locationId, m.is_active AS isActive, m.unidad_compra AS unidadCompra, m.unidad_consumo AS unidadConsumo, m.factor_conversion AS factorConversion FROM materials m WHERE m.category_id = :categoryId ORDER BY m.material_id DESC"
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

    // Verifica si ya existe un material con el mismo código
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

    // Verifica si ya existe un material con el mismo nombre
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

    // Inserta un nuevo material con su stock inicial en material_stock_locations
    // Operación transaccional: crea material + registra stock en ubicación
    public function save(MaterialModel $material): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO materials (material_code, name, price, cost_type, wholesale_qty, category_id, material_type, supplier_id, detalle_comodin, current_location_id, unidad_compra, unidad_consumo, factor_conversion, image_url) 
                    VALUES (:code, :name, :price, :cost_type, :wholesale_qty, :category_id, :material_type, :supplier_id, :detalle_comodin, :location_id, :unidad_compra, :unidad_consumo, :factor_conversion, :image_url)";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':category_id' => $material->getCategoryId(),
                ':material_type' => $material->getMaterialType(),
                ':supplier_id' => $material->getSupplierId(),
                ':detalle_comodin' => $material->getDetalleComodin(),
                ':location_id' => $material->getLocationId(),
                ':unidad_compra' => $material->getUnidadCompra(),
                ':unidad_consumo' => $material->getUnidadConsumo(),
                ':factor_conversion' => $material->getFactorConversion(),
                ':image_url' => $material->getImageUrl()
            ]);

            // Si el material tiene stock inicial y ubicación, lo registra en la tabla de stock
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

    // Actualiza un material existente y sincroniza el stock entre ubicaciones si cambió
    // Operación transaccional que maneja cambios de ubicación y cantidad
    public function update(MaterialModel $material): bool
    {
        try {
            $this->db->beginTransaction();

            // Lee estado anterior del stock y ubicación
            $stmtOld = $this->db->prepare(
                "SELECT COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS current_stock, m.current_location_id FROM materials m WHERE m.material_id = :id"
            );
            $stmtOld->execute([':id' => $material->getId()]);
            $old = $stmtOld->fetch();

            // Actualiza los campos del material
            $sql = "UPDATE materials SET material_code = :code, name = :name, price = :price, cost_type = :cost_type, wholesale_qty = :wholesale_qty, category_id = :category_id, material_type = :material_type, supplier_id = :supplier_id, detalle_comodin = :detalle_comodin, current_location_id = :location_id, is_active = :is_active, unidad_compra = :unidad_compra, unidad_consumo = :unidad_consumo, factor_conversion = :factor_conversion, image_url = :image_url WHERE material_id = :id";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':id' => $material->getId(),
                ':code' => $material->getCode(),
                ':name' => $material->getName(),
                ':price' => $material->getPrice(),
                ':cost_type' => $material->getCostType(),
                ':wholesale_qty' => $material->getWholesaleQty(),
                ':category_id' => $material->getCategoryId(),
                ':material_type' => $material->getMaterialType(),
                ':supplier_id' => $material->getSupplierId(),
                ':detalle_comodin' => $material->getDetalleComodin(),
                ':location_id' => $material->getLocationId(),
                ':is_active' => $material->getIsActive() ? 1 : 0,
                ':unidad_compra' => $material->getUnidadCompra(),
                ':unidad_consumo' => $material->getUnidadConsumo(),
                ':factor_conversion' => $material->getFactorConversion(),
                ':image_url' => $material->getImageUrl()
            ]);

            // Sincroniza stock_locations según los cambios detectados
            if ($ok) {
                $newStock = $material->getStock();
                $newLocId = $material->getLocationId();
                $oldLocId = $old ? (int)$old['current_location_id'] : null;
                $oldStock = $old ? (int)$old['current_stock'] : 0;

                if ($oldLocId && $newLocId && $newLocId !== $oldLocId) {
                    // Cambió la ubicación: vieja pierde todo, nueva recibe todo
                    $this->upsertStockLocation($material->getId(), $oldLocId, -$oldStock);
                    $this->upsertStockLocation($material->getId(), $newLocId, $newStock);
                } elseif ($oldLocId && $newStock !== $oldStock) {
                    // Misma ubicación, cambió la cantidad
                    $this->upsertStockLocation($material->getId(), $oldLocId, $newStock - $oldStock);
                } elseif (!$oldLocId && $newLocId) {
                    // No tenía ubicación antes, ahora tiene
                    $this->upsertStockLocation($material->getId(), $newLocId, $newStock);
                }

                // Limpia registros con cantidad <= 0
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

    // Inserta o actualiza el stock de un material en una ubicación específica
    private function upsertStockLocation(int $materialId, int $locationId, int $quantityChange): void
    {
        $existing = $this->db->prepare(
            "SELECT quantity FROM material_stock_locations WHERE material_id = :material_id AND location_id = :location_id"
        );
        $existing->execute([':material_id' => $materialId, ':location_id' => $locationId]);
        $row = $existing->fetch();

        if ($row) {
            // Actualiza cantidad existente (nunca menor a 0)
            $newQty = max(0, (int)$row['quantity'] + $quantityChange);
            $this->db->prepare(
                "UPDATE material_stock_locations SET quantity = :qty WHERE material_id = :material_id AND location_id = :location_id"
            )->execute([':qty' => $newQty, ':material_id' => $materialId, ':location_id' => $locationId]);
        } else {
            // Inserta nuevo registro si no existía
            $this->db->prepare(
                "INSERT INTO material_stock_locations (material_id, location_id, quantity) VALUES (:material_id, :location_id, :qty)"
            )->execute([':material_id' => $materialId, ':location_id' => $locationId, ':qty' => max(0, $quantityChange)]);
        }
    }

    // Elimina un material por su ID
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
