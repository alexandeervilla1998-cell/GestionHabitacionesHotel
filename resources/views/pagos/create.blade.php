@extends('layouts.app')

@section('title', 'Crear pago')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Registrar pago',
    'pageSubtitle' => 'Ingresa los detalles para registrar un abono o cancelación de factura.',
])

<div class="hotel-card max-w-xl animate-hotel-scale p-8">
    <form action="{{ route('pagos.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('pagos.partials.form')
        
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="hotel-btn-primary">
                <i data-lucide="save"></i>
                Registrar pago
            </button>
            <a href="{{ route('pagos.index') }}" class="hotel-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
