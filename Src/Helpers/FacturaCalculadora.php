<?php

namespace BetelCreativa\Helpers;

// FacturaCalculadora — Cálculos financieros para facturación
// Toma el costo del servicio y los materiales asignados y calcula subtotal, IVA y total
class FacturaCalculadora
{
    // Calcula los totales de una factura dados el costo del servicio y los materiales
    // $materiales es un array donde cada elemento tiene 'cantidad' (o cantidad_utilizada) y 'precioUnitario' (o precio_unitario)
    public static function calcularTotales(float $costoServicio, array $materiales): array
    {
        // Suma el costo de todos los materiales (cantidad × precio unitario)
        $totalMateriales = 0;
        foreach ($materiales as $m) {
            // Soporta dos nomenclaturas: camelCase (desde JS) y snake_case (desde BD)
            $totalMateriales += (float)($m['cantidad'] ?? $m['cantidad_utilizada'] ?? 0)
                             * (float)($m['precioUnitario'] ?? $m['precio_unitario'] ?? 0);
        }
        // Subtotal = materiales + mano de obra
        $subtotal = $totalMateriales + $costoServicio;
        // IVA calculado sobre el subtotal usando la tasa definida en Config/app.php
        $iva = $subtotal * IVA_RATE;
        // Total final con IVA incluido
        $total = $subtotal + $iva;

        // Devuelve todos los valores redondeados a 2 decimales
        return [
            'totalMateriales' => round($totalMateriales, 2),
            'subtotal'        => round($subtotal, 2),
            'iva'             => round($iva, 2),
            'total'           => round($total, 2),
        ];
    }
}
