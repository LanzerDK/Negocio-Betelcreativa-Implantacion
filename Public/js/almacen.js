let allMaterials = [];
let allCategories = [];
let allLocations = [];
let allZones = [];

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('btn-loading', loading);
}

document.addEventListener('DOMContentLoaded', function () {
  cargarDatosIniciales();
  document.getElementById('addShelfBtn')?.addEventListener('click', function () {
    document.getElementById('shelfZoneType').value = 'new';
    toggleShelfZoneType();
    llenarSelectZona();
    document.getElementById('shelfForm').reset();
    Modal.open('shelfModal');
  });
  document.getElementById('guardarEstanteBtn')?.addEventListener('click', guardarEstante);
  document.getElementById('shelfZoneType')?.addEventListener('change', toggleShelfZoneType);
  document.getElementById('confirmDeleteShelfModalBtn')?.addEventListener('click', eliminarEstante);

});

async function cargarDatosIniciales() {
  await recargarDatos();
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
  llenarSelectZona();
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
    const data = await callApi(APP_URL + 'Public/api/locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
      body: JSON.stringify({ name, description: zone })
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

function confirmarEliminarEstante(locationId, name) {
  document.getElementById('deleteShelfId').value = locationId;
  document.getElementById('deleteShelfName').textContent = name;
  Modal.open('deleteShelfModal');
}

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

async function recargarDatos() {
  try {
    const [mat, cat, loc] = await Promise.all([
      fetch(APP_URL + 'Public/api/materials.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/categories.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; })),
      fetch(APP_URL + 'Public/api/locations.php').then(r => r.json().then(d => { if (!r.ok) throw new Error(d.message); return d; }))
    ]);
    if (mat.success) allMaterials = mat.data;
    if (cat.success) allCategories = cat.data;
    if (loc.success) {
      allLocations = loc.data;
      const zoneSet = new Set();
      loc.data.forEach(l => { if (l.description) zoneSet.add(l.description); });
      allZones = Array.from(zoneSet).sort();
    }
    renderLayout();
    llenarSelectores();
  } catch (err) {
    console.error('Error al recargar:', err.message);
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

function marcarError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('is-invalid');
}

function formatearStock(m) {
  const stock = m.stock || 0;
  const fc = m.factor_conversion || 1;
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


