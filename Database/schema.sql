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
    email VARCHAR(100),
    phone VARCHAR(20),
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
-- Tabla: appointments
-- =============================================
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    event_type VARCHAR(100),
    location VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
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
