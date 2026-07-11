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
use BetelCreativa\Helpers\FacturaCalculadora;

$db = Database::getConnection();

$stmt = $db->prepare(
    "SELECT f.id AS facturaId, f.costo_servicio AS costoServicio, f.total_factura AS totalFactura,
            f.notas_cuota AS notasCuota, f.estado, f.created_at AS createdAt,
            f.created_by_name AS createdByName, f.descripcion_servicio AS descripcionServicio,
            f.plan_tipo AS planTipo, f.plan_cuotas_total AS planCuotasTotal,
            f.tipo, f.factura_origen_id,
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
if ($f['estado'] === 'anulada') { echo '<p style="text-align:center;margin-top:50px;color:red;">Esta factura fue anulada.</p>'; exit; }

$esRecibo = ($f['tipo'] ?? 'factura') === 'recibo';
$parentFacturaId = $esRecibo ? (int)($f['factura_origen_id'] ?? 0) : $facturaId;

// For recibos, get the parent factura total for Estado de Cuenta
$totalContrato = (float)$f['totalFactura'];
if ($esRecibo && $parentFacturaId) {
    $pStmt = $db->prepare("SELECT total_factura, descripcion_servicio, created_at FROM facturas WHERE id = :id");
    $pStmt->execute([':id' => $parentFacturaId]);
    $parent = $pStmt->fetch(PDO::FETCH_ASSOC);
    if ($parent) {
        $totalContrato = (float)$parent['total_factura'];
        $parentFecha = $parent['created_at'];
    }
}

$conceptoPago = ($f['descripcionServicio'] ?? '') ?: ($esRecibo ? 'Abono registrado' : 'Servicio de decoración');

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
    "SELECT p.monto, p.metodo_pago, p.tasa_usada, p.Ref_PagoMovil AS refPagoMovil, p.fecha
     FROM pagos_factura p
     LEFT JOIN facturas r ON p.factura_id = r.id
     WHERE p.factura_id = :id1 OR r.factura_origen_id = :id2
     ORDER BY p.fecha ASC"
);
$stmtPagos->execute([':id1' => $facturaId, ':id2' => $facturaId]);
$pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

$totales = FacturaCalculadora::calcularTotales((float)$f['costoServicio'], $materiales);
$totalMateriales = $totales['totalMateriales'];
$ivaAmount = $totales['iva'];
$totalConIva = $totales['total'];

$totalPagadoVes = 0;
foreach ($pagos as $p) $totalPagadoVes += (float)$p['monto'] * (float)$p['tasa_usada'];
$saldoPendienteVes = $totalConIva - $totalPagadoVes;
$cambio = $totalPagadoVes > $totalConIva ? $totalPagadoVes - $totalConIva : 0;

// For Estado de Cuenta: total abonado across all facturas/recibos of this cita
$totalAbonadoHistory = $totalPagadoVes;
if ($esRecibo) {
    $taStmt = $db->prepare(
        "SELECT COALESCE(SUM(p.monto * p.tasa_usada), 0)
         FROM pagos_factura p
         JOIN facturas r ON p.factura_id = r.id
         WHERE r.cita_id = (SELECT cita_id FROM facturas WHERE id = :id)"
    );
    $taStmt->execute([':id' => $facturaId]);
    $totalAbonadoHistory = (float)$taStmt->fetchColumn();
}

$esCopia = !empty($_GET['copia']);

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
    <link rel="icon" href="<?php echo APP_URL; ?>Public/images/BetEl.png">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',Courier,monospace; background:#fff; padding:30px; color:#000; font-size:12px; line-height:1.5; }
        .recibo { max-width:680px; margin:0 auto; }
        .hdr { display:flex; align-items:center; justify-content:center; gap:24px; margin-bottom:14px; }
        .logo-recibo { max-width:110px; height:auto; }
        .hdr-right { text-align:center; }
        .hdr .t { font-weight:800; font-size:20px; margin-bottom:10px; }
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
        .copia-watermark { text-align:center; margin-top:20px; font-size:28px; font-weight:700; color:#dc3545; border:3px solid #dc3545; padding:10px 20px; display:inline-block; letter-spacing:8px; opacity:0.7; }
        .copia-wrapper { text-align:center; }
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
            <img src="<?php echo (defined('APP_URL') ? APP_URL : '../') . 'Public/images/logoBN.png'; ?>" alt="Bet-El Creativa" class="logo-recibo">
            <div class="hdr-right">
                <div class="t"><?= $esRecibo ? 'Recibo de Abono' : 'Factura' ?></div>
                <div style="font-size:11px;line-height:1.6;">
                    <strong>Negocio BetEl-Creativa 2020</strong><br>
                    V-173970451<br>
                    Avenida 86 Porto Carrero, Casa NRO 175<br>
                    Valencia, Carabobo Zona postal 2001.
                </div>
            </div>
        </div>
        <hr class="sep">

        <?php if ($esRecibo && $parentFacturaId): $parentFechaStr = isset($parentFecha) ? date('d/m/Y', strtotime($parentFecha)) : '—'; ?>
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;"><span style="font-weight:600;">Nro Recibo: <?= str_pad($parentFacturaId, 8, '0', STR_PAD_LEFT) ?></span><span style="font-weight:600;">Fecha: <?= $parentFechaStr ?></span></div>
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;"><span style="font-weight:600;">Nro Abono: <?= str_pad($f['facturaId'], 8, '0', STR_PAD_LEFT) ?></span><span style="font-weight:600;">Fecha: <?= date('d/m/Y', strtotime($f['createdAt'])) ?></span></div>
        <?php else: ?>
        <div><?= line('Nro:', str_pad($f['facturaId'], 8, '0', STR_PAD_LEFT)) ?></div>
        <div><?= line('Fecha:', date('d/m/Y', strtotime($f['createdAt']))) ?></div>
        <?php endif; ?>
        <div><?= line('Atendido por:', htmlspecialchars($f['createdByName'] ?? '—')) ?></div>
        <div><?= line('Cliente:', htmlspecialchars($f['clienteNombre'])) ?></div>
        <div><?= line('C.I:', htmlspecialchars($f['clienteCedula'] ?? '—')) ?></div>
        <div><?= line('Teléfono:', htmlspecialchars($f['clienteTelefono'] ?? '—')) ?></div>
        <div><?= line('Fecha Evento:', date('d/m/Y', strtotime($f['fechaCita']))) ?></div>
        <div><?= line('Ubicación:', htmlspecialchars($f['ubicacion'] ?? '—')) ?></div>
        <div><?= line('Evento:', htmlspecialchars($f['eventType'])) ?></div>
        <hr class="sep">

        <?php if ($esRecibo): ?>
        <div class="st">CONCEPTO DE PAGO:</div>
        <div style="margin:2px 0 6px;"><?= htmlspecialchars($conceptoPago) ?></div>
        <hr class="sep">
        <?php else: ?>
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
           
            <div style="margin-top:2px;">Cantidad de Materiales: <?= count($materiales) ?> 
            </div>
        <?php else: ?>
            <div>Sin materiales asignados</div>
        <?php endif; ?>
        <hr class="sep">
        <?php endif; ?>

        <?php if (!$esRecibo): ?>
        <div class="st"></div>
        
        <?= line('Sub-Total:', fmt($totalMateriales) . ' Bs') ?>
        <?= line('Mano de obra (' . htmlspecialchars($f['descripcionServicio'] ?? 'Servicio') . '):', fmt((float)$f['costoServicio']) . ' Bs') ?>
        <?= line('I.V.A (' . (IVA_RATE * 100) . '%):', fmt($ivaAmount) . ' Bs') ?>
        <hr class="sep">
        <div class="l" style="font-weight:700;font-size:13px;"><span>TOTAL:</span><span><?= fmt($totalConIva) ?> Bs</span></div>
        <hr class="sep">
        <?php endif; ?>

        <div class="st"><?= $esRecibo ? 'DETALLE DEL PAGO' : 'FORMAS DE PAGO' ?>:</div>
        <?php if (!empty($pagos)): ?>
            <?php foreach ($pagos as $p):
                $monto = (float)$p['monto'];
                $tasa = (float)$p['tasa_usada'];
                $montoVes = $monto * $tasa;
                $label = match ($p['metodo_pago']) {
                    'efectivo' => 'Efectivo',
                    'pagomovil' => 'Pago Móvil',
                    'divisas' => 'Divisas',
                    default => htmlspecialchars($p['metodo_pago'])
                };
                ?>
            <div>
                <?php if ($p['metodo_pago'] === 'divisas'): ?>
                    <?= line($label . ':  $' . fmt($monto), fmt($montoVes) . ' Bs') ?>
                    <?= line('Tasa BCV:', fmt($tasa) . ' Bs/$') ?>
                <?php else: ?>
                    <?php $lineLabel = $label . ':'; ?>
                    <?php if ($p['metodo_pago'] === 'pagomovil' && !empty($p['refPagoMovil'])): ?>
                        <?= line($lineLabel, fmt($montoVes) . ' Bs') ?>
                        <?= line('Ref:', htmlspecialchars($p['refPagoMovil'])) ?>
                    <?php else: ?>
                        <?= line($lineLabel, fmt($montoVes) . ' Bs') ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <hr class="sep">
            <?php if ($esRecibo): ?>
            <div class="l" style="font-weight:700;font-size:13px;"><span>MONTO RECIBIDO:</span><span><?= fmt($totalPagadoVes) ?> Bs</span></div>
            <?php else: ?>
            <?= line('Total Pagado:', fmt($totalPagadoVes) . ' Bs') ?>
            <?php if ($cambio > 0): ?>
                <?= line('Devolución:', fmt($cambio) . ' Bs') ?>
            <?php endif; ?>
            <?php if ($saldoPendienteVes > 0.01): ?>
                <?= line('Saldo Pendiente:', fmt($saldoPendienteVes) . ' Bs') ?>
            <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div>Sin pagos registrados</div>
        <?php endif; ?>
        <hr class="sep">

        <?php if ($esRecibo): ?>
        <div class="st">ESTADO DE CUENTA:</div>
        <div><?= line('Total Contrato:', fmt($totalContrato) . ' Bs') ?></div>
        <div><?= line('Total Abonado:', fmt($totalAbonadoHistory) . ' Bs') ?></div>
        <?php $saldoFinal = $totalContrato - $totalAbonadoHistory; ?>
        <div class="l" style="font-weight:700;font-size:13px;margin-top:4px;padding-top:4px;border-top:2px solid #000;">
            <span>SALDO PENDIENTE:</span>
            <span><?= $saldoFinal > 0.01 ? fmt($saldoFinal) . ' Bs' : 'PAGADO / SOLVENTE' ?></span>
        </div>
        <hr class="sep">
        <?php endif; ?>

        <?php if ($f['notasCuota']): ?>
        <div style="margin-top:8px;font-size:11px;">Nota: <?= htmlspecialchars($f['notasCuota']) ?></div>
        <?php endif; ?>

        <?php
        $termStmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'terminos_condiciones'");
        $termStmt->execute();
        $terminosRaw = $termStmt->fetchColumn();
        $terminos = $terminosRaw ? array_filter(array_map('trim', explode("\n", $terminosRaw))) : [];
        if (!empty($terminos)):
        ?>
        <hr class="sep">
        <div class="st">TÉRMINOS Y CONDICIONES:</div>
        <ul style="margin:4px 0 0 16px;padding:0;font-size:10px;line-height:1.5;">
            <?php foreach ($terminos as $t): ?>
            <li><?= htmlspecialchars($t) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($esCopia): ?>
        <div class="copia-wrapper">
            <div class="copia-watermark">C O P I A</div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Betel Creativa — Decoración de Eventos</p>
            <p>Este documento es un comprobante de pago.</p>
            <?php if ($esRecibo): ?>
            <p style="margin-top:6px;font-size:9px;color:#888;">Este recibo avala únicamente el monto reflejado. Los abonos no son reembolsables en caso de cancelación del evento.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
