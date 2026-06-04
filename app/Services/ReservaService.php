<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Habitacion;
use App\Models\DetalleReserva;
use App\Models\ReservaServicio;
use App\Models\Factura;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReservaService
{
    public function getReservas(array $filters = []): LengthAwarePaginator
    {
        $query = Reserva::query();

        // Apply filters
        if (isset($filters['usuario_id'])) {
            $query->where('usuario_id', $filters['usuario_id']);
        }

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['fecha_inicio'])) {
            $query->where('fecha_entrada', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin'])) {
            $query->where('fecha_salida', '<=', $filters['fecha_fin']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        $with = [];
        if (isset($filters['with_usuario'])) $with[] = 'usuario';
        if (isset($filters['with_detalle_reservas'])) $with[] = 'detalleReservas.habitacion';
        if (isset($filters['with_habitaciones'])) $with[] = 'habitaciones';
        if (isset($filters['with_servicios'])) $with[] = 'servicios';
        if (isset($filters['with_factura'])) $with[] = 'factura.pagos';

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function createReserva(array $data): Reserva
    {
        return DB::transaction(function () use ($data) {
            // Validate room availability
            $this->validateRoomAvailability($data['habitaciones'], $data['fecha_entrada'], $data['fecha_salida']);

            // Create reservation
            $reservaData = [
                'usuario_id' => $data['usuario_id'],
                'fecha_entrada' => $data['fecha_entrada'],
                'fecha_salida' => $data['fecha_salida'],
                'estado' => $data['estado'] ?? 'pendiente',
                'activo' => $data['activo'] ?? true
            ];

            $reserva = Reserva::create($reservaData);

            // Add rooms to reservation
            foreach ($data['habitaciones'] as $habitacionData) {
                $habitacion = Habitacion::findOrFail($habitacionData['habitacion_id']);
                
                DetalleReserva::create([
                    'reserva_id' => $reserva->id,
                    'habitacion_id' => $habitacion->id,
                    'noches' => $habitacionData['noches'],
                    'precio_noche' => $habitacion->precio_por_noche,
                    'subtotal' => $habitacionData['noches'] * $habitacion->precio_por_noche,
                    'activo' => true
                ]);
            }

            // Add services if provided
            if (isset($data['servicios'])) {
                foreach ($data['servicios'] as $servicioData) {
                    $servicio = Servicio::findOrFail($servicioData['servicio_id']);
                    
                    if (!$servicio->activo) {
                        throw new \Exception("El servicio {$servicio->nombre} no está activo");
                    }

                    ReservaServicio::create([
                        'reserva_id' => $reserva->id,
                        'servicio_id' => $servicio->id,
                        'cantidad' => $servicioData['cantidad'],
                        'precio_unitario' => $servicio->precio,
                        'subtotal' => $servicioData['cantidad'] * $servicio->precio,
                        'activo' => true
                    ]);
                }
            }

            // Load relationships for response
            $reserva->load([
                'usuario',
                'detalleReservas.habitacion',
                'servicios'
            ]);

            return $reserva;
        });
    }

    /**
     * Update reservation
     */
    public function updateReserva(Reserva $reserva, array $data): Reserva
    {
        // Validate that reservation can be modified
        if (in_array($reserva->estado, ['confirmada', 'completada']) && 
            isset($data['fecha_entrada']) || isset($data['fecha_salida'])) {
            throw new \Exception('No se pueden modificar las fechas de una reserva confirmada o completada');
        }

        $reserva->update($data);
        return $reserva;
    }

    /**
     * Delete reservation with validation
     */
    public function deleteReserva(Reserva $reserva): bool
    {
        // Validate that reservation can be deleted
        if (in_array($reserva->estado, ['confirmada', 'completada'])) {
            throw new \Exception('No se puede eliminar una reserva confirmada o completada');
        }

        return DB::transaction(function () use ($reserva) {
            // Delete details and services
            $reserva->detalleReservas()->delete();
            $reserva->servicios()->delete();
            
            // Delete invoice if exists
            if ($reserva->factura) {
                $reserva->factura->pagos()->delete();
                $reserva->factura()->delete();
            }

            return $reserva->delete();
        });
    }

    /**
     * Confirm reservation
     */
    public function confirmarReserva(Reserva $reserva): Reserva
    {
        if ($reserva->estado !== 'pendiente') {
            throw new \Exception('Solo se pueden confirmar reservas en estado pendiente');
        }

        // Validate that reservation has rooms
        if ($reserva->detalleReservas->isEmpty()) {
            throw new \Exception('La reserva debe tener al menos una habitación');
        }

        return DB::transaction(function () use ($reserva) {
            $reserva->update(['estado' => 'confirmada']);

            // Update room status to occupied
            foreach ($reserva->detalleReservas as $detalle) {
                $detalle->habitacion->update(['estado' => 'ocupada']);
            }

            return $reserva;
        });
    }

    /**
     * Cancel reservation
     */
    public function cancelarReserva(Reserva $reserva): Reserva
    {
        if ($reserva->estado === 'completada') {
            throw new \Exception('No se puede cancelar una reserva completada');
        }

        return DB::transaction(function () use ($reserva) {
            $estabaConfirmada = $reserva->estado === 'confirmada';
            $reserva->update(['estado' => 'cancelada']);

            if ($estabaConfirmada) {
                foreach ($reserva->detalleReservas as $detalle) {
                    $detalle->habitacion->update(['estado' => 'disponible']);
                }
            }

            return $reserva;
        });
    }

    /**
     * Find reservation by ID with relationships
     */
    public function findReservaById(int $id, array $with = []): ?Reserva
    {
        $query = Reserva::query();
        
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Validate room availability
     */
    private function validateRoomAvailability(array $habitaciones, string $fechaEntrada, string $fechaSalida): void
    {
        foreach ($habitaciones as $habitacionData) {
            $habitacion = Habitacion::findOrFail($habitacionData['habitacion_id']);
            
            if (!$habitacion->estaDisponible()) {
                throw new \Exception("La habitación {$habitacion->numero} no está disponible");
            }

            // Check if room is occupied in requested dates
            $ocupada = DetalleReserva::where('habitacion_id', $habitacion->id)
                ->whereHas('reserva', function ($query) use ($fechaEntrada, $fechaSalida) {
                    $query->where('estado', 'confirmada')
                          ->where(function ($q) use ($fechaEntrada, $fechaSalida) {
                              $q->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                                ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                                ->orWhere(function ($q) use ($fechaEntrada, $fechaSalida) {
                                    $q->where('fecha_entrada', '<=', $fechaEntrada)
                                      ->where('fecha_salida', '>=', $fechaSalida);
                                });
                          });
                })->exists();

            if ($ocupada) {
                throw new \Exception("La habitación {$habitacion->numero} está ocupada en las fechas solicitadas");
            }
        }
    }

    /**
     * Generate invoice for reservation
     */
    public function generarFactura(Reserva $reserva): Factura
    {
        if ($reserva->estado !== 'confirmada') {
            throw new \Exception('Solo se pueden generar facturas para reservas confirmadas');
        }

        if ($reserva->factura) {
            throw new \Exception('La reserva ya tiene una factura generada');
        }

        return DB::transaction(function () use ($reserva) {
            // Calculate totals
            $subtotalHabitaciones = $reserva->subtotal_habitaciones;
            $subtotalServicios = $reserva->subtotal_servicios;
            $subtotal = $subtotalHabitaciones + $subtotalServicios;
            $impuestos = $subtotal * 0.16; // 16% taxes
            $total = $subtotal + $impuestos;

            // Create invoice
            $factura = Factura::create([
                'reserva_id' => $reserva->id,
                'numero_factura' => 'FAC-' . date('Y') . '-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'fecha_emision' => now(),
            ]);

            return $factura;
        });
    }
}
