<?php

declare(strict_types=1);

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class VacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:dosen,tendik'],
            'description' => ['nullable', 'string'],
            'min_qualification' => ['required', 'string'],
            'quota' => ['required', 'integer', 'min:1'],
            'open_date' => ['required', 'date'],
            'close_date' => ['required', 'date', 'after_or_equal:open_date'],
        ];
    }
}
