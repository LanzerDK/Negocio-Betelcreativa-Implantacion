// Identificador del material que se está editando (null si no hay edición activa)
let editingMaterialId = null;

// Mapas de búsqueda rápida: ID → nombre de categoría y ID → nombre de proveedor
let categoriasMap = {};
let suppliersMap = {};

// Arreglo maestro con todos los materiales cargados desde la API
let allMaterials = [];

// Estado global de filtros aplicados sobre la tabla de materiales
const FILTERS = {
  search: '',
  categoryId: null,
  stockEnStock: false,
  stockBajo: false,
  stockSinStock: false
};

// Evento principal: inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function () {
  // Carga inicial de materiales, categorías y proveedores
  cargarMateriales();

  // Auto-generación de código al escribir nombre o cambiar categoría
  document.getElementById('nuevoMaterial')?.addEventListener('input', autoGenerarCodigo);
  document.getElementById('nuevaCategoria')?.addEventListener('change', autoGenerarCodigo);

  // Toggle del campo cantidad por mayor según tipo de costo (nuevo material)
  document.getElementById('nuevoCostType')?.addEventListener('change', function () {
    document.getElementById('wholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
  });
  // Toggle del campo cantidad por mayor según tipo de costo (edición)
  document.getElementById('costType')?.addEventListener('change', function () {
    document.getElementById('editWholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
  });

  // Botones de guardado: crear nuevo material y guardar edición
  document.getElementById('guardarMaterialBtn')?.addEventListener('click', agregarNuevoMaterial);
  document.getElementById('guardarCambiosBtn')?.addEventListener('click', guardarEdicionMaterial);

  // Preview de imagen al seleccionar archivo (nuevo material)
  document.getElementById('nuevaImagen')?.addEventListener('change', function (e) {
    mostrarPreview(e.target, 'nuevaImagenPreview');
  });
  // Preview de imagen al seleccionar archivo (edición)
  document.getElementById('editImagen')?.addEventListener('change', function (e) {
    mostrarPreview(e.target, 'editImagenPreview');
  });

  // Búsqueda por texto: filtra materiales en tiempo real
  const searchInput = document.querySelector('.search-box input');
  searchInput?.addEventListener('input', function () {
    FILTERS.search = this.value.toLowerCase();
    aplicarFiltros();
  });

  // Checkboxes de filtro por estado de stock (En Stock / Bajo / Sin Stock)
  document.querySelectorAll('.stock-filter input').forEach(cb => {
    cb.addEventListener('change', function () {
      const text = this.closest('label')?.textContent.trim() || '';
      if (text.includes('En Stock')) FILTERS.stockEnStock = this.checked;
      else if (text.includes('Stock Bajo')) FILTERS.stockBajo = this.checked;
      else if (text.includes('Sin Stock')) FILTERS.stockSinStock = this.checked;
      aplicarFiltros();
    });
  });

  // Botón limpiar: resetea todos los filtros y refresca la tabla
  document.querySelector('.btn-limpiar')?.addEventListener('click', limpiarFiltros);

  // Click en "Todas las categorías" (primer item del sidebar): quita filtro de categoría
  document.querySelector('.category-item:first-child')?.addEventListener('click', function () {
    document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');
    FILTERS.categoryId = null;
    aplicarFiltros();
  });

  // Al abrir el modal de nuevo material: carga categorías y limpia campos
  const nuevoModal = document.getElementById('nuevoMaterialModal');
  if (nuevoModal) {
    nuevoModal.addEventListener('modal:shown', function () {
      cargarCategoriasParaSelect();
      document.getElementById('nuevoCodigo').value = '';
      document.getElementById('wholesaleQtyGroup').style.display = 'none';
    });
  }


});

// Carga paralela de materiales, categorías y proveedores desde la API
async function cargarMateriales() {
  try {
    const [matRes, catRes, supRes] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/admin/suppliers.php').then(r => r.json()).catch(() => ({ success: false, data: [] }))
    ]);
    // Mapea categorías: ID → nombre, y carga los selects y el sidebar
    if (catRes && catRes.success) {
      catRes.data.forEach(c => { categoriasMap[c.id] = c.name; });
      try { cargarSelectCategorias(catRes.data); } catch (e) { console.error('Error en cargarSelectCategorias:', e); }
      try { cargarCategoriasSidebar(catRes.data); } catch (e) { console.error('Error en cargarCategoriasSidebar:', e); }
    }
    // Mapea proveedores: ID → nombre de empresa
    if (supRes && supRes.success) {
      supRes.data.forEach(s => { suppliersMap[s.id] = s.company_name; });
    }
    // Guarda el listado completo y aplica filtros activos
    if (matRes && matRes.success) {
      allMaterials = matRes.data;
      aplicarFiltros();
    }
  } catch (err) {
    console.error('Error al cargar datos:', err.message || err);
  }
}

// Recarga categorías desde la API y actualiza los selects y el mapa
function cargarCategoriasParaSelect() {
  return callApi(APP_URL + 'Public/api/categories.php')
    .then(data => {
      if (data.success) {
        const newMap = {};
        data.data.forEach(c => { newMap[c.id] = c.name; });
        categoriasMap = newMap;
        try { cargarSelectCategorias(data.data); } catch (e) { console.error('Error en cargarSelectCategorias:', e); }
      }
    })
    .catch(err => console.error('Error al cargar categorías:', err));
}

// Puebla los selects de categoría (nuevo y editar) con las opciones activas
function cargarSelectCategorias(categorias) {
  const selects = ['nuevaCategoria', 'categoria'];
  for (const id of selects) {
    const sel = document.getElementById(id);
    if (!sel) continue;
    const currentVal = sel.value;
    sel.innerHTML = '<option value="">Seleccionar categoría</option>';
    for (const c of categorias) {
      if (c.status !== 'Active') continue;
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      if (String(c.id) === String(currentVal)) opt.selected = true;
      sel.appendChild(opt);
    }
  }
}



// Renderiza la lista de categorías en el sidebar lateral con evento de filtrado
function cargarCategoriasSidebar(categorias) {
  const list = document.querySelector('.category-list');
  if (!list) return;
  const firstItem = list.querySelector('.category-item:first-child');
  list.innerHTML = '';
  if (firstItem) list.appendChild(firstItem);
  for (const c of categorias) {
    const li = document.createElement('li');
    li.className = 'category-item';
    li.innerHTML = `<div class="category-icon"><i class="fas fa-tag"></i></div><span>${escapeHtml(c.name)}</span>`;
    li.addEventListener('click', function () {
      document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
      this.classList.add('active');
      FILTERS.categoryId = parseInt(c.id);
      aplicarFiltros();
    });
    list.appendChild(li);
  }
}

// Genera una abreviatura de 1-3 letras a partir de un texto (para códigos automáticos)
function abreviar(texto) {
  const words = texto.split(/\s+/).filter(w => w.length > 1);
  if (words.length === 1) {
    return words[0].substring(0, 3).toUpperCase();
  } else if (words.length === 2) {
    return (words[0].substring(0, 2) + words[1].substring(0, 1)).toUpperCase();
  } else {
    return words.slice(0, 3).map(w => w.charAt(0).toUpperCase()).join('');
  }
}

// Auto-genera el código del material basado en nombre y categoría seleccionada
// Formato: ABREV-NOMBRE-ABREVCAT-NNN (NNN es el consecutivo siguiente)
function autoGenerarCodigo() {
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const catSelect = document.getElementById('nuevaCategoria');
  const catId = parseInt(catSelect.value);
  if (!nombre || !catId || isNaN(catId)) return;

  const nameAbbrev = abreviar(nombre);
  const catName = categoriasMap[catId] || '';
  const catAbbrev = abreviar(catName);
  const prefix = `${catAbbrev}-`;

  // Busca el número consecutivo más alto para esta categoría
  let maxNum = 0;
  for (const m of allMaterials) {
    if (!m.code || m.category_id !== catId) continue;
    const parts = m.code.split('-');
    const catPart = parts.length >= 3 ? parts[parts.length - 2] : null;
    const numPart = parseInt(parts[parts.length - 1], 10);
    if (catPart === catAbbrev && !isNaN(numPart) && numPart > maxNum) {
      maxNum = numPart;
    }
  }
  document.getElementById('nuevoCodigo').value = `${nameAbbrev}-${prefix}${String(maxNum + 1).padStart(3, '0')}`;
}

// Aplica los filtros activos (búsqueda, categoría, stock) sobre allMaterials y renderiza
function aplicarFiltros() {
  if (!allMaterials || !allMaterials.length) {
    renderizarMateriales([]);
    return;
  }
  let filtered = allMaterials;

  // Filtro por texto de búsqueda (nombre o código)
  if (FILTERS.search) {
    const s = FILTERS.search.toLowerCase();
    filtered = filtered.filter(m =>
      m.name.toLowerCase().includes(s) ||
      (m.code && m.code.toLowerCase().includes(s))
    );
  }
  // Filtro por categoría
  if (FILTERS.categoryId) {
    filtered = filtered.filter(m => m.category_id === FILTERS.categoryId);
  }
  // Filtro por estado de stock: sin stock (≤0), stock bajo (≤10), en stock (>10)
  filtered = filtered.filter(m => {
    if (!FILTERS.stockEnStock && !FILTERS.stockBajo && !FILTERS.stockSinStock) return true;
    if (m.stock <= 0) return FILTERS.stockSinStock;
    if (m.stock <= 10) return FILTERS.stockBajo;
    return FILTERS.stockEnStock;
  });

  renderizarMateriales(filtered);
}

// Resetea todos los filtros a su estado por defecto y re-renderiza
function limpiarFiltros() {
  FILTERS.search = '';
  FILTERS.categoryId = null;
  FILTERS.stockEnStock = false;
  FILTERS.stockBajo = false;
  FILTERS.stockSinStock = false;

  document.querySelector('.search-box input').value = '';
  document.querySelectorAll('.stock-filter input').forEach(cb => cb.checked = false);
  document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
  document.querySelector('.category-item:first-child')?.classList.add('active');
  aplicarFiltros();
}

// Renderiza las tarjetas de materiales en el grid del contenedor principal
function renderizarMateriales(materials) {
  const container = document.getElementById('materialsContainer');
  if (!container) return;
  container.innerHTML = '';

  // Estado vacío: no hay materiales para mostrar
  if (!materials || materials.length === 0) {
    container.innerHTML = '<div class="empty-state" style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--gray);"><i class="fas fa-box-open" style="font-size:3rem;margin-bottom:15px;"></i><p>No hay materiales registrados</p></div>';
    return;
  }

  const fragment = document.createDocumentFragment();

  for (const mat of materials) {
    const isInactive = mat.is_active === false;
    const card = document.createElement('div');
    card.className = 'material-card' + (isInactive ? ' inhabilitado' : '');
    card.dataset.id = mat.id;

    // Clase y texto del badge según nivel de stock
    const badgeClass = mat.stock <= 0 ? 'badge-out' : mat.stock <= 10 ? 'badge-low' : 'badge-stock';
    const badgeText = mat.stock <= 0 ? 'Sin Stock' : mat.stock <= 10 ? 'Stock Bajo' : 'En Stock';
    const progressClass = mat.stock <= 0 ? 'progress-low' : mat.stock <= 10 ? 'progress-medium' : 'progress-high';
    const categoriaNombre = categoriasMap[mat.category_id] || 'Sin categoría';
    const proveedorNombre = suppliersMap[mat.supplier_id] || null;

    // Formato de precio: unitario o por mayor (con precio por unidad)
    let costDisplay = '$' + Number(mat.price).toFixed(2);
    if (mat.cost_type === 'wholesale' && mat.wholesale_qty) {
      const unitPrice = (Number(mat.price) / Number(mat.wholesale_qty)).toFixed(2);
      costDisplay = `$${Number(mat.price).toFixed(2)} / ${mat.wholesale_qty}uds ($${unitPrice}/ud)`;
    }

    // HTML de la tarjeta de material: badge, imagen, datos, stock, acciones
    card.innerHTML = `
      <div class="card-badge ${badgeClass}">${badgeText}</div>
      ${isInactive ? '<div class="inactive-badge"><i class="fas fa-ban"></i> Inhabilitado</div>' : ''}
      <div class="material-image${mat.imageUrl ? '' : ' no-image'}" style="${mat.imageUrl ? "background-image: url('" + mat.imageUrl + "')" : ''}${isInactive ? '; opacity:0.5' : ''}"></div>
      <div class="material-info">
        <div class="material-title">
          <h3>${escapeHtml(mat.name)}</h3>
          <div class="material-price">${costDisplay}</div>
        </div>
        <div class="material-details">
          <span><i class="fas fa-tag"></i> ${escapeHtml(categoriaNombre)}</span>
          <span><i class="fas fa-barcode"></i> ${escapeHtml(mat.code)}</span>
          <span><i class="fas fa-boxes"></i> ${mat.material_type === 'activo_retornable' ? 'Activo/Retornable' : 'Consumible'}</span>
          ${proveedorNombre ? `<span><i class="fas fa-truck"></i> ${escapeHtml(proveedorNombre)}</span>` : ''}
        </div>
        <div class="stock-info">
          <span><i class="fas fa-box"></i> ${mat.stock} unidades</span>
          <div class="stock-progress-bar">
            <div class="progress-value ${progressClass}"></div>
          </div>
        </div>
        <div class="card-actions">
          <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</button>
          <button class="action-btn toggle-btn ${isInactive ? 'is-disabled' : ''}" onclick="toggleEstadoMaterial(this, ${mat.id})">
            ${isInactive ? '<i class="fas fa-check-circle"></i> Habilitar' : '<i class="fas fa-eye-slash"></i> Inhabilitar'}
          </button>
        </div>
      </div>
    `;

    // Evento click en botón Editar: bloquea si está inhabilitado, abre modal de edición
    card.querySelector('.edit-btn').addEventListener('click', function () {
      if (isInactive) {
        toast('Material inhabilitado. Actívelo primero para editarlo.', 'warning');
        return;
      }
      editingMaterialId = mat.id;
      cargarCategoriasParaSelect().then(() => llenarFormularioEdicion(mat));
      Modal.open('editarMaterialModal');
    });

    fragment.appendChild(card);
  }

  container.appendChild(fragment);
}

// Alterna el estado activo/inactivo de un material (toggle)
// Bloquea deshabilitar si el material tiene stock > 0
async function toggleEstadoMaterial(boton, id) {
  const habilitar = boton.classList.contains('is-disabled');

  if (!habilitar) {
    const mat = allMaterials.find(m => m.id === id);
    if (mat && mat.stock > 0) {
      toast('No se puede deshabilitar: el material tiene existencia (' + mat.stock + ' unidades).', 'error');
      return;
    }
  }

  try {
    const res = await fetch(APP_URL + 'Public/api/materials.php?id=' + id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ is_active: habilitar ? 1 : 0 })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Error del servidor');
    if (data.success) {
      cargarMateriales();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast(err.message, 'error');
    console.error(err);
  }
}

// Llena el formulario de edición con los datos del material seleccionado
function llenarFormularioEdicion(mat) {
  document.getElementById('codigo').value = mat.code || '';
  document.getElementById('material').value = mat.name || '';
  document.getElementById('categoria').value = mat.category_id || '';
  document.getElementById('precio').value = mat.price || 0;
  const ct = document.getElementById('costType');
  if (ct) {
    ct.value = mat.cost_type || 'unit';
    document.getElementById('editWholesaleQtyGroup').style.display = ct.value === 'wholesale' ? 'block' : 'none';
  }
  const wq = document.getElementById('wholesaleQty');
  if (wq) wq.value = (mat.cost_type === 'wholesale' && mat.wholesale_qty) ? mat.wholesale_qty : '';

  // Campos de configuración de empaque
  document.getElementById('editUnidadCompra').value = mat.unidad_compra || 'Paquete';
  document.getElementById('editUnidadConsumo').value = mat.unidad_consumo || 'Unidad';
  document.getElementById('editFactorConversion').value = mat.factor_conversion || 1;

  // Muestra u oculta el preview de imagen existente
  const editPreview = document.getElementById('editImagenPreview');
  if (mat.imageUrl) {
    editPreview.querySelector('img').src = mat.imageUrl;
    editPreview.style.display = 'block';
  } else {
    editPreview.style.display = 'none';
    editPreview.querySelector('img').src = '';
  }
}

// Cierra un modal y recarga la lista de materiales después de un breve delay
function ocultarModalYRefrescar(modalId) {
  Modal.close(modalId);
  setTimeout(cargarMateriales, 500);
}

// Envía los datos del nuevo material a la API (POST)
async function agregarNuevoMaterial() {
  clearErrors();

  const codigo = document.getElementById('nuevoCodigo').value.trim();
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const categoryId = parseInt(document.getElementById('nuevaCategoria').value) || null;
  const costType = document.getElementById('nuevoCostType')?.value || 'unit';
  const tipoMaterial = document.getElementById('nuevoTipoMaterial')?.value || 'consumible';
  const wholesaleQty = costType === 'wholesale' ? parseInt(document.getElementById('nuevoWholesaleQty')?.value) : null;

  // Validación de campos obligatorios
  let valid = true;
  if (!codigo) { showError('nuevoCodigo', 'El código es obligatorio'); valid = false; }
  if (!nombre) { showError('nuevoMaterial', 'El nombre es obligatorio'); valid = false; }
  if (!valid) return;

  // Validación de precio según tipo de costo (unitario vs por mayor)
  let price, dataWholesaleQty = null;
  if (costType === 'wholesale') {
    const totalCost = parseFloat(document.getElementById('nuevoPrecio')?.value);
    const errs = [];
    if (totalCost < 0) errs.push('El costo total no puede ser negativo.');
    if (wholesaleQty < 0) errs.push('La cantidad por mayor no puede ser negativa.');
    if (errs.length) { errs.forEach(m => toast(m, 'warning')); return; }
    if (totalCost <= 0 || isNaN(totalCost)) { showError('nuevoPrecio', 'Costo total obligatorio'); return; }
    if (!wholesaleQty || wholesaleQty <= 0) { showError('nuevoWholesaleQty', 'Indique la cantidad'); return; }
    price = totalCost;
    dataWholesaleQty = wholesaleQty;
  } else {
    const unitPrice = parseFloat(document.getElementById('nuevoPrecio')?.value);
    if (unitPrice < 0) { toast('El precio no puede ser negativo.', 'warning'); return; }
    if (unitPrice <= 0 || isNaN(unitPrice)) { showError('nuevoPrecio', 'Precio obligatorio'); return; }
    price = unitPrice;
  }

  // Subida de imagen opcional antes de enviar el formulario
  let imageUrl = null;
  const fileInput = document.getElementById('nuevaImagen');
  if (fileInput?.files?.length > 0) {
    imageUrl = await subirImagenMaterial(fileInput.files[0]);
  }

  // Envío POST a la API con todos los campos del material
  try {
    const res = await fetch(APP_URL + 'Public/api/materials.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({
        code: codigo, name: nombre, category_id: categoryId,
        stock: 0, price: price, cost_type: costType,
        wholesale_qty: dataWholesaleQty, material_type: tipoMaterial,

        unidad_compra: document.getElementById('nuevaUnidadCompra').value.trim() || 'Paquete',
        unidad_consumo: document.getElementById('nuevaUnidadConsumo').value.trim() || 'Unidad',
        factor_conversion: parseInt(document.getElementById('nuevoFactorConversion').value) || 1,
        image_url: imageUrl
      })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Error del servidor');
    if (data.success) {
      // Resetear formulario y cerrar modal
      document.getElementById('nuevoMaterialForm').reset();
      document.getElementById('nuevoCodigo').value = '';
      document.getElementById('wholesaleQtyGroup').style.display = 'none';
      document.getElementById('nuevaImagenPreview').style.display = 'none';
      document.getElementById('nuevaImagenPreview').querySelector('img').src = '';
      ocultarModalYRefrescar('nuevoMaterialModal');
      toast(data.message, 'success');
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast(err.message, 'error');
    console.error(err);
  }
}

// Guarda los cambios de un material existente (PUT)
async function guardarEdicionMaterial() {
  if (!editingMaterialId) return;
  clearErrors();

  const name = document.getElementById('material').value.trim();
  const categoryId = parseInt(document.getElementById('categoria').value) || null;
  const costType = document.getElementById('costType')?.value || 'unit';

  // Validación de nombre obligatorio
  let valid = true;
  if (!name) { showError('material', 'El nombre es obligatorio'); valid = false; }
  if (!valid) return;

  // Validación de precio según tipo de costo
  let price, dataWholesaleQty = null;
  if (costType === 'wholesale') {
    const totalCost = parseFloat(document.getElementById('precio')?.value);
    const wq = parseInt(document.getElementById('wholesaleQty')?.value);
    const errs = [];
    if (totalCost < 0) errs.push('El costo total no puede ser negativo.');
    if (wq < 0) errs.push('La cantidad por mayor no puede ser negativa.');
    if (errs.length) { errs.forEach(m => toast(m, 'warning')); return; }
    if (totalCost <= 0 || isNaN(totalCost)) { showError('precio', 'Costo total obligatorio'); return; }
    if (!wq || wq <= 0) { showError('wholesaleQty', 'Indique la cantidad'); return; }
    price = totalCost;
    dataWholesaleQty = wq;
  } else {
    const unitPrice = parseFloat(document.getElementById('precio')?.value);
    if (unitPrice < 0) { toast('El precio no puede ser negativo.', 'warning'); return; }
    if (unitPrice <= 0 || isNaN(unitPrice)) { showError('precio', 'Precio obligatorio'); return; }
    price = unitPrice;
  }

  // Subida de imagen opcional
  let imageUrl = null;
  const fileInput = document.getElementById('editImagen');
  if (fileInput?.files?.length > 0) {
    imageUrl = await subirImagenMaterial(fileInput.files[0]);
  }

  // Envío PUT a la API con los campos actualizados
  try {
    const res = await fetch(APP_URL + 'Public/api/materials.php?id=' + editingMaterialId, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({
        name, category_id: categoryId,
        price, cost_type: costType, wholesale_qty: dataWholesaleQty,
        unidad_compra: document.getElementById('editUnidadCompra').value.trim(),
        unidad_consumo: document.getElementById('editUnidadConsumo').value.trim(),
        factor_conversion: parseInt(document.getElementById('editFactorConversion').value) || 1,
        image_url: imageUrl
      })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Error del servidor');
    if (data.success) {
      editingMaterialId = null;
      document.getElementById('editImagen').value = '';
      document.getElementById('editImagenPreview').style.display = 'none';
      document.getElementById('editImagenPreview').querySelector('img').src = '';
      ocultarModalYRefrescar('editarMaterialModal');
      toast(data.message, 'success');
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast(err.message, 'error');
    console.error(err);
  }
}

// Muestra un mensaje de error bajo un campo del formulario
function showError(fieldId, message) {
  const field = document.getElementById(fieldId);
  if (!field) return;
  field.classList.add('is-invalid');
  const parent = field.closest('.mb-3') || field.parentElement;
  let errorEl = parent.querySelector('.invalid-feedback');
  if (!errorEl) {
    errorEl = document.createElement('div');
    errorEl.className = 'invalid-feedback';
    parent.appendChild(errorEl);
  }
  errorEl.textContent = message;
}

// Elimina todas las marcas de error de validación del formulario
function clearErrors() {
  document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

// Muestra una vista previa de imagen seleccionada usando FileReader
function mostrarPreview(input, previewId) {
  const preview = document.getElementById(previewId);
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (ev) {
      preview.querySelector('img').src = ev.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.style.display = 'none';
    preview.querySelector('img').src = '';
  }
}

// Sube una imagen del material al servidor y retorna la URL pública
async function subirImagenMaterial(file) {
  const formData = new FormData();
  formData.append('material_image', file);
  const res = await fetch(APP_URL + 'Public/api/upload-material-image.php', {
    method: 'POST',
    headers: { 'X-CSRF-Token': CSRF_TOKEN },
    body: formData
  });
  const data = await res.json();
  if (!res.ok || !data.success) throw new Error(data.message || 'Error al subir imagen');
  return data.data.url;
}

