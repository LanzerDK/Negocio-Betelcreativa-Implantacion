let allMaterials = [];
let allCategories = [];
let allLocations = [];
let allZones = [];
let suppliersMap = {};
let currentPage = 1;
const PER_PAGE = 15;

const FILTERS = { search: '', categoryId: '', zone: '' };

document.addEventListener('DOMContentLoaded', function () {
  cargarDatos();
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
  document.getElementById('zoneFilter')?.addEventListener('change', function () {
    FILTERS.zone = this.value;
    currentPage = 1;
    renderTabla();
  });
});

async function cargarDatos() {
  try {
    const [mat, cat, loc, sup] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/locations.php').then(r => r.json()),
      fetch(APP_URL + 'Public/api/admin/suppliers.php').then(r => r.json()).catch(() => ({ success: false, data: [] }))
    ]);
    if (mat.success) allMaterials = mat.data;
    if (cat.success) allCategories = cat.data;
    if (loc.success) {
      allLocations = loc.data;
      const zoneSet = new Set();
      loc.data.forEach(l => { if (l.description) zoneSet.add(l.description); });
      allZones = Array.from(zoneSet).sort();
    }
    if (sup && sup.success) {
      suppliersMap = {};
      sup.data.forEach(s => { suppliersMap[s.id] = s.company_name; });
    }
    llenarSelectores();
    renderTabla();
  } catch (err) {
    console.error('Error al cargar datos:', err);
  }
}

function llenarSelectores() {
  const catFilter = document.getElementById('categoryFilter');
  if (catFilter) {
    catFilter.innerHTML = '<option value="">Todas</option>';
    allCategories.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      catFilter.appendChild(opt);
    });
  }
  const zoneFilter = document.getElementById('zoneFilter');
  if (zoneFilter) {
    zoneFilter.innerHTML = '<option value="">Todas las zonas</option>';
    allZones.forEach(z => {
      const opt = document.createElement('option');
      opt.value = z;
      opt.textContent = z;
      zoneFilter.appendChild(opt);
    });
  }
}

function getMaterialLocation(m) {
  const locationId = m.location_id;
  if (!locationId) return { name: '—', zone: '—' };
  const loc = allLocations.find(l => l.id === locationId);
  if (!loc) return { name: '—', zone: '—' };
  return { name: loc.name, zone: loc.description || '—' };
}

function renderTabla() {
  const container = document.getElementById('tableBody');
  if (!container) return;
  let filtered = allMaterials.filter(m => m.is_active !== false);
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
  if (FILTERS.zone) {
    filtered = filtered.filter(m => {
      const loc = allLocations.find(l => l.id === m.location_id);
      return loc && loc.description === FILTERS.zone;
    });
  }
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
    const loc = getMaterialLocation(m);
    const fc = m.factor_conversion || 1;
    const packaging = fc > 1 ? (m.unidad_compra || 'Paquete') : 'Unitario';
    const stockText = formatearStock(m);
    const row = document.createElement('div');
    row.className = 'table-row';
    row.innerHTML = `
      <div data-label="Material">
        <div class="material-info">
          <div class="material-img"><i class="fas fa-box"></i></div>
          <div>
            <div class="material-name">${escapeHtml(m.name)}</div>
            <div class="material-code">Código: ${escapeHtml(m.code || '—')}</div>
          </div>
        </div>
      </div>
      <div data-label="Stock">${stockText}</div>
      <div data-label="Empaque"><span class="packaging-badge">${escapeHtml(packaging)}${fc > 1 ? ' (1 ' + escapeHtml(m.unidad_compra || 'Paquete') + ' = ' + fc + ' ' + escapeHtml(m.unidad_consumo || 'Unidad') + ')' : ''}</span></div>
      <div data-label="Proveedor">${escapeHtml(suppliersMap[m.supplier_id] || '—')}</div>
      <div data-label="Ubicación"><span class="location-name">${escapeHtml(loc.name)}</span></div>
      <div data-label="Zona"><span class="zone-name">${escapeHtml(loc.zone)}</span></div>
    `;
    fragment.appendChild(row);
  });
  container.appendChild(fragment);
}

function renderPaginacion(page, totalPages, total) {
  const info = document.getElementById('pageInfo');
  if (info) info.textContent = total > 0 ? `${total} materiales encontrados` : 'Sin resultados';
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

function formatearStock(m) {
  const stock = m.stock || 0;
  const fc = m.factor_conversion || 1;
  if (fc <= 1) return stock + ' ' + (m.unidad_consumo || 'Unidad') + '(s)';
  const paquetes = Math.floor(stock / fc);
  const sueltas = stock % fc;
  let texto = stock + ' ' + (m.unidad_consumo || 'Unidad') + '(s)';
  texto += ' (' + paquetes + ' ' + (m.unidad_compra || 'Paquete') + '(s)';
  if (sueltas > 0) texto += ' y ' + sueltas + ' ' + (m.unidad_consumo || 'Unidad') + '(s) sueltas';
  texto += ')';
  return texto;
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
