<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\Certification;
use App\Models\Employee;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CertificationService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function createCertification(
        Employee $employee,
        string $certificationName,
        \DateTimeInterface $certificationDate,
        string $certificationLevel,
        string $certificationNumber,
        ?string $certificatePath = null
    ): Certification {
        return DB::transaction(function () use ($employee, $certificationName, $certificationDate, $certificationLevel, $certificationNumber, $certificatePath) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $certification = Certification::create([
                'employee_id' => $employee->id,
                'certification_name' => $certificationName,
                'certification_date' => $certificationDate,
                'certification_level' => $certificationLevel,
                'certification_number' => $certificationNumber,
                'certificate_path' => $certificatePath,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_CERTIFICATION',
                'Certification',
                $certification->id,
                null,
                'aktif',
            );

            return $certification;
        });
    }

    public function deleteCertification(Certification $certification): void
    {
        DB::transaction(function () use ($certification) {
            $certification->delete();

            $this->activityLog->log(
                $certification->employee?->user?->id,
                'DELETE_CERTIFICATION',
                'Certification',
                $certification->id,
                'aktif',
                'dihapus',
            );
        });
    }
}
