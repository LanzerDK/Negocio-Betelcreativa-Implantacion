<?php

namespace BetelCreativa\Domain;

// PagoFacturaModel — Modelo para los abonos/cuotas aplicados a una factura
// Almacena el monto (siempre en USD), método de pago y tasa BCV usada para auditoría
class PagoFacturaModel
{
    private ?int $id;                // ID único del pago
    private int $facturaId;          // ID de la factura a la que se aplica
    private float $monto;            // Monto del pago en USD (base contable)
    private string $metodoPago;      // Método: divisas, efectivo, pagomovil
    private float $tasaUsada;        // Tasa BCV del momento del pago
    private ?string $fecha;          // Fecha y hora del pago

    // Constructor: recibe datos desde la API o repositorio
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->facturaId = (int)($data['facturaId'] ?? 0);
        $this->monto = (float)($data['monto'] ?? 0);
        $this->metodoPago = $data['metodoPago'] ?? 'efectivo';
        $this->tasaUsada = (float)($data['tasaUsada'] ?? 0);
        $this->fecha = $data['fecha'] ?? null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getFacturaId(): int { return $this->facturaId; }
    public function getMonto(): float { return $this->monto; }
    public function getMetodoPago(): string { return $this->metodoPago; }
    public function getTasaUsada(): float { return $this->tasaUsada; }
    public function getFecha(): ?string { return $this->fecha; }

    // Convierte a array para enviar como JSON
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
