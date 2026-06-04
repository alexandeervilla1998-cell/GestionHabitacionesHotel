<select name="tipo" class="hotel-select" required>
    @foreach(['individual' => 'Individual', 'doble' => 'Doble', 'suite' => 'Suite', 'familiar' => 'Familiar'] as $key => $label)
        <option value="{{ $key }}" @selected(old('tipo', $value ?? '') === $key)>{{ $label }}</option>
    @endforeach
</select>
