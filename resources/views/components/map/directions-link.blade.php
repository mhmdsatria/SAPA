@props([
    'latitude',
    'longitude',
    'label' => 'Buka rute',
    'compact' => false,
])

@php
    $validLatitude = is_numeric($latitude) && (float) $latitude >= -90 && (float) $latitude <= 90;
    $validLongitude = is_numeric($longitude) && (float) $longitude >= -180 && (float) $longitude <= 180;
    $lat = $validLatitude ? number_format((float) $latitude, 7, '.', '') : null;
    $lng = $validLongitude ? number_format((float) $longitude, 7, '.', '') : null;
    $url = $lat !== null && $lng !== null
        ? 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng).'&travelmode=driving'
        : null;
    $baseClasses = $compact
        ? 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-blue-950/40 dark:hover:text-blue-300'
        : 'inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-blue-950/40 dark:hover:text-blue-300';
@endphp

@if($url)
    <a
        href="{{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Buka rute menuju lokasi laporan di Google Maps"
        title="Buka rute di Google Maps"
        {{ $attributes->class($baseClasses) }}
    >
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6M15 12H3m9 9a9 9 0 100-18 9 9 0 000 18z" />
        </svg>
        @unless($compact)
            <span>{{ $label }}</span>
        @endunless
    </a>
@endif
