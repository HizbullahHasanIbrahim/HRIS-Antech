<?php

declare(strict_types=1);

namespace App\Services\Recruitment;

use App\Models\Application;
use App\Models\Vacancy;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class RecruitmentService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function createVacancy(array $data, int $userId): Vacancy
    {
        return DB::transaction(function () use ($data, $userId) {
            $vacancy = Vacancy::create(array_merge($data, ['created_by' => $userId, 'status' => 'draf']));
            $this->activityLog->log($userId, 'CREATE_VACANCY', 'Vacancy', $vacancy->id, null, 'draf');

            return $vacancy;
        });
    }

    public function apply(int $vacancyId, int $applicantId): Application
    {
        return DB::transaction(function () use ($vacancyId, $applicantId) {
            $application = Application::create([
                'vacancy_id' => $vacancyId,
                'applicant_id' => $applicantId,
                'applied_at' => now(),
                'status' => 'diajukan',
            ]);
            $this->activityLog->log(null, 'CREATE_APPLICATION', 'Application', $application->id, null, 'diajukan');

            return $application;
        });
    }
}
