<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\UserModel;
use BetelCreativa\Helpers\ApiResponse;
use PDO;
use PDOException;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function existsByEmailOrUser(string $email, string $username): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email OR username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email'    => $email,
                ':username' => $username
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        return false;
            }
    }

    public function save(UserModel $user): bool
    {
        try {

            $sql = "INSERT INTO users (first_name, last_name, username, email, id_number, password, security_code, phone) 
                VALUES (:first_name, :last_name, :username, :email, :id_number, :password, :security_code, :phone)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':first_name'    => $user->getName(),
                ':last_name'     => $user->getLastName(),
                ':username'      => $user->getUser(),
                ':email'         => $user->getEmail(),
                ':id_number'     => $user->getCi(),
                ':password'      => $user->getPasswordHash(),
                ':security_code' => 'XBX-89X-XsA', // El código que definimos
                ':phone'         => $user->getPhone()
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    public function findByUsernameOrEmail(string $identifier): ?UserModel
    {
        try {
            $sql = "SELECT 
                        user_id AS id, 
                        first_name AS name, 
                        last_name AS lastName, 
                        username AS user, 
                        email, 
                        id_number AS ci, 
                        phone, 
                        password AS passwordHash 
                    FROM users 
                    WHERE username = :identifier1 OR email = :identifier2";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':identifier1' => $identifier, ':identifier2' => $identifier]);
            
            $data = $stmt->fetch();

            if ($data) {
                return new UserModel($data);
            }
            
            return null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
        }
    }
}
