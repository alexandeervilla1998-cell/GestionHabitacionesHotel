<div class="mb-8 flex flex-col gap-4 border-b-2 border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between animate-hotel-slide">
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-hotel-light">Hotel Los Cracks</p>
        <h1 class="mt-1 text-2xl font-bold text-hotel-dark sm:text-3xl">{{ $pageTitle ?? 'Panel' }}</h1>
        @isset($pageSubtitle)
            <p class="mt-2 max-w-2xl text-sm text-gray-600">{{ $pageSubtitle }}</p>
        @endisset
    </div>
    @isset($pageActions)
        <div class="flex flex-wrap gap-2">
            {!! $pageActions !!}
        </div>
    @endisset
</div>
