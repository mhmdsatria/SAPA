<!doctype html>
<html lang="id" x-data="{ dark: localStorage.getItem('theme') === 'dark', sidebar: false }" x-init="$watch('dark', value => { localStorage.setItem('theme', value ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', value) }); document.documentElement.classList.toggle('dark', dark)">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#0f172a"><link rel="manifest" href="/manifest.json"><title>{{ $title ?? 'Admin' }} · {{ config('app.name') }}</title>@vite(['resources/css/app.css','resources/js/app.js']) @livewireStyles</head>
<body>
<div class="portal-shell">
    <div x-show="sidebar" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden" @click="sidebar=false"></div>
    <aside class="portal-sidebar" :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="portal-brand"><img src="/icons/icon-192.png" class="h-11 w-11 rounded-2xl" alt="Logo"><div><span class="block text-lg font-black">LaporKota GIS</span><span class="text-xs text-slate-400">Panel Administrator</span></div></a>
        <nav class="mt-8 space-y-1">
            <x-navigation.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" icon="⌂">Dashboard</x-navigation.sidebar-link>
            <x-navigation.sidebar-link :href="route('admin.complaints.incoming')" :active="request()->routeIs('admin.complaints.incoming')" icon="◉">Laporan Masuk</x-navigation.sidebar-link>
            <x-navigation.sidebar-link :href="route('admin.complaints.archive')" :active="request()->routeIs('admin.complaints.archive')" icon="▤">Arsip Laporan</x-navigation.sidebar-link>
            <x-navigation.sidebar-link :href="route('admin.categories')" :active="request()->routeIs('admin.categories')" icon="◆">Kategori</x-navigation.sidebar-link>
            <x-navigation.sidebar-link :href="route('admin.map')" :active="request()->routeIs('admin.map')" icon="⌖">Peta Pantau</x-navigation.sidebar-link>
            <x-navigation.sidebar-link :href="route('home')" :active="false" icon="↗">Portal Publik</x-navigation.sidebar-link>
        </nav>
        <div class="mt-auto rounded-2xl bg-white/5 p-4 text-sm text-slate-300"><p class="font-bold text-white">{{ auth()->user()->name }}</p><p class="mt-1 text-xs text-slate-400">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin Daerah' }}</p></div>
    </aside>
    <section class="portal-content"><header class="portal-topbar"><button class="icon-button lg:hidden" @click="sidebar=true">☰</button><div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Administrasi</p><h1 class="font-black">{{ $title ?? 'Dashboard Admin' }}</h1></div><div class="ml-auto flex gap-2"><button class="icon-button" @click="dark=!dark"><span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span></button><form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="secondary">Keluar</x-ui.button></form></div></header><main class="portal-main animate-enter"><x-flash />{{ $slot }}</main></section>
</div>@livewireScripts
</body></html>
