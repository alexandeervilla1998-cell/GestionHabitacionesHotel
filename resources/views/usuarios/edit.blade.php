@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Editar usuario',
    'pageSubtitle' => 'Modifica los datos de ' . $usuario->nombre,
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="hotel-label" for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label" for="correo">Correo</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo', $usuario->correo) }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label" for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password" class="hotel-input">
            <p class="mt-1 text-xs text-gray-500">Dejar vacío para no cambiar</p>
        </div>
        <div>
            <label class="hotel-label" for="rol">Rol</label>
            <select name="rol" id="rol" class="hotel-select" required>
                <option value="admin" @selected($usuario->rol === 'admin')">Admin</option>
                <option value="recepcionista" @selected($usuario->rol === 'recepcionista')">Recepcionista</option>
            </select>
        </div>
        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" @checked($usuario->activo)>
            <label for="activo" class="text-sm font-medium text-gray-700">Usuario activo</label>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar cambios
            </button>
            <a href="{{ route('usuarios.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
