<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\SupplierModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class SupplierRepository
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
                "SELECT supplier_id AS id, company_name, contact_name, phone, email, address, notes,
                        supplier_type, subtype, is_active AS isActive,
                        created_at, updated_at
                 FROM suppliers ORDER BY supplier_id DESC"
            );
            $suppliers = [];
            while ($row = $stmt->fetch()) {
                $suppliers[] = new SupplierModel($row);
            }
            return $suppliers;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    public function findById(int $id): ?SupplierModel
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT supplier_id AS id, company_name, contact_name, phone, email, address, notes,
                        supplier_type, subtype, is_active AS isActive
                 FROM suppliers WHERE supplier_id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            return $data ? new SupplierModel($data) : null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM suppliers WHERE company_name = :name";
            $params = [':name' => $name];
            if ($excludeId !== null) {
                $sql .= " AND supplier_id != :excludeId";
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

    public function countMaterials(int $supplierId): int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM materials WHERE supplier_id = :id"
            );
            $stmt->execute([':id' => $supplierId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return 0;
        }
    }

    public function save(SupplierModel $supplier): bool
    {
        try {
            $sql = "INSERT INTO suppliers (company_name, contact_name, phone, email, address, notes, supplier_type, subtype)
                    VALUES (:company_name, :contact_name, :phone, :email, :address, :notes, :supplier_type, :subtype)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':company_name' => $supplier->getCompanyName(),
                ':contact_name' => $supplier->getContactName(),
                ':phone' => $supplier->getPhone(),
                ':email' => $supplier->getEmail(),
                ':address' => $supplier->getAddress(),
                ':notes' => $supplier->getNotes(),
                ':supplier_type' => $supplier->getSupplierType(),
                ':subtype' => $supplier->getSubtype()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function update(SupplierModel $supplier): bool
    {
        try {
            $sql = "UPDATE suppliers SET company_name = :company_name, contact_name = :contact_name,
                     phone = :phone, email = :email, address = :address, notes = :notes,
                     supplier_type = :supplier_type, subtype = :subtype, is_active = :is_active
                    WHERE supplier_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $supplier->getId(),
                ':company_name' => $supplier->getCompanyName(),
                ':contact_name' => $supplier->getContactName(),
                ':phone' => $supplier->getPhone(),
                ':email' => $supplier->getEmail(),
                ':address' => $supplier->getAddress(),
                ':notes' => $supplier->getNotes(),
                ':supplier_type' => $supplier->getSupplierType(),
                ':subtype' => $supplier->getSubtype(),
                ':is_active' => $supplier->getIsActive() ? 1 : 0
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM suppliers WHERE supplier_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }
}
