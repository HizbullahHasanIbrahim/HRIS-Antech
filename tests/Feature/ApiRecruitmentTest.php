<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiRecruitmentTest extends TestCase
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
        Storage::fake('private');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('selection_stages')->delete();
        DB::table('applications')->delete();
        DB::table('vacancies')->delete();
        DB::table('applicants')->delete();
        DB::table('users')->where('username', 'like', 'rec_test_%')->delete();
        DB::table('employees')->where('nip', 'like', 'REC_TEST_%')->delete();
        DB::table('departments')->where('name', 'like', 'Rec Unit%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    public function test_create_vacancy_and_apply(): void
    {
        $dept = Department::create([
            'name' => 'Rec Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'REC_TEST_'.uniqid(),
            'full_name' => 'Rec Admin',
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1990-01-01',
            'email' => 'rec_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2020-01-01',
        ]);

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => 'rec_test_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'api_token' => 'rec-token-12345',
            'is_active' => true,
        ]);

        // Assign Staff Rekrutmen role for vacancy creation authorization
        $staffRekrutmenRole = Role::where('name', 'Staff Rekrutmen')->first();
        if (! $staffRekrutmenRole) {
            $staffRekrutmenRole = Role::create(['name' => 'Staff Rekrutmen']);
        }
        $user->userRoles()->create([
            'role_id' => $staffRekrutmenRole->id,
            'unit_id' => $dept->id,
        ]);

        // 1. Create Vacancy
        $vacancyRes = $this->withHeader('Authorization', 'Bearer rec-token-12345')
            ->postJson('/api/v1/vacancies', [
                'unit_id' => $dept->id,
                'title' => 'Dosen Teknik Informatika',
                'category' => 'dosen',
                'description' => 'Lowongan dosen tetap',
                'min_qualification' => 'S2 Teknik Informatika',
                'quota' => 2,
                'open_date' => now()->toDateString(),
                'close_date' => now()->addMonth()->toDateString(),
            ]);

        $vacancyRes->assertStatus(201);
        $vacancyId = $vacancyRes->json('data.id');

        // 2. Submit Applicant
        $file = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $applicantRes = $this->withHeader('Authorization', 'Bearer rec-token-12345')
            ->post('/api/v1/applicants', [
                'full_name' => 'Budi Santoso',
                'email' => 'budi_'.uniqid().'@example.com',
                'phone' => '08123456789',
                'birth_date' => '1995-05-15',
                'highest_education' => 'S2',
                'cv_file' => $file,
            ]);

        $applicantRes->assertStatus(201);
        $applicantId = $applicantRes->json('data.id');

        // 3. Submit Application
        $appRes = $this->withHeader('Authorization', 'Bearer rec-token-12345')
            ->postJson('/api/v1/applications', [
                'vacancy_id' => $vacancyId,
                'applicant_id' => $applicantId,
            ]);

        $appRes->assertStatus(201);
        $this->assertDatabaseHas('applications', [
            'vacancy_id' => $vacancyId,
            'applicant_id' => $applicantId,
            'status' => 'diajukan',
        ]);
    }
}
