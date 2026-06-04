@extends('layouts.app')

@section('title', 'Crear factura')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Nueva factura',
    'pageSubtitle' => 'Genera una factura para reservas confirmadas pendientes de facturación.',
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <div class="mb-6 border-l-4 border-l-hotel bg-gray-50 px-4 py-3 text-sm text-gray-700">
        <div class="flex gap-2 items-center font-semibold text-hotel-dark mb-1">
            <i data-lucide="info" class="w-4 h-4"></i>
            Impuestos Incluidos
        </div>
        El IVA se calcula automáticamente al 13% (tasa estándar de El Salvador) sobre el total acumulado de la reserva.
    </div>

    <form action="{{ route('facturas.store') }}" method="POST" class="space-y-6">
        @csrf
        <div>
            <label class="hotel-label" for="reserva_select">Seleccionar Reserva Confirmada</label>
            <select name="reserva_id" id="reserva_select" class="hotel-select" required onchange="calcularPreview(this)">
                <option value="">-- Seleccione una reserva --</option>
                @foreach($reservas as $reserva)
                    <option value="{{ $reserva->id }}"
                        data-subtotal="{{ $reserva->subtotal_habitaciones + $reserva->subtotal_servicios }}">
                        Reserva #{{ $reserva->id }} — {{ $reserva->usuario->nombre }}
                        ({{ $reserva->fecha_entrada->format('d/m/Y') }} al {{ $reserva->fecha_salida->format('d/m/Y') }})
                        — ${{ number_format($reserva->subtotal_habitaciones + $reserva->subtotal_servicios, 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Vista Previa de Factura -->
        <div id="preview" style="display:none;" class="border-2 border-dashed border-gray-300 bg-gray-50 p-6 animate-hotel-fade">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-4">
                <span class="text-xs uppercase tracking-wider font-bold text-gray-500">Vista Previa de Factura</span>
                <span class="text-xs font-semibold px-2 py-0.5 bg-hotel/10 text-hotel border border-hotel/20 uppercase">Borrador</span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Subtotal reserva:</span>
                    <span class="font-mono font-semibold text-gray-800">$<span id="prev_subtotal">0.00</span></span>
                </div>
                <div class="flex justify-between">
                    <span>IVA (13.00%):</span>
                    <span class="font-mono font-semibold text-gray-800">$<span id="prev_iva">0.00</span></span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3 text-base text-hotel-dark font-bold">
                    <span>Total a Facturar:</span>
                    <span class="font-mono text-hotel">$<span id="prev_total">0.00</span></span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="file-text"></i>
                Generar factura
            </button>
            <a href="{{ route('facturas.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function calcularPreview(select) {
    const opt     = select.options[select.selectedIndex];
    const preview = document.getElementById('preview');
    if (!select.value) { preview.style.display = 'none'; return; }
    const subtotal = parseFloat(opt.dataset.subtotal) || 0;
    const iva      = Math.round(subtotal * 0.13 * 100) / 100;
    const total    = subtotal + iva;
    document.getElementById('prev_subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('prev_iva').textContent      = iva.toFixed(2);
    document.getElementById('prev_total').textContent    = total.toFixed(2);
    
    // Trigger slide/fade animation
    preview.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    // Force initial preview check on page load if select has value (e.g. old input redirect back)
    const select = document.getElementById('reserva_select');
    if (select && select.value) {
        calcularPreview(select);
    }
});
</script>
@endpush
@endsection