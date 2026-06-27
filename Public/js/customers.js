// =============================================
// customers.js - Gestión de Clientes
// Consume la API REST /api/customers.php
// =============================================

let clients = [];
let currentClientId = null;

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_REGEX = /^[\d\s\-\+\(\)]{7,20}$/;

function showError(msg) {
    const existing = document.querySelector('.toast-error');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'toast-error';
    toast.style.cssText = 'position:fixed;top:20px;right:20px;background:#dc3545;color:#fff;padding:15px 25px;border-radius:8px;z-index:9999;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

async function fetchClients()
{
    try {
        const data = await callApi(APP_URL + 'Public/api/customers.php');
        if (data.success) {
            clients = data.data;
            updateClientCounter();
            renderClientsList();
            if (clients.length > 0) {
                const firstItem = document.querySelector('.client-item');
                if (firstItem) firstItem.click();
            } else {
                const noSel = document.getElementById('noClientSelected');
                const detail = document.getElementById('clientDetail');
                if (noSel) noSel.style.display = 'flex';
                if (detail) detail.classList.remove('active');
            }
        }
    } catch (err) {
        console.error('Error al cargar clientes:', err);
        showError('Error al cargar clientes');
    }
}

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
            showError(data.message || 'Error al crear el cliente');
        }
    } catch (err) {
        console.error('Error al crear cliente:', err);
        showError('Error de conexión');
    }
}

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
            const idx = clients.findIndex(c => c.id === id);
            if (idx !== -1) {
                clients[idx] = { ...clients[idx], ...clientData };
            }
            renderClientDetails(id);
        } else {
            showError(data.message || 'Error al actualizar el cliente');
        }
    } catch (err) {
        console.error('Error al actualizar cliente:', err);
        showError('Error de conexión');
    }
}

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
            showError(data.message || 'Error al cambiar estado del cliente');
        }
    } catch (err) {
        console.error('Error al cambiar estado del cliente:', err);
        showError('Error de conexión');
    }
}

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

        item.addEventListener('click', () => {
            document.querySelectorAll('.client-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            renderClientDetails(client.id);
        });

        clientsList.appendChild(item);
    });
}

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
            <div class="preferences-grid">
                ${getPreferencesCards(client.preferences)}
            </div>
        </div>
    `;

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

    const toggleBtn = document.getElementById('toggleClientBtn');
    if (toggleBtn) toggleBtn.addEventListener('click', () => toggleClientStatus(client.id, client.isActive));
}

function getClientTypeLabel(type) {
    const labels = { Regular: 'Regular', Frequent: 'Cliente frecuente', VIP: 'VIP', New: 'Nuevo' };
    return labels[type] || type;
}

function getSourceLabel(source) {
    const labels = { Recommendation: 'Recomendación', 'Social Media': 'Redes Sociales', Website: 'Sitio Web', Event: 'En un evento', Other: 'Otro' };
    return labels[source] || source;
}

function getPreferencesCards(preferences) {
    if (!preferences) return '<p>Sin preferencias registradas</p>';
    const lines = preferences.split('\n');
    const cards = [];
    const iconMap = { 'colores': 'fas fa-palette', 'estilo': 'fas fa-heart', 'no gusta': 'fas fa-times-circle', 'alergias': 'fas fa-allergies' };
    lines.forEach(line => {
        const parts = line.split(':');
        if (parts.length < 2) return;
        const title = parts[0].trim();
        const value = parts.slice(1).join(':').trim();
        const key = title.toLowerCase();
        cards.push(`
            <div class="preference-card">
                <div class="preference-icon"><i class="${iconMap[key] || 'fas fa-info-circle'}"></i></div>
                <div class="preference-title">${title}</div>
                <div class="preference-value">${value}</div>
            </div>
        `);
    });
    return cards.join('') || '<p>Sin preferencias registradas</p>';
}

function openEditClientModal(client)
{
    const modal = document.getElementById('editClientModal');
    if (!modal) return;
    modal.style.display = 'flex';

    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    setVal('editFirstName', client.firstName);
    setVal('editLastName', client.lastName);
    setVal('editEmail', client.email);
    setVal('editPhone', client.phone);
    setVal('editAddress', client.address);
    setVal('editClientType', client.clientType);
    setVal('editSource', client.source);
    setVal('editNotes', client.notes);
    setVal('editPreferences', client.preferences);

    const saveBtn = document.getElementById('saveEditBtn');
    if (saveBtn) {
        const newBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newBtn, saveBtn);
        newBtn.addEventListener('click', () => {
            const email = document.getElementById('editEmail')?.value?.trim() || '';
            const phone = document.getElementById('editPhone')?.value?.trim() || '';
            const firstName = document.getElementById('editFirstName')?.value?.trim() || '';
            const lastName = document.getElementById('editLastName')?.value?.trim() || '';

            if (!firstName || !lastName) {
                showError('El nombre y apellido son obligatorios');
                return;
            }
            if (email && !EMAIL_REGEX.test(email)) {
                showError('El formato del email no es válido');
                return;
            }
            if (phone && !PHONE_REGEX.test(phone)) {
                showError('El formato del teléfono no es válido');
                return;
            }

            const updated = {
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone,
                address: document.getElementById('editAddress')?.value?.trim() || '',
                clientType: document.getElementById('editClientType')?.value || 'Regular',
                source: document.getElementById('editSource')?.value || 'Other',
                notes: document.getElementById('editNotes')?.value?.trim() || '',
                preferences: document.getElementById('editPreferences')?.value?.trim() || ''
            };
            updateClient(client.id, updated);
            modal.style.display = 'none';
        });
    }
}

function setupNewClientForm()
{
    const saveBtn = document.getElementById('saveClientBtn');
    if (!saveBtn) return;

    const newBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newBtn, saveBtn);

    newBtn.addEventListener('click', () => {
        const firstName = document.getElementById('firstName')?.value?.trim();
        const lastName = document.getElementById('lastName')?.value?.trim();
        const email = document.getElementById('email')?.value?.trim() || '';
        const phone = document.getElementById('phone')?.value?.trim() || '';

        if (!firstName || !lastName) {
            showError('Por favor, complete al menos nombre y apellido');
            return;
        }
        if (email && !EMAIL_REGEX.test(email)) {
            showError('El formato del email no es válido');
            return;
        }
        if (phone && !PHONE_REGEX.test(phone)) {
            showError('El formato del teléfono no es válido');
            return;
        }

        const newClient = {
            firstName: firstName,
            lastName: lastName,
            email: email,
            phone: phone,
            address: document.getElementById('address')?.value?.trim() || '',
            clientType: document.getElementById('clientType')?.value || 'Regular',
            source: document.getElementById('source')?.value || 'Other',
            notes: document.getElementById('notes')?.value?.trim() || '',
            preferences: document.getElementById('preferences')?.value?.trim() || ''
        };

        createClient(newClient);
        document.getElementById('clientModal').style.display = 'none';
    });
}

function updateClientCounter()
{
    const span = document.querySelector('.filters-header span');
    if (span) span.textContent = 'Total: ' + clients.length;
}

document.addEventListener('DOMContentLoaded', () => {
    fetchClients();

    const newBtn1 = document.getElementById('newClientBtn');
    const newBtn2 = document.getElementById('newClientBtn2');
    const openModal = () => document.getElementById('clientModal').style.display = 'flex';
    if (newBtn1) newBtn1.addEventListener('click', openModal);
    if (newBtn2) newBtn2.addEventListener('click', openModal);

    setupNewClientForm();

    const closeIds = ['closeModalBtn', 'closeEditModalBtn', 'cancelModalBtn', 'cancelEditModalBtn'];
    closeIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    });

    // Status filter buttons
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
