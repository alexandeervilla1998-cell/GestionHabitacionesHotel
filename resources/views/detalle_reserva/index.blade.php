@extends('layouts.app')

@section('title', 'Detalles de Reservas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Detalles de Reservas',
    'pageSubtitle' => 'Desglose detallado de las habitaciones asignadas a cada reserva y sus costos.',
])

<div class="hotel-table-wrap animate-hotel-fade">
    <table class="hotel-table">
        <thead>
            <tr>
                <th>ID Detalle</th>
                <th>Reserva</th>
                <th>Cliente</th>
                <th>Habitación</th>
                <th>Noches</th>
                <th>Precio / Noche</th>
                <th class="text-right">Subtotal (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $detalle)
                <tr>
                    <td class="font-mono text-gray-500">#{{ $detalle->id }}</td>
                    <td class="font-semibold text-hotel-dark font-mono">#{{ $detalle->reserva_id }}</td>
                    <td>{{ $detalle->reserva?->usuario?->nombre ?? 'N/A' }}</td>
                    <td>
                        <span class="font-semibold text-gray-900">{{ $detalle->habitacion?->numero ?? 'N/A' }}</span>
                        @if($detalle->habitacion)
                            <span class="text-xs text-gray-500">({{ $detalle->habitacion->tipo }})</span>
                        @endif
                    </td>
                    <td class="font-semibold">{{ $detalle->noches }}</td>
                    <td>${{ number_format((float) $detalle->precio_noche, 2) }}</td>
                    <td class="text-right font-semibold text-hotel-dark font-mono">${{ number_format((float) $detalle->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-500">
                        No hay detalles de reservas registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
