-- =============================================
-- Migration 2026-06-30: Refactor roles (APLICADA PARCIALMENTE)
-- Crea tabla roles, migra users.role → users.id_rol
-- solo para bases de datos existentes con el schema VIEJO.
-- Los entornos NUEVOS: Database/schema.sql ya incluye roles e id_rol.
-- =============================================

-- Status en DB real:
--   Paso 1-5: APLICADO
--   Paso 6:   NO APLICADO (id_rol INT NOT NULL existe, pero SIN FK)
--   Paso 7:   NO APLICADO
--   Paso 8:   APLICADO (columna role eliminada)

-- 1. Crear tabla roles
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(20) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 2. Insertar los 3 roles
INSERT IGNORE INTO roles (role_name, display_name) VALUES
    ('super_admin', 'Super Administrador'),
    ('admin', 'Administrador'),
    ('user', 'Usuario');

-- 3. Convertir el primer usuario (admin actual) a super_admin
UPDATE users SET role = 'super_admin' WHERE user_id = 1 AND role = 'admin';

-- 4. Agregar columna id_rol
ALTER TABLE users ADD COLUMN id_rol INT NULL AFTER role;

-- 5. Migrar datos existentes
UPDATE users u
    JOIN roles r ON u.role = r.role_name
    SET u.id_rol = r.id_rol;

-- 6. Hacer id_rol NOT NULL y agregar FK
ALTER TABLE users MODIFY id_rol INT NOT NULL;
ALTER TABLE users ADD CONSTRAINT fk_users_rol
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT;

-- 7. (Nota: primero agrandar VARCHAR si 'super_admin' está truncado)
-- ALTER TABLE users MODIFY role VARCHAR(20) DEFAULT 'user';

-- 8. Eliminar columna role (ya no se usa)
-- ALTER TABLE users DROP COLUMN role;

-- =============================================
-- Rollback (si es necesario)
-- =============================================
-- ALTER TABLE users DROP FOREIGN KEY fk_users_rol;
-- ALTER TABLE users DROP COLUMN id_rol;
-- UPDATE users SET role = 'admin' WHERE user_id = 1;
-- DROP TABLE IF EXISTS roles;
