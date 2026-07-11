// =============================================
// customers.js - Gestión de Clientes
// Consume la API REST /api/customers.php
// =============================================

let clients = [];
let currentClientId = null;

// Expresiones regulares para validación de campos
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_REGEX_VE = /^0[24]\d{9}$/;
const PREF_LINE_REGEX = /^[^:]+:.+$/;

// Muestra un mensaje de error debajo de un campo del formulario
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('is-invalid');
    const parent = field.closest('.form-group');
    if (!parent) return;
    let errorEl = parent.querySelector('.invalid-feedback');
    // Crear elemento de error si no existe aún
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'invalid-feedback';
        parent.appendChild(errorEl);
    }
    errorEl.textContent = message;
}

// Limpia el estado de error de un campo específico
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('is-invalid');
    const parent = field.closest('.form-group');
    if (!parent) return;
    const errorEl = parent.querySelector('.invalid-feedback');
    if (errorEl) errorEl.remove();
}

// Elimina todas las clases de error del formulario actual
function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

// ── OPERACIONES CRUD CON LA API ─────────────────────────────

// Obtiene la lista completa de clientes desde el API
async function fetchClients()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php');
        if (data.success) {
            clients = data.data;
            updateClientCounter();
            renderClientsList();
            // Seleccionar automáticamente el primer cliente si existen datos
            if (clients.length > 0) {
                const firstItem = document.querySelector('.client-item');
                if (firstItem) firstItem.click();
            } else {
                // Mostrar estado vacío si no hay clientes
                const noSel = document.getElementById('noClientSelected');
                const detail = document.getElementById('clientDetail');
                if (noSel) noSel.style.display = 'flex';
                if (detail) detail.classList.remove('active');
            }
        }
    } catch (err) {
        console.error('Error al cargar clientes:', err);
        toast('Error al cargar clientes', 'error');
    }
}

// Crea un nuevo cliente enviando datos por POST al API
async function createClient(clientData)
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(clientData)
        });
        if (data.success) {
            await fetchClients();
        } else {
            toast(data.message || 'Error al crear el cliente', 'error');
        }
    } catch (err) {
        console.error('Error al crear cliente:', err);
        toast('Error de conexión', 'error');
    }
}

// Actualiza un cliente existente enviando datos por PUT al API
async function updateClient(id, clientData)
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php?id=' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(clientData)
        });
        if (data.success) {
            // Actualizar el objeto en el array local sin recargar todo
            const idx = clients.findIndex(c => c.id === id);
            if (idx !== -1) {
                clients[idx] = { ...clients[idx], ...clientData };
            }
            renderClientDetails(id);
        } else {
            toast(data.message || 'Error al actualizar el cliente', 'error');
        }
    } catch (err) {
        console.error('Error al actualizar cliente:', err);
        toast('Error de conexión', 'error');
    }
}

// Alterna el estado activo/inactivo de un cliente
async function toggleClientStatus(id, isActive)
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php?id=' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
            body: JSON.stringify({ is_active: isActive ? 0 : 1 })
        });
        if (data.success) {
            await fetchClients();
        } else {
            toast(data.message || 'Error al cambiar estado del cliente', 'error');
        }
    } catch (err) {
        console.error('Error al cambiar estado del cliente:', err);
        toast('Error de conexión', 'error');
    }
}

// ── RENDERIZADO DE LA LISTA DE CLIENTES ─────────────────────

// Genera la lista lateral de clientes con avatar, nombre y email
function renderClientsList()
{
    const clientsList = document.getElementById('clientsList');
    if (!clientsList) return;
    clientsList.innerHTML = '';

    clients.forEach(client => {
        const item = document.createElement('div');
        item.className = 'client-item' + (client.isActive === false || client.isActive === 0 ? ' inactive' : '');
        item.dataset.id = client.id;

        const name = (client.firstName || '') + ' ' + (client.lastName || '');
        item.innerHTML = `
            <img src="${client.avatar || 'https://i.imgur.com/1As0akH.jpg'}" alt="Cliente" class="client-avatar" onerror="this.src='https://i.imgur.com/1As0akH.jpg'">
            <div class="client-info">
                <h4>${name.trim() || 'Sin nombre'}</h4>
                <p>${client.email || 'Sin email'}</p>
                ${client.isActive === false || client.isActive === 0 ? '<span class="inactive-badge">Inactivo</span>' : ''}
            </div>
        `;

        // Evento click para mostrar detalles del cliente seleccionado
        item.addEventListener('click', () => {
            document.querySelectorAll('.client-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            renderClientDetails(client.id);
        });

        clientsList.appendChild(item);
    });
}

// ── RENDERIZADO DEL DETALLE DEL CLIENTE ─────────────────────

// Muestra toda la información del cliente seleccionado en el panel derecho
function renderClientDetails(clientId)
{
    const client = clients.find(c => c.id === clientId);
    if (!client) return;

    currentClientId = clientId;
    const detail = document.getElementById('clientDetail');
    const noSel = document.getElementById('noClientSelected');
    if (detail) {
        detail.classList.add('active');
        detail.classList.toggle('inactive', client.isActive === false || client.isActive === 0);
    }
    if (noSel) noSel.style.display = 'none';

    const name = (client.firstName || '') + ' ' + (client.lastName || '');
    const avatar = client.avatar || 'https://i.imgur.com/1As0akH.jpg';

    if (!detail) return;
    // Generar HTML completo del detalle con pestañas de información y preferencias
    detail.innerHTML = `
        ${client.isActive === false || client.isActive === 0 ? '<div class="client-inactive-banner"><i class="fas fa-eye-slash"></i> Cliente Inhabilitado</div>' : ''}
        <div class="client-header">
            <img src="${avatar}" alt="Cliente" class="client-main-avatar" onerror="this.src='https://i.imgur.com/1As0akH.jpg'">
            <div class="client-main-info">
                <h2>${name.trim() || 'Sin nombre'}
                    <button class="btn btn-edit" id="editClientBtn">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn ${client.isActive ? 'btn-delete' : 'btn-enable'}" id="toggleClientBtn">
                        ${client.isActive ? '<i class="fas fa-eye-slash"></i> Inhabilitar' : '<i class="fas fa-check-circle"></i> Habilitar'}
                    </button>
                </h2>
                <p><i class="fas fa-id-card"></i> ${client.idNumber || '—'}</p>
                <p><i class="fas fa-envelope"></i> ${client.email || '—'}</p>
                <p><i class="fas fa-phone"></i> ${client.phone || '—'}</p>
                <div class="client-tags">
                    <span class="client-tag">${getClientTypeLabel(client.clientType)}</span>
                </div>
            </div>
        </div>

        <div class="client-tabs">
            <div class="client-tab active" data-tab="info">Información</div>
            <div class="client-tab" data-tab="preferences">Preferencias</div>
        </div>

        <div class="tab-content active" id="info-tab">
            <div class="info-grid">
                <div class="info-card">
                    <h4>Información de Contacto</h4>
                    <div class="info-item"><i class="fas fa-envelope"></i><span>${client.email || '—'}</span></div>
                    <div class="info-item"><i class="fas fa-phone"></i><span>${client.phone || '—'}</span></div>
                    <div class="info-item"><i class="fas fa-map-marker-alt"></i><span>${client.address || '—'}</span></div>
                </div>
                <div class="info-card">
                    <h4>Información Adicional</h4>
                    <div class="info-item"><i class="fas fa-id-card"></i><span>Cédula: ${client.idNumber || '—'}</span></div>
                    <div class="info-item"><i class="fas fa-user-tag"></i><span>${getClientTypeLabel(client.clientType)}</span></div>
                    <div class="info-item"><i class="fas fa-info-circle"></i><span>Nos conoció por: ${getSourceLabel(client.source)}</span></div>
                </div>
            </div>
            <div class="info-card">
                <h4>Notas del Cliente</h4>
                <p>${client.notes || 'Sin notas'}</p>
            </div>
        </div>

        <div class="tab-content" id="preferences-tab">
            <h3>Preferencias de Decoración</h3>
            <div class="preferences-grid" data-client-id="${client.id}">
                ${getPreferencesCards(client.preferences, (window._prefsPages && window._prefsPages[client.id]) || 1)}
            </div>
        </div>
    `;

    // ── EVENTOS DE PESTAÑAS ─────────────────────────────────
    // Alternar entre pestañas de Información y Preferencias
    document.querySelectorAll('.client-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.client-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            const el = document.getElementById(tabId + '-tab');
            if (el) el.classList.add('active');
        });
    });

    // Botón de editar: deshabilitado si el cliente está inactivo
    const editBtn = document.getElementById('editClientBtn');
    if (editBtn) {
        if (client.isActive === false || client.isActive === 0) {
            editBtn.disabled = true;
            editBtn.title = 'No se puede editar un cliente inactivo';
        }
        editBtn.addEventListener('click', () => {
            if (editBtn.disabled) return;
            openEditClientModal(client);
        });
    }

    // Botón de alternar estado activo/inactivo
    const toggleBtn = document.getElementById('toggleClientBtn');
    if (toggleBtn) toggleBtn.addEventListener('click', () => toggleClientStatus(client.id, client.isActive));

    // Paginación de tarjetas de preferencias
    const prefsGrid = document.querySelector('#preferences-tab .preferences-grid');
    if (prefsGrid) {
        prefsGrid.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-page]');
            if (!btn) return;
            const page = parseInt(btn.dataset.page);
            if (page < 1) return;
            if (!window._prefsPages) window._prefsPages = {};
            window._prefsPages[client.id] = page;
            const grid = document.querySelector('#preferences-tab .preferences-grid');
            if (grid) grid.innerHTML = getPreferencesCards(client.preferences, page);
        });
    }
}

// ── FUNCIONES DE UTILIDAD ───────────────────────────────────

// Convierte el tipo de cliente interno a etiqueta en español
function getClientTypeLabel(type) {
    const labels = { Regular: 'Regular', Frequent: 'Cliente frecuente', VIP: 'VIP', New: 'Nuevo' };
    return labels[type] || type;
}

// Convierte la fuente de procedencia a etiqueta en español
function getSourceLabel(source) {
    const labels = { Recommendation: 'Recomendación', 'Social Media': 'Redes Sociales', Website: 'Sitio Web', Event: 'En un evento', Other: 'Otro' };
    return labels[source] || source;
}

// Genera tarjetas paginadas de preferencias de decoración del cliente
function getPreferencesCards(preferences, page) {
    if (!preferences) return '<p>Sin preferencias registradas</p>';
    const lines = preferences.split('\n').filter(l => l.trim());
    const itemsPerPage = 6;
    const totalPages = Math.max(1, Math.ceil(lines.length / itemsPerPage));
    page = Math.min(page || 1, totalPages);
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageLines = lines.slice(start, end);
    // Mapa de palabras clave a iconos de Font Awesome
    const iconMap = { 'colores': 'fas fa-palette', 'estilo': 'fas fa-heart', 'no gusta': 'fas fa-times-circle', 'alergias': 'fas fa-allergies' };
    let html = '';
    pageLines.forEach(line => {
        const parts = line.split(':');
        if (parts.length < 2) return;
        const title = parts[0].trim();
        const value = parts.slice(1).join(':').trim();
        const key = title.toLowerCase();
        html += `
            <div class="preference-card">
                <div class="preference-icon"><i class="${iconMap[key] || 'fas fa-info-circle'}"></i></div>
                <div class="preference-title">${title}</div>
                <div class="preference-value">${value}</div>
            </div>
        `;
    });
    // Agregar controles de paginación si hay más de una página
    if (totalPages > 1) {
        html += `
            <div class="pref-pagination">
                <button class="pref-prev-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
                <span class="pref-page-info">${page} / ${totalPages}</span>
                <button class="pref-next-btn" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
    }
    return html;
}

// ── MODAL DE EDICIÓN DE CLIENTE ─────────────────────────────

// Abre el modal de edición y precarga los datos del cliente
function openEditClientModal(client)
{
    const modal = document.getElementById('editClientModal');
    if (!modal) return;
    clearErrors();
    modal.style.display = 'flex';

    // Función auxiliar para asignar valores a inputs
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    setVal('editFirstName', client.firstName);
    setVal('editLastName', client.lastName);
    // Separar tipo y número de cédula (formato "V-12345678")
    const idParts = (client.idNumber || '').split('-');
    setVal('editIdType', idParts[0] || 'V');
    setVal('editIdNumber', idParts.slice(1).join('-') || '');
    setVal('editEmail', client.email);
    setVal('editPhone', client.phone);
    setVal('editAddress', client.address);
    setVal('editClientType', client.clientType);
    setVal('editSource', client.source);
    setVal('editNotes', client.notes);
    setVal('editPreferences', client.preferences);

    // Clonar botón para eliminar listeners previos y reasignar evento
    const saveBtn = document.getElementById('saveEditBtn');
    if (saveBtn) {
        const newBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newBtn, saveBtn);
        newBtn.addEventListener('click', () => {
            clearErrors();
            const firstName = document.getElementById('editFirstName')?.value?.trim() || '';
            const lastName = document.getElementById('editLastName')?.value?.trim() || '';
            const editIdType = document.getElementById('editIdType')?.value || 'V';
            const editIdNumber = document.getElementById('editIdNumber')?.value?.trim() || '';
            const idNumber = editIdType + '-' + editIdNumber;
            const email = document.getElementById('editEmail')?.value?.trim() || '';
            const phone = document.getElementById('editPhone')?.value?.trim() || '';
            const preferences = document.getElementById('editPreferences')?.value?.trim() || '';

            // ── VALIDACIONES DEL FORMULARIO DE EDICIÓN ───────
            if (!firstName || !lastName) {
                showFieldError('editFirstName', 'El nombre es obligatorio');
                showFieldError('editLastName', 'El apellido es obligatorio');
                return;
            }
            if (!editIdNumber) {
                showFieldError('editIdNumber', 'El número de cédula es obligatorio');
                return;
            }
            if (!/^\d{6,8}$/.test(editIdNumber)) {
                showFieldError('editIdNumber', 'La cédula debe tener 6 o 8 dígitos');
                return;
            }
            // Verificar unicidad de cédula (excluyendo el cliente actual)
            if (idNumber !== client.idNumber && clients.find(c => c.idNumber === idNumber)) {
                showFieldError('editIdNumber', 'Ya existe un cliente con esta cédula.');
                return;
            }
            if (!email) {
                showFieldError('editEmail', 'El correo electrónico es obligatorio');
                return;
            }
            if (!EMAIL_REGEX.test(email)) {
                showFieldError('editEmail', 'El formato del email no es válido');
                return;
            }
            // Verificar unicidad de email (excluyendo el cliente actual)
            if (email !== client.email && clients.find(c => c.email === email && c.id !== client.id)) {
                showFieldError('editEmail', 'Ya existe un cliente con este correo electrónico.');
                return;
            }
            if (!phone) {
                showFieldError('editPhone', 'El teléfono es obligatorio');
                return;
            }
            if (!PHONE_REGEX_VE.test(phone)) {
                showFieldError('editPhone', 'Debe tener 11 dígitos, formato: 0XX-XXX-XXXX.');
                return;
            }
            // Verificar unicidad de teléfono (excluyendo el cliente actual)
            if (phone !== client.phone && clients.find(c => c.phone === phone && c.id !== client.id)) {
                showFieldError('editPhone', 'Ya existe un cliente con este número de teléfono.');
                return;
            }
            // Validar formato de preferencias (Título: Valor) si se proporcionan
            if (preferences) {
                const lines = preferences.split('\n');
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (line && !PREF_LINE_REGEX.test(line)) {
                        showFieldError('editPreferences', 'Cada preferencia debe tener el formato: Título: Valor.');
                        return;
                    }
                }
            }

            // Enviar datos actualizados al API
            const updated = {
                firstName: firstName,
                lastName: lastName,
                idNumber: idNumber,
                email: email,
                phone: phone,
                address: document.getElementById('editAddress')?.value?.trim() || '',
                clientType: document.getElementById('editClientType')?.value || 'Regular',
                source: document.getElementById('editSource')?.value || 'Other',
                notes: document.getElementById('editNotes')?.value?.trim() || '',
                preferences: preferences
            };
            updateClient(client.id, updated);
            modal.style.display = 'none';
        });
    }
    // Configurar validación en tiempo real para los campos de edición
    setupEditRealTimeValidation(client);
}

// ── FORMULARIO DE CREACIÓN DE CLIENTE ───────────────────────

// Configura el botón de guardar del formulario de nuevo cliente con validaciones
function setupNewClientForm()
{
    const saveBtn = document.getElementById('saveClientBtn');
    if (!saveBtn) return;

    // Clonar para eliminar listeners previos
    const newBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newBtn, saveBtn);

    newBtn.addEventListener('click', () => {
        clearErrors();
        const firstName = document.getElementById('firstName')?.value?.trim();
        const lastName = document.getElementById('lastName')?.value?.trim();
        const newIdType = document.getElementById('newIdType')?.value || 'V';
        const newIdNumber = document.getElementById('newIdNumber')?.value?.trim() || '';
        const idNumber = newIdType + '-' + newIdNumber;
        const email = document.getElementById('email')?.value?.trim() || '';
        const phone = document.getElementById('phone')?.value?.trim() || '';
        const preferences = document.getElementById('preferences')?.value?.trim() || '';

        // ── VALIDACIONES DEL FORMULARIO DE CREACIÓN ─────────
        if (!firstName || !lastName) {
            showFieldError('firstName', 'El nombre es obligatorio');
            showFieldError('lastName', 'El apellido es obligatorio');
            return;
        }
        if (!newIdNumber) {
            showFieldError('newIdNumber', 'El número de cédula es obligatorio');
            return;
        }
        if (!/^\d{6,8}$/.test(newIdNumber)) {
            showFieldError('newIdNumber', 'La cédula debe tener 6 a 8 dígitos');
            return;
        }
        if (clients.find(c => c.idNumber === idNumber)) {
            showFieldError('newIdNumber', 'Ya existe un cliente con esta cédula.');
            return;
        }
        if (!email) {
            showFieldError('email', 'El correo electrónico es obligatorio');
            return;
        }
        if (!EMAIL_REGEX.test(email)) {
            showFieldError('email', 'El formato del email no es válido');
            return;
        }
        if (clients.find(c => c.email === email)) {
            showFieldError('email', 'Ya existe un cliente con este correo electrónico.');
            return;
        }
        if (!phone) {
            showFieldError('phone', 'El teléfono es obligatorio');
            return;
        }
        if (!PHONE_REGEX_VE.test(phone)) {
            showFieldError('phone', 'Debe tener 11 dígitos, formato: 0XX-XXX-XXXX.');
            return;
        }
        if (clients.find(c => c.phone === phone)) {
            showFieldError('phone', 'Ya existe un cliente con este número de teléfono.');
            return;
        }
        if (preferences) {
            const lines = preferences.split('\n');
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();
                if (line && !PREF_LINE_REGEX.test(line)) {
                    showFieldError('preferences', 'Cada preferencia debe tener el formato: Título: Valor.');
                    return;
                }
            }
        }

        // Enviar nuevo cliente al API y cerrar modal
        const newClient = {
            firstName: firstName,
            lastName: lastName,
            idNumber: idNumber,
            email: email,
            phone: phone,
            address: document.getElementById('address')?.value?.trim() || '',
            clientType: document.getElementById('clientType')?.value || 'Regular',
            source: document.getElementById('source')?.value || 'Other',
            notes: document.getElementById('notes')?.value?.trim() || '',
            preferences: preferences
        };

        createClient(newClient);
        document.getElementById('clientModal').style.display = 'none';
    });
}

// Actualiza el contador total de clientes en el encabezado de filtros
function updateClientCounter()
{
    const span = document.querySelector('.filters-header span');
    if (span) span.textContent = 'Total: ' + clients.length;
}

// ── Validación en tiempo real ──────────────────────────────────

// Construye el número de cédula completo concatenando tipo + número
function getFullId(idTypeId, idNumberId) {
    const t = document.getElementById(idTypeId);
    const n = document.getElementById(idNumberId);
    return (t ? t.value : 'V') + '-' + (n ? n.value.trim() : '');
}

// Valida un campo del formulario de creación en tiempo real
function validateCreateField(fieldId) {
    clearFieldError(fieldId);
    const val = (document.getElementById(fieldId)?.value || '').trim();
    switch (fieldId) {
        case 'firstName':
        case 'lastName':
            if (!val) { showFieldError(fieldId, 'Este campo es obligatorio'); return false; }
            break;
        case 'newIdNumber': {
            if (!val) { showFieldError(fieldId, 'El número de cédula es obligatorio'); return false; }
            if (!/^\d{6,8}$/.test(val)) { showFieldError(fieldId, 'Debe tener 6 a 8 dígitos'); return false; }
            const full = getFullId('newIdType', 'newIdNumber');
            if (clients.find(c => c.idNumber === full)) { showFieldError(fieldId, 'Ya existe un cliente con esta cédula.'); return false; }
            break;
        }
        case 'email': {
            if (!val) { showFieldError(fieldId, 'El correo electrónico es obligatorio'); return false; }
            if (!EMAIL_REGEX.test(val)) { showFieldError(fieldId, 'El formato del email no es válido'); return false; }
            if (clients.find(c => c.email === val)) { showFieldError(fieldId, 'Ya existe un cliente con este correo.'); return false; }
            break;
        }
        case 'phone': {
            if (!val) { showFieldError(fieldId, 'El teléfono es obligatorio'); return false; }
            if (!PHONE_REGEX_VE.test(val)) { showFieldError(fieldId, 'Debe tener 11 dígitos, formato: 0XX-XXX-XXXX.'); return false; }
            if (clients.find(c => c.phone === val)) { showFieldError(fieldId, 'Ya existe un cliente con este teléfono.'); return false; }
            break;
        }
        case 'preferences': {
            if (!val) break;
            const lines = val.split('\n');
            for (const line of lines) {
                const l = line.trim();
                if (l && !PREF_LINE_REGEX.test(l)) { showFieldError(fieldId, 'Formato: Título: Valor.'); return false; }
            }
            break;
        }
    }
    return true;
}

// Valida un campo del formulario de edición en tiempo real
function validateEditField(fieldId, currentClient) {
    clearFieldError(fieldId);
    const val = (document.getElementById(fieldId)?.value || '').trim();
    switch (fieldId) {
        case 'editFirstName':
        case 'editLastName':
            if (!val) { showFieldError(fieldId, 'Este campo es obligatorio'); return false; }
            break;
        case 'editIdNumber': {
            if (!val) { showFieldError(fieldId, 'El número de cédula es obligatorio'); return false; }
            if (!/^\d{6,8}$/.test(val)) { showFieldError(fieldId, 'Debe tener 6 a 8 dígitos'); return false; }
            const full = getFullId('editIdType', 'editIdNumber');
            if (full !== currentClient.idNumber && clients.find(c => c.idNumber === full)) { showFieldError(fieldId, 'Ya existe un cliente con esta cédula.'); return false; }
            break;
        }
        case 'editEmail': {
            if (!val) { showFieldError(fieldId, 'El correo electrónico es obligatorio'); return false; }
            if (!EMAIL_REGEX.test(val)) { showFieldError(fieldId, 'El formato del email no es válido'); return false; }
            if (val !== currentClient.email && clients.find(c => c.email === val && c.id !== currentClient.id)) { showFieldError(fieldId, 'Ya existe un cliente con este correo.'); return false; }
            break;
        }
        case 'editPhone': {
            if (!val) { showFieldError(fieldId, 'El teléfono es obligatorio'); return false; }
            if (!PHONE_REGEX_VE.test(val)) { showFieldError(fieldId, 'Debe tener 11 dígitos, formato: 0XX-XXX-XXXX.'); return false; }
            if (val !== currentClient.phone && clients.find(c => c.phone === val && c.id !== currentClient.id)) { showFieldError(fieldId, 'Ya existe un cliente con este teléfono.'); return false; }
            break;
        }
        case 'editPreferences': {
            if (!val) break;
            const lines = val.split('\n');
            for (const line of lines) {
                const l = line.trim();
                if (l && !PREF_LINE_REGEX.test(l)) { showFieldError(fieldId, 'Formato: Título: Valor.'); return false; }
            }
            break;
        }
    }
    return true;
}

// Asocia eventos de validación en tiempo real a todos los campos del formulario de creación
function setupCreateRealTimeValidation() {
    const fields = ['firstName', 'lastName', 'newIdNumber', 'email', 'phone', 'preferences'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', () => validateCreateField(id));
    });
    // Revalidar cédula si cambia el tipo (V/E)
    const idType = document.getElementById('newIdType');
    if (idType) idType.addEventListener('change', () => validateCreateField('newIdNumber'));
}

// Asocia eventos de validación en tiempo real a todos los campos del formulario de edición
function setupEditRealTimeValidation(client) {
    const fields = ['editFirstName', 'editLastName', 'editIdNumber', 'editEmail', 'editPhone', 'editPreferences'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', () => validateEditField(id, client));
    });
    const idType = document.getElementById('editIdType');
    if (idType) idType.addEventListener('change', () => validateEditField('editIdNumber', client));
}

// ── INICIALIZACIÓN ──────────────────────────────────────────
// Evento principal al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    // Cargar lista de clientes al iniciar
    fetchClients();

    // Botones para abrir el modal de nuevo cliente
    const newBtn1 = document.getElementById('newClientBtn');
    const newBtn2 = document.getElementById('newClientBtn2');
    const openModal = () => {
        clearErrors();
        document.getElementById('clientModal').style.display = 'flex';
    };
    if (newBtn1) newBtn1.addEventListener('click', openModal);
    if (newBtn2) newBtn2.addEventListener('click', openModal);

    // Configurar formulario de creación y validación en tiempo real
    setupNewClientForm();
    setupCreateRealTimeValidation();

    // Botones para cerrar cualquier modal abierto
    const closeIds = ['closeModalBtn', 'closeEditModalBtn', 'cancelModalBtn', 'cancelEditModalBtn'];
    closeIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        });
    });

    // Cerrar modal al hacer clic fuera del contenido (overlay)
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    });

    // ── FILTROS DE TIPO DE CLIENTE ─────────────────────────
    // Filtrar la lista lateral por tipo de cliente (Todos, Frecuentes, Nuevos, VIP)
    const typeMap = { 'todos': null, 'frecuentes': 'Frequent', 'nuevos': 'New', 'vip': 'VIP' };
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filterType = typeMap[this.textContent.trim().toLowerCase()];
            document.querySelectorAll('.client-item').forEach(item => {
                const id = parseInt(item.dataset.id);
                const client = clients.find(c => c.id === id);
                if (!filterType) { item.style.display = 'flex'; return; }
                item.style.display = (client && client.clientType === filterType) ? 'flex' : 'none';
            });
        });
    });

    // ── BÚSQUEDA DE CLIENTES ───────────────────────────────
    // Filtrar la lista lateral por nombre o email en tiempo real
    const searchInput = document.getElementById('searchClient');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.client-item').forEach(item => {
                if (!term) {
                    item.style.display = 'flex';
                    return;
                }
                const name = item.querySelector('h4')?.textContent?.toLowerCase() || '';
                const email = item.querySelector('p')?.textContent?.toLowerCase() || '';
                item.style.display = (name.includes(term) || email.includes(term)) ? 'flex' : 'none';
            });
        });
    }
});
