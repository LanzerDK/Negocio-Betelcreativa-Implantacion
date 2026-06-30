# Betel Creativa — Technical Reference Document

## 1. Project Overview

**Betel Creativa** is a business management system for a Venezuelan creative events company. It handles users, materials/inventory, customers, appointments (citas), quotes, tasks, and warehouse storage. The system runs on **PHP 8+** (vanilla, no framework), **MySQL** via PDO, with **Composer** PSR-4 autoloading.

### Stack Summary
- **Backend:** PHP 8+ (no framework), MySQL 8+, PDO
- **Frontend:** Bootstrap 5 css only, custom CSS, Chart.js, FullCalendar, Font Awesome, Bootstrap Icons
- **Auth:** Session-based with CSRF tokens
- **Email:** Resend SDK (`resend/resend-php ^1.3`)
- **SMS:** Textbelt (optional)

---

## 2. Directory Structure

```
C:\laragon\www\Betelcreativa\
├── AGENTS.md                  # Agent guide (developer instructions)
├── composer.json              # PSR-4 autoloading config
├── .gitignore                 # Ignores vendor/, .env, logs/
├── Config/
│   ├── app.php                # App bootstrap: EnvLoader, constants, timezone, session start
│   ├── Database.php           # PDO singleton via Database::getConnection()
│   └── EnvLoader.php          # .env file parser (simple key=value)
├── Database/
│   └── schema.sql             # Full MySQL schema (12 tables + seeds)
├── docs/                      # Documentation
├── logs/                      # Log directory (gitignored)
├── Src/
│   ├── Controllers/           # 16 controller classes (static methods)
│   ├── Domain/                # 11 entity/model classes
│   ├── Infrastructure/        # 15 repository classes (PDO queries)
│   └── Helpers/               # 6 utility classes
├── Public/
│   ├── index.php              # Main page router
│   ├── .htaccess              # URL rewriting: /foo → index.php?views=foo
│   ├── router.php             # PHP built-in server support
│   ├── api/                   # REST API entry points (21 files + admin/)
│   ├── css/                   # 13 CSS files
│   ├── js/                    # 13 JS files
│   ├── assets/                # Vendor libs (Chart.js, FullCalendar, jsPDF, etc.)
│   └── images/                # Logo and background images
├── vendor/                    # Composer dependencies (gitignored)
└── Views/                     # 12 PHP view templates
```

---

## 3. Request Flow

```
Browser → URL → .htaccess → Public/index.php → ViewsModel whitelist → Views/{name}.php
                                                      ↕ (auth guard)
                                               API call → Public/api/{resource}.php
                                                          → Src/Controllers/{Controller}::method()
                                                          → Src/Infrastructure/{Repository}::query()
                                                          → ApiResponse::json()
```

### 3.1 Page Route Flow
```
1. Browser requests /foo
2. .htaccess rewrites to index.php?views=foo
3. index.php:
   a. Requires autoload + Config/app.php
   b. Starts session (SessionHelpers::start() or session_start())
   c. Checks ViewsModel whitelist → if not listed, 404
   d. If view is public (login, register, recoverPassword) → skip auth
   e. Else checks auth → if not authenticated → redirect to /login
   f. If first user and no users exist → redirect to /register
   g. Includes Views/{viewName}.php
```

### 3.2 API Route Flow
```
1. JS fetch() calls Public/api/materials.php?action=list
2. materials.php:
   a. Requires autoload + Config/app.php
   b. Calls SessionHelpers::requireAuth()
   c. Routes to MaterialController::list()
   d. Controller validates CSRF for mutation requests
   e. Returns JSON via ApiResponse::json()
```

### 3.3 PHP Built-in Dev Server Flow
```
php -S localhost:8000 Public/router.php
router.php:
  - If file exists at requested path → serve it directly
  - If URL starts with /api/ → include the matching api/*.php file
  - Else → include index.php (which checks views param)
```

---

## 4. Database Schema (12 Tables + Seeds)

### Entity-Relationship Diagram (Text-based)

```
users ──┬── password_resets
        ├── inventory_movements
        └── user_preferences

customers ──┬── citas ──┬── cita_materiales ──┬── materials ──┬── categories
            │           │                      │               └── material_stock_locations
            │           │                      │               └── locations
            │           └── quotes             │
            │                                  └── inventory_movements
            └── event_types (referenced by citas)

tasks (standalone)
settings (key-value config)
```

### 4.1 Table Definitions

| Table | Engine | Key Columns | Foreign Keys |
|-------|--------|-------------|--------------|
| `users` | InnoDB | user_id PK, username UNIQUE, email UNIQUE, password, security_code, role, is_active | - |
| `categories` | InnoDB | category_id PK, name, description, status, image_url | - |
| `materials` | InnoDB | material_id PK, material_code UNIQUE, name, price, cost_type, wholesale_qty, current_stock, image_url, category_id FK, material_type, supplier_id, current_location_id FK, is_active | categories(category_id) ON DELETE SET NULL |
| `customers` | InnoDB | customer_id PK, first_name, last_name, id_number, email, phone, address, client_type, source, notes, preferences, avatar, is_active | - |
| `citas` | InnoDB | id PK, cliente_id FK, fecha_hora_inicio, fecha_hora_fin, event_type_id FK, ubicacion, estado ENUM, estado_previo_cancelacion, fecha_hora_cancelacion, motivo_cancelacion, notas | customers(customer_id) ON DELETE CASCADE, event_types(id) ON DELETE SET NULL |
| `cita_materiales` | InnoDB | id PK, cita_id FK, material_id FK, cantidad_utilizada | citas(id) ON DELETE CASCADE, materials(material_id) ON DELETE CASCADE |
| `locations` | InnoDB | location_id PK, location_name, description | - |
| `material_stock_locations` | InnoDB | material_id + location_id (composite PK), quantity | materials(material_id) ON DELETE CASCADE, locations(location_id) ON DELETE CASCADE |
| `inventory_movements` | InnoDB | movement_id PK, material_id FK, user_id FK, action_type, quantity, reason, extra_note, origin_location_id, destination_location_id, movement_date | materials(material_id) ON DELETE CASCADE, users(user_id) ON DELETE SET NULL |
| `quotes` | InnoDB | quote_id PK, customer_id FK, title, description, value, status, event_date, notes | customers(customer_id) ON DELETE CASCADE |
| `tasks` | InnoDB | task_id PK, title, priority ENUM, status ENUM, completed_at | - |
| `password_resets` | InnoDB | reset_id PK, user_id FK, token, contact, contact_type, expires_at, is_used | users(user_id) ON DELETE CASCADE |
| `user_preferences` | InnoDB | pref_id PK, user_id FK UNIQUE, notify_low_stock, notify_appointments, notify_security, notify_reports | users(user_id) ON DELETE CASCADE |
| `settings` | InnoDB | setting_id PK, setting_key UNIQUE, setting_value, description | - |
| `event_types` | InnoDB | id PK, name, is_active | - |

### 4.2 Seed Data

**event_types** (9 initial):
```
Boda, Cumpleaños, Evento Corporativo, Quinceañero, Baby Shower, Bautizo, Graduación, Aniversario, Otro
```

**settings** (10 initial):
| Key | Value | Description |
|-----|-------|-------------|
| low_stock_threshold | 10 | Min stock before low warning |
| pagination_default | 10 | Rows per page |
| dashboard_refresh_interval | 30000 | Dashboard refresh in ms |
| password_min_length | 6 | Min password length |
| appointment_default_duration | 60 | Default duration in minutes |
| business_hours_start | 08:00 | Opening time |
| business_hours_end | 18:00 | Closing time |
| working_days | 1,2,3,4,5,6 | Working days |
| backup_frequency | weekly | Backup frequency |
| log_retention_days | 90 | Log retention in days |

---

## 5. Configuration Files

### 5.1 Config/EnvLoader.php
Reads `.env` file (key=value format, supports `#` comments). Values cached in `$_ENV`. Required vars: `DB_SERVER`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`. Optional: `APP_URL`, `APP_NAME`, `APP_SESSION_NAME`, `APP_TIMEZONE`, `RESEND_API_KEY`, `RESEND_FROM_EMAIL`, `TEXTBELT_KEY`.

### 5.2 Config/Database.php
PDO singleton via `Database::getConnection()`. Reads DB credentials from `$_ENV`. Connection: `mysql:host={DB_SERVER};dbname={DB_NAME};charset=utf8mb4`. Error mode: `PDO::ATTR_ERRMODE_EXCEPTION`.

### 5.3 Config/app.php
Bootstrap file included by all entry points. Responsibilities:
1. Load EnvLoader
2. Define `APP_URL` constant (auto-detect if not in .env)
3. Define `APP_NAME` constant
4. Call `SessionHelpers::start()` with custom session name from `APP_SESSION_NAME` (default `BetEl`)
5. Set timezone from `APP_TIMEZONE` (default `America/Caracas`)
6. Set error reporting

---

## 6. Src/Domain — Models (11 Classes)

All models are plain PHP objects with typed properties and `jsonSerialize()` for JSON responses.

| Class | Properties |
|-------|------------|
| `ViewsModel` | Static whitelist of view names (lines 11-20), static `isPublicPath()`, static `viewPath()` |
| `UserModel` | user_id, first_name, last_name, username, email, id_number, password, security_code, phone, avatar, role, is_active, checkin_time, created_at, updated_at |
| `CategoryModel` | category_id, name, description, status, image_url, created_at, updated_at |
| `MaterialModel` | material_id, material_code, name, price, cost_type, wholesale_qty, current_stock, image_url, category_id, material_type, supplier_id, current_location_id, is_active, created_at, updated_at |
| `CustomerModel` | customer_id, first_name, last_name, id_number, email, phone, address, client_type, source, notes, preferences, avatar, is_active, created_at, updated_at |
| `AppointmentModel` | id, cliente_id, fecha_hora_inicio, fecha_hora_fin, event_type_id, ubicacion, estado, estado_previo_cancelacion, fecha_hora_cancelacion, motivo_cancelacion, notas, created_at, updated_at |
| `CitaMaterialModel` | id, cita_id, material_id, cantidad_utilizada, created_at |
| `EventTypeModel` | id, name, is_active, created_at, updated_at |
| `LocationModel` | location_id, location_name, description |
| `InventoryMovementModel` | movement_id, material_id, user_id, action_type, quantity, reason, extra_note, origin_location_id, destination_location_id, movement_date |
| `TaskModel` | task_id, title, priority, status, created_at, completed_at |

---

## 7. Src/Infrastructure — Repositories (15 Classes)

All repositories use `Database::getConnection()` for PDO access. The legacy `Connection.php` in `Infrastructure/` exists but is deprecated.

| Repository | Key Methods |
|------------|-------------|
| `UserRepository` | findByUsername(), findByEmail(), findById(), create(), updateLastLogin(), updatePassword(), updateProfile(), updateAvatar(), searchUsers(), countUsers() |
| `CategoryRepository` | findAll(), findById(), create(), update(), toggleStatus(), countMaterialsByCategory() |
| `MaterialRepository` | findAll(), findById(), findByCode(), create(), update(), toggleActive(), updateStock(), updateLocation(), getLowStock(), countByCategory(), search() |
| `CustomerRepository` | findAll(), findById(), create(), update(), toggleActive(), search(), getStats(), countByType() |
| `AppointmentRepository` | findAll(), findById(), create(), update(), cancel(), restore(), findByDateRange(), getCalendarEvents(), getUpcoming(), getStats() |
| `CitaMaterialRepository` | findByCitaId(), save(), deleteByCitaId(), descontarStock(), restaurarStock(), verificarStockDisponible() |
| `EventTypeRepository` | findAll(), findById(), create(), update(), toggleActive() |
| `LocationRepository` | findAll(), findById(), create(), update(), deleteShelf(), findZonesWithShelves() |
| `InventoryMovementRepository` | findAll(), create(), findByMaterial(), findByDateRange(), getMovementsStats() |
| `DashboardRepository` | countActiveMaterials(), countLowStock(), countOutOfStock(), countPendingAppointments(), countActiveCustomers(), findLowStockDetails(), findOutOfStockDetails(), findPendingAppointments(), findUpcomingEvents(), countEventsByType() |
| `ReportRepository` | inventoryByCategory(), movements(), income(), purchases() |
| `SettingsRepository` | get(), set(), getAll() |
| `TaskRepository` | findAllPending(), findAllCompleted(), create(), complete(), delete() |
| `UserPreferenceRepository` | findByUserId(), save() |
| `Connection` | Legacy class (deprecated — uses constants, not the singleton pattern) |

---

## 8. Src/Controllers — Business Logic (16 Classes)

All controllers use **static methods** and return JSON via `ApiResponse`. They validate CSRF tokens on mutation requests via `CsrfHelper::validateRequestOrFail()`.

| Controller | Methods | Description |
|------------|---------|-------------|
| `ControllerLogin` | login(), logout(), checkField() | Authenticate user, destroy session, field availability check |
| `ControllerRegister` | register() | Create new user with security_code validation |
| `ControllerUser` | getProfile(), updateProfile(), updateAvatar(), updatePassword() | User profile management |
| `ControllerAdminUsers` | list(), get(), updateRole(), toggleActive() | Admin user management |
| `DashboardController` | handleRequest() | Returns stats, alerts, upcomingEvents, eventTypeDistribution |
| `AppointmentController` | handleRequest() → list/getById/create/update/cancel/restore | CRUD for citas with state engine and material assignment |
| `CustomerController` | handleRequest() → list/getById/create/update/toggle/search | Client CRUD with inline validation |
| `MaterialController` | handleRequest() → list/getById/create/update/toggle | Material CRUD with stock validation |
| `CategoryController` | handleRequest() → list/getById/create/update/toggle | Category CRUD with material-linking guard |
| `EventTypeController` | handleRequest() → list/create/update/toggle | Event type management |
| `LocationController` | handleRequest() → list/createShelf/deleteShelf | Warehouse shelf management |
| `StorageController` | handleRequest() → getInventory/getHistory/adjustStock/moveMaterial | Inventory adjustments and movements |
| `TaskController` | handleRequest() → list/create/complete/delete | Task management |
| `ControllerReport` | inventory(), movements(), income(), purchases() | Report generation |
| `ControllerSystem` | getSettings(), updateSettings(), getSystemInfo() | System configuration |
| `ViewsController` | render() | Renders whitelisted view files |

---

## 9. Src/Helpers — Utility Classes (6 Classes)

| Helper | Key Features |
|--------|--------------|
| `ApiResponse` | Static methods: `json($data)`, `success($data)`, `error($msg, $code)`, `created($data)` — all set headers and output JSON |
| `CsrfHelper` | `validateRequestOrFail()` (checks `X-CSRF-Token` header or `csrf_token` POST field against `$_SESSION['csrf_token']`) |
| `SessionHelpers` | `start()` (custom session name, httponly, SameSite=Lax), `requireAuth()` (401 if not authenticated), `setAlert()`, `getAlert()` |
| `Logger` | `info()`, `warning()`, `error()` — writes to `logs/app-YYYY-MM-DD.log` with timestamps |
| `EmailService` | Uses Resend PHP SDK; `send($to, $subject, $html)` |
| `SmsService` | Uses Textbelt API; `send($phone, $message)` |

---

## 10. API Endpoints

All APIs return JSON. Base path: `/Public/api/{resource}.php`

### 10.1 Page Routes
| Route | View File | Auth Required | Description |
|-------|-----------|---------------|-------------|
| `/` or `/dashboard` | `dashboard.php` | Yes | Main dashboard |
| `/login` | `login.php` | No | Login page |
| `/register` | `register.php` | No | Registration (gated by security code) |
| `/recoverPassword` | `recoverPassword.php` | No | Password recovery (3-step wizard) |
| `/materials` | `materials.php` | Yes | Material management |
| `/quotes` | `quotes.php` | Yes | Appointment/Citas management |
| `/category` | `category.php` | Yes | Category management |
| `/customers` | `customers.php` | Yes | Customer management |
| `/storage` | `storage.php` | Yes | Warehouse/inventory management |
| `/reports` | `reports.php` | Yes | Reports and statistics |
| `/config` | `config.php` | Yes | User configuration and admin panel |
| `/logout` | (inline in index.php) | Yes | Logout + redirect |
| 404 | `404.php` | No | Error page |

### 10.2 Whitelist
Defined in `Src/Domain/ViewsModel.php:11-20`: `dashboard, login, register, recoverPassword, materials, quotes, category, customers, storage, reports, config`

### 10.3 REST API Endpoints

| API File | Actions | Controller Used |
|----------|---------|-----------------|
| `api/login.php` | POST — login | ControllerLogin::login() |
| `api/register.php` | POST — register | ControllerRegister::register() |
| `api/logout.php` | POST — logout | ControllerLogin::logout() |
| `api/check-field.php` | GET — check field availability | ControllerLogin::checkField() |
| `api/recover.php` | POST — send recovery code, verify, reset | Password recovery helpers |
| `api/users.php` | GET profile, PUT update | ControllerUser |
| `api/appointments.php` | GET list/getById, POST create, PUT update/cancel/restore | AppointmentController |
| `api/customers.php` | GET list/search/getById, POST create, PUT update/toggle | CustomerController |
| `api/materials.php` | GET list/getById, POST create, PUT update/toggle | MaterialController |
| `api/categories.php` | GET list/getById, POST create, PUT update/toggle | CategoryController |
| `api/event-types.php` | GET list, POST create, PUT update/toggle | EventTypeController |
| `api/locations.php` | GET list, POST createShelf, DELETE delete | LocationController |
| `api/storage.php` | GET inventory/history, POST adjust/move | StorageController |
| `api/dashboard.php` | GET stats/alerts/events/distribution | DashboardController |
| `api/tasks.php` | GET list, POST create, PUT complete, DELETE | TaskController |
| `api/reports.php` | GET inventory/movements/income/purchases | ControllerReport |
| `api/settings.php` | GET/PUT system settings | ControllerSystem |
| `api/upload.php` | POST file upload | Upload handler |
| `api/ping.php` | GET health check | Simple pong response |
| `api/debug-fetch.php` | GET debug info | Debug endpoint |
| `api/test-post.php` | POST test | Test endpoint |
| `api/admin/users.php` | GET list/get, PUT role/toggle-active | ControllerAdminUsers |

---

## 11. JavaScript Modules (13 Files)

| File | View | Key Functions |
|------|------|---------------|
| `inicio.js` | login | Form validation, password toggle, secret modal (3-click logo), 90s timer, async login via API |
| `register.js` | register | Field validation (username, email, phone format), password strength meter, password confirmation, async registration |
| `recuperar.js` | recoverPassword | 3-step wizard: step1 send code, step2 6-digit code input (auto-advance), step3 new password validation |
| `dashboard.js` | dashboard | Stat loading, alerts rendering, task list (flip card), upcoming events, Chart.js charts (sales, event types), task modal |
| `categorias.js` | category | CRUD categories (cards with grid), search filter, toggle active/inactive, card-based UI |
| `materiales.js` | materials | CRUD materials (card grid), category filter, search, auto-code generation, cost type toggle (unit/wholesale), toggle active/inactive, modal.js integration |
| `customers.js` | customers | Client list sidebar, search/filter, detail panel with tabs (info, events, preferences), create/edit modals, toggle active, pagination of preferences |
| `citas.js` | quotes | **Full-featured module**: Table + Calendar (FullCalendar) views, CRUD modals with material assignment sidebar, pagination, status filters, date range filters, event type management modal, history section |
| `config.js` | config | Tab navigation (profile, notifications, security, system, users), profile update, avatar upload, notification toggle switches, password change form, system settings CRUD, admin user management table with role/status toggles |
| `almacen.js` | storage | Inventory table with filters, warehouse layout grid (zones/shelves), pagination, history tab, adjust stock modal, move material modal, add shelf modal, add material quick-entry modal |
| `Reportes.js` | reports | Report card selection, filterable report views (inventory, movements, income, purchases), Chart.js charts, data tables, PDF export (jsPDF), Excel export (SheetJS), print support |
| `modal.js` | (shared) | Custom modal system: `Modal.open(id)`, `Modal.close(id)`, backdrop management, focus trapping, ESC key support |
| `toast.js` | (shared) | Custom toast notification system: `callApi(url, options)`, `toast(msg, type)` with auto-dismiss and animations |

### 11.1 Global JS Constants
Injected by PHP into each view:
```javascript
const APP_URL = 'http://localhost/Betelcreativa/';
const CSRF_TOKEN = 'abc123...';  // $_SESSION['csrf_token']
// config.php also injects:
const USER_ID = 1;               // $_SESSION['user_id']
const USER_ROLE = 'admin';       // $_SESSION['role']
```

---

## 12. CSS Files (13 Files + Base)

| File | Lines | Purpose |
|------|-------|---------|
| `_base.css` | 1233 | **Global styles**: CSS variables (primary blue #002266, gold #D4AF37), reset, header, sidebar menu, buttons, forms, modals, badges, tabs, pagination, filters, cards, tables, user dropdown, spinners, toast notifications, responsive breakpoints |
| `dashboardStyle.css` | 459 | Dashboard grid, stat cards, alert cards, card flip animation, task list, event list |
| `materialStyle.css` | 472 | Material grid (auto-fill 300px), material cards, disabled overlay, category sidebar |
| `quoteStyle.css` | 991 | **Largest CSS**: Appointment table grid (7 columns), status badges, FullCalendar overrides, modal wrapper, material list, history section |
| `categoryStyle.css` | 454 | Category grid, category cards, stats section, disabled overlay |
| `customerStyle.css` | 679 | Client sidebar/detail layout, avatar, info grid, event timeline, preferences grid, tabs |
| `storageStyle.css` | 694 | Inventory overview, warehouse layout grid, materials table, history table, sidebar filters |
| `reportStyle.css` | 438 | Report cards grid, report content sections, filter grids, stats grid, tables, print styles |
| `loginStyle.css` | 492 | Glassmorphism login container, animated logo glow, form inputs, secret modal, responsive |
| `registerStyle.css` | 322 | Glassmorphism register container, form grid, password strength bar, gradient text title |
| `recoverPasswordStyle.css` | 363 | Glassmorphism recovery container, 3-step indicator, 6-digit code input grid |
| `configStyle.css` | 412 | Profile card, avatar upload, tab navigation, form grid, notification toggles, security items |
| `404Style.css` | 84 | Minimal 404 page with gold-bordered card |

### 12.1 CSS Architecture Notes
- **Design tokens**: CSS custom properties in `:root` (`--primary`, `--secondary`, `--gold`, `--accent`, etc.)
- **Gold border pattern**: Cards/containers use `::before` pseudo-element with `mask-composite: exclude` for 2px gold gradient border
- **Status colors**: Normalized Spanish names (`pendiente`, `en-proceso`, `en-progreso`, `terminado`, `cancelado`)
- **Modals**: Two systems — `.modal-overlay` (custom) and `.modal` (Bootstrap-style native)
- **Responsive**: Breakpoints at 480px, 768px, 992px, 1024px, 1200px

---

## 13. Views Templates (12 Files)

| View | Lines | Description |
|------|-------|-------------|
| `dashboard.php` | 242 | Header, sidebar nav, 4 stat cards, alerts, 4-grid dashboard, task modal |
| `category.php` | 193 | Search + stats left column, category cards grid right column, category modal |
| `customers.php` | 341 | Left sidebar (search/filter/client list), right detail panel, create/edit modals |
| `materials.php` | 271 | Filter sidebar, material cards grid, create/edit modals with full form |
| `quotes.php` | 429 | **Most complex**: Filter panel, table view (7 columns), calendar (FullCalendar), pagination, history, create/edit modals + material panel sidebar, cancel modal, event type management modal |
| `reports.php` | 505 | 4 report cards (inventory, movements, income, purchases), filterable sections, export buttons |
| `storage.php` | 446 | Overview cards, tab bar (inventory/history), filter sidebar, warehouse grid, materials table, pagination, modals |
| `login.php` | 98 | Glassmorphism login form, secret modal (3 clicks on logo), CSRF token |
| `register.php` | 152 | Registration form with all fields, password strength bar |
| `recoverPassword.php` | 94 | 3-step recovery wizard (contact → 6-digit code → new password) |
| `config.php` | 468 | Profile card, tabs (profile/notifications/security/system/users), form grids, notification toggles, system settings |
| `404.php` | 25 | Simple error page |

### 13.1 Common View Pattern
```html
<body>
  <div class="app-container">
    <header class="app-header"> ... </header>
    <nav class="main-menu"> ... </nav>
    <main class="main-content">
      <!-- Module-specific content -->
    </main>
  </div>
  <!-- Modals -->
  <script>
    const APP_URL = '<?php echo APP_URL; ?>';
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
  </script>
  <script src="<?=APP_URL?>Public/js/toast.js"></script>
  <script src="<?=APP_URL?>Public/js/{module}.js"></script>
</body>
```

---

## 14. Authentication & Security

### 14.1 Session Configuration
- Custom session name from `APP_SESSION_NAME` (default: `BetEl`)
- httponly + SameSite=Lax
- Session start in `Config/app.php` via `SessionHelpers::start()`

### 14.2 Auth Guard
- **Page routes**: `Public/index.php` checks `$_SESSION['user_id']` — redirects to `/login` if missing
- **API endpoints**: Each API file calls `SessionHelpers::requireAuth()` which returns 401 JSON if not authenticated

### 14.3 CSRF Protection
- Token stored in `$_SESSION['csrf_token']`
- Injected into views as `CSRF_TOKEN` JS constant
- Sent via `X-CSRF-Token` header or `csrf_token` POST field
- Validated by `CsrfHelper::validateRequestOrFail()` on all state-changing API methods

### 14.4 Password Security
- Passwords hashed with `password_hash(PASSWORD_DEFAULT)`
- Minimum length configured in settings (default 6)
- Recovery via 3-step wizard with 6-digit code (sent via email or SMS)

### 14.5 Registration Security
- Gated by a hard-coded security code (pattern: `XBX-89X-XsA`)
- On login page: 3 clicks on logo opens a secret modal for code entry
- Rate-limited with a 90-second cooldown timer

---

## 15. Key Modules (Detailed)

### 15.1 Citas (Appointments) — The Core Module

**Data Flow:**
1. **Create**: Form with client, datetime range, event type, location, notes + material assignment panel
   - Material panel fetches active materials via `api/materials.php`
   - User selects materials from dropdown, sets quantity, adds to list
   - Stock validation: cannot exceed `current_stock`, min quantity = 1
   - On submit, creates cita + cita_materiales records
2. **Edit**: Same form pre-populated, materials loaded from `api/appointments.php?id=X`
3. **State machine**: `En Proceso → Pendiente → En Progreso → Terminado` or any → `Cancelado`
4. **Cancel**: Soft-delete with motivo + timestamp, stores prev state for potential restore (3-day window)
5. **Restore**: Checks 3-day window + stock availability, rolls back state
6. **Calendar**: FullCalendar integration with color-coded events by estado
7. **History**: Dedicated section showing cancelled citas with restore capability

**Material Consumption Flow:**
```
User assigns materials → cita_materiales stores {cita_id, material_id, cantidad_utilizada}
On cancelación → restore materials.current_stock (increment by cantidad_utilizada)
On restore → deduct from materials.current_stock again
```

### 15.2 Warehouse (Almacén/Storage)

**Structure:**
```
Warehouse
  └── Zones (e.g., "Zona Flores")
        └── Shelves (e.g., "F1", "F2")
              └── Materials (via material_stock_locations)
```

**Operations:**
- **Adjust stock**: Entry (add) or Exit (reduce) with reason (compra, venta, devolucion, perdida, ajuste, otro)
- **Move material**: Transfer between locations with quantity and reason
- **History**: Full audit trail of all movements with origin/destination

### 15.3 Reports Module

Four report types:
1. **Inventory**: Filter by category, stock status, sort order; doughnut chart + stats + table
2. **Movements**: Filter by date range, type (entry/exit); bar chart + stats + table
3. **Income**: Filter by date range; line chart + stats + table (counts completed citas per month — monetary data pending)
4. **Purchases**: Filter by date range; line chart + stats + table

Export capabilities: PDF (jsPDF), Excel (SheetJS), Print

---

## 16. Notable Patterns & Conventions

### 16.1 Gold Border Pattern
Used extensively across cards and containers:
```css
.element::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  padding: 2px;
  background: var(--gold-gradient);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}
```

### 16.2 CSRF Token Injection Pattern
```php
// In view template:
<script>
  const APP_URL = '<?php echo APP_URL; ?>';
  const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
</script>

// In JS API call:
fetch(APP_URL + 'api/resource.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
  body: JSON.stringify(data)
})

// In PHP controller:
CsrfHelper::validateRequestOrFail();
```

### 16.3 Pagination Pattern
- Server returns: `{ success: true, data: { data: [...], total: N, page: P, limit: L } }`
- Frontend renders page buttons dynamically
- Reused across: citas, storage, config (users)

### 16.4 Disabled/Inactive State Pattern
Cards with `.inhabilitado` class:
- `transform: scale(0.98)`
- `border: 2px solid var(--danger)`
- `::after` overlay: `rgba(0,0,0,0.45)`
- Action buttons remain visible with `z-index: 3`

### 16.5 Validation Layers
- **Client-side**: JS validation before API call (inline + toast)
- **Backend**: PHP controller validates input + CSRF
- **Database**: Constraints (ENUM, NOT NULL, FOREIGN KEY, UNIQUE)

---

## 17. composer.json

```json
{
  "name": "betel-creativa/betel-creativa",
  "autoload": {
    "psr-4": {
      "BetelCreativa\\": "Src/",
      "BetelCreativa\\Config\\": "Config/"
    }
  },
  "require": {
    "resend/resend-php": "^1.3"
  }
}
```

---

## 18. Git & File Structure Notes

- `.gitignore` ignores: `vendor/`, `.env`, `logs/*`, `.DS_Store`, `Thumbs.db`
- `.env.example` does not exist (must be created from `EnvLoader.php` keys)
- Database migrations are documented as SQL comments in `schema.sql` (no migration tool)

---

## 19. Total File Count

| Category | Count |
|----------|-------|
| PHP Controllers | 16 |
| Domain Models | 11 |
| Infrastructure Repos | 15 |
| Helpers | 6 |
| API Endpoints | 21 |
| Views | 12 |
| CSS Files | 13 |
| JS Files | 13 |
| Config Files | 3 |
| Database Schema | 1 |
| Other Config | 3 (composer.json, .gitignore, AGENTS.md) |
| **Total** | **~114 files** |

---

## 20. Development Commands

```bash
# Start PHP built-in dev server
php -S localhost:8000 Public/router.php

# Install/update dependencies
composer install
composer update

# Regenerate autoloader
composer dump-autoload
```

---

## 21. Recent Completed Work (This Session)

- **Block past dates in datetime-local** — `min` set to current datetime in `abrirModalNueva()`
- **Material panel: side-by-side** — `.modal-wrapper` with `display: flex; gap: 24px` replaces overlay
- **Material select + "+" button** — dropdown + add button replaces full material list
- **Fixed field names** — API field mapping corrected (`material_code` → `code`, `current_stock` → `stock`)
- **6-item scroll limit** — `.material-list` `max-height` for 6 items + scrollbar
- **Total count** — "Materiales totales: N" above confirm button
- **Duplicate prevention** — blocks re-adding same material, disables in select
- **API returns materiales** — `getById` includes `materiales` from `CitaMaterialRepository`
- **Stock validation on confirm** — checks stock before closing panel
- **Client filter: only active** — dropdown shows only `isActive === true`
- **Materials required** — `validarDatosCita()` rejects 0 materials
- **Cantidad min=1** — input `min="1"`, `< 1` triggers warning + blocks submit/close
- **Ubicación ellipsis** — `text-overflow: ellipsis` on table columns
- **Estado colors** — normalized Spanish CSS classes with correct palette
- **Table header alignment** — `margin-top: 5px` on `.appointments-table`
- **Calendar event classes** — `classNameEstado()` for normalized `fc-event-*` class names
- **Material module validation** — negative cost/wholesale_qty blocked with toast
- **Negative close protection** — material panel + main modal cannot close with invalid quantities
- **Dashboard event type chart** — now fetches real data from `countEventsByType()` query
