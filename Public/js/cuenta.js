const API = APP_URL + 'Public/api/';

document.querySelectorAll('.config-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.config-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.config-section').forEach(s => s.classList.remove('active'));
        document.getElementById(this.getAttribute('data-target') + '-section').classList.add('active');
    });
});

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

async function loadProfile() {
    try {
        const json = await callApi(API + 'users.php?action=profile');
        if (!json.success) return;
        const d = json.data;
        document.getElementById('name').value = d.name || '';
        document.getElementById('lastName').value = d.last_name || '';
        document.getElementById('ci').value = d.ci || '';
        document.getElementById('email').value = d.email || '';
        document.getElementById('phone').value = d.phone || '';
        document.getElementById('userSince').textContent = 'Miembro desde: ' + (d.member_since ? new Date(d.member_since).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : '--');
    } catch (_) {}
}

document.getElementById('saveProfile')?.addEventListener('click', async function () {
    const body = JSON.stringify({
        name: document.getElementById('name').value.trim(),
        last_name: document.getElementById('lastName').value.trim(),
        ci: document.getElementById('ci').value.trim(),
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
            document.getElementById('userName').textContent = document.getElementById('name').value + ' ' + document.getElementById('lastName').value;
            document.getElementById('userEmail').textContent = document.getElementById('email').value;
        } else {
            toast(json.message, 'error');
        }
    } catch (_) {
        toast('Error de conexión', 'error');
    }
});

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

loadProfile();
