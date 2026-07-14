<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Region;
use App\Services\GisService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Beranda')]
class HomePage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 0)]
    public int $categoryId = 0;

    #[Url(except: 0)]
    public int $regionId = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedRegionId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'regionId']);
        $this->resetPage();
    }

    public function render(GisService $gisService)
    {
        $regionIds = $gisService->descendantRegionIds($this->regionId ?: null);
        $query = Complaint::query()->published()->with(['user', 'region', 'categoryRecord', 'media'])
            ->when($this->categoryId, fn (Builder $q): Builder => $q->where('category_id', $this->categoryId))
            ->when($regionIds !== [], fn (Builder $q): Builder => $q->whereIn('region_id', $regionIds))
            ->when(trim($this->search) !== '', function (Builder $query): Builder {
                $term = '%'.trim($this->search).'%';
                return $query->where(fn (Builder $nested): Builder => $nested
                    ->where('title', 'like', $term)->orWhere('description', 'like', $term)->orWhere('address_text', 'like', $term));
            });

        $complaints = $query->latest('approved_at')->paginate(8);
        $stats = [
            'approved' => Complaint::query()->published()->count(),
            'pending' => Complaint::query()->where('status', Complaint::STATUS_PENDING)->count(),
            'upvotes' => (int) Complaint::query()->published()->sum('upvotes_count'),
        ];
        $mapParams = array_filter(['category_id' => $this->categoryId ?: null, 'region_id' => $this->regionId ?: null]);

        return view('livewire.public.home-page', [
            'complaints' => $complaints,
            'stats' => $stats,
            'categories' => Category::query()->active()->get(),
            'regions' => Region::query()->where('is_active', true)->whereIn('level', ['provinsi', 'kota', 'kabupaten'])->orderBy('level')->orderBy('name')->get(),
            'mapEndpoint' => route('gis.complaints', $mapParams),
            'regionsEndpoint' => route('gis.regions', array_filter(['category_id' => $this->categoryId ?: null])),
        ]);
    }
}
