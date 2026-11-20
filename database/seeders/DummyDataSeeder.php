<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicRankHistory;
use App\Models\ActivityLog;
use App\Models\AllowanceRequest;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\BpjsMembership;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\PerformanceItem;
use App\Models\PerformanceTarget;
use App\Models\Retirement;
use App\Models\Role;
use App\Models\StaffRankHistory;
use App\Models\StudyLeave;
use App\Models\Training;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkloadReport;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Generate dummy employees (20 dosen + 15 tendik)
        $this->command?->info('Generating dummy employees...');
        $dosenEmployees = Employee::factory()
            ->count(20)
            ->state(['category' => 'dosen'])
            ->create();

        $tendikEmployees = Employee::factory()
            ->count(15)
            ->state(['category' => 'tendik'])
            ->create();

        $allEmployees = $dosenEmployees->concat($tendikEmployees);

        // Assign supervisors randomly for some employees
        $this->command?->info('Assigning supervisors...');
        $allEmployees->each(function ($emp) use ($allEmployees) {
            if ($emp->supervisor_id === null && fake()->boolean(40)) {
                $emp->supervisor_id = $allEmployees->where('id', '!=', $emp->id)->random()->id;
                $emp->save();
            }
        });

        // Create users for employees
        $this->command?->info('Creating users for employees...');
        $allEmployees->each(function ($emp) {
            User::factory()->create([
                'employee_id' => $emp->id,
                'username' => strtolower(str_replace(' ', '.', $emp->full_name)),
            ]);
        });

        // Assign roles to users
        $this->command?->info('Assigning roles...');
        $adminBsdmRole = Role::where('name', 'Admin BSDM')->first();
        $dosenRole = Role::where('name', 'Dosen')->first();
        $tendikRole = Role::where('name', 'Tendik')->first();
        $kepalaBiroRole = Role::where('name', 'Kepala Biro')->first();
        $staffHrRole = Role::where('name', 'Staff HR')->first();
        $welfareAdminRole = Role::where('name', 'Welfare Admin')->first();

        // Admin user
        User::where('username', 'admin.bsdm')->first()?->roles()->syncWithoutDetaching([
            $adminBsdmRole->id => ['unit_id' => null],
        ]);

        // Dosen users
        $dosenEmployees->each(function ($emp) use ($dosenRole) {
            $user = $emp->user;
            if ($user) {
                $user->roles()->syncWithoutDetaching([
                    $dosenRole->id => ['unit_id' => $emp->unit_id],
                ]);
            }
        });

        // Tendik users
        $tendikEmployees->each(function ($emp) use ($tendikRole) {
            $user = $emp->user;
            if ($user) {
                $user->roles()->syncWithoutDetaching([
                    $tendikRole->id => ['unit_id' => $emp->unit_id],
                ]);
            }
        });

        // Additional role assignments for testing
        User::whereHas('employee', fn ($q) => $q->where('category', 'tendik'))->limit(3)->each(function ($user) use ($kepalaBiroRole) {
            $user->roles()->syncWithoutDetaching([
                $kepalaBiroRole->id => ['unit_id' => $user->employee?->unit_id],
            ]);
        });

        User::whereHas('employee', fn ($q) => $q->where('category', 'tendik'))->limit(2)->each(function ($user) use ($staffHrRole) {
            $user->roles()->syncWithoutDetaching([
                $staffHrRole->id => ['unit_id' => $user->employee?->unit_id],
            ]);
        });

        User::whereHas('employee', fn ($q) => $q->where('category', 'tendik'))->limit(1)->each(function ($user) use ($welfareAdminRole) {
            $user->roles()->syncWithoutDetaching([
                $welfareAdminRole->id => ['unit_id' => $user->employee?->unit_id],
            ]);
        });

        // Create Leave Balances for current year
        $this->command?->info('Creating leave balances...');
        $leaveTypes = LeaveType::all();
        $currentYear = (int) date('Y');
        $allEmployees->each(function ($emp) use ($leaveTypes, $currentYear) {
            $leaveTypes->each(function ($lt) use ($emp, $currentYear) {
                LeaveBalance::factory()->create([
                    'employee_id' => $emp->id,
                    'leave_type_id' => $lt->id,
                    'year' => $currentYear,
                    'quota_days' => $lt->annual_quota ?? 12,
                    'used_days' => fake()->numberBetween(0, ($lt->annual_quota ?? 12) / 2),
                ]);
            });
        });

        // Create Leave Requests (some approved, some pending)
        $this->command?->info('Creating leave requests...');
        for ($i = 0; $i < 25; $i++) {
            $emp = $allEmployees->random();
            $lt = $leaveTypes->random();
            $status = fake()->randomElement(['diajukan', 'disetujui', 'ditolak']);
            $approver = $allEmployees->where('id', '!=', $emp->id)->random();
            $startDate = now()->addDays(fake()->numberBetween(1, 30))->toDateString();
            $endDate = now()->addDays(fake()->numberBetween(5, 45))->toDateString();

            LeaveRequest::factory()->create([
                'employee_id' => $emp->id,
                'leave_type_id' => $lt->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => max(1, (int) round((strtotime($endDate) - strtotime($startDate)) / 86400) + 1),
                'status' => $status,
                'approver_id' => $approver->id,
                'decided_at' => $status !== 'diajukan' ? now() : null,
                'decision_note' => $status === 'ditolak' ? 'Alasan penolakan' : null,
            ]);
        }

        // Create Overtime Requests
        $this->command?->info('Creating overtime requests...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $allEmployees->random();
            $approver = $allEmployees->where('id', '!=', $emp->id)->random();
            $status = fake()->randomElement(['diajukan', 'disetujui', 'ditolak']);
            OvertimeRequest::factory()->create([
                'employee_id' => $emp->id,
                'work_date' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
                'start_time' => '17:00:00',
                'end_time' => fake()->randomElement(['18:00:00', '19:00:00', '20:00:00', '21:00:00']),
                'total_hours' => fake()->randomFloat(2, 1, 4),
                'status' => $status,
                'approver_id' => $approver->id,
                'decided_at' => $status !== 'diajukan' ? now() : null,
                'decision_note' => $status === 'ditolak' ? 'Alasan penolakan lembur' : null,
            ]);
        }

        // Create Allowance Requests
        $this->command?->info('Creating allowance requests...');
        $allowanceTypes = DB::table('allowance_types')->pluck('id');
        for ($i = 0; $i < 10; $i++) {
            $emp = $allEmployees->random();
            $approver = $allEmployees->where('id', '!=', $emp->id)->random();
            $status = fake()->randomElement(['diajukan', 'disetujui', 'ditolak']);
            AllowanceRequest::factory()->create([
                'employee_id' => $emp->id,
                'allowance_type_id' => $allowanceTypes->random(),
                'period_label' => date('Y-m', strtotime("-$i months")),
                'amount' => fake()->randomFloat(2, 500000, 3000000),
                'status' => $status,
                'approver_id' => $approver->id,
                'decided_at' => $status !== 'diajukan' ? now() : null,
                'decision_note' => $status === 'ditolak' ? 'Alasan penolakan tunjangan' : null,
            ]);
        }

        // Create BPJS Memberships
        $this->command?->info('Creating BPJS memberships...');
        $allEmployees->each(function ($emp) {
            BpjsMembership::factory()->create([
                'employee_id' => $emp->id,
                'bpjs_type' => 'kesehatan',
            ]);
            BpjsMembership::factory()->create([
                'employee_id' => $emp->id,
                'bpjs_type' => 'ketenagakerjaan',
            ]);
        });

        // Create Certifications
        $this->command?->info('Creating certifications...');
        for ($i = 0; $i < 15; $i++) {
            $emp = $dosenEmployees->random();
            Certification::factory()->create([
                'employee_id' => $emp->id,
                'status' => fake()->randomElement(['diajukan', 'disetujui', 'final']),
                'result_date' => fake()->boolean(60) ? now()->subMonths(fake()->numberBetween(1, 12))->toDateString() : null,
            ]);
        }

        // Create Study Leave (only for dosen)
        $this->command?->info('Creating study leave applications...');
        for ($i = 0; $i < 5; $i++) {
            $emp = $dosenEmployees->random();
            StudyLeave::factory()->create([
                'employee_id' => $emp->id,
                'degree_level' => fake()->randomElement(['S2', 'S3']),
                'status' => fake()->randomElement(['direncanakan', 'berjalan', 'selesai', 'dibatalkan']),
                'actual_end_date' => fake()->boolean(30) ? now()->subMonths(fake()->numberBetween(1, 12))->toDateString() : null,
            ]);
        }

        // Create Trainings
        $this->command?->info('Creating trainings...');
        for ($i = 0; $i < 20; $i++) {
            $emp = $allEmployees->random();
            Training::factory()->create([
                'employee_id' => $emp->id,
                'training_type' => fake()->randomElement(['prajabatan', 'teknis', 'kepemimpinan', 'fungsional']),
                'status' => fake()->randomElement(['dijadwalkan', 'berjalan', 'selesai', 'dibatalkan']),
                'retirement_id' => null,
            ]);
        }

        // Create Retirements (for employees near retirement age)
        $this->command?->info('Creating retirement proposals...');
        $nearRetirement = $allEmployees->filter(fn ($e) => $e->birth_date <= now()->subYears(55))->take(5);
        foreach ($nearRetirement as $emp) {
            Retirement::factory()->create([
                'employee_id' => $emp->id,
                'retirement_age' => fake()->randomElement([58, 60, 65]),
                'status' => fake()->randomElement(['diusulkan', 'diproses']),
                'planned_date' => now()->addMonths(fake()->numberBetween(1, 12))->toDateString(),
            ]);
        }

        // Create Academic Rank Histories (for dosen)
        $this->command?->info('Creating academic rank histories...');
        $ranks = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];
        foreach ($dosenEmployees as $emp) {
            $rankCount = fake()->numberBetween(1, 3);
            $currentRank = fake()->randomElement($ranks);
            $creditPoints = fake()->randomFloat(2, 100, 850);

            for ($i = 0; $i < $rankCount; $i++) {
                AcademicRankHistory::factory()->create([
                    'employee_id' => $emp->id,
                    'rank_name' => $currentRank,
                    'credit_points' => $creditPoints,
                    'status' => 'disetujui',
                    'effective_date' => now()->subYears($rankCount - $i)->toDateString(),
                    'sk_number' => 'SK-JAFUNG/'.fake()->numerify('###/'.(2020 + $i)),
                ]);
            }
        }

        // Create Staff Rank Histories (for tendik)
        $this->command?->info('Creating staff rank histories...');
        $staffRanks = ['Pengatur Muda / II A', 'Pengatur / II C', 'Penata Muda / III A', 'Penata / III C'];
        foreach ($tendikEmployees as $emp) {
            $rankCount = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $rankCount; $i++) {
                StaffRankHistory::factory()->create([
                    'employee_id' => $emp->id,
                    'rank_grade' => $staffRanks[$i] ?? fake()->randomElement($staffRanks),
                    'status' => 'disetujui',
                    'effective_date' => now()->subYears($rankCount - $i)->toDateString(),
                    'sk_number' => 'SK-PANGKAT/'.fake()->numerify('###/'.(2020 + $i)),
                ]);
            }
        }

        // Create Workload Reports (for dosen)
        $this->command?->info('Creating workload reports...');
        foreach ($dosenEmployees as $emp) {
            $years = ['2024/2025', '2025/2026'];
            $semesters = ['gasal', 'genap'];
            foreach ($years as $year) {
                foreach ($semesters as $sem) {
                    WorkloadReport::factory()->create([
                        'employee_id' => $emp->id,
                        'academic_year' => $year,
                        'semester' => $sem,
                        'teaching_credit' => fake()->randomFloat(2, 3, 12),
                        'research_credit' => fake()->randomFloat(2, 1, 6),
                        'service_credit' => fake()->randomFloat(2, 1, 4),
                        'support_credit' => fake()->randomFloat(2, 0.5, 3),
                        'status' => fake()->randomElement(['diajukan', 'disetujui', 'final']),
                        'is_current' => ($year === '2025/2026' && $sem === 'gasal'),
                        'validator_id' => $dosenEmployees->where('id', '!=', $emp->id)->random()->id,
                        'validated_at' => now(),
                    ]);
                }
            }
        }

        // Create Performance Targets (for all employees)
        $this->command?->info('Creating performance targets...');
        foreach ($allEmployees as $emp) {
            $target = PerformanceTarget::factory()->create([
                'employee_id' => $emp->id,
                'period_label' => '2026',
                'period_start' => '2026-01-01',
                'period_end' => '2026-12-31',
                'status' => fake()->randomElement(['draf', 'diajukan', 'disetujui']),
                'appraiser_id' => $allEmployees->where('id', '!=', $emp->id)->random()->id,
                'final_score' => fake()->boolean(40) ? fake()->randomFloat(2, 70, 100) : null,
                'rating' => fake()->boolean(40) ? fake()->randomElement(['A', 'B', 'C', 'D']) : null,
                'approved_at' => fake()->boolean(40) ? now() : null,
            ]);

            // Add performance items for each target
            for ($i = 0; $i < fake()->numberBetween(3, 6); $i++) {
                PerformanceItem::factory()->create([
                    'target_id' => $target->id,
                    'description' => fake('id_ID')->sentence(4),
                    'unit' => fake()->randomElement(['orang', 'benda', 'program', 'acara', 'modul']),
                    'target_value' => fake()->randomFloat(2, 10, 100),
                    'realization_value' => fake()->boolean(60) ? fake()->randomFloat(2, 5, 100) : null,
                    'weight' => fake()->randomFloat(2, 10, 30),
                    'score' => fake()->boolean(60) ? fake()->randomFloat(2, 60, 100) : null,
                ]);
            }
        }

        // Create Work Schedules
        $this->command?->info('Creating work schedules...');
        $departments = Department::all();
        foreach ($departments as $dept) {
            $schedule = WorkSchedule::factory()->create([
                'unit_id' => $dept->id,
                'category' => fake()->randomElement(['dosen', 'tendik']),
                'valid_from' => now()->startOfYear()->toDateString(),
                'is_published' => true,
                'published_at' => now(),
            ]);

            // Add days for the schedule
            foreach (range(1, 5) as $dayOfWeek) {
                WorkScheduleDay::factory()->create([
                    'schedule_id' => $schedule->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                ]);
            }
        }

        // Create Vacancies
        $this->command?->info('Creating vacancies...');
        $users = User::all();
        for ($i = 0; $i < 5; $i++) {
            $vacancy = Vacancy::factory()->create([
                'unit_id' => $departments->random()->id,
                'category' => fake()->randomElement(['dosen', 'tendik']),
                'status' => fake()->randomElement(['draf', 'diajukan', 'publikasi']),
                'created_by' => $users->random()->id,
            ]);

            // Add applicants
            for ($j = 0; $j < fake()->numberBetween(1, 5); $j++) {
                $applicant = Applicant::factory()->create();
                Application::factory()->create([
                    'vacancy_id' => $vacancy->id,
                    'applicant_id' => $applicant->id,
                    'status' => fake()->randomElement(['diajukan', 'lolos', 'diterima', 'ditolak']),
                ]);
            }
        }

        // Create Employee Assignments
        $this->command?->info('Creating employee assignments...');
        foreach ($allEmployees as $emp) {
            EmployeeAssignment::factory()->create([
                'employee_id' => $emp->id,
                'unit_id' => $emp->unit_id,
                'category' => $emp->category === 'dosen' ? 'fungsional' : 'pelaksana',
                'effective_from' => $emp->join_date,
                'effective_until' => null,
            ]);
        }

        // Create Activity Logs
        $this->command?->info('Creating activity logs...');
        for ($i = 0; $i < 50; $i++) {
            ActivityLog::factory()->create([
                'actor_id' => $users->random()->id,
                'action' => fake()->randomElement(['CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT', 'LOGIN', 'EXPORT']),
                'entity_type' => fake()->randomElement(['Employee', 'LeaveRequest', 'OvertimeRequest', 'Certification', 'Training', 'Retirement']),
                'entity_id' => fake()->numberBetween(1, 100),
                'old_status' => fake()->boolean(50) ? fake()->randomElement(['diajukan', 'draf', 'direncanakan']) : null,
                'new_status' => fake()->randomElement(['disetujui', 'ditolak', 'final', 'berjalan', 'selesai']),
                'reason' => fake('id_ID')->sentence(),
                'ip_address' => fake()->ipv4(),
                'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
            ]);
        }

        $this->command?->info('Dummy data generation completed!');
    }
}
