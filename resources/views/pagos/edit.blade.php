@extends('layouts.app')

@section('title', 'Editar pago')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Editar pago',
    'pageSubtitle' => 'Modifica la información de pago registrada para la factura.',
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('pagos.update', $pago->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('pagos.partials.form', ['pago' => $pago])
        
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Guardar cambios
            </button>
            <a href="{{ route('pagos.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
