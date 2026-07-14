<form wire:submit="save" class="space-y-7">
    <div>
        <p class="text-sm font-bold text-blue-600">Edit laporan</p>
        <h2 class="text-3xl font-black">Perbaiki laporan sebelum disetujui</h2>
        <p class="mt-2 text-slate-500">Setiap perubahan akan mengembalikan laporan ke status menunggu moderasi.</p>
    </div>

    <div class="panel p-5 sm:p-7">
        <div class="grid gap-7 xl:grid-cols-[1fr_.9fr]">
            <div class="space-y-5">
                <x-ui.select label="Kategori" name="categoryId" wire:model="categoryId">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.textarea label="Deskripsi" name="description" wire:model="description" rows="6" />
                <x-ui.textarea
                    label="Alamat yang disimpan"
                    name="addressText"
                    wire:model="addressText"
                    rows="3"
                    hint="Alamat boleh diedit, sedangkan koordinat tetap berasal dari GPS."
                />
                <x-ui.input label="Patokan" name="landmark" wire:model="landmark" />
                <x-ui.checkbox label="Sembunyikan nama saya" wire:model="isAnonymous" />
            </div>

            <div
                class="space-y-4"
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
                            position => {
                                $wire.captureLocation(
                                    position.coords.latitude,
                                    position.coords.longitude,
                                    position.coords.accuracy
                                ).finally(() => this.locating = false);
                            },
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
                    <h3 class="font-black">Lokasi GPS</h3>
                    <x-ui.button type="button" variant="secondary" @click="locate()" x-bind:disabled="locating">
                        <span x-show="!locating">Ambil ulang GPS</span>
                        <span x-show="locating" x-cloak>Mengambil…</span>
                    </x-ui.button>
                </div>

                <p x-show="error" x-text="error" x-cloak class="rounded-xl bg-red-50 p-3 text-sm text-red-700"></p>

                <x-map.leaflet
                    :always-show-markers="true"
                    :center="[$latitude, $longitude]"
                    :zoom="17"
                    :location-accuracy="$gpsAccuracy"
                    height="390px"
                />

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl bg-slate-100 p-3 dark:bg-slate-800">
                        Latitude terkunci<br>
                        <strong class="font-mono">{{ number_format($latitude, 7, '.', '') }}</strong>
                    </div>
                    <div class="rounded-xl bg-slate-100 p-3 dark:bg-slate-800">
                        Longitude terkunci<br>
                        <strong class="font-mono">{{ number_format($longitude, 7, '.', '') }}</strong>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Alamat otomatis dari GPS</p>
                    <p class="mt-1 text-sm">{{ $geocodedAddress ?: 'Alamat belum tersedia.' }}</p>
                    @if($gpsAccuracy)
                        <p class="mt-1 text-xs text-slate-500">Akurasi sekitar {{ number_format($gpsAccuracy, 0) }} meter.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="panel p-5 sm:p-7">
        <h3 class="text-lg font-black">Media tersimpan</h3>
        <p class="mt-1 text-sm text-slate-500">Tandai media yang ingin dihapus. Minimal satu media harus tetap tersedia.</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($complaint->media as $media)
                <div class="relative overflow-hidden rounded-2xl {{ in_array($media->id, $removeMediaIds, true) ? 'ring-4 ring-red-500 opacity-50' : '' }}">
                    <x-media.tile :media="$media" compact />
                    <button
                        type="button"
                        wire:click="toggleRemoveMedia({{ $media->id }})"
                        class="absolute bottom-2 left-2 right-2 rounded-xl bg-slate-950/80 px-3 py-2 text-xs font-bold text-white backdrop-blur"
                    >
                        {{ in_array($media->id, $removeMediaIds, true) ? 'Batalkan penghapusan' : 'Hapus media ini' }}
                    </button>
                </div>
            @endforeach
        </div>

        <label class="mt-5 block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 p-5 text-center transition hover:border-blue-500 dark:border-slate-700">
            <input
                type="file"
                wire:model="newMediaFiles"
                multiple
                accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                class="sr-only"
            >
            <strong>＋ Tambahkan foto atau video baru</strong>
            <span class="mt-1 block text-xs text-slate-500">Total maksimal delapan media dan 50 MB per berkas.</span>
        </label>

        <div wire:loading wire:target="newMediaFiles" class="mt-3 text-sm font-bold text-blue-600">Mengunggah media…</div>
        @error('newMediaFiles.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if($newMediaFiles)
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($newMediaFiles as $index => $file)
                    <div class="relative overflow-hidden rounded-2xl bg-slate-100 p-2 text-center dark:bg-slate-800">
                        @if(str_starts_with((string) $file->getMimeType(), 'image/'))
                            <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full rounded-xl object-cover" alt="Pratinjau media baru">
                        @else
                            <div class="grid aspect-square place-items-center rounded-xl bg-slate-950 p-3 text-white">
                                <div>
                                    <span class="text-3xl">▶</span>
                                    <p class="mt-2 line-clamp-2 text-xs font-bold">{{ $file->getClientOriginalName() }}</p>
                                </div>
                            </div>
                        @endif
                        <button
                            type="button"
                            wire:click="removeTemporaryMedia({{ $index }})"
                            class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-full bg-slate-950/80 text-white"
                            aria-label="Hapus media baru"
                        >×</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="flex justify-end gap-3">
        <x-ui.button :href="route('profile')" variant="secondary" wire:navigate>Batal</x-ui.button>
        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Simpan dan kirim ulang</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </x-ui.button>
    </div>
</form>
