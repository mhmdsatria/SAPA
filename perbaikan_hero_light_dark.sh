#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(pwd)"
TARGET_FILE="$ROOT_DIR/resources/views/livewire/public/home-page.blade.php"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$ROOT_DIR/storage/app/theme-backups/$TIMESTAMP"
BACKUP_FILE="$BACKUP_DIR/home-page.blade.php"

log() {
    printf '\n\033[1;34m[PERBAIKAN TEMA]\033[0m %s\n' "$1"
}

fail() {
    printf '\n\033[1;31m[GAGAL]\033[0m %s\n' "$1" >&2
    exit 1
}

[[ -f "$ROOT_DIR/artisan" ]] || fail "Jalankan skrip ini dari folder root Laravel yang berisi file artisan."
[[ -f "$TARGET_FILE" ]] || fail "File resources/views/livewire/public/home-page.blade.php tidak ditemukan."
command -v php >/dev/null 2>&1 || fail "PHP CLI tidak ditemukan."

mkdir -p "$BACKUP_DIR"
cp "$TARGET_FILE" "$BACKUP_FILE"
log "Backup dibuat di storage/app/theme-backups/$TIMESTAMP/."

export HERO_TARGET_FILE="$TARGET_FILE"
php <<'PHP'
<?php

declare(strict_types=1);

$path = getenv('HERO_TARGET_FILE');

if (! is_string($path) || $path === '' || ! is_file($path)) {
    fwrite(STDERR, "Target Blade tidak valid.\n");
    exit(1);
}

$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "Target Blade tidak dapat dibaca.\n");
    exit(1);
}

$anchor = 'Pelaporan publik berbasis GIS';
$anchorPosition = strpos($content, $anchor);

if ($anchorPosition === false) {
    fwrite(STDERR, "Blok hero tidak ditemukan karena teks penanda tidak tersedia.\n");
    exit(1);
}

$beforeAnchor = substr($content, 0, $anchorPosition);
$sectionStart = strrpos($beforeAnchor, '<section');

if ($sectionStart === false) {
    fwrite(STDERR, "Tag pembuka section hero tidak ditemukan.\n");
    exit(1);
}

$sectionLineStart = strrpos(substr($content, 0, $sectionStart), "\n");
$sectionStart = $sectionLineStart === false ? 0 : $sectionLineStart + 1;

if (! preg_match('/\R[ \t]*<section\b/', $content, $nextSectionMatch, PREG_OFFSET_CAPTURE, $anchorPosition)) {
    fwrite(STDERR, "Section setelah hero tidak ditemukan.\n");
    exit(1);
}

$nextSectionStart = $nextSectionMatch[0][1];
$lineBreakLength = str_starts_with($nextSectionMatch[0][0], "\r\n") ? 2 : 1;
$nextSectionStart += $lineBreakLength;

$hero = <<<'BLADE'
    <section class="relative isolate overflow-hidden rounded-3xl bg-white px-5 py-8 text-slate-950 shadow-[0_18px_60px_-32px_rgba(15,23,42,0.28)] transition-colors duration-300 dark:bg-slate-950 dark:text-white dark:shadow-2xl sm:px-10 sm:py-12 lg:grid lg:grid-cols-[1.1fr_.9fr] lg:items-center lg:gap-12 lg:px-12 lg:py-16">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white via-white to-blue-50/80 dark:hidden"></div>
        <div class="pointer-events-none absolute -left-24 -top-24 hidden h-96 w-96 rounded-full bg-blue-600/20 blur-[100px] dark:block"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 hidden h-96 w-96 rounded-full bg-indigo-600/20 blur-[100px] dark:block"></div>

        <div class="relative z-10 animate-enter">
            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/20 sm:text-xs">
                Pelaporan publik berbasis GIS
            </span>

            <h1 class="mt-6 max-w-3xl text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl lg:leading-[1.15]">
                Laporkan masalah kota dengan
                <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-300">bukti dan lokasi</span>
                yang terukur.
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400 sm:text-base sm:leading-7">
                Pantau laporan berdasarkan wilayah dan kategori. Setiap laporan yang telah disetujui terhubung langsung dengan media valid, alamat, dan titik koordinat geografis.
            </p>

            <div class="mt-8 flex flex-wrap gap-3 sm:mt-10">
                @auth
                    @if(auth()->user()->isAdmin())
                        <x-ui.button :href="route('admin.dashboard')" wire:navigate class="shadow-lg shadow-blue-600/20">Masuk Panel Admin</x-ui.button>
                    @else
                        <x-ui.button :href="route('complaints.create')" wire:navigate class="shadow-lg shadow-blue-600/20">Buat Laporan</x-ui.button>
                    @endif
                @else
                    <x-ui.button :href="route('login')" wire:navigate class="shadow-lg shadow-blue-600/20">Mulai Melapor</x-ui.button>
                @endauth

                <x-ui.button :href="route('gallery')" variant="secondary" wire:navigate class="shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
                    Lihat Galeri
                </x-ui.button>
            </div>
        </div>

        <div class="relative z-10 mt-10 grid grid-cols-3 gap-2.5 sm:gap-4 lg:mt-0 lg:gap-5">
            <div class="group min-w-0 rounded-2xl border-0 bg-slate-50/95 p-3.5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-lg dark:border dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:backdrop-blur-md dark:hover:border-emerald-500/30 dark:hover:bg-white/10 sm:p-5">
                <div class="inline-flex rounded-xl bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 sm:p-2.5">
                    <svg class="h-5 w-5 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="mt-3 truncate text-xl font-black tracking-tight text-slate-950 dark:text-white sm:mt-4 sm:text-3xl">{{ number_format($stats['approved']) }}</p>
                <p class="mt-1 truncate text-[10px] font-bold text-slate-500 dark:text-slate-400 sm:text-sm">Disetujui</p>
            </div>

            <div class="group min-w-0 rounded-2xl border-0 bg-slate-50/95 p-3.5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-lg dark:border dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:backdrop-blur-md dark:hover:border-amber-500/30 dark:hover:bg-white/10 sm:p-5">
                <div class="inline-flex rounded-xl bg-amber-100 p-2 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 sm:p-2.5">
                    <svg class="h-5 w-5 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="mt-3 truncate text-xl font-black tracking-tight text-slate-950 dark:text-white sm:mt-4 sm:text-3xl">{{ number_format($stats['pending']) }}</p>
                <p class="mt-1 truncate text-[10px] font-bold text-slate-500 dark:text-slate-400 sm:text-sm">Menunggu</p>
            </div>

            <div class="group min-w-0 rounded-2xl border-0 bg-slate-50/95 p-3.5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-lg dark:border dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:backdrop-blur-md dark:hover:border-rose-500/30 dark:hover:bg-white/10 sm:p-5">
                <div class="inline-flex rounded-xl bg-rose-100 p-2 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400 sm:p-2.5">
                    <svg class="h-5 w-5 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <p class="mt-3 truncate text-xl font-black tracking-tight text-slate-950 dark:text-white sm:mt-4 sm:text-3xl">{{ number_format($stats['upvotes']) }}</p>
                <p class="mt-1 truncate text-[10px] font-bold text-slate-500 dark:text-slate-400 sm:text-sm">Dukungan</p>
            </div>
        </div>
    </section>
BLADE;

$updated = substr($content, 0, $sectionStart).$hero."\n\n".substr($content, $nextSectionStart);

if (file_put_contents($path, $updated) === false) {
    fwrite(STDERR, "Perubahan Blade gagal disimpan.\n");
    exit(1);
}

fwrite(STDOUT, "Hero light dan dark mode berhasil diperbarui.\n");
PHP

restore_backup() {
    cp "$BACKUP_FILE" "$TARGET_FILE"
    printf '\n\033[1;33m[ROLLBACK]\033[0m File Blade dikembalikan dari backup karena validasi gagal.\n' >&2
}

trap 'restore_backup' ERR

log "Membersihkan cache Laravel."
php artisan optimize:clear >/dev/null 2>&1 || true

if [[ -f "$ROOT_DIR/package.json" ]] && command -v npm >/dev/null 2>&1; then
    if [[ ! -d "$ROOT_DIR/node_modules" ]]; then
        log "Memasang dependency frontend karena folder node_modules belum tersedia."
        npm install
    fi

    log "Membangun ulang aset frontend."
    npm run build
else
    printf '\n\033[1;33m[PERINGATAN]\033[0m npm tidak tersedia. Jalankan npm run build secara manual setelah Node.js dipasang.\n'
fi

if [[ -d "$ROOT_DIR/vendor" ]]; then
    log "Memvalidasi dan mengompilasi Blade view."
    php artisan view:clear >/dev/null 2>&1 || true
    php artisan view:cache
else
    printf '\n\033[1;33m[PERINGATAN]\033[0m Folder vendor belum tersedia. Validasi Blade dilewati.\n'
fi

trap - ERR

log "Perbaikan selesai. Light mode kini memakai permukaan putih dan teks gelap, sedangkan tampilan gelap hanya aktif pada dark mode."
printf '\nBackup: %s\n' "$BACKUP_FILE"
printf 'Lakukan hard refresh browser dengan Ctrl+Shift+R atau hapus cache PWA bila tampilan lama masih muncul.\n'
