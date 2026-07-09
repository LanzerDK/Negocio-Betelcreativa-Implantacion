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
                        cm.precio_unitario AS precioUnitario,
                        m.name AS materialName, m.material_code AS materialCode,
                        m.price AS materialPrice,
                        COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = m.material_id), 0) AS stockDisponible, m.reserved_stock AS reservedStock
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

    /**
     * Crea o reemplaza la asignación de materiales de una cita.
     * Fase 1: Libera reservas viejas.
     * Fase 2: Verifica stock disponible para cantidades nuevas.
     * Fase 3: Reserva nuevas cantidades.
     * Fase 4: Reemplaza registros en cita_materiales.
     * Fase 5: Registra historial transaccional.
     */
    public function syncMaterialsWithReservation(
        int $citaId,
        array $newMaterials,
        string $estadoCita,
        int $usuarioId,
        string $accion,
        bool $manageTransaction = true
    ): bool {
        try {
            if ($manageTransaction) {
                $this->db->beginTransaction();
            }

            // 1. Cargar materiales actuales desde DB
            $oldRows = $this->findByCitaId($citaId);
            $oldByMat = [];
            foreach ($oldRows as $row) {
                $oldByMat[(int)$row['materialId']] = (int)$row['cantidadUtilizada'];
            }

            // 2. Normalizar nuevos materiales (agrupar por materialId por si vienen duplicados)
            $newByMat = [];
            foreach ($newMaterials as $nm) {
                $mid = (int)($nm['materialId'] ?? $nm['material_id'] ?? 0);
                $cant = (int)($nm['cantidad'] ?? $nm['cantidad_utilizada'] ?? 0);
                if ($mid > 0) {
                    $newByMat[$mid] = ($newByMat[$mid] ?? 0) + $cant;
                }
            }

            // 3. Detectar cambios (comparación raw, sin array_filter — B2 fix)
            ksort($oldByMat);
            ksort($newByMat);
            $materialsChanged = ($oldByMat !== $newByMat);

            if (!$materialsChanged) {
                // C1 fix: sin cambios → salir ANTES de tocar reservas
                return true;
            }

            // 4. Verificar si existe factura cerrada (bloquea modificación)
            $checkFact = $this->db->prepare("SELECT id, estado, costo_servicio FROM facturas WHERE cita_id = :cid LIMIT 1");
            $checkFact->execute([':cid' => $citaId]);
            $factura = $checkFact->fetch(PDO::FETCH_ASSOC);

            if ($factura) {
                if ($factura['estado'] === 'cerrada') {
                    if ($manageTransaction) {
                        $this->db->rollBack();
                    }
                    ApiResponse::error('No se pueden modificar los materiales de una cita con factura cerrada.', 400);
                    return false;
                }
            }

            // 5. Bloquear TODOS los materiales involucrados (old + new) con FOR UPDATE,
            //    ordenados asc para prevenir deadlocks (M4 fix)
            $allIds = array_unique(array_merge(array_keys($oldByMat), array_keys($newByMat)));
            sort($allIds);
            $lockStmt = $this->db->prepare(
                "SELECT reserved_stock FROM materials WHERE material_id = :id FOR UPDATE"
            );
            foreach ($allIds as $mid) {
                $lockStmt->execute([':id' => $mid]);
                $lockStmt->fetch();
            }

            // 6. Verificar stock disponible para cantidades adicionales
            //    (después del lock global, antes de modificar reservas)
            foreach ($newByMat as $mid => $newCant) {
                $oldCant = $oldByMat[$mid] ?? 0;
                $extra = $newCant - $oldCant;
                if ($extra > 0) {
                    $stmt = $this->db->prepare(
                        "SELECT COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = :id), 0) AS current_stock, reserved_stock FROM materials WHERE material_id = :id2"
                    );
                    $stmt->execute([':id' => $mid, ':id2' => $mid]);
                    $mat = $stmt->fetch();
                    if (!$mat) {
                        if ($this->db->inTransaction()) {
                            $this->db->rollBack();
                        }
                        ApiResponse::error('Material no encontrado (ID: ' . $mid . ').', 500);
                        return false;
                    }
                    $disponible = (int)$mat['current_stock'] - (int)$mat['reserved_stock'];
                    if ($extra > $disponible) {
                        if ($this->db->inTransaction()) {
                            $this->db->rollBack();
                        }
                        ApiResponse::error('Stock insuficiente para el material seleccionado.', 500);
                        return false;
                    }
                }
            }

            // 7. Liberar reservas viejas (protegido por FOR UPDATE — M4 fix)
            $freeStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = GREATEST(reserved_stock - :cant, 0) WHERE material_id = :id"
            );
            foreach ($oldByMat as $mid => $oldCant) {
                if ($oldCant > 0) {
                    $freeStmt->execute([':cant' => $oldCant, ':id' => $mid]);
                }
            }

            // 8. Reservar nuevas cantidades
            $reserveStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = reserved_stock + :cant WHERE material_id = :id"
            );
            foreach ($newByMat as $mid => $cant) {
                if ($cant > 0) {
                    $reserveStmt->execute([':cant' => $cant, ':id' => $mid]);
                }
            }

            // 9. Reemplazar registros en cita_materiales
            $delStmt = $this->db->prepare("DELETE FROM cita_materiales WHERE cita_id = :cid");
            $delStmt->execute([':cid' => $citaId]);

            $priceStmt = $this->db->prepare("SELECT price FROM materials WHERE material_id = :mid");
            $insStmt = $this->db->prepare(
                "INSERT INTO cita_materiales (cita_id, material_id, cantidad_utilizada, precio_unitario)
                 VALUES (:cid, :mid, :cant, :precio)"
            );
            foreach ($newByMat as $mid => $cant) {
                if ($cant > 0) {
                    $priceStmt->execute([':mid' => $mid]);
                    $precio = (float)($priceStmt->fetchColumn() ?: 0);
                    $insStmt->execute([
                        ':cid'   => $citaId,
                        ':mid'   => $mid,
                        ':cant'  => $cant,
                        ':precio' => $precio
                    ]);
                }
            }

            // 6.5 Sincronizar total de factura si existe y está activa
            if ($factura && $factura['estado'] === 'activa') {
                $costoServicio = (float)$factura['costo_servicio'];
                $matTotStmt = $this->db->prepare(
                    "SELECT COALESCE(SUM(cantidad_utilizada * precio_unitario), 0) FROM cita_materiales WHERE cita_id = :cid"
                );
                $matTotStmt->execute([':cid' => $citaId]);
                $totalMateriales = (float)$matTotStmt->fetchColumn();
                $totalFactura = round(($costoServicio + $totalMateriales) * (1 + IVA_RATE), 2);

                $upd = $this->db->prepare("UPDATE facturas SET total_factura = :total WHERE id = :fid");
                $upd->execute([':total' => $totalFactura, ':fid' => $factura['id']]);
            }

            // 10. Registrar historial (solo materiales cuyo valor cambió)
            foreach ($allIds as $mid) {
                $oldCant = $oldByMat[$mid] ?? 0;
                $newCant = $newByMat[$mid] ?? 0;
                if ($oldCant !== $newCant) {
                    $this->logHistorial($citaId, $mid, $oldCant, $newCant, $accion, $estadoCita, $usuarioId);
                }
            }

            if ($manageTransaction) {
                $this->db->commit();
            }
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            ApiResponse::error('Error al sincronizar materiales: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Libera las reservas de una cita (al cancelar).
     * NO elimina los registros de cita_materiales para permitir restauración futura.
     */
    public function cancelReservations(int $citaId, string $estadoCita, int $usuarioId): bool
    {
        try {
            $this->db->beginTransaction();

            $materiales = $this->findByCitaId($citaId);

            // Lock all materials with FOR UPDATE, sorted to prevent deadlocks
            usort($materiales, fn($a, $b) => (int)$a['materialId'] - (int)$b['materialId']);
            $lockStmt = $this->db->prepare(
                "SELECT reserved_stock FROM materials WHERE material_id = :id FOR UPDATE"
            );
            foreach ($materiales as $mat) {
                $lockStmt->execute([':id' => $mat['materialId']]);
                $lockStmt->fetch();
            }

            $freeStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = GREATEST(reserved_stock - :cant, 0) WHERE material_id = :id"
            );

            foreach ($materiales as $mat) {
                $cant = (int)$mat['cantidadUtilizada'];
                if ($cant > 0) {
                    $freeStmt->execute([':cant' => $cant, ':id' => $mat['materialId']]);
                }
                $this->logHistorial($citaId, (int)$mat['materialId'], $cant, 0, 'Cancelado', $estadoCita, $usuarioId);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al cancelar reservas: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Re-reserva los materiales de una cita cancelada (al restaurar).
     * Los registros de cita_materiales aún existen de antes de la cancelación.
     */
    public function restoreReservations(int $citaId, string $estadoCita, int $usuarioId): bool
    {
        try {
            $this->db->beginTransaction();

            $materiales = $this->findByCitaId($citaId);
            usort($materiales, fn($a, $b) => (int)$a['materialId'] - (int)$b['materialId']);

            $reserveStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = reserved_stock + :cant WHERE material_id = :id"
            );

            foreach ($materiales as $mat) {
                $cant = (int)$mat['cantidadUtilizada'];
                if ($cant > 0) {
                    $check = $this->db->prepare(
                        "SELECT COALESCE((SELECT SUM(quantity) FROM material_stock_locations WHERE material_id = :id), 0) AS current_stock, reserved_stock FROM materials WHERE material_id = :id2 FOR UPDATE"
                    );
                    $check->execute([':id' => $mat['materialId'], ':id2' => $mat['materialId']]);
                    $m = $check->fetch();
                    if (!$m || ((int)$m['current_stock'] - (int)$m['reserved_stock']) < $cant) {
                        $this->db->rollBack();
                        return false;
                    }
                    $reserveStmt->execute([':cant' => $cant, ':id' => $mat['materialId']]);
                }
                $this->logHistorial($citaId, (int)$mat['materialId'], 0, $cant, 'Asignado', $estadoCita, $usuarioId);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al restaurar reservas: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Ejecuta la deducción real de stock cuando una cita pasa a Finalizada.
     * Consume stock secuencialmente de todas las ubicaciones (FIFO por location_id),
     * libera reserved_stock y registra en inventory_movements.
     */
    public function executeDeductionOnCompleted(int $citaId, int $usuarioId): bool
    {
        try {
            $this->db->beginTransaction();

            $materiales = $this->findByCitaId($citaId);
            usort($materiales, fn($a, $b) => (int)$a['materialId'] - (int)$b['materialId']);

            $now = date('Y-m-d H:i:s');

            $locsStmt = $this->db->prepare(
                "SELECT location_id, quantity FROM material_stock_locations
                 WHERE material_id = :id AND quantity > 0
                 ORDER BY location_id ASC FOR UPDATE"
            );
            $deleteStmt = $this->db->prepare(
                "DELETE FROM material_stock_locations WHERE material_id = :mid AND location_id = :lid"
            );
            $updateStmt = $this->db->prepare(
                "UPDATE material_stock_locations SET quantity = quantity - :take WHERE material_id = :mid AND location_id = :lid"
            );
            $releaseStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = GREATEST(reserved_stock - :cant, 0) WHERE material_id = :id"
            );

            $movStmt = $this->db->prepare(
                "INSERT INTO inventory_movements (material_id, user_id, action_type, quantity, reason, tipo_referencia, referencia_id, movement_date)
                 VALUES (:mid, :uid, 'Exit', :qty, :reason, :tipo_ref, :ref_id, :date)"
            );

            foreach ($materiales as $mat) {
                $cant = (int)$mat['cantidadUtilizada'];
                if ($cant <= 0) continue;

                // Bloquear y leer todas las ubicaciones con stock > 0
                $locsStmt->execute([':id' => $mat['materialId']]);
                $locations = $locsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar stock total disponible
                $total = array_sum(array_column($locations, 'quantity'));
                if ($total < $cant) {
                    $this->db->rollBack();
                    ApiResponse::error('Stock insuficiente al ejecutar cita para: ' . $mat['materialName'], 500);
                    return false;
                }

                // Consumo secuencial FIFO por ubicación
                $remaining = $cant;
                foreach ($locations as $loc) {
                    if ($remaining <= 0) break;
                    $locQty = (int)$loc['quantity'];
                    $take = min($locQty, $remaining);
                    if ($take === $locQty) {
                        $deleteStmt->execute([':mid' => $mat['materialId'], ':lid' => $loc['location_id']]);
                    } else {
                        $updateStmt->execute([':take' => $take, ':mid' => $mat['materialId'], ':lid' => $loc['location_id']]);
                    }
                    $remaining -= $take;
                }

                // Liberar reserved_stock
                $releaseStmt->execute([
                    ':cant' => $cant,
                    ':id'   => $mat['materialId']
                ]);

                $movStmt->execute([
                    ':mid'     => $mat['materialId'],
                    ':uid'     => $usuarioId,
                    ':qty'     => $cant,
                    ':reason'  => 'Salida por Cita #' . $citaId,
                    ':tipo_ref' => 'cita',
                    ':ref_id'  => $citaId,
                    ':date'    => $now
                ]);

                $this->logHistorial($citaId, (int)$mat['materialId'], $cant, 0, 'Ejecutado', 'Finalizada', $usuarioId);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            ApiResponse::error('Error al ejecutar deducción: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Revierte la deducción de stock cuando se anula una factura asociada
     * a una cita Finalizada. Lee los movimientos Exit previos y crea Entry inversos.
     */
    public function reverseDeductionOnAnulacion(int $citaId, int $usuarioId, int $facturaId, bool $manageTransaction = true): bool
    {
        try {
            $ownTx = $manageTransaction && !$this->db->inTransaction();
            if ($ownTx) $this->db->beginTransaction();

            $movStmt = $this->db->prepare(
                "SELECT material_id, SUM(quantity) AS qty
                 FROM inventory_movements
                 WHERE tipo_referencia = 'cita'
                   AND referencia_id = :cid
                   AND action_type = 'Exit'
                 GROUP BY material_id"
            );
            $movStmt->execute([':cid' => $citaId]);
            $movements = $movStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($movements)) {
                if ($ownTx) $this->db->commit();
                return true; // nada que revertir
            }

            $now = date('Y-m-d H:i:s');

            $upsertStmt = $this->db->prepare(
                "INSERT INTO material_stock_locations (material_id, location_id, quantity)
                 VALUES (:mid, :lid, :qty)
                 ON DUPLICATE KEY UPDATE quantity = quantity + :qty2"
            );
            $entryMovStmt = $this->db->prepare(
                "INSERT INTO inventory_movements (material_id, user_id, action_type, quantity, reason, tipo_referencia, referencia_id, movement_date)
                 VALUES (:mid, :uid, 'Entry', :qty, :reason, 'cita', :ref_id, :date)"
            );
            $releaseStmt = $this->db->prepare(
                "UPDATE materials SET reserved_stock = GREATEST(reserved_stock - :cant, 0) WHERE material_id = :id"
            );

            // Buscar ubicación por defecto válida (fallback: primera location existente)
            $defaultLocStmt = $this->db->query("SELECT id FROM locations ORDER BY id ASC LIMIT 1");
            $defaultLoc = (int)$defaultLocStmt->fetchColumn();
            if ($defaultLoc <= 0) $defaultLoc = 1;

            foreach ($movements as $m) {
                $materialId = (int)$m['material_id'];
                $qty = (int)$m['qty'];
                if ($qty <= 0) continue;

                // Devolver stock a la primera ubicación disponible del material
                $locStmt = $this->db->prepare(
                    "SELECT location_id FROM material_stock_locations WHERE material_id = :mid ORDER BY location_id ASC LIMIT 1"
                );
                $locStmt->execute([':mid' => $materialId]);
                $loc = $locStmt->fetch(PDO::FETCH_ASSOC);

                $locationId = $loc ? (int)$loc['location_id'] : $defaultLoc;

                $upsertStmt->execute([
                    ':mid'  => $materialId,
                    ':lid'  => $locationId,
                    ':qty'  => $qty,
                    ':qty2' => $qty
                ]);

                $entryMovStmt->execute([
                    ':mid'    => $materialId,
                    ':uid'    => $usuarioId,
                    ':qty'    => $qty,
                    ':reason' => 'Reversión por anulación de Factura #' . $facturaId,
                    ':ref_id' => $citaId,
                    ':date'   => $now
                ]);

                // Liberar reserved_stock (pudo haber quedado)
                $releaseStmt->execute([':cant' => $qty, ':id' => $materialId]);
            }

            if ($ownTx) $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($ownTx) && $ownTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            ApiResponse::error('Error al revertir deducción: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function deleteByCitaId(int $citaId): void
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cita_materiales WHERE cita_id = :cid");
            $stmt->execute([':cid' => $citaId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error al limpiar materiales de la cita.', 500);
        }
    }

    public function obtenerHistorial(int $citaId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT h.*, m.name AS materialName, m.material_code AS materialCode,
                        u.first_name, u.last_name
                 FROM cita_materiales_historial h
                 LEFT JOIN materials m ON h.material_id = m.material_id
                 LEFT JOIN users u ON h.usuario_id = u.user_id
                 WHERE h.cita_id = :citaId
                 ORDER BY h.id DESC"
            );
            $stmt->execute([':citaId' => $citaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al obtener historial.', 500);
            return [];
        }
    }

    private function logHistorial(int $citaId, int $materialId, int $cantidadAnterior, int $cantidadNueva, string $accion, string $estadoCita, int $usuarioId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cita_materiales_historial
             (cita_id, material_id, cantidad_anterior, cantidad_nueva, accion, estado_cita_momento, usuario_id)
             VALUES (:cid, :mid, :ca, :cn, :acc, :est, :uid)"
        );
        $stmt->execute([
            ':cid' => $citaId,
            ':mid' => $materialId,
            ':ca'  => $cantidadAnterior,
            ':cn'  => $cantidadNueva,
            ':acc' => $accion,
            ':est' => $estadoCita,
            ':uid' => $usuarioId
        ]);
    }
}
