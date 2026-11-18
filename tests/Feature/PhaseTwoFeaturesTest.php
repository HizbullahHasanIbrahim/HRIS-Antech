<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseTwoFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
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
        DB::table('users')->where('username', 'like', 'p2_test_%')->delete();
        DB::table('employees')->where('nip', 'like', 'P2_TEST_%')->delete();
        DB::table('departments')->where('name', 'like', 'P2 Unit%')->delete();
        DB::table('applicants')->where('email', 'like', 'p2_%@example.com')->delete();
        DB::table('vacancies')->where('title', 'like', 'P2 Vacancy%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    public function test_export_sdm_summary_csv(): void
    {
        $dept = Department::create([
            'name' => 'P2 Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'P2_TEST_'.uniqid(),
            'full_name' => 'P2 Employee Test',
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1990-01-01',
            'email' => 'p2_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2020-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'p2_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'p2-token-12345',
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'Admin BSDM')->first();
        $user->userRoles()->create(['role_id' => $adminRole->id, 'unit_id' => $dept->id]);

        $response = $this->withHeader('Authorization', 'Bearer p2-token-12345')
            ->get('/api/v1/reports/export/sdm-summary');

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsStringIgnoringCase('unit', $content);
    }

    public function test_search_employee(): void
    {
        $dept = Department::create([
            'name' => 'P2 Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $uniqueName = 'Ahmad Dahlan '.uniqid();
        $employee = Employee::create([
            'nip' => 'P2_TEST_'.uniqid(),
            'full_name' => $uniqueName,
            'category' => 'dosen',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1980-01-01',
            'email' => 'p2_'.uniqid().'@example.com',
            'highest_education' => 'S3',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2010-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'p2_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'p2-token-search-12345',
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'Admin BSDM')->first();
        $user->userRoles()->create(['role_id' => $adminRole->id, 'unit_id' => $dept->id]);

        $response = $this->withHeader('Authorization', 'Bearer p2-token-search-12345')
            ->getJson('/api/v1/search/employees?q=Ahmad');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_search_vacancies(): void
    {
        $dept = Department::create([
            'name' => 'P2 Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'P2_TEST_'.uniqid(),
            'full_name' => 'Recruiter Test',
            'category' => 'tendik',
            'gender' => 'P',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1995-01-01',
            'email' => 'p2_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2021-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'p2_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'p2-token-vac-12345',
            'is_active' => true,
        ]);

        $staffRekrutmenRole = Role::where('name', 'Staff Rekrutmen')->first();
        $user->userRoles()->create(['role_id' => $staffRekrutmenRole->id, 'unit_id' => $dept->id]);

        Vacancy::create([
            'unit_id' => $dept->id,
            'title' => 'P2 Vacancy Staff IT',
            'category' => 'tendik',
            'description' => 'Rekrutmen Programmer',
            'min_qualification' => 'S1 Informatika',
            'quota' => 1,
            'open_date' => now()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(),
            'status' => 'buka',
            'created_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer p2-token-vac-12345')
            ->getJson('/api/v1/search/vacancies?q=Programmer');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('data'));
    }
}
