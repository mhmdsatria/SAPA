<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Region;
use App\Services\GisService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Arsip Laporan')]
class ComplaintArchive extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $categoryId = 0;
    public int $regionId = 0;
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'categoryId', 'regionId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'categoryId', 'regionId', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render(GisService $gisService)
    {
        $regionIds = $gisService->descendantRegionIds($this->regionId ?: null);
        $complaints = Complaint::query()->forAdmin(auth()->user())
            ->with(['user', 'region', 'moderator', 'categoryRecord', 'media'])
            ->when(trim($this->search) !== '', function (Builder $query): Builder {
                $term = '%'.trim($this->search).'%';
                return $query->where(fn (Builder $nested): Builder => $nested
                    ->where('title', 'like', $term)->orWhere('address_text', 'like', $term)
                    ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', $term)));
            })
            ->when($this->status !== '', fn (Builder $query): Builder => $query->where('status', $this->status))
            ->when($this->categoryId, fn (Builder $query): Builder => $query->where('category_id', $this->categoryId))
            ->when($regionIds !== [], fn (Builder $query): Builder => $query->whereIn('region_id', $regionIds))
            ->when($this->dateFrom !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->dateTo))
            ->latest()->paginate(15);
        $export = array_filter([
            'date_from' => $this->dateFrom, 'date_to' => $this->dateTo, 'category_id' => $this->categoryId ?: null,
            'region_id' => $this->regionId ?: null, 'status' => $this->status,
        ]);

        return view('livewire.admin.complaint-archive', [
            'complaints' => $complaints,
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'regions' => Region::query()->where('is_active', true)->whereIn('level', ['provinsi', 'kota', 'kabupaten'])->orderBy('name')->get(),
            'excelUrl' => route('admin.export.excel', $export),
            'pdfUrl' => route('admin.export.pdf', $export),
        ]);
    }
}
