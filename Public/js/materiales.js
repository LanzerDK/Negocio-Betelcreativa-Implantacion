let editingMaterialId = null;
let categoriasMap = {};
let allMaterials = [];

const FILTERS = {
  search: '',
  categoryId: null,
  stockEnStock: true,
  stockBajo: true,
  stockSinStock: true
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
    nuevoModal.addEventListener('shown.bs.modal', function () {
      cargarCategoriasParaSelect();
      cargarUbicacionesParaSelect();
      document.getElementById('nuevoCodigo').value = '';
      document.getElementById('wholesaleQtyGroup').style.display = 'none';
    });
  }
});

function cargarMateriales() {
  Promise.all([
    fetch(APP_URL + 'api/materials.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message || 'HTTP ' + r.status); return d; })),
    fetch(APP_URL + 'api/categories.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message || 'HTTP ' + r.status); return d; }))
  ])
    .then(([matRes, catRes]) => {
      if (catRes && catRes.success) {
        catRes.data.forEach(c => { categoriasMap[c.id] = c.name; });
        try { cargarSelectCategorias(catRes.data); } catch (e) { console.error('Error en cargarSelectCategorias:', e); }
        try { cargarCategoriasSidebar(catRes.data); } catch (e) { console.error('Error en cargarCategoriasSidebar:', e); }
      }
      if (matRes && matRes.success) {
        allMaterials = matRes.data;
        aplicarFiltros();
      }
    })
    .catch(err => {
      console.error('Error al cargar datos:', err.message || err);
    });
}

function cargarCategoriasParaSelect() {
  return fetch(APP_URL + 'api/categories.php')
    .then(r => r.json())
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

function cargarUbicacionesParaSelect() {
  return fetch(APP_URL + 'api/locations.php')
    .then(r => r.json())
    .then(data => {
      if (data.success && Array.isArray(data.data)) {
        const selects = ['newLocation', 'editLocation'];
        for (const id of selects) {
          const sel = document.getElementById(id);
          if (!sel) continue;
          const currentVal = sel.value;
          sel.innerHTML = '<option value="">Sin ubicación</option>';
          for (const loc of data.data) {
            const opt = document.createElement('option');
            opt.value = loc.id || loc.location_id;
            opt.textContent = loc.name || loc.location_name;
            if (String(opt.value) === String(currentVal)) opt.selected = true;
            sel.appendChild(opt);
          }
        }
      }
    })
    .catch(err => console.error('Error al cargar ubicaciones:', err));
}

function cargarSelectCategorias(categorias) {
  const selects = ['nuevaCategoria', 'categoria'];
  for (const id of selects) {
    const sel = document.getElementById(id);
    if (!sel) continue;
    const currentVal = sel.value;
    sel.innerHTML = '<option value="">Seleccionar categoría</option>';
    for (const c of categorias) {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      if (String(c.id) === String(currentVal)) opt.selected = true;
      sel.appendChild(opt);
    }
  }
}

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

function autoGenerarCodigo() {
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const catSelect = document.getElementById('nuevaCategoria');
  const catId = parseInt(catSelect.value);
  if (!nombre || !catId || isNaN(catId)) return;

  const words = nombre.split(/\s+/);
  const nameAbbrev = words.map(w => w.charAt(0).toUpperCase()).join('').slice(0, 3);
  const catName = categoriasMap[catId] || '';
  const catAbbrev = catName.replace(/[^a-zA-ZáéíóúÁÉÍÓÚ]/g, '').substring(0, 3).toUpperCase();
  const prefix = `${nameAbbrev}-${catAbbrev}-`;

  let maxNum = 0;
  for (const m of allMaterials) {
    if (m.code && m.code.startsWith(prefix)) {
      const parts = m.code.split('-');
      const num = parseInt(parts[parts.length - 1], 10);
      if (!isNaN(num) && num > maxNum) maxNum = num;
    }
  }
  document.getElementById('nuevoCodigo').value = prefix + String(maxNum + 1).padStart(3, '0');
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
    if (m.stock <= 0) return FILTERS.stockSinStock;
    if (m.stock <= 10) return FILTERS.stockBajo;
    return FILTERS.stockEnStock;
  });

  renderizarMateriales(filtered);
}

function limpiarFiltros() {
  FILTERS.search = '';
  FILTERS.categoryId = null;
  FILTERS.stockEnStock = true;
  FILTERS.stockBajo = true;
  FILTERS.stockSinStock = true;

  document.querySelector('.search-box input').value = '';
  document.querySelectorAll('.stock-filter input').forEach(cb => cb.checked = true);
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

    let costDisplay = '$' + Number(mat.price).toFixed(2);
    if (mat.cost_type === 'wholesale' && mat.wholesale_qty) {
      const unitPrice = (Number(mat.price) / Number(mat.wholesale_qty)).toFixed(2);
      costDisplay = `$${Number(mat.price).toFixed(2)} / ${mat.wholesale_qty}uds ($${unitPrice}/ud)`;
    }

    card.innerHTML = `
      <div class="card-badge ${badgeClass}">${badgeText}</div>
      ${isInactive ? '<div class="inactive-badge"><i class="fas fa-ban"></i> Inhabilitado</div>' : ''}
      <div class="material-image" style="background-image: url('${mat.imageUrl || 'https://via.placeholder.com/600x400?text=' + encodeURIComponent(mat.name)}'); ${isInactive ? 'opacity:0.5;' : ''}"></div>
      <div class="material-info">
        <div class="material-title">
          <h3>${escapeHtml(mat.name)}</h3>
          <div class="material-price">${costDisplay}</div>
        </div>
        <div class="material-details">
          <span><i class="fas fa-tag"></i> ${escapeHtml(categoriaNombre)}</span>
          <span><i class="fas fa-barcode"></i> ${escapeHtml(mat.code)}</span>
        </div>
        <div class="stock-info">
          <span><i class="fas fa-box"></i> ${mat.stock} unidades</span>
          <div class="progress-bar">
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
      Promise.all([
        cargarCategoriasParaSelect(),
        cargarUbicacionesParaSelect()
      ]).then(() => llenarFormularioEdicion(mat));
      const modal = new bootstrap.Modal(document.getElementById('editarMaterialModal'));
      modal.show();
    });

    fragment.appendChild(card);
  }

  container.appendChild(fragment);
}

function toggleEstadoMaterial(boton, id) {
  const habilitar = boton.classList.contains('is-disabled');

  fetch(APP_URL + 'api/materials.php?id=' + id, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
    body: JSON.stringify({ is_active: habilitar ? 1 : 0 })
  })
    .then(res => res.json().then(d => { if (!res.ok) throw new Error(d.message || 'HTTP ' + res.status); return d; }))
    .then(data => {
      if (data.success) {
        cargarMateriales();
      } else {
        toast('Error: ' + data.message, 'error');
      }
    })
    .catch(err => {
      toast('Error de conexión.', 'error');
      console.error(err);
    });
}

function llenarFormularioEdicion(mat) {
  document.getElementById('codigo').value = mat.code || '';
  document.getElementById('material').value = mat.name || '';
  document.getElementById('categoria').value = mat.category_id || '';
  document.getElementById('stock').value = mat.stock || 0;
  document.getElementById('precio').value = mat.price || 0;
  document.getElementById('editLocation').value = mat.location_id || '';
  const ct = document.getElementById('costType');
  if (ct) {
    ct.value = mat.cost_type || 'unit';
    document.getElementById('editWholesaleQtyGroup').style.display = ct.value === 'wholesale' ? 'block' : 'none';
  }
  const wq = document.getElementById('wholesaleQty');
  if (wq) wq.value = (mat.cost_type === 'wholesale' && mat.wholesale_qty) ? mat.wholesale_qty : '';
}

function ocultarModalYRefrescar(modalId) {
  const el = document.getElementById(modalId);
  let instance = bootstrap.Modal.getInstance(el);
  if (!instance) {
    instance = new bootstrap.Modal(el);
  }
  instance.hide();
  setTimeout(cargarMateriales, 500);
}

function agregarNuevoMaterial() {
  clearErrors();

  const codigo = document.getElementById('nuevoCodigo').value.trim();
  const nombre = document.getElementById('nuevoMaterial').value.trim();
  const categoryId = parseInt(document.getElementById('nuevaCategoria').value) || null;
  const stock = parseInt(document.getElementById('nuevoStock').value);
  const costType = document.getElementById('nuevoCostType')?.value || 'unit';
  const wholesaleQty = costType === 'wholesale' ? parseInt(document.getElementById('nuevoWholesaleQty')?.value) : null;
  const locationId = parseInt(document.getElementById('newLocation').value) || null;

  let valid = true;
  if (!codigo) { showError('nuevoCodigo', 'El código es obligatorio'); valid = false; }
  if (!nombre) { showError('nuevoMaterial', 'El nombre es obligatorio'); valid = false; }
  if (stock < 0 || isNaN(stock)) { showError('nuevoStock', 'Stock inválido'); valid = false; }
  if (!valid) return;

  let price, dataWholesaleQty = null;
  if (costType === 'wholesale') {
    const totalCost = parseFloat(document.getElementById('nuevoPrecio')?.value);
    if (totalCost <= 0 || isNaN(totalCost)) { showError('nuevoPrecio', 'Costo total obligatorio'); return; }
    if (!wholesaleQty || wholesaleQty <= 0) { showError('nuevoWholesaleQty', 'Indique la cantidad'); return; }
    price = totalCost;
    dataWholesaleQty = wholesaleQty;
  } else {
    const unitPrice = parseFloat(document.getElementById('nuevoPrecio')?.value);
    if (unitPrice <= 0 || isNaN(unitPrice)) { showError('nuevoPrecio', 'Precio obligatorio'); return; }
    price = unitPrice;
  }

  fetch(APP_URL + 'api/materials.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
    body: JSON.stringify({
      code: codigo, name: nombre, category_id: categoryId,
      stock: stock || 0, price: price, cost_type: costType,
      wholesale_qty: dataWholesaleQty, location_id: locationId
    })
  })
    .then(res => res.json().then(d => { if (!res.ok) throw new Error(d.message || 'HTTP ' + res.status); return d; }))
    .then(data => {
      if (data.success) {
        document.getElementById('nuevoMaterialForm').reset();
        document.getElementById('nuevoCodigo').value = '';
        document.getElementById('wholesaleQtyGroup').style.display = 'none';
        ocultarModalYRefrescar('nuevoMaterialModal');
      } else {
        toast('Error: ' + data.message, 'error');
      }
    })
    .catch(err => {
      toast('Error de conexión.', 'error');
      console.error(err);
    });
}

function guardarEdicionMaterial() {
  if (!editingMaterialId) return;
  clearErrors();

  const code = document.getElementById('codigo').value.trim();
  const name = document.getElementById('material').value.trim();
  const categoryId = parseInt(document.getElementById('categoria').value) || null;
  const stock = parseInt(document.getElementById('stock').value);
  const costType = document.getElementById('costType')?.value || 'unit';

  let valid = true;
  if (!code) { showError('codigo', 'El código es obligatorio'); valid = false; }
  if (!name) { showError('material', 'El nombre es obligatorio'); valid = false; }
  if (stock < 0 || isNaN(stock)) { showError('stock', 'Stock inválido'); valid = false; }
  if (!valid) return;

  let price, dataWholesaleQty = null;
  if (costType === 'wholesale') {
    const totalCost = parseFloat(document.getElementById('precio')?.value);
    if (totalCost <= 0 || isNaN(totalCost)) { showError('precio', 'Costo total obligatorio'); return; }
    const wq = parseInt(document.getElementById('wholesaleQty')?.value);
    if (!wq || wq <= 0) { showError('wholesaleQty', 'Indique la cantidad'); return; }
    price = totalCost;
    dataWholesaleQty = wq;
  } else {
    const unitPrice = parseFloat(document.getElementById('precio')?.value);
    if (unitPrice <= 0 || isNaN(unitPrice)) { showError('precio', 'Precio obligatorio'); return; }
    price = unitPrice;
  }

  const locationId = parseInt(document.getElementById('editLocation').value) || null;

  fetch(APP_URL + 'api/materials.php?id=' + editingMaterialId, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
    body: JSON.stringify({
      code, name, category_id: categoryId, stock,
      price, cost_type: costType, wholesale_qty: dataWholesaleQty,
      location_id: locationId
    })
  })
    .then(res => res.json().then(d => { if (!res.ok) throw new Error(d.message || 'HTTP ' + res.status); return d; }))
    .then(data => {
      if (data.success) {
        editingMaterialId = null;
        ocultarModalYRefrescar('editarMaterialModal');
      } else {
        toast('Error: ' + data.message, 'error');
      }
    })
    .catch(err => {
      toast('Error de conexión.', 'error');
      console.error(err);
    });
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

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
