<?php

declare(strict_types=1);

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vacancy_id' => ['required', 'integer', Rule::exists('vacancies', 'id')],
            'applicant_id' => ['required', 'integer', Rule::exists('applicants', 'id')],
            'aik_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:applied,in_process,approved,rejected,employee_created'],
        ];
    }
}
