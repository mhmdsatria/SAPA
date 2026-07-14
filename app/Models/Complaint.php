<?php

namespace App\Models;

use App\Traits\HasLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Complaint extends Model
{
    use HasFactory;
    use HasLocation;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const CATEGORY_JALAN = 'jalan';
    public const CATEGORY_KEBERSIHAN = 'kebersihan';
    public const CATEGORY_PENERANGAN = 'penerangan';
    public const CATEGORY_LAINNYA = 'lainnya';

    protected $fillable = [
        'user_id', 'region_id', 'moderated_by', 'category_id', 'title', 'slug',
        'description', 'category', 'latitude', 'longitude', 'location', 'gps_accuracy',
        'location_source', 'address_text', 'geocoded_address', 'address_is_edited',
        'landmark', 'image_path', 'image_original_name', 'image_mime', 'image_size',
        'image_hash', 'image_taken_at', 'image_age_days', 'exif_is_stale', 'status',
        'is_anonymous', 'is_duplicate_flag', 'duplicate_of_id', 'rejected_reason',
        'approved_at', 'rejected_at', 'last_edited_at', 'edit_count', 'upvotes_count',
        'comments_count',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'gps_accuracy' => 'float',
            'image_taken_at' => 'datetime',
            'exif_is_stale' => 'boolean',
            'is_anonymous' => 'boolean',
            'is_duplicate_flag' => 'boolean',
            'address_is_edited' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'last_edited_at' => 'datetime',
            'edit_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ComplaintMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(Upvote::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForAdmin(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdminDaerah()) {
            return $query->whereIn('region_id', $user->regionalAssignments()->select('region_id'));
        }

        return $query->whereRaw('1 = 0');
    }

    public function isEditableByReporter(): bool
    {
        return $this->status !== self::STATUS_APPROVED;
    }

    public function getImageUrlAttribute(): string
    {
        $image = $this->relationLoaded('media')
            ? $this->media->firstWhere('media_type', 'image')
            : $this->media()->where('media_type', 'image')->first();

        if ($image) {
            return $image->url;
        }

        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : asset('icons/icon-512.png');
    }

    public function getPrimaryMediaAttribute(): ?ComplaintMedia
    {
        return $this->relationLoaded('media') ? $this->media->first() : $this->media()->first();
    }

    public function getReporterNameAttribute(): string
    {
        return $this->is_anonymous ? 'Pelapor anonim' : ($this->user?->name ?? 'Warga');
    }

    public function getCategoryLabelAttribute(): string
    {
        if ($this->categoryRecord) {
            return $this->categoryRecord->name;
        }

        return match ($this->category) {
            self::CATEGORY_JALAN => 'Jalan Raya',
            self::CATEGORY_KEBERSIHAN => 'Kebersihan',
            self::CATEGORY_PENERANGAN => 'Penerangan',
            default => 'Lainnya',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return $this->categoryRecord?->color
            ?? config('gis.category_colors.'.$this->category, '#2563eb');
    }
}
