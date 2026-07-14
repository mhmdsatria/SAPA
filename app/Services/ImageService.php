<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageService
{
    public function processComplaintPhoto(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Berkas foto tidak valid.');
        }

        $takenAt = $this->extractTakenAt($file);
        $ageDays = $takenAt?->isPast()
            ? (int) floor($takenAt->diffInDays(CarbonImmutable::now(config('app.timezone'))))
            : null;
        $isStale = $ageDays !== null && $ageDays > 7;
        $path = sprintf(
            'complaints/%s/%s.webp',
            now()->format('Y/m'),
            Str::uuid()->toString()
        );

        Storage::disk('public')->makeDirectory(dirname($path));

        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager
            ->decodePath($file->getRealPath())
            ->orient()
            ->scaleDown(width: 1920, height: 1920);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 82);
        $encoded->save(Storage::disk('public')->path($path));

        return [
            'image_path' => $path,
            'image_original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            'image_mime' => 'image/webp',
            'image_size' => Storage::disk('public')->size($path),
            'image_hash' => hash_file('sha256', Storage::disk('public')->path($path)),
            'image_taken_at' => $takenAt,
            'image_age_days' => $ageDays,
            'exif_is_stale' => $isStale,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function extractTakenAt(UploadedFile $file): ?CarbonImmutable
    {
        if (! function_exists('exif_read_data')) {
            return null;
        }

        try {
            $exif = @exif_read_data($file->getRealPath(), null, true, false);

            if (! is_array($exif)) {
                return null;
            }

            $candidates = [
                $exif['EXIF']['DateTimeOriginal'] ?? null,
                $exif['EXIF']['DateTimeDigitized'] ?? null,
                $exif['IFD0']['DateTime'] ?? null,
            ];

            foreach ($candidates as $candidate) {
                if (is_int($candidate)) {
                    return CarbonImmutable::createFromTimestamp($candidate, config('app.timezone'));
                }

                if (! is_string($candidate) || trim($candidate) === '') {
                    continue;
                }

                $parsed = CarbonImmutable::createFromFormat(
                    'Y:m:d H:i:s',
                    trim($candidate),
                    config('app.timezone')
                );

                if ($parsed !== false) {
                    return $parsed;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }
}
