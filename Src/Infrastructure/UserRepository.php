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
                ':security_code' => '',
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
                        password AS passwordHash,
                        registration_date AS checkinTime,
                        role,
                        avatar AS avatarUrl
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
            return null;
        }
    }

    public function findByEmail(string $email): ?UserModel
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
                        password AS passwordHash,
                        registration_date AS checkinTime,
                        role,
                        avatar AS avatarUrl
                    FROM users 
                    WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $data = $stmt->fetch();
            if ($data) {
                return new UserModel($data);
            }
            return null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    /**
     * Busca un usuario por su ID
     */
    public function findById(int $id): ?UserModel
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
                        password AS passwordHash,
                        registration_date AS checkinTime,
                        role,
                        avatar AS avatarUrl
                    FROM users 
                    WHERE user_id = :id";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();

            if ($data) {
                return new UserModel($data);
            }
            
            return null;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return null;
        }
    }

    /**
     * Actualiza los datos del perfil de un usuario
     */
    public function update(UserModel $user): bool
    {
        try {
            $sql = "UPDATE users SET 
                        first_name = :first_name, 
                        last_name = :last_name, 
                        email = :email, 
                        phone = :phone
                    WHERE user_id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':first_name' => $user->getName(),
                ':last_name'  => $user->getLastName(),
                ':email'      => $user->getEmail(),
                ':phone'      => $user->getPhone(),
                ':id'         => $user->getId(),
            ]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Actualiza solo la contraseña
     */
    public function updatePassword(int $userId, string $newHash): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = :hash WHERE user_id = :id");
            return $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Actualiza la ruta del avatar
     */
    public function updateAvatar(int $userId, string $avatarPath): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET avatar = :avatar WHERE user_id = :id");
            return $stmt->execute([':avatar' => $avatarPath, ':id' => $userId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    // ── Admin: listar usuarios ──────────────────────────────────
    public function findAll(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $where = '';
            $params = [':limit' => $perPage, ':offset' => $offset];

            if ($search !== '') {
                $where = "WHERE (first_name LIKE :q OR last_name LIKE :q2 OR email LIKE :q3 OR username LIKE :q4)";
                $params[':q'] = "%{$search}%";
                $params[':q2'] = "%{$search}%";
                $params[':q3'] = "%{$search}%";
                $params[':q4'] = "%{$search}%";
            }

            $countSql = "SELECT COUNT(*) FROM users $where";
            $countStmt = $this->db->prepare($countSql);
            if ($search !== '') {
                $countStmt->execute([':q' => "%{$search}%", ':q2' => "%{$search}%", ':q3' => "%{$search}%", ':q4' => "%{$search}%"]);
            } else {
                $countStmt->execute();
            }
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT 
                        user_id AS id, 
                        first_name AS name, 
                        last_name AS lastName, 
                        username AS user, 
                        email,
                        phone,
                        role,
                        is_active,
                        avatar,
                        registration_date AS checkinTime
                    FROM users 
                    $where
                    ORDER BY registration_date DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $users = [];
            foreach ($rows as $r) {
                $users[] = [
                    'id'         => (int)$r['id'],
                    'name'       => $r['name'],
                    'last_name'  => $r['lastName'],
                    'username'   => $r['user'],
                    'email'      => $r['email'],
                    'phone'      => $r['phone'],
                    'role'       => $r['role'] ?? 'user',
                    'is_active'  => (bool)($r['is_active'] ?? true),
                    'avatar'     => $r['avatar'],
                    'member_since' => $r['checkinTime'],
                ];
            }

            return [
                'users'      => $users,
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $perPage,
                'total_pages'=> max(1, (int)ceil($total / $perPage)),
            ];
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return [];
        }
    }

    /**
     * Admin: actualizar rol de un usuario
     */
    public function updateRole(int $userId, string $role): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE user_id = :id");
            return $stmt->execute([':role' => $role, ':id' => $userId]);
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Admin: activar/desactivar usuario
     */
    public function toggleActive(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            return true;
        } catch (PDOException $e) {
            ApiResponse::error('Error de base de datos: ' . $e->getMessage(), 500);
            return false;
        }
    }

    /**
     * Obtiene el rol de un usuario
     */
    public function getRole(int $userId): string
    {
        try {
            $stmt = $this->db->prepare("SELECT role FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            $role = $stmt->fetchColumn();
            return $role !== false ? $role : 'user';
        } catch (PDOException $e) {
            return 'user';
        }
    }
}
