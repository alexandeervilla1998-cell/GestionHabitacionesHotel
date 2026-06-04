@extends('layouts.app')

@section('title', 'Finanzas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Finanzas',
    'pageSubtitle' => 'Gestión de facturas y pagos del hotel.',
])

<div x-data="{ activeTab: 'facturas' }" class="animate-hotel-fade">
    <!-- Tabs -->
    <div class="flex gap-2 border-b border-gray-200 mb-6">
        <button @click="activeTab = 'facturas'"
                :class="activeTab === 'facturas' ? 'border-b-2 border-hotel text-hotel font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-3 text-sm font-semibold uppercase tracking-wide transition-colors">
            Facturas
        </button>
        <button @click="activeTab = 'pagos'"
                :class="activeTab === 'pagos' ? 'border-b-2 border-hotel text-hotel font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-3 text-sm font-semibold uppercase tracking-wide transition-colors">
            Pagos
        </button>
        @if(auth()->user()->rol === 'admin')
            <button @click="activeTab = 'metricas'"
                    :class="activeTab === 'metricas' ? 'border-b-2 border-hotel text-hotel font-bold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-semibold uppercase tracking-wide transition-colors">
                Métricas
            </button>
        @endif
    </div>

    <!-- Facturas Tab -->
    <div x-show="activeTab === 'facturas'" x-transition>
        <div class="flex justify-end mb-4">
            <a href="{{ route('facturas.create') }}" class="hotel-btn-primary">
                <i data-lucide="plus"></i> Nueva factura
            </a>
        </div>

        <div class="hotel-table-wrap">
            <table class="hotel-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Reserva</th>
                        <th>Cliente</th>
                        <th>Subtotal</th>
                        <th>Impuestos (13%)</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo Pendiente</th>
                        <th>Estado</th>
                        <th class="text-right">Opciones</th>
                    </tr>
                </thead>
                <tbody x-data="{ expanded: false, limit: 10 }">
                    @forelse($facturas as $factura)
                        <tr x-show="expanded || {{ $loop->index }} < limit" x-transition>
                            <td class="font-mono text-gray-500 font-semibold">#{{ $factura->id }}</td>
                            <td class="font-bold text-hotel-dark font-mono">{{ $factura->numero_factura }}</td>
                            <td class="font-semibold text-gray-700 font-mono">#{{ $factura->reserva_id }}</td>
                            <td>{{ $factura->reserva?->cliente?->nombre ?? 'N/A' }}</td>
                            <td class="font-mono">${{ number_format((float) $factura->subtotal, 2) }}</td>
                            <td class="font-mono">${{ number_format((float) $factura->impuestos, 2) }}</td>
                            <td class="font-bold text-hotel-dark font-mono">${{ number_format((float) $factura->total, 2) }}</td>
                            <td class="font-mono text-emerald-700 font-semibold">${{ number_format((float) $factura->total_pagado, 2) }}</td>
                            <td class="font-mono font-semibold {{ $factura->saldo_pendiente > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                ${{ number_format((float) $factura->saldo_pendiente, 2) }}
                            </td>
                            <td>
                                @if($factura->saldo_pendiente <= 0)
                                    <span class="hotel-badge-green">Pagado</span>
                                @elseif($factura->total_pagado > 0)
                                    <span class="hotel-badge-amber">Abonado</span>
                                @else
                                    <span class="hotel-badge-red">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('facturas.regenerar', $factura->id) }}" method="POST"
                                          data-confirm-delete="¿Regenerar la factura {{ $factura->numero_factura }} con los montos actualizados?">
                                        @csrf
                                        <button type="submit" class="hotel-btn hotel-btn-secondary py-1 px-3 text-xs flex items-center gap-1.5 font-bold">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                            Regenerar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-gray-500">
                                No hay facturas registradas en este momento.
                            </td>
                        </tr>
                    @endforelse
                    @if(count($facturas) > 10)
                        <tr>
                            <td colspan="11" class="py-4 text-center">
                                <button type="button" @click="expanded = !expanded" class="hotel-btn hotel-btn-secondary mx-auto">
                                    <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'"></i>
                                    <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'"></span>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagos Tab -->
    <div x-show="activeTab === 'pagos'" x-transition>
        <div class="flex justify-end mb-4">
            <a href="{{ route('pagos.create') }}" class="hotel-btn-primary">
                <i data-lucide="plus"></i> Registrar pago
            </a>
        </div>

        <div class="hotel-table-wrap">
            <table class="hotel-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-right">Opciones</th>
                    </tr>
                </thead>
                <tbody x-data="{ expanded: false, limit: 10 }">
                    @forelse($pagos as $pago)
                        <tr x-show="expanded || {{ $loop->index }} < limit" x-transition>
                            <td class="font-mono text-gray-500 font-semibold">#{{ $pago->id }}</td>
                            <td class="font-bold text-hotel-dark font-mono">{{ $pago->factura?->numero_factura ?? 'N/A' }}</td>
                            <td>{{ $pago->factura?->reserva?->cliente?->nombre ?? 'N/A' }}</td>
                            <td class="font-mono font-bold text-hotel-dark">${{ number_format((float) $pago->monto, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</td>
                            <td>
                                @if($pago->estado_pago === 'completado')
                                    <span class="hotel-badge-green">Completado</span>
                                @elseif($pago->estado_pago === 'pendiente')
                                    <span class="hotel-badge-amber">Pendiente</span>
                                @elseif($pago->estado_pago === 'fallido')
                                    <span class="hotel-badge-red">Fallido</span>
                                @elseif($pago->estado_pago === 'reembolsado')
                                    <span class="hotel-badge-gray">Reembolsado</span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-600">{{ $pago->creado_en ? $pago->creado_en->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if($pago->estado_pago === 'pendiente')
                                        <form action="{{ route('pagos.procesar', $pago->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="hotel-btn hotel-btn-secondary py-1 px-3 text-xs flex items-center gap-1.5 font-bold">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                Procesar
                                            </button>
                                        </form>
                                        <form action="{{ route('pagos.cancelar', $pago->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="hotel-btn hotel-btn-secondary py-1 px-3 text-xs flex items-center gap-1.5 font-bold">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('pagos.edit', $pago->id) }}" class="hotel-btn hotel-btn-secondary py-1 px-3 text-xs flex items-center gap-1.5 font-bold">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        Editar
                                    </a>
                                    <form action="{{ route('pagos.destroy', $pago->id) }}" method="POST"
                                          data-confirm-delete="¿Eliminar el pago #{{ $pago->id }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hotel-btn hotel-btn-danger py-1 px-3 text-xs flex items-center gap-1.5 font-bold">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-500">
                                No hay pagos registrados en este momento.
                            </td>
                        </tr>
                    @endforelse
                    @if(count($pagos) > 10)
                        <tr>
                            <td colspan="8" class="py-4 text-center">
                                <button type="button" @click="expanded = !expanded" class="hotel-btn hotel-btn-secondary mx-auto">
                                    <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'"></i>
                                    <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'"></span>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Métricas Tab (Solo Admin) -->
    @if(auth()->user()->rol === 'admin')
        <div x-show="activeTab === 'metricas'" x-transition>
            {{-- KPIs principales --}}
            <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="hotel-card animate-hotel-slide stagger-1 border-t-4 border-t-sky-600 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Total reservas</p>
                    <p class="mt-2 text-3xl font-bold text-hotel-dark">{{ $totalReservas }}</p>
                    <p class="mt-2 text-xs text-gray-600">
                        {{ $reservasConfirmadas }} confirmadas · {{ $reservasPendientes }} pendientes · {{ $reservasCanceladas }} canceladas
                    </p>
                </div>
                <div class="hotel-card animate-hotel-slide stagger-2 border-t-4 border-t-emerald-600 p-5">
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
        </div>
    @endif
</div>

@push('scripts')
@if(auth()->user()->rol === 'admin')
<script>
    window.__metricasReservas = @json($reservasPorEstado);
    window.__metricasIngresos = @json($ingresosPorMes);
</script>
<script defer src="{{ asset('js/hotel-metricas.js') }}"></script>
@endif
@endpush
@endsection
