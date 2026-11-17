<?php

declare(strict_types=1);

namespace App\Http\Requests\Welfare;

use Illuminate\Foundation\Http\FormRequest;

class AllowanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allowance_type_id' => ['required', 'integer', 'exists:allowance_types,id'],
            'period_label' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
