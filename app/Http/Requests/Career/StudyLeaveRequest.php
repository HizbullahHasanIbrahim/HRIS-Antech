<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class StudyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university' => ['required', 'string', 'max:150'],
            'study_program' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'funding_source' => ['required', 'string', 'in:mandiri,beasiswa,lembaga'],
            'scholarship_name' => ['nullable', 'string', 'max:100'],
            'supervisor_id' => ['required', 'integer'],
        ];
    }
}