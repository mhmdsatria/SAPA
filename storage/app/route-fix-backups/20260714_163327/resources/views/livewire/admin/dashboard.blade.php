<div class="page-stack">
    <div>
        <p class="page-kicker">Ringkasan operasional</p>
        <h2 class="page-heading">Dashboard moderasi</h2>
        <p class="page-description">Pantau volume laporan, keamanan media, indikasi duplikat, dan distribusi kategori pada satu tampilan.</p>
    </div>

    <div class="grid grid-cols-2 gap-2.5 sm:gap-4 lg:grid-cols-3 2xl:grid-cols-6">
        <x-dashboard.stat-card label="Total" :value="$stats['total']" icon="▤" />
        <x-dashboard.stat-card label="Menunggu" :value="$stats['pending']" icon="◷" />
        <x-dashboard.stat-card label="Disetujui" :value="$stats['approved']" icon="✓" />
        <x-dashboard.stat-card label="Ditolak" :value="$stats['rejected']" icon="×" />
        <x-dashboard.stat-card label="Media Lama" :value="$stats['stale']" icon="⚠" />
        <x-dashboard.stat-card label="Duplikat" :value="$stats['duplicate']" icon="◎" />
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_.9fr]">
        <section class="panel p-4 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black sm:text-lg">Distribusi kategori</h3>
                    <p class="text-[10px] text-slate-500 sm:text-xs">Kategori dinamis yang dikelola administrator.</p>
                </div>
                <a href="{{ route('admin.categories') }}" wire:navigate class="shrink-0 text-xs font-black text-blue-600 dark:text-blue-400">Kelola →</a>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4">
                @foreach($categoryStats as $category)
                    <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-2 text-xs sm:text-sm">
                            <span class="truncate font-black">{{ $category->name }}</span>
                            <span class="shrink-0 font-black">{{ $category->complaints_count }}</span>
                        </div>
                        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-1.5 rounded-full" style="width: {{ $stats['total'] ? max(4, ($category->complaints_count / $stats['total']) * 100) : 0 }}%; background: {{ $category->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel p-4 sm:p-6">
            <h3 class="text-base font-black sm:text-lg">Aktivitas tujuh hari</h3>
            <div class="mt-5 flex h-44 items-end gap-2 sm:h-52 sm:gap-3">
                @php($max = max(1, (int) $dailyStats->max('total')))
                @foreach($dailyStats as $day)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                        <span class="text-[9px] font-black sm:text-xs">{{ $day->total }}</span>
                        <div class="w-full rounded-t-lg bg-blue-600" style="height: {{ max(8, ($day->total / $max) * 150) }}px"></div>
                        <span class="text-[8px] text-slate-400 sm:text-[10px]">{{ \Carbon\Carbon::parse($day->report_date)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="panel overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 sm:p-5">
            <h3 class="text-base font-black sm:text-lg">Laporan terbaru</h3>
            <a href="{{ route('admin.complaints.incoming') }}" wire:navigate class="text-xs font-black text-blue-600 dark:text-blue-400">Buka antrean →</a>
        </div>
        <div class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-slate-800 lg:grid-cols-3">
            @foreach($recent as $complaint)
                <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="group min-w-0 bg-white p-3 transition hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800/70 sm:p-4">
                    <div class="flex items-start gap-2.5 sm:gap-3">
                        <x-media.tile :media="$complaint->primary_media" compact class="h-14 w-14 shrink-0 sm:h-16 sm:w-16" />
                        <div class="min-w-0 flex-1">
                            <x-badge.status :status="$complaint->status" />
                            <p class="mt-2 line-clamp-2 text-xs font-black leading-4 group-hover:text-blue-600 dark:group-hover:text-blue-400 sm:text-sm sm:leading-5">{{ $complaint->title }}</p>
                            <p class="mt-1 truncate text-[9px] text-slate-500 sm:text-[10px]">{{ $complaint->reporter_name }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>
