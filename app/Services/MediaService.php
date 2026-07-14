<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MediaService
{
    public const MAX_FILES = 8;

    public function __construct(private readonly ImageService $imageService)
    {
    }

    /** @param array<int, UploadedFile> $files */
    public function processMany(array $files): array
    {
        if ($files === [] || count($files) > self::MAX_FILES) {
            throw new RuntimeException('Jumlah media harus antara 1 sampai '.self::MAX_FILES.' berkas.');
        }

        $processed = [];

        try {
            foreach (array_values($files) as $index => $file) {
                $processed[] = $this->processOne($file, $index);
            }
        } catch (Throwable $exception) {
            $this->deleteProcessed($processed);
            throw $exception;
        }

        return $processed;
    }

    public function processOne(UploadedFile $file, int $sortOrder = 0): array
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Salah satu berkas media tidak valid.');
        }

        $mime = (string) $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            $image = $this->imageService->processComplaintPhoto($file);

            return [
                'media_type' => 'image',
                'path' => $image['image_path'],
                'original_name' => $image['image_original_name'],
                'mime_type' => $image['image_mime'],
                'size_bytes' => $image['image_size'],
                'sha256' => $image['image_hash'],
                'taken_at' => $image['image_taken_at'],
                'age_days' => $image['image_age_days'],
                'is_stale' => $image['exif_is_stale'],
                'sort_order' => $sortOrder,
                'metadata' => ['source_mime' => $mime],
            ];
        }

        if (! in_array($mime, ['video/mp4', 'video/quicktime', 'video/webm'], true)) {
            throw new RuntimeException('Format media tidak didukung. Gunakan gambar JPEG, PNG, WebP atau video MP4, MOV, WebM.');
        }

        $extension = match ($mime) {
            'video/quicktime' => 'mov',
            'video/webm' => 'webm',
            default => 'mp4',
        };
        $path = sprintf('complaints/%s/%s.%s', now()->format('Y/m'), Str::uuid(), $extension);
        Storage::disk('public')->makeDirectory(dirname($path));
        Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));

        return [
            'media_type' => 'video',
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            'mime_type' => $mime,
            'size_bytes' => Storage::disk('public')->size($path),
            'sha256' => hash_file('sha256', Storage::disk('public')->path($path)),
            'taken_at' => null,
            'age_days' => null,
            'is_stale' => false,
            'sort_order' => $sortOrder,
            'metadata' => ['extension' => $extension],
        ];
    }

    public function deleteProcessed(array $items): void
    {
        foreach ($items as $item) {
            $this->deletePath($item['path'] ?? null);
        }
    }

    public function deletePath(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
