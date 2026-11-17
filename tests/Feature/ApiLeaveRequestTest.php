<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiLeaveRequestTest extends TestCase
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

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('activity_logs')->truncate();
        DB::table('leave_requests')->delete();
        DB::table('leave_balances')->delete();
        DB::table('users')->where('username', 'like', 'api_test_%')->delete();
        DB::table('employees')->where('nip', 'like', 'API_TEST_%')->delete();
        DB::table('leave_types')->where('type', 'like', 'API_%')->delete();
        DB::table('departments')->where('name', 'like', 'API Unit%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    public function test_api_create_leave_request_success(): void
    {
        $dept = Department::create([
            'name' => 'API Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'API_TEST_'.uniqid(),
            'full_name' => 'API Employee',
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1990-01-01',
            'email' => 'api_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2020-01-01',
        ]);

        $approver = Employee::create([
            'nip' => 'API_TEST_APP_'.uniqid(),
            'full_name' => 'API Approver',
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1985-01-01',
            'email' => 'apiapp_'.uniqid().'@example.com',
            'highest_education' => 'S2',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2015-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'api_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'test-token-12345',
            'is_active' => true,
        ]);

        $leaveType = LeaveType::create([
            'type' => 'API_CUTI',
            'name' => 'API Cuti Tahunan',
            'annual_quota' => 12,
            'applies_to' => 'SEMUA',
            'is_active' => true,
        ]);

        LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
            'quota_days' => 12,
            'used_days' => 0,
            'version' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test-token-12345')
            ->postJson('/api/v1/leave-requests', [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
                'reason' => 'Liburan keluarga',
                'approver_id' => $approver->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'reason' => 'Liburan keluarga',
        ]);
    }
}
