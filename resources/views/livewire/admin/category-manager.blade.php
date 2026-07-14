<div class="page-stack">
    <div>
        <p class="page-kicker">Konfigurasi sistem</p>
        <h2 class="page-heading">Kelola kategori laporan</h2>
        <p class="page-description">Tambah, ubah, urutkan, aktifkan, atau nonaktifkan kategori tanpa mengubah struktur database.</p>
    </div>

    <div class="grid gap-5 xl:grid-cols-[360px_1fr]">
        <form wire:submit="save" class="panel space-y-4 p-4 sm:p-6">
            <h3 class="text-base font-black sm:text-lg">{{ $editingId ? 'Edit kategori' : 'Kategori baru' }}</h3>
            <x-ui.input label="Nama" name="name" wire:model="name" />
            <x-ui.input label="Slug" name="slug" wire:model="slug" hint="Boleh kosong, akan dibuat dari nama." />
            <x-ui.textarea label="Deskripsi" name="description" wire:model="description" rows="3"></x-ui.textarea>
            <div class="grid grid-cols-2 gap-3">
                <x-ui.input label="Warna" name="color" type="color" wire:model="color" />
                <x-ui.input label="Urutan" name="sortOrder" type="number" wire:model="sortOrder" />
            </div>
            <x-ui.input label="Ikon teks" name="icon" wire:model="icon" hint="Contoh: road, trash, lightbulb." />
            <x-ui.checkbox label="Kategori aktif" wire:model="isActive" />
            <div class="flex gap-2">
                <x-ui.button type="submit">Simpan</x-ui.button>
                @if($editingId)<x-ui.button type="button" variant="secondary" wire:click="resetForm">Batal</x-ui.button>@endif
            </div>
        </form>

        <div>
            <div class="mobile-card-list">
                @foreach($categories as $category)
                    <article class="panel min-w-0 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="h-4 w-4 shrink-0 rounded-full" style="background: {{ $category->color }}"></span>
                            <button wire:click="toggle({{ $category->id }})" class="rounded-full px-2 py-1 text-[9px] font-black {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                        </div>
                        <h3 class="mt-2 truncate text-xs font-black">{{ $category->name }}</h3>
                        <p class="mt-1 truncate text-[9px] text-slate-500">{{ $category->slug }}</p>
                        <p class="mt-2 text-[9px] font-bold text-slate-400">{{ $category->complaints_count }} laporan · urutan {{ $category->sort_order }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-1.5">
                            <x-ui.button variant="secondary" wire:click="edit({{ $category->id }})">Edit</x-ui.button>
                            <x-ui.button variant="danger" wire:click="remove({{ $category->id }})" wire:confirm="Hapus kategori ini? Jika sudah dipakai, kategori hanya akan dinonaktifkan.">Hapus</x-ui.button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="panel desktop-table">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800">
                        <tr><th class="p-4">Kategori</th><th class="p-4">Warna</th><th class="p-4">Laporan</th><th class="p-4">Status</th><th class="p-4 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($categories as $category)
                            <tr>
                                <td class="p-4"><p class="font-black">{{ $category->name }}</p><p class="text-xs text-slate-500">{{ $category->slug }} · urutan {{ $category->sort_order }}</p></td>
                                <td class="p-4"><span class="inline-flex items-center gap-2"><span class="h-5 w-5 rounded-full" style="background: {{ $category->color }}"></span>{{ $category->color }}</span></td>
                                <td class="p-4">{{ $category->complaints_count }}</td>
                                <td class="p-4"><button wire:click="toggle({{ $category->id }})" class="rounded-full px-3 py-1 text-xs font-bold {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</button></td>
                                <td class="p-4"><div class="flex justify-end gap-2"><x-ui.button variant="secondary" wire:click="edit({{ $category->id }})">Edit</x-ui.button><x-ui.button variant="danger" wire:click="remove({{ $category->id }})" wire:confirm="Hapus kategori ini? Jika sudah dipakai, kategori hanya akan dinonaktifkan.">Hapus</x-ui.button></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
