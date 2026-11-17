<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class TrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'training_type' => ['required', 'string', 'in:internal,eksternal,pra_pensiun'],
            'organizer' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'retirement_id' => ['nullable', 'integer', 'exists:retirements,id'],
        ];
    }
}
