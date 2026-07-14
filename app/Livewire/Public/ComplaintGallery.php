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
#[Title('Galeri Laporan')]
class ComplaintGallery extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 0)]
    public int $categoryId = 0;

    #[Url(except: 0)]
    public int $regionId = 0;

    #[Url(except: '')]
    public string $mediaType = '';

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

    public function updatedMediaType(): void
    {
        $this->resetPage();
    }

    public function render(GisService $gisService)
    {
        $regionIds = $gisService->descendantRegionIds($this->regionId ?: null);
        $complaints = Complaint::query()->published()
            ->with(['media', 'categoryRecord', 'region'])
            ->when($this->categoryId, fn (Builder $q): Builder => $q->where('category_id', $this->categoryId))
            ->when($regionIds !== [], fn (Builder $q): Builder => $q->whereIn('region_id', $regionIds))
            ->when($this->mediaType !== '', fn (Builder $q): Builder => $q->whereHas('media', fn (Builder $m): Builder => $m->where('media_type', $this->mediaType)))
            ->when(trim($this->search) !== '', function (Builder $q): Builder {
                $term = '%'.trim($this->search).'%';
                return $q->where(fn (Builder $n): Builder => $n->where('title', 'like', $term)->orWhere('address_text', 'like', $term));
            })
            ->latest('approved_at')->paginate(18);

        return view('livewire.public.complaint-gallery', [
            'complaints' => $complaints,
            'categories' => Category::query()->active()->get(),
            'regions' => Region::query()->where('is_active', true)->whereIn('level', ['provinsi', 'kota', 'kabupaten'])->orderBy('level')->orderBy('name')->get(),
        ]);
    }
}
