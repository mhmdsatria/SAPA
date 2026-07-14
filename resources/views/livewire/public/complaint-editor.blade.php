<form wire:submit="save" class="page-stack">
    <div>
        <p class="page-kicker">Edit laporan</p>
        <h2 class="page-heading">Perbaiki laporan sebelum disetujui</h2>
        <p class="page-description">Setiap perubahan akan mengembalikan laporan ke antrean moderasi. Koordinat dapat diambil ulang dari GPS dan alamat teks tetap dapat disesuaikan.</p>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_.9fr]">
        <section class="panel space-y-4 p-4 sm:p-6">
            <h3 class="text-base font-black sm:text-lg">Informasi laporan</h3>
            <x-ui.select label="Kategori" name="categoryId" wire:model="categoryId">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.textarea label="Deskripsi" name="description" wire:model="description" rows="6" />
            <x-ui.textarea label="Alamat yang disimpan" name="addressText" wire:model="addressText" rows="3" hint="Alamat dapat diedit, sedangkan koordinat tetap berasal dari GPS." />
            <x-ui.input label="Patokan" name="landmark" wire:model="landmark" />
            <x-ui.checkbox label="Sembunyikan nama saya" wire:model="isAnonymous" />
        </section>

        <section
            class="panel space-y-4 p-4 sm:p-6"
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
                            this.error = failure.message || 'Lokasi gagal diambil. Periksa izin GPS pada browser.';
                        },
                        { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }
                    );
                }
            }"
        >
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-base font-black sm:text-lg">Lokasi GPS</h3>
                <x-ui.button type="button" variant="secondary" @click="locate()" x-bind:disabled="locating">
                    <span x-show="!locating">Ambil Ulang</span>
                    <span x-show="locating" x-cloak>Mengambil…</span>
                </x-ui.button>
            </div>

            <p x-show="error" x-text="error" x-cloak class="rounded-xl bg-red-50 p-3 text-xs font-semibold text-red-700 dark:bg-red-950/30 dark:text-red-300 sm:text-sm"></p>

            <x-map.leaflet :always-show-markers="true" :center="[$latitude, $longitude]" :zoom="17" :location-accuracy="$gpsAccuracy" height="360px" />

            <div class="grid grid-cols-2 gap-2 text-[10px] sm:text-xs">
                <div class="panel-soft p-3">
                    <p class="text-slate-500">Latitude</p>
                    <strong class="mt-1 block truncate font-mono">{{ number_format($latitude, 7, '.', '') }}</strong>
                </div>
                <div class="panel-soft p-3">
                    <p class="text-slate-500">Longitude</p>
                    <strong class="mt-1 block truncate font-mono">{{ number_format($longitude, 7, '.', '') }}</strong>
                </div>
            </div>

            <x-map.directions-link :latitude="$latitude" :longitude="$longitude" label="Buka lokasi di Google Maps" class="w-full" />

            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/30 sm:p-4">
                <p class="text-[10px] font-black uppercase tracking-wide text-emerald-700 dark:text-emerald-300 sm:text-xs">Alamat otomatis dari GPS</p>
                <p class="mt-1 text-xs leading-5 sm:text-sm">{{ $geocodedAddress ?: 'Alamat belum tersedia.' }}</p>
                @if($gpsAccuracy)
                    <p class="mt-1 text-[10px] text-slate-500 sm:text-xs">Akurasi sekitar {{ number_format($gpsAccuracy, 0) }} meter.</p>
                @endif
            </div>
        </section>
    </div>

    <section class="panel p-4 sm:p-6">
        <h3 class="text-base font-black sm:text-lg">Media laporan</h3>
        <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">Tandai media yang ingin dihapus. Minimal satu media harus tetap tersedia.</p>

        <div class="mt-4 gallery-grid">
            @foreach($complaint->media as $media)
                <div class="relative overflow-hidden rounded-xl {{ in_array($media->id, $removeMediaIds, true) ? 'ring-4 ring-red-500 opacity-50' : '' }}">
                    <x-media.tile :media="$media" compact />
                    <button type="button" wire:click="toggleRemoveMedia({{ $media->id }})" class="absolute inset-x-2 bottom-2 rounded-lg bg-slate-950/85 px-2 py-1.5 text-[9px] font-black text-white backdrop-blur sm:text-xs">
                        {{ in_array($media->id, $removeMediaIds, true) ? 'Batalkan' : 'Hapus Media' }}
                    </button>
                </div>
            @endforeach
        </div>

        <label class="mt-4 block cursor-pointer rounded-xl border-2 border-dashed border-slate-300 p-4 text-center transition hover:border-blue-500 dark:border-slate-700 sm:p-5">
            <input type="file" wire:model="newMediaFiles" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" class="sr-only">
            <strong class="text-xs sm:text-sm">＋ Tambahkan foto atau video baru</strong>
            <span class="mt-1 block text-[10px] text-slate-500 sm:text-xs">Total maksimal delapan media dan 50 MB per berkas.</span>
        </label>

        <div wire:loading wire:target="newMediaFiles" class="mt-3 text-xs font-bold text-blue-600 sm:text-sm">Mengunggah media…</div>
        @error('newMediaFiles.*') <p class="mt-2 text-xs font-bold text-red-600 sm:text-sm">{{ $message }}</p> @enderror

        @if($newMediaFiles)
            <div class="mt-4 gallery-grid">
                @foreach($newMediaFiles as $index => $file)
                    <div class="relative overflow-hidden rounded-xl bg-slate-100 text-center dark:bg-slate-800">
                        @if(str_starts_with((string) $file->getMimeType(), 'image/'))
                            <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full object-cover" alt="Pratinjau media baru">
                        @else
                            <div class="grid aspect-square place-items-center bg-slate-950 p-3 text-white">
                                <div>
                                    <span class="text-2xl sm:text-3xl">▶</span>
                                    <p class="mt-2 line-clamp-2 text-[10px] font-bold sm:text-xs">{{ $file->getClientOriginalName() }}</p>
                                </div>
                            </div>
                        @endif
                        <button type="button" wire:click="removeTemporaryMedia({{ $index }})" class="absolute right-2 top-2 grid h-7 w-7 place-items-center rounded-full bg-slate-950/80 text-white" aria-label="Hapus media baru">×</button>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="flex justify-end gap-2">
        <x-ui.button :href="route('profile')" variant="secondary" wire:navigate>Batal</x-ui.button>
        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </x-ui.button>
    </div>
</form>
