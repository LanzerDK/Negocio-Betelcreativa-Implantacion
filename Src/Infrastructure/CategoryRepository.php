<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\CategoryModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class CategoryRepository
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
                "SELECT category_id AS id, name, description, status, image_url AS imageUrl FROM categories ORDER BY category_id DESC"
            );
            $categories = [];
            while ($row = $stmt->fetch()) {
                $categories[] = new CategoryModel($row);
            }
            return $categories;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findById(int $id): ?CategoryModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT category_id AS id, name, description, status, image_url AS imageUrl FROM categories WHERE category_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new CategoryModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM categories WHERE name = :name";
            $params = [':name' => $name];
            if ($excludeId !== null) {
                $sql .= " AND category_id != :excludeId";
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

    public function save(CategoryModel $category): bool
    {
        try {
            $sql = "INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $category->getName(),
                ':description' => $category->getDescription(),
                ':status' => $category->getStatus()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function update(CategoryModel $category): bool
    {
        try {
            $sql = "UPDATE categories SET name = :name, description = :description, status = :status WHERE category_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $category->getId(),
                ':name' => $category->getName(),
                ':description' => $category->getDescription(),
                ':status' => $category->getStatus()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM categories WHERE category_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
