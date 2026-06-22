<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\InventoryMovementModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class StorageRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function recordAdjustment(int $materialId, int $userId, string $type, int $quantity, string $reason, ?string $notes): bool
    {
        try {
            $this->db->beginTransaction();

            if ($type === 'entry') {
                $this->db->exec("UPDATE materials SET current_stock = current_stock + $quantity WHERE material_id = $materialId");
            } else {
                $this->db->exec("UPDATE materials SET current_stock = GREATEST(current_stock - $quantity, 0) WHERE material_id = $materialId");
            }

            $actionType = $type === 'entry' ? 'Entry' : 'Exit';
            $stmt = $this->db->prepare(
                "INSERT INTO inventory_movements (material_id, user_id, action_type, quantity, reason, extra_note, movement_date)
                 VALUES (:material_id, :user_id, :action_type, :quantity, :reason, :extra_note, NOW())"
            );
            $stmt->execute([
                ':material_id' => $materialId,
                ':user_id' => $userId,
                ':action_type' => $actionType,
                ':quantity' => $quantity,
                ':reason' => $reason,
                ':extra_note' => $notes
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al registrar ajuste: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function recordMove(int $materialId, int $userId, int $fromLocationId, int $toLocationId, int $quantity, string $reason, ?string $notes): bool
    {
        try {
            $this->db->beginTransaction();

            $check = $this->db->prepare(
                "SELECT quantity FROM material_stock_locations WHERE material_id = :mid AND location_id = :lid"
            );
            $check->execute([':mid' => $materialId, ':lid' => $fromLocationId]);
            $row = $check->fetch();
            $available = $row ? (int)$row['quantity'] : 0;

            if ($available < $quantity) {
                throw new PDOException("Stock insuficiente en la ubicación origen. Disponible: $available, Solicitado: $quantity.");
            }

            $this->upsertStockLocation($materialId, $fromLocationId, -$quantity);
            $this->upsertStockLocation($materialId, $toLocationId, $quantity);

            $this->db->exec(
                "DELETE FROM material_stock_locations WHERE material_id = $materialId AND quantity <= 0"
            );

            $checkCurrent = $this->db->query(
                "SELECT location_id FROM material_stock_locations WHERE material_id = $materialId ORDER BY quantity DESC LIMIT 1"
            )->fetch();

            if ($checkCurrent) {
                $this->db->exec("UPDATE materials SET current_location_id = {$checkCurrent['location_id']} WHERE material_id = $materialId");
            }

            $stmt = $this->db->prepare(
                "INSERT INTO inventory_movements (material_id, user_id, action_type, quantity, reason, extra_note, origin_location_id, destination_location_id, movement_date)
                 VALUES (:material_id, :user_id, 'Transfer', :quantity, :reason, :extra_note, :origin, :destination, NOW())"
            );
            $stmt->execute([
                ':material_id' => $materialId,
                ':user_id' => $userId,
                ':quantity' => $quantity,
                ':reason' => $reason,
                ':extra_note' => $notes,
                ':origin' => $fromLocationId,
                ':destination' => $toLocationId
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al mover material: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function getHistory(int $page = 1, int $perPage = 15): array
    {
        try {
            $offset = ($page - 1) * $perPage;

            $countStmt = $this->db->query("SELECT COUNT(*) FROM inventory_movements");
            $total = (int)$countStmt->fetchColumn();

            $stmt = $this->db->prepare(
                "SELECT
                    m.movement_id AS id,
                    m.material_id AS materialId,
                    mat.name AS materialName,
                    mat.material_code AS materialCode,
                    m.user_id AS userId,
                    u.first_name AS userName,
                    m.movement_date AS movementDate,
                    m.action_type AS actionType,
                    m.quantity,
                    m.reason,
                    m.extra_note AS extraNote,
                    m.origin_location_id AS originLocationId,
                    ol.location_name AS originName,
                    m.destination_location_id AS destinationLocationId,
                    dl.location_name AS destinationName
                 FROM inventory_movements m
                 LEFT JOIN materials mat ON m.material_id = mat.material_id
                 LEFT JOIN users u ON m.user_id = u.user_id
                 LEFT JOIN locations ol ON m.origin_location_id = ol.location_id
                 LEFT JOIN locations dl ON m.destination_location_id = dl.location_id
                 ORDER BY m.movement_date DESC
                 LIMIT $perPage OFFSET $offset"
            );
            $stmt->execute();
            $rows = $stmt->fetchAll();

            return [
                'data' => $rows,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => max(1, (int)ceil($total / $perPage))
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }

    public function getSummary(): array
    {
        try {
            $totalMaterials = (int)$this->db->query("SELECT COUNT(*) FROM materials WHERE is_active = 1")->fetchColumn();
            $totalLocations = (int)$this->db->query("SELECT COUNT(*) FROM locations")->fetchColumn();
            $lowStock = (int)$this->db->query("SELECT COUNT(*) FROM materials WHERE is_active = 1 AND current_stock > 0 AND current_stock <= 10")->fetchColumn();
            $outOfStock = (int)$this->db->query("SELECT COUNT(*) FROM materials WHERE is_active = 1 AND (current_stock IS NULL OR current_stock <= 0)")->fetchColumn();

            return [
                'totalMaterials' => $totalMaterials,
                'totalLocations' => $totalLocations,
                'lowStock' => $lowStock,
                'outOfStock' => $outOfStock
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener resumen: ' . $e->getMessage(), 500);
        }
    }

    public function getStockByMaterial(int $materialId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT msl.location_id AS locationId, l.location_name AS locationName, msl.quantity
                 FROM material_stock_locations msl
                 JOIN locations l ON msl.location_id = l.location_id
                 WHERE msl.material_id = :material_id AND msl.quantity > 0
                 ORDER BY l.location_name ASC"
            );
            $stmt->execute([':material_id' => $materialId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
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
}
