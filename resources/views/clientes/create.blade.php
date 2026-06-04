@extends('layouts.app')

@section('title', 'Crear cliente')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Nuevo cliente',
    'pageSubtitle' => 'Registra un nuevo cliente en el sistema.',
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('clientes.store') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="hotel-label" for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label" for="correo">Correo</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label" for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" class="hotel-input">
        </div>
        <div>
            <label class="hotel-label" for="identificacion">Identificación</label>
            <input type="text" id="identificacion" name="identificacion" value="{{ old('identificacion') }}" class="hotel-input">
        </div>
        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" checked>
            <label for="activo" class="text-sm font-medium text-gray-700">Cliente activo</label>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar
            </button>
            <a href="{{ route('clientes.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
