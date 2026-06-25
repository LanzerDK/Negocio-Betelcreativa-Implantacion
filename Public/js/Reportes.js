let charts = {};
// @debug — panel visible en la página para diagnóstico
(function () {
    const box = document.createElement('div');
    box.id = 'reportes-debug';
    box.style.cssText = 'position:fixed;bottom:0;right:0;width:420px;max-height:300px;overflow:auto;background:#111;color:#0f0;font:12px monospace;padding:8px;z-index:9999;border-radius:6px 0 0 0;opacity:0.9';
    box.innerHTML = '<div style="font-weight:bold;margin-bottom:4px">[Reportes Debug]</div>';
    document.body.appendChild(box);
    window.__debugLog = function (...args) {
        const line = document.createElement('div');
        line.textContent = args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ');
        box.appendChild(line);
        box.scrollTop = box.scrollHeight;
        console.log(...args);
    };
})();

document.addEventListener('DOMContentLoaded', () => {
    __debugLog('DOMContentLoaded iniciado');
    initReportNavigation();
    initChartInstances();
    initGenerateButtons();
    initExportButtons();

    // Set default dates and load initial data
    setDefaultDates();
    setTimeout(() => {
        __debugLog('setTimeout disparado — cargando reportes');
        loadReport('inventory');
        loadReport('movements');
        loadReport('income');
        loadReport('purchases');
    }, 100);
});

function setDefaultDates() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    const fmt = d => d.toISOString().split('T')[0];

    document.querySelectorAll('.movements-from, .income-from, .purchases-from').forEach(el => {
        el.value = fmt(thirtyDaysAgo);
    });
    document.querySelectorAll('.movements-to, .income-to, .purchases-to').forEach(el => {
        el.value = fmt(today);
    });
}

function initReportNavigation() {
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

    document.querySelectorAll('.close-report').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.report-content').classList.remove('active');
        });
    });
}

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
                if (chartType === 'doughnut') {
                    base.backgroundColor = CHART_COLORS.palette.slice(0, ds.data.length);
                    base.borderWidth = 0;
                } else if (chartType === 'bar') {
                    base.backgroundColor = i === 0 ? 'rgba(76, 175, 80, 0.7)' : 'rgba(244, 67, 54, 0.7)';
                    base.borderColor = i === 0 ? '#4caf50' : '#f44336';
                    base.borderWidth = 1;
                } else {
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

function initGenerateButtons() {
    document.querySelectorAll('.generate-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const report = this.dataset.report;
            loadReport(report);
        });
    });
}

async function loadReport(report) {
    const params = getFilterParams(report);
    const url = APP_URL + 'api/reports.php?action=' + report + '&' + new URLSearchParams(params);
    __debugLog('loadReport(' + report + ') →', url);

    try {
        const res = await fetch(url);
        const json = await res.json();
        __debugLog('loadReport(' + report + ') respuesta:', 'success=' + json.success, 'rows=' + (json.data?.table?.rows?.length ?? 'N/A'));
        if (json.success) {
            renderReport(report, json.data);
        } else {
            toast(json.message || 'Error al cargar reporte', 'error');
            __debugLog('loadReport(' + report + ') ERROR:', json.message);
        }
    } catch (err) {
        toast('Error de conexión al cargar reporte', 'error');
        __debugLog('loadReport(' + report + ') EXCEPCIÓN:', err.message);
        console.error(err);
    }
}

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

function renderReport(report, data) {
    if (!data) {
        __debugLog('renderReport(' + report + ') — data es null/undefined');
        return;
    }
    __debugLog('renderReport(' + report + ') — chart=' + !!data.chart + ' stats=' + (data.stats?.length ?? 0) + ' rows=' + (data.table?.rows?.length ?? 0));

    try { updateChart(report, data.chart); } catch (e) { __debugLog('Error en chart:', report, e.message); console.error(e); }
    try { updateStats(report, data.stats); } catch (e) { __debugLog('Error en stats:', report, e.message); console.error(e); }
    try { updateTable(report, data.table); } catch (e) { __debugLog('Error en table:', report, e.message); console.error(e); }

    if (data.note) {
        const noteEl = document.querySelector('.income-note');
        if (noteEl) noteEl.textContent = data.note;
    }

    if (data.chart?.chartUrl) {
        const reportEl = document.getElementById(report + 'Report');
        if (reportEl) reportEl.dataset.chartUrl = data.chart.chartUrl;
    }
}

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
                valEl.textContent = stat.currency ? '$' + formatNumber(val) : formatNumber(val);
            }
            if (labEl) labEl.textContent = stat.label;
        }
    });
}

function updateTable(report, tableData) {
    __debugLog('updateTable(' + report + ') llamado');
    if (!tableData || !tableData.headers || !tableData.rows) {
        __debugLog('updateTable: sin datos para', report, '→ tableData=', tableData);
        return;
    }
    const selector = '.' + report + '-table-body';
    const tbody = document.querySelector(selector);
    __debugLog('updateTable: selector=' + selector + ' → encontrado=' + !!tbody);
    if (!tbody) {
        return;
    }

    if (tableData.rows.length === 0) {
        __debugLog('updateTable: 0 filas — mostrando "No hay datos"');
        tbody.innerHTML = '<tr><td colspan="99" style="text-align:center;padding:20px;color:var(--gray);">No hay datos para este período</td></tr>';
        return;
    }

    const html = tableData.rows.map(row => {
        const cells = row.map(cell => {
            if (typeof cell === 'number') return '<td>' + formatNumber(cell) + '</td>';
            return '<td>' + escapeHtml(String(cell)) + '</td>';
        }).join('');
        return '<tr>' + cells + '</tr>';
    }).join('');
    __debugLog('updateTable: insertando ' + tableData.rows.length + ' filas, HTML length=' + html.length);
    tbody.innerHTML = html;
    __debugLog('updateTable: innerHTML asignado correctamente');
}

function formatNumber(n) {
    if (n === null || n === undefined) return '0';
    if (typeof n === 'number' && !Number.isInteger(n)) {
        return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return Number(n).toLocaleString('es-VE');
}

function escapeHtml(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

// ── PDF export ──────────────────────────────────

function initExportButtons() {
    document.querySelectorAll('.export-pdf').forEach(btn => {
        btn.addEventListener('click', function () {
            let reportId = this.dataset.report;
            if (!reportId.endsWith('Report')) reportId = reportId + 'Report';
            exportToPDF(reportId);
        });
    });

    document.querySelectorAll('.report-btn.outline').forEach(btn => {
        btn.addEventListener('click', function () {
            let reportId = this.dataset.report + 'Report';
            exportToPDF(reportId);
        });
    });

    document.getElementById('exportAllBtn')?.addEventListener('click', exportAllReports);
}

async function exportToPDF(reportId) {
    const element = document.getElementById(reportId);
    if (!element) { toast('Reporte no encontrado.', 'error'); return; }

    const wasActive = element.classList.contains('active');
    if (!wasActive) element.classList.add('active');

    try {
        const chartUrl = element.dataset.chartUrl;

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        if (chartUrl) {
            // Try QuickChart first for high-quality image
            try {
                const img = await loadImage(chartUrl);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (img.height * pdfWidth) / img.width;
                pdf.addImage(img, 'PNG', 0, 10, pdfWidth, Math.min(pdfHeight, 250));
            } catch (_) {
                // QuickChart failed, fallback to html2canvas
                await fallbackCanvas(pdf, element);
            }
        } else {
            await fallbackCanvas(pdf, element);
        }

        pdf.save(`reporte_${reportId}.pdf`);
    } finally {
        if (!wasActive) element.classList.remove('active');
    }
}

function loadImage(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = url;
    });
}

function fallbackCanvas(pdf, element) {
    return new Promise((resolve) => {
        setTimeout(() => {
            html2canvas(element, { scale: 2, useCORS: true, logging: false })
                .then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    resolve();
                })
                .catch(() => resolve());
        }, 300);
    });
}

async function exportAllReports() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'mm', 'a4');
    const reportIds = ['inventoryReport', 'movementsReport', 'incomeReport', 'purchasesReport'];

    for (let i = 0; i < reportIds.length; i++) {
        const element = document.getElementById(reportIds[i]);
        if (!element) continue;

        const wasActive = element.classList.contains('active');
        if (!wasActive) element.classList.add('active');

        const report = reportIds[i].replace('Report', '');
        await loadReport(report);
        await new Promise(r => setTimeout(r, 500));

        try {
            const chartUrl = element.dataset.chartUrl;
            if (chartUrl) {
                try {
                    const img = await loadImage(chartUrl);
                    if (i > 0) pdf.addPage();
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (img.height * pdfWidth) / img.width;
                    pdf.addImage(img, 'PNG', 0, 10, pdfWidth, Math.min(pdfHeight, 250));
                } catch (_) {
                    await fallbackCanvasPage(pdf, element, i);
                }
            } else {
                await fallbackCanvasPage(pdf, element, i);
            }
        } finally {
            if (!wasActive) element.classList.remove('active');
        }
    }

    pdf.save('todos_los_reportes.pdf');
}

function fallbackCanvasPage(pdf, element, pageIndex) {
    return new Promise((resolve) => {
        setTimeout(() => {
            html2canvas(element, { scale: 2, useCORS: true, logging: false })
                .then(canvas => {
                    if (pageIndex > 0) pdf.addPage();
                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    resolve();
                })
                .catch(() => resolve());
        }, 300);
    });
}
