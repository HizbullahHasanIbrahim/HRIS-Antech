<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            'Admin BSDM',
            'Kepala Biro',
            'Kepala Bagian',
            'Staff HR',
            'Wakil Rektor',
            'Dosen',
            'Tendik',
            'Welfare Admin',
        ];
        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => 'Peran ' . $roleName]
            );
        }

        // 2. Departments
        $departments = [
            ['name' => 'Biro Sumber Daya Manusia', 'unit_type' => 'biro', 'is_active' => true],
            ['name' => 'Fakultas Teknologi Industri', 'unit_type' => 'fakultas', 'is_active' => true],
            ['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'unit_type' => 'fakultas', 'is_active' => true],
            ['name' => 'Fakultas Ekonomi dan Bisnis', 'unit_type' => 'fakultas', 'is_active' => true],
            ['name' => 'Lembaga Penelitian dan Pengabdian Masyarakat', 'unit_type' => 'lembaga', 'is_active' => true],
            ['name' => 'Program Studi Teknik Informatika', 'unit_type' => 'prodi', 'is_active' => true],
            ['name' => 'Program Studi Pendidikan Matematika', 'unit_type' => 'prodi', 'is_active' => true],
        ];
        $deptModels = [];
        foreach ($departments as $deptData) {
            $dept = Department::firstOrCreate(
                ['name' => $deptData['name']],
                ['unit_type' => $deptData['unit_type'], 'is_active' => $deptData['is_active']]
            );
            $deptModels[$deptData['name']] = $dept;
        }

        // 3. Leave Types
        $leaveTypes = [
            ['type' => 'tahunan', 'name' => 'Cuti Tahunan', 'annual_quota' => 12, 'applies_to' => 'semua', 'is_active' => true],
            ['type' => 'sakit', 'name' => 'Cuti Sakit', 'annual_quota' => 0, 'applies_to' => 'semua', 'is_active' => true],
            ['type' => 'melahirkan', 'name' => 'Cuti Melahirkan', 'annual_quota' => 90, 'applies_to' => 'dosen', 'is_active' => true],
            ['type' => 'besar', 'name' => 'Cuti Besar', 'annual_quota' => 90, 'applies_to' => 'dosen', 'is_active' => true],
            ['type' => 'penting', 'name' => 'Cuti Karena Alasan Penting', 'annual_quota' => 0, 'applies_to' => 'semua', 'is_active' => true],
            ['type' => 'luar_tanggungan', 'name' => 'Cuti di Luar Tanggungan Negara', 'annual_quota' => 0, 'applies_to' => 'semua', 'is_active' => true],
        ];
        foreach ($leaveTypes as $ltData) {
            LeaveType::firstOrCreate(['type' => $ltData['type']], $ltData);
        }

        // 4. Allowance Types
        $allowanceTypes = [
            ['code' => 'JAB', 'name' => 'Tunjangan Jabatan', 'requires_doctorate' => false, 'period_type' => 'bulanan', 'description' => 'Tunjangan jabatan fungsional/struktural', 'is_active' => true],
            ['code' => 'FUNG', 'name' => 'Tunjangan Fungsional', 'requires_doctorate' => false, 'period_type' => 'bulanan', 'description' => 'Tunjangan fungsional dosen', 'is_active' => true],
            ['code' => 'DOC', 'name' => 'Tunjangan Doktorat', 'requires_doctorate' => true, 'period_type' => 'bulanan', 'description' => 'Tunjangan khusus doktor', 'is_active' => true],
            ['code' => 'TRANSP', 'name' => 'Tunjangan Transport', 'requires_doctorate' => false, 'period_type' => 'bulanan', 'description' => 'Tunjangan transport bulanan', 'is_active' => true],
        ];
        foreach ($allowanceTypes as $atData) {
            AllowanceType::firstOrCreate(['code' => $atData['code']], $atData);
        }

        // 5. Demo Users with Employees
        $demoUsers = [
            [
                'username' => 'admin.bsdm',
                'password' => 'password123',
                'role' => 'Admin BSDM',
                'employee' => [
                    'nip' => '198501012010011001',
                    'full_name' => 'Admin BSDM Demo',
                    'category' => 'tendik',
                    'gender' => 'L',
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '1985-01-01',
                    'email' => 'admin.bsdm@uad.ac.id',
                    'highest_education' => 'S2',
                    'unit_id' => $deptModels['Biro Sumber Daya Manusia']->id,
                    'employment_status' => 'aktif',
                    'join_date' => '2010-01-01',
                ],
                'unit_id' => null,
            ],
            [
                'username' => 'kepala.biro',
                'password' => 'password123',
                'role' => 'Kepala Biro',
                'employee' => [
                    'nip' => '198001012005011001',
                    'full_name' => 'Kepala Biro Demo',
                    'category' => 'tendik',
                    'gender' => 'L',
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '1980-01-01',
                    'email' => 'kepala.biro@uad.ac.id',
                    'highest_education' => 'S2',
                    'unit_id' => $deptModels['Biro Sumber Daya Manusia']->id,
                    'employment_status' => 'aktif',
                    'join_date' => '2005-01-01',
                ],
                'unit_id' => $deptModels['Biro Sumber Daya Manusia']->id,
            ],
            [
                'username' => 'kepala.bagian',
                'password' => 'password123',
                'role' => 'Kepala Bagian',
                'employee' => [
                    'nip' => '198201012008011001',
                    'full_name' => 'Kepala Bagian Demo',
                    'category' => 'tendik',
                    'gender' => 'P',
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '1982-01-01',
                    'email' => 'kepala.bagian@uad.ac.id',
                    'highest_education' => 'S1',
                    'unit_id' => $deptModels['Fakultas Teknologi Industri']->id,
                    'employment_status' => 'aktif',
                    'join_date' => '2008-01-01',
                ],
                'unit_id' => $deptModels['Fakultas Teknologi Industri']->id,
            ],
            [
                'username' => 'dosen.demo',
                'password' => 'password123',
                'role' => 'Dosen',
                'employee' => [
                    'nip' => '199001012015011001',
                    'full_name' => 'Dosen Demo',
                    'category' => 'dosen',
                    'gender' => 'L',
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '1990-01-01',
                    'email' => 'dosen.demo@uad.ac.id',
                    'highest_education' => 'S3',
                    'unit_id' => $deptModels['Program Studi Teknik Informatika']->id,
                    'employment_status' => 'aktif',
                    'join_date' => '2015-01-01',
                ],
                'unit_id' => $deptModels['Program Studi Teknik Informatika']->id,
            ],
            [
                'username' => 'tendik.demo',
                'password' => 'password123',
                'role' => 'Tendik',
                'employee' => [
                    'nip' => '199201012018011001',
                    'full_name' => 'Tendik Demo',
                    'category' => 'tendik',
                    'gender' => 'P',
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '1992-01-01',
                    'email' => 'tendik.demo@uad.ac.id',
                    'highest_education' => 'S1',
                    'unit_id' => $deptModels['Biro Sumber Daya Manusia']->id,
                    'employment_status' => 'aktif',
                    'join_date' => '2018-01-01',
                ],
                'unit_id' => $deptModels['Biro Sumber Daya Manusia']->id,
            ],
        ];

        foreach ($demoUsers as $userData) {
            $employee = Employee::firstOrCreate(
                ['nip' => $userData['employee']['nip']],
                [
                    'full_name' => $userData['employee']['full_name'],
                    'category' => $userData['employee']['category'],
                    'gender' => $userData['employee']['gender'],
                    'birth_place' => $userData['employee']['birth_place'],
                    'birth_date' => $userData['employee']['birth_date'],
                    'email' => $userData['employee']['email'],
                    'highest_education' => $userData['employee']['highest_education'],
                    'unit_id' => $userData['employee']['unit_id'],
                    'employment_status' => $userData['employee']['employment_status'],
                    'join_date' => $userData['employee']['join_date'],
                ]
            );

            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                [
                    'employee_id' => $employee->id,
                    'password_hash' => Hash::make($userData['password']),
                    'is_active' => true,
                ]
            );

            UserRole::firstOrCreate([
                'user_id' => $user->id,
                'role_id' => $roleModels[$userData['role']]->id,
            ], [
                'unit_id' => $userData['unit_id'],
            ]);
        }
    }
}
