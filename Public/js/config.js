const API = APP_URL + 'Public/api/';

// ── Tabs ────────────────────────────────────────────────
document.querySelectorAll('.config-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.config-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.config-section').forEach(s => s.classList.remove('active'));
        document.getElementById(this.getAttribute('data-target') + '-section').classList.add('active');
    });
});

// ── Avatar ──────────────────────────────────────────────
document.querySelector('.avatar-upload')?.addEventListener('click', () => {
    document.getElementById('avatarInput').click();
});

document.getElementById('avatarInput').addEventListener('change', async function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        const json = await callApi(API + 'upload.php', { method: 'POST', body: formData });
        if (json.success) {
            document.getElementById('userAvatar').src = APP_URL + 'Public/' + json.data.avatar_url;
            toast('Avatar actualizado', 'success');
        } else {
            toast(json.message || 'Error al subir avatar', 'error');
        }
    } catch (err) {
        toast('Error de conexión', 'error');
    }
});

// ── Cargar perfil ───────────────────────────────────────
async function loadProfile() {
    try {
        const json = await callApi(API + 'users.php?action=profile', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!json.success) return;
        const d = json.data;
        document.getElementById('name').value = d.name || '';
        document.getElementById('lastName').value = d.last_name || '';
        document.getElementById('email').value = d.email || '';
        document.getElementById('phone').value = d.phone || '';
    } catch (_) { /* ignore */ }
}

// ── Guardar perfil ──────────────────────────────────────
document.getElementById('saveProfile')?.addEventListener('click', async function () {
    const body = JSON.stringify({
        name: document.getElementById('name').value.trim(),
        last_name: document.getElementById('lastName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
    });

    try {
        const json = await callApi(API + 'users.php?action=profile', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body,
        });
        if (json.success) {
            toast('Perfil actualizado', 'success');
            document.querySelector('.user-details h2').textContent = document.getElementById('name').value + ' ' + document.getElementById('lastName').value;
        } else {
            toast(json.message, 'error');
        }
    } catch (_) {
        toast('Error de conexión', 'error');
    }
});

// ── Notificaciones: cargar ──────────────────────────────
async function loadPreferences() {
    try {
        const json = await callApi(API + 'users.php?action=preferences', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!json.success) return;
        const p = json.data;
        document.getElementById('notify_low_stock').checked = p.notify_low_stock;
        document.getElementById('notify_appointments').checked = p.notify_appointments;
        document.getElementById('notify_security').checked = p.notify_security;
        document.getElementById('notify_reports').checked = p.notify_reports;
    } catch (_) { /* ignore */ }
}

// ── Notificaciones: guardar ─────────────────────────────
document.getElementById('saveNotifications')?.addEventListener('click', async function () {
    const body = JSON.stringify({
        notify_low_stock: document.getElementById('notify_low_stock').checked,
        notify_appointments: document.getElementById('notify_appointments').checked,
        notify_security: document.getElementById('notify_security').checked,
        notify_reports: document.getElementById('notify_reports').checked,
    });

    try {
        const json = await callApi(API + 'users.php?action=preferences', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body,
        });
        if (json.success) {
            toast('Preferencias guardadas', 'success');
        } else {
            toast(json.message, 'error');
        }
    } catch (_) {
        toast('Error de conexión', 'error');
    }
});

// ── Seguridad ───────────────────────────────────────────
document.getElementById('changePasswordBtn')?.addEventListener('click', function () {
    const form = document.getElementById('passwordForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('cancelPassword')?.addEventListener('click', () => {
    document.getElementById('passwordForm').style.display = 'none';
});

document.getElementById('savePassword')?.addEventListener('click', async function () {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    if (!current || !newPass || !confirm) {
        toast('Completa todos los campos', 'error');
        return;
    }
    if (newPass !== confirm) {
        toast('Las contraseñas no coinciden', 'error');
        return;
    }
    if (newPass.length < 6) {
        toast('Mínimo 6 caracteres', 'error');
        return;
    }

    try {
        const json = await callApi(API + 'users.php?action=change-password', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ current_password: current, new_password: newPass }),
        });
        if (json.success) {
            toast('Contraseña actualizada', 'success');
            document.getElementById('passwordForm').style.display = 'none';
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            toast(json.message, 'error');
        }
    } catch (_) {
        toast('Error de conexión', 'error');
    }
});

// ── Sistema: cargar settings ────────────────────────────
async function loadSystemSettings() {
    try {
        const json = await callApi(API + 'settings.php?action=list', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!json.success) return;
        json.data.forEach(item => {
            const el = document.getElementById('set_' + item.key);
            if (el) el.value = item.value;
        });
    } catch (_) { /* ignore */ }
}

// ── Sistema: guardar ────────────────────────────────────
document.getElementById('saveSystem')?.addEventListener('click', async function () {
    const keys = [
        'low_stock_threshold', 'pagination_default', 'dashboard_refresh_interval',
        'password_min_length', 'appointment_default_duration', 'business_hours_start',
        'business_hours_end', 'working_days', 'backup_frequency', 'log_retention_days',
    ];

    const settings = {};
    for (const key of keys) {
        const el = document.getElementById('set_' + key);
        if (el) settings[key] = el.value;
    }
    if (Object.keys(settings).length === 0) return;
    try {
        const json = await callApi(API + 'settings.php?action=batch-update', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ settings }),
        });
        if (json.success) {
            toast(json.message || 'Configuración del sistema guardada', 'success');
        } else {
            toast(json.message || 'Error al guardar configuración', 'error');
        }
    } catch (_) {
        toast('Error de conexión al guardar configuración', 'error');
    }
});

// ── Users: cargar lista ──────────────────────────────────
let usersPage = 1;
let usersSearch = '';

async function loadUsers(page = 1) {
    usersPage = page;
    const search = document.getElementById('userSearch')?.value.trim() || '';
    usersSearch = search;
    try {
        const url = `${API}admin/users.php?action=list&page=${page}&search=${encodeURIComponent(search)}`;
        const json = await callApi(url, { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!json.success) return;
        renderUsers(json.data);
    } catch (_) { /* ignore */ }
}

function renderUsers(data) {
    const tbody = document.getElementById('usersTableBody');
    const pagination = document.getElementById('usersPagination');
    if (!tbody) return;

    if (!data.users.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:var(--gray);">No se encontraron usuarios.</td></tr>';
        if (pagination) pagination.innerHTML = '';
        return;
    }

    tbody.innerHTML = data.users.map(u => `
        <tr>
            <td><strong>${escapeHtml(u.name + ' ' + u.last_name)}</strong><br><small style="color:var(--gray);">@${escapeHtml(u.username)}</small></td>
            <td>${escapeHtml(u.email)}</td>
            <td>
                <select class="role-select" data-user-id="${u.id}" ${u.id === USER_ID ? 'disabled' : ''}>
                    <option value="user" ${u.role === 'user' ? 'selected' : ''}>Usuario</option>
                    <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>Admin</option>
                </select>
            </td>
            <td>
                <span class="status-badge ${u.is_active ? 'status-active' : 'status-inactive'}">
                    ${u.is_active ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                ${u.id !== USER_ID ? `
                    <button class="btn-icon toggle-active-btn" data-user-id="${u.id}" title="${u.is_active ? 'Desactivar' : 'Activar'}">
                        <i class="fas ${u.is_active ? 'fa-ban' : 'fa-check-circle'}"></i>
                    </button>
                ` : '<small style="color:var(--gray);">(tú)</small>'}
            </td>
        </tr>
    `).join('');

    // Paginación
    if (pagination) {
        pagination.innerHTML = '';
        for (let i = 1; i <= data.total_pages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'btn btn-sm' + (i === data.page ? ' btn-primary' : ' btn-secondary');
            btn.style.margin = '0 2px';
            btn.addEventListener('click', () => loadUsers(i));
            pagination.appendChild(btn);
        }
    }

    // Eventos: cambio de rol
    document.querySelectorAll('.role-select').forEach(sel => {
        sel.addEventListener('change', async function () {
            const userId = this.dataset.userId;
            const role = this.value;
            try {
                const json = await callApi(`${API}admin/users.php?action=role`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ user_id: parseInt(userId), role }),
                });
                if (json.success) {
                    toast('Rol actualizado', 'success');
                } else {
                    toast(json.message, 'error');
                    loadUsers(usersPage);
                }
            } catch (_) {
                toast('Error de conexión', 'error');
            }
        });
    });

    // Eventos: toggle active
    document.querySelectorAll('.toggle-active-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const userId = this.dataset.userId;
            try {
                const json = await callApi(`${API}admin/users.php?action=toggle-active`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ user_id: parseInt(userId) }),
                });
                if (json.success) {
                    toast('Estado cambiado', 'success');
                    loadUsers(usersPage);
                } else {
                    toast(json.message, 'error');
                }
            } catch (_) {
                toast('Error de conexión', 'error');
            }
        });
    });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ── Users: búsqueda con debounce ─────────────────────────
document.getElementById('userSearch')?.addEventListener('input', function () {
    clearTimeout(this._timer);
    this._timer = setTimeout(() => loadUsers(1), 400);
});

// ── Init ─────────────────────────────────────────────────
loadProfile();
loadPreferences();
if (document.getElementById('system-section')) loadSystemSettings();
if (document.getElementById('users-section')) loadUsers();
