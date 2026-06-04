@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Clientes',
    'pageSubtitle' => 'Gestión y control de clientes del hotel.',
    'pageActions' => '<a href="' . route('clientes.create') . '" class="hotel-btn-primary"><i data-lucide="plus"></i> Nuevo cliente</a>',
])

<div class="space-y-4">
    <div class="hotel-card animate-hotel-fade p-4">
        <form action="{{ route('clientes.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="hotel-label text-sm">Buscar</label>
                <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo, teléfono, identificación..." class="hotel-input">
            </div>
            <div>
                <label class="hotel-label text-sm">Estado</label>
                <select name="estado" class="hotel-select">
                    <option value="activo" @selected($estado === 'activo')">Activos</option>
                    <option value="inactivo" @selected($estado === 'inactivo')">Inactivos</option>
                </select>
            </div>
            <button type="submit" class="hotel-btn-secondary">
                <i data-lucide="search"></i>
                Buscar
            </button>
            @if($buscar || $estado !== 'activo')
                <a href="{{ route('clientes.index') }}" class="hotel-btn-ghost">
                    <i data-lucide="x"></i>
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <div class="hotel-table-wrap animate-hotel-fade">
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Identificación</th>
                    <th>Estado</th>
                    <th class="text-right">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td class="font-mono text-gray-500">#{{ $cliente->id }}</td>
                        <td class="font-semibold text-gray-900">{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->correo }}</td>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                        <td>{{ $cliente->identificacion ?? '-' }}</td>
                        <td>
                            @if($cliente->activo)
                                <span class="hotel-badge-green">Activo</span>
                            @else
                                <span class="hotel-badge-red">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="hotel-btn hotel-btn-secondary">
                                    <i data-lucide="pencil"></i>
                                    Editar
                                </a>
                                @if($cliente->activo)
                                    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="inline"
                                          data-confirm-delete="¿Desactivar el cliente {{ $cliente->nombre }}?">
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
                            No hay clientes registrados en este momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
