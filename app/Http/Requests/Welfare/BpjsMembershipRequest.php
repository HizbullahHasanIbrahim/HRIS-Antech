<?php

declare(strict_types=1);

namespace App\Http\Requests\Welfare;

use Illuminate\Foundation\Http\FormRequest;

class BpjsMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bpjs_type' => ['required', 'string', 'in:kesehatan,ketenagakerjaan'],
            'membership_number' => ['required', 'string', 'max:30'],
            'registered_date' => ['required', 'date'],
        ];
    }
}
