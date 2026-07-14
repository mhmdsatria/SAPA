<div class="page-stack">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="page-kicker">Master data</p>
            <h2 class="page-heading">Arsip laporan</h2>
            <p class="page-description">Telusuri seluruh laporan yang telah diproses dan ekspor data berdasarkan filter.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button :href="$excelUrl" variant="success">Excel</x-ui.button>
            <x-ui.button :href="$pdfUrl" variant="danger">PDF</x-ui.button>
        </div>
    </div>

    <div class="panel grid grid-cols-2 gap-3 p-3 sm:p-4 lg:grid-cols-3 xl:grid-cols-6">
        <div class="col-span-2 lg:col-span-1 xl:col-span-2">
            <x-ui.input label="Pencarian" wire:model.live.debounce.350ms="search" placeholder="Judul, alamat, pelapor" />
        </div>
        <x-ui.select label="Status" wire:model.live="status">
            <option value="">Semua</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </x-ui.select>
        <x-ui.select label="Kategori" wire:model.live="categoryId">
            <option value="0">Semua</option>
            @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
        </x-ui.select>
        <x-ui.select label="Wilayah" wire:model.live="regionId">
            <option value="0">Semua</option>
            @foreach($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach
        </x-ui.select>
        <div class="grid grid-cols-2 gap-2">
            <x-ui.input label="Dari" type="date" wire:model.live="dateFrom" />
            <x-ui.input label="Sampai" type="date" wire:model.live="dateTo" />
        </div>
        <button wire:click="resetFilters" class="text-left text-xs font-black text-blue-600 dark:text-blue-400 xl:col-span-6">Reset filter</button>
    </div>

    <div class="mobile-card-list">
        @forelse($complaints as $complaint)
            <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="panel min-w-0 overflow-hidden p-3">
                <div class="flex items-start justify-between gap-2">
                    <x-badge.status :status="$complaint->status" />
                    <span class="text-[9px] font-bold text-slate-400">{{ $complaint->media->count() }} media</span>
                </div>
                <h3 class="mt-2 line-clamp-2 text-xs font-black leading-4">{{ $complaint->title }}</h3>
                <p class="mt-1 line-clamp-2 text-[9px] leading-4 text-slate-500">{{ $complaint->address_text }}</p>
                <div class="mt-3 border-t border-slate-100 pt-2 text-[9px] font-semibold text-slate-400 dark:border-slate-800">
                    <p class="truncate">{{ $complaint->reporter_name }}</p>
                    <p class="mt-1 truncate">{{ $complaint->region?->name }}</p>
                </div>
            </a>
        @empty
            <div class="panel col-span-2 p-8 text-center text-xs text-slate-500">Data tidak ditemukan.</div>
        @endforelse
    </div>

    <div class="panel desktop-table">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                    <th class="p-4">Laporan</th>
                    <th class="p-4">Pelapor</th>
                    <th class="p-4">Kategori</th>
                    <th class="p-4">Wilayah</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Media</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($complaints as $complaint)
                    <tr>
                        <td class="min-w-72 p-4"><p class="font-black">{{ $complaint->title }}</p><p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $complaint->address_text }}</p></td>
                        <td class="p-4">{{ $complaint->reporter_name }}</td>
                        <td class="p-4"><x-badge.category :category="$complaint->categoryRecord" :label="$complaint->category_label" :color="$complaint->category_color" /></td>
                        <td class="p-4">{{ $complaint->region?->name }}</td>
                        <td class="p-4"><x-badge.status :status="$complaint->status" /></td>
                        <td class="p-4">{{ $complaint->media->count() }} @if($complaint->exif_is_stale)⚠@endif @if($complaint->is_duplicate_flag)◎@endif</td>
                        <td class="p-4"><a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="font-black text-blue-600">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-slate-500">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $complaints->links() }}
</div>
