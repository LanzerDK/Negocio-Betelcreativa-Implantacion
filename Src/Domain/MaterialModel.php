<?php

namespace BetelCreativa\Domain;

// MaterialModel — Modelo de dominio para los materiales/inventario
// Representa un artículo con código, nombre, precio, tipo, stock y configuración de empaque
class MaterialModel
{
    // Propiedades del material
    private ?int $id;                      // ID único (null si es nuevo)
    private string $code;                  // Código único del material (ej: MAT-001)
    private string $name;                  // Nombre descriptivo
    private float $price;                  // Precio unitario
    private string $costType;              // Tipo de costo: 'unit' o 'wholesale'
    private ?int $wholesaleQty;            // Cantidad para precio por mayoreo
    private int $stock;                    // Stock actual disponible (calculado desde material_stock_locations)
    private ?string $imageUrl;             // URL de la imagen del material
    private ?int $categoryId;              // ID de la categoría a la que pertenece
    private string $materialType;          // Tipo: 'consumible' o 'activo_retornable'
    private ?int $supplierId;              // ID del proveedor asociado
    private ?string $detalleComodin;       // Detalle si el proveedor es tipo 'comodín'
    private ?int $locationId;              // ID de la ubicación predeterminada
    private bool $isActive;                // Si el material está activo en el sistema
    private int $reservedStock;            // Stock reservado para citas en curso
    private string $unidadCompra;          // Unidad en la que se compra (ej: "Paquete", "Unidad")
    private string $unidadConsumo;         // Unidad en la que se consume (ej: "Unidad")
    private int $factorConversion;         // Cuántas unidades de consumo hay en una unidad de compra

    // Constructor: recibe datos desde API o repositorio
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

    // Getters
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

    // Setters
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
