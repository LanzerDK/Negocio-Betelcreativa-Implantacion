-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 11-07-2026 a las 18:19:24
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `betelcreativa`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_hora_inicio` datetime NOT NULL,
  `fecha_hora_fin` datetime NOT NULL,
  `event_type_id` int DEFAULT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','En Progreso','Finalizada','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `estado_previo_cancelacion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_hora_cancelacion` datetime DEFAULT NULL,
  `motivo_cancelacion` text COLLATE utf8mb4_unicode_ci,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `motivo_sin_materiales` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `cliente_id`, `fecha_hora_inicio`, `fecha_hora_fin`, `event_type_id`, `ubicacion`, `estado`, `estado_previo_cancelacion`, `fecha_hora_cancelacion`, `motivo_cancelacion`, `notas`, `motivo_sin_materiales`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-07-16 08:00:00', '2026-07-24 08:00:00', 5, 'En casa', 'En Proceso', NULL, NULL, NULL, NULL, 'ghhggh', '2026-07-11 18:16:08', '2026-07-11 18:16:32'),
(2, 1, '2026-07-31 08:00:00', '2026-08-01 08:00:00', 5, 'En casa', 'Pendiente', NULL, NULL, NULL, NULL, 'jhjhj', '2026-07-11 18:18:32', '2026-07-11 18:18:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita_materiales`
--

CREATE TABLE `cita_materiales` (
  `id` int NOT NULL,
  `cita_id` int NOT NULL,
  `material_id` int NOT NULL,
  `cantidad_utilizada` int NOT NULL DEFAULT '0',
  `precio_unitario` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita_materiales_historial`
--

CREATE TABLE `cita_materiales_historial` (
  `id` int NOT NULL,
  `cita_id` int NOT NULL,
  `material_id` int NOT NULL,
  `cantidad_anterior` int DEFAULT '0',
  `cantidad_nueva` int DEFAULT '0',
  `accion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Asignado|Modificado|Cancelado|Ejecutado',
  `estado_cita_momento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customers`
--

CREATE TABLE `customers` (
  `customer_id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `client_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'regular',
  `source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `preferences` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `id_number`, `email`, `phone`, `address`, `client_type`, `source`, `notes`, `preferences`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Gasdsad', 'asdsa', 'V-31234564', 'ismael_4_joiel_2@hotmail.com', '04140468794', 'urb. Sana rosa', 'Frequent', 'Website', 'hjjhjjh', '', NULL, 1, '2026-07-11 18:15:50', '2026-07-11 18:15:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `event_types`
--

CREATE TABLE `event_types` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `event_types`
--

INSERT INTO `event_types` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Boda', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(2, 'Cumpleaños', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(3, 'Evento Corporativo', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(4, 'Quinceañero', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(5, 'Baby Shower', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(6, 'Bautizo', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(7, 'Graduación', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(8, 'Aniversario', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23'),
(9, 'Otro', 1, '2026-07-11 16:12:23', '2026-07-11 16:12:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int NOT NULL,
  `cita_id` int NOT NULL,
  `costo_servicio` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Mano de obra / Honorarios',
  `total_factura` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'costo_servicio + suma total de materiales',
  `notas_cuota` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de quien creó la factura',
  `tipo` enum('factura','recibo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'factura' COMMENT 'Tipo de documento: factura (principal) o recibo',
  `factura_origen_id` int DEFAULT NULL COMMENT 'Si es recibo, apunta a la factura principal',
  `descripcion_servicio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción del servicio prestado',
  `estado` enum('activa','cerrada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `plan_tipo` enum('contado','cuotas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contado',
  `plan_cuotas_total` tinyint DEFAULT NULL COMMENT 'Total de cuotas si plan_tipo=cuotas',
  `plan_monto_cuota_sugerido` decimal(12,2) DEFAULT NULL COMMENT 'Monto sugerido por cuota'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `cita_id`, `costo_servicio`, `total_factura`, `notas_cuota`, `created_by_name`, `tipo`, `factura_origen_id`, `descripcion_servicio`, `estado`, `created_at`, `plan_tipo`, `plan_cuotas_total`, `plan_monto_cuota_sugerido`) VALUES
(1, 1, 45.00, 52.20, NULL, 'ismael maestre', 'factura', NULL, 'Evento completo', 'cerrada', '2026-07-11 18:16:32', 'cuotas', NULL, 26.10),
(2, 1, 0.00, 1000.00, NULL, 'ismael maestre', 'recibo', 1, NULL, 'cerrada', '2026-07-11 18:16:48', 'contado', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_historial`
--

CREATE TABLE `facturas_historial` (
  `id` int NOT NULL,
  `factura_id` int NOT NULL,
  `estado_anterior` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_nuevo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` int DEFAULT NULL COMMENT 'user_id que realizó el cambio',
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturas_historial`
--

INSERT INTO `facturas_historial` (`id`, `factura_id`, `estado_anterior`, `estado_nuevo`, `changed_by`, `changed_at`, `motivo`) VALUES
(1, 1, '', 'activa', 2, '2026-07-11 18:16:32', 'Creación de factura'),
(2, 1, 'activa', 'cerrada', 2, '2026-07-11 18:16:48', 'Pago completo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `movement_id` int NOT NULL,
  `material_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Entry, Exit, Transfer',
  `quantity` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_note` text COLLATE utf8mb4_unicode_ci,
  `origin_location_id` int DEFAULT NULL,
  `destination_location_id` int DEFAULT NULL,
  `tipo_referencia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'cita, compra, venta, transferencia, ajuste',
  `referencia_id` int DEFAULT NULL,
  `movement_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locations`
--

CREATE TABLE `locations` (
  `location_id` int NOT NULL,
  `location_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` int DEFAULT NULL,
  `max_capacity` int NOT NULL DEFAULT '200',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`, `warehouse_id`, `max_capacity`, `description`) VALUES
(1, 'Almacén General', NULL, 200, 'Ubicación por defecto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materials`
--

CREATE TABLE `materials` (
  `material_id` int NOT NULL,
  `material_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost_type` enum('unit','wholesale') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unit',
  `unidad_compra` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unidad',
  `unidad_consumo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unidad',
  `factor_conversion` int NOT NULL DEFAULT '1',
  `wholesale_qty` int DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `material_type` enum('activo_retornable','consumible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'consumible',
  `supplier_id` int DEFAULT NULL,
  `detalle_comodin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_location_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `reserved_stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_stock_locations`
--

CREATE TABLE `material_stock_locations` (
  `material_id` int NOT NULL,
  `location_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_factura`
--

CREATE TABLE `pagos_factura` (
  `id` int NOT NULL,
  `factura_id` int NOT NULL,
  `monto` decimal(12,2) NOT NULL COMMENT 'Siempre en USD (base contable)',
  `metodo_pago` enum('divisas','efectivo','pagomovil') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasa_usada` decimal(12,2) NOT NULL COMMENT 'Tasa BCV del momento del pago',
  `Ref_PagoMovil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nro de referencia para Pago Móvil',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos_factura`
--

INSERT INTO `pagos_factura` (`id`, `factura_id`, `monto`, `metodo_pago`, `tasa_usada`, `Ref_PagoMovil`, `fecha`) VALUES
(1, 1, 0.07, 'efectivo', 709.69, NULL, '2026-07-11 18:16:32'),
(2, 2, 1.41, 'efectivo', 709.69, NULL, '2026-07-11 18:16:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quotes`
--

CREATE TABLE `quotes` (
  `quote_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `value` decimal(12,2) DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `event_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int NOT NULL,
  `role_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `role_name`, `display_name`) VALUES
(1, 'super_admin', 'Super Administrador'),
(2, 'admin', 'Administrador'),
(3, 'user', 'Usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `setting_id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'low_stock_threshold', '10', 'Cantidad mínima antes de marcar stock bajo', '2026-07-11 16:12:26'),
(2, 'pagination_default', '10', 'Filas por página en tablas', '2026-07-11 16:12:26'),
(3, 'dashboard_refresh_interval', '30000', 'Intervalo de actualización del dashboard en ms', '2026-07-11 16:12:26'),
(4, 'password_min_length', '6', 'Longitud mínima de contraseña', '2026-07-11 16:12:26'),
(5, 'appointment_default_duration', '60', 'Duración predeterminada de citas en minutos', '2026-07-11 16:12:26'),
(6, 'business_hours_start', '08:00', 'Hora de apertura', '2026-07-11 16:12:26'),
(7, 'business_hours_end', '18:00', 'Hora de cierre', '2026-07-11 16:12:26'),
(8, 'working_days', '1,2,3,4,5,6', 'Días laborales (1=domingo, 7=sábado)', '2026-07-11 16:12:26'),
(9, 'backup_frequency', 'weekly', 'Frecuencia de respaldo: daily|weekly|monthly', '2026-07-11 16:12:26'),
(10, 'log_retention_days', '90', 'Días de retención de logs', '2026-07-11 16:12:26'),
(11, 'bcv_rate', '709.6935', 'Tasa de cambio BCV (USD a VES)', '2026-07-11 18:16:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int NOT NULL,
  `company_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `supplier_type` enum('fijo','comodin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fijo',
  `subtype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `company_name`, `contact_name`, `phone`, `email`, `address`, `notes`, `supplier_type`, `subtype`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Proveedor General', 'Sistema', NULL, NULL, NULL, NULL, 'fijo', NULL, 1, '2026-07-11 16:12:22', '2026-07-11 16:12:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('pending','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `security_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_rol` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `checkin_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `email`, `id_number`, `password`, `security_code`, `phone`, `avatar`, `id_rol`, `is_active`, `checkin_time`, `created_at`, `updated_at`) VALUES
(2, 'ismael', 'maestre', 'Ismael12', 'ismael@hotmail.com', 'V-30889803', '$2y$10$9xgEHXig40/Uo5ZZbuX1T.7pXDYvGNS0FcKpBkPDrpGpFAnrJcITW', '', '0414-470-04-55', NULL, 1, 1, NULL, '2026-07-11 18:15:14', '2026-07-11 18:15:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_preferences`
--

CREATE TABLE `user_preferences` (
  `pref_id` int NOT NULL,
  `user_id` int NOT NULL,
  `notify_low_stock` tinyint(1) DEFAULT '1',
  `notify_appointments` tinyint(1) DEFAULT '1',
  `notify_security` tinyint(1) DEFAULT '1',
  `notify_reports` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `warehouses`
--

CREATE TABLE `warehouses` (
  `warehouse_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_shelves` int NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_citas_customer` (`cliente_id`),
  ADD KEY `fk_citas_event_type` (`event_type_id`);

--
-- Indices de la tabla `cita_materiales`
--
ALTER TABLE `cita_materiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cita_materiales_cita` (`cita_id`),
  ADD KEY `fk_cita_materiales_material` (`material_id`);

--
-- Indices de la tabla `cita_materiales_historial`
--
ALTER TABLE `cita_materiales_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historial_cita` (`cita_id`),
  ADD KEY `fk_historial_material` (`material_id`),
  ADD KEY `fk_historial_usuario` (`usuario_id`);

--
-- Indices de la tabla `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indices de la tabla `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_facturas_cita` (`cita_id`);

--
-- Indices de la tabla `facturas_historial`
--
ALTER TABLE `facturas_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`);

--
-- Indices de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_referencia_logistica` (`tipo_referencia`,`referencia_id`);

--
-- Indices de la tabla `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_time` (`attempted_at`);

--
-- Indices de la tabla `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD UNIQUE KEY `material_code` (`material_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indices de la tabla `material_stock_locations`
--
ALTER TABLE `material_stock_locations`
  ADD PRIMARY KEY (`material_id`,`location_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `pagos_factura`
--
ALTER TABLE `pagos_factura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `quotes`
--
ALTER TABLE `quotes`
  ADD PRIMARY KEY (`quote_id`),
  ADD KEY `fk_quotes_customer` (`customer_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indices de la tabla `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indices de la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`pref_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`warehouse_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cita_materiales`
--
ALTER TABLE `cita_materiales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cita_materiales_historial`
--
ALTER TABLE `cita_materiales_historial`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `facturas_historial`
--
ALTER TABLE `facturas_historial`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `movement_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_factura`
--
ALTER TABLE `pagos_factura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quotes`
--
ALTER TABLE `quotes`
  MODIFY `quote_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `pref_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `warehouse_id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_customer` FOREIGN KEY (`cliente_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_citas_event_type` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cita_materiales`
--
ALTER TABLE `cita_materiales`
  ADD CONSTRAINT `fk_cita_materiales_cita` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_cita_materiales_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `cita_materiales_historial`
--
ALTER TABLE `cita_materiales_historial`
  ADD CONSTRAINT `fk_historial_cita` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_historial_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_cita` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `facturas_historial`
--
ALTER TABLE `facturas_historial`
  ADD CONSTRAINT `facturas_historial_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `material_stock_locations`
--
ALTER TABLE `material_stock_locations`
  ADD CONSTRAINT `material_stock_locations_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_stock_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos_factura`
--
ALTER TABLE `pagos_factura`
  ADD CONSTRAINT `pagos_factura_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `quotes`
--
ALTER TABLE `quotes`
  ADD CONSTRAINT `fk_quotes_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
