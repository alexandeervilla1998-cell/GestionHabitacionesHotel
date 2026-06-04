<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacturaResource;
use App\Models\Factura;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Factura::query();

            // Filtros
            if ($request->has('reserva_id')) {
                $query->where('reserva_id', $request->reserva_id);
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            if ($request->has('fecha_inicio')) {
                $query->where('fecha_emision', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin')) {
                $query->where('fecha_emision', '<=', $request->fecha_fin);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where('numero_factura', 'like', "%{$search}%");
            }

            $facturas = $query->with(['reserva.usuario', 'pagos'])->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Facturas obtenidas correctamente',
                'data' => FacturaResource::collection($facturas),
                'pagination' => [
                    'current_page' => $facturas->currentPage(),
                    'last_page' => $facturas->lastPage(),
                    'per_page' => $facturas->perPage(),
                    'total' => $facturas->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reserva_id' => 'required|exists:reservas,id|unique:facturas,reserva_id',
                'impuestos' => 'sometimes|numeric|min:0'
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

                // Validar que la reserva esté confirmada
                if ($reserva->estado !== 'confirmada') {
                    throw new \Exception('Solo se pueden generar facturas para reservas confirmadas');
                }

                // Validar que no tenga factura ya
                if ($reserva->factura) {
                    throw new \Exception('La reserva ya tiene una factura generada');
                }

                // Calcular totales
                $subtotalHabitaciones = $reserva->subtotal_habitaciones;
                $subtotalServicios = $reserva->subtotal_servicios;
                $subtotal = $subtotalHabitaciones + $subtotalServicios;
                $impuestos = $request->impuestos ?? ($subtotal * 0.16); // 16% de impuestos por defecto
                $total = $subtotal + $impuestos;

                // Crear factura
                $factura = Factura::create([
                    'reserva_id' => $reserva->id,
                    'numero_factura' => 'FAC-' . date('Y') . '-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT),
                    'subtotal' => $subtotal,
                    'impuestos' => $impuestos,
                    'total' => $total,
                    'fecha_emision' => now(),
                    'activo' => true
                ]);

                // Cargar relaciones para la respuesta
                $factura->load(['reserva.usuario', 'reserva.detalleReservas.habitacion', 'reserva.servicios']);

                return response()->json([
                    'success' => true,
                    'message' => 'Factura generada correctamente',
                    'data' => new FacturaResource($factura)
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar factura',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $factura = Factura::with([
                'reserva.usuario',
                'reserva.detalleReservas.habitacion',
                'reserva.servicios',
                'pagos'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Factura obtenida correctamente',
                'data' => new FacturaResource($factura)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener factura',
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $factura = Factura::findOrFail($id);

            // No permitir modificar facturas existentes (solo activo/desactivar)
            $validator = Validator::make($request->all(), [
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar que no tenga pagos completados si se quiere desactivar
            if ($request->has('activo') && !$request->activo) {
                $pagosCompletados = $factura->pagos()->where('estado_pago', 'completado')->exists();
                if ($pagosCompletados) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede desactivar una factura con pagos completados',
                        'errors' => ['pagos' => ['La factura tiene pagos completados']]
                    ], 422);
                }
            }

            $factura->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Factura actualizada correctamente',
                'data' => new FacturaResource($factura->load('reserva.usuario'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar factura',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $factura = Factura::findOrFail($id);

            // Validar que no tenga pagos
            if ($factura->pagos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una factura con pagos registrados',
                    'errors' => ['pagos' => ['La factura tiene pagos asociados']]
                ], 422);
            }

            $factura->delete();

            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar factura',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function porReserva($reservaId)
    {
        try {
            $reserva = Reserva::findOrFail($reservaId);
            $factura = Factura::where('reserva_id', $reservaId)
                ->with(['reserva.usuario', 'pagos'])
                ->first();

            if (!$factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'La reserva no tiene factura generada',
                    'errors' => ['factura' => ['No se encontró factura para esta reserva']]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Factura de reserva obtenida correctamente',
                'data' => new FacturaResource($factura)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener factura de reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function generarDesdeReserva($reservaId)
    {
        try {
            $reserva = Reserva::findOrFail($reservaId);

            // Validar que la reserva esté confirmada
            if ($reserva->estado !== 'confirmada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden generar facturas para reservas confirmadas',
                    'errors' => ['estado' => ['La reserva está en estado ' . $reserva->estado]]
                ], 422);
            }

            // Validar que no tenga factura ya
            if ($reserva->factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'La reserva ya tiene una factura generada',
                    'errors' => ['factura' => ['Ya existe una factura para esta reserva']]
                ], 422);
            }

            return DB::transaction(function () use ($reserva) {
                // Calcular totales
                $subtotalHabitaciones = $reserva->subtotal_habitaciones;
                $subtotalServicios = $reserva->subtotal_servicios;
                $subtotal = $subtotalHabitaciones + $subtotalServicios;
                $impuestos = $subtotal * 0.16; // 16% de impuestos
                $total = $subtotal + $impuestos;

                // Crear factura
                $factura = Factura::create([
                    'reserva_id' => $reserva->id,
                    'numero_factura' => 'FAC-' . date('Y') . '-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT),
                    'subtotal' => $subtotal,
                    'impuestos' => $impuestos,
                    'total' => $total,
                    'fecha_emision' => now(),
                    'activo' => true
                ]);

                // Cargar relaciones para la respuesta
                $factura->load([
                    'reserva.usuario',
                    'reserva.detalleReservas.habitacion',
                    'reserva.servicios'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Factura generada correctamente desde la reserva',
                    'data' => new FacturaResource($factura)
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar factura desde reserva',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function estadoPago($id)
    {
        try {
            $factura = Factura::with('pagos')->findOrFail($id);

            $totalPagado = $factura->pagos()->where('estado_pago', 'completado')->sum('monto');
            $saldoPendiente = $factura->total - $totalPagado;
            $estaPagada = $totalPagado >= $factura->total;

            return response()->json([
                'success' => true,
                'message' => 'Estado de pago obtenido correctamente',
                'data' => [
                    'factura_id' => $factura->id,
                    'numero_factura' => $factura->numero_factura,
                    'total' => (float) $factura->total,
                    'total_pagado' => (float) $totalPagado,
                    'saldo_pendiente' => (float) $saldoPendiente,
                    'esta_pagada' => $estaPagada,
                    'porcentaje_pagado' => $factura->total > 0 ? round(($totalPagado / $factura->total) * 100, 2) : 0,
                    'pagos' => $factura->pagos->map(function ($pago) {
                        return [
                            'id' => $pago->id,
                            'monto' => (float) $pago->monto,
                            'metodo_pago' => $pago->metodo_pago,
                            'estado_pago' => $pago->estado_pago,
                            'creado_en' => $pago->creado_en
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado de pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
