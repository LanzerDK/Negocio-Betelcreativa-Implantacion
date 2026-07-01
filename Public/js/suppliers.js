let editingSupplierId = null;

document.addEventListener('DOMContentLoaded', function () {
    cargarProveedores();

    document.getElementById('newSupplierBtn').addEventListener('click', function () {
        editingSupplierId = null;
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nuevo Proveedor';
        limpiarFormulario();
        document.getElementById('supplierModal').style.display = 'flex';
    });

    document.getElementById('closeModalBtn').addEventListener('click', function () {
        document.getElementById('supplierModal').style.display = 'none';
    });

    document.getElementById('cancelModalBtn').addEventListener('click', function () {
        document.getElementById('supplierModal').style.display = 'none';
    });

    document.getElementById('saveSupplierBtn').addEventListener('click', guardarProveedor);

    document.getElementById('searchInput')?.addEventListener('input', filtrarProveedores);
});

function limpiarFormulario() {
    document.getElementById('supplierCompany').value = '';
    document.getElementById('supplierContact').value = '';
    document.getElementById('supplierPhone').value = '';
    document.getElementById('supplierEmail').value = '';
    document.getElementById('supplierAddress').value = '';
}

function cargarProveedores() {
    Promise.all([
        callApi(APP_URL + 'Public/api/admin/suppliers.php'),
        callApi(APP_URL + 'Public/api/materials.php')
    ])
        .then(([supRes, matRes]) => {
            if (supRes.success) {
                renderizarProveedores(supRes.data, matRes.success ? matRes.data : []);
            }
        })
        .catch(err => console.error('Error de red:', err));
}

function renderizarProveedores(proveedores, materiales) {
    const container = document.getElementById('suppliersContainer');
    container.innerHTML = '';

    materiales = materiales || [];

    const activos = materiales.filter(m => m.is_active !== false && m.is_active !== 0);

    const total = proveedores.length;
    const activas = proveedores.filter(s => s.is_active !== false && s.is_active !== 0).length;

    const matCountBySup = {};
    activos.forEach(m => {
        const sid = m.supplier_id;
        if (sid) matCountBySup[sid] = (matCountBySup[sid] || 0) + 1;
    });

    const totalVinculados = Object.values(matCountBySup).reduce((a, b) => a + b, 0);

    let topSupplier = '—';
    let maxCount = 0;
    proveedores.forEach(s => {
        const count = matCountBySup[s.id] || 0;
        if (count > maxCount) {
            maxCount = count;
            topSupplier = s.company_name;
        }
    });

    document.getElementById('totalSuppliers').textContent = total;
    document.getElementById('totalSupplierMaterials').textContent = totalVinculados;
    document.getElementById('activeSuppliers').textContent = activas;
    document.getElementById('topSupplier').textContent = topSupplier;

    if (proveedores.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-truck"></i><p>No hay proveedores registrados</p></div>';
        return;
    }

    proveedores.forEach(sup => {
        const card = document.createElement('div');
        const isActive = sup.is_active !== false && sup.is_active !== 0;
        card.className = 'supplier-card' + (!isActive ? ' inhabilitado' : '');
        card.dataset.id = sup.id;
        card.dataset.name = sup.company_name;

        const matCount = matCountBySup[sup.id] || 0;

        const safeName = escapeHtml(sup.company_name);
        const contact = sup.contact_name ? escapeHtml(sup.contact_name) : null;
        const phone = sup.phone ? escapeHtml(sup.phone) : null;
        const email = sup.email ? escapeHtml(sup.email) : null;
        const address = sup.address ? escapeHtml(sup.address) : null;

        card.innerHTML = `
            <div class="supplier-header">
                <div class="supplier-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="supplier-title">
                    <h3>${safeName}</h3>
                    <div class="supplier-status ${isActive ? 'status-active' : 'status-inactive'}">${isActive ? 'Activo' : 'Inactivo'}</div>
                </div>
            </div>
            <div class="supplier-body">
                ${contact ? `<div class="supplier-detail"><i class="fas fa-user"></i> ${contact}</div>` : ''}
                ${phone ? `<div class="supplier-detail"><i class="fas fa-phone"></i> ${phone}</div>` : ''}
                ${email ? `<div class="supplier-detail"><i class="fas fa-envelope"></i> ${email}</div>` : ''}
                ${address ? `<div class="supplier-detail address"><i class="fas fa-map-marker-alt"></i> ${address}</div>` : ''}
                <div class="supplier-detail"><i class="fas fa-box"></i> ${matCount} material(es) vinculado(s)</div>
            </div>
            <div class="supplier-actions">
                <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                <button class="action-btn toggle-btn">
                    <i class="fas ${isActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${isActive ? 'Inhabilitar' : 'Habilitar'}
                </button>
            </div>
        `;

        card.querySelector('.edit-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            if (!isActive) {
                toast('Proveedor inhabilitado. Actívelo primero para editarlo.', 'warning');
                return;
            }
            editarProveedor(sup);
        });

        card.querySelector('.toggle-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            toggleEstadoProveedor(sup.id, isActive, card, this, matCount);
        });

        container.appendChild(card);
    });
}

function editarProveedor(sup) {
    editingSupplierId = sup.id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Proveedor';
    document.getElementById('supplierCompany').value = sup.company_name || '';
    document.getElementById('supplierContact').value = sup.contact_name || '';
    document.getElementById('supplierPhone').value = sup.phone || '';
    document.getElementById('supplierEmail').value = sup.email || '';
    document.getElementById('supplierAddress').value = sup.address || '';
    document.getElementById('supplierModal').style.display = 'flex';
}

function guardarProveedor() {
    const companyName = document.getElementById('supplierCompany').value.trim();
    const contactName = document.getElementById('supplierContact').value.trim();
    const phone = document.getElementById('supplierPhone').value.trim();
    const email = document.getElementById('supplierEmail').value.trim();
    const address = document.getElementById('supplierAddress').value.trim();

    if (!companyName) {
        toast('El nombre de la empresa es obligatorio.', 'warning');
        return;
    }

    const url = APP_URL + 'Public/api/admin/suppliers.php' + (editingSupplierId ? '?id=' + editingSupplierId : '');
    const method = editingSupplierId ? 'PUT' : 'POST';

    callApi(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({
            company_name: companyName,
            contact_name: contactName || null,
            phone: phone || null,
            email: email || null,
            address: address || null
        })
    })
        .then(data => {
            if (data.success) {
                document.getElementById('supplierModal').style.display = 'none';
                cargarProveedores();
            } else {
                toast(data.message, 'error');
            }
        })
        .catch(err => {
            toast(err.message, 'error');
            console.error(err);
        });
}

function toggleEstadoProveedor(id, isActive, card, button, matCount) {
    const newStatus = isActive ? 0 : 1;

    if (newStatus === 0 && matCount > 0) {
        toast('No se puede deshabilitar este proveedor, tiene materiales vinculados.', 'warning');
        return;
    }

    callApi(APP_URL + 'Public/api/admin/suppliers.php?id=' + id, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({ is_active: newStatus })
    })
        .then(data => {
            if (data.success) {
                card.classList.toggle('inhabilitado');
                const nowActive = newStatus === 1;
                button.innerHTML = `<i class="fas ${nowActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${nowActive ? 'Inhabilitar' : 'Habilitar'}`;
                const badge = card.querySelector('.supplier-status');
                badge.textContent = nowActive ? 'Activo' : 'Inactivo';
                badge.className = 'supplier-status ' + (nowActive ? 'status-active' : 'status-inactive');

                const activasEl = document.getElementById('activeSuppliers');
                if (activasEl) {
                    const current = parseInt(activasEl.textContent);
                    activasEl.textContent = nowActive ? current + 1 : current - 1;
                }
            } else {
                toast(data.message, 'error');
            }
        })
        .catch(err => {
            toast(err.message, 'error');
            console.error(err);
        });
}

function filtrarProveedores() {
    const term = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.supplier-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        card.style.display = (!term || name.includes(term)) ? '' : 'none';
    });
}
