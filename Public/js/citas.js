// =============================================
// citas.js - Gestión de Citas (Appointments)
// =============================================

let appointmentsList = [];
let allAppointments = [];
let cancelledAppointments = [];
let customersList = [];

let currentPage = 1;
const appointmentsPerPage = 10;
let calendar = null;

async function fetchCustomers()
{
    try {
        const res = await fetch(APP_URL + 'api/customers.php?_=' + Date.now());
        const data = await res.json();
        if (data.success) {
            customersList = data.data;
            populateClientSelectors();
        }
    } catch (err) {
        console.error('Error al cargar clientes:', err);
    }
}

async function fetchAppointments()
{
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?_=' + Date.now());
        const data = await res.json();
        if (data.success) {
            appointmentsList = data.data;
            renderAppointmentsTable(currentPage);
            if (calendar) calendar.refetchEvents();
        }
    } catch (err) {
        console.error('Error al cargar citas:', err);
    }
}

async function fetchAllAppointments()
{
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?all=1&_=' + Date.now());
        const data = await res.json();
        if (data.success) {
            allAppointments = data.data;
            if (calendar) calendar.refetchEvents();
        }
    } catch (err) {
        console.error('Error al cargar todas las citas:', err);
    }
}

async function fetchCancelledAppointments()
{
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?cancelled=1&_=' + Date.now());
        const data = await res.json();
        if (data.success) {
            cancelledAppointments = data.data;
            renderHistory();
        }
    } catch (err) {
        console.error('Error al cargar citas canceladas:', err);
    }
}

async function createAppointment(appData)
{
    try {
        const res = await fetch(APP_URL + 'api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(appData)
        });
        const data = await res.json();
        if (data.success) {
            await fetchAppointments();
        } else {
            toast('Error: ' + data.message, 'error');
        }
    } catch (err) {
        console.error('Error al crear cita:', err);
        toast('Error de conexión.', 'error');
    }
}

async function updateAppointment(id, appData)
{
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?id=' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(appData)
        });
        const data = await res.json();
        if (data.success) {
            await fetchAppointments();
        } else {
            toast('Error: ' + data.message, 'error');
        }
    } catch (err) {
        console.error('Error al actualizar cita:', err);
        toast('Error de conexión.', 'error');
    }
}

async function cancelAppointment(id)
{
    if (!confirm('¿Estás seguro de cancelar esta cita?')) return;
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?id=' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        const data = await res.json();
        if (data.success) {
            currentPage = 1;
            await fetchAppointments();
            await fetchAllAppointments();
            await fetchCancelledAppointments();
        } else {
            toast('Error: ' + data.message, 'error');
        }
    } catch (err) {
        console.error('Error al cancelar cita:', err);
        toast('Error de conexión.', 'error');
    }
}

async function reactivateAppointment(id)
{
    if (!confirm('¿Reactivar esta cita? Se cambiará a estado Pendiente.')) return;
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?id=' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ status: 'pending' })
        });
        const data = await res.json();
        if (data.success) {
            await fetchAppointments();
            await fetchAllAppointments();
            await fetchCancelledAppointments();
        } else {
            toast('Error: ' + data.message, 'error');
        }
    } catch (err) {
        console.error('Error al reactivar cita:', err);
        toast('Error de conexión.', 'error');
    }
}

function populateClientSelectors()
{
    const newSelect = document.getElementById('newClient');
    const editSelect = document.getElementById('editClient');
    const selects = [];
    if (newSelect) selects.push(newSelect);
    if (editSelect) selects.push(editSelect);

    selects.forEach(sel => {
        if (!sel) return;
        sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
        if (customersList.length === 0) {
            sel.innerHTML = '<option value="">No hay clientes registrados</option>';
            return;
        }
        customersList.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = (c.firstName || '') + ' ' + (c.lastName || '');
            sel.appendChild(opt);
        });
    });
}

function getCustomerName(id)
{
    const c = customersList.find(c => c.id === id);
    return c ? (c.firstName + ' ' + c.lastName).trim() : 'Cliente #' + id;
}

function getCustomerAvatar(id)
{
    const c = customersList.find(c => c.id === id);
    return c?.avatar || 'https://i.imgur.com/1As0akH.jpg';
}

function getCustomerPhone(id)
{
    const c = customersList.find(c => c.id === id);
    return c?.phone || '';
}

function renderAppointmentsTable(page)
{
    const table = document.querySelector('.appointments-table');
    if (!table) return;

    const existingRows = document.querySelectorAll('.table-row:not(.table-header)');
    existingRows.forEach(r => r.remove());

    const total = appointmentsList.length;
    const totalPages = Math.ceil(total / appointmentsPerPage) || 1;
    const start = (page - 1) * appointmentsPerPage;
    const end = Math.min(start + appointmentsPerPage, total);
    const pageItems = appointmentsList.slice(start, end);

    const showStart = document.getElementById('showingStart');
    const showEnd = document.getElementById('showingEnd');
    const totalSpan = document.getElementById('totalAppointments');
    if (showStart) showStart.textContent = total > 0 ? start + 1 : 0;
    if (showEnd) showEnd.textContent = end;
    if (totalSpan) totalSpan.textContent = total;

    pageItems.forEach(app => {
        const name = getCustomerName(app.customerId);
        const phone = getCustomerPhone(app.customerId);
        const avatar = getCustomerAvatar(app.customerId);
        const dateObj = new Date(app.date + 'T' + (app.startTime || '00:00'));
        const formattedDate = dateObj.toLocaleDateString('es-ES');
        const eventType = app.eventType ? app.eventType.charAt(0).toUpperCase() + app.eventType.slice(1) : '—';

        const row = document.createElement('div');
        row.className = 'table-row';
        row.dataset.id = app.id;
        row.dataset.type = app.eventType || '';
        row.dataset.status = app.status;

        row.innerHTML = `
            <div class="col-1">#${app.id}</div>
            <div class="col-2" style="display:flex;align-items:center;gap:15px;">
                <div class="client-img">
                    <img src="${avatar}" alt="Cliente" onerror="this.src='https://i.imgur.com/1As0akH.jpg'">
                </div>
                <div>
                    <strong>${name}</strong>
                    <div style="font-size:0.85rem;color:var(--gray)">${phone}</div>
                </div>
            </div>
            <div class="col-3">
                <div>${formattedDate}</div>
                <div style="font-size:0.85rem;color:var(--gray)">${app.startTime || '—'} - ${app.endTime || '—'}</div>
            </div>
            <div class="col-4"><span class="event-type">${eventType}</span></div>
            <div class="col-5">${app.location || '—'}</div>
            <div class="col-6"><span class="status ${app.status}">${getStatusText(app.status)}</span></div>
            <div class="col-7" style="display:flex;gap:10px;">
                <button class="action-btn edit" data-id="${app.id}"><i class="fas fa-edit"></i></button>
                ${app.status !== 'cancelled' ? `
                <button class="action-btn cancel-btn" data-id="${app.id}" title="Cancelar cita">
                    <i class="fas fa-ban"></i>
                </button>` : ''}
            </div>
        `;

        table.appendChild(row);
        addEventListenersToRow(row, app.id);
    });
}

function addEventListenersToRow(row, id)
{
    const editBtn = row.querySelector('.action-btn.edit');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            const app = appointmentsList.find(a => a.id === id);
            if (app) openEditModal(app);
        });
    }

    const cancelBtn = row.querySelector('.action-btn.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => cancelAppointment(id));
    }
}

function getStatusText(status)
{
    const map = {
        pending: 'Pendiente', confirmed: 'Confirmada',
        'in-progress': 'En Progreso', completed: 'Completada', cancelled: 'Cancelada'
    };
    return map[status] || status;
}

function initCalendar()
{
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: false,
        editable: true,
        eventDrop: function(info) {
            const id = parseInt(info.event.id);
            const app = appointmentsList.find(a => a.id === id);
            if (!app) return;
            const newDate = info.event.startStr.split('T')[0];
            const newStart = info.event.startStr.split('T')[1] || app.startTime;
            const newEnd = info.event.endStr ? info.event.endStr.split('T')[1] || app.endTime : app.endTime;
            updateAppointment(id, {
                customerId: app.customerId,
                date: newDate,
                startTime: newStart,
                endTime: newEnd,
                eventType: app.eventType,
                location: app.location,
                status: app.status,
                notes: app.notes
            });
        },
        eventResize: function(info) {
            const id = parseInt(info.event.id);
            const app = appointmentsList.find(a => a.id === id);
            if (!app) return;
            const newEnd = info.event.endStr ? info.event.endStr.split('T')[1] || app.endTime : app.endTime;
            updateAppointment(id, {
                customerId: app.customerId,
                date: app.date,
                startTime: app.startTime,
                endTime: newEnd,
                eventType: app.eventType,
                location: app.location,
                status: app.status,
                notes: app.notes
            });
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            const list = allAppointments.length > 0 ? allAppointments : appointmentsList;
            const events = list.map(app => ({
                id: String(app.id),
                title: getCustomerName(app.customerId) + ' - ' + (app.eventType || 'Evento'),
                start: app.date + 'T' + (app.startTime || '00:00'),
                end: app.date + 'T' + (app.endTime || '23:59'),
                className: 'fc-event-' + (app.status || 'pending'),
                extendedProps: {
                    location: app.location || '',
                    status: app.status || 'pending',
                    notes: app.notes || ''
                }
            }));
            successCallback(events);
        },
        eventClick: function(info) {
            const id = parseInt(info.event.id);
            const list = allAppointments.length > 0 ? allAppointments : appointmentsList;
            const app = list.find(a => a.id === id);
            if (app) openEditModal(app);
        },
        datesSet: function() {
            updateCalendarTitle();
        }
    });

    calendar.render();
    updateCalendarTitle();
}

function updateCalendarTitle()
{
    const titleEl = document.getElementById('calendarTitle');
    if (calendar && titleEl) {
        const opts = { year: 'numeric', month: 'long' };
        titleEl.textContent = calendar.view.currentStart.toLocaleDateString('es-ES', opts);
    }
}

function filterAppointments()
{
    const searchVal = document.getElementById('searchInput')?.value?.toLowerCase() || '';
    const typeFilter = document.getElementById('eventTypeFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const dateFrom = document.getElementById('filterDateFrom')?.value || '';
    const dateTo = document.getElementById('filterDateTo')?.value || '';

    document.querySelectorAll('.table-row:not(.table-header)').forEach(row => {
        const id = parseInt(row.dataset.id);
        const app = appointmentsList.find(a => a.id === id);
        if (!app) { row.style.display = 'none'; return; }
        const name = getCustomerName(app.customerId).toLowerCase();
        const matchSearch = name.includes(searchVal);
        const matchType = !typeFilter || (app.eventType || '') === typeFilter;
        const matchStatus = !statusFilter || (app.status || '') === statusFilter;
        const matchDate = (!dateFrom || app.date >= dateFrom) && (!dateTo || app.date <= dateTo);
        row.style.display = (matchSearch && matchType && matchStatus && matchDate) ? 'grid' : 'none';
    });
}

function renderHistory()
{
    const tbody = document.getElementById('historyBody');
    if (!tbody) return;

    if (cancelledAppointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--gray)">No hay citas canceladas.</td></tr>';
        return;
    }

    tbody.innerHTML = cancelledAppointments.map(app => {
        const name = getCustomerName(app.customerId);
        const phone = getCustomerPhone(app.customerId);
        const dateObj = new Date(app.date + 'T' + (app.startTime || '00:00'));
        const formattedDate = dateObj.toLocaleDateString('es-ES');
        const eventType = app.eventType ? app.eventType.charAt(0).toUpperCase() + app.eventType.slice(1) : '—';
        return `<tr>
            <td>#${app.id}</td>
            <td><strong>${name}</strong><br><small style="color:var(--gray)">${phone}</small></td>
            <td>${formattedDate}<br><small style="color:var(--gray)">${app.startTime || '—'}</small></td>
            <td>${eventType}</td>
            <td>${app.location || '—'}</td>
            <td>
                <button class="btn-reactivate" data-id="${app.id}">
                    <i class="fas fa-undo"></i> Reactivar
                </button>
            </td>
        </tr>`;
    }).join('');

    tbody.querySelectorAll('.btn-reactivate').forEach(btn => {
        btn.addEventListener('click', () => reactivateAppointment(parseInt(btn.dataset.id)));
    });
}

function openEditModal(appointment)
{
    const modal = document.getElementById('editModal');
    if (!modal) return;
    modal.style.display = 'flex';

    // Poblar selects primero (esto resetea el innerHTML)
    populateClientSelectors();

    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    setVal('editId', appointment.id);
    setVal('editClient', appointment.customerId);
    setVal('editDate', appointment.date);
    setVal('editStartTime', appointment.startTime);
    setVal('editEndTime', appointment.endTime);
    setVal('editEventType', appointment.eventType);
    setVal('editLocation', appointment.location);
    setVal('editStatus', appointment.status);
    setVal('editNotes', appointment.notes);

    // En edición, el cliente no se debe cambiar
    const clientSelect = document.getElementById('editClient');
    if (clientSelect) {
        clientSelect.disabled = true;
        clientSelect.style.opacity = '0.8';
        clientSelect.style.cursor = 'not-allowed';
    }
}

function validateAppointmentData(data) {
    if (!data.customerId) {
        toast('Debe seleccionar un cliente.', 'warning');
        return false;
    }
    if (!data.date) {
        toast('La fecha es obligatoria.', 'warning');
        return false;
    }
    if (!data.startTime) {
        toast('La hora de inicio es obligatoria.', 'warning');
        return false;
    }
    if (data.endTime && data.startTime >= data.endTime) {
        toast('La hora de fin debe ser posterior a la hora de inicio.', 'warning');
        return false;
    }
    return true;
}

function initApp()
{
    fetchCustomers().then(() => {
        if (customersList.length === 0) {
            const addBtn = document.getElementById('addAppointmentBtn');
            if (addBtn) addBtn.disabled = true;
        }
        fetchAppointments();
        fetchAllAppointments();
        fetchCancelledAppointments();
    });

    renderAppointmentsTable(currentPage);

    const addBtn = document.getElementById('addAppointmentBtn');
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            if (customersList.length === 0) {
                toast('Debe registrar al menos un cliente antes de crear una cita.', 'warning');
                return;
            }
            const modal = document.getElementById('newAppointmentModal');
            if (modal) {
                modal.style.display = 'flex';
                populateClientSelectors();
            }
            const form = document.getElementById('newAppointmentForm');
            if (form) form.reset();
            const today = new Date().toLocaleDateString('en-CA');
            const dateInput = document.getElementById('newDate');
            if (dateInput) dateInput.min = today;
        });
    }

    const newForm = document.getElementById('newAppointmentForm');
    if (newForm) {
        newForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const data = {
                customerId: parseInt(document.getElementById('newClient')?.value || 0),
                date: document.getElementById('newDate')?.value || '',
                startTime: document.getElementById('newStartTime')?.value || '',
                endTime: document.getElementById('newEndTime')?.value || '',
                eventType: document.getElementById('newEventType')?.value || '',
                location: document.getElementById('newLocation')?.value || '',
                status: document.getElementById('newStatus')?.value || 'pending',
                notes: document.getElementById('newNotes')?.value || ''
            };
            if (!validateAppointmentData(data)) return;
            createAppointment(data);
            document.getElementById('newAppointmentModal').style.display = 'none';
        });
    }

    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('editId')?.value || 0);
            if (!id) return;
            const data = {
                customerId: parseInt(document.getElementById('editClient')?.value || 0),
                date: document.getElementById('editDate')?.value || '',
                startTime: document.getElementById('editStartTime')?.value || '',
                endTime: document.getElementById('editEndTime')?.value || '',
                eventType: document.getElementById('editEventType')?.value || '',
                location: document.getElementById('editLocation')?.value || '',
                status: document.getElementById('editStatus')?.value || 'pending',
                notes: document.getElementById('editNotes')?.value || ''
            };
            if (!validateAppointmentData(data)) return;
            updateAppointment(id, data);
            document.getElementById('editModal').style.display = 'none';
        });
    }

    const toggleBtn = document.getElementById('toggleViewBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const tableView = document.getElementById('appointmentsTable');
            const calendarView = document.getElementById('calendarView');
            const icon = this.querySelector('i');
            if (tableView && calendarView) {
                if (tableView.style.display !== 'none') {
                    tableView.style.display = 'none';
                    calendarView.style.display = 'block';
                    if (icon) { icon.classList.remove('fa-calendar'); icon.classList.add('fa-list'); }
                    this.innerHTML = '<i class="fas fa-list"></i> Vista Tabla';
                    if (calendar) calendar.updateSize();
                } else {
                    tableView.style.display = 'block';
                    calendarView.style.display = 'none';
                    if (icon) { icon.classList.remove('fa-list'); icon.classList.add('fa-calendar'); }
                    this.innerHTML = '<i class="fas fa-calendar"></i> Vista Calendario';
                }
            }
        });
    }

    document.querySelectorAll('.close-modal, #cancelNew, #cancelEdit').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        });
    });

    window.addEventListener('click', function(e) {
        document.querySelectorAll('.modal').forEach(m => {
            if (e.target === m) m.style.display = 'none';
        });
    });

    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    const todayBtn = document.getElementById('todayBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => { if (calendar) calendar.prev(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { if (calendar) calendar.next(); });
    if (todayBtn) todayBtn.addEventListener('click', () => { if (calendar) calendar.today(); });

    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    if (prevPage) {
        prevPage.addEventListener('click', () => {
            if (currentPage > 1) { currentPage--; renderAppointmentsTable(currentPage); }
        });
    }
    if (nextPage) {
        nextPage.addEventListener('click', () => {
            const totalPages = Math.ceil(appointmentsList.length / appointmentsPerPage) || 1;
            if (currentPage < totalPages) { currentPage++; renderAppointmentsTable(currentPage); }
        });
    }

    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('eventTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    if (searchInput) searchInput.addEventListener('input', filterAppointments);
    if (typeFilter) typeFilter.addEventListener('change', filterAppointments);
    if (statusFilter) statusFilter.addEventListener('change', filterAppointments);

    const historyToggle = document.getElementById('toggleHistoryBtn');
    const historySection = document.getElementById('historySection');
    if (historyToggle && historySection) {
        historyToggle.addEventListener('click', function() {
            if (historySection.style.display === 'none') {
                historySection.style.display = 'block';
                this.innerHTML = '<i class="fas fa-chevron-up"></i> Ocultar Historial';
            } else {
                historySection.style.display = 'none';
                this.innerHTML = '<i class="fas fa-history"></i> Historial de Canceladas';
            }
        });
    }

    const today = new Date().toLocaleDateString('en-CA');
    const dateFields = ['newDate', 'editDate'];
    dateFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.min = today;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initApp();
    setTimeout(initCalendar, 100);
});
