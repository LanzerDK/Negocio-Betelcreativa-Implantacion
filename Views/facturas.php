<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Bet-El Creativa</title>
    
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>Public/images/favicon.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/css/_base.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>Public/assets/fonts/poppins/poppins.css">
</head>

<body>
    <div class="app-container">
        <!-- Header -->
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
        <!-- Menú principal -->
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

<div class="factura-selector">
    <label for="selectorCitas" class="form-label">Seleccionar Cita</label>
    <select id="selectorCitas" class="form-select" required>
        <option value="">— Seleccione una cita —</option>
    </select>
</div>

<div id="facturaPanel" style="display:none;margin-top:20px;">
    <div class="factura-grid">
        <!-- Tarjeta 1: Información de la Cita -->
        <div class="factura-card" id="card1">
            <div class="factura-card-header">
                <i class="fas fa-calendar-alt"></i> Información de la Cita
                <span id="facturaEstadoBadge" style="display:none;margin-left:10px;"></span>
            </div>
            <div class="factura-card-body">
                <div class="info-row"><span class="info-label">Cliente:</span><span id="card1-cliente" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Cédula:</span><span id="card1-cedula" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Teléfono:</span><span id="card1-telefono" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Fecha:</span><span id="card1-fecha" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Ubicación:</span><span id="card1-ubicacion" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Tipo Evento:</span><span id="card1-evento" class="info-value">—</span></div>
                <div class="info-row"><span class="info-label">Estado Cita:</span><span id="card1-estado" class="info-value">—</span></div>
            </div>
        </div>

        <!-- Tarjeta 2: Materiales -->
        <div class="factura-card" id="card2">
            <div class="factura-card-header">
                <i class="fas fa-boxes"></i> Materiales
            </div>
            <div class="factura-card-body">
                <table class="factura-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Material</th>
                            <th class="text-right">Cant.</th>
                            <th class="text-right">P. Unit. (Bs)</th>
                            <th class="text-right">Total (Bs)</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-materiales">
                        <tr><td colspan="5" class="text-center" style="color:var(--gray);padding:20px;">Seleccione una cita</td></tr>
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
                <div class="factura-actions" id="facturaActions" style="margin-top:15px;display:flex;gap:10px;">
                    <button type="button" class="btn btn-primary" id="btnGenerarFactura" style="flex:1;">
                        <i class="fas fa-file-invoice"></i> Generar Factura
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Pagos -->
        <div class="factura-card" id="card3">
            <div class="factura-card-header">
                <i class="fas fa-credit-card"></i> Pagos y Cuotas
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
                <div class="pago-status" id="status-pago" style="margin:10px 0;padding:8px 12px;border-radius:6px;text-align:center;font-weight:600;"></div>

                <h4 style="margin:15px 0 8px;font-size:0.9rem;color:var(--gray);">Historial de Abonos</h4>
                <table class="factura-table historial-table">
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

                <div class="factura-actions" style="margin-top:15px;">
                    <button type="button" class="btn btn-primary" id="btnRegistrarAbonoPagos" disabled style="flex:1;">
                        <i class="fas fa-plus-circle"></i> Registrar Abono / Cuota
                    </button>
                    <button type="button" class="btn btn-outline" id="btnCerrarFactura" disabled style="flex:1;display:none;">
                        <i class="fas fa-lock"></i> Cerrar Factura
                    </button>
                    <button type="button" class="btn btn-outline" id="btnAnularFactura" disabled style="flex:1;display:none;">
                        <i class="fas fa-ban"></i> Anular
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

                <!-- Radio: Tipo de pago -->
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

                <!-- Sección Pagar ahora (preview) -->
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

                <!-- Sección Cuotas -->
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

        </div> <!-- /.main-content -->
    </div> <!-- /.app-container -->

<style>
.factura-selector { max-width:500px; margin-bottom:10px; }
.factura-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start; }
.factura-grid .factura-card:last-child { grid-column:1 / -1; }
.factura-card { background:#fff; border-radius:10px; box-shadow:var(--shadow),0 2px 20px rgba(212,175,55,0.2); overflow:hidden; transition:var(--transition); display:flex; flex-direction:column; }
.factura-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.1),0 2px 20px rgba(212,175,55,0.25); }
.factura-card-header { padding:14px 18px; font-weight:600; font-size:1rem; background:linear-gradient(to right,var(--primary),var(--secondary)); color:var(--gold-button); border-bottom:1px solid rgba(255,255,255,0.1); flex-shrink:0; }
.factura-card-header i { margin-right:8px; }
.factura-card-body { padding:16px 18px; flex:1; }
.info-row { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f0f0f0; font-size:0.9rem; }
.info-label { color:var(--gray); font-weight:500; }
.info-value { font-weight:600; text-align:right; }
.factura-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
.factura-table th { text-align:left; padding:6px 8px; border-bottom:2px solid #eee; color:var(--gray); font-weight:600; }
.factura-table td { padding:6px 8px; border-bottom:1px solid #f0f0f0; }
.factura-table .text-right { text-align:right; }
.factura-table .text-center { text-align:center; }
.factura-total-section { margin-top:12px; padding-top:10px; border-top:2px solid #eee; }
.total-row { display:flex; justify-content:space-between; padding:4px 0; font-size:0.9rem; }
.total-final { border-top:2px solid #333; margin-top:6px; padding-top:8px; font-size:1rem; }
.pago-resumen { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.pago-item { background:#f8f9fa; border-radius:8px; padding:12px; text-align:center; transition:var(--transition); }
.pago-item.pendiente { background:#fff3cd; }
.pago-item:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.06); }
.pago-label { display:block; font-size:0.8rem; color:var(--gray); margin-bottom:4px; }
.pago-monto { display:block; font-size:1.1rem; font-weight:700; }
.pago-status { border-radius:6px; padding:8px 12px; text-align:center; font-weight:600; }
.pago-status.pagada { background:#d4edda; color:#155724; }
.pago-status.pendiente-pago { background:#fff3cd; color:#856404; }
.historial-table { font-size:0.8rem; }
.historial-table th, .historial-table td { padding:4px 6px; }
.factura-actions { display:flex; gap:10px; }
.estado-badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:0.75rem; font-weight:600; }
.estado-activa { background:#e8f4fd; color:#0066cc; }
.estado-cerrada { background:#d4edda; color:#155724; }
.estado-anulada { background:#f8d7da; color:#721c24; }
.radio-label { font-size:0.9rem; user-select:none; }
.radio-label input[type="radio"] { accent-color:var(--primary); }
.preview-line { display:flex; justify-content:space-between; padding:1px 0; }
.preview-line .pr { text-align:right; white-space:nowrap; }
@media (max-width:900px) { .factura-grid { grid-template-columns:1fr; } .factura-grid .factura-card:last-child { grid-column:1; } .pago-resumen { grid-template-columns:1fr 1fr; } }
</style>

<script>
    const APP_URL = '<?php echo APP_URL; ?>';
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
</script>

<script src="<?php echo APP_URL; ?>Public/js/toast.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/modal.js"></script>
<script src="<?php echo APP_URL; ?>Public/js/facturas.js"></script>
</body>
</html>
