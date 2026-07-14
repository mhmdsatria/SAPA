<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const ROLE_MASYARAKAT = 'masyarakat';
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN_DAERAH = 'admin_daerah';

    protected $fillable = [
        'region_id',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'password',
        'role',
        'provider',
        'provider_id',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(Upvote::class);
    }

    public function regionalAssignments(): HasMany
    {
        return $this->hasMany(AdminDaerahAssignment::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN_DAERAH], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminDaerah(): bool
    {
        return $this->role === self::ROLE_ADMIN_DAERAH;
    }

    public function dashboardRouteName(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'profile';
    }
}
