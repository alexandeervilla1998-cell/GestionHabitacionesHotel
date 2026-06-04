@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Usuarios',
    'pageSubtitle' => 'Administración de cuentas, roles y acceso al sistema.',
    'pageActions' => '<a href="' . route('usuarios.create') . '" class="hotel-btn-primary"><i data-lucide="user-plus"></i> Crear usuario</a>',
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-4 border-2 border-gray-200 rounded animate-hotel-fade">
    <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-col gap-3 w-full sm:flex-row sm:items-center">
        <!-- Search bar -->
        <div class="relative flex-grow">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, correo o rol..." class="hotel-input pl-9" style="padding-left: 2.25rem;">
        </div>
        
        <!-- Filter select -->
        <div class="w-full sm:w-48">
            <select name="estado" onchange="this.form.submit()" class="hotel-select">
                <option value="activo" @selected(request('estado', 'activo') === 'activo')>Activos</option>
                <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2">
            <button type="submit" class="hotel-btn hotel-btn-primary">
                Buscar
            </button>
            @if(request('buscar') || request('estado') === 'inactivo')
                <a href="{{ route('usuarios.index') }}" class="hotel-btn hotel-btn-secondary">
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
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-right">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr x-show="expanded || {{ $loop->index }} < limit" x-transition>
                        <td class="font-mono text-gray-500">#{{ $usuario->id }}</td>
                        <td class="font-semibold text-gray-900">{{ $usuario->nombre }}</td>
                        <td>{{ $usuario->correo }}</td>
                        <td>
                            @php
                                $rolClass = match($usuario->rol) {
                                    'admin' => 'hotel-badge-green',
                                    'recepcionista' => 'hotel-badge-blue',
                                    default => 'hotel-badge-gray',
                                };
                            @endphp
                            <span class="{{ $rolClass }}">{{ $usuario->rol }}</span>
                        </td>
                        <td>
                            @if($usuario->activo)
                                <span class="hotel-badge-green">Activo</span>
                            @else
                                <span class="hotel-badge-red">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="hotel-btn hotel-btn-secondary">
                                    <i data-lucide="pencil"></i>
                                    Editar
                                </a>
                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                      data-confirm-delete="¿Eliminar al usuario {{ $usuario->nombre }}?">
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
                        <td colspan="6" class="py-12 text-center text-gray-500">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(count($usuarios) > 10)
        <div class="flex justify-center pt-2">
            <button type="button" @click="expanded = !expanded" class="hotel-btn hotel-btn-secondary">
                <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'"></i>
                <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'"></span>
            </button>
        </div>
    @endif
</div>
@endsection
