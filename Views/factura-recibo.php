<?php
$facturaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$facturaId): ?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Recibo</title></head>
<body><p style="text-align:center;margin-top:50px;color:red;">ID de factura requerido.</p></body>
</html>
<?php exit; endif;

use BetelCreativa\Config\Database;
use PDO;

$db = Database::getConnection();

$stmt = $db->prepare(
    "SELECT f.id AS facturaId, f.costo_servicio AS costoServicio, f.total_factura AS totalFactura,
            f.notas_cuota AS notasCuota, f.estado, f.created_at AS createdAt,
            CONCAT(cust.first_name, ' ', cust.last_name) AS clienteNombre,
            cust.id_number AS clienteCedula, cust.phone AS clienteTelefono, cust.email AS clienteEmail,
            c.fecha_hora_inicio AS fechaCita, c.ubicacion,
            COALESCE(et.name, '—') AS eventType
     FROM facturas f
     JOIN citas c ON f.cita_id = c.id
     JOIN customers cust ON c.cliente_id = cust.customer_id
     LEFT JOIN event_types et ON c.event_type_id = et.id
     WHERE f.id = :id"
);
$stmt->execute([':id' => $facturaId]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$f) { echo '<p style="text-align:center;margin-top:50px;color:red;">Factura no encontrada.</p>'; exit; }

$stmtMat = $db->prepare(
    "SELECT m.material_code AS codigo, m.name AS nombre, cm.cantidad_utilizada AS cantidad,
            COALESCE(cm.precio_unitario, m.price, 0) AS precio_unitario
     FROM cita_materiales cm
     JOIN materials m ON cm.material_id = m.material_id
     WHERE cm.cita_id = (SELECT cita_id FROM facturas WHERE id = :id)
     ORDER BY m.name"
);
$stmtMat->execute([':id' => $facturaId]);
$materiales = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

$stmtPagos = $db->prepare(
    "SELECT monto, metodo_pago, tasa_usada, fecha FROM pagos_factura WHERE factura_id = :id ORDER BY fecha ASC"
);
$stmtPagos->execute([':id' => $facturaId]);
$pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

$totalPagado = 0;
foreach ($pagos as $p) $totalPagado += (float)$p['monto'];
$saldoPendiente = (float)$f['totalFactura'] - $totalPagado;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?= $f['facturaId'] ?> — Betel Creativa</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; background:#fff; padding:40px; color:#222; }
        .recibo { max-width:700px; margin:0 auto; }
        .header { text-align:center; border-bottom:3px solid #c9a84c; padding-bottom:20px; margin-bottom:25px; }
        .header h1 { font-size:1.8rem; color:#c9a84c; }
        .header h2 { font-size:1rem; color:#666; margin-top:4px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:25px; font-size:0.9rem; }
        .info-grid .label { color:#888; }
        .info-grid .value { font-weight:600; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:0.85rem; }
        th { background:#f5f5f5; padding:8px 10px; text-align:left; border-bottom:2px solid #ddd; }
        td { padding:7px 10px; border-bottom:1px solid #eee; }
        .text-right { text-align:right; }
        .totales { border-top:2px solid #333; margin-top:10px; padding-top:10px; }
        .total-row { display:flex; justify-content:space-between; padding:4px 0; font-size:0.9rem; }
        .total-final { border-top:2px solid #333; margin-top:6px; padding-top:8px; font-size:1.1rem; font-weight:700; }
        .pagos-table { margin-top:20px; }
        .pagos-table h3 { font-size:1rem; margin-bottom:8px; color:#333; }
        .estado-badge { display:inline-block; padding:4px 12px; border-radius:12px; font-size:0.8rem; font-weight:600; }
        .estado-activa { background:#e8f4fd; color:#0066cc; }
        .estado-cerrada { background:#d4edda; color:#155724; }
        .estado-anulada { background:#f8d7da; color:#721c24; }
        .footer { text-align:center; margin-top:30px; padding-top:15px; border-top:1px solid #ddd; font-size:0.8rem; color:#888; }
        .no-print { margin-bottom:20px; text-align:center; }
        .no-print button { padding:10px 24px; background:#c9a84c; color:#fff; border:none; border-radius:6px; font-size:1rem; cursor:pointer; }
        .no-print button:hover { background:#b8952e; }
        @media print {
            .no-print { display:none; }
            body { padding:20px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()"><i class="fas fa-print"></i> Imprimir Recibo</button>
        <button onclick="window.close()" style="background:#666;margin-left:10px;">Cerrar</button>
    </div>

    <div class="recibo">
        <div class="header">
            <h1>Betel Creativa</h1>
            <h2>Recibo de Factura #<?= $f['facturaId'] ?></h2>
            <div style="margin-top:8px;">
                <span class="estado-badge estado-<?= $f['estado'] ?>"><?= ucfirst($f['estado']) ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <div class="label">Cliente</div>
                <div class="value"><?= htmlspecialchars($f['clienteNombre']) ?></div>
                <div class="label" style="margin-top:6px;">Cédula</div>
                <div class="value"><?= htmlspecialchars($f['clienteCedula']) ?></div>
            </div>
            <div>
                <div class="label">Teléfono</div>
                <div class="value"><?= htmlspecialchars($f['clienteTelefono'] ?: '—') ?></div>
                <div class="label" style="margin-top:6px;">Email</div>
                <div class="value"><?= htmlspecialchars($f['clienteEmail'] ?: '—') ?></div>
            </div>
            <div>
                <div class="label">Fecha del Evento</div>
                <div class="value"><?= date('d/m/Y', strtotime($f['fechaCita'])) ?></div>
            </div>
            <div>
                <div class="label">Ubicación</div>
                <div class="value"><?= htmlspecialchars($f['ubicacion'] ?: '—') ?></div>
            </div>
            <div>
                <div class="label">Tipo de Evento</div>
                <div class="value"><?= htmlspecialchars($f['eventType']) ?></div>
            </div>
            <div>
                <div class="label">Emisión</div>
                <div class="value"><?= date('d/m/Y', strtotime($f['createdAt'])) ?></div>
            </div>
        </div>

        <?php if (!empty($materiales)): ?>
        <table>
            <thead>
                <tr><th>Código</th><th>Material</th><th class="text-right">Cant.</th><th class="text-right">P. Unit.</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
                <?php $totalMat = 0; foreach ($materiales as $m):
                    $subtotal = (float)$m['cantidad'] * (float)$m['precio_unitario'];
                    $totalMat += $subtotal; ?>
                <tr>
                    <td><?= htmlspecialchars($m['codigo']) ?></td>
                    <td><?= htmlspecialchars($m['nombre']) ?></td>
                    <td class="text-right"><?= (int)$m['cantidad'] ?></td>
                    <td class="text-right">$<?= number_format((float)$m['precio_unitario'], 2) ?></td>
                    <td class="text-right">$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="totales">
            <?php if (!empty($materiales)): ?>
            <div class="total-row"><span>Total Materiales</span><span>$<?= number_format($totalMat, 2) ?></span></div>
            <?php endif; ?>
            <div class="total-row"><span>Costo de Servicio</span><span>$<?= number_format((float)$f['costoServicio'], 2) ?></span></div>
            <div class="total-row total-final"><span>TOTAL FACTURA</span><span>$<?= number_format((float)$f['totalFactura'], 2) ?></span></div>
        </div>

        <?php if (!empty($pagos)): ?>
        <div class="pagos-table">
            <h3>Abonos Registrados</h3>
            <table>
                <thead><tr><th>Fecha</th><th class="text-right">Monto $</th><th>Método</th></tr></thead>
                <tbody>
                    <?php $totalP = 0; foreach ($pagos as $p):
                        $totalP += (float)$p['monto']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                        <td class="text-right">$<?= number_format((float)$p['monto'], 2) ?></td>
                        <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total-row" style="margin-top:8px;padding-top:8px;border-top:2px solid #ddd;">
                <span><strong>Total Abonado</strong></span>
                <span><strong>$<?= number_format($totalP, 2) ?></strong></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (abs($saldoPendiente) > 0.01): ?>
        <div class="total-row" style="margin-top:15px;padding:10px;background:#fff3cd;border-radius:6px;">
            <span><strong>Saldo Pendiente</strong></span>
            <span><strong>$<?= number_format($saldoPendiente, 2) ?></strong></span>
        </div>
        <?php endif; ?>

        <?php if ($f['notasCuota']): ?>
        <div style="margin-top:15px;font-size:0.85rem;color:#666;border-top:1px dashed #ddd;padding-top:12px;">
            <strong>Nota:</strong> <?= htmlspecialchars($f['notasCuota']) ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Betel Creativa — Decoración de Eventos</p>
            <p>Este documento es un comprobante de pago.</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-fa-kit.js" crossorigin="anonymous"></script>
</body>
</html>
