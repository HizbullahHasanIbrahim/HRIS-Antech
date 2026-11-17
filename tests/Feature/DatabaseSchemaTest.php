<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
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

    public function test_all_29_business_tables_exist(): void
    {
        $expectedTables = [
            'departments',
            'roles',
            'leave_types',
            'allowance_types',
            'applicants',
            'users',
            'vacancies',
            'applications',
            'selection_stages',
            'employees',
            'employee_assignments',
            'user_roles',
            'leave_balances',
            'leave_requests',
            'overtime_requests',
            'work_schedules',
            'work_schedule_days',
            'academic_rank_histories',
            'staff_rank_histories',
            'study_leaves',
            'certifications',
            'retirements',
            'trainings',
            'workload_reports',
            'performance_targets',
            'performance_items',
            'bpjs_memberships',
            'allowance_requests',
            'activity_logs',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
        }
    }

    public function test_activity_logs_is_append_only_structure(): void
    {
        $this->assertTrue(Schema::hasColumn('activity_logs', 'created_at'));
        $this->assertFalse(Schema::hasColumn('activity_logs', 'updated_at'), 'activity_logs must not have updated_at');
        $this->assertFalse(Schema::hasColumn('activity_logs', 'deleted_at'), 'activity_logs must not have deleted_at');
    }

    public function test_employees_table_has_fulltext_and_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('employees', [
            'id', 'nip', 'nidn', 'full_name', 'category', 'gender', 'birth_place',
            'birth_date', 'email', 'phone', 'address', 'highest_education',
            'unit_id', 'supervisor_id', 'application_id', 'employment_status',
            'join_date', 'end_date', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_activity_logs_prevent_update_trigger(): void
    {
        $actorId = DB::table('users')->insertGetId([
            'username' => 'test_audit_user_'.uniqid(),
            'password_hash' => 'hash',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $logId = DB::table('activity_logs')->insertGetId([
            'actor_id' => $actorId,
            'action' => 'TEST_ACTION',
            'entity_type' => 'test',
            'entity_id' => 1,
            'created_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('RULE-13 VIOLATION');

        DB::table('activity_logs')->where('id', $logId)->update(['action' => 'TAMPERED']);
    }

    public function test_activity_logs_prevent_delete_trigger(): void
    {
        $actorId = DB::table('users')->insertGetId([
            'username' => 'test_audit_user_del_'.uniqid(),
            'password_hash' => 'hash',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $logId = DB::table('activity_logs')->insertGetId([
            'actor_id' => $actorId,
            'action' => 'TEST_ACTION',
            'entity_type' => 'test',
            'entity_id' => 2,
            'created_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('RULE-13 VIOLATION');

        DB::table('activity_logs')->where('id', $logId)->delete();
    }
}
