<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\TaskModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class TaskRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAllPending(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT task_id AS id, title, priority, status,
                        created_at AS createdAt, completed_at AS completedAt
                 FROM tasks
                 WHERE status = 'pending'
                 ORDER BY
                    FIELD(priority, 'high', 'medium', 'low'),
                    created_at DESC"
            );
            $tasks = [];
            while ($row = $stmt->fetch()) {
                $tasks[] = new TaskModel($row);
            }
            return $tasks;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findAllCompleted(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT task_id AS id, title, priority, status,
                        created_at AS createdAt, completed_at AS completedAt
                 FROM tasks
                 WHERE status = 'completed'
                 ORDER BY completed_at DESC"
            );
            $tasks = [];
            while ($row = $stmt->fetch()) {
                $tasks[] = new TaskModel($row);
            }
            return $tasks;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findById(int $id): ?TaskModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT task_id AS id, title, priority, status,
                        created_at AS createdAt, completed_at AS completedAt
                 FROM tasks WHERE task_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new TaskModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function save(TaskModel $task): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tasks (title, priority, status) VALUES (:title, :priority, :status)"
            );
            return $stmt->execute([
                ':title' => $task->getTitle(),
                ':priority' => $task->getPriority(),
                ':status' => $task->getStatus(),
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function update(TaskModel $task): bool
    {
        try {
            $sql = "UPDATE tasks SET title = :title, priority = :priority, status = :status";
            if ($task->getStatus() === 'completed') {
                $sql .= ", completed_at = NOW()";
            } else {
                $sql .= ", completed_at = NULL";
            }
            $sql .= " WHERE task_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':title' => $task->getTitle(),
                ':priority' => $task->getPriority(),
                ':status' => $task->getStatus(),
                ':id' => $task->getId(),
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE task_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
