// Estado global: ID de la categoría que se está editando (null si es nueva)
let editingCategoryId = null;
// URL de la imagen subida para la categoría actual
let categoryImageUploadedUrl = null;

// Inicialización al cargar el DOM
document.addEventListener('DOMContentLoaded', function () {
    // Cargar categorías y materiales desde la API
    cargarCategorias();

    // Activar clase 'active' en el menú lateral al hacer clic
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function () {
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Abrir modal de nueva categoría y resetear campos del formulario
    document.getElementById('newCategoryBtn').addEventListener('click', function () {
        editingCategoryId = null;
        categoryImageUploadedUrl = null;
        document.getElementById('categoryModalLabel').textContent = 'Nueva Categoría';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryImage').value = '';
        document.getElementById('categoryImagePreview').style.display = 'none';
        document.getElementById('categoryImagePreview').querySelector('img').src = '';
        Modal.open('categoryModal');
    });

    // Vista previa de imagen al seleccionar archivo
    document.getElementById('categoryImage')?.addEventListener('change', function (e) {
        mostrarPreview(e.target, 'categoryImagePreview');
    });

    // Guardar categoría al hacer clic en el botón
    document.getElementById('saveCategoryBtn').addEventListener('click', guardarCategoria);

    // Filtrar categorías en tiempo real al escribir en el buscador
    document.getElementById('searchInput')?.addEventListener('input', filtrarCategorias);

    // Limpiar campo de búsqueda y restaurar lista completa
    document.getElementById('btnLimpiarCategorias')?.addEventListener('click', function () {
        document.getElementById('searchInput').value = '';
        filtrarCategorias();
    });
});

// Cargar categorías y materiales en paralelo, luego renderizar
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

// Renderizar tarjetas de categorías y estadísticas del encabezado
function renderizarCategorias(categorias, materiales) {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = '';

    materiales = materiales || [];

    // Solo contar materiales activos (is_active !== false y !== 0)
    const activos = materiales.filter(m => m.is_active !== false && m.is_active !== 0);

    // Calcular estadísticas: totales, activas y materiales vinculados
    const total = categorias.length;
    const activas = categorias.filter(c => c.status === 'Active').length;
    const totalMateriales = activos.length;

    // Contar materiales activos por categoría (category_id como clave)
    const matCountByCat = {};
    activos.forEach(m => {
        const cid = m.category_id;
        matCountByCat[cid] = (matCountByCat[cid] || 0) + 1;
    });

    // Determinar la categoría con mayor cantidad de materiales
    let popularCat = '—';
    let maxCount = 0;
    categorias.forEach(c => {
        const count = matCountByCat[c.id] || 0;
        if (count > maxCount) {
            maxCount = count;
            popularCat = c.name;
        }
    });

    // Actualizar valores de las tarjetas de estadísticas en el DOM
    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = total;
    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = totalMateriales;
    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = activas;
    document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = popularCat;

    // Mostrar estado vacío si no hay categorías
    if (categorias.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-layer-group"></i><p>No hay categorías registradas</p></div>';
        return;
    }

    // Crear tarjeta DOM por cada categoría
    categorias.forEach(cat => {
        const card = document.createElement('div');
        const isActive = cat.status === 'Active';
        const isInactive = !isActive;
        const matCount = matCountByCat[cat.id] || 0;
        const safeName = escapeHtml(cat.name);

        // Aplicar clase de inhabilitada si la categoría está inactiva
        card.className = 'category-card' + (isInactive ? ' inhabilitado' : '');
        card.dataset.id = cat.id;
        card.dataset.name = cat.name;
        card.dataset.status = cat.status;

        // Construir HTML de la tarjeta con imagen, badge y acciones
        card.innerHTML = `
            ${isInactive ? '<div class="inactive-badge"><i class="fas fa-ban"></i> Inhabilitada</div>' : ''}
            <div class="category-image${cat.imageUrl ? '' : ' no-image'}" style="${cat.imageUrl ? "background-image: url('" + cat.imageUrl + "')" : ''}">
                <div class="card-badge badge-stock">${matCount} materiales</div>
            </div>
            <div class="category-info">
                <div class="category-title">
                    <h3>${safeName}</h3>
                    <div class="material-price">${isActive ? 'Activa' : 'Inactiva'}</div>
                </div>
                <div class="material-details">
                    <span><i class="fas fa-align-left"></i> ${escapeHtml(cat.description) || 'Sin descripción'}</span>
                    <span><i class="fas fa-box"></i> ${matCount} materiales vinculados</span>
                </div>
                <div class="card-actions">
                    <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
                    <button class="action-btn toggle-btn ${isInactive ? 'is-disabled' : ''}">
                        <i class="fas ${isActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${isActive ? 'Inhabilitar' : 'Habilitar'}
                    </button>
                </div>
            </div>
        `;

        // Evento: editar categoría (bloquea si está inhabilitada)
        card.querySelector('.edit-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            if (card.dataset.status !== 'Active') {
                toast('Categoría inhabilitada. Actívela primero para editarla.', 'warning');
                return;
            }
            editarCategoria(cat);
        });

        // Evento: alternar estado activo/inactivo
        card.querySelector('.toggle-btn').addEventListener('click', function (e) {
            e.stopPropagation();
            toggleEstadoCategoria(cat.id, card.dataset.status, card, this, matCount);
        });

        container.appendChild(card);
    });
}

// Abrir modal de edición con los datos de la categoría seleccionada
function editarCategoria(cat) {
    editingCategoryId = cat.id;
    categoryImageUploadedUrl = cat.imageUrl || null;
    document.getElementById('categoryModalLabel').textContent = 'Editar Categoría';
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDescription').value = cat.description;
    document.getElementById('categoryImage').value = '';
    const preview = document.getElementById('categoryImagePreview');
    const img = preview.querySelector('img');
    // Mostrar u ocultar previsualización de imagen existente
    if (cat.imageUrl) {
        img.src = cat.imageUrl;
        preview.style.display = 'block';
    } else {
        img.src = '';
        preview.style.display = 'none';
    }
    Modal.open('categoryModal');
}

// Validar formulario y guardar categoría (crear o actualizar)
function guardarCategoria() {
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();

    // Validar que el nombre no esté vacío
    if (!name) {
        toast('El nombre de la categoría es obligatorio.', 'warning');
        return;
    }

    // Validar que el nombre solo contenga letras y espacios (sin números ni símbolos)
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name)) {
        toast('El nombre solo puede contener letras y espacios.', 'warning');
        return;
    }

    const fileInput = document.getElementById('categoryImage');
    const body = { name, description, image_url: categoryImageUploadedUrl };

    // Función interna para enviar POST o PUT según si es edición o creación
    const doSave = function () {
        const url = APP_URL + 'Public/api/categories.php' + (editingCategoryId ? '?id=' + editingCategoryId : '');
        const method = editingCategoryId ? 'PUT' : 'POST';

        callApi(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(body)
        })
            .then(data => {
                if (data.success) {
                    Modal.close('categoryModal');
                    // Recargar la lista de categorías después de guardar
                    cargarCategorias();
                } else {
                    toast(data.message, 'error');
                }
            })
            .catch(err => {
                toast(err.message, 'error');
                console.error(err);
            });
    };

    // Si hay archivo seleccionado, subir imagen primero y luego guardar
    if (fileInput.files.length > 0) {
        subirImagenCategoria(fileInput.files[0]).then(imgUrl => {
            categoryImageUploadedUrl = imgUrl;
            body.image_url = imgUrl;
            doSave();
        }).catch(err => {
            toast(err.message || 'Error al subir la imagen', 'error');
        });
    } else {
        doSave();
    }
}

// Subir imagen de categoría al servidor vía endpoint dedicado
async function subirImagenCategoria(file) {
    const formData = new FormData();
    formData.append('category_image', file);
    formData.append('csrf_token', CSRF_TOKEN);

    // Enviar archivo con token CSRF en header y en body
    const resp = await fetch(APP_URL + 'Public/api/upload-category-image.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    });
    const data = await resp.json();
    if (!data.success) {
        throw new Error(data.message || 'Error al subir la imagen');
    }
    return data.data.url;
}

// Mostrar vista previa de imagen seleccionada usando FileReader
function mostrarPreview(input, previewId) {
    const preview = document.getElementById(previewId);
    const img = preview.querySelector('img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        img.src = '';
        preview.style.display = 'none';
    }
}

// Alternar estado activo/inactivo de una categoría con validación de materiales vinculados
function toggleEstadoCategoria(id, currentStatus, card, button, matCount) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';

    // Bloquear deshabilitar si tiene materiales vinculados
    if (newStatus === 'Inactive' && matCount > 0) {
        toast('No se puede Deshabilitar esta Categoría, Tiene Materiales Vinculados', 'warning');
        return;
    }

    // Enviar cambio de estado al servidor
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
                // Actualizar clase visual de la tarjeta
                card.classList.toggle('inhabilitado');
                const isActive = newStatus === 'Active';
                // Actualizar texto e ícono del botón de toggle
                button.innerHTML = `<i class="fas ${isActive ? 'fa-eye-slash' : 'fa-check-circle'}"></i> ${isActive ? 'Inhabilitar' : 'Habilitar'}`;
                button.classList.toggle('is-disabled', !isActive);
                // Actualizar badge de estado
                const badge = card.querySelector('.material-price');
                badge.textContent = isActive ? 'Activa' : 'Inactiva';
                card.dataset.status = newStatus;

                // Agregar o eliminar badge de "Inhabilitada"
                const existingBadge = card.querySelector('.inactive-badge');
                if (!isActive && !existingBadge) {
                    const div = document.createElement('div');
                    div.className = 'inactive-badge';
                    div.innerHTML = '<i class="fas fa-ban"></i> Inhabilitada';
                    card.insertBefore(div, card.firstChild);
                } else if (isActive && existingBadge) {
                    existingBadge.remove();
                }

                // Actualizar contador de categorías activas en estadísticas
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

// Filtrar tarjetas de categorías por nombre según el término de búsqueda
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
