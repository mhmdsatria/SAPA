<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Platform GIS pelaporan publik berbasis lokasi.">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/logo.png">
    <title>{{ $title ?? config('app.name') }} · {{ config('app.name') }}</title>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            const theme = saved === 'dark' || saved === 'light' ?
                saved :
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.style.colorScheme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="{ dark: document.documentElement.classList.contains('dark'), mobileMenu: false }" @keydown.escape.window="mobileMenu = false">
    <header class="public-header">
    <div class="mx-auto flex h-16 max-w-[1440px] items-center gap-3 px-3 sm:px-5 lg:px-8">
        
        <!-- Bagian 1: Brand / Logo -->
        <a href="{{ route('profile') }}" wire:navigate class="portal-brand min-w-0 flex-1 flex items-center gap-2" @click="mobileSidebar = false">
            <img src="/logo.png" class="portal-brand-icon shrink-0" alt="Logo SAPA">
            <div class="min-w-0">
                <span class="block truncate text-sm font-black sm:text-base text-slate-900 dark:text-white">SAPA</span>
                <span class="block truncate text-[10px] font-semibold text-slate-400 dark:text-slate-500">Sistem Aduan Publik Terpadu</span>
            </div>
        </a>

        <!-- Bagian 2: Navigasi Desktop -->
        <nav class="ml-5 hidden items-center gap-1 md:flex" aria-label="Navigasi publik">
            <x-navigation.public-link :href="route('home')" :active="request()->routeIs('home')">
                Beranda
            </x-navigation.public-link>
            <x-navigation.public-link :href="route('gallery')" :active="request()->routeIs('gallery', 'complaints.show')">
                Galeri
            </x-navigation.public-link>
        </nav>

        <!-- Bagian 3: Aksi Kanan (Theme, Auth, Hamburger) -->
        <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
            <x-navigation.theme-toggle />

            @guest
                <x-ui.button :href="route('login')" wire:navigate>Masuk</x-ui.button>
            @else
                @if (auth()->user()->isAdmin())
                    <x-ui.button :href="route('admin.dashboard')" wire:navigate>Panel Admin</x-ui.button>
                @else
                    <x-ui.button :href="route('profile')" wire:navigate>Panel Pelapor</x-ui.button>
                @endif
            @endguest

            <!-- Tombol Hamburger (Mobile) -->
            <button type="button" class="icon-button md:hidden" @click="mobileMenu = !mobileMenu" :aria-expanded="mobileMenu" aria-controls="public-mobile-menu" aria-label="Buka menu">
                <svg x-show="!mobileMenu" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenu" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Bagian 4: Navigasi Mobile -->
    <nav id="public-mobile-menu" x-show="mobileMenu" x-transition.origin.top x-cloak class="border-t border-slate-200/80 px-3 py-3 dark:border-slate-800 md:hidden" aria-label="Navigasi publik seluler">
        
        <!-- Diperbaiki: Menggunakan flex-col dan space-y-1 agar menu tersusun vertikal -->
        <div class="flex flex-col space-y-1">
            <x-navigation.public-link :href="route('home')" :active="request()->routeIs('home')" @click="mobileMenu = false">
                Beranda
            </x-navigation.public-link>
            <x-navigation.public-link :href="route('gallery')" :active="request()->routeIs('gallery', 'complaints.show')" @click="mobileMenu = false">
                Galeri
            </x-navigation.public-link>
        </div>
        
    </nav>
</header>

    <main class="public-shell">
        <x-flash />
        {{ $slot }}
    </main>

    <footer
        class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400 sm:text-sm">
        LaporKota GIS · Pelaporan publik berbasis lokasi yang transparan
    </footer>

    @livewireScripts
</body>

</html>
