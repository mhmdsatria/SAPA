<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Region;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Peta Pantau')]
class MonitoringMap extends Component
{
    public int $categoryId = 0;
    public int $regionId = 0;
    public string $status = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function render()
    {
        $query = array_filter([
            'include_all' => 1, 'category_id' => $this->categoryId ?: null, 'region_id' => $this->regionId ?: null,
            'status' => $this->status, 'date_from' => $this->dateFrom, 'date_to' => $this->dateTo,
        ], fn (mixed $value): bool => $value !== '' && $value !== null);

        return view('livewire.admin.monitoring-map', [
            'mapEndpoint' => route('gis.complaints', $query),
            'regionsEndpoint' => route('gis.regions', array_filter(['category_id' => $this->categoryId ?: null])),
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'regions' => Region::query()->where('is_active', true)->whereIn('level', ['provinsi', 'kota', 'kabupaten'])->orderBy('name')->get(),
            'statuses' => [Complaint::STATUS_PENDING => 'Menunggu', Complaint::STATUS_APPROVED => 'Disetujui', Complaint::STATUS_REJECTED => 'Ditolak'],
        ]);
    }
}
