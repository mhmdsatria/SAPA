@props([
    'endpoint' => null,
    'regionEndpoint' => null,
    'center' => null,
    'zoom' => 11,
    'height' => '520px',
    'alwaysShowMarkers' => false,
    'markerZoomThreshold' => 12,
    'locationAccuracy' => null,
])

@php
    $mapId = 'leaflet-'.str()->uuid();
    $initialCenter = $center ?: [
        config('gis.default_center.latitude', -6.9175),
        config('gis.default_center.longitude', 107.6191),
    ];
@endphp

<div
    wire:ignore
    x-data="gisMap(@js([
        'id' => $mapId,
        'endpoint' => $endpoint,
        'regionEndpoint' => $regionEndpoint,
        'center' => $initialCenter,
        'location' => $center && ! $endpoint ? $initialCenter : null,
        'accuracy' => $locationAccuracy,
        'zoom' => $zoom,
        'alwaysShowMarkers' => $alwaysShowMarkers,
        'markerZoomThreshold' => $markerZoomThreshold,
    ]))"
    x-init="init()"
    x-on:wizard-map-location.window="setLocation($event.detail.latitude, $event.detail.longitude, $event.detail.accuracy)"
    x-on:map-resize.window="resizeSoon()"
    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    style="height: {{ $height }}"
>
    <div id="{{ $mapId }}" x-ref="container" class="h-full w-full"></div>

    <div x-show="loading" x-transition.opacity class="pointer-events-none absolute inset-0 z-[500] grid place-items-center bg-white/70 backdrop-blur-sm dark:bg-slate-950/70">
        <div class="rounded-xl bg-white px-3 py-2 text-xs font-black shadow-xl dark:bg-slate-900 sm:px-4 sm:py-3 sm:text-sm">Memuat peta…</div>
    </div>

    <div x-show="error" x-text="error" x-cloak class="absolute bottom-3 left-3 right-3 z-[600] rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white shadow-xl sm:bottom-4 sm:left-4 sm:right-4 sm:px-4 sm:py-3 sm:text-sm"></div>

    @if($endpoint)
        <div class="pointer-events-none absolute bottom-2 right-2 z-[450] rounded-lg bg-white/90 px-2 py-1.5 text-[9px] font-bold text-slate-600 shadow dark:bg-slate-900/90 dark:text-slate-300 sm:bottom-4 sm:right-4 sm:rounded-xl sm:px-3 sm:py-2 sm:text-xs">
            Perbesar untuk melihat titik
        </div>
    @endif
</div>
