@extends('layouts.app')

@section('title', 'Editar servicio')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Editar servicio',
    'pageSubtitle' => $servicio->nombre,
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('servicios.update', $servicio->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="hotel-label" for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $servicio->nombre) }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label" for="precio">Precio (USD)</label>
            <input type="number" step="0.01" min="0" id="precio" name="precio" value="{{ old('precio', $servicio->precio) }}" class="hotel-input" required>
        </div>
        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" @checked($servicio->activo)>
            <label for="activo" class="text-sm font-medium text-gray-700">Servicio activo</label>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar cambios
            </button>
            <a href="{{ route('servicios.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
