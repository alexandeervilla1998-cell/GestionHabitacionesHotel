@extends('layouts.app')

@section('title', 'Editar factura')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Editar factura',
    'pageSubtitle' => 'Modificar estado de factura #' . $factura->numero_factura,
])

<div class="hotel-card max-w-md animate-hotel-scale p-8">
    <form action="{{ route('facturas.update', $factura->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        
        <div class="space-y-2 text-sm text-gray-700">
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <span class="font-semibold text-gray-500">Número de factura:</span>
                <span class="font-mono font-bold text-hotel-dark">{{ $factura->numero_factura }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <span class="font-semibold text-gray-500">Monto total (USD):</span>
                <span class="font-mono font-bold text-hotel-dark">${{ number_format((float) $factura->total, 2) }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" @checked($factura->activo)>
            <label for="activo" class="text-sm font-medium text-gray-700">Factura activa / válida</label>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar
            </button>
            <a href="{{ route('facturas.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
