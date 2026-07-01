-- Betel Creativa - Esquema de Base de Datos
-- Compatible con MySQL 5.7+ y MariaDB 10.2+

-- =============================================
-- Tabla: users
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    id_number VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    security_code VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    id_rol INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    checkin_time DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- Tabla: roles
-- =============================================
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(20) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (role_name, display_name) VALUES
    ('super_admin', 'Super Administrador'),
    ('admin', 'Administrador'),
    ('user', 'Usuario');

-- =============================================
-- Tabla: categories
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status VARCHAR(10) DEFAULT 'active',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- Tabla: suppliers (proveedores)
-- =============================================
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    supplier_type ENUM('fijo','comodin') NOT NULL DEFAULT 'fijo',
    subtype VARCHAR(50) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO suppliers (company_name, contact_name, supplier_type, subtype, is_active) VALUES
('Proveedor General', 'Sistema', 'fijo', NULL, 1);

-- =============================================
-- Tabla: materials
-- =============================================
CREATE TABLE IF NOT EXISTS materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    material_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cost_type ENUM('unit','wholesale') NOT NULL DEFAULT 'unit',
    unidad_compra VARCHAR(50) NOT NULL DEFAULT 'Unidad',
    unidad_consumo VARCHAR(50) NOT NULL DEFAULT 'Unidad',
    factor_conversion INT NOT NULL DEFAULT 1,
    wholesale_qty INT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    category_id INT,
    material_type ENUM('activo_retornable','consumible') NOT NULL DEFAULT 'consumible',
    supplier_id INT,
    detalle_comodin VARCHAR(255) DEFAULT NULL,
    current_location_id INT,
    is_active TINYINT(1) DEFAULT 1,
    reserved_stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- Tabla: customers
-- =============================================
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    id_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    client_type VARCHAR(20) DEFAULT 'regular',
    source VARCHAR(20) DEFAULT 'other',
    notes TEXT,
    preferences TEXT,
    avatar VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- Tabla: event_types
-- Tipos de evento configurables por el usuario
-- =============================================
CREATE TABLE IF NOT EXISTS event_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO event_types (name) VALUES
('Boda'),
('Cumpleaños'),
('Evento Corporativo'),
('Quinceañero'),
('Baby Shower'),
('Bautizo'),
('Graduación'),
('Aniversario'),
('Otro');

-- =============================================
-- Tabla: citas (antes appointments)
-- Incluye máquina de estados automática y
-- auditoría de cancelaciones (soft-delete)
-- =============================================
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_hora_inicio DATETIME NOT NULL,
    fecha_hora_fin DATETIME NOT NULL,
    event_type_id INT DEFAULT NULL,
    ubicacion VARCHAR(255),
    estado ENUM('Pendiente','En Proceso','En Progreso','Finalizada','Cancelado') NOT NULL DEFAULT 'Pendiente',
    estado_previo_cancelacion VARCHAR(20) DEFAULT NULL,
    fecha_hora_cancelacion DATETIME DEFAULT NULL,
    motivo_cancelacion TEXT DEFAULT NULL,
    notas TEXT,
    motivo_sin_materiales TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_citas_customer FOREIGN KEY (cliente_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    CONSTRAINT fk_citas_event_type FOREIGN KEY (event_type_id) REFERENCES event_types(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- Tabla: cita_materiales
-- Relación muchos a muchos entre citas y materiales
-- con cantidades utilizadas y precio unitario
-- =============================================
CREATE TABLE IF NOT EXISTS cita_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    material_id INT NOT NULL,
    cantidad_utilizada INT NOT NULL DEFAULT 0,
    precio_unitario DECIMAL(12,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cita_materiales_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cita_materiales_material FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- Tabla: locations (ubicaciones/estantes del almacén)
-- =============================================
CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(200) NOT NULL,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO locations (location_name, description) VALUES ('Almacén General', 'Ubicación por defecto');

-- =============================================
-- Tabla: material_stock_locations (stock por ubicación)
-- =============================================
CREATE TABLE IF NOT EXISTS material_stock_locations (
    material_id INT NOT NULL,
    location_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (material_id, location_id),
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- Tabla: inventory_movements
-- =============================================
CREATE TABLE IF NOT EXISTS inventory_movements (
    movement_id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    user_id INT,
    action_type VARCHAR(20) NOT NULL COMMENT 'Entry, Exit, Transfer',
    quantity INT NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    extra_note TEXT DEFAULT NULL,
    origin_location_id INT DEFAULT NULL,
    destination_location_id INT DEFAULT NULL,
    tipo_referencia VARCHAR(30) DEFAULT NULL COMMENT 'cita, compra, venta, transferencia, ajuste',
    referencia_id INT DEFAULT NULL,
    movement_date DATETIME NOT NULL,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_referencia_logistica (tipo_referencia, referencia_id)
) ENGINE=InnoDB;

-- =============================================
-- Tabla: quotes
-- =============================================
CREATE TABLE IF NOT EXISTS quotes (
    quote_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    title VARCHAR(200),
    description TEXT,
    value DECIMAL(12,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'pending',
    event_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotes_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- Tabla: tasks (tareas del dashboard)
-- =============================================
CREATE TABLE IF NOT EXISTS tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- =============================================
-- Tabla: password_resets
-- =============================================
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    contact VARCHAR(100) NOT NULL DEFAULT '',
    contact_type VARCHAR(10) NOT NULL DEFAULT '',
    expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- Tabla: login_attempts
-- Control de intentos fallidos de inicio de sesión
-- =============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempted_at)
) ENGINE=InnoDB;

-- =============================================
-- Tabla: user_preferences
-- Preferencias individuales de cada usuario
-- =============================================
CREATE TABLE IF NOT EXISTS user_preferences (
    pref_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    notify_low_stock TINYINT(1) DEFAULT 1,
    notify_appointments TINYINT(1) DEFAULT 1,
    notify_security TINYINT(1) DEFAULT 1,
    notify_reports TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- Tabla: settings
-- Configuración global del sistema (clave-valor)
-- =============================================
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- Valores iniciales de configuración global
-- =============================================
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('low_stock_threshold', '10', 'Cantidad mínima antes de marcar stock bajo'),
('pagination_default', '10', 'Filas por página en tablas'),
('dashboard_refresh_interval', '30000', 'Intervalo de actualización del dashboard en ms'),
('password_min_length', '6', 'Longitud mínima de contraseña'),
('appointment_default_duration', '60', 'Duración predeterminada de citas en minutos'),
('business_hours_start', '08:00', 'Hora de apertura'),
('business_hours_end', '18:00', 'Hora de cierre'),
('working_days', '1,2,3,4,5,6', 'Días laborales (1=domingo, 7=sábado)'),
('backup_frequency', 'weekly', 'Frecuencia de respaldo: daily|weekly|monthly'),
('log_retention_days', '90', 'Días de retención de logs'),
('bcv_rate', '36.50', 'Tasa de cambio BCV (USD a VES)');

-- =============================================
-- Tabla: cita_materiales_historial
-- Auditoría de asignación/reserva/ejecución de materiales en citas
-- =============================================
CREATE TABLE IF NOT EXISTS cita_materiales_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    material_id INT NOT NULL,
    cantidad_anterior INT DEFAULT 0,
    cantidad_nueva INT DEFAULT 0,
    accion VARCHAR(20) NOT NULL COMMENT 'Asignado|Modificado|Cancelado|Ejecutado',
    estado_cita_momento VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_material FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- Tabla: facturas
-- Vinculada 1:1 a citas; almacena costo de
-- servicio, total y notas de cuota
-- =============================================
CREATE TABLE IF NOT EXISTS facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    costo_servicio DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Mano de obra / Honorarios',
    total_factura DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'costo_servicio + suma total de materiales',
    notas_cuota VARCHAR(255) DEFAULT NULL,
    estado ENUM('activa','cerrada','anulada') NOT NULL DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_facturas_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =============================================
-- Tabla: pagos_factura
-- Abonos/cuotas aplicados a una factura;
-- almacena monto (siempre en USD), método,
-- y tasa BCV usada para auditoría financiera
-- =============================================
CREATE TABLE IF NOT EXISTS pagos_factura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL COMMENT 'Siempre en USD (base contable)',
    metodo_pago ENUM('divisas', 'efectivo', 'pagomovil') NOT NULL,
    tasa_usada DECIMAL(12,2) NOT NULL COMMENT 'Tasa BCV del momento del pago',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE CASCADE
) ENGINE=InnoDB;
