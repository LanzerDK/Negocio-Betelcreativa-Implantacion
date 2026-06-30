<?php

namespace BetelCreativa\Domain;

class UserModel
{
    private ?int $id;
    private string $name;
    private string $lastName;
    private string $user;
    private string $email;
    private string $ci;
    private string $phone; 
    private string $passwordHash;
    private string $role = 'user';
    private ?int $idRol = null;
    private ?string $avatarUrl = null;
    private ?string $checkinTime;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->user = $data['user'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->ci = $data['ci'] ?? '';
        // EXPLICACIÓN: Mapeamos el teléfono desde el array que envía el controlador.
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

    public function verificarPassword(string $passwordPlana): bool
    {
        return password_verify($passwordPlana, $this->passwordHash);
    }
}