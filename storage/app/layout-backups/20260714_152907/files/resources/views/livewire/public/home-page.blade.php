<div class="space-y-8">
    <section
        class="overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white shadow-2xl sm:px-10 lg:grid lg:grid-cols-[1.05fr_.95fr] lg:items-center lg:gap-10">
        <div class="animate-enter"><span
                class="inline-flex rounded-full bg-blue-500/15 px-3 py-1 text-xs font-bold text-blue-300">Pelaporan
                publik berbasis GIS</span>
            <h1 class="mt-5 max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">Laporkan masalah kota dengan lokasi
                yang akurat dan bukti yang transparan.</h1>
            <p class="mt-4 max-w-2xl text-slate-300">Pantau laporan berdasarkan kota, kabupaten, provinsi, dan kategori.
                Setiap titik yang disetujui terhubung langsung dengan detail bukti warga.</p>
            <div class="mt-7 flex flex-wrap gap-3">@auth<x-ui.button :href="route('complaints.create')" wire:navigate>Buat
                    Laporan</x-ui.button>@else<x-ui.button :href="route('login')" wire:navigate>Mulai
                    Melapor</x-ui.button>@endauth
                <x-ui.button :href="route('gallery')" variant="secondary" wire:navigate>
                    Lihat Galeri</x-ui.button>
            </div>
        </div>
        <div class="mt-8 grid grid-cols-3 gap-3 lg:mt-0">
            @foreach ([['Disetujui', $stats['approved'], '✓'], ['Menunggu', $stats['pending'], '◷'], ['Dukungan', $stats['upvotes'], '♡']] as [$label, $value, $icon])
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <p class="text-2xl">{{ $icon }}</p>
                    <p class="mt-4 text-2xl font-black">{{ number_format($value) }}</p>
                    <p class="text-xs text-slate-400">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold text-blue-600">Peta publik</p>
                <h2 class="text-2xl font-black">Sebaran laporan per wilayah</h2>
                <p class="mt-1 text-sm text-slate-500">Pada zoom rendah tampil ringkasan wilayah berbentuk gelembung,
                    bukan poligon kotak. Perbesar untuk membuka titik koordinat laporan.</p>
            </div>
        </div>
        {{-- <div class="panel grid gap-4 p-5 md:grid-cols-3"><x-ui.input label="Cari laporan"
                wire:model.live.debounce.400ms="search" placeholder="Judul, alamat, atau masalah" /><x-ui.select
                label="Wilayah" wire:model.live="regionId">
                <option value="0">Semua wilayah</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ ucfirst($region->level) }} · {{ $region->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select label="Kategori" wire:model.live="categoryId">
                <option value="0">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
            <button wire:click="resetFilters" class="text-left text-sm font-bold text-blue-600 md:col-span-3">Reset
                seluruh filter</button>
        </div> --}}
        <div wire:key="public-map-{{ md5($mapEndpoint . $regionsEndpoint) }}"><x-map.leaflet :endpoint="$mapEndpoint"
                :region-endpoint="$regionsEndpoint" height="590px" /></div>
    </section>

    <section class="space-y-5">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-blue-600">Linimasa laporan terbaru</p>
                <h2 class="text-2xl font-black">Laporan yang sudah diverifikasi</h2>
            </div><a href="{{ route('gallery') }}" wire:navigate class="text-sm font-bold text-blue-600">Buka galeri
                →</a>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:gap-8">
    @forelse($complaints as $complaint)
        <article class="group relative flex flex-col sm:flex-row bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-900/50">
            
            <!-- Area Gambar -->
            <div class="relative h-52 shrink-0 overflow-hidden sm:h-auto sm:w-[220px]">
                <x-media.tile 
                    :media="$complaint->primary_media" 
                    :link="route('complaints.show', $complaint)"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 rounded-none" 
                />
            </div>

            <!-- Area Konten -->
            <div class="flex flex-1 flex-col p-6">
                
                <!-- Badge Kategori & Wilayah -->
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <x-badge.category 
                        :category="$complaint->categoryRecord" 
                        :label="$complaint->category_label"
                        :color="$complaint->category_color" 
                    />
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $complaint->region?->name ?? 'Wilayah belum terpetakan' }}
                    </span>
                </div>

                <!-- Judul -->
                <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="text-xl font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                    {{ $complaint->title }}
                </a>
                
                <!-- Deskripsi -->
                <p class="mt-2.5 mb-6 line-clamp-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    {{ $complaint->description }}
                </p>

                <!-- Footer (Waktu & Interaksi) -->
                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-medium text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <div class="flex items-center gap-1.5">
                        <!-- Ikon Jam -->
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ $complaint->approved_at?->diffForHumans() }}</span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Ikon Upvote -->
                        <span class="flex items-center gap-1.5 transition-colors hover:text-rose-500 cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            {{ $complaint->upvotes_count }}
                        </span>
                        <!-- Ikon Komentar -->
                        <span class="flex items-center gap-1.5 transition-colors hover:text-blue-500 cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            {{ $complaint->comments_count }}
                        </span>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <!-- Empty State yang lebih elegan -->
        <div class="col-span-1 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-16 md:col-span-2 dark:border-slate-700 dark:bg-slate-800/50">
            <svg class="mb-4 h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-lg font-medium text-slate-500 dark:text-slate-400">Belum ada laporan sesuai filter.</p>
        </div>
    @endforelse
</div>

<!-- Margin atas untuk paginasi -->
<div class="mt-8">
    {{ $complaints->links() }}
</div>
    </section>
</div>
