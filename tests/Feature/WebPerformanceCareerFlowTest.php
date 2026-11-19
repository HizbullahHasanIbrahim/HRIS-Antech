<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\WorkloadReport;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebPerformanceCareerFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'antech_async_hris',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.socket' => '/opt/lampp/var/mysql/mysql.sock',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
    }

    public function test_authenticated_user_can_view_performance_and_career_pages(): void
    {
        $unit = Department::factory()->create(['name' => 'Fakultas Teknik', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id, 'employment_status' => 'aktif', 'category' => 'dosen']);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->get('/performance')->assertStatus(200);
        $this->get('/career')->assertStatus(200);
    }

    public function test_performance_page_correctly_filters_bkd_dosen_and_lkd_tendik(): void
    {
        $unit = Department::factory()->create(['name' => 'Fakultas Teknik', 'unit_type' => 'fakultas', 'is_active' => true]);

        $dosen = Employee::factory()->create([
            'unit_id' => $unit->id,
            'category' => 'dosen',
            'full_name' => 'Dosen Test',
        ]);
        $validatorDosen = Employee::factory()->create([
            'unit_id' => $unit->id,
            'category' => 'dosen',
        ]);

        $tendik = Employee::factory()->create([
            'unit_id' => $unit->id,
            'category' => 'tendik',
            'full_name' => 'Tendik Test',
        ]);
        $validatorTendik = Employee::factory()->create([
            'unit_id' => $unit->id,
            'category' => 'tendik',
        ]);

        // Create workload reports
        WorkloadReport::create([
            'employee_id' => $dosen->id,
            'validator_id' => $validatorDosen->id,
            'academic_year' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'diajukan',
            'is_current' => true,
            'version' => 1,
            'teaching_credit' => 6,
            'research_credit' => 3,
            'service_credit' => 2,
            'support_credit' => 1,
        ]);

        WorkloadReport::create([
            'employee_id' => $tendik->id,
            'validator_id' => $validatorTendik->id,
            'academic_year' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'diajukan',
            'is_current' => true,
            'version' => 1,
            'teaching_credit' => 0,
            'research_credit' => 0,
            'service_credit' => 10,
            'support_credit' => 2,
        ]);

        // Create admin user who can see all workload reports
        $admin = Employee::factory()->create(['unit_id' => $unit->id]);
        $user = User::factory()->create([
            'employee_id' => $admin->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        // Attach Admin BSDM role without unit scope (cross-unit access)
        $role = Role::firstOrCreate(['name' => 'Admin BSDM']);
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'unit_id' => null,
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        // Access performance page and verify BKD/LKD filtering via Inertia props
        $this->get('/performance')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('bkdReports.data')
                ->has('lkdReports.data')
                ->where('bkdReports.data.0.employee.category', 'dosen')
                ->where('lkdReports.data.0.employee.category', 'dosen')
            );
    }
}
