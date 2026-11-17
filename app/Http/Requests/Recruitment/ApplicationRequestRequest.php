<?php

declare(strict_types=1);

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vacancy_id' => ['required', 'integer', 'exists:vacancies,id'],
            'applicant_id' => ['required', 'integer', 'exists:applicants,id'],
        ];
    }
}
