<?php

declare(strict_types=1);

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class SkpTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_label' => ['required', 'string', 'max:20'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'appraiser_id' => ['required', 'integer'],
        ];
    }
}
