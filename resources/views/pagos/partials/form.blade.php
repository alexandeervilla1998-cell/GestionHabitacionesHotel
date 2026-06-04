<div class="space-y-4" x-data="{
    subtotalHabitaciones: 0,
    subtotalServicios: 0,
    iva: 0,
    total: 0,
    saldoPendiente: 0,
    hasFactura: false,
    updateCalculos() {
        const select = document.getElementById('factura_id');
        if (!select) return;
        const opt = select.options[select.selectedIndex];
        if (select.value && opt) {
            this.subtotalHabitaciones = parseFloat(opt.dataset.subtotalHabitaciones) || 0;
            this.subtotalServicios = parseFloat(opt.dataset.subtotalServicios) || 0;
            this.iva = parseFloat(opt.dataset.iva) || 0;
            this.total = parseFloat(opt.dataset.total) || 0;
            this.saldoPendiente = parseFloat(opt.dataset.saldoPendiente) || 0;
            this.hasFactura = true;
            
            // Set the amount only if not editing an existing payment
            const montoInput = document.getElementById('monto');
            if (montoInput && !montoInput.dataset.editing) {
                montoInput.value = this.saldoPendiente.toFixed(2);
            }
        } else {
            this.subtotalHabitaciones = 0;
            this.subtotalServicios = 0;
            this.iva = 0;
            this.total = 0;
            this.saldoPendiente = 0;
            this.hasFactura = false;
            const montoInput = document.getElementById('monto');
            if (montoInput && !montoInput.dataset.editing) {
                montoInput.value = '';
            }
        }
    }
}" x-init="updateCalculos()">
    <div>
        <label class="hotel-label" for="factura_id">Factura Relacionada</label>
        <select name="factura_id" id="factura_id" class="hotel-select bg-gray-50 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed" required @isset($pago) disabled @endisset @change="updateCalculos()">
            <option value="">-- Seleccione una factura --</option>
            @foreach($facturas as $factura)
                <option value="{{ $factura->id }}" 
                    data-subtotal-habitaciones="{{ $factura->reserva?->subtotal_habitaciones ?? 0 }}"
                    data-subtotal-servicios="{{ $factura->reserva?->subtotal_servicios ?? 0 }}"
                    data-iva="{{ $factura->impuestos }}"
                    data-total="{{ $factura->total }}"
                    data-saldo-pendiente="{{ $factura->saldo_pendiente }}"
                    @selected(isset($pago) && $pago->factura_id == $factura->id)>
                    {{ $factura->numero_factura }} — Saldo Pendiente: ${{ number_format((float) $factura->saldo_pendiente, 2) }}
                </option>
            @endforeach
        </select>
        @isset($pago)
            <input type="hidden" name="factura_id" value="{{ $pago->factura_id }}">
        @endisset
    </div>

    <!-- Breakdown Card -->
    <div x-show="hasFactura" class="border-2 border-dashed border-gray-300 bg-gray-50 p-4 rounded animate-hotel-fade" x-transition>
        <h4 class="text-xs uppercase tracking-wider font-bold text-gray-500 mb-3 border-b pb-1">Desglose de Facturación</h4>
        <div class="space-y-1.5 text-sm text-gray-600">
            <div class="flex justify-between">
                <span>Costo Habitación:</span>
                <span class="font-mono font-semibold text-gray-800">$<span x-text="subtotalHabitaciones.toFixed(2)">0.00</span></span>
            </div>
            <div class="flex justify-between">
                <span>Costo Servicios:</span>
                <span class="font-mono font-semibold text-gray-800">$<span x-text="subtotalServicios.toFixed(2)">0.00</span></span>
            </div>
            <div class="flex justify-between">
                <span>IVA (13%):</span>
                <span class="font-mono font-semibold text-gray-800">$<span x-text="iva.toFixed(2)">0.00</span></span>
            </div>
            <div class="flex justify-between border-t pt-1.5 font-bold">
                <span>Total Factura:</span>
                <span class="font-mono text-hotel-dark">$<span x-text="total.toFixed(2)">0.00</span></span>
            </div>
            <div class="flex justify-between text-hotel-light font-bold">
                <span>Saldo Pendiente:</span>
                <span class="font-mono">$<span x-text="saldoPendiente.toFixed(2)">0.00</span></span>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="hotel-label" for="monto">Monto del Pago (USD)</label>
            <input type="number" step="0.01" min="0.01" name="monto" id="monto" 
                   value="{{ old('monto', isset($pago) ? number_format((float) $pago->monto, 2, '.', '') : '') }}" 
                   @isset($pago) data-editing="true" @endisset
                   class="hotel-input font-mono bg-gray-100 cursor-not-allowed" required readonly placeholder="0.00">
            <p class="text-xs text-gray-500 mt-1">Calculado automáticamente del saldo pendiente.</p>
        </div>

        <div>
            <label class="hotel-label" for="metodo_pago">Método de Pago</label>
            <select name="metodo_pago" id="metodo_pago" class="hotel-select" required>
                @foreach(['efectivo' => 'Efectivo', 'tarjeta_credito' => 'Tarjeta crédito', 'tarjeta_debito' => 'Tarjeta débito', 'transferencia' => 'Transferencia'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('metodo_pago', $pago->metodo_pago ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="hotel-label" for="estado_pago">Estado del Pago</label>
            <select name="estado_pago" id="estado_pago" class="hotel-select" required>
                @foreach(['pendiente' => 'Pendiente', 'completado' => 'Completado', 'fallido' => 'Fallido', 'reembolsado' => 'Reembolsado'] as $estadoKey => $estadoLabel)
                    <option value="{{ $estadoKey }}" @selected(old('estado_pago', $pago->estado_pago ?? 'pendiente') === $estadoKey)>{{ $estadoLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>