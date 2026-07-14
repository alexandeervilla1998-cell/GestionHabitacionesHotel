@extends('layouts.app')

@section('title', 'Métricas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Métricas',
    'pageSubtitle' => 'Panel de análisis del hotel — visible solo para administradores.',
])

{{-- KPIs principales --}}
<div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="hotel-card animate-hotel-slide stagger-1 border-t-4 border-t-sky-600 p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Total reservas</p>
        <p class="mt-2 text-3xl font-bold text-hotel-dark">{{ $totalReservas }}</p>
        <p class="mt-2 text-xs text-gray-600">
            {{ $reservasConfirmadas }} confirmadas · {{ $reservasPendientes }} pendientes · {{ $reservasCanceladas }} canceladas
        </p>
    </div>
    <div class="hotel-card animate-hotel-slide stagger-2 border-t-4 border-t-cyan-600 p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Tasa de ocupación</p>
        <p class="mt-2 text-3xl font-bold text-hotel-dark">{{ $tasaOcupacion }}%</p>
        <p class="mt-2 text-xs text-gray-600">{{ $habitacionesOcupadas }} / {{ $totalHabitaciones }} habitaciones</p>
    </div>
    <div class="hotel-card animate-hotel-slide stagger-3 border-t-4 border-t-amber-500 p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Clientes activos</p>
        <p class="mt-2 text-3xl font-bold text-hotel-dark">{{ $totalClientes }}</p>
    </div>
    <div class="hotel-card animate-hotel-slide stagger-4 border-t-4 border-t-hotel-mid p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Ingresos recaudados</p>
        <p class="mt-2 text-3xl font-bold text-hotel-dark">${{ number_format((float) $ingresosRecaudados, 2) }}</p>
        <p class="mt-2 text-xs text-gray-600">
            Facturado: ${{ number_format((float) $ingresosTotales, 2) }} · Pendiente: ${{ number_format((float) $saldoPendiente, 2) }}
        </p>
    </div>
</div>

{{-- Gráficas --}}
<div class="mb-8 grid gap-6 lg:grid-cols-2">
    <div class="hotel-card animate-hotel-fade p-6">
        <h3 class="text-lg font-bold text-hotel-dark">Reservas por estado</h3>
        <div class="mt-4 chart-box">
            <canvas id="chartReservasEstado"></canvas>
        </div>
        <div class="hotel-table-wrap mt-6">
            <table class="hotel-table">
                <thead><tr><th>Estado</th><th>Cantidad</th><th>%</th></tr></thead>
                <tbody>
                    @foreach($reservasPorEstado as $estado => $cantidad)
                    <tr>
                        <td class="capitalize">{{ $estado }}</td>
                        <td>{{ $cantidad }}</td>
                        <td>{{ $totalReservas > 0 ? round(($cantidad / $totalReservas) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="hotel-card animate-hotel-fade p-6">
        <h3 class="text-lg font-bold text-hotel-dark">Ingresos últimos 6 meses</h3>
        <div class="mt-4 chart-box">
            <canvas id="chartIngresosMes"></canvas>
        </div>
        <div class="hotel-table-wrap mt-6">
            <table class="hotel-table">
                <thead><tr><th>Mes</th><th>Total ($)</th></tr></thead>
                <tbody>
                    @forelse($ingresosPorMes as $mes => $total)
                    <tr><td>{{ $mes }}</td><td>${{ number_format((float) $total, 2) }}</td></tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-gray-500">Sin ingresos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-8 grid gap-6 lg:grid-cols-2">
    <div class="hotel-card p-6 animate-hotel-slide">
        <h3 class="text-lg font-bold text-hotel-dark">Habitaciones más reservadas (por tipo)</h3>
        <div class="hotel-table-wrap mt-4">
            <table class="hotel-table">
                <thead><tr><th>Tipo</th><th>Reservas</th></tr></thead>
                <tbody>
                    @forelse($reservasPorTipo as $row)
                    <tr><td class="capitalize">{{ $row->tipo }}</td><td>{{ $row->total }}</td></tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-gray-500">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="hotel-card p-6 animate-hotel-slide">
        <h3 class="text-lg font-bold text-hotel-dark">Servicios más solicitados</h3>
        <div class="hotel-table-wrap mt-4">
            <table class="hotel-table">
                <thead><tr><th>#</th><th>Servicio</th><th>Unidades</th></tr></thead>
                <tbody>
                    @forelse($serviciosMasSolicitados as $i => $row)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $row->nombre }}</td><td>{{ $row->total_solicitado }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-gray-500">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="hotel-card p-6 animate-hotel-fade">
    <h3 class="text-lg font-bold text-hotel-dark">Top 5 clientes con más reservas</h3>
    <div class="hotel-table-wrap mt-4 max-w-2xl">
        <table class="hotel-table">
            <thead><tr><th>#</th><th>Cliente</th><th>Reservas</th></tr></thead>
            <tbody>
                @forelse($topClientes as $i => $row)
                <tr><td>{{ $i + 1 }}</td><td>{{ $row->nombre }}</td><td>{{ $row->total_reservas }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center text-gray-500">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    window.__metricasReservas = @json($reservasPorEstado);
    window.__metricasIngresos = @json($ingresosPorMes);
</script>
<script defer src="{{ asset('js/hotel-metricas.js') }}"></script>
@endpush
@endsection
