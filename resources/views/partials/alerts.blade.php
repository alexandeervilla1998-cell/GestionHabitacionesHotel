@if(session('success'))
    <div data-alert class="mb-6 flex items-start justify-between gap-4 border-2 border-emerald-700 bg-emerald-50 px-4 py-3 text-emerald-900 animate-hotel-slide" role="alert">
        <p class="font-medium">{{ session('success') }}</p>
        <button type="button" data-dismiss-alert class="text-emerald-800 hover:text-emerald-950 text-lg leading-none" aria-label="Cerrar">&times;</button>
    </div>
@endif

@if(session('error'))
    <div data-alert class="mb-6 flex items-start justify-between gap-4 border-2 border-red-700 bg-red-50 px-4 py-3 text-red-900 animate-hotel-slide" role="alert">
        <p class="font-medium">{{ session('error') }}</p>
        <button type="button" data-dismiss-alert class="text-red-800 hover:text-red-950 text-lg leading-none" aria-label="Cerrar">&times;</button>
    </div>
@endif

@if($errors->any())
    <div data-alert class="mb-6 border-2 border-red-600 bg-red-50 px-4 py-3 text-red-900 animate-hotel-slide" role="alert">
        <p class="mb-2 font-semibold uppercase tracking-wide text-sm">Revisa los siguientes campos:</p>
        <ul class="list-inside list-disc space-y-1 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
