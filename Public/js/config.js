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
            document.querySelector('.imagenfoto img').src = APP_URL + 'Public/' + json.data.avatar_url;
            toast('Avatar actualizado', 'success');
        } else {
            toast(json.message || 'Error al subir avatar', 'error');
        }
    } catch (err) {
        toast('Error de conexión', 'error');
    }
});

// ── Logo del Sistema ────────────────────────────────────
document.getElementById('logoInput')?.addEventListener('change', async function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('logo', file);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        const json = await callApi(API + 'upload-logo.php', { method: 'POST', body: formData });
        if (json.success) {
            document.getElementById('logoPreview').src = APP_URL + 'Public/' + json.data.logo_url;
            document.querySelector('.imagenfoto img').src = APP_URL + 'Public/' + json.data.logo_url;
            toast('Logo del sistema actualizado', 'success');
        } else {
            toast(json.message || 'Error al subir logo', 'error');
        }
    } catch (err) {
        toast('Error de conexión', 'error');
    }
});

document.getElementById('btnRestaurarLogo')?.addEventListener('click', async function () {
    if (!confirm('¿Restaurar el logo original del sistema?')) return;

    try {
        const json = await callApi(API + 'settings.php?action=batch-update', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ settings: { system_logo: '' } }),
        });
        if (json.success) {
            document.getElementById('logoPreview').src = APP_URL + 'Public/images/BetEl.png';
            document.querySelector('.imagenfoto img').src = APP_URL + 'Public/images/BetEl.png';
            toast('Logo restaurado', 'success');
        } else {
            toast(json.message || 'Error al restaurar logo', 'error');
        }
    } catch (_) {
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

// ── Facturacion: cargar terminos ─────────────────────────
async function loadBillingSettings() {
    try {
        const json = await callApi(API + 'facturas.php?action=terminos', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (json.success && json.data?.terminos) {
            const el = document.getElementById('set_terminos_condiciones');
            if (el) el.value = json.data.terminos;
        }
    } catch (_) { /* ignore */ }
}

// ── Facturacion: guardar ─────────────────────────────────
document.getElementById('saveBilling')?.addEventListener('click', async function () {
    const terminos = document.getElementById('set_terminos_condiciones')?.value.trim() || '';
    try {
        const json = await callApi(API + 'settings.php?action=batch-update', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ settings: { terminos_condiciones: terminos } }),
        });
        if (json.success) {
            toast('Configuración de facturación guardada.', 'success');
        } else {
            toast(json.message || 'Error al guardar.', 'error');
        }
    } catch (_) {
        toast('Error de conexión.', 'error');
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
                    <!-- <option value="super_admin" ${u.role === 'super_admin' ? 'selected' : ''}>Super Admin</option> -->
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


// ── Users: búsqueda con debounce ─────────────────────────
document.getElementById('userSearch')?.addEventListener('input', function () {
    clearTimeout(this._timer);
    this._timer = setTimeout(() => loadUsers(1), 400);
});

// ── Crear Usuario (modal) ────────────────────────────────
function abrirModalCrearUsuario() {
    document.getElementById('modalCrearUsuario').style.display = 'flex';
    document.getElementById('formCrearUsuario').reset();
    document.querySelectorAll('#formCrearUsuario .feedback').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.querySelectorAll('#formCrearUsuario input').forEach(el => {
        el.classList.remove('valid', 'invalid');
    });
}

function cerrarModalCrearUsuario() {
    document.getElementById('modalCrearUsuario').style.display = 'none';
}

document.getElementById('btnCrearUsuario')?.addEventListener('click', abrirModalCrearUsuario);
document.getElementById('cerrarModalUsuario')?.addEventListener('click', cerrarModalCrearUsuario);
document.getElementById('cancelarCrearUsuario')?.addEventListener('click', cerrarModalCrearUsuario);

document.getElementById('modalCrearUsuario')?.addEventListener('click', function (e) {
    if (e.target === this) cerrarModalCrearUsuario();
});

document.getElementById('guardarCrearUsuario')?.addEventListener('click', async function () {
    const fields = {
        name: document.getElementById('cu_name'),
        last_name: document.getElementById('cu_lastName'),
        username: document.getElementById('cu_username'),
        email: document.getElementById('cu_email'),
        ci: document.getElementById('cu_ci'),
        phone: document.getElementById('cu_phone'),
        password: document.getElementById('cu_password'),
    };

    let valid = true;
    Object.entries(fields).forEach(([key, el]) => {
        const fb = document.getElementById('cu_' + key + '-feedback');
        if (!el.value.trim() || (key === 'password' && el.value.length < 6)) {
            el.classList.add('invalid'); el.classList.remove('valid');
            if (fb) { fb.textContent = key === 'password' ? 'Mínimo 6 caracteres' : 'Campo obligatorio'; fb.className = 'feedback invalid-feedback'; }
            valid = false;
        } else {
            el.classList.remove('invalid'); el.classList.add('valid');
            if (fb) { fb.textContent = '\u2713'; fb.className = 'feedback valid-feedback'; }
        }
    });

    if (!valid) {
        toast('Corrige los campos marcados', 'error');
        return;
    }

    const tipoCi = document.getElementById('cu_tipoCi').value;
    const ciCompleta = tipoCi + '-' + fields.ci.value.trim();

    const body = JSON.stringify({
        name: fields.name.value.trim(),
        last_name: fields.last_name.value.trim(),
        username: fields.username.value.trim(),
        email: fields.email.value.trim(),
        ci: ciCompleta,
        phone: fields.phone.value.trim(),
        password: fields.password.value,
    });

    try {
        const json = await callApi(API + 'admin/users.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body,
        });
        if (json.success) {
            toast('Usuario creado correctamente', 'success');
            cerrarModalCrearUsuario();
            loadUsers(usersPage);
        } else {
            toast(json.message, 'error');
            if (json.errors) {
                Object.entries(json.errors).forEach(([field, msg]) => {
                    const fb = document.getElementById('cu_' + field + '-feedback');
                    const input = document.getElementById('cu_' + field);
                    if (fb && input) {
                        input.classList.add('invalid');
                        fb.textContent = msg;
                        fb.className = 'feedback invalid-feedback';
                    }
                });
            }
        }
    } catch (_) {
        toast('Error de conexión', 'error');
    }
});

// ── Init ─────────────────────────────────────────────────
loadProfile();
loadPreferences();
if (document.getElementById('billing-section')) loadBillingSettings();
if (document.getElementById('users-section')) loadUsers();
