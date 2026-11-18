<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebOrganizationFlowTest extends TestCase
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

    public function test_authenticated_user_can_view_organization_page(): void
    {
        $unit = Department::factory()->create(['name' => 'Dept Test', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id, 'employment_status' => 'aktif']);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'is_active' => true,
            'password_hash' => bcrypt('password123'),
        ]);

        // Login via web form to establish session
        $loginResponse = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $response = $this->get('/organization');
        $response->assertStatus(200);
    }
}
