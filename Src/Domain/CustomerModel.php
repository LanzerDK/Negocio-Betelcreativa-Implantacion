<?php

namespace BetelCreativa\Domain;

// =============================================
// Modelo de Cliente
// Representa un cliente en el sistema con todos
// sus datos de contacto, tipo, fuente y preferencias
// =============================================
class CustomerModel
{
    private ?int $id;
    private string $firstName;
    private string $lastName;
    private string $idNumber;
    private ?string $email;
    private ?string $phone;
    private ?string $address;
    private string $clientType;
    private string $source;
    private ?string $notes;
    private ?string $preferences;
    private ?string $avatar;
    private bool $isActive;

    // Constructor: recibe array asociativo y asigna cada campo
    // Los nombres de las keys coinciden con los alias SQL del Repository
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->firstName = $data['firstName'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->idNumber = $data['idNumber'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->clientType = $data['clientType'] ?? 'regular';
        $this->source = $data['source'] ?? 'other';
        $this->notes = $data['notes'] ?? null;
        $this->preferences = $data['preferences'] ?? null;
        $this->avatar = $data['avatar'] ?? null;
        $this->isActive = isset($data['isActive']) ? filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN) : true;
    }

    // Getters: permiten leer los datos desde fuera de la clase
    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getIdNumber(): string { return $this->idNumber; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddress(): ?string { return $this->address; }
    public function getClientType(): string { return $this->clientType; }
    public function getSource(): string { return $this->source; }
    public function getNotes(): ?string { return $this->notes; }
    public function getPreferences(): ?string { return $this->preferences; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function isActive(): bool { return $this->isActive; }

    // Setters: permiten modificar los datos del cliente
    public function setFirstName(string $name): void { $this->firstName = $name; }
    public function setLastName(string $name): void { $this->lastName = $name; }
    public function setIdNumber(string $id): void { $this->idNumber = $id; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }
    public function setAddress(?string $address): void { $this->address = $address; }
    public function setClientType(string $type): void { $this->clientType = $type; }
    public function setSource(string $source): void { $this->source = $source; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function setPreferences(?string $prefs): void { $this->preferences = $prefs; }
    public function setAvatar(?string $avatar): void { $this->avatar = $avatar; }
}
