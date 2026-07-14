<!doctype html>
<html lang="id" x-data="{ dark: localStorage.getItem('theme') === 'dark', mobile: false }" x-init="$watch('dark', value => { localStorage.setItem('theme', value ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', value) }); document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb"><meta name="description" content="Platform GIS pelaporan publik berbasis lokasi.">
    <link rel="manifest" href="/manifest.json"><link rel="icon" href="/icons/icon-192.png">
    <title>{{ $title ?? config('app.name') }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) @livewireStyles
</head>
<body>
<header class="public-header">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 font-black tracking-tight"><img src="/icons/icon-192.png" alt="Logo" class="h-10 w-10 rounded-2xl shadow-sm"><span>LaporKota <span class="text-blue-600">GIS</span></span></a>
        <nav class="hidden items-center gap-1 md:flex">
            <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}" href="{{ route('home') }}" wire:navigate>Beranda</a>
            <a class="nav-link {{ request()->routeIs('gallery') ? 'nav-link-active' : '' }}" href="{{ route('gallery') }}" wire:navigate>Galeri</a>
            @auth
                <a class="nav-link {{ request()->routeIs('complaints.create') ? 'nav-link-active' : '' }}" href="{{ route('complaints.create') }}" wire:navigate>Buat Laporan</a>
                <a class="nav-link {{ request()->routeIs('profile') || request()->routeIs('complaints.edit') ? 'nav-link-active' : '' }}" href="{{ route('profile') }}" wire:navigate>Dashboard</a>
                @if(auth()->user()->isAdmin())<a class="nav-link" href="{{ route('admin.dashboard') }}" wire:navigate>Admin</a>@endif
            @endauth
        </nav>
        <div class="flex items-center gap-2">
            <button type="button" class="icon-button" @click="dark = !dark" aria-label="Ubah tema"><span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span></button>
            @guest<x-ui.button :href="route('login')" wire:navigate>Masuk</x-ui.button>@else<form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf<x-ui.button type="submit" variant="secondary">Keluar</x-ui.button></form>@endguest
            <button class="icon-button md:hidden" @click="mobile=!mobile" aria-label="Menu">☰</button>
        </div>
    </div>
    <nav x-show="mobile" x-transition x-cloak class="border-t border-slate-200 px-4 py-3 dark:border-slate-800 md:hidden">
        <div class="grid gap-1"><a class="nav-link" href="{{ route('home') }}" wire:navigate>Beranda</a><a class="nav-link" href="{{ route('gallery') }}" wire:navigate>Galeri</a>@auth<a class="nav-link" href="{{ route('complaints.create') }}" wire:navigate>Buat Laporan</a><a class="nav-link" href="{{ route('profile') }}" wire:navigate>Dashboard</a>@endauth</div>
    </nav>
</header>
<main class="mx-auto min-h-[calc(100vh-150px)] max-w-7xl px-4 py-7 sm:px-6 lg:px-8"><x-flash />{{ $slot }}</main>
<footer class="border-t border-slate-200 py-7 text-center text-sm text-slate-500 dark:border-slate-800">LaporKota GIS · Pelaporan publik yang terukur, transparan, dan berbasis lokasi</footer>
@livewireScripts
</body></html>
