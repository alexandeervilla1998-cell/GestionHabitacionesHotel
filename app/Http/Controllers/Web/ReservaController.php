<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DetalleReserva;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use App\Models\Cliente;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['cliente', 'detalleReservas.habitacion', 'servicios', 'factura'])
            ->where('activo', true)
            ->orderBy('creado_en', 'desc')
            ->get();

        return view('reservas.index', compact('reservas'));
    }

    public function create()
    {
        $clientes     = Cliente::where('activo', true)->orderBy('nombre')->get();
        $habitaciones = Habitacion::where('activo', true)->orderBy('numero')->get();
        $servicios    = Servicio::where('activo', true)->orderBy('nombre')->get();

        return view('reservas.create', compact('clientes', 'habitaciones', 'servicios'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida'  => 'required|date|after:fecha_entrada',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'servicios'     => 'nullable|array',
            'servicios.*.servicio_id' => 'required|exists:servicios,id',
            'servicios.*.cantidad'    => 'required|integer|min:1',
        ], [
            'fecha_salida.after' => 'La fecha de salida debe ser un dia posterior a la de entrada (reserva por noches).',
        ]);

        $noches = $this->calcularNoches($validatedData['fecha_entrada'], $validatedData['fecha_salida']);

        if ($noches < 1) {
            throw ValidationException::withMessages([
                'fecha_salida' => 'La reserva debe incluir al menos 1 noche.',
            ]);
        }

        try {
            return DB::transaction(function () use ($validatedData, $noches) {
                $habitacion = Habitacion::findOrFail($validatedData['habitacion_id']);

                if (!$habitacion->activo || !$habitacion->estaDisponible()) {
                    return redirect()->back()->withInput()->with('error', 'La habitacion no esta disponible.');
                }

                if ($this->habitacionOcupada($habitacion->id, $validatedData['fecha_entrada'], $validatedData['fecha_salida'])) {
                    return redirect()->back()->withInput()->with('error', 'La habitacion esta ocupada en las fechas solicitadas.');
                }

                $reserva = Reserva::create([
                    'cliente_id'    => $validatedData['cliente_id'],
                    'fecha_entrada' => $validatedData['fecha_entrada'],
                    'fecha_salida'  => $validatedData['fecha_salida'],
                    'estado'        => 'pendiente',
                    'activo'        => true,
                ]);

                DetalleReserva::create([
                    'reserva_id'    => $reserva->id,
                    'habitacion_id' => $habitacion->id,
                    'noches'        => $noches,
                    'precio_noche'  => $habitacion->precio_por_noche,
                    'subtotal'      => $noches * (float) $habitacion->precio_por_noche,
                    'activo'        => true,
                ]);

                $this->guardarServicios($reserva, $validatedData['servicios'] ?? []);

                return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
            });
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al crear la reserva: ' . $ex->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $reserva = Reserva::with(['detalleReservas', 'servicios'])->find($id);

            if ($reserva == null) {
                return redirect()->route('reservas.index')->with('error', 'Registro no encontrado.');
            }

            $clientes     = Cliente::where('activo', true)->orderBy('nombre')->get();
            $habitaciones = Habitacion::where('activo', true)->orderBy('numero')->get();
            $servicios    = Servicio::where('activo', true)->orderBy('nombre')->get();
            $detalle      = $reserva->detalleReservas->first();
            $serviciosReserva = $reserva->servicios->map(fn ($s) => [
                'servicio_id' => $s->id,
                'nombre'      => $s->nombre,
                'precio'      => (float) $s->precio,
                'cantidad'    => (int) $s->pivot->cantidad,
                'subtotal'    => (float) $s->pivot->subtotal,
            ])->values();

            return view('reservas.edit', compact('reserva', 'clientes', 'habitaciones', 'servicios', 'detalle', 'serviciosReserva'));
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al buscar el registro.');
        }
    }

    public function update(Request $request, string $id)
    {
        $reserva = Reserva::with(['detalleReservas', 'servicios'])->find($id);

        if ($reserva == null) {
            return redirect()->route('reservas.index')->with('error', 'Registro no encontrado.');
        }

        $validatedData = $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida'  => 'required|date|after:fecha_entrada',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'estado'        => 'required|in:pendiente,confirmada,cancelada,completada',
            'servicios'     => 'nullable|array',
            'servicios.*.servicio_id' => 'required|exists:servicios,id',
            'servicios.*.cantidad'    => 'required|integer|min:1',
        ], [
            'fecha_salida.after' => 'La fecha de salida debe ser un dia posterior a la de entrada (reserva por noches).',
        ]);

        $noches = $this->calcularNoches($validatedData['fecha_entrada'], $validatedData['fecha_salida']);

        if ($noches < 1) {
            throw ValidationException::withMessages([
                'fecha_salida' => 'La reserva debe incluir al menos 1 noche.',
            ]);
        }

        if (in_array($reserva->estado, ['confirmada', 'completada']) &&
            ($reserva->fecha_entrada->format('Y-m-d') !== $validatedData['fecha_entrada'] ||
             $reserva->fecha_salida->format('Y-m-d') !== $validatedData['fecha_salida'])) {
            return redirect()->back()->withInput()->with('error', 'No se pueden modificar las fechas de una reserva confirmada o completada.');
        }

        try {
            return DB::transaction(function () use ($reserva, $validatedData, $noches) {
                $habitacion = Habitacion::findOrFail($validatedData['habitacion_id']);
                $detalle    = $reserva->detalleReservas->first();

                if ($detalle && (int) $detalle->habitacion_id !== (int) $habitacion->id) {
                    if ($this->habitacionOcupada($habitacion->id, $validatedData['fecha_entrada'], $validatedData['fecha_salida'], $reserva->id)) {
                        return redirect()->back()->withInput()->with('error', 'La habitacion esta ocupada en las fechas solicitadas.');
                    }
                } elseif (!$detalle && $this->habitacionOcupada($habitacion->id, $validatedData['fecha_entrada'], $validatedData['fecha_salida'], $reserva->id)) {
                    return redirect()->back()->withInput()->with('error', 'La habitacion esta ocupada en las fechas solicitadas.');
                }

                $reserva->cliente_id    = $validatedData['cliente_id'];
                $reserva->fecha_entrada = $validatedData['fecha_entrada'];
                $reserva->fecha_salida  = $validatedData['fecha_salida'];
                $reserva->estado        = $validatedData['estado'];
                $reserva->save();

                if ($detalle) {
                    $detalle->update([
                        'habitacion_id' => $habitacion->id,
                        'noches'        => $noches,
                        'precio_noche'  => $habitacion->precio_por_noche,
                        'subtotal'      => $noches * (float) $habitacion->precio_por_noche,
                    ]);
                } else {
                    DetalleReserva::create([
                        'reserva_id'    => $reserva->id,
                        'habitacion_id' => $habitacion->id,
                        'noches'        => $noches,
                        'precio_noche'  => $habitacion->precio_por_noche,
                        'subtotal'      => $noches * (float) $habitacion->precio_por_noche,
                        'activo'        => true,
                    ]);
                }

                ReservaServicio::where('reserva_id', $reserva->id)->delete();
                $this->guardarServicios($reserva, $validatedData['servicios'] ?? []);

                return redirect()->route('reservas.index')->with('success', 'Registro actualizado exitosamente.');
            });
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el registro: ' . $ex->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $reserva = Reserva::find($id);

            if ($reserva == null) {
                return redirect()->route('reservas.index')->with('error', 'Registro no encontrado.');
            }

            if ($reserva->estado !== 'cancelada') {
                return redirect()->back()->with('error', 'Solo se pueden eliminar reservas canceladas.');
            }

            $reserva->activo = false;

            return $reserva->save()
                ? redirect()->route('reservas.index')->with('success', 'Reserva desactivada exitosamente.')
                : redirect()->back()->with('error', 'Error al desactivar la reserva.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al desactivar la reserva.');
        }
    }

    public function confirmar(string $id)
    {
        try {
            $reserva = Reserva::with('detalleReservas.habitacion')->find($id);

            if ($reserva == null || $reserva->estado !== 'pendiente') {
                return redirect()->back()->with('error', 'Solo se pueden confirmar reservas pendientes.');
            }

            if ($reserva->detalleReservas->isEmpty()) {
                return redirect()->back()->with('error', 'La reserva debe tener al menos una habitacion.');
            }

            DB::transaction(function () use ($reserva) {
                $reserva->update(['estado' => 'confirmada']);
                foreach ($reserva->detalleReservas as $detalle) {
                    $detalle->habitacion->update(['estado' => 'ocupada']);
                }
            });

            return redirect()->route('reservas.index')->with('success', 'Reserva confirmada exitosamente.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al confirmar la reserva.');
        }
    }

    public function cancelar(string $id)
    {
        try {
            $reserva = Reserva::with('detalleReservas.habitacion')->find($id);

            if ($reserva == null) {
                return redirect()->back()->with('error', 'Registro no encontrado.');
            }

            if ($reserva->estado === 'completada') {
                return redirect()->back()->with('error', 'No se puede cancelar una reserva completada.');
            }

            if ($reserva->estado === 'cancelada') {
                return redirect()->back()->with('error', 'La reserva ya esta cancelada.');
            }

            DB::transaction(function () use ($reserva) {
                $estabaConfirmada = $reserva->estado === 'confirmada';
                $reserva->update(['estado' => 'cancelada']);

                if ($estabaConfirmada) {
                    foreach ($reserva->detalleReservas as $detalle) {
                        $detalle->habitacion->update(['estado' => 'disponible']);
                    }
                }
            });

            return redirect()->route('reservas.index')->with('success', 'Reserva cancelada exitosamente.');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'Error al cancelar la reserva.');
        }
    }

    private function calcularNoches(string $fechaEntrada, string $fechaSalida): int
    {
        return (int) Carbon::parse($fechaEntrada)->startOfDay()
            ->diffInDays(Carbon::parse($fechaSalida)->startOfDay());
    }

    private function guardarServicios(Reserva $reserva, array $servicios): void
    {
        foreach ($servicios as $servicioData) {
            $servicio = Servicio::findOrFail($servicioData['servicio_id']);

            if (!$servicio->activo) {
                continue;
            }

            $cantidad = (int) $servicioData['cantidad'];

            ReservaServicio::create([
                'reserva_id'      => $reserva->id,
                'servicio_id'     => $servicio->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $servicio->precio,
                'subtotal'        => $cantidad * (float) $servicio->precio,
                'activo'          => true,
            ]);
        }
    }

    private function habitacionOcupada(int $habitacionId, string $fechaEntrada, string $fechaSalida, ?int $exceptReservaId = null): bool
    {
        $query = DetalleReserva::where('habitacion_id', $habitacionId)
            ->whereHas('reserva', function ($q) use ($fechaEntrada, $fechaSalida, $exceptReservaId) {
                $q->where('estado', 'confirmada')
                    ->where('activo', true)
                    ->where('fecha_entrada', '<', $fechaSalida)
                    ->where('fecha_salida', '>', $fechaEntrada);

                if ($exceptReservaId) {
                    $q->where('id', '!=', $exceptReservaId);
                }
            });

        return $query->exists();
    }
}
