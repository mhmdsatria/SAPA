<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/logo.png">
    <title>{{ $title ?? 'Panel Pelapor' }} · {{ config('app.name') }}</title>
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

<body x-data="{
    dark: document.documentElement.classList.contains('dark'),
    mobileSidebar: false,
    isCollapsed: false
}"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileSidebar && window.innerWidth < 1024)"
    @keydown.escape.window="mobileSidebar = false">
    <!-- Tambahkan flex w-full agar layout rapi jika sebelumnya mengandalkan margin -->
    <div class="portal-shell lg:flex lg:w-full">

        <!-- Overlay untuk Mobile -->
        <div x-show="mobileSidebar" x-transition.opacity x-cloak class="portal-overlay lg:hidden"
            @click="mobileSidebar = false"></div>

        <!-- Sidebar -->
        <!-- Tambahkan shrink-0 agar sidebar tidak mengecil sendiri -->
        <aside class="portal-sidebar transition-[width,transform] duration-300 z-40 shrink-0"
            :class="[
                mobileSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                isCollapsed ? 'lg:w-[80px]' : 'lg:w-64'
            ]"
            aria-label="Navigasi panel pelapor">
            <div class="flex items-center justify-between gap-2" :class="isCollapsed ? 'lg:justify-center' : ''">
                <a href="{{ route('profile') }}" wire:navigate
                    class="portal-brand min-w-0 flex-1 flex items-center gap-2" @click="mobileSidebar = false">
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
                <button type="button" class="icon-button lg:hidden" @click="mobileSidebar = false"
                    aria-label="Tutup sidebar">×</button>
            </div>

            <p class="portal-nav-label" x-show="!isCollapsed" x-transition.opacity>Pelaporan</p>

            <nav class="space-y-1">
                <x-navigation.sidebar-link :href="route('profile')" :active="request()->routeIs('profile')" icon="⌂">
                    <span x-show="!isCollapsed" x-transition.opacity>Dashboard</span>
                </x-navigation.sidebar-link>

                <x-navigation.sidebar-link :href="route('complaints.create')" :active="request()->routeIs('complaints.create')" icon="＋">
                    <span x-show="!isCollapsed" x-transition.opacity>Buat Laporan</span>
                </x-navigation.sidebar-link>
                <x-navigation.sidebar-link :href="route('home')" icon="-">
                    <span x-show="!isCollapsed" x-transition.opacity>Lihat Website</span>
                </x-navigation.sidebar-link>

                @if (request()->routeIs('complaints.edit'))
                    <x-navigation.sidebar-link :href="url()->current()" :active="true" icon="✎">
                        <span x-show="!isCollapsed" x-transition.opacity>Edit Laporan</span>
                    </x-navigation.sidebar-link>
                @endif
            </nav>

            <div class="portal-user-card mt-auto transition-all" :class="isCollapsed ? 'px-2' : ''">
                <div class="flex items-center" :class="isCollapsed ? 'justify-center' : 'gap-3'">
                    <span
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-600 text-sm font-black text-white"
                        title="{{ auth()->user()->name }}">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1" x-show="!isCollapsed" x-transition.opacity>
                        <p class="truncate text-xs font-black text-slate-900 dark:text-white">{{ auth()->user()->name }}
                        </p>
                        <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">
                            {{ auth()->user()->email ?: auth()->user()->phone }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-3" x-show="!isCollapsed"
                    x-transition.opacity>
                    @csrf
                    <button type="submit"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-white dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Keluar</button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="mt-3 flex justify-center"
                    x-show="isCollapsed" x-transition.opacity x-cloak>
                    @csrf
                    <button type="submit"
                        class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-white dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        title="Keluar">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <!-- Perbaikan: margin-left (ml) dihapus, diganti menjadi flex-1 min-w-0 -->
        <section class="portal-content transition-all duration-300 flex-1 min-w-0 w-full relative">
            <header class="portal-topbar flex items-center gap-4">
                <button type="button" class="icon-button lg:hidden" @click="mobileSidebar = true"
                    aria-label="Buka sidebar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <button type="button" class="icon-button hidden lg:block" @click="isCollapsed = !isCollapsed"
                    aria-label="Toggle sidebar desktop">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="min-w-0">
                    <h1 class="truncate text-sm font-black text-slate-950 dark:text-white sm:text-base">
                        {{ $title ?? 'Dashboard Pelapor' }}</h1>
                </div>
                <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
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
