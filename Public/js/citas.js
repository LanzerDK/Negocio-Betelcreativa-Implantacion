// =============================================
// citas.js - Gestión de Citas (Appointments)
// =============================================

let appointmentsList = [];
let customersList = [];

let currentPage = 1;
const appointmentsPerPage = 10;
let calendar = null;

async function fetchCustomers()
{
    try {
        const res = await fetch(APP_URL + 'api/customers.php');
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
        const res = await fetch(APP_URL + 'api/appointments.php');
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
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error('Error al crear cita:', err);
        alert('Error de conexión');
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
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error('Error al actualizar cita:', err);
        alert('Error de conexión');
    }
}

async function deleteAppointment(id)
{
    if (!confirm('¿Estás seguro de eliminar esta cita?')) return;
    try {
        const res = await fetch(APP_URL + 'api/appointments.php?id=' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        const data = await res.json();
        if (data.success) {
            await fetchAppointments();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error('Error al eliminar cita:', err);
        alert('Error de conexión');
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
                <button class="action-btn delete-btn" data-id="${app.id}">
                    <i class="fas fa-trash"></i>
                </button>
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

    const deleteBtn = row.querySelector('.action-btn.delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => deleteAppointment(id));
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
            const events = appointmentsList.map(app => ({
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
            const app = appointmentsList.find(a => a.id === id);
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

    document.querySelectorAll('.table-row:not(.table-header)').forEach(row => {
        const id = parseInt(row.dataset.id);
        const app = appointmentsList.find(a => a.id === id);
        if (!app) { row.style.display = 'none'; return; }
        const name = getCustomerName(app.customerId).toLowerCase();
        const matchSearch = name.includes(searchVal);
        const matchType = !typeFilter || (app.eventType || '') === typeFilter;
        const matchStatus = !statusFilter || (app.status || '') === statusFilter;
        row.style.display = (matchSearch && matchType && matchStatus) ? 'grid' : 'none';
    });
}

function openEditModal(appointment)
{
    const modal = document.getElementById('editModal');
    if (!modal) return;
    modal.style.display = 'flex';

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

    populateClientSelectors();
}

function validateAppointmentData(data) {
    if (!data.customerId) {
        alert('Debe seleccionar un cliente.');
        return false;
    }
    if (!data.date) {
        alert('La fecha es obligatoria.');
        return false;
    }
    if (!data.startTime) {
        alert('La hora de inicio es obligatoria.');
        return false;
    }
    if (data.endTime && data.startTime >= data.endTime) {
        alert('La hora de fin debe ser posterior a la hora de inicio.');
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
    });

    renderAppointmentsTable(currentPage);

    const addBtn = document.getElementById('addAppointmentBtn');
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            if (customersList.length === 0) {
                alert('Debe registrar al menos un cliente antes de crear una cita.');
                return;
            }
            const modal = document.getElementById('newAppointmentModal');
            if (modal) {
                modal.style.display = 'flex';
                populateClientSelectors();
            }
            const form = document.getElementById('newAppointmentForm');
            if (form) form.reset();
            const today = new Date().toISOString().split('T')[0];
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

    const today = new Date().toISOString().split('T')[0];
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
