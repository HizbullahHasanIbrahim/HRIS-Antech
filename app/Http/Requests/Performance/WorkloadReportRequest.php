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
            'period_label' => ['required', 'string', 'max:20'],
            'academic_year' => ['required', 'string', 'max:10'],
            'semester' => ['required', 'string', 'max:10'],
        ];
    }
}
