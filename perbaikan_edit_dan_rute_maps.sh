#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(pwd)"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$ROOT_DIR/storage/app/route-fix-backups/$TIMESTAMP"
MANIFEST_FILE="$BACKUP_DIR/manifest.txt"

log() {
    printf '\n\033[1;34m[PERBAIKAN EDIT & RUTE]\033[0m %s\n' "$1"
}

warn() {
    printf '\n\033[1;33m[PERINGATAN]\033[0m %s\n' "$1"
}

fail() {
    printf '\n\033[1;31m[GAGAL]\033[0m %s\n' "$1" >&2
    exit 1
}

[[ -f "$ROOT_DIR/artisan" ]] || fail "Jalankan skrip dari folder root Laravel yang berisi file artisan."
[[ -f "$ROOT_DIR/composer.json" ]] || fail "composer.json tidak ditemukan."
command -v php >/dev/null 2>&1 || fail "PHP CLI tidak ditemukan."
command -v python3 >/dev/null 2>&1 || fail "Python 3 tidak ditemukan."

TARGETS=(
    "app/Livewire/Public/ComplaintEditor.php"
    "app/Services/GisService.php"
    "resources/js/gis-map.js"
    "resources/views/components/map/directions-link.blade.php"
    "resources/views/livewire/public/home-page.blade.php"
    "resources/views/livewire/public/profile-dashboard.blade.php"
    "resources/views/livewire/public/complaint-detail.blade.php"
    "resources/views/livewire/public/complaint-editor.blade.php"
    "resources/views/livewire/public/complaint-gallery.blade.php"
    "resources/views/livewire/admin/incoming-complaints.blade.php"
    "resources/views/livewire/admin/complaint-archive.blade.php"
    "resources/views/livewire/admin/dashboard.blade.php"
)

mkdir -p "$BACKUP_DIR"
: > "$MANIFEST_FILE"

for relative in "${TARGETS[@]}"; do
    source_path="$ROOT_DIR/$relative"
    backup_path="$BACKUP_DIR/$relative"

    if [[ -f "$source_path" ]]; then
        mkdir -p "$(dirname "$backup_path")"
        cp "$source_path" "$backup_path"
        printf 'EXISTED\t%s\n' "$relative" >> "$MANIFEST_FILE"
    else
        printf 'ABSENT\t%s\n' "$relative" >> "$MANIFEST_FILE"
    fi
done

rollback() {
    local status=$?
    trap - ERR INT TERM

    if [[ -f "$MANIFEST_FILE" ]]; then
        while IFS=$'\t' read -r state relative; do
            [[ -n "$relative" ]] || continue
            if [[ "$state" == "EXISTED" ]]; then
                mkdir -p "$(dirname "$ROOT_DIR/$relative")"
                cp "$BACKUP_DIR/$relative" "$ROOT_DIR/$relative"
            else
                rm -f "$ROOT_DIR/$relative"
            fi
        done < "$MANIFEST_FILE"
    fi

    printf '\n\033[1;31m[ROLLBACK]\033[0m Perubahan dibatalkan dan file dikembalikan dari backup.\n' >&2
    exit "$status"
}

trap rollback ERR INT TERM

log "Backup source dibuat di storage/app/route-fix-backups/$TIMESTAMP/."

export PROJECT_ROOT="$ROOT_DIR"
python3 <<'PY'
from __future__ import annotations

import os
import re
from pathlib import Path

root = Path(os.environ['PROJECT_ROOT'])
changed: list[str] = []
notes: list[str] = []


def read(relative: str) -> str:
    path = root / relative
    if not path.is_file():
        raise RuntimeError(f'File wajib tidak ditemukan: {relative}')
    return path.read_text(encoding='utf-8')


def write(relative: str, content: str) -> None:
    path = root / relative
    path.parent.mkdir(parents=True, exist_ok=True)
    old = path.read_text(encoding='utf-8') if path.exists() else None
    if old != content:
        path.write_text(content, encoding='utf-8')
        changed.append(relative)


def replace_once(content: str, old: str, new: str) -> tuple[str, bool]:
    if old not in content:
        return content, False
    return content.replace(old, new, 1), True


# 1. Perbaikan fatal error orWhereKey().
relative = 'app/Livewire/Public/ComplaintEditor.php'
content = read(relative)
if 'orWhereKey($this->categoryId)' in content:
    content = content.replace(
        'orWhereKey($this->categoryId)',
        "orWhere('id', $this->categoryId)",
    )
elif "orWhere('id', $this->categoryId)" not in content:
    raise RuntimeError(
        'Pola query kategori pada ComplaintEditor tidak dikenali. '
        'Tidak ada orWhereKey maupun perbaikan yang diharapkan.'
    )
write(relative, content)

# 2. Komponen Atomic Design untuk membuka rute Google Maps.
directions_component = r'''@props([
    'latitude',
    'longitude',
    'label' => 'Buka rute',
    'compact' => false,
])

@php
    $validLatitude = is_numeric($latitude) && (float) $latitude >= -90 && (float) $latitude <= 90;
    $validLongitude = is_numeric($longitude) && (float) $longitude >= -180 && (float) $longitude <= 180;
    $lat = $validLatitude ? number_format((float) $latitude, 7, '.', '') : null;
    $lng = $validLongitude ? number_format((float) $longitude, 7, '.', '') : null;
    $url = $lat !== null && $lng !== null
        ? 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($lat.','.$lng).'&travelmode=driving'
        : null;
    $baseClasses = $compact
        ? 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-blue-950/40 dark:hover:text-blue-300'
        : 'inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-blue-950/40 dark:hover:text-blue-300';
@endphp

@if($url)
    <a
        href="{{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Buka rute menuju lokasi laporan di Google Maps"
        title="Buka rute di Google Maps"
        {{ $attributes->class($baseClasses) }}
    >
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6M15 12H3m9 9a9 9 0 100-18 9 9 0 000 18z" />
        </svg>
        @unless($compact)
            <span>{{ $label }}</span>
        @endunless
    </a>
@endif
'''
write('resources/views/components/map/directions-link.blade.php', directions_component)

# 3. GeoJSON marker mendapat URL rute Google Maps.
relative = 'app/Services/GisService.php'
content = read(relative)
if "'route_url'" not in content:
    marker = "                    'url' => route('complaints.show', $complaint),"
    insertion = marker + "\n                    'route_url' => $this->googleMapsDirectionsUrl((float) $complaint->latitude, (float) $complaint->longitude),"
    content, ok = replace_once(content, marker, insertion)
    if not ok:
        raise RuntimeError('Lokasi properti URL pada GisService tidak ditemukan.')

if 'function googleMapsDirectionsUrl' not in content:
    last_brace = content.rfind('\n}')
    if last_brace < 0:
        raise RuntimeError('Penutup class GisService tidak ditemukan.')
    method = r'''

    public function googleMapsDirectionsUrl(float $latitude, float $longitude): string
    {
        $destination = number_format($latitude, 7, '.', '').','.number_format($longitude, 7, '.', '');

        return 'https://www.google.com/maps/dir/?api=1&destination='.
            rawurlencode($destination).
            '&travelmode=driving';
    }
'''
    content = content[:last_brace] + method + content[last_brace:]
write(relative, content)

# 4. Popup Leaflet menampilkan Detail dan Buka Rute.
relative = 'resources/js/gis-map.js'
content = read(relative)
if 'Buka rute Google Maps' not in content:
    popup_pattern = re.compile(
        r'(?m)^(?P<indent>\s*)marker\.bindPopup\(`.*?map-popup.*?`\);\s*$',
    )
    match = popup_pattern.search(content)
    if not match:
        raise RuntimeError('Blok marker.bindPopup pada gis-map.js tidak ditemukan.')
    indent = match.group('indent')
    replacement = f'''{indent}const detailUrl = escapeHtml(p.url || '#');
{indent}const routeUrl = escapeHtml(
{indent}    p.route_url ||
{indent}    `https://www.google.com/maps/dir/?api=1&destination=${{encodeURIComponent(`${{lat}},${{lng}}`)}}&travelmode=driving`
{indent});
{indent}marker.bindPopup(`
{indent}    <div class="map-popup">
{indent}        <strong>${{escapeHtml(p.title)}}</strong>
{indent}        <div>${{escapeHtml(p.category_label || '')}}${{p.region ? ` · ${{escapeHtml(p.region)}}` : ''}}</div>
{indent}        <p>${{escapeHtml(p.address || '')}}</p>
{indent}        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem">
{indent}            <a href="${{detailUrl}}">Lihat detail</a>
{indent}            <a href="${{routeUrl}}" target="_blank" rel="noopener noreferrer">Buka rute Google Maps</a>
{indent}        </div>
{indent}    </div>
{indent}`);'''
    content = content[:match.start()] + replacement + content[match.end():]
write(relative, content)

# 5. Halaman edit menampilkan tombol rute lokasi yang sedang tersimpan.
relative = 'resources/views/livewire/public/complaint-editor.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    latest_anchor = '''            </div>\n\n            <div class="rounded-xl border border-emerald-200'''
    latest_insert = '''            </div>\n\n            <x-map.directions-link :latitude="$latitude" :longitude="$longitude" label="Buka lokasi di Google Maps" class="w-full" />\n\n            <div class="rounded-xl border border-emerald-200'''
    content, ok = replace_once(content, latest_anchor, latest_insert)
    if not ok:
        old_anchor = '''                </div>\n\n                <div class="rounded-2xl border border-emerald-200'''
        old_insert = '''                </div>\n\n                <x-map.directions-link :latitude="$latitude" :longitude="$longitude" label="Buka lokasi di Google Maps" class="w-full" />\n\n                <div class="rounded-2xl border border-emerald-200'''
        content, ok = replace_once(content, old_anchor, old_insert)
    if not ok:
        raise RuntimeError('Posisi tombol rute pada complaint-editor.blade.php tidak ditemukan.')
write(relative, content)

# 6. Detail laporan selalu memiliki tombol rute.
relative = 'resources/views/livewire/public/complaint-detail.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    # Layout terbaru, sisipkan setelah blok auth pertama pada heading.
    heading_endauth = '''        @endauth\n    </div>'''
    heading_insert = '''        @endauth\n        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute" />\n    </div>'''
    content, ok = replace_once(content, heading_endauth, heading_insert)
    if not ok:
        # Layout lama satu baris, sisipkan pada informasi koordinat.
        old_anchor = '''</dl>@if($complaint->address_is_edited)'''
        old_insert = '''</dl><div class="mt-4"><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute Google Maps" /></div>@if($complaint->address_is_edited)'''
        content, ok = replace_once(content, old_anchor, old_insert)
    if not ok:
        raise RuntimeError('Posisi tombol rute pada complaint-detail.blade.php tidak ditemukan.')
write(relative, content)

# 7. Dashboard pelapor, tombol Detail disandingkan dengan tombol Rute.
relative = 'resources/views/livewire/public/profile-dashboard.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    patterns = [
        (
            '''                        <x-ui.button :href="route('complaints.show', $complaint)" variant="secondary" wire:navigate>Detail</x-ui.button>''',
            '''                        <x-ui.button :href="route('complaints.show', $complaint)" variant="secondary" wire:navigate>Detail</x-ui.button>\n                        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute" class="w-full" />''',
        ),
        (
            '''<x-ui.button :href="route('complaints.show',$complaint)" variant="secondary" wire:navigate>Detail</x-ui.button>''',
            '''<x-ui.button :href="route('complaints.show',$complaint)" variant="secondary" wire:navigate>Detail</x-ui.button><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute" />''',
        ),
    ]
    ok = False
    for old, new in patterns:
        content, ok = replace_once(content, old, new)
        if ok:
            break
    if not ok:
        raise RuntimeError('Tombol Detail dashboard pelapor tidak ditemukan.')
write(relative, content)

# 8. Linimasa beranda mendapat tombol rute tanpa mengganti hero tema.
relative = 'resources/views/livewire/public/home-page.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    latest_anchor = '''                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-[10px] font-bold text-slate-400 dark:border-slate-800 sm:text-xs">\n                            <span>{{ $complaint->approved_at?->diffForHumans() }}</span>\n                            <span>♡ {{ $complaint->upvotes_count }} · {{ $complaint->comments_count }} komentar</span>\n                        </div>'''
    latest_insert = latest_anchor + '''\n                        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute Google Maps" class="mt-2 w-full text-xs" />'''
    content, ok = replace_once(content, latest_anchor, latest_insert)
    if not ok:
        old_anchor = '''<div class="mt-4 flex items-center justify-between text-xs text-slate-400"><span>{{ $complaint->approved_at?->diffForHumans() }}</span><span>♡ {{ $complaint->upvotes_count }} · 💬 {{ $complaint->comments_count }}</span></div>'''
        old_insert = old_anchor + '''<x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Rute Google Maps" class="mt-3 w-full" />'''
        content, ok = replace_once(content, old_anchor, old_insert)
    if not ok:
        notes.append('Beranda: pola kartu tidak dikenali, popup peta tetap memiliki tombol rute.')
write(relative, content)

# 9. Galeri tidak lagi memakai anchor pembungkus agar Detail dan Rute dapat berdampingan.
relative = 'resources/views/livewire/public/complaint-gallery.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    content = content.replace(
        ''':link="route('complaints.show', $complaint)" ''',
        '',
    ).replace(
        ''':link="route('complaints.show',$complaint)" ''',
        '',
    )

    latest_region = '''                    <div class="mt-1 flex items-center gap-1 text-[9px] font-semibold text-slate-300 sm:text-[11px]">\n                        <span aria-hidden="true">⌖</span>\n                        <span class="truncate">{{ $complaint->region?->name ?? 'Wilayah tidak diketahui' }}</span>\n                    </div>'''
    latest_actions = latest_region + '''\n                    <div class="mt-2 flex items-center gap-1.5">\n                        <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="inline-flex flex-1 items-center justify-center rounded-lg bg-white/95 px-2 py-1.5 text-[9px] font-black text-slate-900 transition hover:bg-white sm:text-xs">Detail</a>\n                        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" compact class="h-8 w-8 border-white/20 bg-slate-950/70 text-white hover:bg-slate-950 dark:border-white/20 dark:bg-slate-950/70 dark:text-white" />\n                    </div>'''
    content, ok = replace_once(content, latest_region, latest_actions)
    if not ok:
        old_region = '''<p class="mt-1 text-[11px] text-slate-300">{{ $complaint->region?->name }}</p>'''
        old_actions = old_region + '''<div class="mt-2 flex items-center gap-1.5"><a href="{{ route('complaints.show',$complaint) }}" wire:navigate class="inline-flex flex-1 items-center justify-center rounded-lg bg-white/95 px-2 py-1.5 text-[10px] font-black text-slate-900">Detail</a><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" compact class="h-8 w-8 border-white/20 bg-slate-950/70 text-white dark:border-white/20 dark:bg-slate-950/70 dark:text-white" /></div>'''
        content, ok = replace_once(content, old_region, old_actions)
    if not ok:
        notes.append('Galeri: pola overlay tidak dikenali, detail dan popup peta tetap menyediakan rute.')
write(relative, content)

# 10. Antrean moderasi admin mendapat tombol rute.
relative = 'resources/views/livewire/admin/incoming-complaints.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    patterns = [
        (
            '''                        <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="inline-block text-xs font-black text-blue-600 dark:text-blue-400 sm:text-sm">Buka detail dan peta →</a>''',
            '''                        <div class="flex flex-wrap items-center gap-2">\n                            <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="inline-flex items-center rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/60 sm:text-sm">Buka detail dan peta →</a>\n                            <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute" />\n                        </div>''',
        ),
        (
            '''<a href="{{ route('complaints.show',$complaint) }}" wire:navigate class="inline-block text-sm font-bold text-blue-600">Buka detail dan peta →</a>''',
            '''<div class="flex flex-wrap items-center gap-2"><a href="{{ route('complaints.show',$complaint) }}" wire:navigate class="inline-flex items-center rounded-xl bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">Buka detail dan peta →</a><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" label="Buka rute" /></div>''',
        ),
    ]
    ok = False
    for old, new in patterns:
        content, ok = replace_once(content, old, new)
        if ok:
            break
    if not ok:
        notes.append('Antrean admin: pola tautan detail tidak dikenali, popup peta tetap memiliki tombol rute.')
write(relative, content)

# 11. Arsip admin desktop mendapat tombol rute. Mobile tetap dapat menuju detail dan rute tersedia di detail.
relative = 'resources/views/livewire/admin/complaint-archive.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    patterns = [
        (
            '''<td class="p-4"><a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="font-black text-blue-600">Detail</a></td>''',
            '''<td class="p-4"><div class="flex items-center gap-2"><a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="font-black text-blue-600">Detail</a><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" compact /></div></td>''',
        ),
        (
            '''<td class="p-4"><a href="{{ route('complaints.show',$complaint) }}" wire:navigate class="font-bold text-blue-600">Detail</a></td>''',
            '''<td class="p-4"><div class="flex items-center gap-2"><a href="{{ route('complaints.show',$complaint) }}" wire:navigate class="font-bold text-blue-600">Detail</a><x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" compact /></div></td>''',
        ),
    ]
    ok = False
    for old, new in patterns:
        content, ok = replace_once(content, old, new)
        if ok:
            break
    if not ok:
        notes.append('Arsip admin: pola kolom aksi tidak dikenali, detail dan popup peta tetap menyediakan rute.')
write(relative, content)

# 12. Dashboard admin terbaru: ubah kartu anchor menjadi article agar tombol eksternal valid.
relative = 'resources/views/livewire/admin/dashboard.blade.php'
content = read(relative)
if '<x-map.directions-link' not in content:
    latest_block = re.compile(
        r'''(?P<indent>\s*)<a href="\{\{ route\('complaints\.show', \$complaint\) \}\}" wire:navigate class="group min-w-0 bg-white p-3 transition hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800/70 sm:p-4">(?P<body>.*?)</a>''',
        re.S,
    )
    match = latest_block.search(content)
    if match:
        indent = match.group('indent')
        body = match.group('body')
        replacement = (
            f'{indent}<article class="group min-w-0 bg-white p-3 transition hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800/70 sm:p-4">'
            f'{body}'
            f'\n{indent}    <div class="mt-3 grid grid-cols-[1fr_auto] gap-2">'
            f'\n{indent}        <a href="{{{{ route(\'complaints.show\', $complaint) }}}}" wire:navigate class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-[10px] font-black text-white transition hover:bg-blue-700 sm:text-xs">Detail</a>'
            f'\n{indent}        <x-map.directions-link :latitude="$complaint->latitude" :longitude="$complaint->longitude" compact />'
            f'\n{indent}    </div>'
            f'\n{indent}</article>'
        )
        content = content[:match.start()] + replacement + content[match.end():]
    else:
        notes.append('Dashboard admin: format kartu terbaru tidak dikenali, halaman detail dan popup peta tetap menyediakan rute.')
write(relative, content)

print('File yang berubah:')
for item in changed:
    print(f'  - {item}')
if notes:
    print('Catatan kompatibilitas:')
    for note in notes:
        print(f'  - {note}')
PY

log "Memeriksa sintaks PHP."
php -l "$ROOT_DIR/app/Livewire/Public/ComplaintEditor.php" >/dev/null
php -l "$ROOT_DIR/app/Services/GisService.php" >/dev/null

if command -v node >/dev/null 2>&1; then
    log "Memeriksa sintaks JavaScript peta."
    node --check "$ROOT_DIR/resources/js/gis-map.js" >/dev/null
else
    warn "Node.js tidak ditemukan. Pemeriksaan JavaScript dilewati."
fi

log "Membersihkan cache Laravel."
php artisan optimize:clear >/dev/null 2>&1 || true

if [[ -f "$ROOT_DIR/package.json" ]] && command -v npm >/dev/null 2>&1; then
    if [[ ! -d "$ROOT_DIR/node_modules" ]]; then
        log "Folder node_modules belum tersedia. Menjalankan npm install."
        npm install
    fi

    log "Membangun ulang aset frontend agar popup rute aktif."
    npm run build
else
    warn "npm tidak tersedia. Jalankan npm run build secara manual agar perubahan gis-map.js aktif."
fi

if [[ -d "$ROOT_DIR/vendor" ]]; then
    log "Mengompilasi Blade untuk memvalidasi komponen dan view."
    php artisan view:clear >/dev/null 2>&1 || true
    php artisan view:cache
else
    warn "Folder vendor tidak tersedia. Validasi Blade dilewati."
fi

trap - ERR INT TERM

log "Perbaikan selesai. Error orWhereKey telah dihapus dan tombol rute Google Maps telah ditambahkan."
printf '\nBackup source: %s\n' "$BACKUP_DIR"
printf 'Uji halaman edit: /lapor/{slug}/edit\n'
printf 'Lakukan hard refresh Ctrl+Shift+R bila popup peta masih memakai aset lama.\n'
