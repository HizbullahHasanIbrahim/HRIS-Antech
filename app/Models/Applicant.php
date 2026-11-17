<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'applicants';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'birth_date',
        'highest_education',
        'cv_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }
}
