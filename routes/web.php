<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClienteController;
use App\Http\Controllers\Web\DetalleReservaController;
use App\Http\Controllers\Web\FacturaController;
use App\Http\Controllers\Web\FinanzasController;
use App\Http\Controllers\Web\HabitacionController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MetricasController;
use App\Http\Controllers\Web\PagoController;
use App\Http\Controllers\Web\ReservaController;
use App\Http\Controllers\Web\ReservaServicioController;
use App\Http\Controllers\Web\ServicioController;
use App\Http\Controllers\Web\UsuarioController;
use Illuminate\Support\Facades\Route;

// ── AUTH ──────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ── RUTAS PROTEGIDAS ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home.home');

    // Admin + Recepcionista
    Route::middleware('rol:admin,recepcionista')->group(function () {
        Route::get('/clientes',                   [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/create',            [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes/store',            [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/edit/{id}',         [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/update/{id}',       [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/destroy/{id}',   [ClienteController::class, 'destroy'])->name('clientes.destroy');

        Route::get('/habitaciones/verificar-numero', [HabitacionController::class, 'verificarNumero'])->name('habitaciones.verificar-numero');
        Route::get('/habitaciones',               [HabitacionController::class, 'index'])->name('habitaciones.index');
        Route::get('/habitaciones/create',        [HabitacionController::class, 'create'])->name('habitaciones.create');
        Route::post('/habitaciones/store',        [HabitacionController::class, 'store'])->name('habitaciones.store');
        Route::get('/habitaciones/edit/{id}',     [HabitacionController::class, 'edit'])->name('habitaciones.edit');
        Route::put('/habitaciones/update/{id}',   [HabitacionController::class, 'update'])->name('habitaciones.update');
        Route::delete('/habitaciones/destroy/{id}',[HabitacionController::class, 'destroy'])->name('habitaciones.destroy');
        Route::post('/habitaciones/mantenimiento/{id}', [HabitacionController::class, 'mantenimiento'])->name('habitaciones.mantenimiento');
        Route::post('/habitaciones/sacar-mantenimiento/{id}', [HabitacionController::class, 'sacarMantenimiento'])->name('habitaciones.sacar-mantenimiento');

        Route::get('/servicios',                  [ServicioController::class, 'index'])->name('servicios.index');
        Route::get('/servicios/create',           [ServicioController::class, 'create'])->name('servicios.create');
        Route::post('/servicios/store',           [ServicioController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/edit/{id}',        [ServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/update/{id}',      [ServicioController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/destroy/{id}',  [ServicioController::class, 'destroy'])->name('servicios.destroy');

        Route::get('/reservas',                   [ReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/create',            [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('/reservas/store',            [ReservaController::class, 'store'])->name('reservas.store');
        Route::get('/reservas/edit/{id}',         [ReservaController::class, 'edit'])->name('reservas.edit');
        Route::put('/reservas/update/{id}',       [ReservaController::class, 'update'])->name('reservas.update');
        Route::delete('/reservas/destroy/{id}',   [ReservaController::class, 'destroy'])->name('reservas.destroy');
        Route::post('/reservas/confirmar/{id}',   [ReservaController::class, 'confirmar'])->name('reservas.confirmar');
        Route::post('/reservas/cancelar/{id}',    [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
        Route::post('/reservas/completar/{id}',   [ReservaController::class, 'completar'])->name('reservas.completar');

        Route::get('/detalle_reserva',            [DetalleReservaController::class, 'index'])->name('detalle_reserva.index');
        Route::get('/reserva_servicio',           [ReservaServicioController::class, 'index'])->name('reserva_servicio.index');

        Route::get('/finanzas',                   [FinanzasController::class, 'index'])->name('finanzas.index');

        Route::get('/facturas',                   [FacturaController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/create',            [FacturaController::class, 'create'])->name('facturas.create');
        Route::post('/facturas/store',            [FacturaController::class, 'store'])->name('facturas.store');
        Route::post('/facturas/regenerar/{id}',   [FacturaController::class, 'regenerar'])->name('facturas.regenerar');

        Route::get('/pagos',                      [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/create',               [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos/store',               [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/pagos/edit/{id}',            [PagoController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/update/{id}',          [PagoController::class, 'update'])->name('pagos.update');
        Route::delete('/pagos/destroy/{id}',      [PagoController::class, 'destroy'])->name('pagos.destroy');
        Route::post('/pagos/procesar/{id}',       [PagoController::class, 'procesar'])->name('pagos.procesar');
        Route::post('/pagos/cancelar/{id}',       [PagoController::class, 'cancelar'])->name('pagos.cancelar');
        Route::post('/pagos/reembolsar/{id}',     [PagoController::class, 'reembolsar'])->name('pagos.reembolsar');
        Route::post('/pagos/pagar-reserva/{id}',  [PagoController::class, 'pagarReserva'])->name('pagos.pagar-reserva');
    });

    // Solo Admin
    Route::middleware('rol:admin')->group(function () {
        Route::get('/metricas', [MetricasController::class, 'index'])->name('metricas.index');

        Route::get('/usuarios',                   [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create',            [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios/store',            [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/edit/{id}',         [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/update/{id}',       [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/destroy/{id}',   [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });
});