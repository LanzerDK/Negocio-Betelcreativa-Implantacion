// =============================================
// dashboard.js - Panel de Control con datos reales
// =============================================

let dashboardData = null;
let pendingTasks = [];
let completedTasks = [];

// ── CARGA INICIAL ────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadAll();
    setInterval(loadAll, 30000);
    initCharts();
    initTaskModal();
});

async function loadAll()
{
    await Promise.all([
        fetchDashboard(),
        fetchTasks(),
        fetchCompletedTasks(),
    ]);
}

// ── DASHBOARD API ────────────────────────────
async function fetchDashboard()
{
    try {
        const json = await callApi(APP_URL + 'Public/api/dashboard.php?_=' + Date.now());
        if (json.success) {
            dashboardData = json.data;
            renderStats();
            renderAlerts();
            renderUpcomingEvents();
            renderEventTypeChart(dashboardData.eventTypeDistribution || []);
        }
    } catch (err) {
        console.error('Error al cargar dashboard:', err);
    }
}

function renderStats()
{
    const s = dashboardData.stats;
    document.getElementById('statPendingAppts').textContent = s.pendingAppts;
    document.getElementById('statLowStock').textContent = s.lowStockCount + s.outOfStockCount;
    document.getElementById('statNewCustomers').textContent = s.totalCustomers;
}

function renderAlerts()
{
    const container = document.getElementById('alertsContainer');
    const alerts = [];

    // Low stock alerts
    dashboardData.alerts.lowStock.forEach(m => {
        alerts.push({
            icon: 'fa-box-open',
            title: 'Stock Bajo',
            desc: `${m.name} — solo quedan ${m.stock} unidades`,
            cls: 'warning',
        });
    });

    // Out of stock alerts
    dashboardData.alerts.outOfStock.forEach(m => {
        alerts.push({
            icon: 'fa-times-circle',
            title: 'Material Agotado',
            desc: `${m.name} — sin existencias`,
            cls: 'critical',
        });
    });

    // Pending appointment alerts
    dashboardData.alerts.pending.forEach(a => {
        alerts.push({
            icon: 'fa-calendar-exclamation',
            title: 'Cita Pendiente',
            desc: `${a.customer} — ${formatTime(a.date)} ${a.startTime} ${a.eventType ? '- ' + a.eventType : ''}`,
            cls: 'info',
        });
    });

    if (alerts.length === 0) {
        container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--success);font-weight:600;"><i class="fas fa-check-circle"></i> Todo en orden, no hay alertas</div>';
        return;
    }

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
            <h3><i class="fas fa-star"></i> ${e.customer}</h3>
            <p><i class="far fa-calendar-alt"></i> ${formatTime(e.date)} — ${e.startTime}</p>
            <p><i class="fas fa-tag"></i> ${e.eventType || 'Evento'} ${e.location ? '— ' + e.location : ''}</p>
        </div>
        ${i < events.length - 1 ? '<hr>' : ''}
    `).join('');
}

function formatTime(dateStr)
{
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return `${d.getDate()} ${months[d.getMonth()]}`;
}

// ── CHARTS ──────────────────────────────────
const EVENT_COLORS = ['#002266','#0A369D','#D4AF37','#4A90E2','#F7E493','#28A745','#DC3545','#17A2B8','#6C757D'];
let eventTypeChart = null;

function initCharts()
{
    const salesEl = document.getElementById('salesChart');
    if (salesEl) {
        new Chart(salesEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                datasets: [{
                    label: 'Ventas Mensuales ($)',
                    data: [12000, 19000, 15000, 18000, 22000, 24580, 21000, 23000, 24500, 26000, 28000, 30000],
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

    list.querySelectorAll('.task-check').forEach(el => {
        el.addEventListener('click', () => completeTask(parseInt(el.dataset.id)));
    });
}

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

function priorityLabel(p)
{
    return { high: 'Alta', medium: 'Media', low: 'Baja' }[p] || p;
}

function escapeHtml(str)
{
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

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
function initTaskModal()
{
    const modal = document.getElementById('taskModal');
    const openBtn = document.getElementById('addTaskBtn');
    const closeBtn = document.getElementById('closeTaskModal');
    const cancelBtn = document.getElementById('cancelTaskBtn');
    const saveBtn = document.getElementById('saveTaskBtn');
    const titleInput = document.getElementById('taskTitle');
    const priorityInput = document.getElementById('taskPriority');

    function open() { modal.classList.add('show'); titleInput.value = ''; priorityInput.value = 'medium'; titleInput.focus(); }
    function close() { modal.classList.remove('show'); }

    openBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    cancelBtn?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });

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
document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('tasksCard');
    const flipBtn = document.getElementById('toggleHistoryBtn');
    const backBtn = document.getElementById('toggleBackBtn');

    flipBtn?.addEventListener('click', () => {
        card.classList.add('flipped');
        fetchCompletedTasks();
    });

    backBtn?.addEventListener('click', () => {
        card.classList.remove('flipped');
        fetchTasks();
    });
});
