@extends('layouts.app')

@section('title', 'Servicios de Reservas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Servicios de Reservas',
    'pageSubtitle' => 'Historial y desglose de servicios adicionales solicitados en las reservas.',
])

<div class="hotel-table-wrap animate-hotel-fade">
    <table class="hotel-table">
        <thead>
            <tr>
                <th>ID Registro</th>
                <th>Reserva</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th class="text-right">Subtotal (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservaServicios as $item)
                <tr>
                    <td class="font-mono text-gray-500">#{{ $item->id }}</td>
                    <td class="font-semibold text-hotel-dark font-mono">#{{ $item->reserva_id }}</td>
                    <td>{{ $item->reserva?->cliente?->nombre ?? 'N/A' }}</td>
                    <td class="font-semibold text-gray-900">{{ $item->servicio?->nombre ?? 'N/A' }}</td>
                    <td class="font-mono">{{ $item->cantidad }}</td>
                    <td>${{ number_format((float) $item->precio_unitario, 2) }}</td>
                    <td class="text-right font-semibold text-hotel-dark font-mono">${{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-500">
                        No hay servicios de reservas registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
