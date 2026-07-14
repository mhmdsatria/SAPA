<div class="page-stack">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap gap-2">
                <x-badge.status :status="$complaint->status" />
                <x-badge.category :category="$complaint->categoryRecord" :label="$complaint->category_label" :color="$complaint->category_color" />
            </div>
            <h1 class="mt-3 max-w-4xl text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ $complaint->title }}</h1>
            <p class="mt-2 text-xs leading-5 text-slate-500 sm:text-sm">{{ $complaint->reporter_name }} · {{ $complaint->created_at->format('d M Y H:i') }} · {{ $complaint->region?->name }}</p>
        </div>
        @auth
            @if(auth()->id() === $complaint->user_id && $complaint->isEditableByReporter())
                <x-ui.button :href="route('complaints.edit', $complaint)" wire:navigate>Edit Laporan</x-ui.button>
            @endif
        @endauth
        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute" />
    </div>

    <div class="gallery-grid">
        @forelse($complaint->media as $media)
            <x-media.tile :media="$media" compact />
        @empty
            <x-media.tile :media="null" compact />
        @endforelse
    </div>

    <div class="grid gap-5 lg:grid-cols-[1.05fr_.95fr]">
        <section class="space-y-4">
            <article class="panel p-4 sm:p-6">
                <h2 class="text-base font-black sm:text-lg">Rincian laporan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300 sm:leading-7">{{ $complaint->description }}</p>
                <dl class="mt-5 grid grid-cols-2 gap-3 text-xs sm:gap-4 sm:text-sm">
                    <div class="col-span-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-slate-500">Alamat</dt>
                        <dd class="mt-1 font-semibold">{{ $complaint->address_text }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-slate-500">Patokan</dt>
                        <dd class="mt-1 line-clamp-2 font-semibold">{{ $complaint->landmark ?: 'Tidak ada' }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-slate-500">Akurasi GPS</dt>
                        <dd class="mt-1 font-semibold">{{ $complaint->gps_accuracy ? number_format($complaint->gps_accuracy, 0).' meter' : 'Tidak tersedia' }}</dd>
                    </div>
                    <div class="col-span-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-slate-500">Koordinat</dt>
                        <dd class="mt-1 truncate font-mono">{{ number_format($complaint->latitude, 7, '.', '') }}, {{ number_format($complaint->longitude, 7, '.', '') }}</dd>
                    </div>
                </dl>
                @if($complaint->address_is_edited)
                    <p class="mt-4 rounded-xl bg-blue-50 p-3 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/30 dark:text-blue-300 sm:text-xs">Alamat teks telah dikoreksi pelapor. Titik GPS tetap tidak berubah.</p>
                @endif
            </article>

            @if($complaint->status === 'approved')
                <div class="panel p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-black sm:text-base">Dukungan publik</p>
                            <p class="text-[10px] text-slate-500 sm:text-xs">{{ $complaint->upvotes_count }} dukungan dan {{ $complaint->comments_count }} komentar</p>
                        </div>
                        <x-ui.button wire:click="toggleUpvote" :variant="$hasUpvoted ? 'success' : 'secondary'">{{ $hasUpvoted ? 'Didukung' : '♡ Dukung' }}</x-ui.button>
                    </div>
                </div>
            @endif
        </section>

        <aside>
            <x-map.leaflet :always-show-markers="true" :center="[$complaint->latitude, $complaint->longitude]" :zoom="17" :location-accuracy="$complaint->gps_accuracy" height="430px" />
        </aside>
    </div>

    @if($complaint->status === 'approved')
    <section class="space-y-4">
        <h2 class="page-heading">Komentar publik</h2>
        
        @auth
            <form wire:submit="addComment" class="panel space-y-3 p-4 sm:p-5">
                <x-ui.textarea label="Komentar" name="commentContent" wire:model.live.debounce.350ms="commentContent" rows="3"></x-ui.textarea>
                @if($commentPreview !== '' && $commentPreview !== $commentContent)
                    <p class="rounded-lg bg-amber-50 p-2 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-300 sm:text-sm">Pratinjau sensor: {{ $commentPreview }}</p>
                @endif
                <div class="flex justify-end"><x-ui.button type="submit">Kirim Komentar</x-ui.button></div>
            </form>
        @else
            <div class="panel p-4 text-xs sm:p-5 sm:text-sm">
                <a href="{{ route('login') }}" wire:navigate class="font-black text-blue-600">Masuk</a> untuk memberi dukungan dan komentar.
            </div>
        @endauth

        <div class="flex flex-col gap-3" x-data="{ showAll: false }">
            {{-- Ubah di sini: Tambahkan sortByDesc('created_at') --}}
            @forelse($comments->sortByDesc('created_at')->values() as $comment)
                <article 
                    class="panel p-4 {{ $comment->is_hidden ? 'opacity-60' : '' }} sm:p-5"
                    @if($loop->iteration > 10)
                        x-show="showAll"
                        x-transition.opacity
                        x-cloak
                    @endif
                >
                    <div class="flex justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black">{{ $comment->user->name }}</p>
                            <p class="text-[10px] text-slate-500 sm:text-xs">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                        @can('hide', $comment)
                            <button wire:click="toggleCommentVisibility({{ $comment->id }})" class="shrink-0 text-[10px] font-black text-blue-600 sm:text-xs">
                                {{ $comment->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}
                            </button>
                        @endcan
                    </div>
                    <p class="mt-3 whitespace-pre-line text-xs leading-5 sm:text-sm sm:leading-6">{{ $comment->is_hidden ? '[Komentar disembunyikan admin]' : $comment->content }}</p>
                </article>
            @empty
                <div class="panel p-8 text-center text-sm text-slate-500">Belum ada komentar.</div>
            @endforelse

            @if(count($comments) > 10)
                <button 
                    type="button" 
                    @click="showAll = !showAll" 
                    class="mt-2 py-2 w-full text-center text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors"
                >
                    <span x-show="!showAll">Lihat komentar terdahulu ({{ count($comments) - 10 }} lainnya)</span>
                    <span x-show="showAll" x-cloak>Sembunyikan komentar terdahulu</span>
                </button>
            @endif
        </div>
    </section>
@endif
</div>
