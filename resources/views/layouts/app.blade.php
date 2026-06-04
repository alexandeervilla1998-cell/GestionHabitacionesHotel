<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-gray-55 font-sans text-gray-800 antialiased flex flex-col">
    <header class="border-b-2 border-hotel-mid bg-hotel-dark text-white shadow-md">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <a href="{{ route('home.home') }}" class="group flex items-center gap-3 no-underline">
                <div class="flex h-11 w-11 items-center justify-center border-2 border-hotel-light bg-white transition-transform duration-200 group-hover:scale-105 overflow-hidden">
                    <img src="{{ asset('uploads/habitaciones/loscracks.png') }}" alt="Logo" class="h-full w-full object-cover">
                </div>
                <div>
                    <span class="block text-lg font-bold leading-tight">Hotel Los Cracks</span>
                    <span class="text-xs uppercase tracking-widest text-hotel-muted">Gestión de habitaciones</span>
                </div>
            </a>

            @auth
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="hidden border border-white/20 bg-white/5 px-3 py-1.5 text-xs uppercase tracking-wide text-gray-200 sm:inline">
                    {{ Auth::user()->nombre }}
                    <span class="text-hotel-muted">· {{ Auth::user()->rol }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hotel-btn border-white/30 bg-transparent text-white hover:border-white hover:bg-white/10">
                        <i data-lucide="log-out"></i>
                        Salir
                    </button>
                </form>
            </div>
            @endauth
        </div>

        @auth
        <nav class="border-t border-white/10 bg-hotel-dark/95">
            <div class="mx-auto flex max-w-7xl flex-wrap gap-1 px-4 py-2 sm:px-6 lg:px-8">
                @if(in_array(Auth::user()->rol, ['admin', 'recepcionista']))
                    <a href="{{ route('clientes.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Clientes
                    </a>
                    <a href="{{ route('finanzas.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Finanzas
                    </a>
                    <a href="{{ route('reservas.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Reservas
                    </a>
                    <a href="{{ route('detalle_reserva.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Detalles Reservas
                    </a>
                    <a href="{{ route('reserva_servicio.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Servicios Reservas
                    </a>
                    <a href="{{ route('habitaciones.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Habitaciones
                    </a>
                    <a href="{{ route('servicios.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Servicios
                    </a>
                @endif

                @if(Auth::user()->rol === 'admin')
                    <a href="{{ route('usuarios.index') }}" data-nav-link
                       class="border-2 border-transparent px-4 py-2 text-sm font-semibold uppercase tracking-wide text-gray-300 transition-colors hover:border-white/20 hover:bg-white/5 hover:text-white">
                        Usuarios
                    </a>
                @endif
            </div>
        </nav>
        @endauth
    </header>

    <main class="flex-grow mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.alerts')
        @yield('content')
    </main>

    <footer class="mt-auto border-t-2 border-gray-200 bg-white py-4 text-center text-xs text-gray-500">
        Hotel Los Cracks &copy; {{ date('Y') }} — Panel administrativo
    </footer>

    <!-- Lightbox Modal -->
    <div x-data="{ open: false, src: '' }"
         x-on:open-lightbox.window="open = true; src = $event.detail.src; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })"
         x-show="open"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
         style="display: none;"
         @keydown.escape.window="open = false"
         @click.self="open = false">
        <div class="relative max-w-4xl w-full flex flex-col items-center animate-hotel-scale">
            <button @click="open = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 flex items-center gap-1.5 text-sm uppercase font-bold tracking-wider">
                <i data-lucide="x" class="w-5 h-5"></i>
                Cerrar
            </button>
            <img :src="src" class="max-h-[85vh] max-w-full object-contain border-4 border-white/10 shadow-2xl rounded">
        </div>
    </div>

    @stack('scripts')
</body>
</html>
