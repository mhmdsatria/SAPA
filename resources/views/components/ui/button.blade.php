@props([
    'type' => 'button',
    'variant' => 'primary',
    'href' => null,
])

@php
$classes = match ($variant) {
    'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/20',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500/20',
    'warning' => 'bg-amber-500 text-slate-950 hover:bg-amber-400 focus:ring-amber-500/20',
    'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800',
    default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500/20',
};
$base = 'inline-flex min-h-9 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-50 sm:min-h-10 sm:gap-2 sm:px-4 sm:py-2.5 sm:text-sm';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>{{ $slot }}</button>
@endif
