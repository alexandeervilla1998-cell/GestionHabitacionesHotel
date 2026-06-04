@extends('layouts.app')

@section('title', 'Reservas')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Reservas',
    'pageSubtitle' => 'Gestión y control de reservas de habitaciones y servicios.',
    'pageActions' => '<a href="' . route('reservas.create') . '" class="hotel-btn-primary"><i data-lucide="plus"></i> Nueva reserva</a>',
])

<div x-data="{ expanded: false, limit: 10 }" class="space-y-4">
    <div class="hotel-table-wrap animate-hotel-fade">
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Total (USD)</th>
                    <th class="text-right">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $reserva)
                    <tr x-show="expanded || {{ $loop->index }} < limit" x-transition>
                        <td class="font-mono text-gray-500">#{{ $reserva->id }}</td>
                        <td class="font-semibold text-gray-900">{{ $reserva->usuario?->nombre }}</td>
                        <td>{{ optional($reserva->fecha_entrada)->format('d/m/Y') }}</td>
                        <td>{{ optional($reserva->fecha_salida)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $estadoClass = match($reserva->estado) {
                                    'pendiente' => 'hotel-badge-amber',
                                    'confirmada' => 'hotel-badge-green',
                                    'cancelada' => 'hotel-badge-red',
                                    'completada' => 'hotel-badge-gray',
                                    default => 'hotel-badge-gray',
                                };
                            @endphp
                            <span class="{{ $estadoClass }}">{{ $reserva->estado }}</span>
                        </td>
                        <td class="font-semibold text-hotel-dark">${{ number_format((float) $reserva->total, 2) }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('reservas.edit', $reserva->id) }}" class="hotel-btn hotel-btn-secondary">
                                    <i data-lucide="pencil"></i>
                                    Editar
                                </a>
                                @if($reserva->estado === 'pendiente')
                                    <form action="{{ route('reservas.confirmar', $reserva->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="hotel-btn hotel-btn-primary">
                                            <i data-lucide="check"></i>
                                            Confirmar
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($reserva->estado, ['pendiente', 'confirmada']))
                                    <form action="{{ route('reservas.cancelar', $reserva->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="hotel-btn hotel-btn-secondary text-red-700 border-red-200 hover:bg-red-50">
                                            <i data-lucide="x"></i>
                                            Cancelar
                                        </button>
                                    </form>
                                @endif
                                @if($reserva->estado === 'cancelada')
                                    <form action="{{ route('reservas.destroy', $reserva->id) }}" method="POST" class="inline"
                                          data-confirm-delete="¿Desactivar y eliminar permanentemente la reserva #{{ $reserva->id }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hotel-btn hotel-btn-danger">
                                            <i data-lucide="trash-2"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-500">
                            No hay reservas registradas en este momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(count($reservas) > 10)
        <div class="flex justify-center pt-2">
            <button type="button" @click="expanded = !expanded" class="hotel-btn hotel-btn-secondary">
                <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'"></i>
                <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'"></span>
            </button>
        </div>
    @endif
</div>
@endsection
