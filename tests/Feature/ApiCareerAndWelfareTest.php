<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiCareerAndWelfareTest extends TestCase
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
        DB::table('academic_rank_histories')->delete();
        DB::table('staff_rank_histories')->delete();
        DB::table('bpjs_memberships')->delete();
        DB::table('users')->where('username', 'like', 'cw_test_%')->delete();
        DB::table('employees')->where('nip', 'like', 'CW_TEST_%')->delete();
        DB::table('departments')->where('name', 'like', 'CW Unit%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    public function test_academic_rank_submission_and_approval(): void
    {
        $dept = Department::create([
            'name' => 'CW Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $dosen = Employee::create([
            'nip' => 'CW_TEST_'.uniqid(),
            'full_name' => 'Dosen Test',
            'category' => 'dosen',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1985-01-01',
            'email' => 'cw_'.uniqid().'@example.com',
            'highest_education' => 'S3',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2015-01-01',
        ]);

        $approver = Employee::create([
            'nip' => 'CW_TEST_APP_'.uniqid(),
            'full_name' => 'Approver Test',
            'category' => 'dosen',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1975-01-01',
            'email' => 'cwapp_'.uniqid().'@example.com',
            'highest_education' => 'S3',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2005-01-01',
        ]);

        $user = User::create([
            'employee_id' => $dosen->id,
            'username' => 'cw_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'cw-token-12345',
            'is_active' => true,
        ]);

        $approverUser = User::create([
            'employee_id' => $approver->id,
            'username' => 'cw_test_app_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'cw-app-token-12345',
            'is_active' => true,
        ]);

        // 1. Submit Academic Rank
        $res = $this->withHeader('Authorization', 'Bearer cw-token-12345')
            ->postJson('/api/v1/academic-ranks', [
                'rank_name' => 'Lektor Kepala',
                'credit_points' => 400.0,
                'is_accelerated' => false,
                'approver_id' => $approver->id,
            ]);

        $res->assertStatus(201);
        $rankId = $res->json('data.id');

        // 2. Approve Academic Rank
        $approveRes = $this->withHeader('Authorization', 'Bearer cw-app-token-12345')
            ->postJson("/api/v1/academic-ranks/{$rankId}");

        $approveRes->assertStatus(200);
        $this->assertDatabaseHas('academic_rank_histories', [
            'id' => $rankId,
            'status' => 'disetujui',
        ]);
    }

    public function test_bpjs_membership_registration_and_masking(): void
    {
        $dept = Department::create([
            'name' => 'CW Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'CW_TEST_BPJS_'.uniqid(),
            'full_name' => 'BPJS Test Employee',
            'category' => 'tendik',
            'gender' => 'P',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1992-01-01',
            'email' => 'bpjs_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2018-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'cw_test_bpjs_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'bpjs-token-12345',
            'is_active' => true,
        ]);

        // Register BPJS
        $res = $this->withHeader('Authorization', 'Bearer bpjs-token-12345')
            ->postJson('/api/v1/bpjs', [
                'bpjs_type' => 'kesehatan',
                'membership_number' => '0001234567890',
                'registered_date' => '2023-01-01',
            ]);

        $res->assertStatus(201);
        $this->assertEquals('********7890', $res->json('data.membership_number'));

        // Query BPJS list and verify masked
        $listRes = $this->withHeader('Authorization', 'Bearer bpjs-token-12345')
            ->getJson('/api/v1/bpjs');

        $listRes->assertStatus(200);
        $this->assertEquals('********7890', $listRes->json('data.0.membership_number'));
    }
}
