<?php

namespace BetelCreativa\Domain;

class PagoFacturaModel
{
    private ?int $id;
    private int $facturaId;
    private float $monto;
    private string $metodoPago;
    private float $tasaUsada;
    private ?string $fecha;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->facturaId = (int)($data['facturaId'] ?? 0);
        $this->monto = (float)($data['monto'] ?? 0);
        $this->metodoPago = $data['metodoPago'] ?? 'efectivo';
        $this->tasaUsada = (float)($data['tasaUsada'] ?? 0);
        $this->fecha = $data['fecha'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getFacturaId(): int { return $this->facturaId; }
    public function getMonto(): float { return $this->monto; }
    public function getMetodoPago(): string { return $this->metodoPago; }
    public function getTasaUsada(): float { return $this->tasaUsada; }
    public function getFecha(): ?string { return $this->fecha; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'facturaId'   => $this->facturaId,
            'monto'       => $this->monto,
            'metodoPago'  => $this->metodoPago,
            'tasaUsada'   => $this->tasaUsada,
            'fecha'       => $this->fecha
        ];
    }
}
