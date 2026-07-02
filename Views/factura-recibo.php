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

$db = Database::getConnection();

$stmt = $db->prepare(
    "SELECT f.id AS facturaId, f.costo_servicio AS costoServicio, f.total_factura AS totalFactura,
            f.notas_cuota AS notasCuota, f.estado, f.created_at AS createdAt,
            f.created_by_name AS createdByName, f.descripcion_servicio AS descripcionServicio,
            f.plan_tipo AS planTipo, f.plan_cuotas_total AS planCuotasTotal,
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

$IVA_RATE = 0.16;
$totalMateriales = 0;
foreach ($materiales as $m) $totalMateriales += (float)$m['cantidad'] * (float)$m['precio_unitario'];
$subtotal = $totalMateriales + (float)$f['costoServicio'];
$ivaAmount = $subtotal * $IVA_RATE;
$totalConIva = $subtotal + $ivaAmount;
$totalFacturaAlmacenado = (float)$f['totalFactura'];

$totalPagadoVes = 0;
foreach ($pagos as $p) $totalPagadoVes += (float)$p['monto'] * (float)$p['tasa_usada'];
$saldoPendienteVes = $totalConIva - $totalPagadoVes;
$cambio = $totalPagadoVes > $totalConIva ? $totalPagadoVes - $totalConIva : 0;

function fmt($v) { return number_format($v, 2, ',', '.'); }
function line($l, $r) {
    $pad = 76 - mb_strlen($l) - mb_strlen($r);
    $pad = max(1, $pad);
    return '<div class="l"><span class="ll">' . $l . '</span>' . str_repeat(' ', $pad) . '<span class="lr">' . $r . '</span></div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?= str_pad($f['facturaId'], 8, '0', STR_PAD_LEFT) ?> — Bet-El Creativa</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',Courier,monospace; background:#fff; padding:30px; color:#000; font-size:12px; line-height:1.5; }
        .recibo { max-width:680px; margin:0 auto; }
        .hdr { text-align:center; margin-bottom:14px; }
        .hdr .e { font-size:16px; font-weight:700; letter-spacing:1px; }
        .hdr .r { font-size:13px; }
        .hdr .d { font-size:11px; line-height:1.3; }
        .hdr .t { margin-top:5px; border-top:2px solid #000; padding-top:5px; font-weight:700; font-size:13px; }
        .sep { border:none; border-top:2px solid #000; margin:8px 0; }
        .l { display:flex; justify-content:space-between; width:100%; padding:1px 0; }
        .ll { white-space:nowrap; }
        .lr { white-space:nowrap; text-align:right; }
        .st { font-weight:700; font-size:13px; margin:8px 0 4px; }
        .mats { margin:4px 0; }
        .mats .m { font-weight:600; }
        .mats .v { font-size:11px; color:#333; display:flex; justify-content:space-between; }
        .footer { text-align:center; margin-top:18px; padding-top:8px; font-size:10px; color:#555; }
        .no-print { margin-bottom:14px; text-align:center; }
        .no-print button { padding:8px 20px; background:#000; color:#fff; border:none; font-family:inherit; font-size:12px; cursor:pointer; margin:0 4px; }
        .no-print button:hover { background:#333; }
        .cambio-line { border-top:2px solid #000; margin-top:4px; padding-top:4px; font-weight:700; font-size:13px; }
        @media print { .no-print { display:none; } body { padding:15px; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir Recibo</button>
        <button onclick="window.close()" style="background:#666;">Cerrar</button>
    </div>

    <div class="recibo">

        <div class="hdr">
            <div class="e">Negocio BetEl-Creativa 2020</div>
            <div class="r">V-173970451</div>
            <div class="d">Avenida 86 Porto Carrero, Casa NRO 175</div>
            <div class="d">Valencia, Carabobo Zona postal 2001</div>
            <div class="t">RECIBO DE PAGO</div>
        </div>
        <hr class="sep">

        <div><?= line('Nro:', str_pad($f['facturaId'], 8, '0', STR_PAD_LEFT)) ?></div>
        <div><?= line('Fecha:', date('d/m/Y', strtotime($f['createdAt']))) ?></div>
        <div><?= line('Atendido por:', htmlspecialchars($f['createdByName'] ?? '—')) ?></div>
        <div><?= line('Cliente:', htmlspecialchars($f['clienteNombre'])) ?></div>
        <div><?= line('Fecha Evento:', date('d/m/Y', strtotime($f['fechaCita']))) ?></div>
        <div><?= line('Tipo Evento:', htmlspecialchars($f['eventType'])) ?></div>
        <hr class="sep">

        <div class="st">MATERIALES:</div>
        <?php if (!empty($materiales)): ?>
            <?php foreach ($materiales as $m):
                $precio = (float)$m['precio_unitario'];
                $cant = (int)$m['cantidad'];
                $total = $precio * $cant;
                ?>
            <div class="mats">
                <div class="m">[<?= $cant ?>] X <?= htmlspecialchars($m['nombre']) ?></div>
                <div class="v"><span>Valor Original: <?= fmt($precio) ?> Bs</span><span><?= fmt($total) ?> Bs</span></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:2px;">Cantidad Total de Materiales: <?= count($materiales) ?></div>
        <?php else: ?>
            <div>Sin materiales asignados</div>
        <?php endif; ?>
        <hr class="sep">

        <div class="st">TOTALES:</div>
        <?= line('TOTAL:', fmt($totalMateriales) . ' Bs') ?>
        <?= line('Sub-Total:', fmt($totalMateriales) . ' Bs') ?>
        <?= line('Mano de obra (' . htmlspecialchars($f['descripcionServicio'] ?? 'Servicio') . '):', fmt((float)$f['costoServicio']) . ' Bs') ?>
        <?= line('I.V.A (' . ($IVA_RATE * 100) . '%):', fmt($ivaAmount) . ' Bs') ?>
        <hr class="sep">
        <div class="l" style="font-weight:700;font-size:13px;"><span>TOTAL:</span><span><?= fmt($totalConIva) ?> Bs</span></div>
        <hr class="sep">

        <div class="st">FORMAS DE PAGO:</div>
        <?php if (!empty($pagos)): ?>
            <?php foreach ($pagos as $p):
                $monto = (float)$p['monto'];
                $tasa = (float)$p['tasa_usada'];
                $montoVes = $monto * $tasa;
                $label = match ($p['metodo_pago']) {
                    'efectivo' => 'Efectivo',
                    'pagomovil' => 'PagoMóvil',
                    'divisas' => 'Divisas',
                    default => htmlspecialchars($p['metodo_pago'])
                };
                ?>
            <div>
                <?php if ($p['metodo_pago'] === 'divisas'): ?>
                    <?= line($label . ':  $' . fmt($monto), fmt($montoVes) . ' Bs') ?>
                <?php else: ?>
                    <?= line($label . ':', fmt($montoVes) . ' Bs') ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <hr class="sep">
            <?= line('Total Pagado:', fmt($totalPagadoVes) . ' Bs') ?>
            <?php if ($cambio > 0): ?>
                <?= line('Cambio:', fmt($cambio) . ' Bs') ?>
            <?php endif; ?>
            <?php if ($saldoPendienteVes > 0.01): ?>
                <?= line('Saldo Pendiente:', fmt($saldoPendienteVes) . ' Bs') ?>
            <?php endif; ?>
        <?php else: ?>
            <div>Sin pagos registrados</div>
        <?php endif; ?>
        <hr class="sep">

        <?php if ($f['notasCuota']): ?>
        <div style="margin-top:8px;font-size:11px;">Nota: <?= htmlspecialchars($f['notasCuota']) ?></div>
        <?php endif; ?>

        <div class="footer">
            <p>Betel Creativa — Decoración de Eventos</p>
            <p>Este documento es un comprobante de pago.</p>
        </div>
    </div>
</body>
</html>
