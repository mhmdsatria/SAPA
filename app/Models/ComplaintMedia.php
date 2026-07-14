<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ComplaintMedia extends Model
{
    use HasFactory;

    protected $table = 'complaint_media';

    protected $fillable = [
        'complaint_id',
        'media_type',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'sha256',
        'taken_at',
        'age_days',
        'is_stale',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'taken_at' => 'datetime',
            'age_days' => 'integer',
            'is_stale' => 'boolean',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }
}
