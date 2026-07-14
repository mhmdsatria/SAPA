@props(['status'])

@php
$classes = match ($status) {
    'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300',
    default => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
};
$label = match ($status) {
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    default => 'Menunggu',
};
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$classes]) }}>{{ $label }}</span>
