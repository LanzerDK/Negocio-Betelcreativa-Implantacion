<?php

namespace BetelCreativa\Domain;

class MaterialModel
{
    private ?int $id;
    private string $code;
    private string $name;
    private float $price;
    private string $costType;
    private ?int $wholesaleQty;
    private int $stock;
    private ?string $imageUrl;
    private ?int $categoryId;
    private string $materialType;
    private ?int $supplierId;
    private ?string $detalleComodin;
    private ?int $locationId;
    private bool $isActive;
    private int $reservedStock;
    private string $unidadCompra;
    private string $unidadConsumo;
    private int $factorConversion;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->code = $data['code'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->price = (float)($data['price'] ?? 0);
        $this->costType = $data['costType'] ?? 'unit';
        $this->wholesaleQty = $data['wholesaleQty'] ?? null;
        $this->stock = (int)($data['stock'] ?? 0);
        $this->imageUrl = $data['imageUrl'] ?? null;
        $this->categoryId = $data['categoryId'] ?? null;
        $this->materialType = $data['materialType'] ?? 'consumible';
        $this->supplierId = $data['supplierId'] ?? null;
        $this->detalleComodin = $data['detalleComodin'] ?? null;
        $this->locationId = $data['locationId'] ?? null;
        $this->isActive = (bool)($data['isActive'] ?? true);
        $this->reservedStock = (int)($data['reservedStock'] ?? 0);
        $this->unidadCompra = $data['unidadCompra'] ?? 'Unidad';
        $this->unidadConsumo = $data['unidadConsumo'] ?? 'Unidad';
        $this->factorConversion = (int)($data['factorConversion'] ?? 1);
    }

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getPrice(): float { return $this->price; }
    public function getCostType(): string { return $this->costType; }
    public function getWholesaleQty(): ?int { return $this->wholesaleQty; }
    public function getStock(): int { return $this->stock; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getMaterialType(): string { return $this->materialType; }
    public function getSupplierId(): ?int { return $this->supplierId; }
    public function getDetalleComodin(): ?string { return $this->detalleComodin; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getReservedStock(): int { return $this->reservedStock; }
    public function getUnidadCompra(): string { return $this->unidadCompra; }
    public function getUnidadConsumo(): string { return $this->unidadConsumo; }
    public function getFactorConversion(): int { return $this->factorConversion; }

    public function setCode(string $code): void { $this->code = $code; }
    public function setName(string $name): void { $this->name = $name; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function setCostType(string $costType): void { $this->costType = $costType; }
    public function setWholesaleQty(?int $wholesaleQty): void { $this->wholesaleQty = $wholesaleQty; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }
    public function setSupplierId(?int $supplierId): void { $this->supplierId = $supplierId; }
    public function setDetalleComodin(?string $detalleComodin): void { $this->detalleComodin = $detalleComodin; }
    public function setLocationId(?int $locationId): void { $this->locationId = $locationId; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function setReservedStock(int $reservedStock): void { $this->reservedStock = $reservedStock; }
}
