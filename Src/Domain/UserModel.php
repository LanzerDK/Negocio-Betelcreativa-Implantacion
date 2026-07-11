<?php

namespace BetelCreativa\Domain;

// UserModel — Modelo de dominio para los usuarios del sistema
// Representa un usuario con credenciales, rol y datos personales
class UserModel
{
    private ?int $id;                // ID único del usuario
    private string $name;            // Nombre
    private string $lastName;        // Apellido
    private string $user;            // Nombre de usuario (username)
    private string $email;           // Correo electrónico
    private string $ci;              // Cédula de identidad
    private string $phone;           // Teléfono
    private string $passwordHash;    // Hash de la contraseña (bcrypt)
    private string $role = 'user';              // Rol en texto (legacy, para compatibilidad)
    private ?int $idRol = null;                 // ID del rol desde la tabla roles
    private ?string $avatarUrl = null;          // URL del avatar
    private ?string $checkinTime;               // Hora de registro de entrada

    // Constructor: mapea los datos del controlador a las propiedades del modelo
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->user = $data['user'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->ci = $data['ci'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->passwordHash = $data['passwordHash'] ?? '';
        $this->role = $data['role'] ?? 'user';
        $this->idRol = $data['idRol'] ?? null;
        $this->avatarUrl = $data['avatarUrl'] ?? null;
        $this->checkinTime = $data['checkinTime'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getLastName(): string { return $this->lastName; }
    public function getUser(): string { return $this->user; }
    public function getEmail(): string { return $this->email; }
    public function getCi(): string { return $this->ci; }
    public function getPhone(): string { return $this->phone; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string { return $this->role; }
    public function getIdRol(): ?int { return $this->idRol; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }
    public function getCheckinTime(): ?string { return $this->checkinTime; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setUser(string $user): void { $this->user = $user; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; }
    public function setAvatarUrl(?string $url): void { $this->avatarUrl = $url; }
    public function setIdRol(?int $idRol): void { $this->idRol = $idRol; }
    public function setCheckinTime(?string $time): void { $this->checkinTime = $time; }

    // Verifica si una contraseña en texto plano coincide con el hash almacenado
    public function verificarPassword(string $passwordPlana): bool
    {
        return password_verify($passwordPlana, $this->passwordHash);
    }
}