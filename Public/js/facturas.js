let TASA_BCV = 36.50;
let facturaId = null;
let facturaEstado = null;

document.addEventListener('DOMContentLoaded', function () {
    cargarCitas();
    document.getElementById('selectorCitas')?.addEventListener('change', function (e) {
        const citaId = e.target.value;
        if (citaId) {
            cargarDetalleCita(citaId);
        } else {
            document.getElementById('facturaPanel').style.display = 'none';
        }
    });

    document.getElementById('btnGenerarFactura')?.addEventListener('click', function () {
        const citaId = document.getElementById('selectorCitas').value;
        if (!citaId) return;
        document.getElementById('genFacturaCitaId').value = citaId;
        document.getElementById('generarFacturaModal').style.display = 'flex';
    });

    document.getElementById('cancelGenFactura')?.addEventListener('click', function () {
        document.getElementById('generarFacturaModal').style.display = 'none';
    });
    document.getElementById('cancelPago')?.addEventListener('click', function () {
        document.getElementById('pagoModal').style.display = 'none';
    });

    document.getElementById('generarFacturaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const citaId = parseInt(document.getElementById('genFacturaCitaId').value);
        const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
        const notasCuota = document.getElementById('genNotasCuota').value.trim();
        if (costoServicio < 0) return toast('El costo de servicio no puede ser negativo.', 'warning');
        setLoading('btnGuardarFactura', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'crear', cita_id: citaId, costo_servicio: costoServicio, notas_cuota: notasCuota })
            });
            if (res.success) {
                toast('Factura creada exitosamente.', 'success');
                document.getElementById('generarFacturaModal').style.display = 'none';
                cargarDetalleCita(citaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnGuardarFactura', false);
        }
    });

    // Listener estático del botón de abono en Tarjeta 3
    document.getElementById('btnRegistrarAbonoPagos')?.addEventListener('click', function () {
        abrirModalPago();
    });

    // Conversor bilateral USD ↔ VES
    document.getElementById('pagoMontoUsd')?.addEventListener('input', function (e) {
        const usd = parseFloat(e.target.value);
        if (!isNaN(usd) && usd > 0 && e.target.value !== '') {
            document.getElementById('pagoMontoVes').value = (usd * TASA_BCV).toFixed(2);
        } else if (e.target.value === '') {
            document.getElementById('pagoMontoVes').value = '';
        }
    });

    document.getElementById('pagoMontoVes')?.addEventListener('input', function (e) {
        const ves = parseFloat(e.target.value);
        if (!isNaN(ves) && ves > 0 && e.target.value !== '') {
            document.getElementById('pagoMontoUsd').value = (ves / TASA_BCV).toFixed(2);
        } else if (e.target.value === '') {
            document.getElementById('pagoMontoUsd').value = '';
        }
    });

    document.getElementById('pagoForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const facturaIdVal = parseInt(document.getElementById('pagoFacturaId').value);
        const montoUsd = parseFloat(document.getElementById('pagoMontoUsd').value);
        const metodoPago = document.getElementById('pagoMetodo').value;
        const tasaUsada = parseFloat(document.getElementById('pagoTasa').value);
        if (!montoUsd || montoUsd <= 0) return toast('Ingrese un monto válido.', 'warning');
        setLoading('btnGuardarPago', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'pagar', factura_id: facturaIdVal, monto: montoUsd, metodo_pago: metodoPago, tasa_usada: tasaUsada })
            });
            if (res.success) {
                toast('Pago registrado exitosamente.', 'success');
                document.getElementById('pagoModal').style.display = 'none';
                const citaId = document.getElementById('selectorCitas').value;
                if (citaId) cargarDetalleCita(citaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnGuardarPago', false);
        }
    });

    // Cerrar factura: abrir modal de confirmación
    document.getElementById('btnCerrarFactura')?.addEventListener('click', function () {
        if (!facturaId) return;
        document.getElementById('cerrarFacturaModal').style.display = 'flex';
    });

    // Confirmar cierre de factura
    document.getElementById('btnConfirmarCerrar')?.addEventListener('click', async function () {
        if (!facturaId) return;
        setLoading('btnConfirmarCerrar', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'cerrar', factura_id: facturaId })
            });
            if (res.success) {
                toast('Factura cerrada exitosamente.', 'success');
                document.getElementById('cerrarFacturaModal').style.display = 'none';
                const citaId = document.getElementById('selectorCitas').value;
                if (citaId) cargarDetalleCita(citaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnConfirmarCerrar', false);
        }
    });

    document.getElementById('cancelCerrarFactura')?.addEventListener('click', function () {
        document.getElementById('cerrarFacturaModal').style.display = 'none';
    });

    // Anular factura (confirmación directa)
    document.getElementById('btnAnularFactura')?.addEventListener('click', async function () {
        if (!facturaId) return;
        if (!confirm('¿Está seguro de anular esta factura? Esta acción no se puede deshacer.')) return;
        setLoading('btnAnularFactura', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'anular', factura_id: facturaId })
            });
            if (res.success) {
                toast('Factura anulada exitosamente.', 'success');
                const citaId = document.getElementById('selectorCitas').value;
                if (citaId) cargarDetalleCita(citaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnAnularFactura', false);
        }
    });

    // Cerrar modales con X
    document.querySelectorAll('.close-modal').forEach(el => {
        el.addEventListener('click', function () {
            this.closest('.modal').style.display = 'none';
        });
    });
});

function abrirModalPago() {
    if (!facturaId) return toast('Debe generar la factura primero.', 'warning');
    document.getElementById('pagoFacturaId').value = facturaId;
    document.getElementById('pagoMontoUsd').value = '';
    document.getElementById('pagoMontoVes').value = '';
    document.getElementById('pagoTasa').value = TASA_BCV.toFixed(2);
    document.getElementById('pagoModal').style.display = 'flex';
}

async function cargarCitas() {
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php');
        const sel = document.getElementById('selectorCitas');
        if (res.success && Array.isArray(res.data)) {
            sel.innerHTML = '<option value="">— Seleccione una cita —</option>';
            res.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                const fecha = c.fechaHoraInicio ? c.fechaHoraInicio.slice(0, 10) : '—';
                opt.textContent = '#' + c.id + ' - ' + fecha + ' - ' + (c.ubicacion || 'Sin ubicación');
                sel.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error al cargar citas:', err);
    }
}

async function cargarDetalleCita(citaId) {
    try {
        const res = await callApi(APP_URL + 'Public/api/facturas.php?action=detalle&cita_id=' + citaId);
        if (!res.success || !res.data) {
            toast('Error al cargar detalle.', 'error');
            return;
        }
        const d = res.data;
        TASA_BCV = d.tasaBcv || 36.50;
        facturaId = d.general?.facturaId || null;
        facturaEstado = d.general?.facturaEstado || null;

        document.getElementById('facturaPanel').style.display = 'block';

        // Badge de estado de la factura
        const badge = document.getElementById('facturaEstadoBadge');
        if (facturaId && facturaEstado && badge) {
            badge.style.display = 'inline-block';
            badge.textContent = facturaEstado.charAt(0).toUpperCase() + facturaEstado.slice(1);
            badge.className = 'estado-badge estado-' + facturaEstado;
        } else if (badge) {
            badge.style.display = 'none';
        }

        // Tarjeta 1: Info cita
        const g = d.general || {};
        document.getElementById('card1-cliente').textContent = g.clienteNombre || '—';
        document.getElementById('card1-cedula').textContent = g.clienteCedula || '—';
        document.getElementById('card1-telefono').textContent = g.clienteTelefono || '—';
        document.getElementById('card1-fecha').textContent = g.fechaHoraInicio ? g.fechaHoraInicio.replace('T', ' ') : '—';
        document.getElementById('card1-ubicacion').textContent = g.ubicacion || '—';
        document.getElementById('card1-evento').textContent = g.eventType || '—';
        document.getElementById('card1-estado').textContent = g.estado || '—';

        // Tarjeta 2: Materiales
        const mats = d.materiales || [];
        let html = '';
        let totalMat = 0;
        mats.forEach(m => {
            const subtotal = (parseFloat(m.cantidad) || 0) * (parseFloat(m.precioUnitario) || 0);
            totalMat += subtotal;
            html += '<tr>' +
                '<td>' + (m.codigo || '—') + '</td>' +
                '<td>' + (m.nombre || '—') + '</td>' +
                '<td class="text-right">' + (m.cantidad || 0) + '</td>' +
                '<td class="text-right">$' + (parseFloat(m.precioUnitario) || 0).toFixed(2) + '</td>' +
                '<td class="text-right">$' + subtotal.toFixed(2) + '</td>' +
                '</tr>';
        });
        if (!mats.length) {
            html = '<tr><td colspan="5" class="text-center" style="color:var(--gray);padding:20px;">Sin materiales asignados</td></tr>';
        }
        document.getElementById('tabla-materiales').innerHTML = html;
        const costoServicio = parseFloat(g.costoServicio) || 0;
        const totalFactura = parseFloat(g.totalFactura) || (costoServicio + totalMat);
        document.getElementById('card2-costo-servicio').textContent = '$' + costoServicio.toFixed(2);
        document.getElementById('card2-total-materiales').textContent = '$' + totalMat.toFixed(2);
        document.getElementById('card2-total-valor').innerHTML = '<strong>$' + totalFactura.toFixed(2) + '</strong>';

        // Botones Tarjeta 2
        const actionsDiv = document.getElementById('facturaActions');
        if (facturaId) {
            let actionsHtml = '';
            if (facturaEstado === 'activa') {
                actionsHtml += '<button type="button" class="btn btn-primary" id="btnRegistrarAbonoCard2" style="flex:1;"><i class="fas fa-plus-circle"></i> Registrar Abono</button>';
            }
            actionsHtml += '<button type="button" class="btn btn-outline" id="btnImprimir" style="flex:1;"><i class="fas fa-print"></i> Imprimir Recibo</button>';
            actionsDiv.innerHTML = actionsHtml;

            document.getElementById('btnRegistrarAbonoCard2')?.addEventListener('click', function () {
                abrirModalPago();
            });
            document.getElementById('btnImprimir')?.addEventListener('click', function () {
                window.open(APP_URL + 'factura-recibo?id=' + facturaId, '_blank');
            });
        } else {
            actionsDiv.innerHTML = '<button type="button" class="btn btn-primary" id="btnGenerarFactura" style="flex:1;"><i class="fas fa-file-invoice"></i> Generar Factura</button>';
            document.getElementById('btnGenerarFactura').addEventListener('click', function () {
                document.getElementById('genFacturaCitaId').value = citaId;
                document.getElementById('generarFacturaModal').style.display = 'flex';
            });
        }

        // Tarjeta 3: Pagos
        const pagos = d.pagos || [];
        let pagosHtml = '';
        let totalPagadoUsd = 0;
        pagos.forEach(p => {
            const monto = parseFloat(p.monto) || 0;
            totalPagadoUsd += monto;
            const tasa = parseFloat(p.tasaUsada) || 0;
            pagosHtml += '<tr>' +
                '<td>' + (p.fecha ? p.fecha.slice(0, 10) : '—') + '</td>' +
                '<td class="text-right">$' + monto.toFixed(2) + '</td>' +
                '<td>' + (p.metodoPago || '—') + '</td>' +
                '<td class="text-right">' + tasa.toFixed(2) + '</td>' +
                '</tr>';
        });
        if (!pagos.length) {
            pagosHtml = '<tr><td colspan="4" class="text-center" style="color:var(--gray);padding:15px;">Sin pagos registrados</td></tr>';
        }
        document.getElementById('tabla-pagos').innerHTML = pagosHtml;

        const saldoPendiente = totalFactura - totalPagadoUsd;
        const totalVes = totalFactura * TASA_BCV;
        const pendienteVes = saldoPendiente * TASA_BCV;

        document.getElementById('card3-total-usd').textContent = '$' + totalFactura.toFixed(2);
        document.getElementById('card3-total-ves').textContent = totalVes.toFixed(2) + ' Bs';
        document.getElementById('card3-pendiente-usd').textContent = '$' + saldoPendiente.toFixed(2);
        document.getElementById('card3-pendiente-ves').textContent = pendienteVes.toFixed(2) + ' Bs';

        const statusEl = document.getElementById('status-pago');
        if (!facturaId || totalFactura <= 0) {
            statusEl.textContent = 'FACTURA POR GENERAR';
            statusEl.className = 'pago-status';
        } else if (saldoPendiente <= 0.01) {
            statusEl.textContent = '✓ FACTURA TOTALMENTE PAGADA';
            statusEl.className = 'pago-status pagada';
        } else {
            statusEl.textContent = 'PAGO POR CUOTAS — Pendiente: $' + saldoPendiente.toFixed(2);
            statusEl.className = 'pago-status pendiente-pago';
        }

        // Tarjeta 3: Botones de abono, cerrar, anular
        const btnAbono = document.getElementById('btnRegistrarAbonoPagos');
        const btnCerrar = document.getElementById('btnCerrarFactura');
        const btnAnular = document.getElementById('btnAnularFactura');

        if (facturaId && facturaEstado === 'activa') {
            if (btnAbono) { btnAbono.disabled = false; btnAbono.style.display = ''; }
            if (btnCerrar) { btnCerrar.disabled = false; btnCerrar.style.display = ''; }
            if (btnAnular) { btnAnular.disabled = false; btnAnular.style.display = ''; }
        } else {
            if (btnAbono) { btnAbono.disabled = true; btnAbono.style.display = facturaId ? '' : 'none'; }
            if (btnCerrar) { btnCerrar.disabled = true; btnCerrar.style.display = 'none'; }
            if (btnAnular) { btnAnular.disabled = true; btnAnular.style.display = 'none'; }
        }

    } catch (err) {
        console.error('Error al cargar detalle:', err);
        toast('Error al cargar detalle de facturación.', 'error');
    }
}

function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.classList.toggle('btn-loading', loading);
}
