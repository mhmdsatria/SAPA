<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\Upvote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ComplaintService
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly GisService $gisService,
        private readonly CensorService $censorService,
    ) {
    }

    public function create(User $user, array $data, array $mediaFiles): Complaint
    {
        $processedMedia = $this->mediaService->processMany($mediaFiles);

        try {
            return DB::transaction(function () use ($user, $data, $processedMedia): Complaint {
                $category = Category::query()->active()->findOrFail((int) $data['category_id']);
                $latitude = (float) $data['latitude'];
                $longitude = (float) $data['longitude'];
                $duplicate = $this->gisService->detectDuplicate($latitude, $longitude);
                $region = $this->gisService->assignRegionStub($latitude, $longitude);
                $geocoded = trim((string) ($data['geocoded_address'] ?? ''))
                    ?: $this->gisService->reverseGeocode($latitude, $longitude);
                $address = trim((string) ($data['address_text'] ?? '')) ?: $geocoded;
                $description = $this->censorService->censor((string) $data['description']);
                $titleBase = Str::of($description)->squish()->limit(62, '')->toString();
                $title = ucfirst($titleBase ?: 'Laporan warga kategori '.$category->name);

                $complaint = new Complaint([
                    'user_id' => $user->id,
                    'region_id' => $region?->id,
                    'category_id' => $category->id,
                    'category' => $category->slug,
                    'title' => $title,
                    'slug' => Str::slug($title).'-'.Str::lower(Str::random(7)),
                    'description' => $description,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'gps_accuracy' => $data['gps_accuracy'] ?? null,
                    'location_source' => 'browser_geolocation',
                    'address_text' => $address,
                    'geocoded_address' => $geocoded,
                    'address_is_edited' => $address !== $geocoded,
                    'landmark' => $this->censorService->censor($data['landmark'] ?? null) ?: null,
                    'status' => Complaint::STATUS_PENDING,
                    'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                    'is_duplicate_flag' => $duplicate !== null,
                    'duplicate_of_id' => $duplicate?->id,
                ]);
                $complaint->location = Complaint::pointExpression($latitude, $longitude);
                $this->applyLegacyImage($complaint, $processedMedia);
                $complaint->save();
                $complaint->media()->createMany($processedMedia);
                $this->syncMediaFlags($complaint);

                return $complaint->fresh(['user', 'region', 'categoryRecord', 'media']);
            });
        } catch (Throwable $exception) {
            $this->mediaService->deleteProcessed($processedMedia);
            throw $exception;
        }
    }

    public function updateReporterComplaint(
        Complaint $complaint,
        array $data,
        array $newMediaFiles = [],
        array $removeMediaIds = [],
    ): Complaint {
        abort_unless($complaint->isEditableByReporter(), 403, 'Laporan yang disetujui tidak dapat diubah.');
        $processedMedia = $newMediaFiles !== [] ? $this->mediaService->processMany($newMediaFiles) : [];
        $pathsToDelete = [];

        try {
            $updated = DB::transaction(function () use (
                $complaint,
                $data,
                $processedMedia,
                $removeMediaIds,
                &$pathsToDelete,
            ): Complaint {
                $category = Category::query()->findOrFail((int) $data['category_id']);
                $latitude = (float) $data['latitude'];
                $longitude = (float) $data['longitude'];
                $region = $this->gisService->assignRegionStub($latitude, $longitude);
                $geocoded = trim((string) ($data['geocoded_address'] ?? ''))
                    ?: $this->gisService->reverseGeocode($latitude, $longitude);
                $address = trim((string) ($data['address_text'] ?? '')) ?: $geocoded;
                $duplicate = $this->gisService->detectDuplicate($latitude, $longitude, $complaint->id);

                $requestedIds = array_values(array_unique(array_map('intval', $removeMediaIds)));
                $removable = $complaint->media()->whereIn('id', $requestedIds)->lockForUpdate()->get();
                $remainingCount = $complaint->media()->whereNotIn('id', $removable->pluck('id'))->count()
                    + count($processedMedia);

                abort_if($remainingCount < 1, 422, 'Laporan wajib memiliki minimal satu media.');
                abort_if($remainingCount > MediaService::MAX_FILES, 422, 'Jumlah media melebihi batas.');

                $pathsToDelete = $removable->pluck('path')->filter()->values()->all();
                $complaint->media()->whereIn('id', $removable->pluck('id'))->delete();

                $nextOrder = ((int) $complaint->media()->max('sort_order')) + 1;
                foreach ($processedMedia as $index => $mediaData) {
                    $mediaData['sort_order'] = $nextOrder + $index;
                    $complaint->media()->create($mediaData);
                }

                $description = $this->censorService->censor((string) $data['description']);
                $titleBase = Str::of($description)->squish()->limit(62, '')->toString();
                $complaint->fill([
                    'region_id' => $region?->id,
                    'category_id' => $category->id,
                    'category' => $category->slug,
                    'description' => $description,
                    'title' => ucfirst($titleBase ?: 'Laporan warga kategori '.$category->name),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'gps_accuracy' => $data['gps_accuracy'] ?? null,
                    'location_source' => 'browser_geolocation',
                    'address_text' => $address,
                    'geocoded_address' => $geocoded,
                    'address_is_edited' => $address !== $geocoded,
                    'landmark' => $this->censorService->censor($data['landmark'] ?? null) ?: null,
                    'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                    'is_duplicate_flag' => $duplicate !== null,
                    'duplicate_of_id' => $duplicate?->id,
                    'status' => Complaint::STATUS_PENDING,
                    'moderated_by' => null,
                    'approved_at' => null,
                    'rejected_at' => null,
                    'rejected_reason' => null,
                    'last_edited_at' => now(),
                    'edit_count' => ((int) $complaint->edit_count) + 1,
                ]);
                $complaint->location = Complaint::pointExpression($latitude, $longitude);
                $complaint->save();
                $this->syncLegacyFromMedia($complaint);
                $this->syncMediaFlags($complaint);

                return $complaint->fresh(['categoryRecord', 'region', 'media']);
            });
        } catch (Throwable $exception) {
            $this->mediaService->deleteProcessed($processedMedia);
            throw $exception;
        }

        foreach ($pathsToDelete as $path) {
            $this->mediaService->deletePath($path);
        }

        return $updated;
    }

    public function deleteReporterComplaint(Complaint $complaint): void
    {
        abort_unless($complaint->isEditableByReporter(), 403, 'Laporan yang disetujui tidak dapat dihapus.');

        $paths = $complaint->media()->pluck('path')->filter()->values()->all();
        if ($paths === [] && $complaint->image_path) {
            $paths[] = $complaint->image_path;
        }

        DB::transaction(function () use ($complaint): void {
            $complaint->delete();
        });

        foreach ($paths as $path) {
            $this->mediaService->deletePath($path);
        }
    }

    public function addComment(User $user, Complaint $complaint, string $content): Comment
    {
        abort_unless(
            $complaint->status === Complaint::STATUS_APPROVED,
            422,
            'Komentar hanya tersedia untuk laporan yang disetujui.',
        );

        return DB::transaction(function () use ($user, $complaint, $content): Comment {
            $comment = $complaint->comments()->create([
                'user_id' => $user->id,
                'content' => $this->censorService->censor($content),
            ]);
            $complaint->update([
                'comments_count' => $complaint->comments()->where('is_hidden', false)->count(),
            ]);

            return $comment->load('user');
        });
    }

    public function toggleUpvote(User $user, Complaint $complaint): bool
    {
        abort_unless(
            $complaint->status === Complaint::STATUS_APPROVED,
            422,
            'Upvote hanya tersedia untuk laporan yang disetujui.',
        );

        return DB::transaction(function () use ($user, $complaint): bool {
            $existing = Upvote::query()
                ->where('complaint_id', $complaint->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                $active = false;
            } else {
                Upvote::query()->create([
                    'complaint_id' => $complaint->id,
                    'user_id' => $user->id,
                ]);
                $active = true;
            }

            $complaint->update(['upvotes_count' => $complaint->upvotes()->count()]);

            return $active;
        });
    }

    public function approve(User $admin, Complaint $complaint, ?string $editedDescription = null): Complaint
    {
        return DB::transaction(function () use ($admin, $complaint, $editedDescription): Complaint {
            $values = [
                'status' => Complaint::STATUS_APPROVED,
                'moderated_by' => $admin->id,
                'approved_at' => now(),
                'rejected_at' => null,
                'rejected_reason' => null,
            ];

            if ($editedDescription !== null) {
                $values['description'] = $this->censorService->censor($editedDescription);
            }

            $complaint->update($values);

            return $complaint->fresh(['user', 'region', 'moderator', 'categoryRecord', 'media']);
        });
    }

    public function reject(User $admin, Complaint $complaint, string $reason): Complaint
    {
        $complaint->update([
            'status' => Complaint::STATUS_REJECTED,
            'moderated_by' => $admin->id,
            'rejected_reason' => $this->censorService->censor($reason),
            'rejected_at' => now(),
            'approved_at' => null,
        ]);

        return $complaint->fresh(['user', 'region', 'moderator']);
    }

    public function hideComment(User $admin, Comment $comment, bool $hidden = true): Comment
    {
        $comment->update([
            'is_hidden' => $hidden,
            'hidden_by' => $hidden ? $admin->id : null,
            'hidden_at' => $hidden ? now() : null,
        ]);
        $comment->complaint()->update([
            'comments_count' => $comment->complaint->comments()->where('is_hidden', false)->count(),
        ]);

        return $comment->fresh(['user', 'hiddenBy']);
    }

    private function applyLegacyImage(Complaint $complaint, array $media): void
    {
        $firstImage = collect($media)->firstWhere('media_type', 'image') ?? collect($media)->first();

        if (! $firstImage) {
            return;
        }

        $complaint->fill([
            'image_path' => $firstImage['path'],
            'image_original_name' => $firstImage['original_name'],
            'image_mime' => $firstImage['mime_type'],
            'image_size' => $firstImage['size_bytes'],
            'image_hash' => $firstImage['sha256'],
            'image_taken_at' => $firstImage['taken_at'],
            'image_age_days' => $firstImage['age_days'],
            'exif_is_stale' => $firstImage['is_stale'],
        ]);
    }

    private function syncLegacyFromMedia(Complaint $complaint): void
    {
        $media = $complaint->media()
            ->orderByRaw("FIELD(media_type, 'image', 'video')")
            ->orderBy('sort_order')
            ->first();

        if (! $media) {
            return;
        }

        $complaint->update([
            'image_path' => $media->path,
            'image_original_name' => $media->original_name,
            'image_mime' => $media->mime_type,
            'image_size' => $media->size_bytes,
            'image_hash' => $media->sha256,
            'image_taken_at' => $media->taken_at,
            'image_age_days' => $media->age_days,
        ]);
    }

    private function syncMediaFlags(Complaint $complaint): void
    {
        $stale = $complaint->media()->where('is_stale', true)->exists();
        $oldest = $complaint->media()->whereNotNull('age_days')->max('age_days');

        $complaint->update([
            'exif_is_stale' => $stale,
            'image_age_days' => $oldest,
        ]);
    }
}
