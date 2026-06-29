-- Betel Creativa - Esquema de Base de Datos
-- Refleja la estructura real de la base de datos en producción

CREATE DATABASE IF NOT EXISTS BetelCreativa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE BetelCreativa;

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
    role VARCHAR(10) DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    checkin_time DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
-- Tabla: materials
-- =============================================
CREATE TABLE IF NOT EXISTS materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    material_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cost_type ENUM('unit','wholesale') NOT NULL DEFAULT 'unit',
    wholesale_qty INT DEFAULT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) DEFAULT NULL,
    category_id INT,
    material_type ENUM('activo_retornable','consumible') NOT NULL DEFAULT 'consumible',
    supplier_id INT,
    current_location_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
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
    estado ENUM('En Proceso','Pendiente','En Progreso','Terminado','Cancelado') NOT NULL DEFAULT 'En Proceso',
    estado_previo_cancelacion VARCHAR(20) DEFAULT NULL,
    fecha_hora_cancelacion DATETIME DEFAULT NULL,
    motivo_cancelacion TEXT DEFAULT NULL,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (event_type_id) REFERENCES event_types(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- Tabla: cita_materiales
-- Relación muchos a muchos entre citas y materiales
-- con cantidades utilizadas
-- =============================================
CREATE TABLE IF NOT EXISTS cita_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    material_id INT NOT NULL,
    cantidad_utilizada INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- Tabla: locations (ubicaciones/estantes del almacén)
-- =============================================
CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(200) NOT NULL,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

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
    movement_date DATETIME NOT NULL,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
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
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
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
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
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
('log_retention_days', '90', 'Días de retención de logs');

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

-- Valores iniciales de tipos de evento
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
-- Migration 2026-06-28: Agregar id_number a customers
-- Ejecutar si la tabla ya existe:
-- ALTER TABLE customers
--   ADD COLUMN id_number VARCHAR(20) NOT NULL AFTER last_name;
-- Luego: ALTER TABLE customers MODIFY email VARCHAR(100) NOT NULL;
-- Luego: ALTER TABLE customers MODIFY phone VARCHAR(20) NOT NULL;

-- =============================================
-- Migration 2026-06-28: Agregar material_type a materials
-- Ejecutar si la tabla ya existe:
-- ALTER TABLE materials
--   ADD COLUMN material_type ENUM('activo_retornable','consumible') NOT NULL DEFAULT 'consumible'
--   AFTER category_id;

-- =============================================
-- Migration 2026-06-28: Renombrar appointments → citas
-- (ya ejecutada; solo referencia para entornos nuevos)
-- Las columnas event_type_id se migran con:
--   UPDATE citas c
--     JOIN event_types et ON LOWER(et.name) = LOWER(c.event_type)
--     SET c.event_type_id = et.id
--     WHERE c.event_type_id IS NULL;
