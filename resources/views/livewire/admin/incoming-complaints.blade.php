<div class="page-stack">
    <div>
        <p class="page-kicker">Moderasi cepat</p>
        <h2 class="page-heading">Laporan masuk</h2>
        <p class="page-description">Periksa lokasi, media, usia EXIF, indikasi duplikat, dan redaksi sebelum publikasi.</p>
    </div>

    <div class="space-y-4">
        @forelse($complaints as $complaint)
            <article class="panel overflow-hidden">
                <div class="grid lg:grid-cols-[240px_1fr] xl:grid-cols-[290px_1fr]">
                    <div class="grid grid-cols-4 gap-1 bg-slate-100 p-2 dark:bg-slate-800 lg:grid-cols-2">
                        @foreach($complaint->media->take(4) as $media)
                            <x-media.tile :media="$media" compact />
                        @endforeach
                    </div>

                    <div class="space-y-4 p-4 sm:p-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-badge.category :category="$complaint->categoryRecord" :label="$complaint->category_label" :color="$complaint->category_color" />
                            @if($complaint->exif_is_stale)
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-[9px] font-black text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 sm:text-xs">⚠ Media lama</span>
                            @endif
                            @if($complaint->is_duplicate_flag)
                                <span class="rounded-full bg-violet-100 px-2 py-1 text-[9px] font-black text-violet-700 dark:bg-violet-950/40 dark:text-violet-300 sm:text-xs">◎ Duplikat</span>
                            @endif
                            <span class="text-[9px] font-bold text-slate-400 sm:text-xs">{{ $complaint->media->count() }} media</span>
                        </div>

                        <div>
                            <h3 class="text-base font-black sm:text-xl">{{ $complaint->title }}</h3>
                            <p class="mt-1 text-[10px] leading-4 text-slate-500 sm:text-xs">{{ $complaint->reporter_name }} · {{ $complaint->region?->name }} · {{ $complaint->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="grid gap-3 xl:grid-cols-[.8fr_1.2fr]">
                            <div class="panel-soft p-3 text-xs leading-5 sm:p-4 sm:text-sm">
                                <p>{{ $complaint->address_text }}</p>
                                <span class="mt-2 block truncate font-mono text-[9px] text-slate-500 sm:text-xs">{{ $complaint->latitude }}, {{ $complaint->longitude }}</span>
                            </div>
                            <x-ui.textarea label="Redaksi laporan" wire:model="editDescription.{{ $complaint->id }}" rows="4"></x-ui.textarea>
                        </div>

                        <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
                            <x-ui.input label="Alasan penolakan" wire:model="rejectReason.{{ $complaint->id }}" placeholder="Wajib diisi jika laporan ditolak" />
                            <div class="grid grid-cols-3 gap-2 lg:flex lg:items-end">
                                <x-ui.button variant="danger" wire:click="reject({{ $complaint->id }})">Reject</x-ui.button>
                                <x-ui.button variant="success" wire:click="approve({{ $complaint->id }})">Approve</x-ui.button>
                                <x-ui.button wire:click="editAndApprove({{ $complaint->id }})" class="col-span-3 lg:col-span-1">Edit & Approve</x-ui.button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="inline-flex items-center rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/60 sm:text-sm">Buka detail dan peta →</a>
                            <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute" />
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel p-10 text-center text-sm text-slate-500">Tidak ada laporan menunggu moderasi.</div>
        @endforelse
    </div>

    {{ $complaints->links() }}
</div>
