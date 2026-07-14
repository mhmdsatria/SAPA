<div class="page-stack">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="page-kicker">Ringkasan pelapor</p>
            <h2 class="page-heading">Kelola laporan Anda</h2>
            <p class="page-description">Laporan berstatus menunggu atau ditolak masih dapat diperbaiki. Setelah disetujui, laporan menjadi arsip publik yang terkunci.</p>
        </div>
        <x-ui.button :href="route('complaints.create')" wire:navigate>＋ Buat Laporan</x-ui.button>
    </div>

    <div class="stat-grid">
        <x-dashboard.stat-card label="Semua" :value="$counts['all']" icon="▤" />
        <x-dashboard.stat-card label="Menunggu" :value="$counts['pending']" icon="◷" />
        <x-dashboard.stat-card label="Disetujui" :value="$counts['approved']" icon="✓" />
        <x-dashboard.stat-card label="Ditolak" :value="$counts['rejected']" icon="×" />
    </div>

    <div class="panel grid grid-cols-2 gap-3 p-3 sm:p-4 lg:grid-cols-[1fr_220px]">
        <div class="col-span-2 lg:col-span-1">
            <x-ui.input label="Cari laporan" wire:model.live.debounce.350ms="search" placeholder="Judul atau alamat" />
        </div>
        <x-ui.select label="Status" wire:model.live="status">
            <option value="">Semua status</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </x-ui.select>
    </div>

    <div class="content-grid">
        @forelse($complaints as $complaint)
            <article class="panel group min-w-0 overflow-hidden">
                <x-media.tile :media="$complaint->primary_media" :link="route('complaints.show', $complaint)" compact class="rounded-none" />
                <div class="p-3 sm:p-4">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <x-badge.status :status="$complaint->status" />
                        <span class="text-[9px] font-bold text-slate-400 sm:text-[10px]">{{ $complaint->media->count() }} media</span>
                    </div>
                    <h3 class="compact-card-title mt-2">{{ $complaint->title }}</h3>
                    <p class="compact-card-meta mt-1 line-clamp-2">{{ $complaint->address_text }}</p>

                    @if($complaint->status === 'rejected' && $complaint->rejected_reason)
                        <p class="mt-2 line-clamp-2 rounded-lg bg-red-50 p-2 text-[10px] font-semibold text-red-700 dark:bg-red-950/30 dark:text-red-300 sm:text-xs">{{ $complaint->rejected_reason }}</p>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-1.5">
                        <x-ui.button :href="route('complaints.show', $complaint)" variant="secondary" wire:navigate>Detail</x-ui.button>
                        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute" class="w-full" />
                        @if($complaint->isEditableByReporter())
                            <x-ui.button :href="route('complaints.edit', $complaint)" wire:navigate>Edit</x-ui.button>
                            <x-ui.button variant="danger" wire:click="deleteComplaint({{ $complaint->id }})" wire:confirm="Hapus laporan ini beserta seluruh medianya?" class="col-span-2">Hapus</x-ui.button>
                        @else
                            <span class="col-span-2 rounded-lg bg-emerald-50 px-2 py-2 text-center text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Terkunci setelah disetujui</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="panel col-span-full p-10 text-center text-sm text-slate-500">Belum ada laporan.</div>
        @endforelse
    </div>

    {{ $complaints->links() }}
</div>
