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
    private ?string $notes;
    private string $supplierType;
    private ?string $subtype;
    private bool $isActive;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->companyName = $data['company_name'] ?? '';
        $this->contactName = $data['contact_name'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->supplierType = $data['supplier_type'] ?? 'fijo';
        $this->subtype = $data['subtype'] ?? null;
        $this->isActive = (bool)($data['isActive'] ?? $data['is_active'] ?? true);
    }

    public function getId(): ?int { return $this->id; }
    public function getCompanyName(): string { return $this->companyName; }
    public function getContactName(): ?string { return $this->contactName; }
    public function getPhone(): ?string { return $this->phone; }
    public function getEmail(): ?string { return $this->email; }
    public function getAddress(): ?string { return $this->address; }
    public function getNotes(): ?string { return $this->notes; }
    public function getSupplierType(): string { return $this->supplierType; }
    public function getSubtype(): ?string { return $this->subtype; }
    public function getIsActive(): bool { return $this->isActive; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setCompanyName(string $companyName): void { $this->companyName = $companyName; }
    public function setContactName(?string $contactName): void { $this->contactName = $contactName; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setAddress(?string $address): void { $this->address = $address; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function setSupplierType(string $supplierType): void { $this->supplierType = $supplierType; }
    public function setSubtype(?string $subtype): void { $this->subtype = $subtype; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
}
