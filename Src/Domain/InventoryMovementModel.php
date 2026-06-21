<?php

namespace BetelCreativa\Domain;

class InventoryMovementModel
{
    private ?int $id;
    private int $materialId;
    private int $userId;
    private ?string $movementDate;
    private string $actionType;
    private int $quantity;
    private ?string $reason;
    private ?string $extraNote;
    private ?int $originLocationId;
    private ?int $destinationLocationId;

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
