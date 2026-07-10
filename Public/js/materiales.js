let editingMaterialId = null;
let categoriasMap = {};
let suppliersMap = {};
let allMaterials = [];
let quickSupplierTarget = null;

const FILTERS = {
  search: '',
  categoryId: null,
  stockEnStock: false,
  stockBajo: false,
  stockSinStock: false
};

document.addEventListener('DOMContentLoaded', function () {
  cargarMateriales();

  document.getElementById('nuevoMaterial')?.addEventListener('input', autoGenerarCodigo);
  document.getElementById('nuevaCategoria')?.addEventListener('change', autoGenerarCodigo);

  document.getElementById('nuevoCostType')?.addEventListener('change', function () {
    document.getElementById('wholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
  });
  document.getElementById('costType')?.addEventListener('change', function () {
    document.getElementById('editWholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
  });

  document.getElementById('guardarMaterialBtn')?.addEventListener('click', agregarNuevoMaterial);
  document.getElementById('guardarCambiosBtn')?.addEventListener('click', guardarEdicionMaterial);

  // Image preview handlers
  document.getElementById('nuevaImagen')?.addEventListener('change', function (e) {
    mostrarPreview(e.target, 'nuevaImagenPreview');
  });
  document.getElementById('editImagen')?.addEventListener('change', function (e) {
    mostrarPreview(e.target, 'editImagenPreview');
  });

  const searchInput = document.querySelector('.search-box input');
  searchInput?.addEventListener('input', function () {
    FILTERS.search = this.value.toLowerCase();
    aplicarFiltros();
  });

  document.querySelectorAll('.stock-filter input').forEach(cb => {
    cb.addEventListener('change', function () {
      const text = this.closest('label')?.textContent.trim() || '';
      if (text.includes('En Stock')) FILTERS.stockEnStock = this.checked;
      else if (text.includes('Stock Bajo')) FILTERS.stockBajo = this.checked;
      else if (text.includes('Sin Stock')) FILTERS.stockSinStock = this.checked;
      aplicarFiltros();
    });
  });

  document.querySelector('.btn-limpiar')?.addEventListener('click', limpiarFiltros);

  document.querySelector('.category-item:first-child')?.addEventListener('click', function () {
    document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');
    FILTERS.categoryId = null;
    aplicarFiltros();
  });

  const nuevoModal = document.getElementById('nuevoMaterialModal');
  if (nuevoModal) {
    nuevoModal.addEventListener('modal:shown', function () {
      cargarCategoriasParaSelect();
      document.getElementById('nuevoCodigo').value = '';
      document.getElementById('wholesaleQtyGroup').style.display = 'none';
    });
  }

  document.querySelectorAll('.btn-add-supplier').forEach(btn => {
    btn.addEventListener('click', function () {
      quickSupplierTarget = this.getAttribute('data-target');
      const prefix = quickSupplierTarget === 'editProveedor' ? 'edit' : 'nuevo';
      const panel = document.getElementById(prefix + 'SupplierPanel');
      if (!panel) return;
      document.getElementById(prefix + 'QuickSupplierName').value = '';
      document.getElementById(prefix + 'QuickSupplierContact').value = '';
      document.getElementById(prefix + 'QuickSupplierPhone').value = '';
      document.getElementById(prefix + 'QuickSupplierEmail').value = '';
      document.getElementById(prefix + 'QuickSupplierAddress').value = '';
      document.getElementById(prefix + 'QuickSupplierType').value = 'fijo';
      document.getElementById(prefix + 'QuickSupplierSubtype').value = '';
      document.getElementById(prefix + 'QuickSupplierNotes').value = '';
      toggleQuickSupplierFields(prefix, 'fijo');
      panel.style.display = 'flex';
    });
  });

  document.addEventListener('click', function (e) {
    const closeBtn = e.target.closest('[data-close-panel]');
    if (closeBtn) {
      const panelId = closeBtn.getAttribute('data-close-panel');
      document.getElementById(panelId).style.display = 'none';
    }
  });
  document.getElementById('nuevoSaveQuickSupplierBtn')?.addEventListener('click', guardarQuickSupplier);
  document.getElementById('editSaveQuickSupplierBtn')?.addEventListener('click', guardarQuickSupplier);
  document.getElementById('nuevoQuickSupplierType')?.addEventListener('change', function () {
    toggleQuickSupplierFields('nuevo', this.value);
  });
  document.getElementById('editQuickSupplierType')?.addEventListener('change', function () {
    toggleQuickSupplierFields('edit', this.value);
  });
});

function toggleQuickSupplierFields(prefix, type) {
  const isComodin = type === 'comodin';
  document.getElementById(prefix + 'QuickFijoFields').style.display = isComodin ? 'none' : 'block';
  document.getElementById(prefix + 'QuickComodinFields').style.display = isComodin ? 'block' : 'none';
  if (!isComodin) {
    document.getElementById(prefix + 'QuickSupplierSubtype').value = '';
    document.getElementById(prefix + 'QuickSupplierNotes').value = '';
  } else {
    document.getElementById(prefix + 'QuickSupplierContact').value = '';
    document.getElementById(prefix + 'QuickSupplierPhone').value = '';
    document.getElementById(prefix + 'QuickSupplierEmail').value = '';
    document.getElementById(prefix + 'QuickSupplierAddress').value = '';
  }
}

async function cargarMateriales() {
  try {
    const [matRes, catRes, supRes] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/admin/suppliers.php').then(r => r.json()).catch(() => ({ success: false, data: [] }))
    ]);
    if (catRes && catRes.success) {
      catRes.data.forEach(c => { categoriasMap[c.id] = c.name; });
      try { cargarSelectCategorias(catRes.data); } catch (e) { console.error('Error en cargarSelectCategorias:', e); }
      try { cargarCategoriasSidebar(catRes.data); } catch (e) { console.error('Error en cargarCategoriasSidebar:', e); }
    }
    if (supRes && supRes.success) {
      supRes.data.forEach(s => { suppliersMap[s.id] = s.company_name; });
      try { cargarSelectProveedores(supRes.data); } catch (e) { console.error('Error en cargarSelectProveedores:', e); }
    }
    if (matRes && matRes.success) {
      allMaterials = matRes.data;
      aplicarFiltros();
    }
  } catch (err) {
    console.error('Error al cargar datos:', err.message || err);
  }
}

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

function cargarSelectProveedores(proveedores) {
  const selects = ['nuevoProveedor', 'editProveedor'];
  for (const id of selects) {
    const sel = document.getElementById(id);
    if (!sel) continue;
    const currentVal = sel.value;
    sel.innerHTML = '<option value="">Seleccionar proveedor</option>';
    for (const s of proveedores) {
      if (s.is_active === false || s.is_active === 0) continue;
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = s.company_name;
      opt.dataset.type = s.supplier_type || 'fijo';
      if (String(s.id) === String(currentVal)) opt.selected = true;
      sel.appendChild(opt);
    }
  }
}

document.addEventListener('change', function (e) {
  if (e.target.id === 'nuevoProveedor') {
    const opt = e.target.selectedOptions[0];
    const isComodin = opt && opt.dataset.type === 'comodin';
    document.getElementById('nuevoDetalleComodinGroup').style.display = isComodin ? 'block' : 'none';
    if (!isComodin) document.getElementById('nuevoDetalleComodin').value = '';
  }
  if (e.target.id === 'editProveedor') {
    const opt = e.target.selectedOptions[0];
    const isComodin = opt && opt.dataset.type === 'comodin';
    document.getElementById('editDetalleComodinGroup').style.display = isComodin ? 'block' : 'none';
    if (!isComodin) document.getElementById('editDetalleComodin').value = '';
  }
});

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

function autoGenerarCodigo() {
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const catSelect = document.getElementById('nuevaCategoria');
  const catId = parseInt(catSelect.value);
  if (!nombre || !catId || isNaN(catId)) return;

  const nameAbbrev = abreviar(nombre);
  const catName = categoriasMap[catId] || '';
  const catAbbrev = abreviar(catName);
  const prefix = `${catAbbrev}-`;

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

function aplicarFiltros() {
  if (!allMaterials || !allMaterials.length) {
    renderizarMateriales([]);
    return;
  }
  let filtered = allMaterials;

  if (FILTERS.search) {
    const s = FILTERS.search.toLowerCase();
    filtered = filtered.filter(m =>
      m.name.toLowerCase().includes(s) ||
      (m.code && m.code.toLowerCase().includes(s))
    );
  }
  if (FILTERS.categoryId) {
    filtered = filtered.filter(m => m.category_id === FILTERS.categoryId);
  }
  filtered = filtered.filter(m => {
    if (!FILTERS.stockEnStock && !FILTERS.stockBajo && !FILTERS.stockSinStock) return true;
    if (m.stock <= 0) return FILTERS.stockSinStock;
    if (m.stock <= 10) return FILTERS.stockBajo;
    return FILTERS.stockEnStock;
  });

  renderizarMateriales(filtered);
}

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

function renderizarMateriales(materials) {
  const container = document.getElementById('materialsContainer');
  if (!container) return;
  container.innerHTML = '';

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

    const badgeClass = mat.stock <= 0 ? 'badge-out' : mat.stock <= 10 ? 'badge-low' : 'badge-stock';
    const badgeText = mat.stock <= 0 ? 'Sin Stock' : mat.stock <= 10 ? 'Stock Bajo' : 'En Stock';
    const progressClass = mat.stock <= 0 ? 'progress-low' : mat.stock <= 10 ? 'progress-medium' : 'progress-high';
    const categoriaNombre = categoriasMap[mat.category_id] || 'Sin categoría';
    const proveedorNombre = suppliersMap[mat.supplier_id] || null;

    let costDisplay = '$' + Number(mat.price).toFixed(2);
    if (mat.cost_type === 'wholesale' && mat.wholesale_qty) {
      const unitPrice = (Number(mat.price) / Number(mat.wholesale_qty)).toFixed(2);
      costDisplay = `$${Number(mat.price).toFixed(2)} / ${mat.wholesale_qty}uds ($${unitPrice}/ud)`;
    }

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
  document.getElementById('editProveedor').value = mat.supplier_id || '';
  document.getElementById('editProveedor').dispatchEvent(new Event('change'));
  document.getElementById('editDetalleComodin').value = mat.detalle_comodin || '';
  document.getElementById('editUnidadCompra').value = mat.unidad_compra || 'Paquete';
  document.getElementById('editUnidadConsumo').value = mat.unidad_consumo || 'Unidad';
  document.getElementById('editFactorConversion').value = mat.factor_conversion || 1;

  // Show existing image preview
  const editPreview = document.getElementById('editImagenPreview');
  if (mat.imageUrl) {
    editPreview.querySelector('img').src = mat.imageUrl;
    editPreview.style.display = 'block';
  } else {
    editPreview.style.display = 'none';
    editPreview.querySelector('img').src = '';
  }
}

function ocultarModalYRefrescar(modalId) {
  Modal.close(modalId);
  setTimeout(cargarMateriales, 500);
}

async function agregarNuevoMaterial() {
  clearErrors();

  const codigo = document.getElementById('nuevoCodigo').value.trim();
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const categoryId = parseInt(document.getElementById('nuevaCategoria').value) || null;
  const costType = document.getElementById('nuevoCostType')?.value || 'unit';
  const tipoMaterial = document.getElementById('nuevoTipoMaterial')?.value || 'consumible';
  const wholesaleQty = costType === 'wholesale' ? parseInt(document.getElementById('nuevoWholesaleQty')?.value) : null;

  let valid = true;
  if (!codigo) { showError('nuevoCodigo', 'El código es obligatorio'); valid = false; }
  if (!nombre) { showError('nuevoMaterial', 'El nombre es obligatorio'); valid = false; }
  if (!valid) return;

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

  let imageUrl = null;
  const fileInput = document.getElementById('nuevaImagen');
  if (fileInput?.files?.length > 0) {
    imageUrl = await subirImagenMaterial(fileInput.files[0]);
  }

  try {
    const res = await fetch(APP_URL + 'Public/api/materials.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({
        code: codigo, name: nombre, category_id: categoryId,
        stock: 0, price: price, cost_type: costType,
        wholesale_qty: dataWholesaleQty, material_type: tipoMaterial,
        supplier_id: parseInt(document.getElementById('nuevoProveedor').value) || null,
        detalle_comodin: document.getElementById('nuevoDetalleComodin')?.value.trim() || null,
        unidad_compra: document.getElementById('nuevaUnidadCompra').value.trim() || 'Paquete',
        unidad_consumo: document.getElementById('nuevaUnidadConsumo').value.trim() || 'Unidad',
        factor_conversion: parseInt(document.getElementById('nuevoFactorConversion').value) || 1,
        image_url: imageUrl
      })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Error del servidor');
    if (data.success) {
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

async function guardarEdicionMaterial() {
  if (!editingMaterialId) return;
  clearErrors();

  const name = document.getElementById('material').value.trim();
  const categoryId = parseInt(document.getElementById('categoria').value) || null;
  const costType = document.getElementById('costType')?.value || 'unit';

  let valid = true;
  if (!name) { showError('material', 'El nombre es obligatorio'); valid = false; }
  if (!valid) return;

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

  let imageUrl = null;
  const fileInput = document.getElementById('editImagen');
  if (fileInput?.files?.length > 0) {
    imageUrl = await subirImagenMaterial(fileInput.files[0]);
  }

  try {
    const res = await fetch(APP_URL + 'Public/api/materials.php?id=' + editingMaterialId, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({
        name, category_id: categoryId,
        price, cost_type: costType, wholesale_qty: dataWholesaleQty,
        supplier_id: parseInt(document.getElementById('editProveedor').value) || null,
        detalle_comodin: document.getElementById('editDetalleComodin')?.value.trim() || null,
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

function clearErrors() {
  document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

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

function guardarQuickSupplier() {
  const prefix = quickSupplierTarget === 'editProveedor' ? 'edit' : 'nuevo';
  const name = document.getElementById(prefix + 'QuickSupplierName').value.trim();
  const type = document.getElementById(prefix + 'QuickSupplierType').value;
  if (!name) {
    toast('El nombre de la empresa es obligatorio.', 'warning');
    return;
  }
  const body = { company_name: name, supplier_type: type };
  if (type === 'fijo') {
    body.contact_name = document.getElementById(prefix + 'QuickSupplierContact').value.trim() || null;
    body.phone = document.getElementById(prefix + 'QuickSupplierPhone').value.trim() || null;
    body.email = document.getElementById(prefix + 'QuickSupplierEmail').value.trim() || null;
    body.address = document.getElementById(prefix + 'QuickSupplierAddress').value.trim() || null;
  } else {
    body.subtype = document.getElementById(prefix + 'QuickSupplierSubtype').value || null;
    body.notes = document.getElementById(prefix + 'QuickSupplierNotes').value.trim() || null;
  }
  callApi(APP_URL + 'Public/api/admin/suppliers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
    body: JSON.stringify(body)
  })
    .then(data => {
      if (data.success) {
        document.getElementById(prefix + 'SupplierPanel').style.display = 'none';
        return callApi(APP_URL + 'Public/api/admin/suppliers.php');
      } else {
        toast(data.message, 'error');
        return null;
      }
    })
    .then(supRes => {
      if (supRes && supRes.success) {
        supRes.data.forEach(s => { suppliersMap[s.id] = s.company_name; });
        cargarSelectProveedores(supRes.data);
        if (quickSupplierTarget) {
          const sel = document.getElementById(quickSupplierTarget);
          if (sel && supRes.data.length > 0) {
            sel.value = supRes.data[supRes.data.length - 1].id;
          }
        }
        quickSupplierTarget = null;
        toast('Proveedor creado exitosamente.', 'success');
      }
    })
    .catch(err => {
      toast('Error de conexión.', 'error');
      console.error(err);
    });
}


