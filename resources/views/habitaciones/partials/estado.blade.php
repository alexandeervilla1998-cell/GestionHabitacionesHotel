<select name="estado" class="hotel-select" required>
    @foreach(['disponible' => 'Disponible', 'ocupada' => 'Ocupada', 'mantenimiento' => 'Mantenimiento'] as $key => $label)
        @if(!isset($hideOcupada) || !$hideOcupada || $key !== 'ocupada')
            <option value="{{ $key }}" @selected(old('estado', $value ?? 'disponible') === $key)>{{ $label }}</option>
        @endif
    @endforeach
</select>
