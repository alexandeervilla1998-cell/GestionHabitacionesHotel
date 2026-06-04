<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservaServicioResource;
use App\Models\ReservaServicio;
use App\Models\Reserva;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReservaServicioController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ReservaServicio::query();

            // Filtros
            if ($request->has('reserva_id')) {
                $query->where('reserva_id', $request->reserva_id);
            }

            if ($request->has('servicio_id')) {
                $query->where('servicio_id', $request->servicio_id);
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            $reservaServicios = $query->with(['reserva.usuario', 'servicio'])->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Servicios de reserva obtenidos correctamente',
                'data' => ReservaServicioResource::collection($reservaServicios),
                'pagination' => [
                    'current_page' => $reservaServicios->currentPage(),
                    'last_page' => $reservaServicios->lastPage(),
                    'per_page' => $reservaServicios->perPage(),
                    'total' => $reservaServicios->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reserva_id' => 'required|exists:reservas,id',
                'servicio_id' => 'required|exists:servicios,id',
                'cantidad' => 'required|integer|min:1',
                'precio_unitario' => 'required|numeric|min:0',
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
                $servicio = Servicio::findOrFail($request->servicio_id);

                // Validar que la reserva esté en estado pendiente
                if ($reserva->estado !== 'pendiente') {
                    throw new \Exception('Solo se pueden agregar servicios a reservas en estado pendiente');
                }

                // Validar que el servicio esté activo
                if (!$servicio->activo) {
                    throw new \Exception("El servicio {$servicio->nombre} no está activo");
                }

                // Validar que no se duplique el servicio en la misma reserva
                $existente = ReservaServicio::where('reserva_id', $reserva->id)
                    ->where('servicio_id', $servicio->id)
                    ->exists();

                if ($existente) {
                    throw new \Exception("El servicio ya está incluido en esta reserva");
                }

                $data = $request->all();
                $data['subtotal'] = $request->cantidad * $request->precio_unitario;
                $data['activo'] = $request->activo ?? true;

                $reservaServicio = ReservaServicio::create($data);

                // Cargar relaciones para la respuesta
                $reservaServicio->load(['reserva.usuario', 'servicio']);

                return response()->json([
                    'success' => true,
                    'message' => 'Servicio agregado a la reserva correctamente',
                    'data' => new ReservaServicioResource($reservaServicio)
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar servicio a la reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reservaServicio = ReservaServicio::with(['reserva.usuario', 'servicio'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Servicio de reserva obtenido correctamente',
                'data' => new ReservaServicioResource($reservaServicio)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicio de reserva',
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reservaServicio = ReservaServicio::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'cantidad' => 'sometimes|required|integer|min:1',
                'precio_unitario' => 'sometimes|required|numeric|min:0',
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
            if ($reservaServicio->reserva->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden modificar servicios de reservas en estado pendiente',
                    'errors' => ['estado' => ['La reserva está en estado ' . $reservaServicio->reserva->estado]]
                ], 422);
            }

            $data = $request->all();

            // Recalcular subtotal si cambian cantidad o precio
            if ($request->has('cantidad') || $request->has('precio_unitario')) {
                $cantidad = $request->cantidad ?? $reservaServicio->cantidad;
                $precioUnitario = $request->precio_unitario ?? $reservaServicio->precio_unitario;
                $data['subtotal'] = $cantidad * $precioUnitario;
            }

            $reservaServicio->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Servicio de reserva actualizado correctamente',
                'data' => new ReservaServicioResource($reservaServicio->load(['reserva.usuario', 'servicio']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar servicio de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reservaServicio = ReservaServicio::findOrFail($id);

            // Validar que la reserva esté en estado pendiente
            if ($reservaServicio->reserva->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar servicios de reservas en estado pendiente',
                    'errors' => ['estado' => ['La reserva está en estado ' . $reservaServicio->reserva->estado]]
                ], 422);
            }

            $reservaServicio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado de la reserva correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar servicio de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function agregarServicio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reserva_id' => 'required|exists:reservas,id',
                'servicio_id' => 'required|exists:servicios,id',
                'cantidad' => 'required|integer|min:1'
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
                $servicio = Servicio::findOrFail($request->servicio_id);

                // Validar que la reserva esté en estado pendiente
                if ($reserva->estado !== 'pendiente') {
                    throw new \Exception('Solo se pueden agregar servicios a reservas en estado pendiente');
                }

                // Validar que el servicio esté activo
                if (!$servicio->activo) {
                    throw new \Exception("El servicio {$servicio->nombre} no está activo");
                }

                // Verificar si ya existe el servicio en la reserva
                $existente = ReservaServicio::where('reserva_id', $reserva->id)
                    ->where('servicio_id', $servicio->id)
                    ->first();

                if ($existente) {
                    // Si ya existe, actualizar la cantidad
                    $nuevaCantidad = $existente->cantidad + $request->cantidad;
                    $existente->update([
                        'cantidad' => $nuevaCantidad,
                        'subtotal' => $nuevaCantidad * $servicio->precio
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Servicio actualizado en la reserva correctamente',
                        'data' => new ReservaServicioResource($existente->load(['reserva.usuario', 'servicio']))
                    ]);
                }

                // Crear nuevo servicio en la reserva
                $reservaServicio = ReservaServicio::create([
                    'reserva_id' => $reserva->id,
                    'servicio_id' => $servicio->id,
                    'cantidad' => $request->cantidad,
                    'precio_unitario' => $servicio->precio,
                    'subtotal' => $request->cantidad * $servicio->precio,
                    'activo' => true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Servicio agregado a la reserva correctamente',
                    'data' => new ReservaServicioResource($reservaServicio->load(['reserva.usuario', 'servicio']))
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar servicio a la reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function porReserva($reservaId)
    {
        try {
            $reserva = Reserva::findOrFail($reservaId);
            $servicios = ReservaServicio::where('reserva_id', $reservaId)
                ->with('servicio')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Servicios de reserva obtenidos correctamente',
                'data' => ReservaServicioResource::collection($servicios),
                'reserva' => [
                    'id' => $reserva->id,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'estado' => $reserva->estado,
                    'subtotal_servicios' => $reserva->subtotal_servicios
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function serviciosDisponibles()
    {
        try {
            $servicios = Servicio::activos()->get();

            return response()->json([
                'success' => true,
                'message' => 'Servicios disponibles obtenidos correctamente',
                'data' => $servicios->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'precio' => (float) $servicio->precio,
                        'activo' => $servicio->activo
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios disponibles',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
