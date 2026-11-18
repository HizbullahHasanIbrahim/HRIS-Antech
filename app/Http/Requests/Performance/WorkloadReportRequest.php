<?php

declare(strict_types=1);

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class WorkloadReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year' => ['required', 'string', 'max:10'],
            'semester' => ['required', 'string', 'max:10'],
            'teaching_credit' => ['required', 'numeric', 'min:0'],
            'research_credit' => ['required', 'numeric', 'min:0'],
            'service_credit' => ['required', 'numeric', 'min:0'],
            'support_credit' => ['required', 'numeric', 'min:0'],
            'validator_id' => ['required', 'integer'],
        ];
    }
}