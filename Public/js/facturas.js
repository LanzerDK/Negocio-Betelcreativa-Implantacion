let TASA_BCV = APP_CONFIG?.bcvRate || 0;
let facturaId = null;
let facturaEstado = null;
let facturaCitaId = null;
let currentTab = 'abiertas';
let materialesCache = [];
let allFacturas = [];
let selectedFacturaId = null;

document.addEventListener('DOMContentLoaded', function () {

    cargarTerminos();

    // ── Sidebar Tabs ─────────────────────────────────────
    document.querySelectorAll('.sidebar-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentTab = this.dataset.tab;
            selectedFacturaId = null;
            ocultarDetalle();
            cargarFacturas(currentTab);
        });
    });

    // ── Sidebar Search ───────────────────────────────────
    document.getElementById('sidebarSearch')?.addEventListener('input', function () {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => filtrarFacturas(this.value), 300);
    });

    // ── Abrir modal generar factura ──────────────────────
    document.getElementById('btnGenerarFactura')?.addEventListener('click', function () {
        if (!facturaCitaId) return toast('Seleccione una factura primero.', 'warning');
        document.getElementById('genFacturaCitaId').value = facturaCitaId;
        document.getElementById('genCostoServicio').value = '';
        document.getElementById('genDescripcionServicio').value = '';
        document.getElementById('genNotasCuota').value = '';
        document.querySelector('input[name="planTipo"][value="contado"]').checked = true;
        document.getElementById('cuotasSection').style.display = 'none';
        document.getElementById('contadoPreview').style.display = 'block';
        document.getElementById('genMetodoPago').value = 'efectivo';
        document.getElementById('genDivisaSection').style.display = 'none';
        document.getElementById('genMontoUsd').value = '';
        document.getElementById('genTasaBcv').textContent = TASA_BCV.toFixed(2);
        actualizarPreviews(facturaCitaId);
        document.getElementById('generarFacturaModal').style.display = 'flex';
    });

    document.getElementById('cancelGenFactura')?.addEventListener('click', function () {
        document.getElementById('generarFacturaModal').style.display = 'none';
    });
    document.getElementById('cancelPago')?.addEventListener('click', function () {
        document.getElementById('pagoModal').style.display = 'none';
    });

    // ── Radio planTipo toggle ────────────────────────────
    document.querySelectorAll('input[name="planTipo"]').forEach(el => {
        el.addEventListener('change', function () {
            const isCuotas = this.value === 'cuotas';
            document.getElementById('cuotasSection').style.display = isCuotas ? 'block' : 'none';
            document.getElementById('contadoPreview').style.display = isCuotas ? 'none' : 'block';
            const citaId = document.getElementById('genFacturaCitaId').value;
            if (citaId) actualizarPreviews(citaId);
        });
    });

    document.getElementById('genMetodoPago')?.addEventListener('change', function () {
        document.getElementById('genDivisaSection').style.display = this.value === 'divisas' ? 'block' : 'none';
    });

    document.getElementById('genCostoServicio')?.addEventListener('input', function () {
        const citaId = document.getElementById('genFacturaCitaId').value;
        if (citaId) actualizarPreviews(citaId);
    });

    document.getElementById('genNumCuotas')?.addEventListener('change', function () {
        const citaId = document.getElementById('genFacturaCitaId').value;
        if (citaId) actualizarPreviews(citaId);
    });

    // ── Submit generar factura ───────────────────────────
    document.getElementById('generarFacturaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const citaId = parseInt(document.getElementById('genFacturaCitaId').value);
        const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
        const notasCuota = document.getElementById('genNotasCuota').value.trim();
        const descripcionServicio = document.getElementById('genDescripcionServicio').value.trim();
        const planTipo = document.querySelector('input[name="planTipo"]:checked').value;
        const planCuotasTotal = planTipo === 'cuotas' ? parseInt(document.getElementById('genNumCuotas').value) : null;

        const metodoPago = planTipo === 'contado' ? document.getElementById('genMetodoPago').value : null;
        const tasaUsada = planTipo === 'contado' ? TASA_BCV : null;
        const montoUsd = (planTipo === 'contado' && metodoPago === 'divisas') ? parseFloat(document.getElementById('genMontoUsd').value) || 0 : null;

        if (costoServicio < 0) return toast('El costo de servicio no puede ser negativo.', 'warning');
        setLoading('btnGuardarFactura', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify(Object.assign({
                    action: 'crear',
                    cita_id: citaId,
                    costo_servicio: costoServicio,
                    descripcion_servicio: descripcionServicio,
                    plan_tipo: planTipo,
                    plan_cuotas_total: planCuotasTotal,
                    metodo_pago: metodoPago,
                    tasa_usada: tasaUsada,
                    monto_usd: montoUsd
                }, planTipo === 'cuotas' ? { notas_cuota: notasCuota } : {}))
            });
            if (res.success) {
                document.getElementById('generarFacturaModal').style.display = 'none';
                if (res.data?.planTipo === 'contado') {
                    toast('Factura creada y pagada exitosamente.', 'success');
                    const reciboId = res.data?.reciboId || res.data?.id;
                    setTimeout(() => {
                        window.open(APP_URL + 'factura-recibo?id=' + reciboId, '_blank');
                    }, 500);
                } else {
                    const cuotaStr = (res.data?.planMontoCuotaSugerido || 0).toFixed(2);
                    toast('Plan de ' + res.data?.planCuotasTotal + ' cuotas creado. Cuota sugerida: ' + cuotaStr + ' Bs', 'success');
                }
                cargarFacturas(currentTab);
                if (res.data?.id) {
                    const pDet = new URLSearchParams({ action: 'detalle-factura', factura_id: res.data.id });
                    callApi(APP_URL + 'Public/api/facturas.php?' + pDet.toString())
                        .then(r => { if (r.success && r.data) renderDetalleFactura(r.data, res.data.id); });
                }
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnGuardarFactura', false);
        }
    });

    // ── Registrar Abono ──────────────────────────────────
    document.getElementById('btnRegistrarAbonoPagos')?.addEventListener('click', function () {
        abrirModalPago();
    });

    document.getElementById('pagoMetodo')?.addEventListener('change', function (e) {
        const divGroup = document.getElementById('divisaGroup');
        if (e.target.value === 'divisas') {
            divGroup.style.display = 'block';
            document.getElementById('pagoMontoUsd').removeAttribute('readonly');
            document.getElementById('pagoMontoUsd').style.background = '';
        } else {
            divGroup.style.display = 'none';
            document.getElementById('pagoMontoUsd').value = '';
        }
    });

    document.getElementById('pagoMontoVes')?.addEventListener('input', function (e) {
        const metodo = document.getElementById('pagoMetodo').value;
        const ves = parseFloat(e.target.value);
        if (metodo === 'divisas' && !isNaN(ves) && ves > 0 && e.target.value !== '') {
            document.getElementById('pagoMontoUsd').value = (ves / TASA_BCV).toFixed(2);
        } else if (metodo === 'divisas' && e.target.value === '') {
            document.getElementById('pagoMontoUsd').value = '';
        }
    });

    document.getElementById('pagoForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const facturaIdVal = parseInt(document.getElementById('pagoFacturaId').value);
        const metodoPago = document.getElementById('pagoMetodo').value;
        const montoVes = parseFloat(document.getElementById('pagoMontoVes').value);
        const tasaUsada = parseFloat(document.getElementById('pagoTasa').value);
        if (!montoVes || montoVes <= 0) return toast('Ingrese un monto válido.', 'warning');
        const montoUsd = montoVes / tasaUsada;
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
                cargarFacturas(currentTab);
                if (selectedFacturaId) cargarDetalleFactura(selectedFacturaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnGuardarPago', false);
        }
    });

    // ── Cerrar factura ───────────────────────────────────
    document.getElementById('btnCerrarFactura')?.addEventListener('click', function () {
        if (!facturaId) return;
        document.getElementById('cerrarFacturaModal').style.display = 'flex';
    });

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
                cargarFacturas(currentTab);
                if (selectedFacturaId) cargarDetalleFactura(selectedFacturaId);
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

    // ── Anular factura ───────────────────────────────────
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
                cargarFacturas(currentTab);
                if (selectedFacturaId) cargarDetalleFactura(selectedFacturaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnAnularFactura', false);
        }
    });

    // ── Cerrar modales con X ─────────────────────────────
    document.querySelectorAll('.close-modal').forEach(el => {
        el.addEventListener('click', function () {
            this.closest('.modal').style.display = 'none';
        });
    });

    // ── Load initial data ────────────────────────────────
    cargarFacturas('abiertas');
});

// ──────────────────────────────────────────────────────────
// Sidebar: Cargar facturas por tab
// ──────────────────────────────────────────────────────────
async function cargarFacturas(tab) {
    const list = document.getElementById('sidebarList');
    list.innerHTML = '<div class="sidebar-empty">Cargando facturas...</div>';
    try {
        const res = await callApi(APP_URL + 'Public/api/facturas.php?action=list&status=' + tab);
        if (!res.success || !Array.isArray(res.data)) {
            list.innerHTML = '<div class="sidebar-empty">Error al cargar facturas.</div>';
            return;
        }
        allFacturas = res.data;
        renderSidebarList(allFacturas);
    } catch (err) {
        list.innerHTML = '<div class="sidebar-empty">Error de conexión.</div>';
    }
}

function renderSidebarList(facturas) {
    const list = document.getElementById('sidebarList');
    if (!facturas.length) {
        list.innerHTML = '<div class="sidebar-empty">No hay facturas en esta sección.</div>';
        return;
    }
    list.innerHTML = facturas.map(f => {
        const fecha = f.fechaCita ? f.fechaCita.slice(0, 10) : '—';
        const hasFactura = f.facturaId > 0;
        const estadoLabel = !hasFactura ? 'Pendiente' : f.estado === 'activa' ? 'Abierta' : f.estado === 'cerrada' ? 'Pagada' : 'Anulada';
        const estadoClass = !hasFactura ? 'estado-pendiente' : f.estado === 'activa' ? 'estado-activa' : f.estado === 'cerrada' ? 'estado-cerrada' : 'estado-anulada';
        const displayId = hasFactura ? '#' + String(f.facturaId).padStart(6, '0') : 'Cita #' + String(f.citaId).padStart(4, '0');
        const activeClass = (hasFactura ? parseInt(f.facturaId) : parseInt(f.citaId)) === selectedFacturaId ? ' active' : '';
        return '<div class="factura-item' + activeClass + '" data-factura-id="' + (f.facturaId || 0) + '" data-cita-id="' + f.citaId + '" onclick="seleccionarFactura(this)">' +
            '<div class="fi-header">' +
            '<span class="fi-id">' + displayId + '</span>' +
            '<span class="fi-date">' + fecha + '</span>' +
            '</div>' +
            '<div class="fi-client">' + escapeHtml(f.clienteNombre || '—') + '</div>' +
            '<div style="margin-top:4px;display:flex;justify-content:space-between;align-items:center;">' +
            '<span class="fi-status ' + estadoClass + '">' + estadoLabel + '</span>' +
            (hasFactura ? '<span style="font-size:0.7rem;color:var(--gray);">' + (parseFloat(f.totalFactura) || 0).toFixed(2) + ' Bs</span>' : '') +
            '</div>' +
            '</div>';
    }).join('');
}

function filtrarFacturas(query) {
    const q = query.toLowerCase().trim();
    if (!q) {
        renderSidebarList(allFacturas);
        return;
    }
    const filtradas = allFacturas.filter(f => {
        const searchStr = (f.facturaId ? '#' + String(f.facturaId) : 'Cita #' + String(f.citaId)) + ' ' + (f.clienteNombre || '') + ' ' + (f.estado || '');
        return searchStr.toLowerCase().includes(q);
    });
    renderSidebarList(filtradas);
}

// ──────────────────────────────────────────────────────────
// Seleccionar factura de la sidebar
// ──────────────────────────────────────────────────────────
function seleccionarFactura(el) {
    const fid = parseInt(el.dataset.facturaId);
    const cid = parseInt(el.dataset.citaId);
    selectedFacturaId = fid;
    document.querySelectorAll('.factura-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');

    if (fid > 0) {
        const params = new URLSearchParams({ action: 'detalle-factura', factura_id: fid });
        callApi(APP_URL + 'Public/api/facturas.php?' + params.toString())
            .then(res => {
                if (res.success && res.data) {
                    renderDetalleFactura(res.data, fid);
                } else {
                    toast('Error al cargar detalle.', 'error');
                }
            })
            .catch(err => {
                toast('Error al cargar detalle.', 'error');
            });
    } else if (cid > 0) {
        const params = new URLSearchParams({ action: 'detalle', cita_id: cid });
        callApi(APP_URL + 'Public/api/facturas.php?' + params.toString())
            .then(res => {
                if (res.success && res.data) {
                    renderDetalleFactura(res.data, fid);
                } else {
                    toast('Error al cargar detalle.', 'error');
                }
            })
            .catch(err => {
                toast('Error al cargar detalle.', 'error');
            });
    }
}

function ocultarDetalle() {
    document.getElementById('detailEmpty').style.display = 'block';
    document.getElementById('detailContent').style.display = 'none';
}

// ──────────────────────────────────────────────────────────
// Renderizar detalle de factura
// ──────────────────────────────────────────────────────────
function renderDetalleFactura(d, fid) {
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'block';

    TASA_BCV = d.tasaBcv || APP_CONFIG.bcvRate || 0;
    facturaId = d.general?.facturaId || fid || null;
    facturaEstado = d.general?.facturaEstado || null;
    facturaCitaId = d.general?.citaId || null;
    materialesCache = d.materiales || [];

    const badge = document.getElementById('facturaEstadoBadge');
    if (facturaId && facturaEstado && badge) {
        badge.style.display = 'inline-block';
        badge.textContent = facturaEstado.charAt(0).toUpperCase() + facturaEstado.slice(1);
        badge.className = 'estado-badge estado-' + facturaEstado;
    } else if (badge) {
        badge.style.display = 'none';
    }

    const g = d.general || {};
    document.getElementById('card1-cliente').textContent = g.clienteNombre || '—';
    document.getElementById('card1-cedula').textContent = g.clienteCedula || '—';
    document.getElementById('card1-telefono').textContent = g.clienteTelefono || '—';
    document.getElementById('card1-fecha').textContent = g.fechaHoraInicio ? g.fechaHoraInicio.replace('T', ' ') : '—';
    document.getElementById('card1-ubicacion').textContent = g.ubicacion || '—';
    document.getElementById('card1-evento').textContent = g.eventType || '—';
    document.getElementById('card1-estado-cita').textContent = g.citaEstado || '—';

    const atendidoRow = document.getElementById('card1-atendido-row');
    const atendidoEl = document.getElementById('card1-atendido');
    if (g.createdByName) {
        atendidoRow.style.display = 'flex';
        atendidoEl.textContent = g.createdByName;
    } else {
        atendidoRow.style.display = 'none';
    }

    // ── Desglose Table ───────────────────────────────────
    const mats = d.materiales || [];
    let html = '';
    let totalMat = 0;
    mats.forEach(m => {
        const cant = parseFloat(m.cantidad) || 0;
        const pu = parseFloat(m.precioUnitario) || 0;
        const subtotal = cant * pu;
        const ivaItem = subtotal * APP_CONFIG.ivaRate;
        totalMat += subtotal;
        html += '<tr>' +
            '<td>' + (m.codigo || '—') + '</td>' +
            '<td>' + (m.nombre || '—') + '</td>' +
            '<td class="text-right">' + cant + '</td>' +
            '<td class="text-right">' + pu.toFixed(2) + '</td>' +
            '<td class="text-right">' + subtotal.toFixed(2) + '</td>' +
            '<td class="text-right">' + ivaItem.toFixed(2) + '</td>' +
            '<td class="text-right">' + subtotal.toFixed(2) + '</td>' +
            '</tr>';
    });
    if (!mats.length) {
        html = '<tr><td colspan="7" class="text-center" style="color:var(--gray);padding:20px;">Sin materiales asignados</td></tr>';
    }
    document.getElementById('tabla-desglose').innerHTML = html;

    const costoServicio = parseFloat(g.costoServicio) || 0;
    const subtotal = totalMat + costoServicio;
    const iva = subtotal * APP_CONFIG.ivaRate;
    const totalFacturaCalc = subtotal + iva;
    document.getElementById('card2-total-materiales').textContent = totalMat.toFixed(2) + ' Bs';
    document.getElementById('card2-costo-servicio').textContent = costoServicio.toFixed(2) + ' Bs';
    document.getElementById('card2-iva').textContent = iva.toFixed(2) + ' Bs';
    document.getElementById('card2-total-valor').innerHTML = '<strong>' + totalFacturaCalc.toFixed(2) + ' Bs</strong>';

    // ── Actions en Card 2 ────────────────────────────────
    const actionsDiv = document.getElementById('facturaActions');
    if (facturaId) {
        let actionsHtml = '';
        if (facturaEstado === 'activa') {
            actionsHtml += '<button type="button" class="btn btn-primary" id="btnRegistrarAbonoCard2" style="flex:1;"><i class="fas fa-plus-circle"></i> Registrar Abono</button>';
        }
        actionsHtml += '<button type="button" class="btn btn-outline" id="btnImprimir" style="flex:1;"><i class="fas fa-print"></i> Imprimir Recibo</button>';
        actionsDiv.innerHTML = actionsHtml;
        actionsDiv.style.display = 'flex';
        document.getElementById('btnRegistrarAbonoCard2')?.addEventListener('click', function () {
            abrirModalPago();
        });
        document.getElementById('btnImprimir')?.addEventListener('click', function () {
            window.open(APP_URL + 'factura-recibo?id=' + facturaId, '_blank');
        });
    } else {
        // No tiene factura
        actionsDiv.innerHTML = '<button type="button" class="btn btn-primary" id="btnGenerarFactura" style="flex:1;"><i class="fas fa-file-invoice"></i> Generar Factura</button>';
        actionsDiv.style.display = 'flex';
        document.getElementById('btnGenerarFactura')?.addEventListener('click', function () {
            if (!facturaCitaId) return;
            document.getElementById('genFacturaCitaId').value = facturaCitaId;
            document.getElementById('generarFacturaModal').style.display = 'flex';
            actualizarPreviews(facturaCitaId);
        });
    }

    // ── Pagos ────────────────────────────────────────────
    const pagos = d.pagos || [];
    let pagosHtml = '';
    let totalPagadoVes = 0;
    pagos.forEach(p => {
        const monto = parseFloat(p.monto) || 0;
        const tasa = parseFloat(p.tasaUsada) || 0;
        const montoVes = monto * tasa;
        totalPagadoVes += montoVes;
        const esDivisas = p.metodoPago === 'divisas';
        const montoStr = esDivisas
            ? '$' + monto.toFixed(2) + ' @ ' + tasa.toFixed(2)
            : montoVes.toFixed(2) + ' Bs';
        pagosHtml += '<tr>' +
            '<td>' + (p.fecha ? p.fecha.slice(0, 10) : '—') + '</td>' +
            '<td class="text-right">' + montoStr + '</td>' +
            '<td>' + (p.metodoPago === 'divisas' ? 'Divisas' : p.metodoPago === 'efectivo' ? 'Efectivo' : 'PagoMóvil') + '</td>' +
            '<td class="text-right">' + tasa.toFixed(2) + '</td>' +
            '</tr>';
    });
    if (!pagos.length) {
        pagosHtml = '<tr><td colspan="4" class="text-center" style="color:var(--gray);padding:15px;">Sin pagos registrados</td></tr>';
    }
    document.getElementById('tabla-pagos').innerHTML = pagosHtml;

    const saldoPendienteVes = Math.max(0, totalFacturaCalc - totalPagadoVes);
    document.getElementById('card3-total-ves').textContent = totalFacturaCalc.toFixed(2) + ' Bs';
    document.getElementById('card3-pendiente-ves').textContent = saldoPendienteVes.toFixed(2) + ' Bs';

    // Progress bar
    const progressFill = document.getElementById('pagoProgressFill');
    const progressPct = totalFacturaCalc > 0 ? (totalPagadoVes / totalFacturaCalc * 100) : 0;
    progressFill.style.width = Math.min(100, progressPct) + '%';
    progressFill.className = 'pago-progress-fill ' + (progressPct >= 100 ? 'ok' : 'warning');
    document.getElementById('pago-progress-pagado').textContent = 'Pagado: ' + totalPagadoVes.toFixed(2) + ' Bs';
    document.getElementById('pago-progress-pendiente').textContent = 'Pendiente: ' + saldoPendienteVes.toFixed(2) + ' Bs';

    // Status
    const statusEl = document.getElementById('status-pago');
    if (!facturaId || totalFacturaCalc <= 0) {
        statusEl.textContent = 'FACTURA POR GENERAR';
        statusEl.className = 'pago-status';
    } else if (saldoPendienteVes <= 0.01) {
        statusEl.textContent = '✓ FACTURA TOTALMENTE PAGADA';
        statusEl.className = 'pago-status pagada';
    } else {
        statusEl.textContent = 'PAGO POR CUOTAS — Pendiente: ' + saldoPendienteVes.toFixed(2) + ' Bs';
        statusEl.className = 'pago-status pendiente-pago';
    }

    // Pago Actions (Card 3)
    const pagoActions = document.getElementById('pagoActions');
    const btnAbono = document.getElementById('btnRegistrarAbonoPagos');
    const btnCerrar = document.getElementById('btnCerrarFactura');
    const btnAnular = document.getElementById('btnAnularFactura');
    if (facturaId && facturaEstado === 'activa') {
        pagoActions.style.display = 'flex';
        if (btnAbono) { btnAbono.style.display = ''; }
        if (btnCerrar) { btnCerrar.style.display = ''; }
        if (btnAnular) { btnAnular.style.display = ''; }
    } else {
        pagoActions.style.display = 'none';
    }
}

// ──────────────────────────────────────────────────────────
// Modal: Registrar Abono
// ──────────────────────────────────────────────────────────
function abrirModalPago() {
    if (!facturaId) return toast('Debe generar la factura primero.', 'warning');
    document.getElementById('pagoFacturaId').value = facturaId;
    document.getElementById('pagoMontoVes').value = '';
    document.getElementById('pagoMontoUsd').value = '';
    document.getElementById('pagoTasa').value = TASA_BCV.toFixed(2);
    document.getElementById('pagoTasaDisplay').textContent = TASA_BCV.toFixed(2);
    document.getElementById('divisaGroup').style.display = 'none';
    document.getElementById('pagoMetodo').value = 'efectivo';
    document.getElementById('pagoModal').style.display = 'flex';
}

// ──────────────────────────────────────────────────────────
// Preview helpers
// ──────────────────────────────────────────────────────────
function actualizarPreviews(citaId) {
    const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
    let totalMat = 0;
    materialesCache.forEach(m => {
        totalMat += (parseFloat(m.cantidad) || 0) * (parseFloat(m.precioUnitario) || 0);
    });
    const subtotal = totalMat + costoServicio;
    const iva = subtotal * APP_CONFIG.ivaRate;
    const total = subtotal + iva;

    const fmt = v => v.toFixed(2) + ' Bs';

    document.getElementById('previewSubtotal').textContent = fmt(subtotal);
    document.getElementById('previewIva').textContent = fmt(iva);
    document.getElementById('previewTotal').textContent = fmt(total);

    document.getElementById('cuotaPreviewSubtotal').textContent = fmt(subtotal);
    document.getElementById('cuotaPreviewIva').textContent = fmt(iva);
    document.getElementById('cuotaPreviewTotal').textContent = fmt(total);
    const n = parseInt(document.getElementById('genNumCuotas').value) || 2;
    document.getElementById('cuotaPreviewCuota').textContent = n + ' cuotas de ' + fmt(total / n);
}

// ──────────────────────────────────────────────────────────
// Términos y Condiciones
// ──────────────────────────────────────────────────────────
async function cargarTerminos() {
    try {
        const res = await callApi(APP_URL + 'Public/api/facturas.php?action=terminos');
        const termsBody = document.getElementById('termsBody');
        if (res.success && res.data?.terminos) {
            const terms = res.data.terminos;
            const lines = terms.split('\n').filter(l => l.trim());
            if (lines.length) {
                termsBody.innerHTML = '<ul>' + lines.map(l => '<li>' + escapeHtml(l.trim()) + '</li>').join('') + '</ul>';
            } else {
                termsBody.innerHTML = '<p class="terms-placeholder">No hay términos configurados aún.</p>';
            }
        } else {
            document.getElementById('termsBody').innerHTML = '<p class="terms-placeholder">No hay términos configurados aún.</p>';
        }
    } catch (err) {
        document.getElementById('termsBody').innerHTML = '<p class="terms-placeholder">Error al cargar términos.</p>';
    }
}

// ──────────────────────────────────────────────────────────
// Utility
// ──────────────────────────────────────────────────────────
function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.classList.toggle('btn-loading', loading);
}
