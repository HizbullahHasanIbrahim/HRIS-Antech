<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Attendance\LeaveRequestService;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaveRequestServiceTest extends TestCase
{
    protected LeaveRequestService $service;

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

        $this->service = new LeaveRequestService(new ActivityLogService);
    }

    protected function tearDown(): void
    {
        // Clean up test data - order matters for FK constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('activity_logs')->truncate();
        DB::table('leave_requests')->delete();
        DB::table('leave_balances')->delete();
        DB::table('users')->where('username', 'like', 'test_lr_%')->delete();
        DB::table('employees')->where('nip', 'like', 'TEST_%')->delete();
        DB::table('leave_types')->where('name', 'like', 'Test%')->delete();
        DB::table('departments')->where('name', 'like', 'Test%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    protected function createTestDepartment(): Department
    {
        return Department::create([
            'name' => 'Test Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);
    }

    protected function createTestEmployee(int $unitId, ?int $supervisorId = null): Employee
    {
        return Employee::create([
            'nip' => 'TEST_'.uniqid(),
            'full_name' => 'Test Employee '.uniqid(),
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1990-01-01',
            'email' => 'test_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $unitId,
            'supervisor_id' => $supervisorId,
            'employment_status' => 'aktif',
            'join_date' => '2020-01-01',
        ]);
    }

    protected function createTestUser(int $employeeId): User
    {
        return User::create([
            'employee_id' => $employeeId,
            'username' => 'test_lr_'.uniqid(),
            'password_hash' => 'hashed',
            'is_active' => true,
        ]);
    }

    public function test_create_leave_request_success(): void
    {
        $dept = $this->createTestDepartment();
        $employee = $this->createTestEmployee($dept->id);
        $approver = $this->createTestEmployee($dept->id);
        $this->createTestUser($employee->id);

        $leaveType = LeaveType::create([
            'type' => 'T'.uniqid(),
            'name' => 'Test Cuti '.uniqid(),
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

        $result = $this->service->createRequest(
            $employee,
            $leaveType->id,
            now()->addDays(5),
            now()->addDays(7),
            'Alasan cuti test',
            $approver->id
        );

        $this->assertNotNull($result->id);
        $this->assertEquals(LeaveRequestStatus::SUBMITTED->value, $result->status);
        $this->assertEquals($employee->id, $result->employee_id);
        $this->assertEquals($approver->id, $result->approver_id);
        $this->assertEquals(1, $result->version);
    }

    public function test_self_approval_rejected(): void
    {
        $dept = $this->createTestDepartment();
        $employee = $this->createTestEmployee($dept->id);

        $leaveType = LeaveType::create([
            'type' => 'T'.uniqid(),
            'name' => 'Test Cuti SA '.uniqid(),
            'annual_quota' => 12,
            'applies_to' => 'SEMUA',
            'is_active' => true,
        ]);

        $this->expectException(UnauthorizedActionException::class);
        $this->expectExceptionMessage('Employee cannot approve their own leave request.');

        $this->service->createRequest(
            $employee,
            $leaveType->id,
            now()->addDays(5),
            now()->addDays(7),
            'Self approval test',
            $employee->id // same as employee
        );
    }

    public function test_approve_decreases_balance(): void
    {
        $dept = $this->createTestDepartment();
        $employee = $this->createTestEmployee($dept->id);
        $approver = $this->createTestEmployee($dept->id);
        $this->createTestUser($employee->id);
        $this->createTestUser($approver->id);

        $leaveType = LeaveType::create([
            'type' => 'T'.uniqid(),
            'name' => 'Test Cuti AB '.uniqid(),
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

        $request = $this->service->createRequest(
            $employee,
            $leaveType->id,
            now()->addDays(5),
            now()->addDays(7),
            'Test approve',
            $approver->id
        );

        $result = $this->service->approveRequest($request, $approver);

        $this->assertEquals(LeaveRequestStatus::APPROVED->value, $result->status);
        $this->assertEquals(2, $result->version);

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();

        $this->assertGreaterThan(0, $balance->used_amount);
        $this->assertLessThan(12, $balance->remaining_amount);
    }

    public function test_reject_requires_decision_note(): void
    {
        $dept = $this->createTestDepartment();
        $employee = $this->createTestEmployee($dept->id);
        $approver = $this->createTestEmployee($dept->id);
        $this->createTestUser($employee->id);
        $this->createTestUser($approver->id);

        $leaveType = LeaveType::create([
            'type' => 'T'.uniqid(),
            'name' => 'Test Cuti RJ '.uniqid(),
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

        $request = $this->service->createRequest(
            $employee,
            $leaveType->id,
            now()->addDays(5),
            now()->addDays(7),
            'Test reject',
            $approver->id
        );

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('Decision note is required');

        $this->service->rejectRequest($request, $approver, '');
    }

    public function test_cancel_only_submitted_requests(): void
    {
        $dept = $this->createTestDepartment();
        $employee = $this->createTestEmployee($dept->id);
        $approver = $this->createTestEmployee($dept->id);
        $this->createTestUser($employee->id);
        $this->createTestUser($approver->id);

        $leaveType = LeaveType::create([
            'type' => 'T'.uniqid(),
            'name' => 'Test Cuti CN '.uniqid(),
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

        $request = $this->service->createRequest(
            $employee,
            $leaveType->id,
            now()->addDays(5),
            now()->addDays(7),
            'Test cancel',
            $approver->id
        );

        // Approve first
        $approved = $this->service->approveRequest($request, $approver);

        // Then try to cancel
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('Only submitted leave requests can be cancelled.');

        $this->service->cancelRequest($approved);
    }
}
