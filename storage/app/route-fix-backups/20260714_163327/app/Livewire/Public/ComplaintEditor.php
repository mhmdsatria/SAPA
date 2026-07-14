<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Services\GisService;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.citizen')]
#[Title('Edit Laporan')]
class ComplaintEditor extends Component
{
    use WithFileUploads;

    public Complaint $complaint;
    public int $categoryId;
    public string $description;
    public string $addressText;
    public string $landmark = '';
    public bool $isAnonymous = false;
    public array $newMediaFiles = [];
    public array $removeMediaIds = [];

    #[Locked]
    public ?float $latitude = null;

    #[Locked]
    public ?float $longitude = null;

    #[Locked]
    public ?float $gpsAccuracy = null;

    #[Locked]
    public string $geocodedAddress = '';

    public function mount(Complaint $complaint): void
    {
        Gate::authorize('update', $complaint);
        abort_unless($complaint->isEditableByReporter(), 403);
        $this->complaint = $complaint->load(['media', 'categoryRecord']);
        $this->categoryId = (int) ($complaint->category_id ?: Category::query()->where('slug', $complaint->category)->value('id'));
        $this->description = $complaint->description;
        $this->addressText = $complaint->address_text;
        $this->geocodedAddress = $complaint->geocoded_address ?: $complaint->address_text;
        $this->landmark = (string) $complaint->landmark;
        $this->isAnonymous = $complaint->is_anonymous;
        $this->latitude = $complaint->latitude;
        $this->longitude = $complaint->longitude;
        $this->gpsAccuracy = $complaint->gps_accuracy;
    }

    public function captureLocation(float $latitude, float $longitude, ?float $accuracy, GisService $gisService): void
    {
        validator(compact('latitude', 'longitude', 'accuracy'), [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
        ])->validate();
        $this->latitude = round($latitude, 7);
        $this->longitude = round($longitude, 7);
        $this->gpsAccuracy = $accuracy !== null ? round($accuracy, 2) : null;
        $this->geocodedAddress = $gisService->reverseGeocode($this->latitude, $this->longitude);
        $this->addressText = $this->geocodedAddress;
        $this->dispatch('wizard-map-location', latitude: $this->latitude, longitude: $this->longitude, accuracy: $this->gpsAccuracy);
    }

    public function toggleRemoveMedia(int $mediaId): void
    {
        $this->removeMediaIds = in_array($mediaId, $this->removeMediaIds, true)
            ? array_values(array_diff($this->removeMediaIds, [$mediaId]))
            : [...$this->removeMediaIds, $mediaId];
    }

    public function removeTemporaryMedia(int $index): void
    {
        unset($this->newMediaFiles[$index]);
        $this->newMediaFiles = array_values($this->newMediaFiles);
    }

    public function save(ComplaintService $service): mixed
    {
        Gate::authorize('update', $this->complaint);
        $this->validate([
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'addressText' => ['required', 'string', 'min:5', 'max:1000'],
            'landmark' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'newMediaFiles' => ['array', 'max:'.MediaService::MAX_FILES],
            'newMediaFiles.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm', 'max:51200'],
            'removeMediaIds' => ['array'],
            'removeMediaIds.*' => ['integer'],
        ]);

        $service->updateReporterComplaint($this->complaint, [
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'address_text' => $this->addressText,
            'geocoded_address' => $this->geocodedAddress,
            'landmark' => $this->landmark,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'gps_accuracy' => $this->gpsAccuracy,
            'is_anonymous' => $this->isAnonymous,
        ], $this->newMediaFiles, $this->removeMediaIds);

        session()->flash('success', 'Perubahan tersimpan. Laporan kembali masuk antrean moderasi.');

        return $this->redirectRoute('profile', navigate: true);
    }

    public function render()
    {
        return view('livewire.public.complaint-editor', [
            'categories' => Category::query()->where(fn ($q) => $q->where('is_active', true)->orWhereKey($this->categoryId))->orderBy('sort_order')->get(),
        ]);
    }
}
