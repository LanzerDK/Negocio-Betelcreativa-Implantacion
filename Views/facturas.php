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
            <a href="<?php echo APP_URL; ?>materials" class="menu-item">
                <i class="fas fa-box-open"></i>
                <span>Materiales</span>
            </a>
            <a href="<?php echo APP_URL; ?>quotes" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Citas</span>
            </a>
            <a href="<?php echo APP_URL; ?>facturas" class="menu-item active">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
            <a href="<?php echo APP_URL; ?>category" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Categoría</span>
            </a>
            <a href="<?php echo APP_URL; ?>suppliers" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>
            <a href="<?php echo APP_URL; ?>customers" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="<?php echo APP_URL; ?>storage" class="menu-item">
                <i class="fas fa-warehouse"></i>
                <span>Almacén</span>
            </a>
            <a href="<?php echo APP_URL; ?>reports" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </a>
        </nav>

        <div class="main-content">

<div class="page-header">
    <h1><i class="fas fa-file-invoice-dollar"></i> Facturación</h1>
</div>

<div class="factura-layout">

    <!-- Sidebar -->
    <div class="factura-sidebar">
        <div class="sidebar-tabs">
            <button class="sidebar-tab active" data-tab="abiertas">
                <i class="fas fa-folder-open"></i>
                <span>Abiertas</span>
            </button>
            <button class="sidebar-tab" data-tab="pendientes">
                <i class="fas fa-clock"></i>
                <span>Pendientes</span>
            </button>
            <button class="sidebar-tab" data-tab="pagadas">
                <i class="fas fa-check-circle"></i>
                <span>Pagadas</span>
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
                                <span>Total Materiales:</span>
                                <span id="card2-total-materiales">0.00 Bs</span>
                            </div>
                            <div class="total-row">
                                <span>Costo de Servicio:</span>
                                <span id="card2-costo-servicio">0.00 Bs</span>
                            </div>
                            <div class="total-row">
                                <span>I.V.A (16%):</span>
                                <span id="card2-iva">0.00 Bs</span>
                            </div>
                            <div class="total-row total-final">
                                <span><strong>TOTAL FACTURA:</strong></span>
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
                        <option value="divisas">Divisas ($)</option>
                        <option value="efectivo">Efectivo (Bs)</option>
                        <option value="pagomovil">PagoMóvil</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monto (Bs)</label>
                    <input type="number" step="0.01" min="0" class="form-input" id="pagoMontoVes" placeholder="0.00">
                </div>
                <div class="form-group" id="divisaGroup" style="display:none;">
                    <label class="form-label">Equivalencia en Dólares ($)</label>
                    <input type="number" step="0.01" min="0" class="form-input" id="pagoMontoUsd" placeholder="0.00" readonly style="background:#f5f5f5;">
                    <small style="color:var(--gray);">1 $ = <span id="pagoTasaDisplay">0.00</span> Bs</small>
                </div>
                <input type="hidden" id="pagoTasa">
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
            <h3 class="modal-title">Generar Factura</h3>
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
                    <small style="color:var(--gray);">Mano de obra / honorarios de decoración</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas de Cuota</label>
                    <input type="text" class="form-input" id="genNotasCuota" placeholder="Ej: 50% de inicial para reservar">
                </div>

                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">Tipo de Pago</label>
                    <div class="radio-group" style="display:flex;gap:20px;margin-top:6px;">
                        <label class="radio-label" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="planTipo" value="contado" checked> Pagar ahora
                        </label>
                        <label class="radio-label" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="planTipo" value="cuotas"> Pagar por Cuotas
                        </label>
                    </div>
                </div>

                <div id="contadoPreview" style="margin-top:12px;padding:12px;background:#f9f9f9;border-radius:8px;font-family:'Courier New',monospace;font-size:12px;line-height:1.6;">
                    <div style="text-align:center;font-weight:700;margin-bottom:6px;">— RECIBO DE PAGO —</div>
                    <div class="preview-line"><span>Sub-Total:</span><span class="pr" id="previewSubtotal">0.00 Bs</span></div>
                    <div class="preview-line"><span>I.V.A (16%):</span><span class="pr" id="previewIva">0.00 Bs</span></div>
                    <div class="preview-line" style="border-top:1px solid #000;padding-top:4px;font-weight:700;"><span>TOTAL:</span><span class="pr" id="previewTotal">0.00 Bs</span></div>
                    <div style="margin-top:10px;padding-top:8px;border-top:1px dashed #ccc;">
                        <div class="form-group" style="margin-bottom:6px;">
                            <label style="font-family:inherit;font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Método de Pago</label>
                            <select class="form-select" id="genMetodoPago" style="font-size:11px;padding:4px 6px;">
                                <option value="efectivo">Efectivo (Bs)</option>
                                <option value="pagomovil">PagoMóvil</option>
                                <option value="divisas">Divisas ($)</option>
                            </select>
                        </div>
                        <div id="genDivisaSection" style="display:none;">
                            <div class="form-group" style="margin-bottom:2px;">
                                <label style="font-family:inherit;font-size:11px;font-weight:600;display:block;margin-bottom:2px;">Monto en Dólares ($)</label>
                                <input type="number" step="0.01" min="0" class="form-input" id="genMontoUsd" placeholder="0.00" style="font-size:11px;padding:4px 6px;">
                            </div>
                            <small style="color:var(--gray);">1 $ = <span id="genTasaBcv">0.00</span> Bs</small>
                        </div>
                    </div>
                </div>

                <div id="cuotasSection" style="display:none;margin-top:12px;">
                    <div class="form-group">
                        <label class="form-label">Número de Cuotas</label>
                        <select class="form-select" id="genNumCuotas">
                            <option value="2">2 cuotas</option>
                            <option value="3">3 cuotas</option>
                            <option value="6">6 cuotas</option>
                        </select>
                    </div>
                    <div style="padding:12px;background:#f9f9f9;border-radius:8px;font-family:'Courier New',monospace;font-size:12px;line-height:1.6;">
                        <div class="preview-line"><span>Sub-Total:</span><span class="pr" id="cuotaPreviewSubtotal">0.00 Bs</span></div>
                        <div class="preview-line"><span>I.V.A (16%):</span><span class="pr" id="cuotaPreviewIva">0.00 Bs</span></div>
                        <div class="preview-line" style="border-top:1px solid #000;padding-top:4px;font-weight:700;"><span>TOTAL:</span><span class="pr" id="cuotaPreviewTotal">0.00 Bs</span></div>
                        <div class="preview-line" style="border-top:1px dashed #000;margin-top:4px;padding-top:4px;"><span>Cuotas:</span><span class="pr" id="cuotaPreviewCuota">—</span></div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:16px;">
                    <button type="button" class="btn btn-outline" id="cancelGenFactura">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarFactura">Confirmar</button>
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
    document.addEventListener('click', function () {
        document.getElementById('settingsDropdown')?.classList.remove('show');
    });
</script>
<script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/facturas.js"></script>
</body>
</html>
