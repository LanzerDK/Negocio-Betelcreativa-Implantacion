let editingCategoryId = null;

document.addEventListener('DOMContentLoaded', function () {
    cargarCategorias();

    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function () {
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.getElementById('newCategoryBtn').addEventListener('click', function () {
        editingCategoryId = null;
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Categoría';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryStatus').value = 'Active';
        document.getElementById('categoryModal').style.display = 'flex';
    });

    document.getElementById('closeModalBtn').addEventListener('click', function () {
        document.getElementById('categoryModal').style.display = 'none';
    });

    document.getElementById('cancelModalBtn').addEventListener('click', function () {
        document.getElementById('categoryModal').style.display = 'none';
    });

    document.getElementById('saveCategoryBtn').addEventListener('click', guardarCategoria);

    document.getElementById('searchInput')?.addEventListener('input', filtrarCategorias);
});

function cargarCategorias() {
    Promise.all([
        callApi(APP_URL + 'Public/api/categories.php'),
        callApi(APP_URL + 'Public/api/materials.php')
    ])
        .then(([catRes, matRes]) => {
            if (catRes.success) {
                renderizarCategorias(catRes.data, matRes.success ? matRes.data : []);
            }
        })
        .catch(err => console.error('Error de red:', err));
}

function renderizarCategorias(categorias, materiales) {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = '';

    materiales = materiales || [];

    // Solo contar materiales activos
    const activos = materiales.filter(m => m.is_active !== false && m.is_active !== 0);

    const total = categorias.length;
    const activas = categorias.filter(c => c.status === 'Active').length;
    const totalMateriales = activos.length;

    // Cuenta materiales activos por categoría
    const matCountByCat = {};
    activos.forEach(m => {
        const cid = m.category_id;
        matCountByCat[cid] = (matCountByCat[cid] || 0) + 1;
    });

    // Encuentra la categoría con más materiales
    let popularCat = '—';
    let maxCount = 0;
    categorias.forEach(c => {
        const count = matCountByCat[c.id] || 0;
        if (count > maxCount) {
            maxCount = count;
            popularCat = c.name;
        }
    });

    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = total;
    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = totalMateriales;
    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = activas;
    document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = popularCat;

    if (categorias.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-layer-group"></i><p>No hay categorías registradas</p></div>';
        return;
    }

    categorias.forEach(cat => {
        const card = document.createElement('div');
        card.className = 'category-card' + (cat.status !== 'Active' ? ' inhabilitado' : '');
        card.dataset.id = cat.id;
        card.dataset.name = cat.name;
        card.dataset.description = cat.description;
        card.dataset.status = cat.status;

        const isActive = cat.status === 'Active';
        const matCount = matCountByCat[cat.id] || 0;

        const safeName = escapeHtml(cat.name);
        const svgFallback = 'data:image/svg+xml,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400">' +
            '<rect fill="#e0e0e0" width="600" height="400"/>' +
            '<text x="300" y="200" text-anchor="middle" dy=".3em" font-size="24" fill="#999" font-family="Arial">' +
            safeName.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') +
            '</text></svg>'
        );

        card.innerHTML = `
            <div class="category-image" style="background-image: url('${cat.imageUrl || svgFallback}');">
                <div class="category-count">${matCount} materiales</div>
            </div>
            <div class="category-info">
                <div class="category-title">
                    <h3>${safeName}</h3>
                    <div class="category-status ${isActive ? 'status-active' : 'status-inactive'}">${isActive ? 'Activa' : 'Inactiva'}</div>
                </div>
                <div class="category-description">${escapeHtml(cat.description)}</div>
                <div class="category-actions">
                    <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                    <button class="action-btn toggle-btn">
                        <i class="fas ${isActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${isActive ? 'Inhabilitar' : 'Habilitar'}
                    </button>
                </div>
            </div>
        `;

        card.querySelector('.edit-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            if (!isActive) {
                toast('Categoría inhabilitada. Actívela primero para editarla.', 'warning');
                return;
            }
            editarCategoria(cat);
        });

        card.querySelector('.toggle-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            toggleEstadoCategoria(cat.id, card.dataset.status, card, this, matCount);
        });

        container.appendChild(card);
    });
}

function editarCategoria(cat) {
    editingCategoryId = cat.id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Categoría';
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDescription').value = cat.description;
    document.getElementById('categoryStatus').value = cat.status;
    document.getElementById('categoryModal').style.display = 'flex';
}

function guardarCategoria() {
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();
    const status = document.getElementById('categoryStatus').value;

    if (!name) {
        toast('El nombre de la categoría es obligatorio.', 'warning');
        return;
    }

    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name)) {
        toast('El nombre solo puede contener letras y espacios.', 'warning');
        return;
    }

    const url = APP_URL + 'Public/api/categories.php' + (editingCategoryId ? '?id=' + editingCategoryId : '');
    const method = editingCategoryId ? 'PUT' : 'POST';

    callApi(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({ name, description, status })
    })
        .then(data => {
            if (data.success) {
                document.getElementById('categoryModal').style.display = 'none';
                cargarCategorias();
            } else {
                toast(data.message, 'error');
            }
        })
        .catch(err => {
            toast(err.message, 'error');
            console.error(err);
        });
}

function toggleEstadoCategoria(id, currentStatus, card, button, matCount) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';

    if (newStatus === 'Inactive' && matCount > 0) {
        toast('No se puede Deshabilitar esta Categoría, Tiene Materiales Vinculados', 'warning');
        return;
    }

    callApi(APP_URL + 'Public/api/categories.php?id=' + id, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({ status: newStatus })
    })
        .then(data => {
            if (data.success) {
                card.classList.toggle('inhabilitado');
                const isActive = newStatus === 'Active';
                button.innerHTML = `<i class="fas ${isActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${isActive ? 'Inhabilitar' : 'Habilitar'}`;
                const badge = card.querySelector('.category-status');
                badge.textContent = isActive ? 'Activa' : 'Inactiva';
                badge.className = 'category-status ' + (isActive ? 'status-active' : 'status-inactive');
                card.dataset.status = newStatus;

                const activasEl = document.querySelector('.stat-card:nth-child(3) .stat-value');
                if (activasEl) {
                    const current = parseInt(activasEl.textContent);
                    activasEl.textContent = isActive ? current + 1 : current - 1;
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

function filtrarCategorias() {
    const term = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.category-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        if (!term) {
            card.style.display = '';
        } else {
            card.style.display = name.includes(term) ? '' : 'none';
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
