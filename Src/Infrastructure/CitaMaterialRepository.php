<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class CitaMaterialRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByCitaId(int $citaId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT cm.id, cm.cita_id AS citaId, cm.material_id AS materialId,
                        cm.cantidad_utilizada AS cantidadUtilizada,
                        m.name AS materialName, m.material_code AS materialCode,
                        m.current_stock AS stockDisponible
                 FROM cita_materiales cm
                 JOIN materials m ON cm.material_id = m.material_id
                 WHERE cm.cita_id = :citaId
                 ORDER BY m.name"
            );
            $stmt->execute([':citaId' => $citaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar materiales de la cita.', 500);
            return [];
        }
    }

    public function guardarMateriales(int $citaId, array $materiales): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO cita_materiales (cita_id, material_id, cantidad_utilizada)
                 VALUES (:citaId, :materialId, :cantidad)"
            );

            foreach ($materiales as $mat) {
                $stmt->execute([
                    ':citaId'    => $citaId,
                    ':materialId' => (int)($mat['materialId'] ?? $mat['material_id'] ?? 0),
                    ':cantidad'   => (int)($mat['cantidad'] ?? $mat['cantidad_utilizada'] ?? 0)
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al guardar materiales.', 500);
            return false;
        }
    }

    public function eliminarMaterialesDeCita(int $citaId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cita_materiales WHERE cita_id = :citaId");
            return $stmt->execute([':citaId' => $citaId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error al eliminar materiales.', 500);
            return false;
        }
    }

    public function descontarStock(int $citaId): bool
    {
        try {
            $this->db->beginTransaction();
            $materiales = $this->findByCitaId($citaId);
            $stmt = $this->db->prepare(
                "UPDATE materials SET current_stock = current_stock - :cantidad WHERE material_id = :id AND current_stock >= :cantidad2"
            );
            foreach ($materiales as $mat) {
                $stmt->execute([
                    ':cantidad' => $mat['cantidadUtilizada'],
                    ':id'       => $mat['materialId'],
                    ':cantidad2' => $mat['cantidadUtilizada']
                ]);
                if ($stmt->rowCount() === 0) {
                    $this->db->rollBack();
                    ApiResponse::error('Stock insuficiente para material: ' . $mat['materialName']);
                    return false;
                }
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al descontar stock.', 500);
            return false;
        }
    }

    public function restaurarStock(int $citaId): bool
    {
        try {
            $this->db->beginTransaction();
            $materiales = $this->findByCitaId($citaId);
            $stmt = $this->db->prepare(
                "UPDATE materials SET current_stock = current_stock + :cantidad WHERE material_id = :id"
            );
            foreach ($materiales as $mat) {
                $stmt->execute([
                    ':cantidad' => $mat['cantidadUtilizada'],
                    ':id'       => $mat['materialId']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al restaurar stock.', 500);
            return false;
        }
    }

    public function verificarStockDisponible(int $citaId): bool
    {
        try {
            $materiales = $this->findByCitaId($citaId);
            foreach ($materiales as $mat) {
                if ($mat['cantidadUtilizada'] > $mat['stockDisponible']) {
                    return false;
                }
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
