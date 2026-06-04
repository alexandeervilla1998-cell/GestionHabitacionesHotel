@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@include('partials.page-header', [
    'pageTitle' => 'Inicio',
    'pageSubtitle' => 'Resumen general del sistema y accesos rápidos a los módulos principales.',
])

<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @php
        $cards = [
            ['label' => 'Habitaciones', 'value' => $stats['habitaciones'], 'delay' => 'stagger-1', 'badge' => 'hotel-badge-green'],
            ['label' => 'Reservas', 'value' => $stats['reservas'], 'delay' => 'stagger-2', 'badge' => 'hotel-badge-amber'],
            ['label' => 'Facturas', 'value' => $stats['facturas'], 'delay' => 'stagger-3', 'badge' => 'hotel-badge-gray'],
            ['label' => 'Pagos completados', 'value' => '$' . number_format((float) $stats['pagos'], 2), 'delay' => 'stagger-4', 'badge' => 'hotel-badge-green', 'wide' => true],
        ];
        if (Auth::user()->rol === 'admin') {
            array_unshift($cards, ['label' => 'Usuarios', 'value' => $stats['usuarios'], 'delay' => 'stagger-0', 'badge' => 'hotel-badge-blue']);
        }
    @endphp

    @foreach($cards as $card)
        <div class="hotel-card animate-hotel-slide {{ $card['delay'] }} {{ ($card['wide'] ?? false) ? 'sm:col-span-2 lg:col-span-1' : '' }} p-6 transition-shadow duration-200 hover:shadow-md">
            <span class="{{ $card['badge'] }}">{{ $card['label'] }}</span>
            <p class="mt-4 text-3xl font-bold text-hotel-dark">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-10 grid gap-6 lg:grid-cols-2 animate-hotel-fade">
    <div class="hotel-card p-6">
        <h2 class="text-lg font-bold text-hotel-dark">Accesos rápidos</h2>
        <p class="mt-1 text-sm text-gray-600">Navega a los módulos del panel.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('home.home') }}" class="hotel-btn-secondary">Inicio</a>
            @if(Auth::user()->rol === 'admin')
                <a href="{{ route('metricas.index') }}" class="hotel-btn-primary">
                    <i data-lucide="bar-chart-3"></i>
                    Métricas
                </a>
                <a href="{{ route('usuarios.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="users"></i>
                    Usuarios
                </a>
            @endif
            @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
                <a href="{{ route('reservas.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="calendar"></i>
                    Reservas
                </a>
                <a href="{{ route('facturas.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="file-text"></i>
                    Facturas
                </a>
                <a href="{{ route('pagos.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="credit-card"></i>
                    Pagos
                </a>
                <a href="{{ route('habitaciones.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="bed-double"></i>
                    Habitaciones
                </a>
                <a href="{{ route('servicios.index') }}" class="hotel-btn-secondary">
                    <i data-lucide="sparkles"></i>
                    Servicios
                </a>
            @endif
        </div>
    </div>

    <div class="hotel-card border-l-4 border-l-hotel-mid p-6">
        <h2 class="text-lg font-bold text-hotel-dark">Sesión activa</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Usuario</dt>
                <dd class="font-semibold text-gray-800">{{ Auth::user()->nombre }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Correo</dt>
                <dd class="font-semibold text-gray-800">{{ Auth::user()->correo }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Rol</dt>
                <dd><span class="hotel-badge-green">{{ Auth::user()->rol }}</span></dd>
            </div>
        </dl>
    </div>
</div>
@endsection
