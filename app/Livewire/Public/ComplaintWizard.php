<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Services\CensorService;
use App\Services\ComplaintService;
use App\Services\GisService;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.citizen')]
#[Title('Buat Laporan')]
class ComplaintWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;
    /** @var array<int, UploadedFile> */
    public array $mediaFiles = [];
    public int $categoryId = 0;

    #[Locked]
    public ?float $latitude = null;

    #[Locked]
    public ?float $longitude = null;

    #[Locked]
    public ?float $gpsAccuracy = null;

    #[Locked]
    public string $geocodedAddress = '';

    public string $addressText = '';
    public bool $locationDetected = false;
    public bool $locationConfirmed = false;
    public string $landmark = '';
    public string $description = '';
    public bool $isAnonymous = false;
    public string $censoredPreview = '';

    public function mount(): void
    {
        $this->categoryId = (int) (Category::query()->active()->value('id') ?? 0);
    }

    public function updatedMediaFiles(): void
    {
        $this->validateMedia();
    }

    public function removeTemporaryMedia(int $index): void
    {
        if (array_key_exists($index, $this->mediaFiles)) {
            unset($this->mediaFiles[$index]);
            $this->mediaFiles = array_values($this->mediaFiles);
        }
    }

    public function updatedDescription(CensorService $censorService): void
    {
        $this->censoredPreview = $censorService->censor($this->description);
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateMedia();
            $this->step = 2;
            $this->dispatch('map-resize');

            return;
        }

        if ($this->step === 2) {
            $this->confirmLocation();
        }
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->dispatch('map-resize');
    }

    public function captureLocation(float $latitude, float $longitude, ?float $accuracy, GisService $gisService): void
    {
        validator(
            ['latitude' => $latitude, 'longitude' => $longitude, 'accuracy' => $accuracy],
            [
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'accuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            ]
        )->validate();

        $this->latitude = round($latitude, 7);
        $this->longitude = round($longitude, 7);
        $this->gpsAccuracy = $accuracy !== null ? round($accuracy, 2) : null;
        $this->geocodedAddress = $gisService->reverseGeocode($this->latitude, $this->longitude);
        $this->addressText = $this->geocodedAddress;
        $this->locationDetected = true;
        $this->locationConfirmed = false;
        $this->resetErrorBag('location');

        $this->dispatch('wizard-map-location',
            latitude: $this->latitude,
            longitude: $this->longitude,
            accuracy: $this->gpsAccuracy,
        );
    }

    public function refreshAddress(GisService $gisService): void
    {
        if ($this->latitude === null || $this->longitude === null) {
            return;
        }
        $this->geocodedAddress = $gisService->reverseGeocode($this->latitude, $this->longitude);
        $this->addressText = $this->geocodedAddress;
    }

    public function confirmLocation(): void
    {
        if (! $this->locationDetected || $this->latitude === null || $this->longitude === null) {
            $this->addError('location', 'Aktifkan GPS dan ambil lokasi terlebih dahulu.');

            return;
        }

        $this->validate(['addressText' => ['required', 'string', 'min:5', 'max:1000']]);
        $this->locationConfirmed = true;
        $this->step = 3;
    }

    public function submit(ComplaintService $complaintService): mixed
    {
        $this->validateMedia();
        $this->validate([
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'gpsAccuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'addressText' => ['required', 'string', 'min:5', 'max:1000'],
            'landmark' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'isAnonymous' => ['boolean'],
            'locationConfirmed' => ['accepted'],
        ], ['locationConfirmed.accepted' => 'Lokasi GPS harus dikonfirmasi.']);

        $complaint = $complaintService->create(auth()->user(), [
            'category_id' => $this->categoryId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'gps_accuracy' => $this->gpsAccuracy,
            'geocoded_address' => $this->geocodedAddress,
            'address_text' => $this->addressText,
            'landmark' => $this->landmark,
            'description' => $this->description,
            'is_anonymous' => $this->isAnonymous,
        ], $this->mediaFiles);

        session()->flash('success', $complaint->is_duplicate_flag
            ? 'Laporan tersimpan dan ditandai sebagai kemungkinan duplikat untuk ditinjau admin.'
            : 'Laporan berhasil dikirim dan menunggu moderasi admin.');

        return $this->redirectRoute('profile', navigate: true);
    }

    private function validateMedia(): void
    {
        $this->validate([
            'mediaFiles' => ['required', 'array', 'min:1', 'max:'.MediaService::MAX_FILES],
            'mediaFiles.*' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm', 'max:51200'],
        ], [
            'mediaFiles.required' => 'Unggah minimal satu foto atau video.',
            'mediaFiles.max' => 'Maksimal '.MediaService::MAX_FILES.' media per laporan.',
            'mediaFiles.*.max' => 'Ukuran setiap media maksimal 50 MB.',
        ]);
    }

    public function render()
    {
        return view('livewire.public.complaint-wizard', [
            'categories' => Category::query()->active()->get(),
        ]);
    }
}
