let allMaterials = [];
let allCategories = [];
let allLocations = [];
let allZones = [];
let historyData = [];
let historyPage = 1;
let currentTab = 'inventory';

const FILTERS = { search: '', categoryId: '', status: '' };

const PER_PAGE = 10;
const HISTORY_PER_PAGE = 15;
let currentPage = 1;

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('btn-loading', loading);
}

document.addEventListener('DOMContentLoaded', function () {
  cargarDatosIniciales();
  document.getElementById('settingsBtn')?.addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('settingsDropdown')?.classList.toggle('show');
  });
  document.addEventListener('click', function () {
    document.getElementById('settingsDropdown')?.classList.remove('show');
  });
  document.getElementById('searchInput')?.addEventListener('input', function () {
    FILTERS.search = this.value.toLowerCase();
    currentPage = 1;
    renderTabla();
  });
  document.getElementById('categoryFilter')?.addEventListener('change', function () {
    FILTERS.categoryId = this.value;
    currentPage = 1;
    renderTabla();
  });
  document.getElementById('statusFilter')?.addEventListener('change', function () {
    FILTERS.status = this.value;
    currentPage = 1;
    renderTabla();
  });
  document.getElementById('applyFilters')?.addEventListener('click', function () {
    currentPage = 1;
    renderTabla();
  });
  document.getElementById('viewInventoryTab')?.addEventListener('click', function () {
    currentTab = 'inventory';
    document.getElementById('inventorySection').style.display = 'block';
    document.getElementById('historySection').style.display = 'none';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
  document.getElementById('viewHistoryTab')?.addEventListener('click', function () {
    currentTab = 'history';
    document.getElementById('inventorySection').style.display = 'none';
    document.getElementById('historySection').style.display = 'block';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    cargarHistorial(1);
  });
  document.getElementById('addShelfBtn')?.addEventListener('click', function () {
    document.getElementById('shelfZoneType').value = 'new';
    toggleShelfZoneType();
    llenarSelectZona();
    document.getElementById('shelfForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('shelfModal'));
    modal.show();
  });
  document.getElementById('guardarEstanteBtn')?.addEventListener('click', guardarEstante);
  document.getElementById('addMaterialInFilterBtn')?.addEventListener('click', function () {
    abrirModalNuevoMaterial();
  });
  document.getElementById('guardarNuevoMaterialBtn')?.addEventListener('click', guardarNuevoMaterial);
  document.getElementById('shelfZoneType')?.addEventListener('change', toggleShelfZoneType);
  document.getElementById('confirmDeleteShelfModalBtn')?.addEventListener('click', eliminarEstante);
});

async function cargarDatosIniciales() {
  await recargarDatos();
}

function renderOverview(summary) {
  document.getElementById('totalMaterials').textContent = summary.totalMaterials || 0;
  document.getElementById('totalLocations').textContent = summary.totalLocations || 0;
  document.getElementById('lowStockCount').textContent = summary.lowStock || 0;
  document.getElementById('outOfStockCount').textContent = summary.outOfStock || 0;
}

function renderLayout() {
  const grid = document.getElementById('layoutGrid');
  if (!grid) return;
  grid.innerHTML = '';
  const grouped = {};
  allLocations.forEach(l => {
    const zone = l.description || 'Otras';
    if (!grouped[zone]) grouped[zone] = [];
    grouped[zone].push(l);
  });
  for (const [zone, locs] of Object.entries(grouped)) {
    const zoneDiv = document.createElement('div');
    zoneDiv.className = 'zone';
    const iconMap = {
      'iluminación': 'fa-lightbulb', 'telas': 'fa-fan', 'globos': 'fa-candy-cane',
      'mobiliario': 'fa-chair', 'flores': 'fa-spa'
    };
    const icon = iconMap[zone.toLowerCase()] || 'fa-tag';
    zoneDiv.innerHTML = `
      <div class="zone-header">
        <div class="zone-icon"><i class="fas ${icon}"></i></div>
        <div class="zone-title">${escapeHtml(zone)}</div>
      </div>
      <div class="shelves">
        ${locs.map(l => {
          const count = allMaterials.filter(m => m.location_id === l.id || 
            (Array.isArray(m.stockLocations) && m.stockLocations.some(sl => sl.locationId === l.id))
          ).length;
          return `<div class="shelf">
            <div class="shelf-name">${escapeHtml(l.name)}</div>
            <div class="shelf-stats">
              <div class="shelf-stat">${count} items</div>
              <button class="shelf-delete" data-id="${l.id}" data-name="${escapeHtml(l.name)}" title="Eliminar estante"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>`;
        }).join('')}
      </div>
    `;
    grid.appendChild(zoneDiv);
  }
  document.querySelectorAll('.shelf-delete').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      confirmarEliminarEstante(parseInt(this.dataset.id), this.dataset.name);
    });
  });
}

function llenarSelectores() {
  const catFilter = document.getElementById('categoryFilter');
  if (catFilter) {
    catFilter.innerHTML = '<option value="">Todas las categorías</option>';
    allCategories.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      catFilter.appendChild(opt);
    });
  }
  llenarSelectMaterial('adjustMaterial', null);
  llenarSelectMaterial('moveMaterialSelect', null);
  llenarSelectUbicacion('moveNewLocation', null);
  llenarSelectUbicacion('addMaterialLocation', null);
  llenarSelectZona();
  const catSel = document.getElementById('addMatCategory');
  if (catSel) {
    catSel.innerHTML = '<option value="">Seleccionar categoría</option>';
    allCategories.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      catSel.appendChild(opt);
    });
  }
}

function llenarSelectMaterial(selectId, selected) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = '<option value="">Seleccionar material...</option>';
  allMaterials.filter(m => m.is_active !== false).forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = `${m.name} (${m.code || 'sin código'})`;
    if (selected && String(m.id) === String(selected)) opt.selected = true;
    sel.appendChild(opt);
  });
}

function llenarSelectUbicacion(selectId, selected) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  const editVal = sel.dataset.currentValue;
  sel.innerHTML = '<option value="">Seleccionar ubicación...</option>';
  allLocations.forEach(l => {
    const opt = document.createElement('option');
    opt.value = l.id;
    opt.textContent = `${l.name} - ${l.description || 'Sin zona'}`;
    const selVal = selected || editVal;
    if (selVal && String(l.id) === String(selVal)) opt.selected = true;
    sel.appendChild(opt);
  });
}

function renderTabla() {
  const container = document.getElementById('tableBody');
  if (!container) return;
  let filtered = allMaterials;
  if (FILTERS.search) {
    const s = FILTERS.search;
    filtered = filtered.filter(m =>
      m.name.toLowerCase().includes(s) ||
      (m.code && m.code.toLowerCase().includes(s))
    );
  }
  if (FILTERS.categoryId) {
    filtered = filtered.filter(m => String(m.category_id) === FILTERS.categoryId);
  }
  if (FILTERS.status === 'in-stock') filtered = filtered.filter(m => m.stock > 10);
  else if (FILTERS.status === 'low-stock') filtered = filtered.filter(m => m.stock > 0 && m.stock <= 10);
  else if (FILTERS.status === 'out-of-stock') filtered = filtered.filter(m => !m.stock || m.stock <= 0);

  const total = filtered.length;
  const totalPages = Math.ceil(total / PER_PAGE) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage - 1) * PER_PAGE;
  const end = Math.min(start + PER_PAGE, total);
  const pageItems = filtered.slice(start, end);

  renderPaginacion(currentPage, totalPages, total);
  container.innerHTML = '';
  const fragment = document.createDocumentFragment();
  pageItems.forEach(m => {
    const catName = allCategories.find(c => c.id === m.category_id)?.name || '—';
    const locName = allLocations.find(l => l.id === m.location_id)?.name || '—';
    const statusClass = !m.stock || m.stock <= 0 ? 'out-of-stock' : m.stock <= 10 ? 'low-stock' : 'in-stock';
    const statusText = !m.stock || m.stock <= 0 ? 'Agotado' : m.stock <= 10 ? 'Stock Bajo' : 'En Stock';
    const row = document.createElement('div');
    row.className = 'table-row';
    row.dataset.id = m.id;
    row.innerHTML = `
      <div class="col-1" data-label="ID">#${m.id}</div>
      <div class="col-2" data-label="Material">
        <div style="display:flex;align-items:center;gap:15px;">
          <div class="material-img"><i class="fas fa-box"></i></div>
          <div>
            <strong>${escapeHtml(m.name)}</strong>
            <div style="font-size:0.85rem;color:var(--gray)">Código: ${escapeHtml(m.code || '—')}</div>
          </div>
        </div>
      </div>
      <div class="col-3" data-label="Categoría">${escapeHtml(catName)}</div>
      <div class="col-4" data-label="Stock"><span class="status ${statusClass}">${m.stock || 0} unidades</span></div>
      <div class="col-5" data-label="Ubicación">${escapeHtml(locName)}</div>
      <div class="col-6" data-label="Acciones">
        <button class="action-btn adjust" data-id="${m.id}" title="Ajustar Inventario"><i class="fas fa-sliders-h"></i></button>
        <button class="action-btn move" data-id="${m.id}" title="Mover Material"><i class="fas fa-arrows-alt"></i></button>
      </div>
    `;
    row.querySelector('.adjust')?.addEventListener('click', () => abrirAjustar(m));
    row.querySelector('.move')?.addEventListener('click', () => abrirMover(m));
    fragment.appendChild(row);
  });
  container.appendChild(fragment);
}

function renderPaginacion(page, totalPages, total) {
  const info = document.getElementById('pageInfo');
  if (info) info.textContent = `Mostrando ${total > 0 ? (page - 1) * PER_PAGE + 1 : 0}-${Math.min(page * PER_PAGE, total)} de ${total} materiales`;
  const controls = document.getElementById('pageControls');
  if (!controls) return;
  controls.innerHTML = '';
  if (totalPages <= 1) return;
  const prev = document.createElement('button');
  prev.className = 'page-btn';
  prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
  prev.disabled = page <= 1;
  prev.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTabla(); } });
  controls.appendChild(prev);
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.className = 'page-btn' + (i === page ? ' active' : '');
    btn.textContent = i;
    btn.addEventListener('click', () => { currentPage = i; renderTabla(); });
    controls.appendChild(btn);
  }
  const next = document.createElement('button');
  next.className = 'page-btn';
  next.innerHTML = '<i class="fas fa-chevron-right"></i>';
  next.disabled = page >= totalPages;
  next.addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; renderTabla(); } });
  controls.appendChild(next);
}

async function abrirAjustar(material) {
  document.getElementById('adjustId').value = material.id;
  document.getElementById('adjustMaterial').value = material.id;
  document.getElementById('adjustType').value = 'entry';
  document.getElementById('adjustQuantity').value = '';
  document.getElementById('adjustReason').value = 'ajuste';
  document.getElementById('adjustNotes').value = '';
  document.getElementById('adjustCurrentStock').value = material.stock || 0;
  const locSel = document.getElementById('adjustLocation');
  llenarSelectUbicacion('adjustLocation', null);
  const firstOpt = document.createElement('option');
  firstOpt.value = '';
  firstOpt.textContent = 'Ubicación principal del material';
  locSel.prepend(firstOpt);
  locSel.value = '';
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  const modal = new bootstrap.Modal(document.getElementById('adjustModal'));
  modal.show();
}

async function abrirMover(material) {
  document.getElementById('moveId').value = material.id;
  llenarSelectMaterial('moveMaterialSelect', material.id);
  document.getElementById('moveMaterialSelect').disabled = true;
  const locs = await fetchStockLocations(material.id);
  const currentLoc = locs.length > 0 ? locs[0].locationId : material.location_id;
  document.getElementById('moveCurrentLocation').value = allLocations.find(l => l.id === currentLoc)?.name || '—';
  document.getElementById('moveCurrentLocation').dataset.locationId = currentLoc || '';
  const toSel = document.getElementById('moveNewLocation');
  llenarSelectUbicacion('moveNewLocation', null);
  Array.from(toSel.options).forEach(opt => {
    if (opt.value === String(currentLoc)) opt.disabled = true;
  });
  document.getElementById('moveQuantity').value = '';
  document.getElementById('moveReason').value = 'reorganizacion';
  document.getElementById('moveNotes').value = '';
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  const modal = new bootstrap.Modal(document.getElementById('moveModal'));
  modal.show();
}

async function fetchStockLocations(materialId) {
  try {
    const res = await fetch(APP_URL + 'api/storage.php?action=stock&material_id=' + materialId);
    const data = await res.json();
    return data.success ? data.data : [];
  } catch {
    return [];
  }
}

document.getElementById('guardarAjusteBtn')?.addEventListener('click', guardarAjuste);
document.getElementById('guardarMovimientoBtn')?.addEventListener('click', guardarMovimiento);
document.getElementById('addMatCostType')?.addEventListener('change', function () {
  document.getElementById('addMatWholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
});

async function guardarAjuste() {
  const materialId = parseInt(document.getElementById('adjustId').value);
  const type = document.getElementById('adjustType').value;
  const quantity = parseInt(document.getElementById('adjustQuantity').value);
  const reason = document.getElementById('adjustReason').value;
  const notes = document.getElementById('adjustNotes').value.trim();
  const locationId = parseInt(document.getElementById('adjustLocation').value) || null;
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  let valid = true;
  if (!materialId) { valid = false; }
  if (!quantity || quantity <= 0) { marcarError('adjustQuantity'); valid = false; }
  if (!valid) return toast('Complete todos los campos requeridos.', 'error');
  if (!confirm('¿Está seguro de registrar este ajuste de inventario?')) return;
  setLoading('guardarAjusteBtn', true);
  try {
    const res = await fetch(APP_URL + 'api/storage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ action: 'adjust', material_id: materialId, type, quantity, reason, notes, location_id: locationId })
    });
    const data = await res.json();
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('adjustModal'))?.hide();
      toast('Ajuste registrado exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarAjusteBtn', false);
  }
}

async function guardarMovimiento() {
  const materialId = parseInt(document.getElementById('moveId').value);
  const fromLocationId = parseInt(document.getElementById('moveCurrentLocation').dataset.locationId);
  const toLocationId = parseInt(document.getElementById('moveNewLocation').value);
  const quantity = parseInt(document.getElementById('moveQuantity').value);
  const reason = document.getElementById('moveReason').value;
  const notes = document.getElementById('moveNotes').value.trim();
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  let valid = true;
  if (!materialId || !fromLocationId) { valid = false; }
  if (!toLocationId) { marcarError('moveNewLocation'); valid = false; }
  if (!quantity || quantity <= 0) { marcarError('moveQuantity'); valid = false; }
  if (fromLocationId === toLocationId) { toast('La ubicación de destino debe ser diferente.', 'error'); return; }
  if (!valid) return toast('Complete todos los campos requeridos.', 'error');
  if (!confirm('¿Está seguro de mover ' + quantity + ' unidades a la nueva ubicación?')) return;
  setLoading('guardarMovimientoBtn', true);
  try {
    const res = await fetch(APP_URL + 'api/storage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ action: 'move', material_id: materialId, from_location_id: fromLocationId, to_location_id: toLocationId, quantity, reason, notes })
    });
    const data = await res.json();
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('moveModal'))?.hide();
      toast('Material movido exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarMovimientoBtn', false);
  }
}

async function guardarEstante() {
  const name = document.getElementById('shelfName').value.trim();
  const zoneType = document.getElementById('shelfZoneType').value;
  let zone = '';
  if (zoneType === 'new') {
    zone = document.getElementById('shelfNewZone').value.trim();
    if (!zone) return toast('Ingrese el nombre de la nueva zona.', 'error');
  } else {
    zone = document.getElementById('shelfZoneSelect').value;
    if (!zone) return toast('Seleccione una zona existente.', 'error');
  }
  if (!name) return toast('El nombre del estante es obligatorio.', 'error');
  if (!confirm(`¿Está seguro de agregar el estante "${name}" en la zona "${zone}"?`)) return;
  setLoading('guardarEstanteBtn', true);
  try {
    const res = await fetch(APP_URL + 'api/locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, description: zone })
    });
    const data = await res.json();
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('shelfModal'))?.hide();
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

function confirmarEliminarEstante(locationId, name) {
  document.getElementById('deleteShelfId').value = locationId;
  document.getElementById('deleteShelfName').textContent = name;
  const modal = new bootstrap.Modal(document.getElementById('deleteShelfModal'));
  modal.show();
}

async function eliminarEstante() {
  const id = parseInt(document.getElementById('deleteShelfId').value);
  if (!id) return;
  setLoading('confirmDeleteShelfModalBtn', true);
  try {
    const res = await fetch(APP_URL + 'api/locations.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('deleteShelfModal'))?.hide();
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

async function recargarDatos() {
  try {
    const [mat, cat, loc, sum] = await Promise.all([
      fetch(APP_URL + 'api/materials.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'api/categories.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'api/locations.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'api/storage.php?action=summary').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; }))
    ]);
    if (mat.success) allMaterials = mat.data;
    if (cat.success) allCategories = cat.data;
    if (loc.success) {
      allLocations = loc.data;
      const zoneSet = new Set();
      loc.data.forEach(l => { if (l.description) zoneSet.add(l.description); });
      allZones = Array.from(zoneSet).sort();
    }
    if (sum.success) renderOverview(sum.data);
    renderLayout();
    renderTabla();
    llenarSelectores();
  } catch (err) {
    console.error('Error al recargar:', err.message);
  }
}

async function cargarHistorial(page) {
  historyPage = page;
  const container = document.getElementById('historyBody');
  if (!container) return;
  try {
    const res = await fetch(APP_URL + 'api/storage.php?action=history&page=' + page + '&per_page=' + HISTORY_PER_PAGE);
    const data = await res.json();
    if (!data.success) { container.innerHTML = '<tr><td colspan="7">Error al cargar historial</td></tr>'; return; }
    historyData = data.data.data || [];
    const total = data.data.total || 0;
    const totalPages = data.data.totalPages || 1;
    document.getElementById('historyInfo').textContent = `Mostrando ${historyData.length} de ${total} movimientos`;
    const controls = document.getElementById('historyPages');
    controls.innerHTML = '';
    if (totalPages > 1) {
      const prev = document.createElement('button');
      prev.className = 'page-btn'; prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
      prev.disabled = page <= 1;
      prev.addEventListener('click', () => cargarHistorial(page - 1));
      controls.appendChild(prev);
      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (i === page ? ' active' : '');
        btn.textContent = i;
        btn.addEventListener('click', () => cargarHistorial(i));
        controls.appendChild(btn);
      }
      const next = document.createElement('button');
      next.className = 'page-btn'; next.innerHTML = '<i class="fas fa-chevron-right"></i>';
      next.disabled = page >= totalPages;
      next.addEventListener('click', () => cargarHistorial(page + 1));
      controls.appendChild(next);
    }
    container.innerHTML = '';
    if (!historyData.length) {
      container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">No hay movimientos registrados</td></tr>';
      return;
    }
    const frag = document.createDocumentFragment();
    historyData.forEach(h => {
      const tr = document.createElement('tr');
      const date = h.movementDate ? new Date(h.movementDate).toLocaleString('es-VE') : '—';
      const typeLabel = { Entry: 'Entrada', Exit: 'Salida', Transfer: 'Traslado' }[h.actionType] || h.actionType;
      const qtyStr = h.actionType === 'Entry' ? '+' + h.quantity : h.actionType === 'Exit' ? '-' + h.quantity : h.quantity + ' uds';
      const originDest = h.actionType === 'Transfer' ? `${h.originName || '—'} → ${h.destinationName || '—'}` : '—';
      tr.innerHTML = `
        <td>${date}</td>
        <td><strong>${escapeHtml(h.materialName || '—')}</strong><br><small style="color:var(--gray)">${escapeHtml(h.materialCode || '')}</small></td>
        <td><span class="status ${h.actionType === 'Entry' ? 'in-stock' : h.actionType === 'Exit' ? 'out-of-stock' : 'low-stock'}">${typeLabel}</span></td>
        <td>${qtyStr}</td>
        <td>${originDest}</td>
        <td>${escapeHtml(h.userName || '—')}</td>
        <td>${escapeHtml(h.reason || '—')}</td>
      `;
      frag.appendChild(tr);
    });
    container.appendChild(frag);
  } catch (err) {
    container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">Error al cargar historial</td></tr>';
  }
}

async function guardarNuevoMaterial() {
  const code = document.getElementById('addMatCode').value.trim();
  const name = document.getElementById('addMatName').value.trim();
  const categoryId = parseInt(document.getElementById('addMatCategory').value) || null;
  const stock = parseInt(document.getElementById('addMatStock').value) || 0;
  const costType = document.getElementById('addMatCostType').value;
  const price = parseFloat(document.getElementById('addMatPrice').value) || 0;
  const wholesaleQty = costType === 'wholesale' ? parseInt(document.getElementById('addMatWholesaleQty').value) || 0 : 0;
  const locationId = parseInt(document.getElementById('addMaterialLocation').value) || null;
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  if (!name) { marcarError('addMatName'); return toast('El nombre del material es obligatorio.', 'error'); }
  if (!code) { marcarError('addMatCode'); return toast('El código del material es obligatorio.', 'error'); }
  setLoading('guardarNuevoMaterialBtn', true);
  try {
    const res = await fetch(APP_URL + 'api/materials.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, code, category_id: categoryId, stock, cost_type: costType, price, wholesale_qty: wholesaleQty, location_id: locationId })
    });
    const data = await res.json();
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('addMaterialModal'))?.hide();
      toast('Material creado exitosamente.', 'success');
      await recargarDatos();
    } else {
      toast('Error: ' + data.message, 'error');
    }
  } catch (err) {
    toast('Error de conexión.', 'error');
    console.error(err);
  } finally {
    setLoading('guardarNuevoMaterialBtn', false);
  }
}

function llenarSelectZona(selected) {
  const sel = document.getElementById('shelfZoneSelect');
  if (!sel) return;
  sel.innerHTML = '<option value="">Seleccionar zona...</option>';
  allZones.forEach(z => {
    const opt = document.createElement('option');
    opt.value = z;
    opt.textContent = z;
    if (selected && z === selected) opt.selected = true;
    sel.appendChild(opt);
  });
}

function toggleShelfZoneType() {
  const val = document.getElementById('shelfZoneType')?.value;
  document.getElementById('shelfNewZoneGroup').style.display = val === 'new' ? 'block' : 'none';
  document.getElementById('shelfExistingZoneGroup').style.display = val === 'existing' ? 'block' : 'none';
}

function abrirModalNuevoMaterial() {
  const modal = new bootstrap.Modal(document.getElementById('addMaterialModal'));
  document.getElementById('addMaterialForm').reset();
  document.getElementById('addMatCode').value = '';
  // Auto-generate code
  const codePrefix = 'MAT-';
  const randomSuffix = Math.random().toString(36).substring(2, 8).toUpperCase();
  document.getElementById('addMatCode').value = codePrefix + randomSuffix;
  modal.show();
}

function marcarError(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('is-invalid'); }
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
