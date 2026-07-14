@props(['media', 'link' => null, 'compact' => false])

@php
    // Menambahkan 'relative' dan 'overflow-hidden' agar efek zoom gambar tidak keluar dari kotak
    // Menambahkan warna background default (slate) agar rapi saat gambar masih loading
    $baseClasses = 'media-tile group relative block overflow-hidden bg-slate-100 dark:bg-slate-800 ' . ($compact ? 'aspect-square' : 'aspect-[4/3]');
    
    // Class Tailwind untuk label Foto/Video dengan efek Glassmorphism
    $chipClasses = 'absolute top-3 left-3 z-10 inline-flex items-center gap-1.5 rounded-full bg-black/50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md shadow-sm';
@endphp

@if($link)
{{-- Menggunakan $attributes->merge() menggantikan string concatenation manual --}}
<a href="{{ $link }}" wire:navigate {{ $attributes->merge(['class' => $baseClasses]) }}>
    @if($media?->isVideo())
        <video src="{{ $media->url }}" muted playsinline preload="metadata" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"></video>
        <span class="{{ $chipClasses }}">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4l12 6-12 6z"/></svg> Video
        </span>
    @else
        <img src="{{ $media?->url ?? asset('icons/icon-512.png') }}" alt="Media laporan" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        <span class="{{ $chipClasses }}">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Foto
        </span>
    @endif
    {{ $slot }}
</a>
@else
<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    @if($media?->isVideo())
        <video src="{{ $media->url }}" controls playsinline preload="metadata" class="h-full w-full object-cover"></video>
        <span class="{{ $chipClasses }}">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4l12 6-12 6z"/></svg> Video
        </span>
    @else
        <img src="{{ $media?->url ?? asset('icons/icon-512.png') }}" alt="Media laporan" loading="lazy" class="h-full w-full object-cover">
        <span class="{{ $chipClasses }}">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Foto
        </span>
    @endif
    {{ $slot }}
</div>
@endif