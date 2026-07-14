@props(['media', 'link' => null, 'compact' => false])

@php
    $baseClasses = 'media-tile group '.($compact ? 'aspect-square' : 'aspect-[4/3]');
    $chipClasses = 'media-type-chip inline-flex items-center gap-1';
@endphp

@if($link)
    <a href="{{ $link }}" wire:navigate {{ $attributes->class($baseClasses) }}>
        @if($media?->isVideo())
            <video src="{{ $media->url }}" muted playsinline preload="metadata" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"></video>
            <span class="{{ $chipClasses }}"><span aria-hidden="true">▶</span> Video</span>
        @else
            <img src="{{ $media?->url ?? asset('icons/icon-512.png') }}" alt="Media laporan" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            <span class="{{ $chipClasses }}"><span aria-hidden="true">▧</span> Foto</span>
        @endif
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->class($baseClasses) }}>
        @if($media?->isVideo())
            <video src="{{ $media->url }}" controls playsinline preload="metadata" class="h-full w-full object-cover"></video>
            <span class="{{ $chipClasses }}"><span aria-hidden="true">▶</span> Video</span>
        @else
            <img src="{{ $media?->url ?? asset('icons/icon-512.png') }}" alt="Media laporan" loading="lazy" class="h-full w-full object-cover">
            <span class="{{ $chipClasses }}"><span aria-hidden="true">▧</span> Foto</span>
        @endif
        {{ $slot }}
    </div>
@endif
