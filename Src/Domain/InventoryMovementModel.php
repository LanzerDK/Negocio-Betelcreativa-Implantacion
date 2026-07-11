<?php

namespace BetelCreativa\Domain;

// InventoryMovementModel — Modelo para los movimientos de inventario
// Registra cualquier cambio en el stock: entrada, salida, transferencia o ajuste
class InventoryMovementModel
{
    private ?int $id;                        // ID único del movimiento
    private int $materialId;                 // Material afectado
    private int $userId;                     // Usuario que realizó el movimiento
    private ?string $movementDate;           // Fecha y hora del movimiento
    private string $actionType;              // Tipo: Entry, Exit, Transfer
    private int $quantity;                   // Cantidad movida
    private ?string $reason;                 // Razón del movimiento
    private ?string $extraNote;              // Nota adicional
    private ?int $originLocationId;          // Ubicación de origen (para transferencias)
    private ?int $destinationLocationId;     // Ubicación de destino (para transferencias)

    // Constructor: recibe datos desde el controlador o repositorio
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->materialId = (int)($data['materialId'] ?? 0);
        $this->userId = (int)($data['userId'] ?? 0);
        $this->movementDate = $data['movementDate'] ?? null;
        $this->actionType = $data['actionType'] ?? 'Entry';
        $this->quantity = (int)($data['quantity'] ?? 0);
        $this->reason = $data['reason'] ?? null;
        $this->extraNote = $data['extraNote'] ?? null;
        $this->originLocationId = $data['originLocationId'] ?? null;
        $this->destinationLocationId = $data['destinationLocationId'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getMaterialId(): int { return $this->materialId; }
    public function getUserId(): int { return $this->userId; }
    public function getMovementDate(): ?string { return $this->movementDate; }
    public function getActionType(): string { return $this->actionType; }
    public function getQuantity(): int { return $this->quantity; }
    public function getReason(): ?string { return $this->reason; }
    public function getExtraNote(): ?string { return $this->extraNote; }
    public function getOriginLocationId(): ?int { return $this->originLocationId; }
    public function getDestinationLocationId(): ?int { return $this->destinationLocationId; }
}
