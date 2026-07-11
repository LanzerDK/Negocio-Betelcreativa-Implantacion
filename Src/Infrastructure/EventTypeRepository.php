<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\EventTypeModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

// EventTypeRepository — Acceso a datos de la tabla `event_types`
// CRUD con validación de nombre único y verificación de citas asociadas
class EventTypeRepository
{
    private PDO $db;

    // Obtiene la conexión PDO singleton desde Database
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Obtiene todos los tipos de evento activos, ordenados alfabéticamente
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT id, name, is_active AS isActive
                 FROM event_types
                 ORDER BY name ASC"
            );
            $types = [];
            while ($row = $stmt->fetch()) {
                $types[] = new EventTypeModel($row);
            }
            return $types;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    // Busca un tipo de evento por su ID
    public function findById(int $id): ?EventTypeModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, is_active AS isActive
                 FROM event_types WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new EventTypeModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    // Verifica si ya existe un tipo de evento con el mismo nombre (excluyendo ID opcional)
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM event_types WHERE name = :name";
            $params = [':name' => $name];
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

    // Verifica si existen citas asociadas a un tipo de evento
    // (útil para evitar eliminar tipos con citas activas)
    public function hasAppointments(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM citas WHERE event_type_id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Inserta un nuevo tipo de evento
    public function save(EventTypeModel $type): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO event_types (name) VALUES (:name)");
            return $stmt->execute([':name' => $type->getName()]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Actualiza el nombre de un tipo de evento existente
    public function update(EventTypeModel $type): bool
    {
        try {
            $sql = "UPDATE event_types SET name = :name WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $type->getId(),
                ':name' => $type->getName()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // Elimina un tipo de evento por su ID
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM event_types WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
