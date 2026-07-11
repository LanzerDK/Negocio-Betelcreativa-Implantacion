// Estado global: arrays de datos cargados desde la API
let allMaterials = [];
let allCategories = [];
let allLocations = [];
let allWarehouses = [];

// Mapa de proveedores { id: company_name } para selects y vista
let suppliersMap = {};
// Datos y paginación del historial de movimientos
let historyData = [];
let historyPage = 1;
// Pestaña activa actual (inventory o history)
let currentTab = 'inventory';
// Filtros activos de la tabla de inventario
const FILTERS = { search: '', categoryId: '', status: '' };
// Elementos por página en inventario e historial
const PER_PAGE = 10;
const HISTORY_PER_PAGE = 15;
let currentPage = 1;

// Alternar estado de carga (deshabilitar/habilitar botón) para evitar envíos duplicados
function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('btn-loading', loading);
}

// Inicialización al cargar el DOM: datos y eventos de la vista de inventario
document.addEventListener('DOMContentLoaded', function () {
  cargarDatosIniciales();
  // Filtro de búsqueda por texto (nombre o código)
  document.getElementById('searchInput')?.addEventListener('input', function () {
    FILTERS.search = this.value.toLowerCase();
    currentPage = 1;
    renderTabla();
  });
  // Filtro por categoría
  document.getElementById('categoryFilter')?.addEventListener('change', function () {
    FILTERS.categoryId = this.value;
    currentPage = 1;
    renderTabla();
  });
  // Filtro por estado de stock (agotado, bajo, en stock)
  document.getElementById('statusFilter')?.addEventListener('change', function () {
    FILTERS.status = this.value;
    currentPage = 1;
    renderTabla();
  });
  // Botón aplicar filtros (recarga la tabla)
  document.getElementById('applyFilters')?.addEventListener('click', function () {
    currentPage = 1;
    renderTabla();
  });
  // Cambiar a pestaña de inventario
  document.getElementById('viewInventoryTab')?.addEventListener('click', function () {
    currentTab = 'inventory';
    document.getElementById('inventorySection').style.display = 'block';
    document.getElementById('historySection').style.display = 'none';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
  // Cambiar a pestaña de historial de movimientos
  document.getElementById('viewHistoryTab')?.addEventListener('click', function () {
    currentTab = 'history';
    document.getElementById('inventorySection').style.display = 'none';
    document.getElementById('historySection').style.display = 'block';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    cargarHistorial(1);
  });
  // Botón agregar nuevo material desde el filtro
  document.getElementById('addMaterialInFilterBtn')?.addEventListener('click', function () {
    abrirModalNuevoMaterial();
  });
  // Botones de guardar material y ajuste de inventario
  document.getElementById('guardarNuevoMaterialBtn')?.addEventListener('click', guardarNuevoMaterial);
  document.getElementById('guardarAjusteBtn')?.addEventListener('click', guardarAjuste);
  // Validación en tiempo real de cantidad al escribir
  document.getElementById('adjustQuantity')?.addEventListener('input', validarCantidadTiempoReal);
  // Cambiar tipo de ajuste (entrada/salida) actualiza motivos, precio y validación
  document.getElementById('adjustType')?.addEventListener('change', function () {
    actualizarMotivosAjuste();
    toggleSupplierPrice();
    validarCantidadTiempoReal();
  });
  // Mostrar/ocultar campo de cantidad por mayoreo según tipo de costo
  document.getElementById('addMatCostType')?.addEventListener('change', function () {
    document.getElementById('addMatWholesaleQtyGroup').style.display = this.value === 'wholesale' ? 'block' : 'none';
  });
  // Actualizar información de conversión al cambiar tipo de ingreso (unitario/paquete)
  document.querySelectorAll('input[name="tipoIngreso"]').forEach(radio => {
    radio.addEventListener('change', function () {
      const id = parseInt(document.getElementById('adjustId').value);
      const mat = allMaterials.find(m => m.id === id);
      if (mat) actualizarInfoConversion(mat);
    });
  });
  // Actualizar stock y info de conversión al cambiar material en el selector de ajuste
  document.getElementById('adjustMaterial')?.addEventListener('change', function () {
    const id = parseInt(this.value);
    const mat = allMaterials.find(m => m.id === id);
    if (mat) {
      document.getElementById('adjustCurrentStock').value = mat.stock || 0;
      actualizarInfoConversion(mat);
    }
  });
});

// Cargar datos iniciales al montar la página
async function cargarDatosIniciales() {
  await recargarDatos();
}

// Renderizar estadísticas resumen del inventario
function renderOverview(summary) {
  document.getElementById('totalMaterials').textContent = summary.totalMaterials || 0;
  document.getElementById('totalLocations').textContent = summary.totalLocations || 0;
  document.getElementById('lowStockCount').textContent = summary.lowStock || 0;
  document.getElementById('outOfStockCount').textContent = summary.outOfStock || 0;
}

// Poblar todos los selectores de la vista (categorías, materiales, ubicaciones, proveedores)
function llenarSelectores() {
  // Select de filtro de categorías
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
  // Select de material en el modal de ajuste
  llenarSelectMaterial('adjustMaterial', null);
  // Select de ubicación en el modal de nuevo material
  llenarSelectUbicacion('addMaterialLocation', null);
  // Select de categoría en el modal de nuevo material
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
  // Select de proveedor en el modal de nuevo material
  const supSel = document.getElementById('addMatSupplier');
  if (supSel) {
    supSel.innerHTML = '<option value="">Seleccionar proveedor</option>';
    Object.entries(suppliersMap).forEach(([id, name]) => {
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = name;
      supSel.appendChild(opt);
    });
  }
  // Select de proveedor en el modal de ajuste
  const adjustSupSel = document.getElementById('adjustSupplier');
  if (adjustSupSel) {
    adjustSupSel.innerHTML = '<option value="">Ninguno</option>';
    Object.entries(suppliersMap).forEach(([id, name]) => {
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = name;
      adjustSupSel.appendChild(opt);
    });
  }
  // Select de ubicación en el modal de ajuste
  const adjustLocSel = document.getElementById('adjustLocation');
  if (adjustLocSel) {
    adjustLocSel.innerHTML = '<option value="">Seleccionar ubicación...</option>';
    allLocations.forEach(l => {
      const opt = document.createElement('option');
      opt.value = l.id;
      const wh = allWarehouses.find(w => w.id === l.warehouse_id);
      opt.textContent = l.name + (wh ? ' (' + wh.name + ')' : '') + ' — Cap. ' + (l.max_capacity || 'N/A');
      adjustLocSel.appendChild(opt);
    });
  }
}

// Poblar select de materiales solo con materiales activos
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

// Poblar select de ubicaciones con opción de preselección
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

// Renderizar tabla de inventario con filtros, paginación y filas de materiales
function renderTabla() {
  const container = document.getElementById('tableBody');
  if (!container) return;
  let filtered = allMaterials;
  // Aplicar filtro de búsqueda por nombre o código
  if (FILTERS.search) {
    const s = FILTERS.search;
    filtered = filtered.filter(m =>
      m.name.toLowerCase().includes(s) ||
      (m.code && m.code.toLowerCase().includes(s))
    );
  }
  // Aplicar filtro por categoría
  if (FILTERS.categoryId) {
    filtered = filtered.filter(m => String(m.category_id) === FILTERS.categoryId);
  }
  // Aplicar filtro por estado de stock
  if (FILTERS.status === 'in-stock') filtered = filtered.filter(m => m.stock > 10);
  else if (FILTERS.status === 'low-stock') filtered = filtered.filter(m => m.stock > 0 && m.stock <= 10);
  else if (FILTERS.status === 'out-of-stock') filtered = filtered.filter(m => !m.stock || m.stock <= 0);
  // Calcular paginación
  const total = filtered.length;
  const totalPages = Math.ceil(total / PER_PAGE) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  const start = (currentPage - 1) * PER_PAGE;
  const end = Math.min(start + PER_PAGE, total);
  const pageItems = filtered.slice(start, end);
  renderPaginacion(currentPage, totalPages, total);
  // Renderizar filas de materiales
  container.innerHTML = '';
  const fragment = document.createDocumentFragment();
  pageItems.forEach(m => {
    // Determinar clase y texto de estado de stock
    const statusClass = !m.stock || m.stock <= 0 ? 'out-of-stock' : m.stock <= 10 ? 'low-stock' : 'in-stock';
    const statusText = !m.stock || m.stock <= 0 ? 'Agotado' : m.stock <= 10 ? 'Stock Bajo' : 'En Stock';
    const row = document.createElement('div');
    row.className = 'table-row';
    row.dataset.id = m.id;
    // Construir HTML de la fila con ID, nombre, categoría, proveedor, stock y acciones
    row.innerHTML = `
      <div class="col-1" data-label="ID">#${m.id}</div>
      <div class="col-2" data-label="Material">
        <div style="display:flex;align-items:center;gap:16px;">
          <div class="material-img"><i class="fas fa-box"></i></div>
          <div class="material-info">
            <strong>${escapeHtml(m.name)}</strong>
            <div class="material-code">Código: ${escapeHtml(m.code || '—')}</div>
          </div>
        </div>
      </div>
      <div class="col-3" data-label="Categoría">${escapeHtml(allCategories.find(c => c.id === m.category_id)?.name || '—')}</div>
      <div class="col-supplier" data-label="Proveedor">${escapeHtml(suppliersMap[m.supplier_id] || '—')}</div>
      <div class="col-4" data-label="Stock"><span class="status ${statusClass}">${formatearStock(m)}</span></div>
      <div class="col-5" data-label="Acciones">
        <button class="action-btn adjust" data-id="${m.id}" title="Ajustar Inventario"><i class="fas fa-sliders-h"></i></button>
      </div>
    `;
    // Evento: abrir modal de ajuste para este material
    row.querySelector('.adjust')?.addEventListener('click', () => abrirAjustar(m));
    fragment.appendChild(row);
  });
  container.appendChild(fragment);
}

// Renderizar controles de paginación de la tabla de inventario
function renderPaginacion(page, totalPages, total) {
  // Mostrar rango de elementos visibles y total
  const info = document.getElementById('pageInfo');
  if (info) info.textContent = `Mostrando ${total > 0 ? (page - 1) * PER_PAGE + 1 : 0}-${Math.min(page * PER_PAGE, total)} de ${total} materiales`;
  const controls = document.getElementById('pageControls');
  if (!controls) return;
  controls.innerHTML = '';
  if (totalPages <= 1) return;
  // Botón anterior
  const prev = document.createElement('button');
  prev.className = 'page-btn';
  prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
  prev.disabled = page <= 1;
  prev.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTabla(); } });
  controls.appendChild(prev);
  // Botones numéricos de página
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.className = 'page-btn' + (i === page ? ' active' : '');
    btn.textContent = i;
    btn.addEventListener('click', () => { currentPage = i; renderTabla(); });
    controls.appendChild(btn);
  }
  // Botón siguiente
  const next = document.createElement('button');
  next.className = 'page-btn';
  next.innerHTML = '<i class="fas fa-chevron-right"></i>';
  next.disabled = page >= totalPages;
  next.addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; renderTabla(); } });
  controls.appendChild(next);
}

// Abrir modal de ajuste de inventario para un material específico
async function abrirAjustar(material) {
  document.getElementById('adjustId').value = material.id;
  document.getElementById('adjustMaterial').value = material.id;
  document.getElementById('adjustMaterial').disabled = true;
  document.getElementById('adjustType').value = 'entry';
  document.getElementById('adjustQuantity').value = '';
  document.getElementById('adjustReason').value = 'ajuste';
  document.getElementById('adjustNotes').value = '';
  document.getElementById('adjustSupplier').value = '';
  document.getElementById('adjustPurchasePrice').value = '';
  document.getElementById('adjustCurrentStock').value = material.stock || 0;
  // Repoblar select de proveedores
  const adjSup = document.getElementById('adjustSupplier');
  if (adjSup) {
    adjSup.innerHTML = '<option value="">Ninguno</option>';
    Object.entries(suppliersMap).forEach(([id, name]) => {
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = name;
      adjSup.appendChild(opt);
    });
  }
  // Poblar select de ubicaciones
  const locSel = document.getElementById('adjustLocation');
  if (locSel) {
    locSel.innerHTML = '<option value="">Seleccionar ubicación...</option>';
    allLocations.forEach(l => {
      const opt = document.createElement('option');
      opt.value = l.id;
      const wh = allWarehouses.find(w => w.id === l.warehouse_id);
      opt.textContent = l.name + (wh ? ' (' + wh.name + ')' : '') + ' — Cap. ' + (l.max_capacity || 'N/A');
      locSel.appendChild(opt);
    });
  }
  actualizarMotivosAjuste();
  toggleSupplierPrice();
  // Limpiar estados de error previos
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  // Restablecer radio de tipo de ingreso a unitario
  document.getElementById('tipoUnitario').checked = true;
  actualizarInfoConversion(material);
  // Cargar stock por ubicación del material desde la API
  try {
    const data = await callApi(APP_URL + 'Public/api/storage.php?action=stock&material_id=' + material.id);
    const locContainer = document.getElementById('adjustLocations');
    if (locContainer) {
      if (data.success && data.data && data.data.length > 0) {
        locContainer.innerHTML = '<div class="location-stock-title"><i class="fas fa-map-marker-alt"></i> Ubicaciones:</div>';
        data.data.forEach(item => {
          const loc = allLocations.find(l => l.id === item.locationId);
          if (!loc) return;
          const wh = allWarehouses.find(w => w.id === loc.warehouse_id);
          const whName = wh ? wh.name + ' / ' : '';
          const capText = loc.max_capacity ? ' (Capacidad: ' + loc.max_capacity + ')' : '';
          const div = document.createElement('div');
          div.className = 'location-stock-item';
          div.textContent = '• ' + whName + loc.name + ' — ' + item.quantity + ' unid.' + capText;
          locContainer.appendChild(div);
        });
      } else {
        locContainer.innerHTML = '<div class="location-stock-empty">Sin stock en ubicaciones.</div>';
      }
    }
  } catch (e) {
    console.error('Error al cargar ubicaciones:', e);
  }
  Modal.open('adjustModal');
}

// Mostrar u ocultar campos de proveedor y precio de compra según tipo de ajuste
function toggleSupplierPrice() {
  const type = document.getElementById('adjustType').value;
  const show = type === 'entry';
  document.getElementById('supplierGroup').style.display = show ? 'block' : 'none';
  document.getElementById('purchasePriceGroup').style.display = show ? 'block' : 'none';
}

// Actualizar texto informativo de conversión entre unidades de compra y consumo
function actualizarInfoConversion(material) {
  const infoEl = document.getElementById('conversionInfo');
  const fc = material.factor_conversion || 1;
  const uc = material.unidad_compra || 'Paquete';
  const ucon = material.unidad_consumo || 'Unidad';
  const selected = document.querySelector('input[name="tipoIngreso"]:checked')?.value || 'Unitario';
  if (fc > 1) {
    // Mostrar factor de conversión cuando aplica (factor > 1)
    if (selected === 'Paquete') {
      infoEl.textContent = `1 ${uc} = ${fc} ${ucon}(s). El stock se ajustará en ${ucon}(s).`;
    } else {
      infoEl.textContent = `Factor de conversión: ${fc} ${ucon}(s) por ${uc}.`;
    }
  } else {
    infoEl.textContent = '';
  }
}

// Validar campos y guardar ajuste de inventario (entrada o salida de stock)
async function guardarAjuste() {
  const materialId = parseInt(document.getElementById('adjustId').value);
  const type = document.getElementById('adjustType').value;
  const cantidadIngresada = parseInt(document.getElementById('adjustQuantity').value);
  const tipoIngreso = document.querySelector('input[name="tipoIngreso"]:checked')?.value || 'Unitario';
  const reason = document.getElementById('adjustReason').value;
  const notes = document.getElementById('adjustNotes').value.trim();
  const adjSup = document.getElementById('adjustSupplier');
  const adjSupVal = adjSup.value;
  const supplier = adjSup.selectedIndex > 0 ? adjSup.options[adjSup.selectedIndex].textContent.trim() : '';
  const locationId = parseInt(document.getElementById('adjustLocation').value) || null;
  const purchasePrice = parseFloat(document.getElementById('adjustPurchasePrice').value) || null;
  // Limpiar errores de validación previos
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  let valid = true;
  if (!materialId) { valid = false; }
  if (!cantidadIngresada || cantidadIngresada <= 0) { marcarError('adjustQuantity'); valid = false; }
  if (!valid) return toast('Complete todos los campos requeridos.', 'error');
  // Validar que el motivo sea coherente con el tipo de ajuste
  if (type === 'entry' && (reason === 'venta' || reason === 'perdida')) {
    return toast('Motivo inválido: para una entrada de stock el motivo no puede ser venta o pérdida.', 'error');
  }
  if (type === 'exit' && reason === 'compra') {
    return toast('Motivo inválido: para una salida de stock el motivo no puede ser compra.', 'error');
  }
  // Calcular cantidad real considerando factor de conversión de paquetes
  const currentStock = parseInt(document.getElementById('adjustCurrentStock').value);
  const mat = allMaterials.find(m => m.id === materialId);
  const fc = mat?.factor_conversion || 1;
  const realQuantity = tipoIngreso === 'Paquete' ? cantidadIngresada * fc : cantidadIngresada;
  // Validar que la salida no exceda el stock actual
  if (type === 'exit' && realQuantity > currentStock) {
    marcarError('adjustQuantity');
    return toast('Cantidad inválida, la salida no puede ser mayor al stock actual.', 'error');
  }
  if (!confirm('¿Está seguro de registrar este ajuste de inventario?')) return;
  setLoading('guardarAjusteBtn', true);
  try {
    // Construir body de la petición con datos del ajuste
    const body = { action: 'adjust', material_id: materialId, type, cantidad_ingresada: cantidadIngresada, tipo_ingreso: tipoIngreso, reason, notes };
    if (supplier) { body.supplier = supplier; body.supplier_id = adjSupVal; }
    if (purchasePrice !== null) body.purchase_price = purchasePrice;
    if (locationId) body.location_id = locationId;
    const data = await callApi(APP_URL + 'Public/api/storage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify(body)
    });
    if (data.success) {
      Modal.close('adjustModal');
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

// Recargar todos los datos del módulo de inventario en paralelo
async function recargarDatos() {
  try {
    const [mat, cat, loc, sum, sup, wh] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/locations.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/storage.php?action=summary').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/admin/suppliers.php').then(r => r.json()).catch(() => ({ success: false, data: [] })),
      fetch(APP_URL + 'Public/api/warehouses.php').then(r => r.json()).catch(() => ({ success: false, data: [] }))
    ]);
    // Actualizar arrays y mapa de datos globales
    if (mat.success) allMaterials = mat.data;
    if (cat.success) allCategories = cat.data;
    if (loc.success) {
      allLocations = loc.data;
    }
    if (wh && wh.success) allWarehouses = wh.data;
    if (sup && sup.success) {
      suppliersMap = {};
      sup.data.forEach(s => { suppliersMap[s.id] = s.company_name; });
    }
    if (sum.success) renderOverview(sum.data);
    renderTabla();
    llenarSelectores();
  } catch (err) {
    console.error('Error al recargar:', err.message);
  }
}

// Cargar y renderizar historial de movimientos de inventario con paginación del servidor
async function cargarHistorial(page) {
  historyPage = page;
  const container = document.getElementById('historyBody');
  if (!container) return;
  try {
    // Consultar historial paginado al servidor
    const data = await callApi(APP_URL + 'Public/api/storage.php?action=history&page=' + page + '&per_page=' + HISTORY_PER_PAGE);
    if (!data.success) { container.innerHTML = '<tr><td colspan="9">Error al cargar historial</td></tr>'; return; }
    historyData = data.data.data || [];
    const total = data.data.total || 0;
    const totalPages = data.data.totalPages || 1;
    document.getElementById('historyInfo').textContent = `Mostrando ${historyData.length} de ${total} movimientos`;
    // Renderizar paginación del historial
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
    // Renderizar filas del historial de movimientos
    container.innerHTML = '';
    if (!historyData.length) {
      container.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray)">No hay movimientos registrados</td></tr>';
      return;
    }
    const frag = document.createDocumentFragment();
    historyData.forEach(h => {
      const tr = document.createElement('tr');
      // Formatear fecha al formato local venezolano
      const date = h.movementDate ? new Date(h.movementDate).toLocaleString('es-VE') : '—';
      // Mapear tipo de acción a etiqueta en español
      const typeLabel = { Entry: 'Entrada', Exit: 'Salida', Transfer: 'Traslado' }[h.actionType] || h.actionType;
      const qtyStr = h.actionType === 'Entry' ? '+' + h.quantity : h.actionType === 'Exit' ? '-' + h.quantity : h.quantity + ' uds';
      const originDest = h.actionType === 'Transfer' ? `${h.originName || '—'} → ${h.destinationName || '—'}` : '—';
      let supplierText = '—';
      let priceText = '—';
      // Extraer datos de proveedor y precio del campo extraNote (JSON)
      if (h.extraNote) {
        try {
          const parsed = JSON.parse(h.extraNote);
          if (parsed.meta) {
            if (parsed.meta.supplier) supplierText = escapeHtml(parsed.meta.supplier);
            if (parsed.meta.purchase_price) priceText = 'Bs ' + parseFloat(parsed.meta.purchase_price).toFixed(2);
          }
        } catch (e) {}
      }
      tr.innerHTML = `
        <td>${date}</td>
        <td><strong>${escapeHtml(h.materialName || '—')}</strong><br><small style="color:var(--gray)">${escapeHtml(h.materialCode || '')}</small></td>
        <td><span class="status ${h.actionType === 'Entry' ? 'in-stock' : h.actionType === 'Exit' ? 'out-of-stock' : 'low-stock'}">${typeLabel}</span></td>
        <td>${qtyStr}</td>
        <td>${originDest}</td>
        <td>${escapeHtml(h.userName || '—')}</td>
        <td>${escapeHtml(h.reason || '—')}</td>
        <td>${supplierText}</td>
        <td>${priceText}</td>
      `;
      frag.appendChild(tr);
    });
    container.appendChild(frag);
  } catch (err) {
    container.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray)">Error al cargar historial</td></tr>';
  }
}

// Actualizar opciones de motivo según el tipo de ajuste seleccionado (entrada/salida)
function actualizarMotivosAjuste() {
  const type = document.getElementById('adjustType').value;
  const sel = document.getElementById('adjustReason');
  // Deshabilitar motivos incompatibles con el tipo de ajuste
  Array.from(sel.options).forEach(opt => {
    opt.disabled = false;
    if (type === 'entry' && (opt.value === 'venta' || opt.value === 'perdida')) {
      opt.disabled = true;
    }
    if (type === 'exit' && opt.value === 'compra') {
      opt.disabled = true;
    }
  });
  // Si el motivo seleccionado está deshabilitado, seleccionar el primero válido
  if (sel.selectedOptions[0]?.disabled) {
    const firstValid = Array.from(sel.options).find(o => !o.disabled);
    if (firstValid) sel.value = firstValid.value;
  }
}

// Guardar nuevo material validando campos obligatorios
async function guardarNuevoMaterial() {
  const code = document.getElementById('addMatCode').value.trim();
  const name = document.getElementById('addMatName').value.trim();
  const categoryId = parseInt(document.getElementById('addMatCategory').value) || null;
  const stock = parseInt(document.getElementById('addMatStock').value) || 0;
  const costType = document.getElementById('addMatCostType').value;
  const price = parseFloat(document.getElementById('addMatPrice').value) || 0;
  const wholesaleQty = costType === 'wholesale' ? parseInt(document.getElementById('addMatWholesaleQty').value) || 0 : 0;
  const locationId = parseInt(document.getElementById('addMaterialLocation').value) || null;
  const supplierId = parseInt(document.getElementById('addMatSupplier').value) || null;
  // Limpiar errores de validación previos
  document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  if (!name) { marcarError('addMatName'); return toast('El nombre del material es obligatorio.', 'error'); }
  if (!code) { marcarError('addMatCode'); return toast('El código del material es obligatorio.', 'error'); }
  setLoading('guardarNuevoMaterialBtn', true);
  try {
    // Enviar datos del material al servidor via POST
    const data = await callApi(APP_URL + 'Public/api/materials.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, code, category_id: categoryId, stock, cost_type: costType, price, wholesale_qty: wholesaleQty, location_id: locationId, supplier_id: supplierId })
    });
    if (data.success) {
      Modal.close('addMaterialModal');
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

// Abrir modal de nuevo material con código auto-generado
function abrirModalNuevoMaterial() {
  document.getElementById('addMaterialForm').reset();
  document.getElementById('addMatCode').value = '';
  // Generar código aleatorio con prefijo MAT-
  const codePrefix = 'MAT-';
  const randomSuffix = Math.random().toString(36).substring(2, 8).toUpperCase();
  document.getElementById('addMatCode').value = codePrefix + randomSuffix;
  Modal.open('addMaterialModal');
}

// Marcar un campo con borde de error visual (clase CSS is-invalid)
function marcarError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('is-invalid');
}

// Remover borde de error visual de un campo
function limpiarError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('is-invalid');
}

// Validar cantidad en tiempo real: debe ser > 0 y no mayor al stock para salidas
function validarCantidadTiempoReal() {
  const qtyInput = document.getElementById('adjustQuantity');
  const tipo = document.getElementById('adjustType')?.value;
  const cant = parseInt(qtyInput?.value);
  const currentStock = parseInt(document.getElementById('adjustCurrentStock')?.value || 0);
  if (!cant || cant <= 0) {
    if (qtyInput) qtyInput.classList.add('is-invalid');
  } else if (tipo === 'exit' && cant > currentStock) {
    if (qtyInput) qtyInput.classList.add('is-invalid');
  } else {
    if (qtyInput) qtyInput.classList.remove('is-invalid');
  }
}

// Formatear stock mostrando paquetes y unidades sueltas cuando factor_conversion > 1
function formatearStock(m) {
  const stock = m.stock || 0;
  const fc = m.factor_conversion || 1;
  // Si no hay factor de conversión, mostrar unidades simples
  if (fc <= 1) return stock + ' unidades';
  const paquetes = Math.floor(stock / fc);
  const sueltas = stock % fc;
  let texto = stock + ' ' + (m.unidad_consumo || 'Unidad') + '(s)';
  if (paquetes > 0 || sueltas > 0) {
    texto += ' (' + paquetes + ' ' + (m.unidad_compra || 'Paquete') + '(s)';
    if (sueltas > 0) texto += ' y ' + sueltas + ' ' + (m.unidad_consumo || 'Unidad') + '(s) sueltas';
    texto += ')';
  }
  return texto;
}

