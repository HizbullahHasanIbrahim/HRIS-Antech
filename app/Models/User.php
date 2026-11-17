<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'employee_id',
        'username',
        'password_hash',
        'api_token',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'api_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot(['unit_id'])
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasRole(string $roleName, ?int $unitId = null): bool
    {
        return $this->userRoles()->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        })->where(function ($q) use ($unitId) {
            $q->whereNull('unit_id');
            if ($unitId !== null) {
                $q->orWhere('unit_id', $unitId);
            }
        })->exists();
    }
}
