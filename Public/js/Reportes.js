// Almacén de instancias de Chart.js por ID de canvas
let charts = {};

// Inicialización al cargar el DOM: configura navegación, gráficos y botones
document.addEventListener('DOMContentLoaded', () => {
    // Navegación entre reportes (tabs del menú lateral)
    initReportNavigation();
    // Crear instancias vacías de los 4 gráficos
    initChartInstances();
    // Vincular botones "Generar" a sus respectivos reportes
    initGenerateButtons();


    // Establecer fechas por defecto y cargar todos los reportes iniciales
    setDefaultDates();
    setTimeout(() => {
        loadReport('inventory');
        loadReport('movements');
        loadReport('income');
        loadReport('purchases');
    }, 100);
});

// Establece fechas por defecto: últimos 30 días hasta hoy
function setDefaultDates() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    const fmt = d => d.toISOString().split('T')[0];

    // Aplica fechas a los campos de filtro de cada reporte
    document.querySelectorAll('.movements-from, .income-from, .purchases-from').forEach(el => {
        el.value = fmt(thirtyDaysAgo);
    });
    document.querySelectorAll('.movements-to, .income-to, .purchases-to').forEach(el => {
        el.value = fmt(today);
    });
}

// Configura la navegación entre secciones de reportes
function initReportNavigation() {
    // Click en botón de reporte: activa la sección correspondiente y carga datos
    document.querySelectorAll('.report-btn.primary').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.report-content').forEach(r => r.classList.remove('active'));
            const report = this.dataset.report;
            const reportId = report + 'Report';
            const el = document.getElementById(reportId);
            if (el) {
                el.classList.add('active');
                el.scrollIntoView({ behavior: 'smooth' });
            }
            loadReport(report);
        });
    });

    // Click en botón cerrar reporte: oculta la sección
    document.querySelectorAll('.close-report').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.report-content').classList.remove('active');
        });
    });
}

// Paleta de colores para los gráficos Chart.js
const CHART_COLORS = {
    primary: '#002266',
    secondary: '#0A369D',
    accent: '#D4AF37',
    green: '#4caf50',
    red: '#f44336',
    orange: '#ff9800',
    blue: '#2196f3',
    palette: ['#002266','#0A369D','#D4AF37','#4A90E2','#F7E493','#4caf50','#ff9800','#f44336'],
};

// Convierte la configuración de gráfico de la API a formato Chart.js
function createChartConfig(apiChart) {
    if (!apiChart) return null;
    const chartType = apiChart.type;
    return {
        type: chartType,
        data: {
            labels: apiChart.data.labels,
            datasets: apiChart.data.datasets.map((ds, i) => {
                const base = {
                    label: ds.label,
                    data: ds.data,
                };
                // Estilos según tipo de gráfico
                if (chartType === 'doughnut') {
                    base.backgroundColor = CHART_COLORS.palette.slice(0, ds.data.length);
                    base.borderWidth = 0;
                } else if (chartType === 'bar') {
                    // Verde para primera serie, rojo para segunda
                    base.backgroundColor = i === 0 ? 'rgba(76, 175, 80, 0.7)' : 'rgba(244, 67, 54, 0.7)';
                    base.borderColor = i === 0 ? '#4caf50' : '#f44336';
                    base.borderWidth = 1;
                } else {
                    // Estilo línea con relleno semitransparente
                    base.backgroundColor = 'rgba(10, 54, 157, 0.1)';
                    base.borderColor = '#0A369D';
                    base.borderWidth = 3;
                    base.tension = 0.3;
                    base.fill = true;
                    base.pointBorderColor = '#0A369D';
                    base.pointBorderWidth = 2;
                }
                return base;
            }),
        },
    };
}

// Crea las instancias vacías de los 4 gráficos (doughnut, bar, line, line)
function initChartInstances() {
    const configs = [
        { id: 'inventoryChart',   type: 'doughnut' },
        { id: 'movementsChart',   type: 'bar' },
        { id: 'incomeChart',      type: 'line' },
        { id: 'purchasesChart',   type: 'line' },
    ];

    configs.forEach(({ id, type }) => {
        const el = document.getElementById(id);
        if (!el) return;
        charts[id] = new Chart(el.getContext('2d'), {
            type,
            data: { labels: [], datasets: [] },
            options: getChartOptions(type),
        });
    });
}

// Retorna las opciones de configuración de Chart.js según el tipo de gráfico
function getChartOptions(type) {
    const base = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
    };
    if (type === 'bar') {
        base.scales = { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } };
    }
    if (type === 'line') {
        base.scales = { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } };
    }
    if (type === 'doughnut') {
        base.cutout = '60%';
        base.plugins.legend.position = 'right';
    }
    return base;
}

// Vincula los botones "Generar" a la función loadReport
function initGenerateButtons() {
    document.querySelectorAll('.generate-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const report = this.dataset.report;
            loadReport(report);
        });
    });
}

// Carga un reporte específico desde la API y actualiza la vista
async function loadReport(report) {
    const params = getFilterParams(report);
    const url = APP_URL + 'Public/api/reports.php?action=' + report + '&' + new URLSearchParams(params);
    try {
        const json = await callApi(url);
        if (json.success) {
            renderReport(report, json.data);
        } else {
            toast(json.message || 'Error al cargar reporte', 'error');
        }
    } catch (err) {
        toast('Error de conexión al cargar reporte', 'error');
        console.error(err);
    }
}

// Extrae los parámetros de filtro del DOM según el tipo de reporte
function getFilterParams(report) {
    switch (report) {
        case 'inventory': {
            const cat = document.querySelector('.inventory-category')?.value || '';
            const st = document.querySelector('.inventory-stock-status')?.value || '';
            const ord = document.querySelector('.inventory-order')?.value || 'name';
            const p = {};
            if (cat) p.category = cat;
            if (st) p.stock_status = st;
            if (ord) p.order_by = ord;
            return p;
        }
        case 'movements': {
            const from = document.querySelector('.movements-from')?.value || '';
            const to = document.querySelector('.movements-to')?.value || '';
            const type = document.querySelector('.movements-type')?.value || '';
            const p = { from, to };
            if (type) p.type = type;
            return p;
        }
        case 'income': {
            const from = document.querySelector('.income-from')?.value || '';
            const to = document.querySelector('.income-to')?.value || '';
            return { from, to };
        }
        case 'purchases': {
            const from = document.querySelector('.purchases-from')?.value || '';
            const to = document.querySelector('.purchases-to')?.value || '';
            return { from, to };
        }
        default:
            return {};
    }
}

// Renderiza un reporte: actualiza gráfico, estadísticas, tabla y nota
function renderReport(report, data) {
    if (!data) return;

    try { updateChart(report, data.chart); } catch (e) { console.error(e); }
    try { updateStats(report, data.stats); } catch (e) { console.error(e); }
    try { updateTable(report, data.table); } catch (e) { console.error(e); }

    // Mostrar nota informativa si el reporte la incluye (ej: ingresos)
    if (data.note) {
        const noteEl = document.querySelector('.income-note');
        if (noteEl) noteEl.textContent = data.note;
    }

    // Guardar URL del gráfico en el dataset para exportación PDF
    if (data.chart?.chartUrl) {
        const reportEl = document.getElementById(report + 'Report');
        if (reportEl) reportEl.dataset.chartUrl = data.chart.chartUrl;
    }
}

// Actualiza un gráfico existente con nuevos datos de la API
function updateChart(report, chartData) {
    const canvasId = report + 'Chart';
    const existing = charts[canvasId];
    if (!existing || !chartData) return;

    const cfg = createChartConfig(chartData);
    if (!cfg) return;

    existing.data.labels = cfg.data.labels;
    existing.data.datasets = cfg.data.datasets;
    existing.update();
}

// Actualiza las tarjetas de estadísticas con los valores del reporte
function updateStats(report, stats) {
    if (!stats) return;
    const container = document.querySelector('.' + report + '-stats');
    if (!container) return;

    const cards = container.querySelectorAll('.stat-card');
    stats.forEach((stat, i) => {
        if (cards[i]) {
            const valEl = cards[i].querySelector('.stat-value');
            const labEl = cards[i].querySelector('.stat-label');
            if (valEl) {
                const val = stat.value ?? 0;
                // Formato con prefijo $ si la stat es en divisa
                valEl.textContent = stat.currency ? '$' + formatNumber(val) : formatNumber(val);
            }
            if (labEl) labEl.textContent = stat.label;
        }
    });
}

// Renderiza la tabla de datos del reporte con formato numérico localizado
function updateTable(report, tableData) {
    if (!tableData || !tableData.headers || !tableData.rows) return;

    const selector = '.' + report + '-table-body';
    const tbody = document.querySelector(selector);
    if (!tbody) return;

    // Estado vacío: sin datos para el período seleccionado
    if (tableData.rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="99" style="text-align:center;padding:20px;color:var(--gray);">No hay datos para este período</td></tr>';
        return;
    }

    // Renderizar cada fila con formato numérico para celdas numéricas
    const html = tableData.rows.map(row => {
        const cells = row.map(cell => {
            if (typeof cell === 'number') return '<td>' + formatNumber(cell) + '</td>';
            return '<td>' + escapeHtml(String(cell)) + '</td>';
        }).join('');
        return '<tr>' + cells + '</tr>';
    }).join('');
    tbody.innerHTML = html;
}

// Formatea un número con localización venezolana (es-VE)
function formatNumber(n) {
    if (n === null || n === undefined) return '0';
    // Decimales para números flotantes
    if (typeof n === 'number' && !Number.isInteger(n)) {
        return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    // Si es string no numérico, retornar tal cual
    if (typeof n === 'string' && isNaN(Number(n))) return n;
    return Number(n).toLocaleString('es-VE');
}


// ── Exportación PDF (tabla solamente) ─────────────────────

// Delegación de eventos: detecta clic en botones de exportación PDF
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.export-pdf');
    if (!btn) return;
    const reportId = btn.dataset.report;
    exportToPDF(reportId);
});

// Extrae headers y filas de la tabla HTML y los agrega al PDF con jsPDF
function addTableToPdf(pdf, element, startY) {
    const table = element.querySelector('.report-table');
    if (!table) return;

    // Extraer encabezados de la tabla
    const headers = [...table.querySelectorAll('thead th')].map(th => th.textContent.trim());
    const rows = [];
    const tbody = table.querySelector('tbody');
    if (tbody) {
        // Extraer filas, excluyendo filas completamente vacías
        [...tbody.querySelectorAll('tr')].forEach(tr => {
            const cells = [...tr.querySelectorAll('td')].map(td => td.textContent.trim());
            if (cells.length > 0 && cells.some(c => c !== '')) {
                rows.push(cells);
            }
        });
    }
    if (rows.length === 0) return;

    // Generar tabla en el PDF con estilo corporativo (azul dorado)
    pdf.autoTable({
        head: [headers],
        body: rows,
        startY: startY,
        theme: 'grid',
        headStyles: {
            fillColor: [0, 34, 102],
            textColor: [212, 175, 55],
            fontStyle: 'bold',
            halign: 'center',
        },
        alternateRowStyles: {
            fillColor: [248, 249, 250],
        },
        styles: {
            fontSize: 8,
            cellPadding: 2,
            textColor: [33, 37, 41],
            lineColor: [212, 175, 55],
            lineWidth: 0.3,
        },
        columnStyles: { 0: { cellWidth: 'auto' } },
        margin: { left: 5, right: 5 },
    });
}

// Genera y descarga un PDF con el reporte completo (título + tabla)
async function exportToPDF(reportId) {
    const element = document.getElementById(reportId);
    if (!element) { toast('Reporte no encontrado.', 'error'); return; }

    const report = reportId.replace('Report', '');
    // Asegurar que el reporte esté visible para poder capturar su tabla
    const wasActive = element.classList.contains('active');
    if (!wasActive) element.classList.add('active');

    // Recargar datos del reporte antes de exportar
    await loadReport(report);
    await new Promise(r => setTimeout(r, 600));

    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();

        // Título centrado en el encabezado del PDF
        const title = element.querySelector('.report-name')?.textContent || report;
        pdf.setFontSize(16);
        pdf.text(title, pdfWidth / 2, 12, { align: 'center' });

        // Agregar tabla del reporte al PDF
        addTableToPdf(pdf, element, 20);

        // Descargar el archivo PDF generado
        pdf.save(`reporte_${reportId}.pdf`);
    } finally {
        // Restaurar visibilidad del reporte si estaba oculto
        if (!wasActive) element.classList.remove('active');
    }
}
