<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateModelsAndFactories extends Command
{
    protected $signature = 'hris:generate-models-and-factories';

    protected $description = 'Generate full Eloquent models and factories for all 29 tables according to SDD.md';

    public function handle(): int
    {
        $this->generateModels();
        $this->generateFactories();
        $this->info('Successfully generated all models and factories.');

        return 0;
    }

    private function generateModels(): void
    {
        $models = [
            'Department' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'parent_id',
        'name',
        'unit_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'unit_id');
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'unit_id');
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'unit_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
PHP,
            'Role' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withPivot(['unit_id'])
            ->withTimestamps();
    }
}
PHP,
            'LeaveType' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'default_quota',
        'is_quota_based',
        'requires_document',
    ];

    protected $casts = [
        'default_quota' => 'integer',
        'is_quota_based' => 'boolean',
        'requires_document' => 'boolean',
    ];

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }
}
PHP,
            'AllowanceType' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllowanceType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'allowance_types';

    protected $fillable = [
        'name',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function allowanceRequests(): HasMany
    {
        return $this->hasMany(AllowanceRequest::class, 'allowance_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
PHP,
            'Applicant' => <<<'PHP'
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
        'nik',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }
}
PHP,
            'User' => <<<'PHP'
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
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
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
PHP,
            'Vacancy' => <<<'PHP'
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
        'position_type',
        'quota',
        'open_date',
        'close_date',
        'status',
    ];

    protected $casts = [
        'quota' => 'integer',
        'open_date' => 'date',
        'close_date' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
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
PHP,
            'Application' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'applications';

    protected $fillable = [
        'vacancy_id',
        'applicant_id',
        'cv_path',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(SelectionStage::class, 'application_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'application_id');
    }
}
PHP,
            'SelectionStage' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectionStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'selection_stages';

    protected $fillable = [
        'application_id',
        'stage_name',
        'score',
        'status',
        'scheduled_date',
        'assessed_by',
        'assessed_at',
        'notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'scheduled_date' => 'date',
        'assessed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
PHP,
            'Employee' => <<<'PHP'
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
PHP,
            'EmployeeAssignment' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_assignments';

    protected $fillable = [
        'employee_id',
        'unit_id',
        'position_title',
        'assignment_type',
        'start_date',
        'end_date',
        'sk_number',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
    }

    public function scopeUnitScope(Builder $query, ?int $unitId): Builder
    {
        return $unitId ? $query->where('unit_id', $unitId) : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
PHP,
            'UserRole' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'unit_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
    }
}
PHP,
            'LeaveBalance' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_balances';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'quota_amount',
        'used_amount',
        'remaining_amount',
        'version',
    ];

    protected $casts = [
        'year' => 'integer',
        'quota_amount' => 'integer',
        'used_amount' => 'integer',
        'remaining_amount' => 'integer',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
PHP,
            'LeaveRequest' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approver_id',
        'submitted_at',
        'decided_at',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'diajukan');
    }
}
PHP,
            'OvertimeRequest' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'overtime_requests';

    protected $fillable = [
        'employee_id',
        'overtime_date',
        'start_time',
        'end_time',
        'duration_hours',
        'reason',
        'status',
        'approver_id',
        'submitted_at',
        'decided_at',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'duration_hours' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'diajukan');
    }
}
PHP,
            'WorkSchedule' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_schedules';

    protected $fillable = [
        'name',
        'schedule_type',
        'effective_start',
        'effective_end',
        'is_active',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'is_active' => 'boolean',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(WorkScheduleDay::class, 'schedule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
PHP,
            'WorkScheduleDay' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkScheduleDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_schedule_days';

    protected $fillable = [
        'schedule_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'is_work_day',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_work_day' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'schedule_id');
    }
}
PHP,
            'AcademicRankHistory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicRankHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_rank_histories';

    protected $fillable = [
        'employee_id',
        'rank_name',
        'credit_points',
        'is_accelerated',
        'status',
        'submitted_at',
        'approver_id',
        'decided_at',
        'effective_date',
        'sk_number',
        'sk_date',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'credit_points' => 'decimal:2',
        'is_accelerated' => 'boolean',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'effective_date' => 'date',
        'sk_date' => 'date',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
PHP,
            'StaffRankHistory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffRankHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_rank_histories';

    protected $fillable = [
        'employee_id',
        'rank_level',
        'status',
        'submitted_at',
        'approver_id',
        'decided_at',
        'effective_date',
        'sk_number',
        'sk_date',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'effective_date' => 'date',
        'sk_date' => 'date',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
PHP,
            'StudyLeave' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'study_leaves';

    protected $fillable = [
        'employee_id',
        'degree_level',
        'institution_name',
        'major',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'sk_number',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
PHP,
            'Certification' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'certifications';

    protected $fillable = [
        'employee_id',
        'cert_type',
        'cert_number',
        'issued_by',
        'issued_date',
        'expiry_date',
        'certificate_path',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
PHP,
            'Retirement' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'retirements';

    protected $fillable = [
        'employee_id',
        'pension_type',
        'proposed_date',
        'effective_date',
        'status',
        'sk_number',
        'sk_date',
        'reason',
        'processed_by',
        'version',
    ];

    protected $casts = [
        'proposed_date' => 'date',
        'effective_date' => 'date',
        'sk_date' => 'date',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function training(): HasOne
    {
        return $this->hasOne(Training::class, 'retirement_id');
    }
}
PHP,
            'Training' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trainings';

    protected $fillable = [
        'employee_id',
        'retirement_id',
        'title',
        'provider',
        'start_date',
        'end_date',
        'hours',
        'status',
        'certificate_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hours' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function retirement(): BelongsTo
    {
        return $this->belongsTo(Retirement::class, 'retirement_id');
    }
}
PHP,
            'WorkloadReport' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkloadReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'workload_reports';

    protected $fillable = [
        'employee_id',
        'academic_year',
        'semester',
        'teaching_credit',
        'research_credit',
        'service_credit',
        'support_credit',
        'status',
        'validator_id',
        'validated_at',
        'version',
        'is_current',
        'revision_reason',
    ];

    protected $casts = [
        'teaching_credit' => 'decimal:2',
        'research_credit' => 'decimal:2',
        'service_credit' => 'decimal:2',
        'support_credit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'validated_at' => 'datetime',
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validator_id');
    }
}
PHP,
            'PerformanceTarget' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceTarget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'performance_targets';

    protected $fillable = [
        'employee_id',
        'period_label',
        'status',
        'appraiser_id',
        'submitted_at',
        'approved_at',
        'final_score',
        'evaluation_summary',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'final_score' => 'decimal:2',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function appraiser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'appraiser_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PerformanceItem::class, 'target_id');
    }
}
PHP,
            'PerformanceItem' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'performance_items';

    protected $fillable = [
        'target_id',
        'activity_name',
        'target_quantity',
        'actual_quantity',
        'target_quality',
        'actual_quality',
        'score',
        'weight',
    ];

    protected $casts = [
        'target_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'target_quality' => 'decimal:2',
        'actual_quality' => 'decimal:2',
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(PerformanceTarget::class, 'target_id');
    }
}
PHP,
            'BpjsMembership' => <<<'PHP'
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
        return strlen($num) > 4 ? str_repeat('*', strlen($num) - 4) . substr($num, -4) : $num;
    }
}
PHP,
            'AllowanceRequest' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllowanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'allowance_requests';

    protected $fillable = [
        'employee_id',
        'allowance_type_id',
        'period_label',
        'amount',
        'status',
        'approver_id',
        'submitted_at',
        'decided_at',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class, 'allowance_type_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
PHP,
            'ActivityLog' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'old_status',
        'new_status',
        'reason',
        'details',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
PHP,
        ];

        foreach ($models as $name => $code) {
            file_put_contents(app_path("Models/{$name}.php"), $code);
        }
    }

    private function generateFactories(): void
    {
        $factories = [
            'DepartmentFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake('id_ID')->unique()->words(2, true) . ' BSDM',
            'unit_type' => fake()->randomElement(['biro', 'bidang', 'urusan', 'unit']),
            'is_active' => true,
        ];
    }
}
PHP,
            'RoleFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'admin_bsdm', 'kepala_unit', 'pegawai', 'asesor_bkd', 'tim_rekrutmen',
                'pimpinan_universitas', 'staf_layanan', 'verifikator'
            ]),
            'description' => fake('id_ID')->sentence(),
        ];
    }
}
PHP,
            'LeaveTypeFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Cuti Tahunan', 'Cuti Melahirkan', 'Cuti Sakit', 'Cuti Alasan Penting', 'Cuti Besar']),
            'default_quota' => 12,
            'is_quota_based' => true,
            'requires_document' => false,
        ];
    }
}
PHP,
            'AllowanceTypeFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AllowanceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllowanceTypeFactory extends Factory
{
    protected $model = AllowanceType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Tunjangan Jabatan', 'Tunjangan Fungsional', 'Tunjangan Kehadiran', 'Tunjangan Kinerja']),
            'category' => fake()->randomElement(['fungsional', 'struktural', 'umum']),
            'is_active' => true,
        ];
    }
}
PHP,
            'ApplicantFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'full_name' => fake('id_ID')->name(),
            'email' => fake('id_ID')->unique()->safeEmail(),
            'phone' => fake('id_ID')->phoneNumber(),
            'nik' => fake('id_ID')->unique()->numerify('3404############'),
        ];
    }
}
PHP,
            'UserFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'employee_id' => null,
            'username' => fake()->unique()->userName(),
            'password_hash' => Hash::make('password'),
            'is_active' => true,
            'last_login_at' => now(),
        ];
    }
}
PHP,
            'VacancyFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    public function definition(): array
    {
        return [
            'unit_id' => Department::factory(),
            'title' => 'Rekrutmen Dosen / Tenaga Kependidikan ' . fake()->jobTitle(),
            'position_type' => fake()->randomElement(['dosen', 'tendik']),
            'quota' => fake()->numberBetween(1, 5),
            'open_date' => now()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(),
            'status' => 'buka',
        ];
    }
}
PHP,
            'ApplicationFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'applicant_id' => Applicant::factory(),
            'cv_path' => 'resumes/' . fake()->uuid() . '.pdf',
            'status' => 'terkirim',
            'applied_at' => now(),
        ];
    }
}
PHP,
            'SelectionStageFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\SelectionStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SelectionStageFactory extends Factory
{
    protected $model = SelectionStage::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'stage_name' => fake()->randomElement(['seleksi_berkas', 'tes_kompetensi', 'wawancara', 'aipt_microteaching']),
            'score' => fake()->randomFloat(2, 60, 100),
            'status' => 'lulus',
            'scheduled_date' => now()->toDateString(),
            'assessed_by' => User::factory(),
            'assessed_at' => now(),
            'notes' => fake('id_ID')->sentence(),
        ];
    }
}
PHP,
            'EmployeeFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        return [
            'nip' => fake()->unique()->numerify('1990########00#'),
            'nidn' => fake()->unique()->numerify('05########'),
            'full_name' => fake('id_ID')->name($gender === 'L' ? 'male' : 'female'),
            'category' => fake()->randomElement(['dosen', 'tendik']),
            'gender' => $gender,
            'birth_place' => fake('id_ID')->city(),
            'birth_date' => fake()->date('Y-m-d', '-25 years'),
            'email' => fake('id_ID')->unique()->safeEmail(),
            'phone' => fake('id_ID')->phoneNumber(),
            'address' => fake('id_ID')->address(),
            'highest_education' => fake()->randomElement(['S1', 'S2', 'S3']),
            'unit_id' => Department::factory(),
            'supervisor_id' => null,
            'application_id' => null,
            'employment_status' => 'aktif',
            'join_date' => fake()->date('Y-m-d', '-5 years'),
            'end_date' => null,
        ];
    }
}
PHP,
            'EmployeeAssignmentFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeAssignmentFactory extends Factory
{
    protected $model = EmployeeAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'unit_id' => Department::factory(),
            'position_title' => fake('id_ID')->jobTitle(),
            'assignment_type' => 'struktural',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'sk_number' => 'SK/' . fake()->numerify('###/UAD/2026'),
            'is_active' => true,
        ];
    }
}
PHP,
            'UserRoleFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRoleFactory extends Factory
{
    protected $model = UserRole::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
            'unit_id' => null,
        ];
    }
}
PHP,
            'LeaveBalanceFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => (int) date('Y'),
            'quota_amount' => 12,
            'used_amount' => 0,
            'remaining_amount' => 12,
            'version' => 1,
        ];
    }
}
PHP,
            'LeaveRequestFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'total_days' => 3,
            'reason' => fake('id_ID')->sentence(),
            'status' => 'diajukan',
            'approver_id' => Employee::factory(),
            'submitted_at' => now(),
            'decided_at' => null,
            'decision_note' => null,
            'version' => 1,
        ];
    }
}
PHP,
            'OvertimeRequestFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeRequestFactory extends Factory
{
    protected $model = OvertimeRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'duration_hours' => 3.00,
            'reason' => fake('id_ID')->sentence(),
            'status' => 'diajukan',
            'approver_id' => Employee::factory(),
            'submitted_at' => now(),
            'decided_at' => null,
            'decision_note' => null,
            'version' => 1,
        ];
    }
}
PHP,
            'WorkScheduleFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        return [
            'name' => 'Jadwal Reguler BSDM 5 Hari',
            'schedule_type' => 'reguler',
            'effective_start' => now()->startOfYear()->toDateString(),
            'effective_end' => null,
            'is_active' => true,
        ];
    }
}
PHP,
            'WorkScheduleDayFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleDayFactory extends Factory
{
    protected $model = WorkScheduleDay::class;

    public function definition(): array
    {
        return [
            'schedule_id' => WorkSchedule::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'is_work_day' => true,
        ];
    }
}
PHP,
            'AcademicRankHistoryFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicRankHistory;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicRankHistoryFactory extends Factory
{
    protected $model = AcademicRankHistory::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'rank_name' => fake()->randomElement(['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar']),
            'credit_points' => fake()->randomFloat(2, 100, 850),
            'is_accelerated' => false,
            'status' => 'disetujui',
            'submitted_at' => now()->subMonth(),
            'approver_id' => Employee::factory(),
            'decided_at' => now(),
            'effective_date' => now()->toDateString(),
            'sk_number' => 'SK-JAFUNG/' . fake()->numerify('###/2026'),
            'sk_date' => now()->toDateString(),
            'decision_note' => fake('id_ID')->sentence(),
            'version' => 1,
        ];
    }
}
PHP,
            'StaffRankHistoryFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\StaffRankHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffRankHistoryFactory extends Factory
{
    protected $model = StaffRankHistory::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'rank_level' => fake()->randomElement(['Pengatur Muda / II A', 'Pengatur / II C', 'Penata Muda / III A', 'Penata / III C']),
            'status' => 'disetujui',
            'submitted_at' => now()->subMonth(),
            'approver_id' => Employee::factory(),
            'decided_at' => now(),
            'effective_date' => now()->toDateString(),
            'sk_number' => 'SK-PANGKAT/' . fake()->numerify('###/2026'),
            'sk_date' => now()->toDateString(),
            'decision_note' => fake('id_ID')->sentence(),
            'version' => 1,
        ];
    }
}
PHP,
            'StudyLeaveFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\StudyLeave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyLeaveFactory extends Factory
{
    protected $model = StudyLeave::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'degree_level' => fake()->randomElement(['S2', 'S3', 'Postdoc']),
            'institution_name' => fake()->randomElement(['Universitas Gadjah Mada', 'Institut Teknologi Bandung', 'Universitas Ahmad Dahlan', 'Monash University']),
            'major' => 'Informatika',
            'start_date' => now()->toDateString(),
            'expected_end_date' => now()->addYears(3)->toDateString(),
            'actual_end_date' => null,
            'status' => 'aktif',
            'sk_number' => 'SK-TB/' . fake()->numerify('###/2026'),
            'created_by' => User::factory(),
        ];
    }
}
PHP,
            'CertificationFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certification;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'cert_type' => fake()->randomElement(['Serdos', 'Keahlian BNSP', 'Profesi']),
            'cert_number' => 'CERT-' . fake()->numerify('##########'),
            'issued_by' => 'Kementerian Pendidikan Kebudayaan Riset dan Teknologi',
            'issued_date' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addYears(4)->toDateString(),
            'certificate_path' => 'certs/' . fake()->uuid() . '.pdf',
        ];
    }
}
PHP,
            'RetirementFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Retirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetirementFactory extends Factory
{
    protected $model = Retirement::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'pension_type' => fake()->randomElement(['bup', 'dini', 'meninggal']),
            'proposed_date' => now()->toDateString(),
            'effective_date' => now()->addMonths(6)->toDateString(),
            'status' => 'diajukan',
            'sk_number' => null,
            'sk_date' => null,
            'reason' => fake('id_ID')->sentence(),
            'processed_by' => User::factory(),
            'version' => 1,
        ];
    }
}
PHP,
            'TrainingFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'retirement_id' => null,
            'title' => 'Pelatihan ' . fake()->words(3, true),
            'provider' => 'Lembaga Pengembangan Profesi',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
            'hours' => 32,
            'status' => 'selesai',
            'certificate_path' => 'trainings/' . fake()->uuid() . '.pdf',
        ];
    }
}
PHP,
            'WorkloadReportFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\WorkloadReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkloadReportFactory extends Factory
{
    protected $model = WorkloadReport::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'academic_year' => '2025/2026',
            'semester' => 'gasal',
            'teaching_credit' => 6.00,
            'research_credit' => 3.00,
            'service_credit' => 2.00,
            'support_credit' => 1.00,
            'status' => 'disetujui',
            'validator_id' => Employee::factory(),
            'validated_at' => now(),
            'version' => 1,
            'is_current' => true,
            'revision_reason' => null,
        ];
    }
}
PHP,
            'PerformanceTargetFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceTargetFactory extends Factory
{
    protected $model = PerformanceTarget::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'period_label' => '2026',
            'status' => 'disetujui',
            'appraiser_id' => Employee::factory(),
            'submitted_at' => now()->subMonth(),
            'approved_at' => now(),
            'final_score' => 88.50,
            'evaluation_summary' => fake('id_ID')->paragraph(),
            'version' => 1,
        ];
    }
}
PHP,
            'PerformanceItemFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PerformanceItem;
use App\Models\PerformanceTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceItemFactory extends Factory
{
    protected $model = PerformanceItem::class;

    public function definition(): array
    {
        return [
            'target_id' => PerformanceTarget::factory(),
            'activity_name' => fake('id_ID')->sentence(4),
            'target_quantity' => 10,
            'actual_quantity' => 10,
            'target_quality' => 100.00,
            'actual_quality' => 90.00,
            'score' => 90.00,
            'weight' => 25.00,
        ];
    }
}
PHP,
            'BpjsMembershipFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BpjsMembership;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class BpjsMembershipFactory extends Factory
{
    protected $model = BpjsMembership::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'bpjs_type' => fake()->randomElement(['kesehatan', 'ketenagakerjaan']),
            'membership_number' => fake()->unique()->numerify('0001########'),
            'registered_date' => now()->subYears(2)->toDateString(),
            'status' => 'aktif',
            'end_date' => null,
            'archived_at' => null,
        ];
    }
}
PHP,
            'AllowanceRequestFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllowanceRequestFactory extends Factory
{
    protected $model = AllowanceRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'allowance_type_id' => AllowanceType::factory(),
            'period_label' => date('Y-m'),
            'amount' => 1500000.00,
            'status' => 'diajukan',
            'approver_id' => Employee::factory(),
            'submitted_at' => now(),
            'decided_at' => null,
            'decision_note' => null,
            'version' => 1,
        ];
    }
}
PHP,
            'ActivityLogFactory' => <<<'PHP'
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => 'CREATE',
            'entity_type' => 'Employee',
            'entity_id' => fake()->numberBetween(1, 100),
            'old_status' => null,
            'new_status' => 'aktif',
            'reason' => 'Pencatatan awal',
            'details' => ['source' => 'web_portal'],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
PHP,
        ];

        foreach ($factories as $name => $code) {
            file_put_contents(database_path("factories/{$name}.php"), $code);
        }
    }
}
