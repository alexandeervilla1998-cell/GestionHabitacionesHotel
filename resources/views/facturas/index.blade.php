@extends('layouts.app')

@section('title', 'Facturas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Facturas',
    'pageSubtitle' => 'Historial de facturación de reservas, impuestos y estados de cuenta.',
    'pageActions' => '<a href="' . route('facturas.create') . '" class="hotel-btn-primary"><i data-lucide="plus"></i> Nueva factura</a>',
])

<div class="hotel-table-wrap animate-hotel-fade">
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
                    <td class="font-mono text-cyan-700 font-semibold">${{ number_format((float) $factura->total_pagado, 2) }}</td>
                    <td class="font-mono font-semibold {{ $factura->saldo_pendiente > 0 ? 'text-red-600' : 'text-cyan-600' }}">
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
@endsection
