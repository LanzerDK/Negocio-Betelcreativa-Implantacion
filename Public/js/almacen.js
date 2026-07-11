// Estado global: arrays de datos cargados desde la API
let allMaterials = [];
let allCategories = [];
let allLocations = [];
let allWarehouses = [];
let allLocationStock = [];
// Paginación del layout de almacenes
let currentPage = 1;
const ITEMS_PER_PAGE = 9;

// Alternar estado de carga (deshabilitar/habilitar botón) para evitar envíos duplicados
function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('btn-loading', loading);
}

// Inicialización al cargar el DOM: cargar datos y registrar eventos
document.addEventListener('DOMContentLoaded', function () {
  cargarDatosIniciales();
  // Botón nuevo almacén: resetear formulario y abrir modal
  document.getElementById('addWarehouseBtn')?.addEventListener('click', function () {
    const form = document.getElementById('warehouseForm');
    const preview = document.getElementById('whNamePreview');
    const maxEl = document.getElementById('whMaxShelves');
    if (!form || !preview || !maxEl) return;
    form.reset();
    preview.textContent = 'Nombre del Almacén';
    maxEl.value = 100;
    Modal.open('warehouseModal');
  });
  // Botón guardar almacén
  document.getElementById('guardarWarehouseBtn')?.addEventListener('click', guardarWarehouse);
  // Botón nuevo estante: resetear formulario, llenar select de almacén y abrir modal
  document.getElementById('addShelfBtn')?.addEventListener('click', function () {
    const form = document.getElementById('shelfForm');
    const maxEl = document.getElementById('shelfMaxCapacity');
    if (!form || !maxEl) return;
    form.reset();
    maxEl.value = 200;
    llenarSelectAlmacen('shelfWarehouseId');
    Modal.open('shelfModal');
  });
  // Botón guardar estante
  document.getElementById('guardarEstanteBtn')?.addEventListener('click', guardarEstante);
  // Botón confirmar eliminación de estante
  document.getElementById('confirmDeleteShelfModalBtn')?.addEventListener('click', eliminarEstante);
  // Botón guardar movimiento de material entre estantes
  document.getElementById('guardarMovimientoShelfBtn')?.addEventListener('click', guardarMovimientoShelf);
});

// Cargar datos iniciales al montar la página
async function cargarDatosIniciales() {
  await recargarDatos();
}

// Renderizar el layout visual de almacenes con sus estantes y paginación
function renderLayout() {
  const grid = document.getElementById('layoutGrid');
  if (!grid) return;
  grid.innerHTML = '';
  const paginationEl = document.getElementById('pagination');
  if (paginationEl) paginationEl.innerHTML = '';

  // Filtrar estantes huérfanos (sin almacén asignado, distintos de "Almacén General")
  const orphanShelves = allLocations.filter(l => l.warehouse_id === null && l.name !== 'Almacén General');
  if (!allWarehouses.length && !orphanShelves.length) {
    grid.innerHTML = '<div class="empty-state"><p>No hay almacenes registrados. Cree un almacén para comenzar.</p></div>';
    return;
  }

  // Calcular paginación de almacenes
  const totalPages = Math.max(1, Math.ceil(allWarehouses.length / ITEMS_PER_PAGE));
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage - 1) * ITEMS_PER_PAGE;
  const pageWarehouses = allWarehouses.slice(start, start + ITEMS_PER_PAGE);

  // Renderizar cada almacén con sus estantes asociados
  pageWarehouses.forEach(wh => {
    const shelves = allLocations.filter(l => l.warehouse_id === wh.id);
    const whDiv = document.createElement('div');
    whDiv.className = 'warehouse';
    const shelfCount = shelves.length;
    const maxShelves = wh.max_shelves || 100;

    // Construir header del almacén con nombre, código y ubicación
    const whHeader = document.createElement('div');
    whHeader.className = 'warehouse-header';
    whHeader.innerHTML = `
      <div class="warehouse-header-left">
        <div class="warehouse-icon"><i class="fas fa-warehouse"></i></div>
        <div>
          <div class="warehouse-name">${escapeHtml(wh.name)}</div>
          <div class="warehouse-code">Código: ${escapeHtml(wh.code)}</div>
        </div>
      </div>
      <div class="warehouse-header-info">
        <span class="warehouse-shelf-count">Estantes: ${shelfCount} / ${maxShelves}</span>
        ${wh.location ? `<span class="warehouse-location"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(wh.location)}</span>` : ''}
      </div>
    `;
    whDiv.appendChild(whHeader);

    // Renderizar estantes del almacén o mensaje vacío
    const shelvesDiv = document.createElement('div');
    shelvesDiv.className = 'shelves';
    if (!shelves.length) {
      shelvesDiv.innerHTML = '<div class="shelves-empty">No hay estantes en este almacén.</div>';
    } else {
      shelves.forEach(l => {
        shelvesDiv.appendChild(crearShelfElement(l));
      });
    }
    whDiv.appendChild(shelvesDiv);
    grid.appendChild(whDiv);
  });

  // Renderizar sección de estantes huérfanos (sin almacén asignado)
  if (orphanShelves.length) {
    const orphanDiv = document.createElement('div');
    orphanDiv.className = 'warehouse';
    const orphanHeader = document.createElement('div');
    orphanHeader.className = 'warehouse-header';
    orphanHeader.innerHTML = `
      <div class="warehouse-header-left">
        <div class="warehouse-icon"><i class="fas fa-question-circle"></i></div>
        <div>
          <div class="warehouse-name">Sin Almacén</div>
          <div class="warehouse-code">Estantes sin almacén asignado</div>
        </div>
      </div>
      <div class="warehouse-header-info">
        <span class="warehouse-shelf-count">Estantes: ${orphanShelves.length}</span>
      </div>
    `;
    orphanDiv.appendChild(orphanHeader);
    const shelvesDiv = document.createElement('div');
    shelvesDiv.className = 'shelves';
    orphanShelves.forEach(l => {
      shelvesDiv.appendChild(crearShelfElement(l));
    });
    orphanDiv.appendChild(shelvesDiv);
    grid.appendChild(orphanDiv);
  }

  // Mostrar paginación si hay más almacenes que el límite por página
  if (allWarehouses.length > ITEMS_PER_PAGE) {
    renderPagination(totalPages);
  }

// Crear elemento DOM de un estante con nombre, capacidad y botones de acción
function crearShelfElement(l) {
  // Buscar stock actual del estante en el array de stock por ubicación
  const ls = allLocationStock.find(s => s.locationId === l.id);
  const itemCount = ls ? ls.currentStock : 0;
  const maxCap = l.max_capacity || 200;
  const shelfDiv = document.createElement('div');
  shelfDiv.className = 'shelf';
  shelfDiv.innerHTML = `
    <div class="shelf-left">
      <div class="shelf-name">${escapeHtml(l.name)}</div>
      <div class="shelf-stats">${itemCount} / ${maxCap} items</div>
    </div>
    <div class="shelf-actions">
      <button class="shelf-view" data-id="${l.id}" data-name="${escapeHtml(l.name)}" title="Ver materiales"><i class="fas fa-eye"></i></button>
      <button class="shelf-move" data-id="${l.id}" data-name="${escapeHtml(l.name)}" title="Mover material"><i class="fas fa-arrows-alt"></i></button>
      <button class="shelf-delete" data-id="${l.id}" data-name="${escapeHtml(l.name)}" title="Eliminar estante"><i class="fas fa-trash-alt"></i></button>
    </div>
  `;
  return shelfDiv;
}

  // Vincular eventos de los botones de acción de estantes (ver, mover, eliminar)
  document.querySelectorAll('.shelf-view').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = parseInt(this.dataset.id);
      const name = this.dataset.name;
      abrirVerMateriales(id, name);
    });
  });
  document.querySelectorAll('.shelf-move').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = parseInt(this.dataset.id);
      const name = this.dataset.name;
      abrirMoverShelf(id, name);
    });
  });
  document.querySelectorAll('.shelf-delete').forEach(btn => {
    btn.addEventListener('click', function () {
      confirmarEliminarEstante(parseInt(this.dataset.id), this.dataset.name);
    });
  });
}

// Poblar select de almacenes con las opciones disponibles
function llenarSelectAlmacen(selectId, selected) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = '<option value="">Seleccionar almacén...</option>';
  allWarehouses.forEach(wh => {
    const opt = document.createElement('option');
    opt.value = wh.id;
    opt.textContent = `${wh.name} (${wh.code})`;
    if (selected && String(wh.id) === String(selected)) opt.selected = true;
    sel.appendChild(opt);
  });
}

// Poblar select de ubicaciones con capacidad disponible, excluyendo una ubicación específica
function llenarSelectUbicacion(selectId, selected, excludeId) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = '<option value="">Seleccionar ubicación...</option>';
  allLocations.forEach(l => {
    // Excluir la ubicación de origen en movimientos
    if (excludeId && l.id === excludeId) return;
    const opt = document.createElement('option');
    opt.value = l.id;
    // Buscar almacén padre y stock de la ubicación para mostrar capacidad libre
    const wh = allWarehouses.find(w => w.id === l.warehouse_id);
    const ls = allLocationStock.find(s => s.locationId === l.id);
    if (ls && ls.maxCapacity > 0) {
      const libre = ls.maxCapacity - ls.currentStock;
      opt.textContent = `${l.name}${wh ? ' (' + wh.name + ')' : ''} — Libre: ${libre} de ${ls.maxCapacity}`;
    } else {
      opt.textContent = `${l.name}${wh ? ' (' + wh.name + ')' : ''}`;
    }
    if (selected && String(l.id) === String(selected)) opt.selected = true;
    sel.appendChild(opt);
  });
}

// Renderizar controles de paginación del layout de almacenes
function renderPagination(totalPages) {
  const paginationEl = document.getElementById('pagination');
  if (!paginationEl) return;
  paginationEl.innerHTML = '';

  // Botón "Anterior"
  const prev = document.createElement('button');
  prev.className = 'page-btn' + (currentPage === 1 ? ' disabled' : '');
  prev.innerHTML = '<i class="fas fa-chevron-left"></i> Anterior';
  prev.disabled = currentPage === 1;
  prev.addEventListener('click', function () { if (currentPage > 1) { currentPage--; renderLayout(); } });
  paginationEl.appendChild(prev);

  // Calcular rango de páginas visibles (máximo 5 botones)
  const maxVisible = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
  let endPage = Math.min(totalPages, startPage + maxVisible - 1);
  if (endPage - startPage + 1 < maxVisible) {
    startPage = Math.max(1, endPage - maxVisible + 1);
  }

  // Botón de primera página + ellipsis si es necesario
  if (startPage > 1) {
    const first = document.createElement('button');
    first.className = 'page-btn';
    first.textContent = '1';
    first.addEventListener('click', function () { currentPage = 1; renderLayout(); });
    paginationEl.appendChild(first);
    if (startPage > 2) {
      const dots = document.createElement('span');
      dots.className = 'page-dots';
      dots.textContent = '...';
      paginationEl.appendChild(dots);
    }
  }

  // Botones de páginas intermedias
  for (let i = startPage; i <= endPage; i++) {
    const btn = document.createElement('button');
    btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
    btn.textContent = i;
    btn.addEventListener('click', function () { currentPage = i; renderLayout(); });
    paginationEl.appendChild(btn);
  }

  // Ellipsis + botón de última página si es necesario
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      const dots = document.createElement('span');
      dots.className = 'page-dots';
      dots.textContent = '...';
      paginationEl.appendChild(dots);
    }
    const last = document.createElement('button');
    last.className = 'page-btn';
    last.textContent = totalPages;
    last.addEventListener('click', function () { currentPage = totalPages; renderLayout(); });
    paginationEl.appendChild(last);
  }

  // Botón "Siguiente"
  const next = document.createElement('button');
  next.className = 'page-btn' + (currentPage === totalPages ? ' disabled' : '');
  next.innerHTML = 'Siguiente <i class="fas fa-chevron-right"></i>';
  next.disabled = currentPage === totalPages;
  next.addEventListener('click', function () { if (currentPage < totalPages) { currentPage++; renderLayout(); } });
  paginationEl.appendChild(next);
}

// Guardar nuevo almacén validando campos obligatorios y rangos
async function guardarWarehouse() {
  const nameEl = document.getElementById('whName');
  const locEl = document.getElementById('whLocation');
  const maxEl = document.getElementById('whMaxShelves');
  if (!nameEl || !maxEl) return toast('Error al inicializar el formulario.', 'error');
  const name = nameEl.value.trim();
  const location = locEl ? locEl.value.trim() : '';
  const maxShelves = parseInt(maxEl.value) || 100;
  if (!name) return toast('El nombre del almacén es obligatorio.', 'error');
  if (!location) return toast('La ubicación del almacén es obligatoria.', 'error');
  if (maxShelves < 1 || maxShelves > 100) return toast('El máximo de estantes debe estar entre 1 y 100.', 'error');
  if (!confirm(`¿Está seguro de agregar el almacén "${name}"?`)) return;
  setLoading('guardarWarehouseBtn', true);
  try {
    // Enviar datos del almacén al servidor via POST
    const data = await callApi(APP_URL + 'Public/api/warehouses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, location, max_shelves: maxShelves })
    });
    if (data.success) {
      Modal.close('warehouseModal');
      toast('Almacén creado exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarWarehouseBtn', false);
  }
}

// Guardar nuevo estante validando campos, capacidad y existencia del almacén
async function guardarEstante() {
  const nameEl = document.getElementById('shelfName');
  const whEl = document.getElementById('shelfWarehouseId');
  const maxEl = document.getElementById('shelfMaxCapacity');
  if (!nameEl || !whEl || !maxEl) return toast('Error al inicializar el formulario.', 'error');
  const name = nameEl.value.trim();
  const warehouseId = parseInt(whEl.value);
  const maxCapacity = parseInt(maxEl.value) || 200;
  if (!name) return toast('El nombre del estante es obligatorio.', 'error');
  if (!warehouseId) return toast('Seleccione un almacén.', 'error');
  if (maxCapacity < 1 || maxCapacity > 200) return toast('La capacidad máxima debe estar entre 1 y 200.', 'error');
  const wh = allWarehouses.find(w => w.id === warehouseId);
  if (!wh) return toast('Almacén no encontrado.', 'error');
  if (!confirm(`¿Está seguro de agregar el estante "${name}" en "${wh.name}"?`)) return;
  setLoading('guardarEstanteBtn', true);
  try {
    // Enviar datos del estante al servidor via POST
    const data = await callApi(APP_URL + 'Public/api/locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, warehouse_id: warehouseId, max_capacity: maxCapacity })
    });
    if (data.success) {
      Modal.close('shelfModal');
      document.getElementById('shelfForm').reset();
      toast('Estante agregado exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarEstanteBtn', false);
  }
}

// Abrir modal de confirmación para eliminar un estante
function confirmarEliminarEstante(locationId, name) {
  document.getElementById('deleteShelfId').value = locationId;
  document.getElementById('deleteShelfName').textContent = name;
  Modal.open('deleteShelfModal');
}

// Ejecutar eliminación de estante enviando DELETE al servidor
async function eliminarEstante() {
  const id = parseInt(document.getElementById('deleteShelfId').value);
  if (!id) return;
  setLoading('confirmDeleteShelfModalBtn', true);
  try {
    const data = await callApi(APP_URL + 'Public/api/locations.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ id })
    });
    if (data.success) {
      Modal.close('deleteShelfModal');
      toast('Estante eliminado exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
  } finally {
    setLoading('confirmDeleteShelfModalBtn', false);
  }
}

// Abrir modal para ver materiales almacenados en un estante específico
async function abrirVerMateriales(shelfId, shelfName) {
  document.getElementById('viewShelfModalTitle').textContent = `Materiales en ${shelfName}`;
  const tbody = document.getElementById('viewShelfBody');
  tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray)">Cargando...</td></tr>';
  Modal.open('viewShelfModal');
  try {
    // Consultar stock del estante al servidor
    const data = await callApi(APP_URL + 'Public/api/storage.php?action=stock&location_id=' + shelfId);
    const items = data.success ? data.data : [];
    tbody.innerHTML = '';
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray)">Este estante no tiene materiales.</td></tr>';
      return;
    }
    // Renderizar filas de materiales con código, nombre y cantidad
    const frag = document.createDocumentFragment();
    items.forEach(item => {
      const tr = document.createElement('tr');
      const mat = allMaterials.find(m => m.id === item.materialId || m.id === item.material_id);
      const code = mat ? (mat.code || '—') : (item.code || '—');
      const name = mat ? mat.name : (item.name || '—');
      const qty = item.quantity || 0;
      tr.innerHTML = `<td>${escapeHtml(code)}</td><td>${escapeHtml(name)}</td><td>${qty}</td>`;
      frag.appendChild(tr);
    });
    tbody.appendChild(frag);
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray)">Error al cargar materiales.</td></tr>';
  }
}

// Abrir modal para mover material de un estante a otro
async function abrirMoverShelf(shelfId, shelfName) {
  document.getElementById('moveShelfId').value = shelfId;
  document.getElementById('moveShelfOrigin').value = shelfName;
  const matSelect = document.getElementById('moveShelfMaterial');
  matSelect.innerHTML = '<option value="">Cargando materiales...</option>';
  matSelect.disabled = true;
  Modal.open('moveFromShelfModal');
  try {
    // Consultar materiales disponibles en el estante de origen
    const data = await callApi(APP_URL + 'Public/api/storage.php?action=stock&location_id=' + shelfId);
    const items = data.success ? data.data : [];
    matSelect.innerHTML = '<option value="">Seleccionar material...</option>';
    // Poblar select de materiales con stock disponible
    items.forEach(item => {
      const matId = item.materialId || item.material_id;
      const mat = allMaterials.find(m => m.id === matId);
      if (mat) {
        const opt = document.createElement('option');
        opt.value = mat.id;
        opt.textContent = `${mat.name} (${mat.code || 'sin código'}) — Stock: ${item.quantity || 0}`;
        matSelect.appendChild(opt);
      }
    });
    matSelect.disabled = false;
    // Actualizar max del input de cantidad al seleccionar un material
    matSelect.addEventListener('change', function() {
      const opt = this.options[this.selectedIndex];
      const stockMatch = opt.text.match(/Stock:\s*(\d+)/);
      const maxStock = stockMatch ? parseInt(stockMatch[1]) : 0;
      const qtyInput = document.getElementById('moveShelfQuantity');
      qtyInput.max = maxStock;
      qtyInput.placeholder = `Máx: ${maxStock}`;
    });
    // Llenar select de ubicaciones destino excluyendo la origen
    llenarSelectUbicacion('moveShelfDestination', null, shelfId);
    document.getElementById('moveShelfQuantity').value = '';
    document.getElementById('moveShelfQuantity').max = 0;
    document.getElementById('moveShelfReason').value = 'reorganizacion';
    document.getElementById('moveShelfNotes').value = '';
    // Limpiar estados de error previos
    document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  } catch (err) {
    matSelect.innerHTML = '<option value="">Error al cargar materiales</option>';
    toast('Error al cargar materiales del estante.', 'error');
  }
}

// Validar y guardar movimiento de material entre estantes
async function guardarMovimientoShelf() {
  const fromLocationId = parseInt(document.getElementById('moveShelfId').value);
  const materialId = parseInt(document.getElementById('moveShelfMaterial').value);
  const toLocationId = parseInt(document.getElementById('moveShelfDestination').value);
  const quantity = parseInt(document.getElementById('moveShelfQuantity').value);
  const reason = document.getElementById('moveShelfReason').value;
  const notes = document.getElementById('moveShelfNotes').value.trim();
  // Limpiar errores de validación previos
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  let valid = true;
  // Validar campos obligatorios
  if (!materialId) { valid = false; toast('Seleccione un material.', 'error'); }
  if (!toLocationId) { document.getElementById('moveShelfDestination').classList.add('is-invalid'); valid = false; }
  if (!quantity || quantity <= 0) { document.getElementById('moveShelfQuantity').classList.add('is-invalid'); valid = false; }
  // Validar que origen y destino sean diferentes
  if (fromLocationId === toLocationId) { toast('La ubicación de destino debe ser diferente.', 'error'); return; }
  // Validar que la cantidad no exceda el stock disponible en origen
  const matSelect = document.getElementById('moveShelfMaterial');
  const selectedOpt = matSelect.options[matSelect.selectedIndex];
  const stockMatch = selectedOpt.text.match(/Stock:\s*(\d+)/);
  const maxStock = stockMatch ? parseInt(stockMatch[1]) : 0;
  if (quantity > maxStock) {
    toast(`Solo hay ${maxStock} unidades disponibles de este material en el origen.`, 'error');
    document.getElementById('moveShelfQuantity').classList.add('is-invalid');
    return;
  }
  if (!valid) return;
  if (!confirm('¿Está seguro de mover ' + quantity + ' unidades?')) return;
  setLoading('guardarMovimientoShelfBtn', true);
  try {
    // Enviar solicitud de movimiento al servidor via POST
    const data = await callApi(APP_URL + 'Public/api/storage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ action: 'move', material_id: materialId, from_location_id: fromLocationId, to_location_id: toLocationId, quantity, reason, notes })
    });
    if (data.success) {
      Modal.close('moveFromShelfModal');
      toast('Material movido exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarMovimientoShelfBtn', false);
  }
}

// Recargar todos los datos del módulo de almacenes en paralelo
async function recargarDatos() {
  try {
    const [mat, cat, loc, wh, ls] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/locations.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/warehouses.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/storage.php?action=locations-stock').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; }))
    ]);
    // Actualizar arrays globales solo si la respuesta fue exitosa
    if (mat.success) allMaterials = mat.data;
    if (cat.success) allCategories = cat.data;
    if (loc.success) allLocations = loc.data;
    if (wh.success) allWarehouses = wh.data;
    if (ls.success) allLocationStock = ls.data;
    // Re-renderizar el layout con los datos actualizados
    renderLayout();
  } catch (err) {
    console.error('Error al recargar:', err.message);
  }
}

// Marcar un campo con borde de error visual (clase CSS is-invalid)
function marcarError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('is-invalid');
}