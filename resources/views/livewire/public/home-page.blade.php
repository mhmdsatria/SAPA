<div class="page-stack">
    

    <section class="space-y-4">
        <div>
            <p class="page-kicker">Peta publik</p>
            <h2 class="page-heading">Sebaran laporan per wilayah</h2>
            <p class="page-description">Gunakan filter untuk melihat wilayah atau kategori tertentu. Pada zoom rendah ditampilkan ringkasan wilayah, kemudian titik laporan muncul saat peta diperbesar.</p>
        </div>

        <div class="panel grid grid-cols-2 gap-3 p-3 sm:p-4 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:items-end">
            <div class="col-span-2 lg:col-span-1">
                <x-ui.input label="Cari laporan" wire:model.live.debounce.400ms="search" placeholder="Judul, alamat, atau masalah" />
            </div>
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
            <x-ui.button type="button" variant="secondary" wire:click="resetFilters">Reset</x-ui.button>
        </div>

        <div wire:key="public-map-{{ md5($mapEndpoint.$regionsEndpoint) }}">
            <x-map.leaflet :endpoint="$mapEndpoint" :region-endpoint="$regionsEndpoint" height="560px" />
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="page-kicker">Linimasa terbaru</p>
                <h2 class="page-heading">Laporan terverifikasi</h2>
            </div>
            <a href="{{ route('gallery') }}" wire:navigate class="shrink-0 text-xs font-black text-blue-600 dark:text-blue-400 sm:text-sm">Buka galeri →</a>
        </div>

        <div class="content-grid">
            @forelse($complaints as $complaint)
                <article class="panel group min-w-0 overflow-hidden">
                    <x-media.tile :media="$complaint->primary_media" :link="route('complaints.show', $complaint)" compact class="rounded-none" />
                    <div class="p-3 sm:p-4">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $complaint->category_color }}"></span>
                            <span class="truncate text-[10px] font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $complaint->category_label }}</span>
                        </div>
                        <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="compact-card-title mt-2 block transition group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $complaint->title }}</a>
                        <p class="compact-card-meta mt-1 line-clamp-1">{{ $complaint->region?->name ?? 'Wilayah belum dipetakan' }}</p>
                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-[10px] font-bold text-slate-400 dark:border-slate-800 sm:text-xs">
                            <span>{{ $complaint->approved_at?->diffForHumans() }}</span>
                            <span>♡ {{ $complaint->upvotes_count }} · {{ $complaint->comments_count }} komentar</span>
                        </div>
                        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute Google Maps" class="mt-2 w-full text-xs" />
                    </div>
                </article>
            @empty
                <div class="panel col-span-full p-10 text-center text-sm text-slate-500">Belum ada laporan yang sesuai dengan filter.</div>
            @endforelse
        </div>

        {{ $complaints->links() }}
    </section>
</div>
