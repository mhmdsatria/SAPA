<div class="page-stack">
    <div>
        <p class="page-kicker">Pemantauan GIS</p>
        <h2 class="page-heading">Peta pantau utama</h2>
        <p class="page-description">Ringkasan wilayah ditampilkan sebagai gelembung proporsional. Titik laporan muncul setelah peta diperbesar.</p>
    </div>

    <div class="panel grid grid-cols-2 gap-3 p-3 sm:p-4 lg:grid-cols-3 xl:grid-cols-5">
        <x-ui.select label="Kategori" wire:model.live="categoryId">
            <option value="0">Semua kategori</option>
            @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
        </x-ui.select>
        <x-ui.select label="Wilayah" wire:model.live="regionId">
            <option value="0">Semua wilayah</option>
            @foreach($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach
        </x-ui.select>
        <x-ui.select label="Status" wire:model.live="status">
            <option value="">Semua status</option>
            @foreach($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
        </x-ui.select>
        <x-ui.input label="Dari tanggal" type="date" wire:model.live="dateFrom" />
        <x-ui.input label="Sampai tanggal" type="date" wire:model.live="dateTo" />
    </div>

    <div wire:key="admin-map-{{ md5($mapEndpoint.$regionsEndpoint) }}">
        <x-map.leaflet :endpoint="$mapEndpoint" :region-endpoint="$regionsEndpoint" height="640px" />
    </div>
</div>
