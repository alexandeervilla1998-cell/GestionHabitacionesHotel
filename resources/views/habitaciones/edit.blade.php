@extends('layouts.app')

@section('title', 'Editar habitación')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Editar habitación',
    'pageSubtitle' => 'Habitación #' . $habitacion->numero,
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('habitaciones.update', $habitacion->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="hotel-label" for="numero">Número</label>
            <span id="numero-estado" class="mb-1 block text-sm font-semibold"></span>
            <input type="text" id="numero" name="numero" value="{{ old('numero', $habitacion->numero) }}" class="hotel-input" required autocomplete="off">
        </div>
        <div>
            <label class="hotel-label">Tipo</label>
            @include('habitaciones.partials.tipo', ['value' => $habitacion->tipo])
        </div>
        <div>
            <label class="hotel-label" for="precio_por_noche">Precio por noche (USD)</label>
            <input type="number" step="0.01" min="0" id="precio_por_noche" name="precio_por_noche" value="{{ old('precio_por_noche', $habitacion->precio_por_noche) }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label">Estado</label>
            @include('habitaciones.partials.estado', ['value' => $habitacion->estado, 'hideOcupada' => true])
        </div>
        <div>
            <label class="hotel-label" for="imagen">Imagen de la habitación</label>
            @if($habitacion->imagen)
                <div class="mb-2">
                    <img src="{{ asset($habitacion->imagen) }}" alt="Habitación {{ $habitacion->numero }}" 
                         onclick="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: '{{ asset($habitacion->imagen) }}' } }))"
                         class="h-32 w-auto object-cover border-2 border-gray-200 rounded cursor-pointer hover:opacity-85 transition-opacity" title="Ampliar imagen">
                    <span class="text-xs text-gray-500 block mt-1">Imagen actual: {{ $habitacion->imagen }}</span>
                </div>
            @endif
            <input type="file" id="imagen" name="imagen" class="hotel-input" accept="image/*">
        </div>
        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" @checked($habitacion->activo)>
            <label for="activo" class="text-sm font-medium text-gray-700">Habitación activa</label>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar cambios
            </button>
            <a href="{{ route('habitaciones.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('numero');
    const estado = document.getElementById('numero-estado');
    const exceptId = @json($habitacion->id);
    const url = @json(route('habitaciones.verificar-numero'));

    const verificar = window.HotelApp.debounce(async function () {
        const numero = input.value.trim();
        if (!numero) {
            estado.textContent = '';
            return;
        }
        try {
            const res = await fetch(url + '?numero=' + encodeURIComponent(numero) + '&except_id=' + exceptId);
            const data = await res.json();
            if (data.existe) {
                estado.textContent = 'Este número ya existe.';
                estado.className = 'mb-1 block text-sm font-semibold text-red-700';
            } else {
                estado.textContent = 'Número disponible.';
                estado.className = 'mb-1 block text-sm font-semibold text-cyan-700';
            }
        } catch (_) {
            estado.textContent = '';
        }
    }, 300);

    input.addEventListener('input', verificar);
    verificar();
})();
</script>
@endpush
@endsection
