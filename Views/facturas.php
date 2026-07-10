<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Bet-El Creativa</title>

    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>Public/images/favicon.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/facturasStyle.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <h1 class="logo-icon"><i class="fas fa-file-invoice-dollar"></i></h1>
                <div class="app-info">
                    <h1>Facturación</h1>
                    <p>Administra las facturas y pagos de tus citas</p>
                </div>
            </div>
            <div class="user-container">
                <div class="imagenfoto">
                    <img src="<?php echo APP_URL; ?>Public/images/BetEl.png" alt="Bet-El Creativa Logo">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></h2>
                    <p><?php echo htmlspecialchars(roleLabel($_SESSION['user_role'] ?? null)); ?></p>
                </div>
                <div class="user-settings" id="userSettings">
                    <button class="settings-btn" id="settingsBtn">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="settings-dropdown" id="settingsDropdown">
                    <a href="<?php echo APP_URL; ?><?php echo in_array(($_SESSION['user_role'] ?? ''), ['super_admin', 'admin']) ? 'admin-settings' : 'cuenta'; ?>" class="dropdown-item">
                        <i class="fas fa-user"></i> Cuenta
                    </a>
                        <a href="<?php echo APP_URL; ?>logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <nav class="main-menu">
            <a href="<?php echo APP_URL; ?>dashboard" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>
            <div class="menu-item-wrapper">
                <a href="<?php echo APP_URL; ?>storage" class="menu-item">
                    <i class="fas fa-warehouse"></i>
                    <span>Almacén</span>
                </a>
                <button class="submenu-toggle" id="almacenSubmenuToggle" type="button">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="submenu-dropdown" id="almacenSubmenu">
                    <a href="<?php echo APP_URL; ?>storage-distribucion" class="submenu-item"><i class="fas fa-truck-loading"></i> Distribución</a>
                    <a href="<?php echo APP_URL; ?>storage-inventario" class="submenu-item"><i class="fas fa-clipboard-list"></i> Inventario</a>
                </div>
            </div>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item active">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <?php if (($_SESSION['user_role'] ?? '') === 'super_admin'): ?>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="main-content">
        
<div class="page-header">
    <h1><i class="fas fa-file-invoice-dollar"></i> Facturación</h1>
</div>

<div class="factura-layout">

    <!-- Sidebar -->
    <div class="factura-sidebar">
        <div class="sidebar-tabs">
            <button class="sidebar-tab active" data-tab="pendientes">
                <i class="fas fa-clock"></i>
                <span>Pendiente</span>
            </button>
            <button class="sidebar-tab" data-tab="abiertas">
                <i class="fas fa-folder-open"></i>
                <span>Abiertas</span>
            </button>
            <button class="sidebar-tab" data-tab="pagadas">
                <i class="fas fa-check-circle"></i>
                <span>Pagadas</span>
            </button>
            <button class="sidebar-tab" data-tab="canceladas">
                <i class="fas fa-ban"></i>
                <span>Canceladas</span>
            </button>
        </div>
        <div class="sidebar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="sidebarSearch" placeholder="Buscar factura...">
        </div>
        <div class="sidebar-list" id="sidebarList">
            <div class="sidebar-empty">Cargando facturas...</div>
        </div>
    </div>

    <!-- Detail Panel -->
    <div class="factura-detail" id="facturaDetail">

        <!-- Empty state -->
        <div class="detail-empty" id="detailEmpty">
            <i class="fas fa-file-invoice-dollar"></i>
            <h3>Selecciona una factura</h3>
            <p>Elige una factura de la lista para ver sus detalles.</p>
        </div>

        <!-- Detail content (hidden initially) -->
        <div id="detailContent" style="display:none;">

            <!-- Top Row -->
            <div class="detail-grid">

                <!-- Card 1: Detalles de Facturación y Cita -->
                <div class="factura-card" id="card1">
                    <div class="factura-card-header">
                        <i class="fas fa-calendar-alt"></i> Detalles de Facturación y Cita
                        <span id="facturaEstadoBadge" style="display:none;" class="estado-badge"></span>
                    </div>
                    <div class="factura-card-body">
                        <div class="info-row"><span class="info-label">Cliente:</span><span id="card1-cliente" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Cédula:</span><span id="card1-cedula" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Teléfono:</span><span id="card1-telefono" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Fecha:</span><span id="card1-fecha" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Ubicación:</span><span id="card1-ubicacion" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Tipo Evento:</span><span id="card1-evento" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Estado Cita:</span><span id="card1-estado-cita" class="info-value">—</span></div>
                        <div class="info-row atendido-row" id="card1-atendido-row" style="display:none;">
                            <span class="info-label">Atendido por:</span>
                            <span id="card1-atendido" class="info-value">—</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Detalle de Factura / Desglose -->
                <div class="factura-card" id="card2">
                    <div class="factura-card-header">
                        <i class="fas fa-receipt"></i> Detalle de Factura / Desglose
                    </div>
                    <div class="factura-card-body">
                        <table class="desglose-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th class="text-right">Cant.</th>
                                    <th class="text-right">P. Unit. (Bs)</th>
                                    <th class="text-right">Total (Bs)</th>
                                    <th class="text-right">IVA (16%)</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-desglose">
                                <tr><td colspan="7" class="text-center" style="color:var(--gray);padding:20px;">Seleccione una factura</td></tr>
                            </tbody>
                        </table>
                        <div class="factura-total-section">
                            <div class="total-row">
                                <span>Costo de Servicio:</span>
                                <span id="card2-costo-servicio">0.00 Bs</span>
                            </div>
                            <div class="total-row">
                                <span>I.V.A (16%):</span>
                                <span id="card2-iva">0.00 Bs</span>
                            </div>
                            <div class="total-row total-final">
                                <span><strong>TOTAL MATERIALES:</strong></span>
                                <span id="card2-total-valor"><strong>0.00 Bs</strong></span>
                            </div>
                        </div>
                        <div class="factura-actions" id="facturaActions">
                            <button type="button" class="btn btn-primary" id="btnGenerarFactura" style="flex:1;display:none;">
                                <i class="fas fa-file-invoice"></i> Generar Factura
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Card 3: Historial de Pagos -->
            <div class="factura-card" id="card3">
                <div class="factura-card-header">
                    <i class="fas fa-credit-card"></i> Historial de Pagos y Cuotas
                </div>
                <div class="factura-card-body">
                    <div class="pago-resumen">
                        <div class="pago-item">
                            <span class="pago-label">Total Factura</span>
                            <span class="pago-monto" id="card3-total-ves">0.00 Bs</span>
                        </div>
                        <div class="pago-item pendiente">
                            <span class="pago-label">Saldo Pendiente</span>
                            <span class="pago-monto" id="card3-pendiente-ves">0.00 Bs</span>
                        </div>
                    </div>

                    <div class="pago-progress-container" id="pagoProgressContainer">
                        <div class="pago-progress-bar">
                            <div class="pago-progress-fill" id="pagoProgressFill" style="width:0%;"></div>
                        </div>
                        <div class="pago-progress-labels">
                            <span id="pago-progress-pagado">Pagado: 0.00 Bs</span>
                            <span id="pago-progress-pendiente">Pendiente: 0.00 Bs</span>
                        </div>
                    </div>

                    <div class="pago-status" id="status-pago"></div>

                    <h4 style="margin:15px 0 8px;font-size:0.9rem;color:var(--gray);">Historial de Abonos</h4>
                    <table class="historial-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-right">Monto (Bs)</th>
                                <th>Método</th>
                                <th class="text-right">Tasa</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pagos">
                            <tr><td colspan="4" class="text-center" style="color:var(--gray);padding:15px;">Sin pagos registrados</td></tr>
                        </tbody>
                    </table>

                    <div class="factura-actions" id="pagoActions" style="margin-top:15px;display:none;">
                        <button type="button" class="btn btn-primary" id="btnRegistrarAbonoPagos" style="flex:1;">
                            <i class="fas fa-plus-circle"></i> Registrar Abono / Cuota
                        </button>
                        <button type="button" class="btn btn-outline" id="btnCerrarFactura" style="flex:1;display:none;">
                            <i class="fas fa-lock"></i> Cerrar Factura
                        </button>
                        <button type="button" class="btn btn-outline" id="btnAnularFactura" style="flex:1;display:none;">
                            <i class="fas fa-ban"></i> Anular
                        </button>
                    </div>
                    <div class="factura-actions" id="pagoActionsReadOnly" style="margin-top:15px;display:none;">
                        <button type="button" class="btn btn-outline" id="btnImprimirOriginal" style="flex:1;">
                            <i class="fas fa-print"></i> Imprimir Recibo
                        </button>
                        <button type="button" class="btn btn-outline" id="btnImprimirCopia" style="flex:1;">
                            <i class="fas fa-copy"></i> Imprimir Copia
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 4: Términos y Condiciones -->
            <div class="terms-section">
                <div class="factura-card-header">
                    <i class="fas fa-file-contract"></i> Términos y Condiciones (Vista Previa)
                </div>
                <div class="terms-body" id="termsBody">
                    <p class="terms-placeholder">Cargando términos...</p>
                </div>
            </div>

        </div>

        <!-- Cancelada Detail (hidden initially) -->
        <div id="detailCancelada" style="display:none;">
            <div class="factura-card" style="border-left:4px solid #dc3545;">
                <div class="factura-card-header" style="color:#dc3545;">
                    <i class="fas fa-ban"></i> Factura Cancelada
                    <span class="estado-badge estado-anulada" id="canceladaBadge" style="display:inline-block;">Cancelada</span>
                </div>
                <div class="factura-card-body">
                    <div class="cancelada-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="info-row"><span class="info-label">Cliente:</span><span id="can-cliente" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Cédula:</span><span id="can-cedula" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Fecha / Hora Cita:</span><span id="can-fechaCita" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Fecha / Hora Factura:</span><span id="can-fechaFactura" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Código de Factura:</span><span id="can-codigo" class="info-value">—</span></div>
                        <div class="info-row"><span class="info-label">Motivo Cancelación:</span><span id="can-motivo" class="info-value">—</span></div>
                        <div class="info-row" style="grid-column:1/-1;border-top:1px solid #eee;padding-top:10px;">
                            <span class="info-label" style="color:#dc3545;font-weight:700;">Monto Pagado:</span>
                            <span id="can-montoPagado" class="info-value" style="color:#28a745;font-weight:700;">0.00 Bs</span>
                        </div>
                        <div class="info-row" style="grid-column:1/-1;">
                            <span class="info-label">Monto Total (debía pagar):</span>
                            <span id="can-montoTotal" class="info-value">0.00 Bs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modales -->

<!-- Modal: Registrar Abono -->
<div class="modal" id="pagoModal">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3 class="modal-title">Registrar Abono</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="pagoForm">
                <input type="hidden" id="pagoFacturaId">
                <div class="form-group">
                    <label class="form-label">Método de Pago</label>
                    <select class="form-select" id="pagoMetodo" required>
                        <option value="efectivo">Efectivo (Bs)</option>
                        <option value="pagomovil">Pago Móvil</option>
                        <option value="divisas">Dólar $</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" min="0" class="form-input" id="pagoMontoVes" placeholder="0.00">
                    <small style="color:var(--gray);" id="pagoInputHint">Ingrese el monto en Bolívares</small>
                </div>
                <div id="pagoConversionRow" style="margin-top:8px;padding:10px;background:#f0f9ff;border-radius:6px;font-size:0.85rem;display:none;">
                    <span id="pagoConversionText"></span>
                </div>
                <input type="hidden" id="pagoTasa" value="0">
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="cancelPago">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPago">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Generar Factura -->
<div class="modal" id="generarFacturaModal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-file-invoice"></i> Generar Factura</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="generarFacturaForm">
                <input type="hidden" id="genFacturaCitaId">
                <div class="form-group">
                    <label class="form-label">Descripción del Servicio</label>
                    <input type="text" class="form-input" id="genDescripcionServicio" placeholder="Ej: Decoración de mesa principal">
                </div>
                <div class="form-group">
                    <label class="form-label">Costo de Servicio (Bs)</label>
                    <input type="number" step="0.01" min="0" class="form-input" id="genCostoServicio" placeholder="0.00" required>
                    <small class="form-help">Mano de obra / honorarios de decoración</small>
                </div>

                <!-- Preview del total -->
                <div class="gen-preview">
                    <div class="gen-preview-title"><i class="fas fa-receipt"></i> Resumen de Factura</div>
                    <div class="gen-preview-row"><span>Sub-Total:</span><span id="previewSubtotal">0,00 Bs</span></div>
                    <div class="gen-preview-row"><span>I.V.A (16%):</span><span id="previewIva">0,00 Bs</span></div>
                    <div class="gen-preview-row gen-preview-total"><span>TOTAL:</span><span id="previewTotal">0,00 Bs</span></div>
                    <div class="gen-preview-row gen-preview-min"><span>Anticipo mínimo (50%):</span><span id="previewMinimo">0,00 Bs</span></div>
                </div>

                <!-- Anticipo -->
                <div class="gen-anticipo">
                    <label class="gen-anticipo-label"><i class="fas fa-hand-holding-usd"></i> Anticipo (mín. 50%)</label>
                    <div class="gen-anticipo-row">
                        <div class="form-group" style="flex:1;min-width:160px;">
                            <label class="form-label" id="genMontoLabel">Monto a pagar ahora (Bs)</label>
                            <input type="number" step="0.01" min="0" class="form-input" id="genMontoBs" placeholder="0,00" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:140px;">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" id="genMetodoPago">
                                <option value="efectivo">Efectivo (Bs)</option>
                                <option value="pagomovil">Pago Móvil</option>
                                <option value="divisas">Dólar $</option>
                            </select>
                        </div>
                    </div>
                    <div id="genConversionRow" class="gen-conversion" style="display:none;">
                        <span id="genConversionText"></span>
                    </div>
                    <div id="genErrorAnticipo" class="gen-error"></div>
                    <div class="gen-progress-wrap">
                        <div class="pago-progress-bar">
                            <div class="pago-progress-fill warning" id="genProgressFill" style="width:0%;"></div>
                        </div>
                        <div class="gen-progress-labels">
                            <span id="genProgressLabel">0%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="genTasaBcv" value="0">

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="cancelGenFactura">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarFactura">Confirmar y Pagar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Cerrar Factura -->
<div class="modal" id="cerrarFacturaModal">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h3 class="modal-title">Cerrar Factura</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:15px;">¿Está seguro de cerrar esta factura? Una vez cerrada no se podrán registrar más pagos.</p>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="cancelCerrarFactura">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCerrar">Cerrar Factura</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Anular Factura -->
<div class="modal" id="anularFacturaModal">
    <div class="modal-content" style="max-width:440px;">
        <div class="modal-header">
            <h3 class="modal-title">Anular Factura</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="anularFacturaForm">
                <p style="margin-bottom:15px;">¿Está seguro de anular esta factura? Esta acción no se puede deshacer.</p>
                <div class="form-group">
                    <label class="form-label">Motivo de Anulación <span style="color:#dc3545;">*</span></label>
                    <textarea class="form-textarea" id="anularMotivo" rows="3" placeholder="Indique el motivo de la anulación..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="cancelAnularFactura">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarAnular">Anular Factura</button>
                </div>
            </form>
        </div>
    </div>
</div>

        </div>
    </div>

<style>
.radio-label { font-size:0.9rem; user-select:none; }
.radio-label input[type="radio"] { accent-color:var(--primary); }
.preview-line { display:flex; justify-content:space-between; padding:1px 0; }
.preview-line .pr { text-align:right; white-space:nowrap; }
</style>

<script>
    const APP_URL = '<?php echo APP_URL; ?>';
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    const APP_CONFIG = {
        ivaRate: <?php echo IVA_RATE; ?>,
        bcvRate: <?php
            try {
                $tasaSvc = new \BetelCreativa\Services\ExchangeRateService();
                echo $tasaSvc->getEffectiveRate();
            } catch (\Throwable $e) {
                echo 0;
            }
        ?>
    };
</script>
<script>
    document.getElementById('settingsBtn')?.addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById('settingsDropdown')?.classList.toggle('show');
    });
    document.getElementById('almacenSubmenuToggle')?.addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById('almacenSubmenu')?.classList.toggle('show');
    });
    document.addEventListener('click', function () {
        document.getElementById('settingsDropdown')?.classList.remove('show');
        document.getElementById('almacenSubmenu')?.classList.remove('show');
    });
</script>
<script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/facturas.js"></script>
</body>
</html>
