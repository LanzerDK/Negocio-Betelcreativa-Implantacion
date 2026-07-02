let citasList = [];
let todasLasCitas = [];
let canceladasList = [];
let clientesList = [];
let tiposEventoList = [];
let materialesDisponibles = [];
let materialesAsignados = [];
let paginaActual = 1;
const itemsPorPagina = 10;
let calendario = null;
let modoMaterial = 'nuevo'; // 'nuevo' | 'editar'
let citaEditandoId = null;
let facturaEstadoCita = null;
let citaMaterialesOriginales = []; // snapshot al abrir edición (para cálculo de stock disponible)

// ==================== HELPERS TIMEZONE VET ====================

function ahoraEnCaracas() {
    return new Date(new Date().toLocaleString('en-US', { timeZone: 'America/Caracas' }));
}

function sumarDiasHabiles(desde, dias) {
    const result = new Date(desde);
    let contados = 0;
    while (contados < dias) {
        result.setDate(result.getDate() + 1);
        const diaSem = result.getDay();
        if (diaSem !== 0 && diaSem !== 6) contados++;
    }
    result.setHours(23, 59, 59, 999);
    return result;
}

function esRestaurable(fechaCancelacion, fechaHoraInicio) {
    if (!fechaCancelacion) return false;
    const ahora = ahoraEnCaracas();
    if (ahora >= new Date(fechaHoraInicio)) return false;
    const fechaLimite = sumarDiasHabiles(new Date(fechaCancelacion), 3);
    return ahora <= fechaLimite;
}

// ==================== CARGA INICIAL ====================

async function fetchTiposEvento()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/event-types.php?_=' + Date.now());
        if (data.success) {
            tiposEventoList = data.data;
            poblarSelectoresTipoEvento();
        }
    } catch (err) {
        console.error('Error al cargar tipos de evento:', err);
    }
}

function poblarSelectoresTipoEvento()
{
    const selects = [
        document.getElementById('newEventType'),
        document.getElementById('editEventType'),
        document.getElementById('eventTypeFilter')
    ];
    selects.forEach(sel => {
        if (!sel) return;
        const valActual = sel.value;
        if (sel.id === 'eventTypeFilter') {
            sel.innerHTML = '<option value="">Todos</option>';
        } else {
            sel.innerHTML = '<option value="">Seleccionar tipo...</option>';
        }
        tiposEventoList.forEach(et => {
            const opt = document.createElement('option');
            opt.value = et.id;
            opt.textContent = et.name;
            sel.appendChild(opt);
        });
        if (valActual && sel.querySelector(`option[value="${valActual}"]`)) {
            sel.value = valActual;
        }
    });
}

async function fetchClientes()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php?_=' + Date.now());
        if (data.success) {
            clientesList = data.data;
            poblarSelectoresCliente();
        }
    } catch (err) {
        console.error('Error al cargar clientes:', err);
    }
}

function poblarSelectoresCliente()
{
    const selects = [
        document.getElementById('newClient'),
        document.getElementById('editClient')
    ];
    const activos = clientesList.filter(c => c.isActive == true || c.isActive === true);
    selects.forEach(sel => {
        if (!sel) return;
        sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
        if (activos.length === 0) {
            sel.innerHTML = '<option value="">No hay clientes activos</option>';
            return;
        }
        activos.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = (c.firstName || '') + ' ' + (c.lastName || '');
            sel.appendChild(opt);
        });
    });
}

async function fetchCitas()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/appointments.php?_=' + Date.now());
        if (data.success) {
            citasList = data.data;
            renderizarTabla(paginaActual);
            if (calendario) calendario.refetchEvents();
        }
    } catch (err) {
        console.error('Error al cargar citas:', err);
    }
}

async function fetchTodasLasCitas()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/appointments.php?todos=1&_=' + Date.now());
        if (data.success) {
            todasLasCitas = data.data;
            if (calendario) calendario.refetchEvents();
        }
    } catch (err) {
        console.error('Error al cargar todas las citas:', err);
    }
}

async function fetchCanceladas()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/appointments.php?canceladas=1&_=' + Date.now());
        if (data.success) {
            canceladasList = data.data;
            renderizarHistorial();
        }
    } catch (err) {
        console.error('Error al cargar canceladas:', err);
    }
}

// ==================== UTILIDADES ====================

function obtenerNombreCliente(id)
{
    const c = clientesList.find(c => c.id === id);
    return c ? (c.firstName + ' ' + c.lastName).trim() : 'Cliente #' + id;
}

function obtenerAvatarCliente(id)
{
    const c = clientesList.find(c => c.id === id);
    return c?.avatar || 'https://i.imgur.com/1As0akH.jpg';
}

function obtenerTelefonoCliente(id)
{
    const c = clientesList.find(c => c.id === id);
    return c?.phone || '';
}

function textoEstado(estado)
{
    return estado || '—';
}

function classNameEstado(estado)
{
    return (estado || '').replace(/\s+/g, '-').toLowerCase();
}

function calcularMaxDisponible(material, yaAsignado)
{
    return Math.min(material.stock, material.stock - (material.reservedStock || 0) + (yaAsignado || 0));
}

function formatearFechaHora(datetime)
{
    if (!datetime) return '—';
    const d = new Date(datetime.replace(' ', 'T'));
    return d.toLocaleDateString('es-ES') + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

// ==================== TABLA PRINCIPAL ====================

function renderizarTabla(pagina)
{
    const tabla = document.querySelector('.appointments-table');
    if (!tabla) return;

    const filasExistentes = document.querySelectorAll('.table-row:not(.table-header)');
    filasExistentes.forEach(r => r.remove());

    const total = citasList.length;
    const totalPaginas = Math.ceil(total / itemsPorPagina) || 1;
    const inicio = (pagina - 1) * itemsPorPagina;
    const fin = Math.min(inicio + itemsPorPagina, total);
    const itemsPagina = citasList.slice(inicio, fin);

    const mostrarInicio = document.getElementById('showingStart');
    const mostrarFin = document.getElementById('showingEnd');
    const totalSpan = document.getElementById('totalAppointments');
    if (mostrarInicio) mostrarInicio.textContent = total > 0 ? inicio + 1 : 0;
    if (mostrarFin) mostrarFin.textContent = fin;
    if (totalSpan) totalSpan.textContent = total;

    const contPaginas = document.getElementById('pageButtons');
    if (contPaginas) {
        contPaginas.innerHTML = '';
        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (i === pagina ? ' active' : '');
            btn.textContent = i;
            btn.addEventListener('click', () => { paginaActual = i; renderizarTabla(paginaActual); });
            contPaginas.appendChild(btn);
        }
    }
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (prevBtn) prevBtn.disabled = pagina <= 1;
    if (nextBtn) nextBtn.disabled = pagina >= totalPaginas;

    itemsPagina.forEach(cita => {
        const nombre = obtenerNombreCliente(cita.clienteId);
        const telefono = obtenerTelefonoCliente(cita.clienteId);
        const avatar = obtenerAvatarCliente(cita.clienteId);
        const fechaHora = formatearFechaHora(cita.fechaHoraInicio);

        const fila = document.createElement('div');
        fila.className = 'table-row';
        fila.dataset.id = cita.id;
        fila.dataset.estado = cita.estado;

        const esCancelado = cita.estado === 'Cancelado';
        const esFinalizada = cita.estado === 'Finalizada';

        fila.innerHTML = `
            <div class="col-1">#${cita.id}</div>
            <div class="col-2" style="display:flex;align-items:center;gap:15px;">
                <div class="client-img">
                    <img src="${avatar}" alt="Cliente" onerror="this.src='https://i.imgur.com/1As0akH.jpg'">
                </div>
                <div>
                    <strong>${nombre}</strong>
                    <div style="font-size:0.85rem;color:var(--gray)">${telefono}</div>
                </div>
            </div>
            <div class="col-3">${fechaHora}</div>
            <div class="col-4"><span class="event-type">${cita.eventType || '—'}</span></div>
            <div class="col-5">${cita.ubicacion || '—'}</div>
            <div class="col-6"><span class="status ${classNameEstado(cita.estado)}">${textoEstado(cita.estado)}</span></div>
            <div class="col-7" style="display:flex;gap:10px;">
                ${!esCancelado && !esFinalizada ? `<button class="action-btn edit" data-id="${cita.id}"><i class="fas fa-edit"></i></button>` : ''}
                ${!esCancelado && !esFinalizada ? `<button class="action-btn cancel-btn" data-id="${cita.id}" title="Cancelar cita"><i class="fas fa-ban"></i></button>` : ''}
                ${esCancelado && esRestaurable(cita.fechaHoraCancelacion, cita.fechaHoraInicio) ? `<button class="action-btn restore-btn" data-id="${cita.id}" title="Restaurar cita"><i class="fas fa-undo"></i></button>` : ''}
            </div>
        `;

        tabla.appendChild(fila);
        agregarListenersFila(fila, cita.id);
    });
}

function agregarListenersFila(fila, id)
{
    const editBtn = fila.querySelector('.action-btn.edit');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            const cita = citasList.find(c => c.id === id);
            if (cita) abrirModalEdicion(cita);
        });
    }

    const cancelBtn = fila.querySelector('.action-btn.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => abrirModalCancelacion(id));
    }

    const restoreBtn = fila.querySelector('.action-btn.restore-btn');
    if (restoreBtn) {
        restoreBtn.addEventListener('click', () => restaurarCita(id));
    }
}

// ==================== FILTROS ====================

function filtrarCitas()
{
    const searchVal = document.getElementById('searchInput')?.value?.toLowerCase() || '';
    const typeFilter = document.getElementById('eventTypeFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const dateFrom = document.getElementById('filterDateFrom')?.value || '';
    const dateTo = document.getElementById('filterDateTo')?.value || '';

    document.querySelectorAll('.table-row:not(.table-header)').forEach(fila => {
        const id = parseInt(fila.dataset.id);
        const cita = citasList.find(c => c.id === id);
        if (!cita) { fila.style.display = 'none'; return; }
        const nombre = obtenerNombreCliente(cita.clienteId).toLowerCase();
        const coincideSearch = nombre.includes(searchVal);
        const coincideTipo = !typeFilter || (String(cita.eventTypeId || '')) === typeFilter;
        const coincideEstado = !statusFilter || (cita.estado || '') === statusFilter;
        const fInicio = cita.fechaHoraInicio ? cita.fechaHoraInicio.split(' ')[0] : '';
        const coincideFecha = (!dateFrom || fInicio >= dateFrom) && (!dateTo || fInicio <= dateTo);
        fila.style.display = (coincideSearch && coincideTipo && coincideEstado && coincideFecha) ? 'grid' : 'none';
    });
}

// ==================== CALENDARIO ====================

function initCalendar()
{
    const el = document.getElementById('calendar');
    if (!el) return;

    calendario = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: false,
        editable: true,
        events: function(fetchInfo, successCallback) {
            const lista = todasLasCitas.length > 0 ? todasLasCitas : citasList;
            const eventos = lista.map(c => ({
                id: String(c.id),
                title: obtenerNombreCliente(c.clienteId) + ' - ' + (c.eventType || 'Evento'),
                start: c.fechaHoraInicio ? c.fechaHoraInicio.replace(' ', 'T') : '',
                end: c.fechaHoraFin ? c.fechaHoraFin.replace(' ', 'T') : '',
                className: 'fc-event-' + classNameEstado(c.estado || 'En Proceso'),
                extendedProps: { ubicacion: c.ubicacion || '', estado: c.estado || '', notas: c.notas || '' }
            }));
            successCallback(eventos);
        },
        eventClick: function(info) {
            const id = parseInt(info.event.id);
            const lista = todasLasCitas.length > 0 ? todasLasCitas : citasList;
            const cita = lista.find(c => c.id === id);
            if (cita) abrirModalEdicion(cita);
        },
        datesSet: function() { actualizarTituloCalendario(); }
    });

    calendario.render();
    actualizarTituloCalendario();
}

function actualizarTituloCalendario()
{
    const titulo = document.getElementById('calendarTitle');
    if (calendario && titulo) {
        const opts = { year: 'numeric', month: 'long' };
        titulo.textContent = calendario.view.currentStart.toLocaleDateString('es-ES', opts);
    }
}

// ==================== MODAL NUEVA CITA ====================

function abrirModalNueva()
{
    if (clientesList.length === 0) {
        toast('Debe registrar al menos un cliente.', 'warning');
        return;
    }
    materialesAsignados = [];
    document.getElementById('newMaterialCount').textContent = '';
    document.getElementById('newSubmitBtn').disabled = false;
    document.getElementById('newAppointmentModal').style.display = 'flex';
    poblarSelectoresCliente();
    const form = document.getElementById('newAppointmentForm');
    if (form) form.reset();
    // Bloquear fechas pasadas
    const hoy = new Date().toISOString().slice(0, 10);
    const fechaInicio = document.getElementById('newFechaInicio');
    if (fechaInicio) fechaInicio.min = hoy;
    const fechaFin = document.getElementById('newFechaFin');
    if (fechaFin) fechaFin.min = hoy;
    // Resetear hora a 8:00 AM por defecto
    ['newHoraInicio_h','newHoraFin_h'].forEach(id => { const s=document.getElementById(id); if(s) s.value='8'; });
    ['newHoraInicio_m','newHoraFin_m'].forEach(id => { const s=document.getElementById(id); if(s) s.value='00'; });
    ['newHoraInicio_a','newHoraFin_a'].forEach(id => { const s=document.getElementById(id); if(s) s.value='AM'; });
}

// ==================== MODAL EDICIÓN ====================

async function abrirModalEdicion(cita)
{
    citaEditandoId = cita.id;
    document.getElementById('editModal').style.display = 'flex';

    // Deshabilitar botón "Asignar Materiales" hasta que los datos estén listos
    const asignarBtn = document.getElementById('editAsignarMateriales');
    if (asignarBtn) asignarBtn.disabled = true;

    poblarSelectoresCliente();
    poblarSelectoresTipoEvento();

    const asignar = (id, valor) => { const el = document.getElementById(id); if (el) el.value = valor ?? ''; };
    asignar('editId', cita.id);
    asignar('editClient', cita.clienteId);
    if (cita.fechaHoraInicio) {
        const p = cita.fechaHoraInicio.replace(' ', 'T').split('T');
        asignar('editFechaInicio', p[0] || '');
        if (p[1]) {
            const t = hora24a12(p[1].slice(0, 5));
            asignar('editHoraInicio_h', t.h);
            asignar('editHoraInicio_m', t.m);
            asignar('editHoraInicio_a', t.ap);
        }
    }
    if (cita.fechaHoraFin) {
        const p = cita.fechaHoraFin.replace(' ', 'T').split('T');
        asignar('editFechaFin', p[0] || '');
        if (p[1]) {
            const t = hora24a12(p[1].slice(0, 5));
            asignar('editHoraFin_h', t.h);
            asignar('editHoraFin_m', t.m);
            asignar('editHoraFin_a', t.ap);
        }
    }
    asignar('editEventType', cita.eventTypeId);
    asignar('editUbicacion', cita.ubicacion);
    asignar('editNotas', cita.notas);

    // Motivo sin materiales
    const sinMatCheck = document.getElementById('editSinMateriales');
    const sinMatTextarea = document.getElementById('editMotivoSinMateriales');
    if (cita.motivoSinMateriales) {
        if (sinMatCheck) sinMatCheck.checked = true;
        if (sinMatTextarea) sinMatTextarea.value = cita.motivoSinMateriales;
        toggleSinMateriales('editar');
    }

    const clientSelect = document.getElementById('editClient');
    if (clientSelect) {
        clientSelect.disabled = true;
        clientSelect.style.opacity = '0.8';
        clientSelect.style.cursor = 'not-allowed';
    }

    // Cargar materiales asignados (await para evitar race condition)
    document.getElementById('editMaterialCount').textContent = '';
    const mats = await cargarMaterialesCita(cita.id);
    materialesAsignados = mats;
    citaMaterialesOriginales = JSON.parse(JSON.stringify(mats));
    actualizarContadorMateriales('edit');
    const panel = document.getElementById('editMaterialPanel');
    if (panel && panel.style.display === 'flex') {
        actualizarSelectMateriales('editar');
        renderizarListaMateriales('editar');
    }
    if (asignarBtn) asignarBtn.disabled = false;

    // Bloquear edición de materiales si la factura está cerrada
    if (facturaEstadoCita === 'cerrada') {
        if (asignarBtn) {
            asignarBtn.disabled = true;
            asignarBtn.title = 'Factura cerrada — no se pueden modificar materiales';
        }
        const sinMatCheck = document.getElementById('editSinMateriales');
        if (sinMatCheck) sinMatCheck.disabled = true;
        const sinMatTextarea = document.getElementById('editMotivoSinMateriales');
        if (sinMatTextarea) sinMatTextarea.disabled = true;
    }
}

async function cargarMaterialesCita(citaId)
{
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php?id=' + citaId + '&_=' + Date.now());
        if (res.success && res.data) {
            facturaEstadoCita = res.data.facturaEstado || null;
            const mats = res.data.materiales || [];
            return mats.map(m => ({
                materialId: m.materialId || m.materialid,
                cantidad: m.cantidad || m.cantidadUtilizada || m.cantidadutilizada || 0
            }));
        }
    } catch (e) { /* silencio */ }
    facturaEstadoCita = null;
    return [];
}

// ==================== MATERIALES (panel lateral con select + "+") ====================

let todosMateriales = []; // todos los materiales activos (cargados una vez)

async function cargarSelectMateriales()
{
    try {
        const res = await callApi(APP_URL + 'Public/api/materials.php?_=' + Date.now());
        if (!res.success) return;
        todosMateriales = res.data.filter(m => m.is_active == 1 || m.is_active === true);
        const selects = [
            document.getElementById('newMaterialSelect'),
            document.getElementById('editMaterialSelect')
        ];
        selects.forEach(sel => {
            if (!sel) return;
            sel.innerHTML = '<option value="">Seleccionar material...</option>';
            todosMateriales.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = (m.code || '—') + ' - ' + m.name;
                sel.appendChild(opt);
            });
        });
    } catch (e) {
        console.error('Error al cargar materiales:', e);
    }
}

function abrirModalMateriales(modo)
{
    modoMaterial = modo;
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    const panel = document.getElementById(prefix + 'MaterialPanel');
    if (!panel) return;
    // Cargar el select si está vacío
    const sel = document.getElementById(prefix + 'MaterialSelect');
    if (sel && sel.options.length <= 1 && todosMateriales.length > 0) {
        sel.innerHTML = '<option value="">Seleccionar material...</option>';
        todosMateriales.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = (m.code || '—') + ' - ' + m.name;
            sel.appendChild(opt);
        });
    }
    // Limpiar filtro
    const filter = document.getElementById(prefix + 'MaterialFilter');
    if (filter) filter.value = '';
    panel.style.display = 'flex';
    actualizarSelectMateriales(modo);
    renderizarListaMateriales(modo);
}

function agregarMaterial(modo)
{
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    const sel = document.getElementById(prefix + 'MaterialSelect');
    if (!sel) return;
    const materialId = parseInt(sel.value);
    if (!materialId) { toast('Seleccione un material.', 'warning'); return; }
    const material = todosMateriales.find(m => m.id === materialId);
    if (!material) return;
    const existente = materialesAsignados.find(a => a.materialId === materialId);
    if (existente) {
        toast('Este material ya fue agregado.', 'warning');
        sel.value = '';
        return;
    }
    materialesAsignados.push({ materialId: materialId, cantidad: 1 });
    sel.value = '';
    actualizarSelectMateriales(modo);
    renderizarListaMateriales(modo);
}

function eliminarMaterial(modo, materialId)
{
    materialesAsignados = materialesAsignados.filter(a => a.materialId !== materialId);
    renderizarListaMateriales(modo);
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    actualizarContadorMateriales(prefix);
    actualizarSelectMateriales(modo);
}

function actualizarSelectMateriales(modo)
{
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    const sel = document.getElementById(prefix + 'MaterialSelect');
    if (!sel) return;
    const idsAsignados = materialesAsignados.map(a => a.materialId);
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        const id = parseInt(opt.value);
        if (idsAsignados.includes(id)) {
            opt.disabled = true;
        } else {
            opt.disabled = false;
        }
    });
}

function renderizarListaMateriales(modo)
{
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    const cont = document.getElementById(prefix + 'MaterialList');
    if (!cont) return;
    const filtro = (document.getElementById(prefix + 'MaterialFilter')?.value || '').toLowerCase();

    const idsAsignados = materialesAsignados.map(a => a.materialId);
    let aRenderizar = materialesAsignados.filter(a => {
        const m = todosMateriales.find(mat => mat.id === a.materialId);
        if (!m) return false;
        const texto = (m.name + ' ' + (m.code || '')).toLowerCase();
        return texto.includes(filtro);
    });

    if (aRenderizar.length === 0) {
        cont.innerHTML = '<p class="material-empty">' +
            (filtro ? 'No se encontraron materiales.' : 'Presione <strong>+</strong> para agregar materiales.') +
            '</p>';
        return;
    }

    cont.innerHTML = aRenderizar.map(asig => {
        const m = todosMateriales.find(mat => mat.id === asig.materialId);
        if (!m) return '';
        const yaAsignado = modo === 'editar'
            ? (citaMaterialesOriginales.find(o => o.materialId === asig.materialId)?.cantidad || 0)
            : 0;
        const maxDisponible = calcularMaxDisponible(m, yaAsignado);
        const excede = asig.cantidad > maxDisponible;
        // NEW: mostrar stock real de BD; EDIT: mostrar valores simulados (lo que quedará tras guardar)
        let dispText, reservText;
        if (modo === 'nuevo') {
            dispText = m.stock - (m.reservedStock || 0);
            reservText = m.reservedStock || 0;
        } else {
            const reservadoSimulado = (m.reservedStock || 0) - yaAsignado + asig.cantidad;
            dispText = m.stock - reservadoSimulado;
            reservText = reservadoSimulado;
        }
        return `
            <div class="material-item${excede ? ' excede' : ''}" data-id="${m.id}" data-stock="${m.stock}" data-reserved="${m.reservedStock}" data-original-cant="${yaAsignado}">
                <button type="button" class="material-remove" title="Quitar material">&times;</button>
                <div class="material-info">
                    <strong>${m.code || '—'}</strong>
                    <small>${m.name}</small>
                </div>
                <div class="material-stock">Disponible: <strong>${dispText}</strong> | Reservado: ${reservText}</div>
                <div class="material-qty">
                    <input type="number" class="form-input material-cantidad" min="1" max="${maxDisponible}" value="${asig.cantidad}">
                </div>
                <div class="stock-warning"${excede ? '' : ' style="display:none;"'}>¡Stock Insuficiente!</div>
            </div>
        `;
    }).join('');

    // Eventos: cantidad
    cont.querySelectorAll('.material-cantidad').forEach(input => {
        input.addEventListener('input', function() {
            const item = this.closest('.material-item');
            const stock = parseInt(item.dataset.stock);
            const reserved = parseInt(item.dataset.reserved) || 0;
            const yaAsignado = parseInt(item.dataset.originalCant) || 0;
            const maxDisponible = Math.min(stock, stock - reserved + yaAsignado);
            const cant = parseInt(this.value) || 0;
            const warning = item.querySelector('.stock-warning');
            const asig = materialesAsignados.find(a => a.materialId === parseInt(item.dataset.id));
            if (asig) asig.cantidad = cant;
            // Actualizar display en tiempo real (solo en edición, en nuevo se muestra stock real)
            if (modo === 'editar') {
                const nuevoReservado = reserved - yaAsignado + cant;
                const nuevoDisponible = stock - nuevoReservado;
                const stockEl = item.querySelector('.material-stock');
                if (stockEl) stockEl.innerHTML = 'Disponible: <strong>' + nuevoDisponible + '</strong> | Reservado: ' + nuevoReservado;
            }
            if (cant < 1) {
                this.style.background = '#ffcccc';
                this.style.borderColor = 'red';
                warning.textContent = 'La cantidad mínima es 1.';
                warning.style.display = '';
                item.classList.add('excede');
                deshabilitarSubmit(true);
            } else if (cant > maxDisponible) {
                this.style.background = '#ffcccc';
                this.style.borderColor = 'red';
                warning.textContent = '¡Stock Insuficiente!';
                warning.style.display = '';
                item.classList.add('excede');
                deshabilitarSubmit(true);
            } else {
                this.style.background = '';
                this.style.borderColor = '';
                warning.style.display = 'none';
                item.classList.remove('excede');
                deshabilitarSubmit(false);
            }
            actualizarContadorMateriales(prefix);
        });
    });
    // Eventos: eliminar
    cont.querySelectorAll('.material-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.closest('.material-item').dataset.id);
            eliminarMaterial(modo, id);
        });
    });
    actualizarTotalMateriales(prefix);
}

function deshabilitarSubmit(deshabilitado)
{
    const btnNuevo = document.getElementById('newSubmitBtn');
    const btnEditar = document.getElementById('editSubmitBtn');
    if (btnNuevo) btnNuevo.disabled = deshabilitado;
    if (btnEditar) btnEditar.disabled = deshabilitado;
}

function confirmarMateriales(modo)
{
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    // Validar stock antes de confirmar
    for (const asig of materialesAsignados) {
        const m = todosMateriales.find(mat => mat.id === asig.materialId);
        if (!m) continue;
        if (asig.cantidad < 1) {
            toast('La cantidad mínima para "' + (m.code || m.name) + '" es 1.', 'warning');
            return;
        }
        // Para nueva cita, yaAsignado = 0; para editar, usar la cantidad original que tenía esta cita
        const orig = citaMaterialesOriginales.find(o => o.materialId === asig.materialId);
        const yaAsignado = orig ? orig.cantidad : 0;
        const maxDisponible = calcularMaxDisponible(m, yaAsignado);
        if (maxDisponible <= 0 && asig.cantidad > 0) {
            toast('El material "' + (m.code || m.name) + '" no tiene stock disponible.', 'warning');
            return;
        }
        if (asig.cantidad > maxDisponible) {
            toast('Stock insuficiente para "' + (m.code || m.name) + '".', 'warning');
            return;
        }
    }
    actualizarContadorMateriales(prefix);
    document.getElementById(prefix + 'MaterialPanel').style.display = 'none';
    deshabilitarSubmit(false);
}

function hayCantidadInvalida()
{
    return materialesAsignados.some(a => a.cantidad < 1);
}

function cerrarPanelMateriales(modo)
{
    if (hayCantidadInvalida()) {
        toast('Corrija las cantidades inválidas antes de cerrar.', 'warning');
        return;
    }
    const prefix = modo === 'nuevo' ? 'new' : 'edit';
    document.getElementById(prefix + 'MaterialPanel').style.display = 'none';
    deshabilitarSubmit(false);
}

function actualizarContadorMateriales(prefix)
{
    const span = document.getElementById(prefix + 'MaterialCount');
    if (span) {
        const total = materialesAsignados.reduce((s, m) => s + m.cantidad, 0);
        span.textContent = materialesAsignados.length > 0 ? `(${materialesAsignados.length} items, ${total} uds.)` : '';
    }
}

function actualizarTotalMateriales(prefix)
{
    const el = document.getElementById(prefix + 'MaterialTotal');
    if (el) {
        const total = materialesAsignados.length;
        el.textContent = `Materiales totales: ${total}`;
    }
}

// ==================== CANCELACIÓN ====================

let cancelandoId = null;

function abrirModalCancelacion(id)
{
    cancelandoId = id;
    document.getElementById('cancelMotivo').value = '';
    document.getElementById('cancelModal').style.display = 'flex';
}

async function confirmarCancelacion()
{
    const motivo = document.getElementById('cancelMotivo')?.value?.trim();
    if (!motivo) {
        toast('Debe indicar el motivo de cancelación.', 'warning');
        return;
    }
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php?id=' + cancelandoId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ motivoCancelacion: motivo })
        });
        if (res.success) {
            toast('Cita cancelada exitosamente.', 'success');
            document.getElementById('cancelModal').style.display = 'none';
            paginaActual = 1;
            await fetchCitas();
            await fetchTodasLasCitas();
            await fetchCanceladas();
            await cargarSelectMateriales();
        } else {
            toast('Error: ' + res.message, 'error');
        }
    } catch (e) {
        toast(e.message || 'Error de conexión.', 'error');
    }
}

// ==================== RESTAURACIÓN ====================

async function restaurarCita(id)
{
    if (!confirm('¿Restaurar esta cita? Se verificará disponibilidad de materiales.')) return;
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php?id=' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ restaurar: true })
        });
        if (res.success) {
            toast('Cita restaurada exitosamente.', 'success');
            await fetchCitas();
            await fetchTodasLasCitas();
            await fetchCanceladas();
            await cargarSelectMateriales();
        } else {
            toast('Error: ' + res.message, 'error');
        }
    } catch (e) {
        toast(e.message || 'Error de conexión.', 'error');
    }
}

// ==================== API CALLS ====================

async function crearCita(datos)
{
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(datos)
        });
        if (res.success) {
            toast('Cita creada exitosamente.', 'success');
            await fetchCitas();
            await fetchTodasLasCitas();
            await cargarSelectMateriales();
        } else {
            toast('Error: ' + res.message, 'error');
        }
    } catch (e) {
        toast(e.message || 'Error de conexión.', 'error');
    }
}

async function actualizarCita(id, datos)
{
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php?id=' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify(datos)
        });
        if (res.success) {
            toast('Cita actualizada exitosamente.', 'success');
            await fetchCitas();
            await fetchTodasLasCitas();
            await cargarSelectMateriales();
        } else {
            toast('Error: ' + res.message, 'error');
        }
    } catch (e) {
        toast(e.message || 'Error de conexión.', 'error');
    }
}

// ==================== HISTORIAL ====================

function renderizarHistorial()
{
    const tbody = document.getElementById('historyBody');
    if (!tbody) return;

    if (canceladasList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--gray)">No hay citas canceladas.</td></tr>';
        return;
    }

    tbody.innerHTML = canceladasList.filter(c => c.estado === 'Cancelado').map(c => {
        const nombre = obtenerNombreCliente(c.clienteId);
        const telefono = obtenerTelefonoCliente(c.clienteId);
        const fechaHora = formatearFechaHora(c.fechaHoraInicio);
        const puedeRestaurar = esRestaurable(c.fechaHoraCancelacion, c.fechaHoraInicio);
        return `<tr>
            <td>#${c.id}</td>
            <td><strong>${nombre}</strong><br><small style="color:var(--gray)">${telefono}</small></td>
            <td>${fechaHora}</td>
            <td>${c.eventType || '—'}</td>
            <td>${c.ubicacion || '—'}</td>
            <td>
                ${puedeRestaurar ? `<button class="btn-reactivate" data-id="${c.id}"><i class="fas fa-undo"></i> Restaurar</button>` : '<span style="color:var(--gray);font-size:0.85rem;">Vencido</span>'}
            </td>
        </tr>`;
    }).join('');

    tbody.querySelectorAll('.btn-reactivate').forEach(btn => {
        btn.addEventListener('click', () => restaurarCita(parseInt(btn.dataset.id)));
    });
}

// ==================== HISTORIAL DE MATERIALES POR CITA ====================

async function fetchHistorialCita(citaId)
{
    try {
        const res = await callApi(APP_URL + 'Public/api/appointments.php?historial=1&id=' + citaId + '&_=' + Date.now());
        if (res.success) return res.data || [];
    } catch (e) { /* silencio */ }
    return [];
}

function renderHistorialCitaModal(data)
{
    const cont = document.getElementById('citaHistorialContent');
    if (!cont) return;
    if (!data || data.length === 0) {
        cont.innerHTML = '<p style="text-align:center;color:var(--gray);padding:20px;">Sin historial de materiales.</p>';
        return;
    }
    cont.innerHTML = '<div class="historial-timeline">' + data.map(h => {
        const fecha = h.created_at ? formatearFechaHora(h.created_at) : '—';
        const usuario = (h.first_name || '') + ' ' + (h.last_name || '');
        const matNombre = h.materialCode ? (h.materialCode + ' - ' + h.materialName) : (h.materialName || 'Material #' + h.material_id);
        let detalle = '';
        if (h.accion === 'Asignado') {
            detalle = 'Asignado <strong>' + h.cantidad_nueva + '</strong> uds.';
        } else if (h.accion === 'Modificado') {
            detalle = 'Modificado: <strong>' + h.cantidad_anterior + '</strong> → <strong>' + h.cantidad_nueva + '</strong> uds.';
        } else if (h.accion === 'Cancelado') {
            detalle = 'Liberadas <strong>' + h.cantidad_anterior + '</strong> uds.';
        } else if (h.accion === 'Ejecutado') {
            detalle = 'Descontadas <strong>' + h.cantidad_anterior + '</strong> uds.';
        } else {
            detalle = h.cantidad_anterior + ' → ' + h.cantidad_nueva;
        }
        return `<div class="historial-item historial-${h.accion.toLowerCase()}">
            <div class="historial-badge">${h.accion}</div>
            <div class="historial-body">
                <div class="historial-material">${matNombre}</div>
                <div class="historial-detalle">${detalle}</div>
                <div class="historial-meta">${usuario} · ${fecha} · Estado: ${h.estado_cita_momento}</div>
            </div>
        </div>`;
    }).join('') + '</div>';
}

function abrirHistorialCita(citaId)
{
    const modal = document.getElementById('citaHistorialModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.getElementById('citaHistorialContent').innerHTML = '<p style="text-align:center;padding:20px;">Cargando...</p>';
    fetchHistorialCita(citaId).then(data => renderHistorialCitaModal(data));
}

// ==================== TIPOS DE EVENTO (gestión) ====================

async function renderizarListaTiposEvento()
{
    const cont = document.getElementById('eventTypeList');
    if (!cont) return;
    try {
        const data = await callApi(APP_URL + 'Public/api/event-types.php?_=' + Date.now());
        if (!data.success) { cont.innerHTML = '<p class="text-danger">Error al cargar.</p>'; return; }
        const tipos = data.data;
        if (tipos.length === 0) {
            cont.innerHTML = '<p style="color:var(--gray);text-align:center;">No hay tipos de evento registrados.</p>';
            return;
        }
        cont.innerHTML = tipos.map(et => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-bottom:1px solid var(--border);">
                <span>${et.name}</span>
                <button class="btn btn-sm btn-outline" style="color:var(--danger);border-color:var(--danger);" data-id="${et.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
        cont.querySelectorAll('button[data-id]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = parseInt(btn.dataset.id);
                if (!confirm('¿Eliminar este tipo de evento?')) return;
                try {
                    const res = await callApi(APP_URL + 'Public/api/event-types.php?id=' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-Token': CSRF_TOKEN }
                    });
                    if (res.success) {
                        toast('Tipo de evento eliminado.', 'success');
                        await fetchTiposEvento();
                        renderizarListaTiposEvento();
                    } else {
                        toast('Error: ' + res.message, 'error');
                    }
                } catch (e) { toast(e.message || 'Error de conexión.', 'error'); }
            });
        });
    } catch (e) {
        cont.innerHTML = '<p class="text-danger">Error de conexión.</p>';
    }
}

// ==================== UTILIDADES DE HORA ====================

function poblarSelectoresHora()
{
    const ids = [
        'newHoraInicio_h', 'newHoraFin_h',
        'editHoraInicio_h', 'editHoraFin_h'
    ];
    ids.forEach(id => {
        const sel = document.getElementById(id);
        if (!sel) return;
        sel.innerHTML = '';
        for (let h = 1; h <= 12; h++) {
            const opt = document.createElement('option');
            opt.value = h;
            opt.textContent = h;
            sel.appendChild(opt);
        }
    });
    const mids = [
        'newHoraInicio_m', 'newHoraFin_m',
        'editHoraInicio_m', 'editHoraFin_m'
    ];
    mids.forEach(id => {
        const sel = document.getElementById(id);
        if (!sel) return;
        sel.innerHTML = '';
        for (let m = 0; m < 60; m++) {
            const v = String(m).padStart(2, '0');
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            sel.appendChild(opt);
        }
    });
}

function hora24a12(hhmm)
{
    const p = hhmm.split(':');
    let h = parseInt(p[0], 10);
    const m = p[1] || '00';
    const ap = h >= 12 ? 'PM' : 'AM';
    if (h === 0) h = 12;
    else if (h > 12) h -= 12;
    return { h: h.toString(), m: m, ap: ap };
}

function hora12a24(h, m, ap)
{
    h = parseInt(h, 10);
    if (ap === 'AM' && h === 12) h = 0;
    if (ap === 'PM' && h !== 12) h += 12;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

// ==================== VALIDACIÓN ====================

function toggleSinMateriales(mode)
{
    const prefix = mode === 'nuevo' ? 'new' : 'edit';
    const checkbox = document.getElementById(prefix + 'SinMateriales');
    const group = document.getElementById(prefix + 'MotivoMaterialesGroup');
    const textarea = document.getElementById(prefix + 'MotivoSinMateriales');
    const btn = document.getElementById(prefix + 'AsignarMateriales');
    const panel = document.getElementById(prefix + 'MaterialPanel');
    if (!checkbox) return;
    if (checkbox.checked) {
        group.style.display = 'block';
        textarea.setAttribute('required', 'false');
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
        if (panel) panel.style.display = 'none';
        materialesAsignados = [];
        actualizarContadorMateriales(prefix);
    } else {
        group.style.display = 'none';
        textarea.removeAttribute('required');
        textarea.value = '';
        btn.style.opacity = '1';
        btn.style.pointerEvents = '';
    }
}

function validarDatosCita(datos)
{
    if (!datos.clienteId) { toast('Debe seleccionar un cliente.', 'warning'); return false; }
    if (!datos.fechaHoraInicio) { toast('La fecha y hora de inicio es obligatoria.', 'warning'); return false; }
    if (!datos.fechaHoraFin) { toast('La fecha y hora de fin es obligatoria.', 'warning'); return false; }
    if (datos.fechaHoraInicio >= datos.fechaHoraFin) {
        toast('La fecha de fin debe ser posterior a la de inicio.', 'warning');
        return false;
    }
    if (!datos.materiales || datos.materiales.length === 0) {
        if (!datos.motivoSinMateriales) {
            toast('Debe asignar al menos un material o indicar el motivo.', 'warning');
            return false;
        }
    }
    return true;
}

// ==================== INICIALIZACIÓN ====================

function initApp()
{
    poblarSelectoresHora();
    fetchClientes().then(() => {
        if (clientesList.length === 0) {
            const addBtn = document.getElementById('addAppointmentBtn');
            if (addBtn) addBtn.disabled = true;
        }
        fetchTiposEvento();
        cargarSelectMateriales();
        fetchCitas();
        fetchTodasLasCitas();
        fetchCanceladas();
    });

    // Toggle sin materiales
    document.getElementById('newSinMateriales')?.addEventListener('change', () => toggleSinMateriales('nuevo'));
    document.getElementById('editSinMateriales')?.addEventListener('change', () => toggleSinMateriales('editar'));

    // Botón agregar
    document.getElementById('addAppointmentBtn')?.addEventListener('click', abrirModalNueva);

    // Form nueva cita
    document.getElementById('newAppointmentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        if (document.getElementById('newSubmitBtn').disabled) {
            toast('Corrija los errores de stock antes de guardar.', 'warning');
            return;
        }
        if (hayCantidadInvalida()) {
            toast('Corrija las cantidades inválidas antes de guardar.', 'warning');
            return;
        }
        const fechaInicio = document.getElementById('newFechaInicio')?.value || '';
        const hi_h = document.getElementById('newHoraInicio_h')?.value || '8';
        const hi_m = document.getElementById('newHoraInicio_m')?.value || '00';
        const hi_a = document.getElementById('newHoraInicio_a')?.value || 'AM';
        const fechaFin = document.getElementById('newFechaFin')?.value || '';
        const hf_h = document.getElementById('newHoraFin_h')?.value || '8';
        const hf_m = document.getElementById('newHoraFin_m')?.value || '00';
        const hf_a = document.getElementById('newHoraFin_a')?.value || 'AM';
        const datos = {
            clienteId: parseInt(document.getElementById('newClient')?.value || 0),
            fechaHoraInicio: fechaInicio ? fechaInicio + 'T' + hora12a24(hi_h, hi_m, hi_a) : '',
            fechaHoraFin: fechaFin ? fechaFin + 'T' + hora12a24(hf_h, hf_m, hf_a) : '',
            eventTypeId: (v => v ? parseInt(v) : null)(document.getElementById('newEventType')?.value),
            ubicacion: document.getElementById('newUbicacion')?.value || '',
            notas: document.getElementById('newNotas')?.value || '',
            materiales: materialesAsignados,
            motivoSinMateriales: document.getElementById('newMotivoSinMateriales')?.value?.trim() || ''
        };
        if (!validarDatosCita(datos)) return;
        crearCita(datos);
        document.getElementById('newAppointmentModal').style.display = 'none';
    });

    // Asignar materiales (nuevo)
    document.getElementById('newAsignarMateriales')?.addEventListener('click', () => abrirModalMateriales('nuevo'));

    // Form editar cita
    document.getElementById('editForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        if (document.getElementById('editSubmitBtn').disabled) {
            toast('Corrija los errores de stock antes de guardar.', 'warning');
            return;
        }
        if (hayCantidadInvalida()) {
            toast('Corrija las cantidades inválidas antes de guardar.', 'warning');
            return;
        }
        const id = parseInt(document.getElementById('editId')?.value || 0);
        if (!id) return;
        const fechaInicio = document.getElementById('editFechaInicio')?.value || '';
        const hi_h = document.getElementById('editHoraInicio_h')?.value || '8';
        const hi_m = document.getElementById('editHoraInicio_m')?.value || '00';
        const hi_a = document.getElementById('editHoraInicio_a')?.value || 'AM';
        const fechaFin = document.getElementById('editFechaFin')?.value || '';
        const hf_h = document.getElementById('editHoraFin_h')?.value || '8';
        const hf_m = document.getElementById('editHoraFin_m')?.value || '00';
        const hf_a = document.getElementById('editHoraFin_a')?.value || 'AM';
        const datos = {
            clienteId: parseInt(document.getElementById('editClient')?.value || 0),
            fechaHoraInicio: fechaInicio ? fechaInicio + 'T' + hora12a24(hi_h, hi_m, hi_a) : '',
            fechaHoraFin: fechaFin ? fechaFin + 'T' + hora12a24(hf_h, hf_m, hf_a) : '',
            eventTypeId: (v => v ? parseInt(v) : null)(document.getElementById('editEventType')?.value),
            ubicacion: document.getElementById('editUbicacion')?.value || '',
            notas: document.getElementById('editNotas')?.value || '',
            materiales: materialesAsignados,
            motivoSinMateriales: document.getElementById('editMotivoSinMateriales')?.value?.trim() || ''
        };
        if (!validarDatosCita(datos)) return;
        actualizarCita(id, datos);
        document.getElementById('editModal').style.display = 'none';
    });

    // Asignar materiales (editar)
    document.getElementById('editAsignarMateriales')?.addEventListener('click', () => abrirModalMateriales('editar'));
    // Ver historial de materiales (editar)
    document.getElementById('editVerHistorial')?.addEventListener('click', function() {
        const id = parseInt(document.getElementById('editId')?.value || 0);
        if (id) abrirHistorialCita(id);
    });

    // Panel materiales — nuevo
    document.getElementById('newMaterialFilter')?.addEventListener('input', () => renderizarListaMateriales('nuevo'));
    document.getElementById('newAddMaterialBtn')?.addEventListener('click', () => agregarMaterial('nuevo'));
    document.getElementById('newConfirmMaterialPanel')?.addEventListener('click', () => confirmarMateriales('nuevo'));
    document.getElementById('newCloseMaterialPanel')?.addEventListener('click', () => cerrarPanelMateriales('nuevo'));
    document.getElementById('newMaterialSelect')?.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); agregarMaterial('nuevo'); } });
    // Panel materiales — editar
    document.getElementById('editMaterialFilter')?.addEventListener('input', () => renderizarListaMateriales('editar'));
    document.getElementById('editAddMaterialBtn')?.addEventListener('click', () => agregarMaterial('editar'));
    document.getElementById('editConfirmMaterialPanel')?.addEventListener('click', () => confirmarMateriales('editar'));
    document.getElementById('editCloseMaterialPanel')?.addEventListener('click', () => cerrarPanelMateriales('editar'));
    document.getElementById('editMaterialSelect')?.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); agregarMaterial('editar'); } });

    // Modal cancelación
    document.getElementById('confirmCancelBtn')?.addEventListener('click', confirmarCancelacion);
    document.getElementById('cancelCancelBtn')?.addEventListener('click', () => document.getElementById('cancelModal').style.display = 'none');

    // Toggle vista calendario
    document.getElementById('toggleViewBtn')?.addEventListener('click', function() {
        const tableView = document.getElementById('appointmentsTable');
        const calendarView = document.getElementById('calendarView');
        const icon = this.querySelector('i');
        if (tableView && calendarView) {
            if (tableView.style.display !== 'none') {
                tableView.style.display = 'none';
                calendarView.style.display = 'block';
                if (icon) { icon.classList.remove('fa-calendar'); icon.classList.add('fa-list'); }
                this.innerHTML = '<i class="fas fa-list"></i> Vista Tabla';
                if (calendario) calendario.updateSize();
            } else {
                tableView.style.display = 'block';
                calendarView.style.display = 'none';
                if (icon) { icon.classList.remove('fa-list'); icon.classList.add('fa-calendar'); }
                this.innerHTML = '<i class="fas fa-calendar"></i> Vista Calendario';
            }
        }
    });

    // Cerrar modal historial
    document.querySelector('#citaHistorialModal .close-modal')?.addEventListener('click', () => {
        document.getElementById('citaHistorialModal').style.display = 'none';
    });

    // Cerrar modales
    document.querySelectorAll('.close-modal, #cancelNew, #cancelEdit').forEach(btn => {
        btn.addEventListener('click', function() {
            if (hayCantidadInvalida()) {
                toast('Corrija las cantidades inválidas antes de cerrar.', 'warning');
                return;
            }
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        });
    });
    // Cerrar panel lateral de materiales
    document.querySelectorAll('.close-sidebar').forEach(btn => {
        btn.addEventListener('click', function() {
            if (hayCantidadInvalida()) {
                toast('Corrija las cantidades inválidas antes de cerrar.', 'warning');
                return;
            }
            const panel = this.closest('.material-panel');
            if (panel) panel.style.display = 'none';
        });
    });
    window.addEventListener('click', function(e) {
        if (hayCantidadInvalida()) return;
        document.querySelectorAll('.modal').forEach(m => {
            if (e.target === m) m.style.display = 'none';
        });
        const histModal = document.getElementById('citaHistorialModal');
        if (e.target === histModal) histModal.style.display = 'none';
    });

    // Navegación calendario
    document.getElementById('prevMonth')?.addEventListener('click', () => { if (calendario) calendario.prev(); });
    document.getElementById('nextMonth')?.addEventListener('click', () => { if (calendario) calendario.next(); });
    document.getElementById('todayBtn')?.addEventListener('click', () => { if (calendario) calendario.today(); });

    // Paginación
    document.getElementById('prevPage')?.addEventListener('click', () => {
        if (paginaActual > 1) { paginaActual--; renderizarTabla(paginaActual); }
    });
    document.getElementById('nextPage')?.addEventListener('click', () => {
        const totalPaginas = Math.ceil(citasList.length / itemsPorPagina) || 1;
        if (paginaActual < totalPaginas) { paginaActual++; renderizarTabla(paginaActual); }
    });

    // Filtros
    document.getElementById('searchInput')?.addEventListener('input', filtrarCitas);
    document.getElementById('eventTypeFilter')?.addEventListener('change', filtrarCitas);
    document.getElementById('statusFilter')?.addEventListener('change', filtrarCitas);
    document.getElementById('filterDateFrom')?.addEventListener('change', filtrarCitas);
    document.getElementById('filterDateTo')?.addEventListener('change', filtrarCitas);

    // Historial
    document.getElementById('toggleHistoryBtn')?.addEventListener('click', function() {
        const section = document.getElementById('historySection');
        if (section) {
            if (section.style.display === 'none') {
                section.style.display = 'block';
                this.innerHTML = '<i class="fas fa-chevron-up"></i> Ocultar Historial';
            } else {
                section.style.display = 'none';
                this.innerHTML = '<i class="fas fa-history"></i> Historial de Canceladas';
            }
        }
    });

    // Gestión de tipos de evento
    document.getElementById('manageEventTypesBtn')?.addEventListener('click', () => {
        document.getElementById('eventTypeModal').style.display = 'flex';
        renderizarListaTiposEvento();
    });
    document.getElementById('eventTypeForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const name = document.getElementById('eventTypeName')?.value?.trim();
        if (!name) { toast('Ingrese un nombre.', 'warning'); return; }
        try {
            const res = await callApi(APP_URL + 'Public/api/event-types.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ name })
            });
            if (res.success) {
                toast('Tipo de evento creado.', 'success');
                document.getElementById('eventTypeName').value = '';
                await fetchTiposEvento();
                renderizarListaTiposEvento();
            } else {
                toast('Error: ' + res.message, 'error');
            }
        } catch (e) { toast(e.message || 'Error de conexión.', 'error'); }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initApp();
    setTimeout(initCalendar, 100);
});
