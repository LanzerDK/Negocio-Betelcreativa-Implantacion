// =============================================
// dashboard.js - Panel de Control con datos reales
// =============================================

let dashboardData = null;
let pendingTasks = [];
let completedTasks = [];

// ── CARGA INICIAL ────────────────────────────
// Carga todos los datos al dominio y configura actualización automática cada 30 segundos
document.addEventListener('DOMContentLoaded', () => {
    loadAll();
    setInterval(loadAll, 30000);
    initCharts();
    initTaskModal();
});

// Carga paralela de dashboard, tareas pendientes y completadas
async function loadAll()
{
    await Promise.all([
        fetchDashboard(),
        fetchTasks(),
        fetchCompletedTasks(),
    ]);
}

// ── DASHBOARD API ────────────────────────────
// Obtiene los datos del panel desde el API y renderiza todas las secciones
async function fetchDashboard()
{
    try {
        const json = await callApi(APP_URL + 'Public/api/dashboard.php?_=' + Date.now());
        if (json.success) {
            dashboardData = json.data;
            renderStats();
            renderAlerts();
            renderUpcomingEvents();
            renderSalesChart(dashboardData.monthlySales || []);
            renderEventTypeChart(dashboardData.eventTypeDistribution || []);
        }
    } catch (err) {
        console.error('Error al cargar dashboard:', err);
    }
}

// Actualiza las tarjetas de estadísticas con los valores del API
function renderStats()
{
    const s = dashboardData.stats;
    document.getElementById('statPendingAppts').textContent = s.pendingAppts;
    document.getElementById('statLowStock').textContent = s.lowStockCount + s.outOfStockCount;
    document.getElementById('statNewCustomers').textContent = s.totalCustomers;
    document.getElementById('statMonthlySales').textContent = (s.currentMonthSales || 0).toFixed(2) + ' Bs';
}

// Renderiza tarjetas de alertas de stock bajo, agotado y citas pendientes
function renderAlerts()
{
    const container = document.getElementById('alertsContainer');
    const alerts = [];

    // Alertas de stock bajo
    dashboardData.alerts.lowStock.forEach(m => {
        alerts.push({
            icon: 'fa-box-open',
            title: 'Stock Bajo',
            desc: `${m.name} — solo quedan ${m.stock} unidades`,
            cls: 'warning',
        });
    });

    // Alertas de material agotado
    dashboardData.alerts.outOfStock.forEach(m => {
        alerts.push({
            icon: 'fa-times-circle',
            title: 'Material Agotado',
            desc: `${m.name} — sin existencias`,
            cls: 'critical',
        });
    });

    // Alertas de citas pendientes próximas
    dashboardData.alerts.pending.forEach(a => {
        alerts.push({
            icon: 'fa-calendar-exclamation',
            title: 'Cita Pendiente',
            desc: `${a.customer} — ${formatTime(a.date)} ${a.startTime} ${a.eventType ? '- ' + a.eventType : ''}`,
            cls: 'info',
        });
    });

    // Mostrar mensaje de "todo en orden" si no hay alertas
    if (alerts.length === 0) {
        container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--success);font-weight:600;"><i class="fas fa-check-circle"></i> Todo en orden, no hay alertas</div>';
        return;
    }

    // Generar HTML de las tarjetas de alerta
    container.innerHTML = alerts.map(a => `
        <div class="alert-card ${a.cls}">
            <i class="fas ${a.icon}"></i>
            <div class="alert-text">
                <strong>${a.title}</strong>
                <p>${a.desc}</p>
            </div>
        </div>
    `).join('');
}

// Renderiza la lista de eventos próximos del calendario
function renderUpcomingEvents()
{
    const container = document.getElementById('upcomingEventsContainer');
    const events = dashboardData.upcomingEvents;

    if (!events || events.length === 0) {
        container.innerHTML = '<div class="event-placeholder">No hay eventos próximos</div>';
        return;
    }

    container.innerHTML = events.map((e, i) => `
        <div class="event-item">
            <h3><i class="fas fa-star"></i> C.I. ${e.idNumber || '—'} — ${e.customer}</h3>
            <p><i class="far fa-calendar-alt"></i> ${formatTime(e.date)}</p>
            <p><i class="fas fa-tag"></i> ${e.eventType || 'Evento'} ${e.location ? '— ' + e.location : ''}</p>
        </div>
        ${i < events.length - 1 ? '<hr>' : ''}
    `).join('');
}

// Convierte una cadena de fecha ISO a formato legible en español (dd Mes hh:mm)
function formatTime(dateStr)
{
    if (!dateStr) return '';
    const d = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateStr;
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const day = d.getDate();
    const month = months[d.getMonth()];
    const hours = String(d.getHours()).padStart(2, '0');
    const mins = String(d.getMinutes()).padStart(2, '0');
    return `${day} ${month} ${hours}:${mins}`;
}

// ── CHARTS ──────────────────────────────────
// Paleta de colores para los tipos de eventos
const EVENT_COLORS = ['#002266','#0A369D','#D4AF37','#4A90E2','#F7E493','#28A745','#DC3545','#17A2B8','#6C757D'];
let eventTypeChart = null;
let salesChart = null;

// Inicializa los gráficos de Chart.js con configuración por defecto
function initCharts()
{
    // Gráfico de línea para ventas mensuales
    const salesEl = document.getElementById('salesChart');
    if (salesEl) {
        salesChart = new Chart(salesEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Ventas (Bs)',
                    data: [],
                    backgroundColor: 'rgba(10, 54, 157, 0.1)',
                    borderColor: '#0A369D',
                    borderWidth: 3,
                    pointBorderColor: '#0A369D',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Gráfico de dona para distribución de tipos de evento
    const eventEl = document.getElementById('eventTypeChart');
    if (eventEl) {
        eventTypeChart = new Chart(eventEl.getContext('2d'), {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderWidth: 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#222', font: { size: 14, weight: '600' } } }
                },
                cutout: '60%',
            }
        });
    }
}

// Formatea una cadena de mes (YYYY-MM) a etiqueta abreviada (Ene '26)
function formatMonthLabel(m)
{
    const months = { '01':'Ene','02':'Feb','03':'Mar','04':'Abr','05':'May','06':'Jun','07':'Jul','08':'Ago','09':'Sep','10':'Oct','11':'Nov','12':'Dic' };
    return months[m.slice(5,7)] + " '" + m.slice(2,4);
}

// Actualiza el gráfico de línea con los datos de ventas mensuales
function renderSalesChart(monthly)
{
    if (!salesChart) return;
    const labels = monthly.map(m => formatMonthLabel(m.month));
    const data = monthly.map(m => parseFloat(m.total_bs));
    salesChart.data.labels = labels;
    salesChart.data.datasets[0].data = data;
    salesChart.update();
}

// Actualiza el gráfico de dona con la distribución de tipos de evento
function renderEventTypeChart(distribution)
{
    if (!eventTypeChart) return;
    const labels = distribution.map(d => d.name);
    const data = distribution.map(d => parseInt(d.count));
    const colors = distribution.map((_, i) => EVENT_COLORS[i % EVENT_COLORS.length]);
    eventTypeChart.data.labels = labels;
    eventTypeChart.data.datasets[0].data = data;
    eventTypeChart.data.datasets[0].backgroundColor = colors;
    eventTypeChart.update();
}

// ── TASKS API ────────────────────────────────
// Obtiene las tareas pendientes desde el API
async function fetchTasks()
{
    try {
        const json = await callApi(APP_URL + 'Public/api/tasks.php?_=' + Date.now());
        if (json.success) {
            pendingTasks = json.data;
            renderPendingTasks();
        }
    } catch (err) {
        console.error('Error al cargar tareas:', err);
    }
}

// Obtiene las tareas completadas desde el API
async function fetchCompletedTasks()
{
    try {
        const json = await callApi(APP_URL + 'Public/api/tasks.php?completed=1&_=' + Date.now());
        if (json.success) {
            completedTasks = json.data;
            renderCompletedTasks();
        }
    } catch (err) {
        console.error('Error al cargar completadas:', err);
    }
}

// Renderiza la lista de tareas pendientes con botón de completar
function renderPendingTasks()
{
    const list = document.getElementById('pendingTaskList');
    if (pendingTasks.length === 0) {
        list.innerHTML = '<li class="task-placeholder">No hay tareas pendientes</li>';
        return;
    }
    list.innerHTML = pendingTasks.map(t => `
        <li class="task-item" data-id="${t.id}">
            <div class="task-check" data-id="${t.id}"><i class="fas fa-check"></i></div>
            <div class="task-text">${escapeHtml(t.title)}</div>
            <span class="task-priority priority-${t.priority}">${priorityLabel(t.priority)}</span>
        </li>
    `).join('');

    // Asociar evento click a cada checkbox para marcar tarea como completada
    list.querySelectorAll('.task-check').forEach(el => {
        el.addEventListener('click', () => completeTask(parseInt(el.dataset.id)));
    });
}

// Renderiza la lista de tareas ya completadas (solo visualización)
function renderCompletedTasks()
{
    const list = document.getElementById('completedTaskList');
    if (completedTasks.length === 0) {
        list.innerHTML = '<li class="task-placeholder">No hay tareas completadas</li>';
        return;
    }
    list.innerHTML = completedTasks.map(t => `
        <li class="task-item completed">
            <div class="task-check"><i class="fas fa-check"></i></div>
            <div class="task-text">${escapeHtml(t.title)}</div>
            <span class="task-priority priority-${t.priority}">${priorityLabel(t.priority)}</span>
        </li>
    `).join('');
    syncCardHeight();
}

// Convierte el identificador de prioridad a etiqueta en español
function priorityLabel(p)
{
    return { high: 'Alta', medium: 'Media', low: 'Baja' }[p] || p;
}



// Marca una tarea como completada vía PUT al API y recarga ambas listas
async function completeTask(id)
{
    try {
        const json = await callApi(APP_URL + 'Public/api/tasks.php?id=' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN,
            },
            body: JSON.stringify({ status: 'completed' }),
        });
        if (json.success) {
            await Promise.all([fetchTasks(), fetchCompletedTasks()]);
        } else {
            toast('Error: ' + json.message, 'error');
        }
    } catch (err) {
        console.error('Error al completar tarea:', err);
    }
}

// ── TASK MODAL ───────────────────────────────
// Inicializa el modal de creación de tareas con sus eventos
function initTaskModal()
{
    const modal = document.getElementById('taskModal');
    const openBtn = document.getElementById('addTaskBtn');
    const closeBtn = document.getElementById('closeTaskModal');
    const cancelBtn = document.getElementById('cancelTaskBtn');
    const saveBtn = document.getElementById('saveTaskBtn');
    const titleInput = document.getElementById('taskTitle');
    const priorityInput = document.getElementById('taskPriority');

    // Abrir modal con campos limpios y foco en título
    function open() { modal.classList.add('show'); titleInput.value = ''; priorityInput.value = 'medium'; titleInput.focus(); }
    function close() { modal.classList.remove('show'); }

    openBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    cancelBtn?.addEventListener('click', close);
    // Cerrar modal al hacer clic fuera del contenido
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });

    // Guardar nueva tarea via POST al API
    saveBtn?.addEventListener('click', async () => {
        const title = titleInput.value.trim();
        if (!title) { toast('Ingresa una descripción para la tarea.', 'warning'); return; }
        try {
            const json = await callApi(APP_URL + 'Public/api/tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                },
                body: JSON.stringify({ title, priority: priorityInput.value }),
            });
            if (json.success) {
                close();
                await Promise.all([fetchTasks(), fetchCompletedTasks()]);
            } else {
                toast('Error: ' + json.message, 'error');
            }
        } catch (err) {
            console.error('Error al crear tarea:', err);
        }
    });
}

// ── CARD FLIP ────────────────────────────────
// Efecto de volteo de tarjeta para alternar entre tareas pendientes y completadas
document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('tasksCard');
    const flipBtn = document.getElementById('toggleHistoryBtn');
    const backBtn = document.getElementById('toggleBackBtn');

    // Voltear para mostrar historial de tareas completadas
    flipBtn?.addEventListener('click', () => {
        card.classList.add('flipped');
        fetchCompletedTasks();
    });

    // Voltear de vuelta para mostrar tareas pendientes
    backBtn?.addEventListener('click', () => {
        card.classList.remove('flipped');
        fetchTasks();
    });
});
