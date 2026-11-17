<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vacancies';

    protected $fillable = [
        'unit_id',
        'title',
        'category',
        'description',
        'min_qualification',
        'quota',
        'open_date',
        'close_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'quota' => 'integer',
        'open_date' => 'date',
        'close_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'vacancy_id');
    }

    public function scopeUnitScope(Builder $query, ?int $unitId): Builder
    {
        return $unitId ? $query->where('unit_id', $unitId) : $query;
    }
}
