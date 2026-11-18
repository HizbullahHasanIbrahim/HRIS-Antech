<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaveRequestFrontendTest extends TestCase
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('leave_requests')->truncate();
        DB::table('leave_balances')->truncate();
        DB::table('leave_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function test_leave_requests_index_returns_json_for_api(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['employee_id' => $employee->id]);
        $leaveType = LeaveType::factory()->create(['applies_to' => 'SEMUA']);

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'diajukan',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->getJson('/api/v1/leave-requests');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_leave_requests_can_create(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['employee_id' => $employee->id]);
        $leaveType = LeaveType::factory()->create(['applies_to' => 'SEMUA']);
        LeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
            'quota_days' => 20,
            'used_days' => 0,
        ]);
        $approver = Employee::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->postJson('/api/v1/leave-requests', [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDays(5)->format('Y-m-d'),
                'end_date' => now()->addDays(7)->format('Y-m-d'),
                'reason' => 'Test leave request',
                'approver_id' => $approver->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'status', 'start_date', 'end_date']]);
    }
}
