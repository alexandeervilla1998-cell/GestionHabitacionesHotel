@php
    $serviciosJson = $servicios->map(fn ($s) => [
        'id' => $s->id,
        'nombre' => $s->nombre,
        'precio' => (float) $s->precio,
    ])->values();
    $habitacionesJson = $habitaciones->map(fn ($h) => [
        'id' => $h->id,
        'numero' => $h->numero,
        'precio' => (float) $h->precio_por_noche,
    ])->values();
    $serviciosIniciales = $serviciosReserva ?? [];
@endphp

<div class="space-y-4">
    <h3 class="text-md font-bold uppercase tracking-wider text-hotel-mid">Servicios adicionales</h3>
    
    <div class="flex flex-wrap items-end gap-3 border-2 border-gray-200 bg-gray-50 p-4">
        <div class="w-full sm:w-auto sm:flex-1">
            <label class="hotel-label" for="servicio-select">Seleccionar servicio</label>
            <select id="servicio-select" class="hotel-select">
                <option value="">Sin servicio</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" data-precio="{{ $servicio->precio }}">{{ $servicio->nombre }} — ${{ number_format((float) $servicio->precio, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="hotel-label" for="servicio-cantidad">Cantidad</label>
            <input type="number" id="servicio-cantidad" min="1" value="1" class="hotel-input">
        </div>
        <div>
            <button type="button" id="btn-agregar-servicio" class="hotel-btn-secondary">
                <i data-lucide="plus"></i>
                Agregar
            </button>
        </div>
    </div>

    <div id="servicios-tabla-wrap" style="display:none;" class="hotel-table-wrap">
        <table class="hotel-table" id="servicios-tabla">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Precio unit. (USD)</th>
                    <th class="w-32">Cantidad</th>
                    <th>Subtotal</th>
                    <th class="text-right">Opciones</th>
                </tr>
            </thead>
            <tbody id="servicios-tbody"></tbody>
        </table>
    </div>

    <div class="grid gap-4 border-2 border-hotel-dark bg-hotel-dark p-6 text-white sm:grid-cols-2">
        <div class="flex items-center gap-3">
            <span class="text-sm uppercase tracking-widest text-hotel-muted font-bold">Noches de estadía:</span>
            <span id="prev-noches" class="text-xl font-bold font-mono">0</span>
        </div>
        <div class="flex items-center sm:justify-end gap-3">
            <span class="text-sm uppercase tracking-widest text-hotel-muted font-bold">Total estimado:</span>
            <span class="text-2xl font-bold font-mono text-cyan-400">$<span id="prev-total">0.00</span></span>
        </div>
    </div>
</div>

<script>
(function () {
    const catalogo = @json($serviciosJson);
    const habitaciones = @json($habitacionesJson);
    let lineas = @json($serviciosIniciales);

    const selectServicio = document.getElementById('servicio-select');
    const inputCantidad = document.getElementById('servicio-cantidad');
    const btnAgregar = document.getElementById('btn-agregar-servicio');
    const wrapTabla = document.getElementById('servicios-tabla-wrap');
    const tbody = document.getElementById('servicios-tbody');
    const habitacionSelect = document.querySelector('[name="habitacion_id"]');
    const fechaEntrada = document.querySelector('[name="fecha_entrada"]');
    const fechaSalida = document.querySelector('[name="fecha_salida"]');
    const form = habitacionSelect ? habitacionSelect.closest('form') : null;

    function nombreServicio(id) {
        const s = catalogo.find(x => x.id === id);
        return s ? s.nombre : 'Servicio';
    }

    function precioServicio(id) {
        const s = catalogo.find(x => x.id === id);
        return s ? s.precio : 0;
    }

    function calcularNoches() {
        if (!fechaEntrada || !fechaSalida || !fechaEntrada.value || !fechaSalida.value) return 0;
        const entrada = new Date(fechaEntrada.value + 'T00:00:00');
        const salida = new Date(fechaSalida.value + 'T00:00:00');
        const diff = Math.round((salida - entrada) / (1000 * 60 * 60 * 24));
        return diff > 0 ? diff : 0;
    }

    function precioHabitacion() {
        if (!habitacionSelect) return 0;
        const h = habitaciones.find(x => x.id === parseInt(habitacionSelect.value, 10));
        return h ? h.precio : 0;
    }

    function actualizarTotales() {
        const noches = calcularNoches();
        const subHabitacion = noches * precioHabitacion();
        const subServicios = lineas.reduce((sum, l) => sum + (l.cantidad * l.precio), 0);
        document.getElementById('prev-noches').textContent = noches;
        document.getElementById('prev-total').textContent = (subHabitacion + subServicios).toFixed(2);
    }

    function renderTabla() {
        tbody.innerHTML = '';
        lineas.forEach((linea, index) => {
            const sub = linea.cantidad * linea.precio;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-semibold text-gray-900">${nombreServicio(linea.servicio_id)}</td>
                <td class="font-semibold">$${linea.precio.toFixed(2)}</td>
                <td>
                    <input type="number" min="1" value="${linea.cantidad}" data-index="${index}" class="cantidad-linea hotel-input py-1 px-2 w-20 text-center font-mono">
                </td>
                <td class="font-semibold font-mono text-hotel-dark">$${sub.toFixed(2)}</td>
                <td class="text-right">
                    <button type="button" data-index="${index}" class="btn-quitar hotel-btn-danger py-1 px-2 text-xs">
                        <i data-lucide="trash-2"></i>
                        Quitar
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        wrapTabla.style.display = lineas.length ? 'block' : 'none';
        actualizarTotales();
        sincronizarHidden();

        if (window.HotelApp && typeof window.HotelApp.initLucideButtons === 'function') {
            window.HotelApp.initLucideButtons();
        }
    }

    function sincronizarHidden() {
        if (!form) return;
        form.querySelectorAll('input[data-servicio-hidden]').forEach(el => el.remove());
        lineas.forEach((linea, i) => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `servicios[${i}][servicio_id]`;
            idInput.value = linea.servicio_id;
            idInput.setAttribute('data-servicio-hidden', '1');
            form.appendChild(idInput);

            const cantInput = document.createElement('input');
            cantInput.type = 'hidden';
            cantInput.name = `servicios[${i}][cantidad]`;
            cantInput.value = linea.cantidad;
            cantInput.setAttribute('data-servicio-hidden', '1');
            form.appendChild(cantInput);
        });
    }

    btnAgregar.addEventListener('click', function () {
        const servicioId = parseInt(selectServicio.value, 10);
        const cantidad = parseInt(inputCantidad.value, 10) || 0;
        if (!servicioId || cantidad < 1) return;

        const existente = lineas.find(l => l.servicio_id === servicioId);
        if (existente) {
            existente.cantidad += cantidad;
        } else {
            lineas.push({
                servicio_id: servicioId,
                precio: precioServicio(servicioId),
                cantidad: cantidad,
            });
        }

        selectServicio.value = '';
        inputCantidad.value = 1;
        renderTabla();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quitar');
        if (btn) {
            lineas.splice(parseInt(btn.dataset.index, 10), 1);
            renderTabla();
        }
    });

    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('cantidad-linea')) {
            const idx = parseInt(e.target.dataset.index, 10);
            lineas[idx].cantidad = Math.max(1, parseInt(e.target.value, 10) || 1);
            renderTabla();
        }
    });

    [habitacionSelect, fechaEntrada, fechaSalida].forEach(el => {
        if (el) el.addEventListener('change', actualizarTotales);
    });

    renderTabla();
})();
</script>
