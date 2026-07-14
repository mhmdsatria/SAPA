<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/logo.png">
    <title>{{ $title ?? 'Panel Admin' }} · {{ config('app.name') }}</title>
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

<body x-data="{ dark: document.documentElement.classList.contains('dark'), sidebar: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', sidebar && window.innerWidth < 1024)"
    @keydown.escape.window="sidebar = false">
    <div class="portal-shell">
        <div x-show="sidebar" x-transition.opacity x-cloak class="portal-overlay" @click="sidebar = false"></div>

        <aside class="portal-sidebar" :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            aria-label="Navigasi panel admin">
            <div class="flex items-center justify-between gap-2">
                <!-- Tambahkan flex, items-center, dan gap-2 di sini -->
                <a href="{{ route('admin.dashboard') }}" wire:navigate
                    class="portal-brand min-w-0 flex-1 flex items-center gap-2" @click="sidebar = false">

                    <!-- Tambahkan shrink-0 agar gambar tidak memonopoli ruang -->
                    <img src="/logo.png" class="portal-brand-icon shrink-0" alt="Logo SAPA">

                    <!-- Bagian Teks -->
                    <div class="min-w-0">
                        <!-- Tambahkan text-slate-900 dark:text-white -->
                        <span
                            class="block truncate text-sm font-black sm:text-base text-slate-900 dark:text-white">SAPA</span>
                        <span class="block truncate text-[10px] font-semibold text-slate-400 dark:text-slate-500">Sistem
                            Aduan Publik Terpadu</span>
                    </div>
                </a>
                <button type="button" class="icon-button lg:hidden" @click="sidebar = false"
                    aria-label="Tutup sidebar">×</button>
            </div>

            <p class="portal-nav-label">Administrasi</p>
            <nav class="space-y-1">
                <x-navigation.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')"
                    icon="⌂">Dashboard</x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('admin.complaints.incoming')" :active="request()->routeIs('admin.complaints.incoming')" icon="◉">Laporan
                    Masuk</x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('admin.complaints.archive')" :active="request()->routeIs('admin.complaints.archive')" icon="▤">Arsip
                    Laporan</x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('admin.categories')" :active="request()->routeIs('admin.categories')"
                    icon="◆">Kategori</x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('admin.map')" :active="request()->routeIs('admin.map')" icon="⌖">Peta
                    Pantau</x-navigation.sidebar-link>
            </nav>

            <p class="portal-nav-label">Akses publik</p>
            <nav class="space-y-1">
                <x-navigation.sidebar-link :href="route('home')" :active="false" icon="↗">Beranda
                    Publik</x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('gallery')" :active="false" icon="▦">Galeri
                    Publik</x-navigation.sidebar-link>
            </nav>

            <div class="portal-user-card">
                <div class="flex items-center gap-3">
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-600 text-sm font-black text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-black text-slate-900 dark:text-white">{{ auth()->user()->name }}
                        </p>
                        <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">
                            {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin Daerah' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-white dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Keluar</button>
                </form>
            </div>
        </aside>

        <section class="portal-content">
            <header class="portal-topbar">
                <button type="button" class="icon-button lg:hidden" @click="sidebar = true" aria-label="Buka sidebar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <p class="page-kicker">Panel Administrator</p>
                    <h1 class="truncate text-sm font-black text-slate-950 dark:text-white sm:text-base">
                        {{ $title ?? 'Dashboard Admin' }}</h1>
                </div>
                <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                    <a href="{{ route('home') }}" wire:navigate
                        class="hidden rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 sm:inline-flex">Portal
                        Publik</a>
                    <x-navigation.theme-toggle />
                </div>
            </header>

            <main class="portal-main animate-enter">
                <div class="portal-main-inner">
                    <x-flash />
                    {{ $slot }}
                </div>
            </main>
        </section>
    </div>

    @livewireScripts
</body>

</html>
