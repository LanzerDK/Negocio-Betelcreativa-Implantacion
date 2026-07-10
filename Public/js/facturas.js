let TASA_BCV = APP_CONFIG?.bcvRate || 0;
let facturaId = null;
let facturaEstado = null;
let facturaCitaId = null;
let currentTab = 'pendientes';
let materialesCache = [];
let allFacturas = [];
let selectedFacturaId = null;
let totalCalculado = 0;

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
        if (!facturaCitaId) return toast('Seleccione una cita primero.', 'warning');
        document.getElementById('genFacturaCitaId').value = facturaCitaId;
        document.getElementById('genCostoServicio').value = '';
        document.getElementById('genDescripcionServicio').value = '';
        document.getElementById('genMontoBs').value = '';
        document.getElementById('genMetodoPago').value = 'efectivo';
        const genLabel = document.getElementById('genMontoLabel');
        if (genLabel) genLabel.textContent = 'Monto a pagar ahora (Bs)';
        document.getElementById('genMontoBs').placeholder = '0,00';
        document.getElementById('genConversionRow').style.display = 'none';
        document.getElementById('genErrorAnticipo').style.display = 'none';
        document.getElementById('genTasaBcv').value = TASA_BCV;
        document.getElementById('genProgressFill').style.width = '0%';
        document.getElementById('genProgressLabel').textContent = '0%';
        totalCalculado = 0;
        actualizarPreviews(facturaCitaId);
        document.getElementById('generarFacturaModal').style.display = 'flex';
    });

    document.getElementById('cancelGenFactura')?.addEventListener('click', function () {
        document.getElementById('generarFacturaModal').style.display = 'none';
    });
    document.getElementById('cancelPago')?.addEventListener('click', function () {
        document.getElementById('pagoModal').style.display = 'none';
    });

    // ── Conversión en vivo: método de pago en generar factura ──
    document.getElementById('genMetodoPago')?.addEventListener('change', function () {
        const label = document.getElementById('genMontoLabel');
        const input = document.getElementById('genMontoBs');
        if (this.value === 'divisas') {
            label.textContent = 'Monto a pagar ahora ($)';
            input.placeholder = '0.00';
        } else {
            label.textContent = 'Monto a pagar ahora (Bs)';
            input.placeholder = '0,00';
        }
        input.value = '';
        actualizarConversionGenerar();
        validarAnticipo();
    });
    document.getElementById('genMontoBs')?.addEventListener('input', function () {
        actualizarConversionGenerar();
        validarAnticipo();
    });

    function actualizarConversionGenerar() {
        const metodo = document.getElementById('genMetodoPago').value;
        const montoInput = parseFloat(document.getElementById('genMontoBs').value) || 0;
        const row = document.getElementById('genConversionRow');
        const text = document.getElementById('genConversionText');
        if (!montoInput || montoInput <= 0 || !TASA_BCV || TASA_BCV <= 0) { row.style.display = 'none'; return; }
        row.style.display = 'block';
        if (metodo === 'divisas') {
            const bs = montoInput * TASA_BCV;
            text.innerHTML = '<strong>$' + montoInput.toFixed(2) + '</strong> = <strong>' + bs.toFixed(2) + ' Bs</strong> (tasa: 1 $ = ' + TASA_BCV.toFixed(2) + ' Bs)';
        } else {
            const usd = montoInput / TASA_BCV;
            text.innerHTML = '<strong>' + montoInput.toFixed(2) + ' Bs</strong> ≈ <strong>$' + usd.toFixed(2) + ' USD</strong> (tasa: ' + TASA_BCV.toFixed(2) + ')';
        }
    }

    function validarAnticipo() {
        const metodo = document.getElementById('genMetodoPago').value;
        const montoInput = parseFloat(document.getElementById('genMontoBs').value) || 0;
        const montoEnBs = metodo === 'divisas' ? montoInput * TASA_BCV : montoInput;
        const errEl = document.getElementById('genErrorAnticipo');
        const progressFill = document.getElementById('genProgressFill');
        const progressLabel = document.getElementById('genProgressLabel');
        const btnSubmit = document.getElementById('btnGuardarFactura');

        if (!totalCalculado || totalCalculado <= 0) {
            errEl.style.display = 'none';
            progressFill.style.width = '0%';
            progressLabel.textContent = '0%';
            btnSubmit.disabled = false;
            return;
        }

        const minimo = totalCalculado * 0.50;
        const pct = Math.min(100, (montoEnBs / totalCalculado) * 100);
        progressFill.style.width = pct + '%';
        progressFill.className = 'pago-progress-fill ' + (pct >= 100 ? 'ok' : 'warning');
        progressLabel.textContent = pct.toFixed(0) + '%';

        if (montoEnBs <= 0) {
            errEl.style.display = 'none';
            btnSubmit.disabled = false;
            return;
        }

        const esDivisas = metodo === 'divisas';

        if (montoEnBs > totalCalculado + 0.01) {
            const dev = (montoEnBs - totalCalculado).toFixed(2);
            errEl.textContent = 'Se generará una devolución de ' + dev + ' Bs por el excedente.';
            errEl.style.display = 'block';
            errEl.style.color = '#856404';
            btnSubmit.disabled = false;
        } else {
            errEl.style.display = 'none';
            errEl.style.color = '#dc3545';
        }

        if (montoEnBs < minimo - 0.01) {
            const minStr = esDivisas ? (minimo / TASA_BCV).toFixed(2) + ' $' : minimo.toFixed(2) + ' Bs';
            errEl.textContent = 'El anticipo mínimo es del 50% (' + minStr + ').';
            errEl.style.display = 'block';
            btnSubmit.disabled = true;
            return;
        }

        errEl.style.display = 'none';
        btnSubmit.disabled = false;
    }

    // ── Costo servicio recalcula preview ──────────────────
    document.getElementById('genCostoServicio')?.addEventListener('input', function () {
        const citaId = document.getElementById('genFacturaCitaId').value;
        if (citaId) actualizarPreviews(citaId);
    });

    // ── Submit generar factura ───────────────────────────
    document.getElementById('generarFacturaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const citaId = parseInt(document.getElementById('genFacturaCitaId').value);
        const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
        const descripcionServicio = document.getElementById('genDescripcionServicio').value.trim();
        const montoInput = parseFloat(document.getElementById('genMontoBs').value) || 0;
        const metodoPago = document.getElementById('genMetodoPago').value;
        const montoPagoBs = metodoPago === 'divisas' ? montoInput * TASA_BCV : montoInput;

        if (!citaId) return toast('ID de cita requerido.', 'warning');
        if (!descripcionServicio) return toast('Describa el servicio prestado.', 'warning');
        if (!costoServicio || costoServicio <= 0) return toast('Especifique un costo de servicio válido.', 'warning');
        if (!materialesCache.length) return toast('La cita debe tener al menos un material asignado.', 'warning');
        if (!metodoPago) return toast('Seleccione un método de pago.', 'warning');
        if (montoInput <= 0) return toast('Debe especificar un monto de anticipo.', 'warning');

        const minimo = totalCalculado * 0.50;
        if (montoPagoBs < minimo - 0.01) {
            const minStr = metodoPago === 'divisas' ? (minimo / TASA_BCV).toFixed(2) + ' $' : minimo.toFixed(2) + ' Bs';
            return toast('El anticipo mínimo obligatorio es del 50% (' + minStr + ').', 'warning');
        }

        setLoading('btnGuardarFactura', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({
                    action: 'crear',
                    cita_id: citaId,
                    costo_servicio: costoServicio,
                    descripcion_servicio: descripcionServicio,
                    monto_pago_bs: montoPagoBs,
                    metodo_pago: metodoPago,
                    tasa_usada: TASA_BCV
                })
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
                    toast(res.message || 'Anticipo registrado exitosamente.', 'success');
                    const reciboId = res.data?.reciboId;
                    if (reciboId) {
                        setTimeout(() => {
                            window.open(APP_URL + 'factura-recibo?id=' + reciboId, '_blank');
                        }, 500);
                    }
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

    document.getElementById('pagoMetodo')?.addEventListener('change', actualizarConversionPago);
    document.getElementById('pagoMontoVes')?.addEventListener('input', actualizarConversionPago);

    function actualizarConversionPago() {
        const metodo = document.getElementById('pagoMetodo').value;
        const monto = parseFloat(document.getElementById('pagoMontoVes').value) || 0;
        const hint = document.getElementById('pagoInputHint');
        const row = document.getElementById('pagoConversionRow');
        const text = document.getElementById('pagoConversionText');

        if (metodo === 'divisas') {
            hint.textContent = 'Ingrese el monto en Bolívares (se convertirá a Dólares)';
        } else {
            hint.textContent = 'Ingrese el monto en Bolívares';
        }

        if (!monto || monto <= 0 || !TASA_BCV || TASA_BCV <= 0) { row.style.display = 'none'; return; }
        row.style.display = 'block';

        const usd = monto / TASA_BCV;
        if (metodo === 'divisas') {
            text.innerHTML = 'Equivalente en Dólares: <strong>$' + usd.toFixed(2) + '</strong> (tasa: ' + TASA_BCV.toFixed(2) + ')';
        } else {
            text.innerHTML = 'Equivalente en Dólares: <strong>$' + usd.toFixed(2) + '</strong> (tasa: ' + TASA_BCV.toFixed(2) + ')';
        }
    }

    document.getElementById('pagoForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const facturaIdVal = parseInt(document.getElementById('pagoFacturaId').value);
        const metodoPago = document.getElementById('pagoMetodo').value;
        const montoVes = parseFloat(document.getElementById('pagoMontoVes').value);
        const tasaUsada = parseFloat(document.getElementById('pagoTasa').value) || TASA_BCV;
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

    // ── Imprimir (Pagadas / readonly) ────────────────────
    document.getElementById('btnImprimirOriginal')?.addEventListener('click', function () {
        if (!facturaId) return;
        window.open(APP_URL + 'factura-recibo?id=' + facturaId, '_blank');
    });
    document.getElementById('btnImprimirCopia')?.addEventListener('click', function () {
        if (!facturaId) return;
        window.open(APP_URL + 'factura-recibo?id=' + facturaId + '&copia=1', '_blank');
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
    document.getElementById('btnAnularFactura')?.addEventListener('click', function () {
        if (!facturaId) return;
        document.getElementById('anularMotivo').value = '';
        document.getElementById('anularFacturaModal').style.display = 'flex';
    });

    document.getElementById('cancelAnularFactura')?.addEventListener('click', function () {
        document.getElementById('anularFacturaModal').style.display = 'none';
    });

    document.getElementById('anularFacturaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const motivo = document.getElementById('anularMotivo').value.trim();
        if (!motivo) {
            toast('Debe indicar el motivo de anulación.', 'warning');
            return;
        }
        setLoading('btnConfirmarAnular', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'anular', factura_id: facturaId, motivo: motivo })
            });
            if (res.success) {
                toast('Factura anulada exitosamente.', 'success');
                document.getElementById('anularFacturaModal').style.display = 'none';
                cargarFacturas(currentTab);
                if (selectedFacturaId) cargarDetalleFactura(selectedFacturaId);
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (err) {
            toast(err.message || 'Error de conexión.', 'error');
        } finally {
            setLoading('btnConfirmarAnular', false);
        }
    });

    // ── Cerrar modales con X ─────────────────────────────
    document.querySelectorAll('.close-modal').forEach(el => {
        el.addEventListener('click', function () {
            this.closest('.modal').style.display = 'none';
        });
    });

    // ── Load initial data ────────────────────────────────
    cargarFacturas('pendientes');
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

    if (currentTab === 'canceladas') {
        list.innerHTML = facturas.map(f => {
            const fecha = f.fechaFactura ? f.fechaFactura.slice(0, 10) : (f.fechaCita ? f.fechaCita.slice(0, 10) : '—');
            const activeClass = (f.facturaId || 0) === selectedFacturaId ? ' active' : '';
            const totalPagado = parseFloat(f.totalPagadoVes) || 0;
            const totalFactura = parseFloat(f.totalFactura) || 0;
            return '<div class="factura-item' + activeClass + '" data-factura-id="' + (f.facturaId || 0) + '" data-cita-id="' + f.citaId + '" onclick="seleccionarFactura(this)">' +
                '<div class="fi-header">' +
                '<span class="fi-id">#' + String(f.facturaId || 0).padStart(6, '0') + '</span>' +
                '<span class="fi-date">' + fecha + '</span>' +
                '</div>' +
                '<div class="fi-client">' + escapeHtml(f.clienteNombre || '—') + ' <span class="fi-cedula">' + escapeHtml(f.clienteCedula || '') + '</span></div>' +
                '<div style="margin-top:4px;display:flex;justify-content:space-between;align-items:center;">' +
                '<span class="fi-status estado-cancelada">Cancelada</span>' +
                '<span style="font-size:0.7rem;color:#dc3545;">Pagado: ' + (totalPagado).toFixed(2) + ' / ' + totalFactura.toFixed(2) + ' Bs</span>' +
                '</div>' +
                '</div>';
        }).join('');
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
            '<div class="fi-client">' + escapeHtml(f.clienteNombre || '—') + ' <span class="fi-cedula">' + escapeHtml(f.clienteCedula || '') + '</span></div>' +
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

    if (currentTab === 'canceladas') {
        if (fid > 0) {
            const params = new URLSearchParams({ action: 'detalle-factura', factura_id: fid });
            callApi(APP_URL + 'Public/api/facturas.php?' + params.toString())
                .then(res => {
                    if (res.success && res.data) {
                        renderDetalleCancelada(res.data, fid);
                    } else {
                        toast('Error al cargar detalle.', 'error');
                    }
                })
                .catch(err => {
                    toast('Error al cargar detalle.', 'error');
                });
        }
        return;
    }

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
    document.getElementById('detailCancelada').style.display = 'none';
}

// ──────────────────────────────────────────────────────────
// Renderizar detalle de cancelada
// ──────────────────────────────────────────────────────────
function renderDetalleCancelada(d, fid) {
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'none';
    document.getElementById('detailCancelada').style.display = 'block';

    const g = d.general || {};
    const totalPagadoVes = (d.pagos || []).reduce((sum, p) => sum + (parseFloat(p.monto) || 0) * (parseFloat(p.tasaUsada) || 0), 0);
    const totalFactura = parseFloat(g.totalFactura) || 0;

    document.getElementById('can-cliente').textContent = g.clienteNombre || '—';
    document.getElementById('can-cedula').textContent = g.clienteCedula || '—';
    document.getElementById('can-fechaCita').textContent = g.fechaHoraInicio ? g.fechaHoraInicio.replace('T', ' ') : '—';
    document.getElementById('can-fechaFactura').textContent = g.facturaCreatedAt ? g.facturaCreatedAt.replace('T', ' ') : '—';
    document.getElementById('can-codigo').textContent = g.facturaId ? '#' + String(g.facturaId).padStart(6, '0') : '—';
    document.getElementById('can-motivo').textContent = g.motivoCancelacion || 'Sin motivo registrado';
    document.getElementById('can-montoPagado').textContent = totalPagadoVes.toFixed(2) + ' Bs';
    document.getElementById('can-montoTotal').textContent = totalFactura.toFixed(2) + ' Bs';
}

// ──────────────────────────────────────────────────────────
// Renderizar detalle de factura
// ──────────────────────────────────────────────────────────
function renderDetalleFactura(d, fid) {
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'block';
    document.getElementById('detailCancelada').style.display = 'none';

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
    document.getElementById('card2-costo-servicio').textContent = costoServicio.toFixed(2) + ' Bs';
    document.getElementById('card2-iva').textContent = iva.toFixed(2) + ' Bs';
    document.getElementById('card2-total-valor').innerHTML = '<strong>' + totalMat.toFixed(2) + ' Bs</strong>';

    // ── Actions en Card 2 ────────────────────────────────
    const actionsDiv = document.getElementById('facturaActions');
    if (facturaId && facturaEstado === 'cerrada') {
        // Pagada: solo mostrar imprimir
        actionsDiv.innerHTML = '';
        actionsDiv.style.display = 'none';
    } else if (facturaId) {
        let actionsHtml = '';
        if (facturaEstado === 'activa') {
            actionsHtml += '<button type="button" class="btn btn-primary" id="btnRegistrarAbonoCard2" style="flex:1;"><i class="fas fa-plus-circle"></i> Registrar Abono</button>';
        }
        actionsDiv.innerHTML = actionsHtml;
        actionsDiv.style.display = actionsHtml ? 'flex' : 'none';
        document.getElementById('btnRegistrarAbonoCard2')?.addEventListener('click', function () {
            abrirModalPago();
        });
    } else {
        // No tiene factura (Pendiente)
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
        const metodoLabel = p.metodoPago === 'divisas' ? 'Dólar $' : p.metodoPago === 'efectivo' ? 'Efectivo' : 'Pago Móvil';
        const montoStr = esDivisas
            ? '$' + monto.toFixed(2) + ' @ ' + tasa.toFixed(2)
            : montoVes.toFixed(2) + ' Bs';
        pagosHtml += '<tr>' +
            '<td>' + (p.fecha ? p.fecha.slice(0, 10) : '—') + '</td>' +
            '<td class="text-right">' + montoStr + '</td>' +
            '<td>' + metodoLabel + '</td>' +
            '<td class="text-right">' + tasa.toFixed(2) + '</td>' +
            '</tr>';
    });
    const devolucion = Math.max(0, totalPagadoVes - totalFacturaCalc);
    if (devolucion > 0.01) {
        pagosHtml += '<tr class="devolucion-row"><td colspan="4" class="text-right" style="padding-top:8px;font-weight:700;color:#dc3545;">Devolución: ' + devolucion.toFixed(2) + ' Bs</td></tr>';
    }
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
        const devMsg = (totalPagadoVes > totalFacturaCalc + 0.01) ? ' — Devolución: ' + (totalPagadoVes - totalFacturaCalc).toFixed(2) + ' Bs' : '';
        statusEl.textContent = '✓ FACTURA TOTALMENTE PAGADA' + devMsg;
        statusEl.className = 'pago-status pagada';
    } else {
        statusEl.textContent = 'PAGO PARCIAL — Pendiente: ' + saldoPendienteVes.toFixed(2) + ' Bs';
        statusEl.className = 'pago-status pendiente-pago';
    }

    // Pago Actions (Card 3) — Show based on tab and estado
    const pagoActions = document.getElementById('pagoActions');
    const pagoActionsRO = document.getElementById('pagoActionsReadOnly');
    const btnAbono = document.getElementById('btnRegistrarAbonoPagos');
    const btnCerrar = document.getElementById('btnCerrarFactura');
    const btnAnular = document.getElementById('btnAnularFactura');

    if (currentTab === 'pagadas' || facturaEstado === 'cerrada') {
        // Read-only: only show print buttons
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'flex';
    } else if (facturaId && facturaEstado === 'activa') {
        pagoActions.style.display = 'flex';
        pagoActionsRO.style.display = 'none';
        if (btnAbono) { btnAbono.style.display = ''; }
        if (btnCerrar) { btnCerrar.style.display = ''; }
        if (btnAnular) { btnAnular.style.display = ''; }
    } else {
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'none';
    }

    // For pending (sin factura), also hide pago actions
    if (!facturaId) {
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'none';
    }
}

// ──────────────────────────────────────────────────────────
// Modal: Registrar Abono
// ──────────────────────────────────────────────────────────
function abrirModalPago() {
    if (!facturaId) return toast('Debe generar la factura primero.', 'warning');
    document.getElementById('pagoFacturaId').value = facturaId;
    document.getElementById('pagoMontoVes').value = '';
    document.getElementById('pagoTasa').value = TASA_BCV.toFixed(2);
    document.getElementById('pagoMetodo').value = 'efectivo';
    document.getElementById('pagoConversionRow').style.display = 'none';
    document.getElementById('pagoInputHint').textContent = 'Ingrese el monto en Bolívares';
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
    totalCalculado = subtotal + iva;

    const fmt = v => v.toFixed(2) + ' Bs';

    document.getElementById('previewSubtotal').textContent = fmt(subtotal);
    document.getElementById('previewIva').textContent = fmt(iva);
    document.getElementById('previewTotal').textContent = fmt(totalCalculado);
    document.getElementById('previewMinimo').textContent = fmt(totalCalculado * 0.50);
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

function asyncCargarDetalleFactura(facturaId) {
    const params = new URLSearchParams({ action: 'detalle-factura', factura_id: facturaId });
    return callApi(APP_URL + 'Public/api/facturas.php?' + params.toString());
}

async function cargarDetalleFactura(fid) {
    try {
        const res = await asyncCargarDetalleFactura(fid);
        if (res.success && res.data) {
            renderDetalleFactura(res.data, fid);
        }
    } catch (err) {
        // silent
    }
}

// ──────────────────────────────────────────────────────────
// Utility
// ──────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.classList.toggle('btn-loading', loading);
}
