@extends('layouts.app')

@section('title', 'Habitaciones')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Habitaciones',
    'pageSubtitle' => 'Inventario de habitaciones, precios, tipos y disponibilidad.',
    'pageActions' => '<a href="' . route('habitaciones.create') . '" class="hotel-btn-primary"><i data-lucide="plus"></i> Nueva habitación</a>',
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-4 border-2 border-gray-200 rounded animate-hotel-fade">
    <form action="{{ route('habitaciones.index') }}" method="GET" class="flex flex-col gap-3 w-full sm:flex-row sm:items-center">
        <!-- Search bar -->
        <div class="relative flex-grow">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por número, tipo o estado..." class="hotel-input pl-9" style="padding-left: 2.25rem;">
        </div>
        
        <!-- Filter select -->
        <div class="w-full sm:w-48">
            <select name="estado" onchange="this.form.submit()" class="hotel-select">
                <option value="activo" @selected(request('estado', 'activo') === 'activo')>Activas</option>
                <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivas</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2">
            <button type="submit" class="hotel-btn hotel-btn-primary">
                Buscar
            </button>
            @if(request('buscar') || request('estado') === 'inactivo')
                <a href="{{ route('habitaciones.index') }}" class="hotel-btn hotel-btn-secondary">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

<div x-data="{ expanded: false, limit: 10 }" class="space-y-4">
    <div class="hotel-table-wrap animate-hotel-fade">
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Precio / noche</th>
                    <th>Estado</th>
                    <th>Activo</th>
                    <th class="text-right">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($habitaciones as $habitacion)
                    <tr x-show="expanded || {{ $loop->index }} < limit" x-transition>
                        <td class="font-mono text-gray-500">#{{ $habitacion->id }}</td>
                        <td>
                            @if($habitacion->imagen)
                                <img src="{{ asset($habitacion->imagen) }}" alt="Habitación {{ $habitacion->numero }}" 
                                     onclick="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: '{{ asset($habitacion->imagen) }}' } }))"
                                     class="h-12 w-20 object-cover border border-gray-200 rounded shadow-sm cursor-pointer hover:opacity-85 transition-opacity" title="Ampliar imagen">
                            @else
                                <div class="h-12 w-20 bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400 font-medium rounded">
                                    Sin foto
                                </div>
                            @endif
                        </td>
                        <td class="text-lg font-bold text-hotel-dark">{{ $habitacion->numero }}</td>
                        <td class="capitalize">{{ $habitacion->tipo }}</td>
                        <td class="font-semibold">${{ number_format((float) $habitacion->precio_por_noche, 2) }}</td>
                        <td>
                            @php
                                $estadoClass = match($habitacion->estado) {
                                    'disponible' => 'hotel-badge-green',
                                    'ocupada' => 'hotel-badge-amber',
                                    'mantenimiento' => 'hotel-badge-red',
                                    default => 'hotel-badge-gray',
                                };
                            @endphp
                            <span class="{{ $estadoClass }}">{{ $habitacion->estado }}</span>
                        </td>
                        <td>
                            @if($habitacion->activo)
                                <span class="hotel-badge-green">Sí</span>
                            @else
                                <span class="hotel-badge-gray">No</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('habitaciones.edit', $habitacion->id) }}" class="hotel-btn hotel-btn-secondary">
                                    <i data-lucide="pencil"></i>
                                    Editar
                                </a>
                                <form action="{{ route('habitaciones.destroy', $habitacion->id) }}" method="POST"
                                      data-confirm-delete="¿Eliminar la habitación {{ $habitacion->numero }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="hotel-btn hotel-btn-danger">
                                        <i data-lucide="trash-2"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-500">
                            No hay habitaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(count($habitaciones) > 10)
        <div class="flex justify-center pt-2">
            <button type="button" @click="expanded = !expanded" class="hotel-btn hotel-btn-secondary">
                <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'"></i>
                <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'"></span>
            </button>
        </div>
    @endif
</div>
@endsection
