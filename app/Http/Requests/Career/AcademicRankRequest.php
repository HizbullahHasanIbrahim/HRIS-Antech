<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class AcademicRankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rank_name' => ['required', 'string', 'max:50'],
            'credit_points' => ['required', 'numeric', 'min:0'],
            'is_accelerated' => ['required', 'boolean'],
            'approver_id' => ['required', 'integer'],
        ];
    }
}
