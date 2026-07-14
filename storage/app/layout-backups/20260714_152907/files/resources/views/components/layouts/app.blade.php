<!doctype html>
<html lang="id" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" x-init="$watch('dark', value => { localStorage.setItem('theme', value ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', value) }); document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Platform GIS pelaporan publik berbasis lokasi.">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/icon-192.png">
    <title>{{ $title ?? config('app.name') }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 font-black text-slate-950 dark:text-white">
                <img src="/icons/icon-192.png" alt="Logo" class="h-9 w-9 rounded-xl">
                <span>LaporKota GIS</span>
            </a>
            <nav class="hidden items-center gap-1 md:flex">
                <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}" href="{{ route('home') }}" wire:navigate>Beranda</a>
                @auth
                    <a class="nav-link {{ request()->routeIs('complaints.create') ? 'nav-link-active' : '' }}" href="{{ route('complaints.create') }}" wire:navigate>Lapor</a>
                    <a class="nav-link {{ request()->routeIs('profile') ? 'nav-link-active' : '' }}" href="{{ route('profile') }}" wire:navigate>Profil</a>
                    @if(auth()->user()->isAdmin())
                        <a class="nav-link" href="{{ route('admin.dashboard') }}" wire:navigate>Admin</a>
                    @endif
                @endauth
            </nav>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-xl p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="dark = !dark" aria-label="Ubah tema">
                    <span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span>
                </button>
                @guest
                    <x-ui.button :href="route('login')" wire:navigate>Masuk</x-ui.button>
                @else
                    <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="secondary">Keluar</x-ui.button></form>
                @endguest
            </div>
        </div>
    </header>

    <main class="mx-auto min-h-[calc(100vh-140px)] max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-flash />
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 py-6 text-center text-sm text-slate-500 dark:border-slate-800">
        LaporKota GIS · Transparansi pelaporan publik berbasis lokasi
    </footer>
    @livewireScripts
</body>
</html>
