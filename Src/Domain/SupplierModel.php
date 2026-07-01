<?php

namespace BetelCreativa\Domain;

class SupplierModel
{
    private ?int $id;
    private string $companyName;
    private ?string $contactName;
    private ?string $phone;
    private ?string $email;
    private ?string $address;
    private bool $isActive;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->companyName = $data['company_name'] ?? '';
        $this->contactName = $data['contact_name'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->isActive = (bool)($data['isActive'] ?? $data['is_active'] ?? true);
    }

    public function getId(): ?int { return $this->id; }
    public function getCompanyName(): string { return $this->companyName; }
    public function getContactName(): ?string { return $this->contactName; }
    public function getPhone(): ?string { return $this->phone; }
    public function getEmail(): ?string { return $this->email; }
    public function getAddress(): ?string { return $this->address; }
    public function getIsActive(): bool { return $this->isActive; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setCompanyName(string $companyName): void { $this->companyName = $companyName; }
    public function setContactName(?string $contactName): void { $this->contactName = $contactName; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setAddress(?string $address): void { $this->address = $address; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
}
