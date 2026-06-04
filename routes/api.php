<?php

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\HabitacionController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\ReservaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de Usuarios
Route::get('/usuario/obtener/todos', function () {
    $controller = new UsuarioController();
    $data = $controller->obtenerTodos();
    return $data;
});

Route::get('/usuario/obtener/{id}', function ($id) {
    $controller = new UsuarioController();
    $data = $controller->obtenerPorId($id);
    return $data;
});

Route::delete('/usuario/eliminar/{id}', [UsuarioController::class, 'eliminarPorId']);
Route::put('/usuario/actualizar', [UsuarioController::class, 'actualizarPorId']);
Route::post('/usuario/crear', [UsuarioController::class, 'crear']);

// Rutas de Habitaciones
Route::get('/habitacion/obtener/todos', function () {
    $controller = new HabitacionController();
    $data = $controller->obtenerTodos();
    return $data;
});

Route::get('/habitacion/obtener/{id}', function ($id) {
    $controller = new HabitacionController();
    $data = $controller->obtenerPorId($id);
    return $data;
});

Route::delete('/habitacion/eliminar/{id}', [HabitacionController::class, 'eliminarPorId']);
Route::put('/habitacion/actualizar', [HabitacionController::class, 'actualizarPorId']);
Route::post('/habitacion/crear', [HabitacionController::class, 'crear']);
Route::get('/habitacion/disponibles', [HabitacionController::class, 'obtenerDisponibles']);

// Rutas de Servicios
Route::get('/servicio/obtener/todos', function () {
    $controller = new ServicioController();
    $data = $controller->obtenerTodos();
    return $data;
});

Route::get('/servicio/obtener/{id}', function ($id) {
    $controller = new ServicioController();
    $data = $controller->obtenerPorId($id);
    return $data;
});

Route::delete('/servicio/eliminar/{id}', [ServicioController::class, 'eliminarPorId']);
Route::put('/servicio/actualizar', [ServicioController::class, 'actualizarPorId']);
Route::post('/servicio/crear', [ServicioController::class, 'crear']);
Route::get('/servicio/activos', [ServicioController::class, 'obtenerActivos']);

// Rutas de Reservas
Route::get('/reserva/obtener/todos', function () {
    $controller = new ReservaController();
    $data = $controller->obtenerTodos();
    return $data;
});

Route::get('/reserva/obtener/{id}', function ($id) {
    $controller = new ReservaController();
    $data = $controller->obtenerPorId($id);
    return $data;
});

Route::delete('/reserva/eliminar/{id}', [ReservaController::class, 'eliminarPorId']);
Route::put('/reserva/actualizar', [ReservaController::class, 'actualizarPorId']);
Route::post('/reserva/crear', [ReservaController::class, 'crear']);
Route::post('/reserva/{id}/confirmar', [ReservaController::class, 'confirmar']);
Route::post('/reserva/{id}/cancelar', [ReservaController::class, 'cancelar']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
