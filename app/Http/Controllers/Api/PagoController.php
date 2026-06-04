<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagoResource;
use App\Models\Pago;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Pago::query();

            // Filtros
            if ($request->has('factura_id')) {
                $query->where('factura_id', $request->factura_id);
            }

            if ($request->has('metodo_pago')) {
                $query->where('metodo_pago', $request->metodo_pago);
            }

            if ($request->has('estado_pago')) {
                $query->where('estado_pago', $request->estado_pago);
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            if ($request->has('fecha_inicio')) {
                $query->where('creado_en', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin')) {
                $query->where('creado_en', '<=', $request->fecha_fin);
            }

            $pagos = $query->with(['factura.reserva.usuario'])->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Pagos obtenidos correctamente',
                'data' => PagoResource::collection($pagos),
                'pagination' => [
                    'current_page' => $pagos->currentPage(),
                    'last_page' => $pagos->lastPage(),
                    'per_page' => $pagos->perPage(),
                    'total' => $pagos->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'factura_id' => 'required|exists:facturas,id',
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|in:efectivo,tarjeta_crédito,tarjeta_débito,transferencia',
                'estado_pago' => 'sometimes|in:pendiente,completado,fallido,reembolsado',
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
                $factura = Factura::findOrFail($request->factura_id);

                // Validar que la factura esté activa
                if (!$factura->activo) {
                    throw new \Exception('La factura no está activa');
                }

                // Calcular deuda pendiente
                $totalPagado = $factura->pagos()->where('estado_pago', 'completado')->sum('monto');
                $saldoPendiente = $factura->total - $totalPagado;

                // Validar que el monto no exceda la deuda pendiente
                if ($request->monto > $saldoPendiente) {
                    throw new \Exception("El monto del pago ({$request->monto}) excede el saldo pendiente ({$saldoPendiente})");
                }

                $data = $request->all();
                $data['estado_pago'] = $request->estado_pago ?? 'pendiente';
                $data['activo'] = $request->activo ?? true;
                $data['creado_en'] = now();

                $pago = Pago::create($data);

                // Si el pago está completado, actualizar el estado de la factura si está completamente pagada
                if ($pago->estado_pago === 'completado') {
                    $nuevoTotalPagado = $totalPagado + $request->monto;
                    if ($nuevoTotalPagado >= $factura->total) {
                        // La factura está completamente pagada
                        // Aquí podrías agregar lógica adicional como enviar notificaciones
                    }
                }

                // Cargar relaciones para la respuesta
                $pago->load(['factura.reserva.usuario']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago registrado correctamente',
                    'data' => new PagoResource($pago)
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pago = Pago::with(['factura.reserva.usuario'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Pago obtenido correctamente',
                'data' => new PagoResource($pago)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pago',
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pago = Pago::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'monto' => 'sometimes|required|numeric|min:0.01',
                'metodo_pago' => 'sometimes|required|in:efectivo,tarjeta_crédito,tarjeta_débito,transferencia',
                'estado_pago' => 'sometimes|required|in:pendiente,completado,fallido,reembolsado',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request, $pago) {
                $factura = $pago->factura;

                // Validar cambios en el monto
                if ($request->has('monto')) {
                    // Calcular deuda pendiente excluyendo este pago
                    $totalPagado = $factura->pagos()
                        ->where('estado_pago', 'completado')
                        ->where('id', '!=', $pago->id)
                        ->sum('monto');
                    $saldoPendiente = $factura->total - $totalPagado;

                    if ($request->monto > $saldoPendiente) {
                        throw new \Exception("El monto del pago ({$request->monto}) excede el saldo pendiente ({$saldoPendiente})");
                    }
                }

                $pago->update($request->all());

                // Recalcular estado de la factura si cambia el estado del pago
                if ($request->has('estado_pago')) {
                    $totalPagadoActualizado = $factura->pagos()->where('estado_pago', 'completado')->sum('monto');
                    if ($totalPagadoActualizado >= $factura->total) {
                        // La factura está completamente pagada
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pago actualizado correctamente',
                    'data' => new PagoResource($pago->load('factura.reserva.usuario'))
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pago = Pago::findOrFail($id);

            // Validar que no esté completado
            if ($pago->estado_pago === 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un pago completado',
                    'errors' => ['estado_pago' => ['Los pagos completados no pueden ser eliminados']]
                ], 422);
            }

            $pago->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function porFactura($facturaId)
    {
        try {
            $factura = Factura::findOrFail($facturaId);
            $pagos = Pago::where('factura_id', $facturaId)
                ->with('factura.reserva.usuario')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pagos de factura obtenidos correctamente',
                'data' => PagoResource::collection($pagos),
                'factura' => [
                    'id' => $factura->id,
                    'numero_factura' => $factura->numero_factura,
                    'total' => (float) $factura->total,
                    'total_pagado' => (float) $factura->total_pagado,
                    'saldo_pendiente' => (float) $factura->saldo_pendiente,
                    'esta_pagada' => $factura->estaPagada()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos de factura',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function procesarPago($id)
    {
        try {
            $pago = Pago::findOrFail($id);

            // Validar que esté pendiente
            if ($pago->estado_pago !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden procesar pagos en estado pendiente',
                    'errors' => ['estado_pago' => ['El pago está en estado ' . $pago->estado_pago]]
                ], 422);
            }

            return DB::transaction(function () use ($pago) {
                $pago->update(['estado_pago' => 'completado']);

                // Recalcular estado de la factura
                $factura = $pago->factura;
                $totalPagado = $factura->pagos()->where('estado_pago', 'completado')->sum('monto');
                $estaPagada = $totalPagado >= $factura->total;

                return response()->json([
                    'success' => true,
                    'message' => 'Pago procesado correctamente',
                    'data' => [
                        'pago' => new PagoResource($pago->load('factura.reserva.usuario')),
                        'factura_pagada' => $estaPagada,
                        'total_pagado' => (float) $totalPagado,
                        'total_factura' => (float) $factura->total,
                        'saldo_pendiente' => (float) ($factura->total - $totalPagado)
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelarPago($id)
    {
        try {
            $pago = Pago::findOrFail($id);

            // Validar que esté pendiente
            if ($pago->estado_pago !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar pagos en estado pendiente',
                    'errors' => ['estado_pago' => ['El pago está en estado ' . $pago->estado_pago]]
                ], 422);
            }

            $pago->update(['estado_pago' => 'fallido']);

            return response()->json([
                'success' => true,
                'message' => 'Pago cancelado correctamente',
                'data' => new PagoResource($pago->load('factura.reserva.usuario'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function reembolsarPago($id)
    {
        try {
            $pago = Pago::findOrFail($id);

            // Validar que esté completado
            if ($pago->estado_pago !== 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden reembolsar pagos completados',
                    'errors' => ['estado_pago' => ['El pago está en estado ' . $pago->estado_pago]]
                ], 422);
            }

            return DB::transaction(function () use ($pago) {
                $pago->update(['estado_pago' => 'reembolsado']);

                // Recalcular estado de la factura
                $factura = $pago->factura;
                $totalPagado = $factura->pagos()->where('estado_pago', 'completado')->sum('monto');
                $estaPagada = $totalPagado >= $factura->total;

                return response()->json([
                    'success' => true,
                    'message' => 'Pago reembolsado correctamente',
                    'data' => [
                        'pago' => new PagoResource($pago->load('factura.reserva.usuario')),
                        'factura_pagada' => $estaPagada,
                        'total_pagado' => (float) $totalPagado,
                        'total_factura' => (float) $factura->total,
                        'saldo_pendiente' => (float) ($factura->total - $totalPagado)
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reembolsar pago',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenPorFactura($facturaId)
    {
        try {
            $factura = Factura::with('pagos')->findOrFail($facturaId);

            $resumen = [
                'factura' => [
                    'id' => $factura->id,
                    'numero_factura' => $factura->numero_factura,
                    'subtotal' => (float) $factura->subtotal,
                    'impuestos' => (float) $factura->impuestos,
                    'total' => (float) $factura->total,
                    'fecha_emision' => $factura->fecha_emision
                ],
                'pagos' => $factura->pagos->groupBy('estado_pago')->map(function ($pagos, $estado) {
                    return [
                        'estado' => $estado,
                        'cantidad' => $pagos->count(),
                        'total' => (float) $pagos->sum('monto'),
                        'pagos' => PagoResource::collection($pagos)
                    ];
                })->values(),
                'totales' => [
                    'total_pagado' => (float) $factura->total_pagado,
                    'saldo_pendiente' => (float) $factura->saldo_pendiente,
                    'esta_pagada' => $factura->estaPagada(),
                    'porcentaje_pagado' => $factura->total > 0 ? round(($factura->total_pagado / $factura->total) * 100, 2) : 0
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Resumen de pagos obtenido correctamente',
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de pagos',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
