@extends('layouts.app')

@section('title', 'Crear habitación')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Nueva habitación',
    'pageSubtitle' => 'Registra una habitación en el inventario del hotel.',
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('habitaciones.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div>
            <label class="hotel-label" for="numero">Número</label>
            <span id="numero-estado" class="mb-1 block text-sm font-semibold"></span>
            <input type="text" id="numero" name="numero" value="{{ old('numero') }}" class="hotel-input" required autocomplete="off">
        </div>
        <div>
            <label class="hotel-label">Tipo</label>
            @include('habitaciones.partials.tipo')
        </div>
        <div>
            <label class="hotel-label" for="precio_por_noche">Precio por noche (USD)</label>
            <input type="number" step="0.01" min="0" id="precio_por_noche" name="precio_por_noche" placeholder="190.00" value="{{ old('precio_por_noche') }}" class="hotel-input" required>
        </div>
        <div>
            <label class="hotel-label">Estado</label>
            @include('habitaciones.partials.estado')
        </div>
        <div>
            <label class="hotel-label" for="imagen">Imagen de la habitación</label>
            <input type="file" id="imagen" name="imagen" class="hotel-input" accept="image/*">
        </div>
        <div class="flex items-center gap-2 border-2 border-gray-200 bg-gray-50 px-4 py-3">
            <input type="checkbox" name="activo" id="activo" value="1" class="h-4 w-4 accent-hotel-mid" checked>
            <label for="activo" class="text-sm font-medium text-gray-700">Habitación activa</label>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar
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
    const url = @json(route('habitaciones.verificar-numero'));

    const verificar = window.HotelApp.debounce(async function () {
        const numero = input.value.trim();
        if (!numero) {
            estado.textContent = '';
            estado.className = 'mb-1 block text-sm font-semibold';
            return;
        }
        try {
            const res = await fetch(url + '?numero=' + encodeURIComponent(numero));
            const data = await res.json();
            if (data.existe) {
                estado.textContent = 'Este número ya existe.';
                estado.className = 'mb-1 block text-sm font-semibold text-red-700';
            } else {
                estado.textContent = 'Número disponible.';
                estado.className = 'mb-1 block text-sm font-semibold text-emerald-700';
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
