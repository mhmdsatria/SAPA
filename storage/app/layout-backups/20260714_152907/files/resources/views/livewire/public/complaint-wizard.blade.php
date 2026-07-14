<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm font-bold text-blue-600">Wizard pelaporan</p>
        <h1 class="text-3xl font-black">Buat laporan baru</h1>
        <p class="mt-2 text-slate-500">Unggah beberapa foto atau video, ambil GPS perangkat, lalu koreksi teks alamat
            bila hasil geocoding belum lengkap.</p>
    </div>
    <ol class="grid grid-cols-3 gap-3">
        @foreach ([1 => 'Media', 2 => 'Lokasi', 3 => 'Detail'] as $number => $label)
            <li
                class="rounded-2xl border px-3 py-3 text-center text-sm font-bold {{ $step >= $number ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'border-slate-200 text-slate-400 dark:border-slate-800' }}">
                {{ $number }}. {{ $label }}</li>
        @endforeach
    </ol>
    <div class="panel p-5 sm:p-7">
        @if ($step === 1)
            <div class="space-y-5">
                <div>
                    <h2 class="text-xl font-black">Unggah foto atau video</h2>
                    <p class="mt-1 text-sm text-slate-500">Maksimal 8 media. Format gambar JPEG, PNG, WebP dan video MP4,
                        MOV, WebM. Maksimal 50 MB per berkas.</p>
                </div><label
                    class="block cursor-pointer rounded-3xl border-2 border-dashed border-slate-300 p-8 text-center transition hover:border-blue-500 dark:border-slate-700"><input
                        type="file" wire:model="mediaFiles"
                        accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" multiple
                        capture="environment" class="sr-only"><span class="text-4xl">＋</span><span
                        class="mt-3 block font-black">Pilih beberapa foto atau video</span><span
                        class="mt-1 block text-sm text-slate-500">Foto akan dikompresi dan metadata EXIF
                        diperiksa.</span></label>
                <div wire:loading wire:target="mediaFiles" class="text-sm font-bold text-blue-600">Mengunggah media…
                </div>
                @error('mediaFiles')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror @error('mediaFiles.*')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($mediaFiles)
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($mediaFiles as $index => $file)
                            <div
                                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800">
                                @if (str_starts_with((string) $file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full object-cover"
                                    alt="Pratinjau">@else<div
                                        class="grid aspect-square place-items-center p-4 text-center">
                                        <div><span class="text-4xl">▶</span>
                                            <p class="mt-2 line-clamp-2 text-xs font-bold">
                                                {{ $file->getClientOriginalName() }}</p>
                                        </div>
                                    </div>
                                @endif
                                <button type="button" wire:click="removeTemporaryMedia({{ $index }})"
                                    class="absolute right-2 top-2 grid h-8 w-8 place-items-center rounded-full bg-slate-950/75 text-white">
                                    ×</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="flex justify-end">
                    <x-ui.button wire:click="nextStep" wire:loading.attr="disabled">Lanjut ke lokasi</x-ui.button>
                </div>
            </div>
        @elseif($step === 2)
            <div class="space-y-5" x-data="{ locating: false, error: '', locate() { this.error = ''; if (!navigator.geolocation) { this.error = 'Perangkat tidak mendukung GPS.'; return } this.locating = true;
                    navigator.geolocation.getCurrentPosition(p => { $wire.captureLocation(p.coords.latitude, p.coords.longitude, p.coords.accuracy).finally(() => this.locating = false) }, e => { this.locating = false;
                        this.error = e.message || 'Lokasi gagal diambil. Aktifkan izin lokasi dan GPS.' }, { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }) } }">
                <div>
                    <h2 class="text-xl font-black">Ambil dan periksa lokasi</h2>
                    <p class="mt-1 text-sm text-slate-500">Koordinat tetap terkunci dari sensor perangkat. Teks alamat
                        dapat dikoreksi agar lebih mudah dipahami petugas.</p>
                </div>
                <div class="flex flex-wrap gap-2"><x-ui.button type="button" @click="locate()"
                        x-bind:disabled="locating"><span x-show="!locating">📍 Ambil lokasi GPS</span><span
                            x-show="locating" x-cloak>Mengambil lokasi…</span></x-ui.button>
                    @if ($locationDetected)
                        <x-ui.button type="button" variant="secondary" wire:click="refreshAddress">Muat ulang
                            alamat</x-ui.button>
                    @endif
                </div>
                <p x-show="error" x-text="error" x-cloak class="rounded-xl bg-red-50 p-3 text-sm text-red-700"></p>
                @error('location')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <x-map.leaflet :always-show-markers="true" :center="$latitude !== null ? [$latitude, $longitude] : null" :zoom="$latitude !== null ? 17 : 12" :location-accuracy="$gpsAccuracy" height="430px" />
                @if ($locationDetected)
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-100 p-3 dark:bg-slate-800">
                            <p class="text-xs text-slate-500">Latitude terkunci</p>
                            <p class="font-mono font-bold">{{ number_format($latitude, 7, '.', '') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-100 p-3 dark:bg-slate-800">
                            <p class="text-xs text-slate-500">Longitude terkunci</p>
                            <p class="font-mono font-bold">{{ number_format($longitude, 7, '.', '') }}</p>
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Alamat otomatis</p>
                        <p class="mt-1 text-sm">{{ $geocodedAddress }}</p>
                        @if ($gpsAccuracy)
                            <p class="mt-1 text-xs text-slate-500">Akurasi sekitar {{ number_format($gpsAccuracy, 0) }}
                                meter.</p>
                        @endif
                    </div>
                    <x-ui.textarea label="Alamat yang akan disimpan" name="addressText" wire:model="addressText"
                        rows="3" hint="Boleh diperbaiki, tetapi titik koordinat tidak berubah."></x-ui.textarea>
                @endif
                <div class="flex justify-between">
                    <x-ui.button variant="secondary" wire:click="previousStep">Kembali</x-ui.button><x-ui.button
                        wire:click="confirmLocation" :disabled="!$locationDetected">Konfirmasi lokasi</x-ui.button>
                </div>
            </div>
        @else<form wire:submit="submit" class="space-y-5">
                <div>
                    <h2 class="text-xl font-black">Lengkapi detail laporan</h2>
                    <p class="mt-1 text-sm text-slate-500">Gunakan uraian faktual, singkat, dan mudah diverifikasi.</p>
                </div><x-ui.select label="Kategori" name="categoryId" wire:model="categoryId">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input label="Patokan tambahan" name="landmark" wire:model="landmark"
                    placeholder="Contoh: depan gerbang sekolah" /><x-ui.textarea label="Deskripsi" name="description"
                    wire:model.live.debounce.350ms="description" rows="6"
                    placeholder="Jelaskan kondisi, dampak, dan waktu kejadian."></x-ui.textarea>
                @if ($censoredPreview !== '' && $censoredPreview !== $description)
                    <div class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Pratinjau sensor:
                        {{ $censoredPreview }}</div>
                @endif
                <x-ui.checkbox label="Sembunyikan nama saya pada tampilan publik" wire:model="isAnonymous" />
                @error('locationConfirmed')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-between"><x-ui.button type="button" variant="secondary"
                        wire:click="previousStep">Kembali</x-ui.button><x-ui.button type="submit"
                        wire:loading.attr="disabled"><span wire:loading.remove wire:target="submit">Kirim
                            laporan</span><span wire:loading wire:target="submit">Memproses…</span></x-ui.button></div>
            </form>
        @endif
    </div>
</div>
