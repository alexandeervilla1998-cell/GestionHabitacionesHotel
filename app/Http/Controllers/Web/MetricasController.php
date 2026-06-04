<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class MetricasController extends Controller
{
    public function index()
    {
        $totalReservas        = Reserva::count();
        $reservasPendientes   = Reserva::where('estado', 'pendiente')->count();
        $reservasConfirmadas  = Reserva::where('estado', 'confirmada')->count();
        $reservasCanceladas   = Reserva::where('estado', 'cancelada')->count();

        $totalHabitaciones    = Habitacion::where('activo', true)->count();
        $habitacionesOcupadas = Habitacion::where('estado', 'ocupada')->where('activo', true)->count();
        $tasaOcupacion        = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 1)
            : 0;

        $totalClientes        = Cliente::where('activo', true)->count();

        $ingresosTotales    = Factura::sum('total');
        $ingresosRecaudados = Pago::where('estado_pago', 'completado')->sum('monto');
        $saldoPendiente     = $ingresosTotales - $ingresosRecaudados;

        $reservasPorEstado = [
            'Pendiente'  => $reservasPendientes,
            'Confirmada' => $reservasConfirmadas,
            'Cancelada'  => $reservasCanceladas,
        ];

        $formatoMes = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', fecha_emision)"
            : "DATE_FORMAT(fecha_emision, '%Y-%m')";

        $ingresosPorMes = Factura::query()
            ->where('fecha_emision', '>=', now()->subMonths(6))
            ->select(
                DB::raw("$formatoMes as mes"),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        $reservasPorTipo = DB::table('detalle_reservas')
            ->join('habitaciones', 'detalle_reservas.habitacion_id', '=', 'habitaciones.id')
            ->select('habitaciones.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('habitaciones.tipo')
            ->orderByDesc('total')
            ->get();

        $serviciosMasSolicitados = DB::table('reserva_servicio')
            ->join('servicios', 'reserva_servicio.servicio_id', '=', 'servicios.id')
            ->select('servicios.nombre', DB::raw('SUM(reserva_servicio.cantidad) as total_solicitado'))
            ->where('reserva_servicio.activo', true)
            ->groupBy('servicios.nombre')
            ->orderByDesc('total_solicitado')
            ->limit(5)
            ->get();

        $topClientes = DB::table('reservas')
            ->join('clientes', 'reservas.cliente_id', '=', 'clientes.id')
            ->select('clientes.nombre', DB::raw('COUNT(reservas.id) as total_reservas'))
            ->groupBy('clientes.nombre')
            ->orderByDesc('total_reservas')
            ->limit(5)
            ->get();

        return view('metricas.index', compact(
            'totalReservas', 'reservasPendientes', 'reservasConfirmadas', 'reservasCanceladas',
            'totalHabitaciones', 'habitacionesOcupadas', 'tasaOcupacion',
            'totalClientes', 'ingresosTotales', 'ingresosRecaudados', 'saldoPendiente',
            'reservasPorEstado', 'ingresosPorMes',
            'reservasPorTipo', 'serviciosMasSolicitados', 'topClientes'
        ));
    }
}
