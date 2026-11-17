<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BpjsMembership extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bpjs_memberships';

    protected $fillable = [
        'employee_id',
        'bpjs_type',
        'membership_number',
        'registered_date',
        'status',
        'end_date',
        'archived_at',
    ];

    protected $casts = [
        'registered_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getMaskedNumberAttribute(): string
    {
        $num = (string) $this->membership_number;

        return strlen($num) > 4 ? str_repeat('*', strlen($num) - 4).substr($num, -4) : $num;
    }
}
