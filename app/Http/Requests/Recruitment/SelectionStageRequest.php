<?php

declare(strict_types=1);

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class SelectionStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'stage_name' => ['required', 'string', 'max:50'],
            'sequence' => ['required', 'integer', 'min:1'],
            'scheduled_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:menunggu,lolos,gugur'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assessor_notes' => ['nullable', 'string'],
        ];
    }
}
