<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\AppointmentModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class AppointmentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private const COLUMNS = "c.id, c.cliente_id AS clienteId,
            c.fecha_hora_inicio AS fechaHoraInicio,
            c.fecha_hora_fin AS fechaHoraFin,
            COALESCE(et.name, '—') AS eventType,
            c.event_type_id AS eventTypeId,
            c.ubicacion, c.estado,
            c.estado_previo_cancelacion AS estadoPrevioCancelacion,
            c.fecha_hora_cancelacion AS fechaHoraCancelacion,
            c.motivo_cancelacion AS motivoCancelacion,
            c.notas, c.created_at AS createdAt";

    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 WHERE c.estado != 'Cancelado'
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar citas.', 500);
            return [];
        }
    }

    public function findAllWithCanceladas(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar citas.', 500);
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 WHERE c.id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        } catch (PDOException $e) {
            ApiResponse::error('Error al buscar cita.', 500);
            return null;
        }
    }

    public function findCanceladas(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 WHERE c.estado = 'Cancelado'
                 ORDER BY c.fecha_hora_cancelacion DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al cargar canceladas.', 500);
            return [];
        }
    }

    public function findByClienteId(int $clienteId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT " . self::COLUMNS . "
                 FROM citas c
                 LEFT JOIN event_types et ON c.event_type_id = et.id
                 WHERE c.cliente_id = :clienteId
                 ORDER BY c.fecha_hora_inicio DESC"
            );
            $stmt->execute([':clienteId' => $clienteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ApiResponse::error('Error al buscar citas del cliente.', 500);
            return [];
        }
    }

    public function hasTimeConflict(int $clienteId, string $inicio, string $fin, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM citas
                    WHERE cliente_id = :clienteId
                      AND estado != 'Cancelado'
                      AND fecha_hora_inicio < :fin
                      AND fecha_hora_fin > :inicio";
            $params = [
                ':clienteId' => $clienteId,
                ':inicio'    => $inicio,
                ':fin'       => $fin
            ];
            if ($excludeId) {
                $sql .= " AND id != :excludeId";
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function save(array $data): ?int
    {
        try {
            $sql = "INSERT INTO citas (cliente_id, fecha_hora_inicio, fecha_hora_fin,
                                       event_type_id, ubicacion, estado, notas)
                    VALUES (:clienteId, :fechaHoraInicio, :fechaHoraFin,
                            :eventTypeId, :ubicacion, :estado, :notas)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':clienteId'        => $data['clienteId'],
                ':fechaHoraInicio'  => $data['fechaHoraInicio'],
                ':fechaHoraFin'     => $data['fechaHoraFin'],
                ':eventTypeId'      => $data['eventTypeId'] ?? null,
                ':ubicacion'        => $data['ubicacion'] ?? null,
                ':estado'           => $data['estado'] ?? 'En Proceso',
                ':notas'            => $data['notas'] ?? null
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            ApiResponse::error('Error al guardar cita: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            $map = [
                'clienteId'       => 'cliente_id',
                'fechaHoraInicio' => 'fecha_hora_inicio',
                'fechaHoraFin'    => 'fecha_hora_fin',
                'eventTypeId'     => 'event_type_id',
                'ubicacion'       => 'ubicacion',
                'estado'          => 'estado',
                'estadoPrevioCancelacion' => 'estado_previo_cancelacion',
                'fechaHoraCancelacion'    => 'fecha_hora_cancelacion',
                'motivoCancelacion'       => 'motivo_cancelacion',
                'notas'           => 'notas'
            ];

            foreach ($map as $key => $column) {
                if (array_key_exists($key, $data)) {
                    $fields[] = "$column = :$key";
                    $params[":$key"] = $data[$key];
                }
            }

            if (empty($fields)) return false;

            $sql = "UPDATE citas SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            ApiResponse::error('Error al actualizar cita: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        return $this->update($id, ['estado' => $estado]);
    }
}
