<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'nip',
        'nidn',
        'full_name',
        'category',
        'gender',
        'birth_place',
        'birth_date',
        'email',
        'phone',
        'address',
        'highest_education',
        'unit_id',
        'supervisor_id',
        'application_id',
        'employment_status',
        'join_date',
        'end_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'end_date' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'employee_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'employee_id');
    }

    public function academicRankHistories(): HasMany
    {
        return $this->hasMany(AcademicRankHistory::class, 'employee_id');
    }

    public function staffRankHistories(): HasMany
    {
        return $this->hasMany(StaffRankHistory::class, 'employee_id');
    }

    public function studyLeaves(): HasMany
    {
        return $this->hasMany(StudyLeave::class, 'employee_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class, 'employee_id');
    }

    public function retirement(): HasOne
    {
        return $this->hasOne(Retirement::class, 'employee_id');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'employee_id');
    }

    public function workloadReports(): HasMany
    {
        return $this->hasMany(WorkloadReport::class, 'employee_id');
    }

    public function performanceTargets(): HasMany
    {
        return $this->hasMany(PerformanceTarget::class, 'employee_id');
    }

    public function bpjsMemberships(): HasMany
    {
        return $this->hasMany(BpjsMembership::class, 'employee_id');
    }

    public function allowanceRequests(): HasMany
    {
        return $this->hasMany(AllowanceRequest::class, 'employee_id');
    }

    public function scopeUnitScope(Builder $query, ?int $unitId): Builder
    {
        return $unitId ? $query->where('unit_id', $unitId) : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', 'aktif');
    }
}