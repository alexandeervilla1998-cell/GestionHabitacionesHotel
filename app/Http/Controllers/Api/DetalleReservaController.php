<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetalleReservaResource;
use App\Models\DetalleReserva;
use App\Models\Reserva;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DetalleReservaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DetalleReserva::query();

            // Filtros
            if ($request->has('reserva_id')) {
                $query->where('reserva_id', $request->reserva_id);
            }

            if ($request->has('habitacion_id')) {
                $query->where('habitacion_id', $request->habitacion_id);
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            $detalles = $query->with(['reserva.usuario', 'habitacion'])->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Detalles de reserva obtenidos correctamente',
                'data' => DetalleReservaResource::collection($detalles),
                'pagination' => [
                    'current_page' => $detalles->currentPage(),
                    'last_page' => $detalles->lastPage(),
                    'per_page' => $detalles->perPage(),
                    'total' => $detalles->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reserva_id' => 'required|exists:reservas,id',
                'habitacion_id' => 'required|exists:habitaciones,id',
                'noches' => 'required|integer|min:1',
                'precio_noche' => 'required|numeric|min:0',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $reserva = Reserva::findOrFail($request->reserva_id);
                $habitacion = Habitacion::findOrFail($request->habitacion_id);

                // Validar que la reserva esté en estado pendiente
                if ($reserva->estado !== 'pendiente') {
                    throw new \Exception('Solo se pueden agregar habitaciones a reservas en estado pendiente');
                }

                // Validar que la habitación esté disponible
                if (!$habitacion->estaDisponible()) {
                    throw new \Exception("La habitación {$habitacion->numero} no está disponible");
                }

                // Validar que no esté ocupada en las fechas de la reserva
                $ocupada = DetalleReserva::where('habitacion_id', $habitacion->id)
                    ->whereHas('reserva', function ($query) use ($reserva) {
                        $query->where('estado', 'confirmada')
                              ->where(function ($q) use ($reserva) {
                                  $q->whereBetween('fecha_entrada', [$reserva->fecha_entrada, $reserva->fecha_salida])
                                    ->orWhereBetween('fecha_salida', [$reserva->fecha_entrada, $reserva->fecha_salida])
                                    ->orWhere(function ($q) use ($reserva) {
                                        $q->where('fecha_entrada', '<=', $reserva->fecha_entrada)
                                          ->where('fecha_salida', '>=', $reserva->fecha_salida);
                                    });
                              });
                    })->exists();

                if ($ocupada) {
                    throw new \Exception("La habitación {$habitacion->numero} está ocupada en las fechas de la reserva");
                }

                // Validar que no se duplique la habitación en la misma reserva
                $existente = DetalleReserva::where('reserva_id', $reserva->id)
                    ->where('habitacion_id', $habitacion->id)
                    ->exists();

                if ($existente) {
                    throw new \Exception("La habitación ya está incluida en esta reserva");
                }

                $data = $request->all();
                $data['subtotal'] = $request->noches * $request->precio_noche;
                $data['activo'] = $request->activo ?? true;

                $detalle = DetalleReserva::create($data);

                // Cargar relaciones para la respuesta
                $detalle->load(['reserva.usuario', 'habitacion']);

                return response()->json([
                    'success' => true,
                    'message' => 'Detalle de reserva creado correctamente',
                    'data' => new DetalleReservaResource($detalle)
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear detalle de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $detalle = DetalleReserva::with(['reserva.usuario', 'habitacion'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de reserva obtenido correctamente',
                'data' => new DetalleReservaResource($detalle)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de reserva',
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $detalle = DetalleReserva::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'noches' => 'sometimes|required|integer|min:1',
                'precio_noche' => 'sometimes|required|numeric|min:0',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar que la reserva esté en estado pendiente
            if ($detalle->reserva->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden modificar detalles de reservas en estado pendiente',
                    'errors' => ['estado' => ['La reserva está en estado ' . $detalle->reserva->estado]]
                ], 422);
            }

            $data = $request->all();

            // Recalcular subtotal si cambian noches o precio
            if ($request->has('noches') || $request->has('precio_noche')) {
                $noches = $request->noches ?? $detalle->noches;
                $precioNoche = $request->precio_noche ?? $detalle->precio_noche;
                $data['subtotal'] = $noches * $precioNoche;
            }

            $detalle->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de reserva actualizado correctamente',
                'data' => new DetalleReservaResource($detalle->load(['reserva.usuario', 'habitacion']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar detalle de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $detalle = DetalleReserva::findOrFail($id);

            // Validar que la reserva esté en estado pendiente
            if ($detalle->reserva->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar detalles de reservas en estado pendiente',
                    'errors' => ['estado' => ['La reserva está en estado ' . $detalle->reserva->estado]]
                ], 422);
            }

            // Validar que no sea la única habitación
            $totalHabitaciones = $detalle->reserva->detalleReservas()->count();
            if ($totalHabitaciones <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la única habitación de la reserva',
                    'errors' => ['habitaciones' => ['La reserva debe tener al menos una habitación']]
                ], 422);
            }

            $detalle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detalle de reserva eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar detalle de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function porReserva($reservaId)
    {
        try {
            $reserva = Reserva::findOrFail($reservaId);
            $detalles = DetalleReserva::where('reserva_id', $reservaId)
                ->with('habitacion')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Detalles de reserva obtenidos correctamente',
                'data' => DetalleReservaResource::collection($detalles),
                'reserva' => [
                    'id' => $reserva->id,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'estado' => $reserva->estado,
                    'subtotal_habitaciones' => $reserva->subtotal_habitaciones
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
