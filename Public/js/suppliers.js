// Estado global: ID del proveedor que se está editando (null si es nuevo)
let editingSupplierId = null;

// Inicialización al cargar el DOM
document.addEventListener('DOMContentLoaded', function () {
    // Cargar proveedores y materiales desde la API
    cargarProveedores();

    // Abrir modal de nuevo proveedor y limpiar formulario
    document.getElementById('newSupplierBtn').addEventListener('click', function () {
        editingSupplierId = null;
        document.getElementById('supplierModalLabel').textContent = 'Nuevo Proveedor';
        limpiarFormulario();
        Modal.open('supplierModal');
    });

    // Guardar proveedor al hacer clic en el botón
    document.getElementById('saveSupplierBtn').addEventListener('click', guardarProveedor);

    // Filtrar proveedores en tiempo real al escribir en el buscador
    document.getElementById('searchInput')?.addEventListener('input', filtrarProveedores);

    // Alternar campos visibles según el tipo de proveedor (fijo o comodín)
    document.getElementById('supplierType').addEventListener('change', function () {
        toggleSupplierFields(this.value);
    });

    // Limpiar campo de búsqueda y restaurar lista completa
    document.getElementById('btnLimpiarProveedores')?.addEventListener('click', function () {
        document.getElementById('searchInput').value = '';
        filtrarProveedores();
    });
});

// Mostrar u ocultar campos del formulario según el tipo de proveedor seleccionado
function toggleSupplierFields(type) {
    const isComodin = type === 'comodin';
    document.getElementById('fijoFields').style.display = isComodin ? 'none' : 'block';
    document.getElementById('comodinFields').style.display = isComodin ? 'block' : 'none';
    // Limpiar campos del tipo que se oculta para evitar datos residuales
    if (!isComodin) {
        document.getElementById('supplierSubtype').value = '';
        document.getElementById('supplierNotes').value = '';
    } else {
        document.getElementById('supplierContact').value = '';
        document.getElementById('supplierPhone').value = '';
        document.getElementById('supplierEmail').value = '';
        document.getElementById('supplierAddress').value = '';
    }
}

// Resetear todos los campos del formulario de proveedor
function limpiarFormulario() {
    document.getElementById('supplierCompany').value = '';
    document.getElementById('supplierContact').value = '';
    document.getElementById('supplierPhone').value = '';
    document.getElementById('supplierEmail').value = '';
    document.getElementById('supplierAddress').value = '';
    document.getElementById('supplierType').value = 'fijo';
    document.getElementById('supplierSubtype').value = '';
    document.getElementById('supplierNotes').value = '';
    toggleSupplierFields('fijo');
}

// Cargar proveedores y materiales en paralelo, luego renderizar
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

// Renderizar tarjetas de proveedores y estadísticas del encabezado
function renderizarProveedores(proveedores, materiales) {
    const container = document.getElementById('suppliersContainer');
    container.innerHTML = '';

    materiales = materiales || [];

    // Filtrar solo materiales activos para conteo
    const activos = materiales.filter(m => m.is_active !== false && m.is_active !== 0);

    // Calcular estadísticas: total, activos y materiales vinculados
    const total = proveedores.length;
    const activas = proveedores.filter(s => s.is_active !== false && s.is_active !== 0).length;

    // Contar materiales activos por proveedor (supplier_id como clave)
    const matCountBySup = {};
    activos.forEach(m => {
        const sid = m.supplier_id;
        if (sid) matCountBySup[sid] = (matCountBySup[sid] || 0) + 1;
    });

    // Sumar total de materiales vinculados a todos los proveedores
    const totalVinculados = Object.values(matCountBySup).reduce((a, b) => a + b, 0);

    // Determinar el proveedor con mayor cantidad de materiales vinculados
    let topSupplier = '—';
    let maxCount = 0;
    proveedores.forEach(s => {
        const count = matCountBySup[s.id] || 0;
        if (count > maxCount) {
            maxCount = count;
            topSupplier = s.company_name;
        }
    });

    // Actualizar valores de las tarjetas de estadísticas en el DOM
    document.getElementById('totalSuppliers').textContent = total;
    document.getElementById('totalSupplierMaterials').textContent = totalVinculados;
    document.getElementById('activeSuppliers').textContent = activas;
    document.getElementById('topSupplier').textContent = topSupplier;

    // Mostrar estado vacío si no hay proveedores
    if (proveedores.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-truck"></i><p>No hay proveedores registrados</p></div>';
        return;
    }

    // Crear tarjeta DOM por cada proveedor
    proveedores.forEach(sup => {
        const card = document.createElement('div');
        const isActive = sup.is_active !== false && sup.is_active !== 0;
        card.className = 'supplier-card' + (!isActive ? ' inhabilitado' : '');
        card.dataset.id = sup.id;
        card.dataset.name = sup.company_name;

        const matCount = matCountBySup[sup.id] || 0;

        // Escapar datos para prevenir XSS
        const safeName = escapeHtml(sup.company_name);
        const contact = sup.contact_name ? escapeHtml(sup.contact_name) : null;
        const phone = sup.phone ? escapeHtml(sup.phone) : null;
        const email = sup.email ? escapeHtml(sup.email) : null;
        const address = sup.address ? escapeHtml(sup.address) : null;

        // Determinar badge de tipo (fijo o comodín) y subetiqueta
        const isComodin = sup.supplier_type === 'comodin';
        const typeBadge = isComodin
            ? `<span class="supplier-type-badge comodin"><i class="fas fa-exchange-alt"></i> Comodín</span>`
            : `<span class="supplier-type-badge fijo"><i class="fas fa-check-circle"></i> Fijo</span>`;
        const subtypeLabel = isComodin && sup.subtype
            ? `<div class="subtype-label"><i class="fas fa-tag"></i> ${escapeHtml(sup.subtype)}</div>`
            : '';

        // Construir HTML de la tarjeta con header, body y acciones
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
                ${typeBadge}
                ${subtypeLabel}
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

        // Evento: editar proveedor (bloquea si está inactivo)
        card.querySelector('.edit-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            if (!isActive) {
                toast('Proveedor inhabilitado. Actívelo primero para editarlo.', 'warning');
                return;
            }
            editarProveedor(sup);
        });

        // Evento: alternar estado activo/inactivo
        card.querySelector('.toggle-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            toggleEstadoProveedor(sup.id, isActive, card, this, matCount);
        });

        container.appendChild(card);
    });
}

// Abrir modal de edición con los datos del proveedor seleccionado
function editarProveedor(sup) {
    editingSupplierId = sup.id;
    document.getElementById('supplierModalLabel').textContent = 'Editar Proveedor';
    document.getElementById('supplierCompany').value = sup.company_name || '';
    document.getElementById('supplierContact').value = sup.contact_name || '';
    document.getElementById('supplierPhone').value = sup.phone || '';
    document.getElementById('supplierEmail').value = sup.email || '';
    document.getElementById('supplierAddress').value = sup.address || '';
    document.getElementById('supplierType').value = sup.supplier_type || 'fijo';
    document.getElementById('supplierSubtype').value = sup.subtype || '';
    document.getElementById('supplierNotes').value = sup.notes || '';
    // Mostrar campos según el tipo de proveedor (fijo o comodín)
    toggleSupplierFields(sup.supplier_type || 'fijo');
    Modal.open('supplierModal');
}

// Validar formulario y guardar proveedor (crear o actualizar)
function guardarProveedor() {
    const companyName = document.getElementById('supplierCompany').value.trim();
    const contactName = document.getElementById('supplierContact').value.trim();
    const phone = document.getElementById('supplierPhone').value.trim();
    const email = document.getElementById('supplierEmail').value.trim();
    const address = document.getElementById('supplierAddress').value.trim();

    // Validar que el nombre de la empresa sea obligatorio
    if (!companyName) {
        toast('El nombre de la empresa es obligatorio.', 'warning');
        return;
    }

    // Construir URL y método HTTP según si es edición o creación
    const url = APP_URL + 'Public/api/admin/suppliers.php' + (editingSupplierId ? '?id=' + editingSupplierId : '');
    const method = editingSupplierId ? 'PUT' : 'POST';

    // Enviar datos del proveedor al servidor
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
            address: address || null,
            notes: document.getElementById('supplierNotes').value.trim() || null,
            supplier_type: document.getElementById('supplierType').value || 'fijo',
            subtype: document.getElementById('supplierSubtype').value || null
        })
    })
        .then(data => {
            if (data.success) {
                Modal.close('supplierModal');
                // Recargar la lista de proveedores después de guardar
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

// Alternar estado activo/inactivo de un proveedor con validación de materiales vinculados
function toggleEstadoProveedor(id, isActive, card, button, matCount) {
    const newStatus = isActive ? 0 : 1;

    // Bloquear deshabilitar si tiene materiales vinculados
    if (newStatus === 0 && matCount > 0) {
        toast('No se puede deshabilitar este proveedor, tiene materiales vinculados.', 'warning');
        return;
    }

    // Enviar cambio de estado al servidor
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
                // Actualizar clase visual de la tarjeta
                card.classList.toggle('inhabilitado');
                const nowActive = newStatus === 1;
                // Actualizar texto e ícono del botón de toggle
                button.innerHTML = `<i class="fas ${nowActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${nowActive ? 'Inhabilitar' : 'Habilitar'}`;
                // Actualizar badge de estado
                const badge = card.querySelector('.supplier-status');
                badge.textContent = nowActive ? 'Activo' : 'Inactivo';
                badge.className = 'supplier-status ' + (nowActive ? 'status-active' : 'status-inactive');

                // Actualizar contador de proveedores activos en estadísticas
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

// Filtrar tarjetas de proveedores por nombre según el término de búsqueda
function filtrarProveedores() {
    const term = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.supplier-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        card.style.display = (!term || name.includes(term)) ? '' : 'none';
    });
}