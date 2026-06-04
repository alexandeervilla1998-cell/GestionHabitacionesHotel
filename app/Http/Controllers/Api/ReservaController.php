<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\DetalleReserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Devuelve todos los registros
     * de la tabla reservas
     *
     * @return response - JSON - los datos de la tabla
     */
    function obtenerTodos() {
        try {
            // all() Equivale a:
            // SELECT * FROM reservas
            $list = Reserva::with(['usuario', 'detalleReservas.habitacion', 'servicios'])->get();
            return response()->json($list);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cargar los datos: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function obtenerPorId(int $id) {
        try {
            // SELECT * FROM reservas WHERE id = ?
            $data = Reserva::with(['usuario', 'detalleReservas.habitacion', 'servicios'])->find($id);
            return response()->json($data);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al obtener la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function eliminarPorId(int $id) {
        try {
            $data = Reserva::find($id);
            if (!$data) {
                $message = ["message" => "Reserva no encontrada", "status" => false];
                return response()->json($message, 404);
            }
            
            // Verificar si está confirmada o completada
            if (in_array($data->estado, ['confirmada', 'completada'])) {
                $message = ["message" => "No se puede eliminar una reserva confirmada o completada", "status" => false];
                return response()->json($message, 422);
            }

            return DB::transaction(function () use ($data) {
                // Eliminar detalles y servicios
                $data->detalleReservas()->delete();
                $data->servicios()->delete();
                
                // Eliminar factura si existe
                if ($data->factura) {
                    $data->factura->pagos()->delete();
                    $data->factura()->delete();
                }

                // Eliminar reserva
                $data->delete();
                $message = ["message" => "Dato eliminado", "status" => true];
                return response()->json($message);
            });
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al eliminar la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
    
    function actualizarPorId(Request $request) {
        try {
            // Obtener ID de la reserva a actualizar
            $id = $request->id;

            $data = Reserva::find($id);
            if (!$data) {
                $message = ["message" => "Reserva no encontrada", "status" => false];
                return response()->json($message, 404);
            }

            // Validar que se pueda modificar
            if (in_array($data->estado, ['confirmada', 'completada']) && 
                (isset($request->fecha_entrada) || isset($request->fecha_salida))) {
                $message = ["message" => "No se pueden modificar las fechas de una reserva confirmada o completada", "status" => false];
                return response()->json($message, 422);
            }

            $data->usuario_id = $request->usuario_id ?? $data->usuario_id;
            $data->fecha_entrada = $request->fecha_entrada ?? $data->fecha_entrada;
            $data->fecha_salida = $request->fecha_salida ?? $data->fecha_salida;
            $data->estado = $request->estado ?? $data->estado;
            $data->activo = $request->activo ?? $data->activo;

            // Actualizar datos de la reserva
            $isOK = $data->save();

            $message = [];

            if ($isOK) {
                $message = ["message" => "Dato actualizado", "status" => true];
            } else {
                $message = ["message" => "Dato no actualizado", "status" => false];
            }

            return response()->json($message);
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al actualizar la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function crear(Request $request) {
        try {
            return DB::transaction(function () use ($request) {
                // Validar fecha
                if ($request->fecha_salida <= $request->fecha_entrada) {
                    $message = ["message" => "La fecha de salida debe ser posterior a la fecha de entrada", "status" => false];
                    return response()->json($message, 422);
                }

                // Crear reserva
                $data = new Reserva();
                $data->usuario_id = $request->usuario_id;
                $data->fecha_entrada = $request->fecha_entrada;
                $data->fecha_salida = $request->fecha_salida;
                $data->estado = $request->estado ?? 'pendiente';
                $data->activo = $request->activo ?? true;

                // Guardar reserva
                $isOK = $data->save();

                if (!$isOK) {
                    $message = ["message" => "Dato no insertado", "status" => false];
                    return response()->json($message, 500);
                }

                // Agregar habitaciones si se proporcionan
                if ($request->has('habitaciones')) {
                    foreach ($request->habitaciones as $habitacionData) {
                        $habitacion = Habitacion::find($habitacionData['habitacion_id']);
                        if (!$habitacion || !$habitacion->estaDisponible()) {
                            throw new \Exception("Habitación no disponible");
                        }

                        DetalleReserva::create([
                            'reserva_id' => $data->id,
                            'habitacion_id' => $habitacion->id,
                            'noches' => Carbon::parse($request->fecha_salida)->diffInDays(Carbon::parse($request->fecha_entrada)),
                            'precio_noche' => $habitacion->precio_por_noche,
                            'subtotal' => Carbon::parse($request->fecha_salida)->diffInDays(Carbon::parse($request->fecha_entrada)) * $habitacion->precio_por_noche,
                            'activo' => true
                        ]);
                    }
                }

                // Agregar servicios si se proporcionan
                if ($request->has('servicios')) {
                    foreach ($request->servicios as $servicioData) {
                        $servicio = Servicio::find($servicioData['servicio_id']);
                        if (!$servicio || !$servicio->activo) {
                            throw new \Exception("Servicio no disponible");
                        }

                        ReservaServicio::create([
                            'reserva_id' => $data->id,
                            'servicio_id' => $servicio->id,
                            'cantidad' => $servicioData['cantidad'],
                            'precio_unitario' => $servicio->precio,
                            'subtotal' => $servicioData['cantidad'] * $servicio->precio,
                            'activo' => true
                        ]);
                    }
                }

                $message = ["message" => "Dato insertado", "status" => true];
                return response()->json($message);
            });
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al crear la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function confirmar(int $id) {
        try {
            $data = Reserva::find($id);
            if (!$data) {
                $message = ["message" => "Reserva no encontrada", "status" => false];
                return response()->json($message, 404);
            }

            if ($data->estado !== 'pendiente') {
                $message = ["message" => "Solo se pueden confirmar reservas en estado pendiente", "status" => false];
                return response()->json($message, 422);
            }

            return DB::transaction(function () use ($data) {
                $data->update(['estado' => 'confirmada']);

                // Actualizar estado de habitaciones
                foreach ($data->detalleReservas as $detalle) {
                    $detalle->habitacion->update(['estado' => 'ocupada']);
                }

                $message = ["message" => "Reserva confirmada exitosamente", "status" => true];
                return response()->json($message);
            });
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al confirmar la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }

    function cancelar(int $id) {
        try {
            $data = Reserva::find($id);
            if (!$data) {
                $message = ["message" => "Reserva no encontrada", "status" => false];
                return response()->json($message, 404);
            }

            if ($data->estado === 'completada') {
                $message = ["message" => "No se puede cancelar una reserva completada", "status" => false];
                return response()->json($message, 422);
            }

            return DB::transaction(function () use ($data) {
                $data->update(['estado' => 'cancelada']);

                // Liberar habitaciones si estaba confirmada
                if ($data->estado === 'confirmada') {
                    foreach ($data->detalleReservas as $detalle) {
                        $detalle->habitacion->update(['estado' => 'disponible']);
                    }
                }

                $message = ["message" => "Reserva cancelada exitosamente", "status" => true];
                return response()->json($message);
            });
        } catch (\Exception $th) {
            $message = ["message" => "Error grave al cancelar la reserva: " . $th->getMessage(), "status" => false];
            return response()->json($message, 500);
        }
    }
}
