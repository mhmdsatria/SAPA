<div class="mx-auto max-w-5xl page-stack">
    <div>
        <p class="page-kicker">Wizard pelaporan</p>
        <h1 class="page-heading">Buat laporan baru</h1>
        <p class="page-description">Unggah bukti, ambil lokasi GPS perangkat, lalu lengkapi detail laporan. Koordinat tetap terkunci dari sensor, sedangkan teks alamat dapat diperbaiki.</p>
    </div>

    <ol class="grid grid-cols-3 gap-2 sm:gap-3">
        @foreach ([1 => 'Media', 2 => 'Lokasi', 3 => 'Detail'] as $number => $label)
            <li class="rounded-xl border px-2 py-2.5 text-center text-[10px] font-black uppercase tracking-wide sm:px-3 sm:py-3 sm:text-xs {{ $step >= $number ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'border-slate-200 text-slate-400 dark:border-slate-800' }}">
                <span class="block text-sm sm:inline sm:text-xs">{{ $number }}</span>
                <span class="sm:ml-1">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    <div class="panel p-4 sm:p-6 lg:p-7">
        @if ($step === 1)
            <div class="space-y-5">
                <div>
                    <h2 class="text-lg font-black sm:text-xl">Unggah foto atau video</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">Maksimal delapan media. Format JPEG, PNG, WebP, MP4, MOV, dan WebM dengan ukuran maksimal 50 MB per berkas.</p>
                </div>

                <label class="block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 p-5 text-center transition hover:border-blue-500 hover:bg-blue-50/40 dark:border-slate-700 dark:hover:bg-blue-950/20 sm:p-8">
                    <input type="file" wire:model="mediaFiles" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" multiple class="sr-only">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-blue-50 text-2xl font-light text-blue-600 dark:bg-blue-950/50 dark:text-blue-300 mx-auto">＋</span>
                    <span class="mt-3 block text-sm font-black sm:text-base">Pilih foto atau video</span>
                    <span class="mt-1 block text-xs text-slate-500">Foto akan dikompresi dan metadata EXIF diperiksa.</span>
                </label>

                <div wire:loading wire:target="mediaFiles" class="text-xs font-bold text-blue-600 sm:text-sm">Mengunggah media…</div>
                @error('mediaFiles') <p class="text-xs font-bold text-red-600 sm:text-sm">{{ $message }}</p> @enderror
                @error('mediaFiles.*') <p class="text-xs font-bold text-red-600 sm:text-sm">{{ $message }}</p> @enderror

                @if ($mediaFiles)
                    <div class="gallery-grid">
                        @foreach ($mediaFiles as $index => $file)
                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800">
                                @if (str_starts_with((string) $file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full object-cover" alt="Pratinjau media">
                                @else
                                    <div class="grid aspect-square place-items-center bg-slate-950 p-3 text-center text-white">
                                        <div>
                                            <span class="text-2xl sm:text-4xl">▶</span>
                                            <p class="mt-2 line-clamp-2 text-[10px] font-bold sm:text-xs">{{ $file->getClientOriginalName() }}</p>
                                        </div>
                                    </div>
                                @endif
                                <button type="button" wire:click="removeTemporaryMedia({{ $index }})" class="absolute right-2 top-2 grid h-7 w-7 place-items-center rounded-full bg-slate-950/80 text-sm font-black text-white" aria-label="Hapus media">×</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end">
                    <x-ui.button wire:click="nextStep" wire:loading.attr="disabled">Lanjut ke Lokasi</x-ui.button>
                </div>
            </div>
        @elseif($step === 2)
            <div
                class="space-y-5"
                x-data="{
                    locating: false,
                    error: '',
                    locate() {
                        this.error = '';
                        if (!navigator.geolocation) {
                            this.error = 'Perangkat tidak mendukung GPS.';
                            return;
                        }
                        this.locating = true;
                        navigator.geolocation.getCurrentPosition(
                            position => $wire.captureLocation(position.coords.latitude, position.coords.longitude, position.coords.accuracy).finally(() => this.locating = false),
                            failure => {
                                this.locating = false;
                                this.error = failure.message || 'Lokasi gagal diambil. Aktifkan GPS dan izin lokasi browser.';
                            },
                            { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }
                        );
                    }
                }"
            >
                <div>
                    <h2 class="text-lg font-black sm:text-xl">Ambil dan periksa lokasi</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">Koordinat berasal dari sensor perangkat. Alamat hasil geocoding dapat dikoreksi tanpa mengubah titik GPS.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="button" @click="locate()" x-bind:disabled="locating">
                        <span x-show="!locating">⌖ Ambil Lokasi GPS</span>
                        <span x-show="locating" x-cloak>Mengambil…</span>
                    </x-ui.button>
                    @if ($locationDetected)
                        <x-ui.button type="button" variant="secondary" wire:click="refreshAddress">Muat Ulang Alamat</x-ui.button>
                    @endif
                </div>

                <p x-show="error" x-text="error" x-cloak class="rounded-xl bg-red-50 p-3 text-xs font-semibold text-red-700 dark:bg-red-950/30 dark:text-red-300 sm:text-sm"></p>
                @error('location') <p class="text-xs font-bold text-red-600 sm:text-sm">{{ $message }}</p> @enderror

                <x-map.leaflet :always-show-markers="true" :center="$latitude !== null ? [$latitude, $longitude] : null" :zoom="$latitude !== null ? 17 : 12" :location-accuracy="$gpsAccuracy" height="390px" />

                @if ($locationDetected)
                    <div class="grid grid-cols-2 gap-2 text-[10px] sm:gap-3 sm:text-xs">
                        <div class="panel-soft p-3">
                            <p class="text-slate-500">Latitude terkunci</p>
                            <p class="mt-1 truncate font-mono font-black">{{ number_format($latitude, 7, '.', '') }}</p>
                        </div>
                        <div class="panel-soft p-3">
                            <p class="text-slate-500">Longitude terkunci</p>
                            <p class="mt-1 truncate font-mono font-black">{{ number_format($longitude, 7, '.', '') }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/30 sm:p-4">
                        <p class="text-[10px] font-black uppercase tracking-wide text-emerald-700 dark:text-emerald-300 sm:text-xs">Alamat otomatis</p>
                        <p class="mt-1 text-xs leading-5 sm:text-sm">{{ $geocodedAddress }}</p>
                        @if ($gpsAccuracy)
                            <p class="mt-1 text-[10px] text-slate-500 sm:text-xs">Akurasi sekitar {{ number_format($gpsAccuracy, 0) }} meter.</p>
                        @endif
                    </div>

                    <x-ui.textarea label="Alamat yang akan disimpan" name="addressText" wire:model="addressText" rows="3" hint="Alamat boleh diperbaiki. Titik koordinat tidak berubah."></x-ui.textarea>
                @endif

                <div class="flex justify-between gap-2">
                    <x-ui.button variant="secondary" wire:click="previousStep">Kembali</x-ui.button>
                    <x-ui.button wire:click="confirmLocation" :disabled="!$locationDetected">Konfirmasi Lokasi</x-ui.button>
                </div>
            </div>
        @else
            <form wire:submit="submit" class="space-y-5">
                <div>
                    <h2 class="text-lg font-black sm:text-xl">Lengkapi detail laporan</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">Gunakan uraian faktual, singkat, dan mudah diverifikasi.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-ui.select label="Kategori" name="categoryId" wire:model="categoryId">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Patokan tambahan" name="landmark" wire:model="landmark" placeholder="Contoh: depan gerbang sekolah" />
                </div>

                <x-ui.textarea label="Deskripsi" name="description" wire:model.live.debounce.350ms="description" rows="6" placeholder="Jelaskan kondisi, dampak, dan waktu kejadian."></x-ui.textarea>

                @if ($censoredPreview !== '' && $censoredPreview !== $description)
                    <div class="rounded-xl bg-amber-50 p-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-300 sm:text-sm">Pratinjau sensor: {{ $censoredPreview }}</div>
                @endif

                <x-ui.checkbox label="Sembunyikan nama saya pada tampilan publik" wire:model="isAnonymous" />
                @error('locationConfirmed') <p class="text-xs font-bold text-red-600 sm:text-sm">{{ $message }}</p> @enderror

                <div class="flex justify-between gap-2">
                    <x-ui.button type="button" variant="secondary" wire:click="previousStep">Kembali</x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Kirim Laporan</span>
                        <span wire:loading wire:target="submit">Memproses…</span>
                    </x-ui.button>
                </div>
            </form>
        @endif
    </div>
</div>
