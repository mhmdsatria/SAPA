<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'level',
        'center_latitude',
        'center_longitude',
        'boundary_geojson',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'center_latitude' => 'float',
            'center_longitude' => 'float',
            'boundary_geojson' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AdminDaerahAssignment::class);
    }
}
