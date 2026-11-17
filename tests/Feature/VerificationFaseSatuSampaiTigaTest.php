<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerificationFaseSatuSampaiTigaTest extends TestCase
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
        DB::table('users')->where('username', 'like', 'verif_%')->delete();
        DB::table('employees')->where('nip', 'like', 'VERIF_%')->delete();
        DB::table('departments')->where('name', 'like', 'Verif Unit%')->delete();
        DB::table('applicants')->where('email', 'like', 'verif_%@example.com')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    private function createAdminContext(): array
    {
        $dept = Department::create([
            'name' => 'Verif Unit '.uniqid(),
            'unit_type' => 'unit',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'nip' => 'VERIF_'.uniqid(),
            'full_name' => 'Verif Admin '.uniqid(),
            'category' => 'tendik',
            'gender' => 'L',
            'birth_place' => 'Yogyakarta',
            'birth_date' => '1990-01-01',
            'email' => 'verif_'.uniqid().'@example.com',
            'highest_education' => 'S1',
            'unit_id' => $dept->id,
            'employment_status' => 'aktif',
            'join_date' => '2020-01-01',
        ]);

        $username = 'verif_'.uniqid();
        $password = 'Secret123!';
        $token = 'verif-tok-'.uniqid();

        $user = User::create([
            'employee_id' => $employee->id,
            'username' => $username,
            'password_hash' => bcrypt($password),
            'api_token' => $token,
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'Admin BSDM')->first();
        if (! $adminRole) {
            $adminRole = Role::create(['name' => 'Admin BSDM']);
        }
        $user->userRoles()->create(['role_id' => $adminRole->id, 'unit_id' => $dept->id]);

        return compact('dept', 'employee', 'user', 'username', 'password', 'token');
    }

    public function test_fase_1_auth_flow(): void
    {
        $ctx = $this->createAdminContext();

        // 1. Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'username' => $ctx['username'],
            'password' => $ctx['password'],
        ]);
        $loginRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $bearer = $loginRes->json('data.token');

        // 2. Me
        $meRes = $this->withHeader('Authorization', 'Bearer '.$bearer)
            ->getJson('/api/v1/auth/me');
        $meRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['username' => $ctx['username']],
            ]);

        // 3. Refresh
        $refreshRes = $this->withHeader('Authorization', 'Bearer '.$bearer)
            ->postJson('/api/v1/auth/refresh');
        $refreshRes->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'token_type']]);

        // Use the refreshed token
        $bearer = $refreshRes->json('data.token');

        // 4. Logout
        $logoutRes = $this->withHeader('Authorization', 'Bearer '.$bearer)
            ->postJson('/api/v1/auth/logout');
        $logoutRes->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_fase_1_file_upload_cv_and_certification(): void
    {
        $ctx = $this->createAdminContext();

        $applicant = Applicant::create([
            'full_name' => 'Verif Applicant',
            'email' => 'verif_'.uniqid().'@example.com',
            'phone' => '08123456789',
            'birth_date' => '1998-05-20',
            'highest_education' => 'S1',
        ]);

        $fileCv = UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf');

        // Public upload CV
        $cvRes = $this->postJson('/api/v1/upload/cv', [
            'applicant_id' => $applicant->id,
            'file' => $fileCv,
        ]);
        $cvRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotNull($applicant->fresh()->cv_path);

        // Protected upload Certification
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'username' => $ctx['username'],
            'password' => $ctx['password'],
        ]);
        $token = $loginRes->json('data.token');

        $fileCert = UploadedFile::fake()->create('sertifikat.pdf', 300, 'application/pdf');
        $certRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/upload/certification', [
                'employee_id' => $ctx['employee']->id,
                'cert_type' => 'Pendidik',
                'cert_number' => 'CERT-VERIF-'.uniqid(),
                'submitted_date' => now()->toDateString(),
                'file' => $fileCert,
            ]);
        $certRes->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_fase_1_dashboard_stats(): void
    {
        $ctx = $this->createAdminContext();

        $res = $this->withHeader('Authorization', 'Bearer '.$ctx['token'])
            ->getJson('/api/v1/dashboard/stats');

        $res->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'sdm' => ['total_pegawai', 'aktif', 'pensiun', 'by_category', 'by_unit'],
                    'cuti' => ['pending', 'approved_this_month', 'total_this_month'],
                    'lembur' => ['pending', 'approved_this_month', 'total_hours_this_month'],
                    'pensiun' => ['pending_proposals', 'upcoming_12_months', 'upcoming_list'],
                ],
            ]);
    }

    public function test_fase_2_all_6_csv_exports(): void
    {
        $ctx = $this->createAdminContext();

        $endpoints = [
            '/api/v1/reports/export/sdm-summary',
            '/api/v1/reports/export/cuti-lembur',
            '/api/v1/reports/export/karir-dosen',
            '/api/v1/reports/export/pangkat-tendik',
            '/api/v1/reports/export/bkd-lkd',
            '/api/v1/reports/export/skp',
        ];

        foreach ($endpoints as $url) {
            $res = $this->withHeader('Authorization', 'Bearer '.$ctx['token'])
                ->get($url);

            $res->assertStatus(200);
            $this->assertEquals('text/csv; charset=UTF-8', $res->headers->get('content-type'));
        }
    }

    public function test_fase_2_notification_endpoints(): void
    {
        $ctx = $this->createAdminContext();

        // Index
        $listRes = $this->withHeader('Authorization', 'Bearer '.$ctx['token'])
            ->getJson('/api/v1/notifications');
        $listRes->assertStatus(200)->assertJson(['success' => true]);

        // Unread
        $unreadRes = $this->withHeader('Authorization', 'Bearer '.$ctx['token'])
            ->getJson('/api/v1/notifications/unread');
        $unreadRes->assertStatus(200)->assertJson(['success' => true]);

        // Mark all as read
        $markAllRes = $this->withHeader('Authorization', 'Bearer '.$ctx['token'])
            ->postJson('/api/v1/notifications/read-all');
        $markAllRes->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_fase_3_openapi_and_swagger_files_accessible(): void
    {
        $openapiPath = public_path('docs/openapi.json');
        $this->assertFileExists($openapiPath);

        $json = json_decode(file_get_contents($openapiPath), true);
        $this->assertIsArray($json);
        $this->assertEquals('3.0.3', $json['openapi']);
        $this->assertArrayHasKey('paths', $json);
        $this->assertGreaterThan(50, count($json['paths']));

        $indexPath = public_path('docs/index.html');
        $this->assertFileExists($indexPath);
        $this->assertStringContainsString('swagger-ui', file_get_contents($indexPath));
    }
}
