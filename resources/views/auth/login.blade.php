@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div class="hotel-card p-8 sm:p-10">
    <div class="mb-8 text-center lg:text-left">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center border-2 border-hotel-mid bg-white lg:hidden overflow-hidden">
            <img src="{{ asset('uploads/habitaciones/loscracks.png') }}" alt="Logo" class="h-full w-full object-cover">
        </div>
        <h1 class="text-2xl font-bold text-hotel-dark">Iniciar sesión</h1>
        <p class="mt-2 text-sm text-gray-600">Ingresa tus credenciales para acceder al panel.</p>
    </div>

    @if(session('error'))
        <div class="mb-4 border-2 border-red-600 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 border-2 border-red-600 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" class="space-y-5" id="loginForm">
        @csrf
        <div>
            <label for="correo" class="hotel-label">Correo electrónico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                value="{{ old('correo') }}"
                class="hotel-input"
                autocomplete="email"
                required
                autofocus
            >
        </div>
        <div>
            <label for="password" class="hotel-label">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                class="hotel-input"
                autocomplete="current-password"
                required
            >
        </div>
        <button type="submit" class="hotel-btn-primary w-full" id="btnLogin">
            <i data-lucide="log-in"></i>
            Entrar al panel
        </button>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', function () {
    var btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span> Verificando…';
});
</script>
@endpush
@endsection
