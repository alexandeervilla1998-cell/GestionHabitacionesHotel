# Hotel Los Cracks — Sistema de Gestión Hotelera

Sistema web para la gestión interna de un hotel: reservas, habitaciones, clientes, servicios, facturación y pagos. Desarrollado en **Laravel 12** con interfaz **Tailwind CSS + Alpine.js**.

El sistema es de uso **exclusivo para personal del hotel** (administradores y recepcionistas) — no existe portal ni panel para clientes externos.

## Roles

| Rol | Acceso |
|---|---|
| **Admin** | Acceso total: gestión de habitaciones, clientes, reservas, servicios, facturas, pagos, métricas y usuarios del sistema. |
| **Recepcionista** | Gestión operativa diaria: habitaciones, clientes, reservas, servicios, facturas y pagos (sin acceso a usuarios ni métricas). |

## Módulos

- **Clientes** — registro de huéspedes (CRUD).
- **Habitaciones** — inventario, tipos (individual, doble, suite, familiar), estados (disponible, ocupada, mantenimiento) e imágenes.
- **Reservas** — creación de reservas por cliente/habitación con servicios adicionales, estados (pendiente, confirmada, cancelada, completada) y liberación automática al vencer la fecha de salida.
- **Servicios** — catálogo de servicios adicionales del hotel.
- **Facturación** — generación de facturas a partir de reservas confirmadas (incluye cálculo de impuestos).
- **Pagos** — registro y procesamiento de pagos asociados a facturas (efectivo, tarjeta, transferencia).
- **Finanzas / Métricas** — paneles de resumen financiero y estadísticas (solo admin para métricas).

## Stack técnico

- **Backend:** Laravel 12 (PHP 8.2+)
- **Base de datos:** MySQL
- **Frontend:** Tailwind CSS (vía CDN) + Alpine.js + Lucide Icons
- **Autenticación:** sesión basada en `usuarios` (sin registro público; los usuarios los crea un admin)

## Requisitos

- PHP >= 8.2 con extensiones habituales de Laravel (openssl, pdo_mysql, mbstring, etc.)
- Composer
- MySQL (por ejemplo, vía XAMPP)
- Node.js (opcional — no requerido para ejecutar la app, solo si se quiere usar el pipeline de Vite)

## Instalación

```bash
git clone <url-del-repositorio>
cd HabitacionesHotel-AFE
composer install
cp .env.example .env
php artisan key:generate
```

Configura la base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelloscracks
DB_USERNAME=root
DB_PASSWORD=
```

Ejecuta las migraciones y los datos de prueba:

```bash
php artisan migrate --seed
```

Esto crea:
- 1 usuario admin y 2 recepcionistas (ver `database/seeders/UsuarioSeeder.php`)
- 5 clientes de ejemplo
- 8 habitaciones de ejemplo
- 10 servicios de ejemplo

## Ejecutar el proyecto

```bash
php artisan serve
```

La app queda disponible en `http://127.0.0.1:8000`. Inicia sesión con cualquiera de los usuarios creados por el seeder (ver `UsuarioSeeder.php` para las credenciales de prueba).

## Tarea programada

El sistema incluye un comando de mantenimiento que libera automáticamente las habitaciones cuya reserva ya venció:

```bash
php artisan habitaciones:liberar-vencidas
```

Está registrado para ejecutarse diariamente vía el scheduler de Laravel (`bootstrap/app.php`). En producción, agrega el cron de Laravel:

```
* * * * * cd /ruta-al-proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## Base de datos

Motor: **MySQL**. El esquema completo (tablas, columnas, relaciones) está documentado en [database/README.md](database/README.md).

## Estructura relevante

```
app/
  Console/Commands/       Comandos artisan (liberación automática de habitaciones)
  Http/Controllers/Web/   Controladores del panel (CRUDs)
  Http/Controllers/Api/   Controladores de endpoints JSON usados por el frontend
  Http/Middleware/        Middleware de control de roles (RolMiddleware)
  Models/                 Modelos Eloquent
  Services/               Lógica de negocio (ej. HabitacionService)
resources/views/
  layouts/                Layouts base (autenticado y de login)
  partials/               Componentes reutilizables (header, alertas, etc.)
  <recurso>/              Vistas index/create/edit por cada módulo
public/css/hotel.css       Estilos y animaciones propias del panel
public/js/hotel-app.js     Interactividad del panel (Alpine/JS)
routes/web.php             Rutas del panel, agrupadas por rol
```

## Diseño

La interfaz usa una paleta índigo/cian y animaciones sutiles de entrada (fade, slide, scale) definidas en `public/css/hotel.css`, con la configuración de color centralizada en `resources/views/partials/head.blade.php`.
