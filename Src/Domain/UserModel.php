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
    private string $passwordHash;
    private ?string $passwordUnique;
    private ?string $checkinTime;  // mantengo string por simplicidad, puedes cambiar a DateTime
    
    // Constructor con parámetros opcionales usando array (más flexible)
    public function __construct(array $data = []) 
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->user = $data['user'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->ci = $data['ci'] ?? '';
        $this->passwordHash = $data['passwordHash'] ?? '';
        $this->passwordUnique = $data['passwordUnique'] ?? null;
        $this->checkinTime = $data['checkinTime'] ?? null;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getLastName(): string { return $this->lastName; }
    public function getUser(): string { return $this->user; }
    public function getEmail(): string { return $this->email; }
    public function getCi(): string { return $this->ci; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getPasswordUnique(): ?string { return $this->passwordUnique; }
    public function getCheckinTime(): ?string { return $this->checkinTime; }
    
    // Setters (opcionales, solo los que necesites)
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; }
    public function setCheckinTime(?string $time): void { $this->checkinTime = $time; }
    
    // Verificar contraseña
    public function verificarPassword(string $passwordPlana): bool {
        return password_verify($passwordPlana, $this->passwordHash);
    }
}