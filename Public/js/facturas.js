// Tasa del BCV cargada desde configuración global (APP_CONFIG)
let TASA_BCV = APP_CONFIG?.bcvRate || 0;

// Identificador de la factura actualmente seleccionada en la sidebar
let facturaId = null;

// Estado actual de la factura (activa, cerrada, anulada)
let facturaEstado = null;

// ID de la cita asociada a la factura actual
let facturaCitaId = null;

// Pestaña activa en la sidebar: pendientes, abiertas, pagadas, canceladas
let currentTab = 'pendientes';

// Caché de materiales de la cita para cálculos de preview
let materialesCache = [];

// Lista completa de facturas cargadas para la pestaña actual
let allFacturas = [];

// ID de la factura seleccionada en la sidebar (para mantener highlight)
let selectedFacturaId = null;

// Total calculado de la factura (subtotal + IVA) usado para validar anticipo mínimo
let totalCalculado = 0;

// Inicialización principal al cargar el DOM
document.addEventListener('DOMContentLoaded', function () {

    // Carga los términos y condiciones desde la API
    cargarTerminos();

    // ── Sidebar Tabs ─────────────────────────────────────
    // Navegación entre pestañas: pendientes, abiertas, pagadas, canceladas
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
    // Búsqueda con debounce de 300ms en la lista de facturas
    document.getElementById('sidebarSearch')?.addEventListener('input', function () {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => filtrarFacturas(this.value), 300);
    });

    // ── Abrir modal generar factura ──────────────────────
    // Prepara y abre el modal de generación de factura con los datos de la cita seleccionada
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
        const genRefGroup = document.getElementById('genRefGroup');
        if (genRefGroup) genRefGroup.style.display = 'none';
        const genReferencia = document.getElementById('genReferencia');
        if (genReferencia) genReferencia.value = '';
        document.getElementById('genProgressFill').style.width = '0%';
        document.getElementById('genProgressLabel').textContent = '0%';
        totalCalculado = 0;
        actualizarPreviews(facturaCitaId);
        document.getElementById('generarFacturaModal').style.display = 'flex';
    });

    // Botón cancelar del modal generar factura
    document.getElementById('cancelGenFactura')?.addEventListener('click', function () {
        document.getElementById('generarFacturaModal').style.display = 'none';
    });
    // Botón cancelar del modal de pago (abono)
    document.getElementById('cancelPago')?.addEventListener('click', function () {
        document.getElementById('pagoModal').style.display = 'none';
    });

    // ── Conversión en vivo: método de pago en generar factura ──
    // Cambia label y placeholder entre Bolívares y Dólares según método seleccionado
    // Muestra u oculta campo de referencia para Pago Móvil
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
        const genRefGroup = document.getElementById('genRefGroup');
        if (genRefGroup) genRefGroup.style.display = this.value === 'pagomovil' ? 'block' : 'none';
        actualizarConversionGenerar();
        validarAnticipo();
    });
    // Recalcula conversión y valida anticipo al escribir el monto
    document.getElementById('genMontoBs')?.addEventListener('input', function () {
        actualizarConversionGenerar();
        validarAnticipo();
    });

    // Calcula y muestra la conversión en tiempo real Bs ↔ USD
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

    // Valida que el anticipo sea al menos 50% del total calculado
    // Muestra advertencia de sobrepago si el monto excede el total
    function validarAnticipo() {
        const metodo = document.getElementById('genMetodoPago').value;
        const montoInput = parseFloat(document.getElementById('genMontoBs').value) || 0;
        const montoEnBs = metodo === 'divisas' ? montoInput * TASA_BCV : montoInput;
        const errEl = document.getElementById('genErrorAnticipo');
        const progressFill = document.getElementById('genProgressFill');
        const progressLabel = document.getElementById('genProgressLabel');
        const btnSubmit = document.getElementById('btnGuardarFactura');

        // Si no hay total calculado, ocultar errores y permitir envío
        if (!totalCalculado || totalCalculado <= 0) {
            errEl.style.display = 'none';
            progressFill.style.width = '0%';
            progressLabel.textContent = '0%';
            btnSubmit.disabled = false;
            return;
        }

        // Barra de progreso del anticipo (0% a 100%)
        const minimo = totalCalculado * 0.50;
        const pct = Math.min(100, (montoEnBs / totalCalculado) * 100);
        progressFill.style.width = pct + '%';
        progressFill.className = 'pago-progress-fill ' + (pct >= 100 ? 'ok' : 'warning');
        progressLabel.textContent = pct.toFixed(0) + '%';

        // Si el monto es cero o negativo, ocultar errores
        if (montoEnBs <= 0) {
            errEl.style.display = 'none';
            btnSubmit.disabled = false;
            return;
        }

        const esDivisas = metodo === 'divisas';

        // Advertencia de sobrepago (no bloquea el envío)
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

        // Bloqueo si el anticipo es menor al 50% mínimo
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
    // Recalcula totales al modificar el costo del servicio
    document.getElementById('genCostoServicio')?.addEventListener('input', function () {
        const citaId = document.getElementById('genFacturaCitaId').value;
        if (citaId) actualizarPreviews(citaId);
    });

    // ── Submit generar factura ───────────────────────────
    // Envía el formulario de generación de factura a la API
    document.getElementById('generarFacturaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const citaId = parseInt(document.getElementById('genFacturaCitaId').value);
        const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
        const descripcionServicio = document.getElementById('genDescripcionServicio').value.trim();
        const montoInput = parseFloat(document.getElementById('genMontoBs').value) || 0;
        const metodoPago = document.getElementById('genMetodoPago').value;
        const montoPagoBs = metodoPago === 'divisas' ? montoInput * TASA_BCV : montoInput;

        // Validaciones de campos obligatorios
        if (!citaId) return toast('ID de cita requerido.', 'warning');
        if (!descripcionServicio) return toast('Describa el servicio prestado.', 'warning');
        if (!costoServicio || costoServicio <= 0) return toast('Especifique un costo de servicio válido.', 'warning');
        if (!metodoPago) return toast('Seleccione un método de pago.', 'warning');
        if (montoInput <= 0) return toast('Debe especificar un monto de anticipo.', 'warning');

        // Validación de referencia para Pago Móvil (10-12 dígitos)
        if (metodoPago === 'pagomovil') {
            const ref = document.getElementById('genReferencia')?.value?.trim();
            if (!ref) return toast('Debe ingresar el número de referencia de Pago Móvil.', 'warning');
            if (!/^\d{10,12}$/.test(ref)) return toast('La referencia debe contener solo números, entre 10 y 12 dígitos.', 'warning');
            if (!confirm('¿Está seguro que la referencia "' + ref + '" es correcta? Una vez confirmada no podrá modificarse.')) return;
        }

        // Validación del anticipo mínimo del 50%
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
                    tasa_usada: TASA_BCV,
                    ref_pago_movil: document.getElementById('genReferencia')?.value?.trim() || ''
                })
            });
            if (res.success) {
                document.getElementById('generarFacturaModal').style.display = 'none';
                // Si es contado: factura creada y pagada, abrir recibo automáticamente
                if (res.data?.planTipo === 'contado') {
                    toast('Factura creada y pagada exitosamente.', 'success');
                    const reciboId = res.data?.reciboId || res.data?.id;
                    setTimeout(() => {
                        window.open(APP_URL + 'factura-recibo?id=' + reciboId, '_blank');
                    }, 500);
                } else {
                    // Si es cuotas: anticipo registrado, abrir recibo si existe
                    toast(res.message || 'Anticipo registrado exitosamente.', 'success');
                    const reciboId = res.data?.reciboId;
                    if (reciboId) {
                        setTimeout(() => {
                            window.open(APP_URL + 'factura-recibo?id=' + reciboId, '_blank');
                        }, 500);
                    }
                }
                // Cambiar a la pestaña correspondiente según tipo de plan
                if (res.data?.planTipo === 'contado') {
                    currentTab = 'pagadas';
                } else if (res.data?.planTipo === 'cuotas') {
                    currentTab = 'abiertas';
                }
                cargarFacturas(currentTab);
                // Cargar detalle de la factura recién creada
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
    // Abre el modal para registrar un pago/abono a una factura
    document.getElementById('btnRegistrarAbonoPagos')?.addEventListener('click', function () {
        abrirModalPago();
    });

    // Cambio de método de pago en el modal de abono
    document.getElementById('pagoMetodo')?.addEventListener('change', onPagoMetodoChange);
    // Recalcula conversión al escribir monto en el modal de abono
    document.getElementById('pagoMontoVes')?.addEventListener('input', actualizarConversionPago);

    // Alterna label, placeholder y campo de referencia según método de pago
    function onPagoMetodoChange() {
        const metodo = document.getElementById('pagoMetodo').value;
        const label = document.getElementById('pagoLabel');
        const input = document.getElementById('pagoMontoVes');
        const refGroup = document.getElementById('pagoRefGroup');

        if (metodo === 'divisas') {
            label.textContent = 'Monto ($)';
            input.placeholder = '0.00';
        } else {
            label.textContent = 'Monto (Bs)';
            input.placeholder = '0,00';
        }
        input.value = '';
        actualizarConversionPago();

        // Toggle campo de referencia solo para Pago Móvil
        refGroup.style.display = metodo === 'pagomovil' ? 'block' : 'none';
    }

    // Muestra conversión en vivo Bs ↔ USD en el modal de abono
    function actualizarConversionPago() {
        const metodo = document.getElementById('pagoMetodo').value;
        const montoInput = parseFloat(document.getElementById('pagoMontoVes').value) || 0;
        const row = document.getElementById('pagoConversionRow');
        const text = document.getElementById('pagoConversionText');

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

    // Envío del formulario de abono/pago a la API
    document.getElementById('pagoForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const facturaIdVal = parseInt(document.getElementById('pagoFacturaId').value);
        const metodoPago = document.getElementById('pagoMetodo').value;
        const montoInput = parseFloat(document.getElementById('pagoMontoVes').value);
        const tasaUsada = parseFloat(document.getElementById('pagoTasa').value) || TASA_BCV;
        if (!montoInput || montoInput <= 0) return toast('Ingrese un monto válido.', 'warning');

        // Validación de referencia para Pago Móvil
        if (metodoPago === 'pagomovil') {
            const ref = document.getElementById('pagoReferencia')?.value?.trim();
            if (!ref) return toast('Debe ingresar el número de referencia de Pago Móvil.', 'warning');
            if (!/^\d{10,12}$/.test(ref)) return toast('La referencia debe contener solo números, entre 10 y 12 dígitos.', 'warning');
            if (!confirm('¿Está seguro que la referencia "' + ref + '" es correcta? Una vez confirmada no podrá modificarse.')) return;
        }

        // Conversión: si divisas el input es USD; si no, es Bs y se convierte a USD
        const monto = metodoPago === 'divisas' ? montoInput : montoInput / tasaUsada;
        const refPagoMovil = document.getElementById('pagoReferencia')?.value?.trim() || '';

        setLoading('btnGuardarPago', true);
        try {
            const res = await callApi(APP_URL + 'Public/api/facturas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ action: 'pagar', factura_id: facturaIdVal, monto: monto, metodo_pago: metodoPago, tasa_usada: tasaUsada, ref_pago_movil: refPagoMovil })
            });
            if (res.success) {
                toast('Pago registrado exitosamente.', 'success');
                document.getElementById('pagoModal').style.display = 'none';
                // Si la factura se cerró con este pago, cambiar a pestaña pagadas
                if (res.data?.estadoFactura === 'cerrada') {
                    currentTab = 'pagadas';
                }
                cargarFacturas(currentTab);
                // Refrescar detalle si había una factura seleccionada
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
    // Abrir recibo de factura en nueva pestaña (original o copia)
    document.getElementById('btnImprimirOriginal')?.addEventListener('click', function () {
        if (!facturaId) return;
        window.open(APP_URL + 'factura-recibo?id=' + facturaId, '_blank');
    });
    document.getElementById('btnImprimirCopia')?.addEventListener('click', function () {
        if (!facturaId) return;
        window.open(APP_URL + 'factura-recibo?id=' + facturaId + '&copia=1', '_blank');
    });

    // ── Cerrar factura ───────────────────────────────────
    // Abre el modal de confirmación para cerrar una factura activa
    document.getElementById('btnCerrarFactura')?.addEventListener('click', function () {
        if (!facturaId) return;
        document.getElementById('cerrarFacturaModal').style.display = 'flex';
    });

    // Confirmar cierre de factura: envía acción "cerrar" a la API
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

    // Botón cancelar del modal de cierre
    document.getElementById('cancelCerrarFactura')?.addEventListener('click', function () {
        document.getElementById('cerrarFacturaModal').style.display = 'none';
    });

    // ── Anular factura ───────────────────────────────────
    // Abre el modal de anulación (requiere motivo obligatorio)
    document.getElementById('btnAnularFactura')?.addEventListener('click', function () {
        if (!facturaId) return;
        document.getElementById('anularMotivo').value = '';
        document.getElementById('anularFacturaModal').style.display = 'flex';
    });

    // Botón cancelar del modal de anulación
    document.getElementById('cancelAnularFactura')?.addEventListener('click', function () {
        document.getElementById('anularFacturaModal').style.display = 'none';
    });

    // Envío del formulario de anulación a la API
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
    // Evento click genérico para cerrar cualquier modal con botón X
    document.querySelectorAll('.close-modal').forEach(el => {
        el.addEventListener('click', function () {
            this.closest('.modal').style.display = 'none';
        });
    });

    // ── Load initial data ────────────────────────────────
    // Carga inicial de facturas pendientes al entrar a la vista
    cargarFacturas('pendientes');
});

// ──────────────────────────────────────────────────────────
// Sidebar: Cargar facturas por tab (pendientes, abiertas, pagadas, canceladas)
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

// Renderiza la lista de facturas en la sidebar con formato de tarjeta
function renderSidebarList(facturas) {
    const list = document.getElementById('sidebarList');
    if (!facturas.length) {
        list.innerHTML = '<div class="sidebar-empty">No hay facturas en esta sección.</div>';
        return;
    }

    // Formato especial para pestaña canceladas: muestra estado rojo y monto pagado
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

    // Formato para pestañas pendientes, abiertas y pagadas
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

// Filtra la lista de facturas por texto de búsqueda (ID, nombre, cédula, estado)
function filtrarFacturas(query) {
    const q = query.toLowerCase().trim();
    if (!q) {
        renderSidebarList(allFacturas);
        return;
    }
    const filtradas = allFacturas.filter(f => {
        const searchStr = (f.facturaId ? '#' + String(f.facturaId) : 'Cita #' + String(f.citaId)) + ' ' + (f.clienteNombre || '') + ' ' + (f.clienteCedula || '') + ' ' + (f.estado || '');
        return searchStr.toLowerCase().includes(q);
    });
    renderSidebarList(filtradas);
}

// ──────────────────────────────────────────────────────────
// Seleccionar factura de la sidebar: carga el detalle correspondiente
// ──────────────────────────────────────────────────────────
function seleccionarFactura(el) {
    const fid = parseInt(el.dataset.facturaId);
    const cid = parseInt(el.dataset.citaId);
    selectedFacturaId = fid;
    // Actualizar clase active en la lista
    document.querySelectorAll('.factura-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');

    // Canceladas: renderizado especial con vista de solo lectura
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

    // Si tiene factura: cargar detalle de la factura
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
    // Si es pendiente (solo cita): cargar detalle de la cita para pre-llenar factura
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

// Oculta todos los paneles de detalle (mostrando el placeholder vacío)
function ocultarDetalle() {
    document.getElementById('detailEmpty').style.display = 'block';
    document.getElementById('detailContent').style.display = 'none';
    document.getElementById('detailCancelada').style.display = 'none';
}

// ──────────────────────────────────────────────────────────
// Renderizar detalle de cancelada: vista de solo lectura con datos de la anulación
// ──────────────────────────────────────────────────────────
function renderDetalleCancelada(d, fid) {
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'none';
    document.getElementById('detailCancelada').style.display = 'block';

    const g = d.general || {};
    // Calcular total pagado en VES sumando cada pago multiplicado por su tasa
    const totalPagadoVes = (d.pagos || []).reduce((sum, p) => sum + (parseFloat(p.monto) || 0) * (parseFloat(p.tasaUsada) || 0), 0);
    const totalFactura = parseFloat(g.totalFactura) || 0;

    // Rellenar campos de la vista de cancelada
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
// Renderizar detalle de factura: panel principal con 3 cards
// Card 1: datos de la cita/cliente, Card 2: desglose de materiales, Card 3: pagos
// ──────────────────────────────────────────────────────────
function renderDetalleFactura(d, fid) {
    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'block';
    document.getElementById('detailCancelada').style.display = 'none';

    // Actualizar variables de estado global
    TASA_BCV = d.tasaBcv || APP_CONFIG.bcvRate || 0;
    facturaId = d.general?.facturaId || fid || null;
    facturaEstado = d.general?.facturaEstado || null;
    facturaCitaId = d.general?.citaId || null;
    materialesCache = d.materiales || [];

    // Badge de estado de la factura (activa, cerrada, anulada)
    const badge = document.getElementById('facturaEstadoBadge');
    if (facturaId && facturaEstado && badge) {
        badge.style.display = 'inline-block';
        badge.textContent = facturaEstado.charAt(0).toUpperCase() + facturaEstado.slice(1);
        badge.className = 'estado-badge estado-' + facturaEstado;
    } else if (badge) {
        badge.style.display = 'none';
    }

    // ── Card 1: Datos de la cita y cliente ───────────────
    const g = d.general || {};
    document.getElementById('card1-cliente').textContent = g.clienteNombre || '—';
    document.getElementById('card1-cita-id').textContent = '#' + (g.citaId || '—');
    document.getElementById('card1-cedula').textContent = g.clienteCedula || '—';
    document.getElementById('card1-telefono').textContent = g.clienteTelefono || '—';
    document.getElementById('card1-fecha').textContent = g.fechaHoraInicio ? g.fechaHoraInicio.replace('T', ' ') : '—';
    document.getElementById('card1-ubicacion').textContent = g.ubicacion || '—';
    document.getElementById('card1-evento').textContent = g.eventType || '—';
    document.getElementById('card1-estado-cita').textContent = g.citaEstado || '—';

    // Mostrar/ocultar fila "Atendido por" según disponibilidad
    const atendidoRow = document.getElementById('card1-atendido-row');
    const atendidoEl = document.getElementById('card1-atendido');
    if (g.createdByName) {
        atendidoRow.style.display = 'flex';
        atendidoEl.textContent = g.createdByName;
    } else {
        atendidoRow.style.display = 'none';
    }

    // Mostrar/ocultar fila de notas
    const notasRow = document.getElementById('card1-notas-row');
    const notasEl = document.getElementById('card1-notas');
    if (g.notas && g.notas.trim()) {
        notasRow.style.display = 'flex';
        notasEl.textContent = g.notas;
    } else {
        notasRow.style.display = 'none';
    }

    // ── Desglose Table (Card 2) ──────────────────────────
    // Construir tabla de materiales con subtotal e IVA por línea
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
    // Mensaje cuando no hay materiales asignados
    if (!mats.length) {
        if (g.motivoSinMateriales && g.motivoSinMateriales.trim()) {
            html = '<tr><td colspan="7" style="padding:16px;color:#6c757d;font-style:italic;"><strong>Sin materiales.</strong> Motivo: ' + escapeHtml(g.motivoSinMateriales) + '</td></tr>';
        } else {
            html = '<tr><td colspan="7" class="text-center" style="color:var(--gray);padding:20px;">Sin materiales asignados</td></tr>';
        }
    }
    document.getElementById('tabla-desglose').innerHTML = html;

    // Cálculo de totales: materiales + servicio + IVA
    const costoServicio = parseFloat(g.costoServicio) || 0;
    const subtotal = totalMat + costoServicio;
    const iva = subtotal * APP_CONFIG.ivaRate;
    const totalFacturaCalc = subtotal + iva;
    document.getElementById('card2-costo-servicio').textContent = costoServicio.toFixed(2) + ' Bs';
    document.getElementById('card2-iva').textContent = iva.toFixed(2) + ' Bs';
    document.getElementById('card2-total-valor').innerHTML = '<strong>' + totalMat.toFixed(2) + ' Bs</strong>';

    // ── Actions en Card 2 ────────────────────────────────
    // Muestra botones de acción según el estado de la factura
    const actionsDiv = document.getElementById('facturaActions');
    if (facturaId && facturaEstado === 'cerrada') {
        // Pagada: ocultar acciones
        actionsDiv.innerHTML = '';
        actionsDiv.style.display = 'none';
    } else if (facturaId) {
        // Factura activa: botón "Ver Factura"
        let actionsHtml = '';
        if (facturaEstado === 'activa') {
            actionsHtml += '<button type="button" class="btn btn-primary" id="btnVerFactura" style="flex:1;"><i class="fas fa-file-invoice"></i> Ver Factura</button>';
        }
        actionsDiv.innerHTML = actionsHtml;
        actionsDiv.style.display = actionsHtml ? 'flex' : 'none';
        document.getElementById('btnVerFactura')?.addEventListener('click', function () {
            window.open(APP_URL + 'factura-recibo?id=' + facturaId, '_blank');
        });
    } else {
        // Pendiente (sin factura): botón "Generar Factura"
        actionsDiv.innerHTML = '<button type="button" class="btn btn-primary" id="btnGenerarFactura" style="flex:1;"><i class="fas fa-file-invoice"></i> Generar Factura</button>';
        actionsDiv.style.display = 'flex';
        document.getElementById('btnGenerarFactura')?.addEventListener('click', function () {
            if (!facturaCitaId) return;
            document.getElementById('genFacturaCitaId').value = facturaCitaId;
            document.getElementById('generarFacturaModal').style.display = 'flex';
            actualizarPreviews(facturaCitaId);
        });
    }

    // ── Total pagado (para barra de progreso) ─────────
    // Suma cada pago multiplicado por la tasa usada al momento del pago
    const pagos = d.pagos || [];
    let totalPagadoVes = 0;
    pagos.forEach(p => {
        const monto = parseFloat(p.monto) || 0;
        const tasa = parseFloat(p.tasaUsada) || 0;
        totalPagadoVes += monto * tasa;
    });
    const saldoPendienteVes = Math.max(0, totalFacturaCalc - totalPagadoVes);

    // ── Facturas Asociadas (recibos) ─────────────────────
    // Renderiza tabla de facturas/recibos asociados a esta cita
    const facturasAsoc = d.recibos || [];
    const tbodyFact = document.getElementById('tabla-facturas-asociadas');
    if (!facturasAsoc.length && !g.facturaId) {
        tbodyFact.innerHTML = '<tr><td colspan="5" class="text-center" style="color:var(--gray);padding:15px;">Sin facturas asociadas</td></tr>';
    } else {
        let fHtml = '';
        // Factura principal primero
        if (g.facturaId) {
            const fechaFact = g.facturaCreatedAt ? g.facturaCreatedAt.slice(0, 10) : (g.fechaHoraInicio ? g.fechaHoraInicio.slice(0, 10) : '—');
            fHtml += '<tr>' +
                '<td>#' + String(g.facturaId).padStart(6, '0') + '</td>' +
                '<td>' + fechaFact + '</td>' +
                '<td class="text-right">' + totalFacturaCalc.toFixed(2) + '</td>' +
                '<td>Factura</td>' +
                '<td class="text-center"><button class="action-btn" onclick="window.open(\'' + APP_URL + 'factura-recibo?id=' + g.facturaId + '\',\'_blank\')" title="Imprimir"><i class="fas fa-print"></i></button></td>' +
                '</tr>';
        }
        // Después los recibos hijos
        facturasAsoc.forEach(r => {
            const fFecha = r.createdAt ? r.createdAt.slice(0, 10) : '—';
            const fMonto = parseFloat(r.totalFactura) || 0;
            fHtml += '<tr>' +
                '<td>#' + String(r.id).padStart(6, '0') + '</td>' +
                '<td>' + fFecha + '</td>' +
                '<td class="text-right">' + fMonto.toFixed(2) + '</td>' +
                '<td>Recibo</td>' +
                '<td class="text-center"><button class="action-btn" onclick="window.open(\'' + APP_URL + 'factura-recibo?id=' + r.id + '\',\'_blank\')" title="Imprimir Recibo"><i class="fas fa-print"></i></button></td>' +
                '</tr>';
        });
        if (!fHtml) {
            fHtml = '<tr><td colspan="5" class="text-center" style="color:var(--gray);padding:15px;">Sin facturas asociadas</td></tr>';
        }
        tbodyFact.innerHTML = fHtml;
    }

    // ── Card 3: Totales y estado de pago ─────────────────
    document.getElementById('card3-total-ves').textContent = totalFacturaCalc.toFixed(2) + ' Bs';
    document.getElementById('card3-pendiente-ves').textContent = saldoPendienteVes.toFixed(2) + ' Bs';

    // Barra de progreso de pagos (0% a 100%)
    const progressFill = document.getElementById('pagoProgressFill');
    const progressPct = totalFacturaCalc > 0 ? (totalPagadoVes / totalFacturaCalc * 100) : 0;
    progressFill.style.width = Math.min(100, progressPct) + '%';
    progressFill.className = 'pago-progress-fill ' + (progressPct >= 100 ? 'ok' : 'warning');
    document.getElementById('pago-progress-pagado').textContent = 'Pagado: ' + totalPagadoVes.toFixed(2) + ' Bs';
    document.getElementById('pago-progress-pendiente').textContent = 'Pendiente: ' + saldoPendienteVes.toFixed(2) + ' Bs';

    // Texto de estado del pago
    const statusEl = document.getElementById('status-pago');
    if (!facturaId || totalFacturaCalc <= 0) {
        statusEl.textContent = 'FACTURA POR GENERAR';
        statusEl.className = 'pago-status';
    } else if (saldoPendienteVes <= 0.01) {
        // Detectar sobrepago y mostrar mensaje de devolución
        const devMsg = (totalPagadoVes > totalFacturaCalc + 0.01) ? ' — Devolución: ' + (totalPagadoVes - totalFacturaCalc).toFixed(2) + ' Bs' : '';
        statusEl.textContent = '✓ FACTURA TOTALMENTE PAGADA' + devMsg;
        statusEl.className = 'pago-status pagada';
    } else {
        statusEl.textContent = 'PAGO PARCIAL — Pendiente: ' + saldoPendienteVes.toFixed(2) + ' Bs';
        statusEl.className = 'pago-status pendiente-pago';
    }

    // Pago Actions (Card 3) — Mostrar/ocultar botones según tab y estado
    const pagoActions = document.getElementById('pagoActions');
    const pagoActionsRO = document.getElementById('pagoActionsReadOnly');
    const btnAbono = document.getElementById('btnRegistrarAbonoPagos');
    const btnCerrar = document.getElementById('btnCerrarFactura');
    const btnAnular = document.getElementById('btnAnularFactura');

    if (currentTab === 'pagadas' || facturaEstado === 'cerrada') {
        // Pagada: solo vista de solo lectura (imprimir)
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'flex';
    } else if (facturaId && facturaEstado === 'activa') {
        // Activa: botones de acción habilitados
        pagoActions.style.display = 'flex';
        pagoActionsRO.style.display = 'none';
        // Ocultar "Registrar Abono" si ya está totalmente pagado
        if (btnAbono) { btnAbono.style.display = saldoPendienteVes <= 0.01 ? 'none' : ''; }
        if (btnCerrar) { btnCerrar.style.display = ''; }
        if (btnAnular) { btnAnular.style.display = ''; }
    } else {
        // Sin factura o estado desconocido: ocultar todos
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'none';
    }

    // Si no hay factura generada, ocultar acciones de pago
    if (!facturaId) {
        pagoActions.style.display = 'none';
        pagoActionsRO.style.display = 'none';
    }
}

// ──────────────────────────────────────────────────────────
// Modal: Registrar Abono — prepara y abre el modal de pago
// ──────────────────────────────────────────────────────────
function abrirModalPago() {
    if (!facturaId) return toast('Debe generar la factura primero.', 'warning');
    document.getElementById('pagoFacturaId').value = facturaId;
    document.getElementById('pagoMontoVes').value = '';
    document.getElementById('pagoTasa').value = TASA_BCV.toFixed(2);
    document.getElementById('pagoMetodo').value = 'efectivo';
    document.getElementById('pagoConversionRow').style.display = 'none';
    document.getElementById('pagoInputHint').textContent = 'Ingrese el monto en Bolívares';
    const label = document.getElementById('pagoLabel');
    if (label) label.textContent = 'Monto (Bs)';
    document.getElementById('pagoMontoVes').placeholder = '0,00';
    document.getElementById('pagoRefGroup').style.display = 'none';
    document.getElementById('pagoReferencia').value = '';
    document.getElementById('pagoModal').style.display = 'flex';
}

// ──────────────────────────────────────────────────────────
// Preview helpers: recalcula subtotales, IVA y total en tiempo real
// ──────────────────────────────────────────────────────────
function actualizarPreviews(citaId) {
    const costoServicio = parseFloat(document.getElementById('genCostoServicio').value) || 0;
    // Suma el subtotal de todos los materiales del caché
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
    // Mínimo 50% para anticipo
    document.getElementById('previewMinimo').textContent = fmt(totalCalculado * 0.50);
}

// ──────────────────────────────────────────────────────────
// Términos y Condiciones: carga el texto desde la API
// ──────────────────────────────────────────────────────────
async function cargarTerminos() {
    try {
        const res = await callApi(APP_URL + 'Public/api/facturas.php?action=terminos');
        const termsBody = document.getElementById('termsBody');
        if (res.success && res.data?.terminos) {
            const terms = res.data.terminos;
            const lines = terms.split('\n').filter(l => l.trim());
            if (lines.length) {
                // Convertir cada línea en un elemento <li>
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

// Wrapper async para obtener el detalle de una factura por ID
function asyncCargarDetalleFactura(facturaId) {
    const params = new URLSearchParams({ action: 'detalle-factura', factura_id: facturaId });
    return callApi(APP_URL + 'Public/api/facturas.php?' + params.toString());
}

// Carga y renderiza el detalle de una factura por su ID
async function cargarDetalleFactura(fid) {
    try {
        const res = await asyncCargarDetalleFactura(fid);
        if (res.success && res.data) {
            renderDetalleFactura(res.data, fid);
        }
    } catch (err) {
        // silencio
    }
}

// ──────────────────────────────────────────────────────────
// Utility: escapado HTML para prevenir XSS
// ──────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Alterna el estado de carga (deshabilitar/habilitar) de un botón
function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.classList.toggle('btn-loading', loading);
}
