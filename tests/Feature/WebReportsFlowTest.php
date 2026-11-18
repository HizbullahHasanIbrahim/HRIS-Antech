<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebReportsFlowTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_reports_page(): void
    {
        $unit = Department::first() ?? Department::factory()->create(['name' => 'Dept Test', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin BSDM']);
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'unit_id' => null,
        ]);

        $loginResponse = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $response = $this->get('/reports');
        $response->assertStatus(200);
    }

    public function test_authenticated_staff_without_role_cannot_view_reports(): void
    {
        $unit = Department::first() ?? Department::factory()->create(['name' => 'Dept Test', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        $loginResponse = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $response = $this->get('/reports');
        $response->assertStatus(403);
    }

    public function test_reports_page_has_csv_export_links(): void
    {
        $unit = Department::first() ?? Department::factory()->create(['name' => 'Dept Test', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        $role = Role::firstOrCreate(['name' => 'Admin BSDM']);
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'unit_id' => null,
        ]);

        $loginResponse = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $response = $this->get('/reports');
        $response->assertStatus(200);

        // Verify export URLs are passed correctly via Inertia props
        $response->assertInertia(fn ($page) => $page
            ->where('exportUrls.sdm', '/api/v1/reports/export/sdm-summary')
        );
    }
}
