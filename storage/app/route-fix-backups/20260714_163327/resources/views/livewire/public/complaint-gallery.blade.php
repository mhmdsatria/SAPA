<div class="page-stack">
    <div class="max-w-3xl">
        <p class="page-kicker">Galeri publik</p>
        <h1 class="page-heading">Bukti visual laporan warga</h1>
        <p class="page-description">Galeri hanya menampilkan laporan yang sudah disetujui. Pilih foto atau video untuk membuka detail laporan dan lokasi pada peta.</p>
    </div>

    <div class="panel grid grid-cols-2 gap-3 p-3 sm:p-4 lg:grid-cols-4">
        <div class="col-span-2 lg:col-span-1">
            <x-ui.input label="Pencarian" wire:model.live.debounce.350ms="search" placeholder="Judul atau alamat" />
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
        <x-ui.select label="Jenis media" wire:model.live="mediaType">
            <option value="">Foto dan video</option>
            <option value="image">Foto</option>
            <option value="video">Video</option>
        </x-ui.select>
    </div>

    <div class="gallery-grid">
        @forelse($complaints as $complaint)
            <x-media.tile :media="$complaint->primary_media" :link="route('complaints.show', $complaint)" compact class="shadow-sm">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/45 to-transparent p-2.5 pt-10 sm:p-4 sm:pt-16">
                    <p class="line-clamp-2 text-xs font-black leading-4 text-white sm:text-sm sm:leading-5">{{ $complaint->title }}</p>
                    <div class="mt-1 flex items-center gap-1 text-[9px] font-semibold text-slate-300 sm:text-[11px]">
                        <span aria-hidden="true">⌖</span>
                        <span class="truncate">{{ $complaint->region?->name ?? 'Wilayah tidak diketahui' }}</span>
                    </div>
                </div>
            </x-media.tile>
        @empty
            <div class="panel col-span-full p-10 text-center text-sm text-slate-500">Media laporan tidak ditemukan. Sesuaikan filter pencarian, wilayah, atau kategori.</div>
        @endforelse
    </div>

    {{ $complaints->links() }}
</div>
