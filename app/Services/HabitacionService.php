<?php

namespace App\Services;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\DetalleReserva;
use Illuminate\Support\Facades\DB;

class HabitacionService
{
    /**
     * Automatically free rooms when their reservation's checkout date has passed
     */
    public function liberarHabitacionesVencidas(): int
    {
        $contador = 0;

        // Find confirmed reservations where checkout date has passed
        $reservasVencidas = Reserva::where('estado', 'confirmada')
            ->where('fecha_salida', '<', now()->toDateString())
            ->where('activo', true)
            ->get();

        foreach ($reservasVencidas as $reserva) {
            DB::transaction(function () use ($reserva, &$contador) {
                // Update reservation status to completed
                $reserva->update(['estado' => 'completada']);

                // Free all rooms associated with this reservation
                foreach ($reserva->detalleReservas as $detalle) {
                    if ($detalle->habitacion->estado === 'ocupada') {
                        $detalle->habitacion->update(['estado' => 'disponible']);
                        $contador++;
                    }
                }
            });
        }

        return $contador;
    }

    /**
     * Put a room into maintenance mode
     */
    public function ponerMantenimiento(Habitacion $habitacion): Habitacion
    {
        if ($habitacion->estado === 'ocupada') {
            throw new \Exception('No se puede poner una habitación ocupada en mantenimiento');
        }

        $habitacion->update(['estado' => 'mantenimiento']);
        return $habitacion;
    }

    /**
     * Take a room out of maintenance mode
     */
    public function sacarMantenimiento(Habitacion $habitacion): Habitacion
    {
        $habitacion->update(['estado' => 'disponible']);
        return $habitacion;
    }

    /**
     * Get room occupancy statistics
     */
    public function obtenerEstadisticasOcupacion(): array
    {
        $total = Habitacion::where('activo', true)->count();
        $disponibles = Habitacion::where('activo', true)->where('estado', 'disponible')->count();
        $ocupadas = Habitacion::where('activo', true)->where('estado', 'ocupada')->count();
        $mantenimiento = Habitacion::where('activo', true)->where('estado', 'mantenimiento')->count();

        return [
            'total' => $total,
            'disponibles' => $disponibles,
            'ocupadas' => $ocupadas,
            'mantenimiento' => $mantenimiento,
            'porcentaje_ocupacion' => $total > 0 ? round(($ocupadas / $total) * 100, 1) : 0,
        ];
    }
}
