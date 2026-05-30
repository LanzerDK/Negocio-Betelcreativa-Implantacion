<?php

namespace BetelCreativa\Infrastructure;

use BetelCreativa\Config\Database;
use BetelCreativa\Domain\UserModel;
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
        // EXPLICACIÓN: Corregido el nombre de la columna a 'correo' y el parámetro a ':correo' para que coincida.
        // Asumo que tu tabla se llama 'usuarios' (en español) para mantener la consistencia del proyecto.
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email OR username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email'    => $email, 
             ':username' => $username
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function save(UserModel $user): bool
{
    try {
        // Usamos los nombres exactos del PDF
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
    // Esto obligará al sistema a decirnos exactamente qué falló en la base de datos
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error de MySQL: ' . $e->getMessage()
    ]);
    exit;
}
}
}