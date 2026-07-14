<div class="space-y-8">
    
    <!-- Bagian Header -->
    <div class="max-w-3xl">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">
            Galeri publik
        </p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
            Bukti visual laporan warga
        </h1>
        <p class="mt-3 text-base leading-relaxed text-slate-500 dark:text-slate-400">
            Galeri hanya menampilkan laporan yang sudah disetujui. Klik foto atau video untuk membuka detail laporan dan lokasi pada peta.
        </p>
    </div>

    <!-- Panel Filter -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.input 
                label="Pencarian"
                wire:model.live.debounce.350ms="search" 
                placeholder="Cari judul atau alamat..." 
            />
            
            <x-ui.select label="Wilayah" wire:model.live="regionId">
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
            
            <x-ui.select label="Jenis media" wire:model.live="mediaType">
                <option value="">Foto dan video</option>
                <option value="image">Foto</option>
                <option value="video">Video</option>
            </x-ui.select>
        </div>
    </div>

    <!-- Grid Galeri Media -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @forelse($complaints as $complaint)
            <x-media.tile 
                :media="$complaint->primary_media" 
                :link="route('complaints.show', $complaint)" 
                compact
                class="rounded-xl shadow-sm"
            >
                <!-- Overlay Teks Informasi -->
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-4 pt-16 transition-opacity duration-300">
                    <p class="line-clamp-2 text-sm font-bold leading-snug text-white group-hover:text-blue-300 transition-colors">
                        {{ $complaint->title }}
                    </p>
                    
                    <div class="mt-1.5 flex items-center gap-1 text-[11px] font-medium text-slate-300">
                        <!-- Ikon Lokasi Peta -->
                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="truncate">{{ $complaint->region?->name ?? 'Wilayah tidak diketahui' }}</span>
                    </div>
                </div>
            </x-media.tile>
            
        @empty
            <!-- Empty State -->
            <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 px-4 dark:border-slate-700 dark:bg-slate-800/50">
                <svg class="mb-4 h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-lg font-medium text-slate-900 dark:text-white">Media laporan tidak ditemukan.</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Coba sesuaikan filter pencarian, wilayah, atau kategori.</p>
            </div>
        @endforelse
    </div>
    
    <!-- Paginasi -->
    <div class="mt-8">
        {{ $complaints->links() }}
    </div>
    
</div>