<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebRequestsFlowTest extends TestCase
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
        $response = $this->get('/requests');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_requests_page(): void
    {
        $unit = Department::first() ?? Department::factory()->create(['name' => 'Dept Test', 'unit_type' => 'fakultas', 'is_active' => true]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
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

        $response = $this->get('/requests');
        $response->assertStatus(200);
    }

    public function test_web_login_flow(): void
    {
        $user = User::factory()->create([
            'username' => 'webtest.'.uniqid('', true),
            'password_hash' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
