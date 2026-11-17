<?php

declare(strict_types=1);

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class SkpEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'performance_items' => ['required', 'array', 'min:1'],
            'performance_items.*.description' => ['required', 'string', 'max:500'],
            'performance_items.*.unit' => ['required', 'string', 'max:30'],
            'performance_items.*.target_value' => ['required', 'numeric', 'min:0'],
            'performance_items.*.realization_value' => ['required', 'numeric', 'min:0'],
            'performance_items.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'performance_items.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
