<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    @include('partials.head')
</head>
<body class="min-h-full bg-gray-200 font-sans antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-2/5 border-r-2 border-hotel-mid bg-hotel-dark lg:flex lg:flex-col lg:justify-between">
            <div class="p-10 animate-hotel-fade">
                <div class="mb-8 flex h-14 w-14 items-center justify-center border-2 border-hotel-light bg-white transition-transform duration-200 overflow-hidden">
                    <img src="{{ asset('uploads/habitaciones/loscracks.png') }}" alt="Logo" class="h-full w-full object-cover">
                </div>
                <h2 class="text-3xl font-bold leading-tight text-white">Hotel Los Cracks</h2>
                <p class="mt-4 max-w-sm text-gray-300 leading-relaxed">
                    Sistema integral de gestión de habitaciones, reservas y servicios. Accede con tu cuenta corporativa.
                </p>
            </div>
            <div class="border-t border-white/10 p-10 text-sm text-hotel-muted">
                Panel seguro
            </div>
        </aside>

        <main class="flex flex-1 items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md animate-hotel-scale">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
