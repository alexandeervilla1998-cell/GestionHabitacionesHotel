<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HabitacionController extends Controller
{
    /**
     * Devuelve todos los registros
     * de la tabla habitaciones
     *
     * @return response - JSON - los datos de la tabla
     */
    function obtenerTodos() {
        try {
            // all() Equivale a:
            // SELECT * FROM habitaciones
            $list = Habitacion::all();
            return response()->json($list);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cargar los datos: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerPorId(int $id) {
        try {
            // SELECT * FROM habitaciones WHERE id = ?
            $data = Habitacion::find($id);
            return response()->json($data);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al obtener la habitación: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function eliminarPorId(int $id) {
        try {
            $data = Habitacion::find($id);
            if (!$data) {
                $message = ["message" => "Habitación no encontrada", "status" => false];
                return response()->json($message, 404);
            }
            
            // Verificar si tiene reservas activas
            if ($data->detalleReservas()->whereHas('reserva', function ($query) {
                $query->where('estado', 'confirmada');
            })->exists()) {
                $message = ["message" => "No se puede eliminar la habitación. Tiene reservas activas.", "status" => false];
                return response()->json($message, 422);
            }

            // Eliminar imagen si existe
            if ($data->imagen) {
                Storage::disk('public')->delete($data->imagen);
            }
            
            // Eliminar habitación
            $data->delete();
            $message = ["message" => "Dato eliminado", "status" => true];
            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al eliminar la habitación: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
    
    function actualizarPorId(Request $request) {
        try {
            // Obtener ID de la habitación a actualizar
            $id = $request->id;

            $data = Habitacion::find($id);
            if (!$data) {
                $message = ["message" => "Habitación no encontrada", "status" => false];
                return response()->json($message, 404);
            }

            $data->numero = $request->numero ?? $data->numero;
            $data->tipo = $request->tipo ?? $data->tipo;
            $data->precio_por_noche = $request->precio_por_noche ?? $data->precio_por_noche;
            $data->estado = $request->estado ?? $data->estado;
            $data->activo = $request->activo ?? $data->activo;

            // Manejar actualización de imagen
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($data->imagen) {
                    Storage::disk('public')->delete($data->imagen);
                }

                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('habitaciones', $nombreImagen, 'public');
                $data->imagen = $ruta;
            }

            // Actualizar datos de la habitación
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato actualizado", "status" => true];
            } else {
                $message = ["message" => "Dato no actualizado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al actualizar la habitación: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function crear(Request $request) {
        try {
            // Crear nueva habitación
            $data = new Habitacion();
            $data->numero = $request->input('numero');
            $data->tipo = $request->input('tipo');
            $data->precio_por_noche = $request->input('precio_por_noche');
            $data->estado = $request->input('estado') ?? 'disponible';
            $data->activo = $request->input('activo') ?? true;

            // Manejar subida de imagen
            try {
                if ($request->hasFile('imagen')) {
                    $imagen = $request->file('imagen');
                    $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                    $ruta = $imagen->storeAs('habitaciones', $nombreImagen, 'public');
                    $data->imagen = $ruta;
                }
            } catch (\Exception $e) {
                // Si hay error con la imagen, continuar sin ella
            }

            // Guardar nueva habitación
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato insertado", "status" => true];
            } else {
                $message = ["message" => "Dato no insertado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al crear la habitación: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerDisponibles(Request $request) {
        try {
            $query = Habitacion::disponibles()->activas();

            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->has('fecha_entrada') && $request->has('fecha_salida')) {
                // Excluir habitaciones ocupadas en las fechas solicitadas
                $habitacionesOcupadas = Habitacion::whereHas('detalleReservas', function ($query) use ($request) {
                    $query->whereHas('reserva', function ($query) use ($request) {
                        $query->where('estado', 'confirmada')
                              ->where(function ($q) use ($request) {
                                  $q->whereBetween('fecha_entrada', [$request->fecha_entrada, $request->fecha_salida])
                                    ->orWhereBetween('fecha_salida', [$request->fecha_entrada, $request->fecha_salida])
                                    ->orWhere(function ($q) use ($request) {
                                        $q->where('fecha_entrada', '<=', $request->fecha_entrada)
                                          ->where('fecha_salida', '>=', $request->fecha_salida);
                                    });
                              });
                    });
                })->pluck('id');

                $habitacionesDisponibles = $query->whereNotIn('id', $habitacionesOcupadas)->get();
            } else {
                $habitacionesDisponibles = $query->get();
            }

            return response()->json($habitacionesDisponibles);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al obtener habitaciones disponibles: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
}
