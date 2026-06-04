@extends('layouts.app')

@section('title', 'Crear reserva')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Nueva reserva',
    'pageSubtitle' => 'Registra una nueva reserva de habitación y servicios para un cliente.',
])

<div class="hotel-card max-w-4xl animate-hotel-scale p-8">
    <form action="{{ route('reservas.store') }}" method="POST" id="form-reserva" class="space-y-6">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="hotel-label" for="cliente_id">Cliente</label>
                <select name="cliente_id" id="cliente_id" class="hotel-select" required>
                    <option value="">Seleccione un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                            {{ $cliente->nombre }} ({{ $cliente->correo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="hotel-label" for="habitacion_id">Habitación</label>
                <select name="habitacion_id" id="habitacion_id" class="hotel-select" required>
                    <option value="">Seleccione una habitación</option>
                    @foreach($habitaciones as $habitacion)
                        <option value="{{ $habitacion->id }}" data-precio="{{ $habitacion->precio_por_noche }}" @selected(old('habitacion_id') == $habitacion->id)>
                            {{ $habitacion->numero }} — {{ $habitacion->tipo }} (${{ number_format((float) $habitacion->precio_por_noche, 2) }}/noche)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="hotel-label" for="fecha_entrada">Fecha de entrada</label>
                <input type="date" name="fecha_entrada" id="fecha_entrada" value="{{ old('fecha_entrada') }}" class="hotel-input" required>
            </div>

            <div>
                <label class="hotel-label" for="fecha_salida">Fecha de salida</label>
                <input type="date" name="fecha_salida" id="fecha_salida" value="{{ old('fecha_salida') }}" class="hotel-input" required>
            </div>
        </div>

        <div class="border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500">
            <span id="fechas-estado" class="block font-semibold"></span>
            La reserva se calcula por noches. La fecha de salida debe ser posterior a la de entrada.
        </div>

        <hr class="border-gray-200">

        @include('reservas.partials.form-servicios', ['serviciosReserva' => []])

        <div class="flex flex-wrap gap-3 pt-4">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Crear reserva
            </button>
            <a href="{{ route('reservas.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const entrada = document.getElementById('fecha_entrada');
    const salida = document.getElementById('fecha_salida');
    const estado = document.getElementById('fechas-estado');

    function validarFechas() {
        if (!entrada.value || !salida.value) {
            estado.textContent = 'Ingrese fechas para calcular la estadía.';
            estado.className = 'block font-semibold text-gray-600';
            return;
        }
        const e = new Date(entrada.value + 'T00:00:00');
        const s = new Date(salida.value + 'T00:00:00');
        const noches = Math.round((s - e) / (1000 * 60 * 60 * 24));
        if (noches < 1) {
            estado.textContent = 'Error: La fecha de salida debe ser posterior a la fecha de entrada.';
            estado.className = 'block font-semibold text-red-700';
        } else {
            estado.textContent = 'Estadía: ' + noches + ' noche(s) calculada(s).';
            estado.className = 'block font-semibold text-emerald-700';
        }
    }

    entrada.addEventListener('change', validarFechas);
    salida.addEventListener('change', validarFechas);
    validarFechas();
})();
</script>
@endpush
@endsection
