<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class CertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certification_name' => ['required', 'string', 'max:150'],
            'certification_date' => ['required', 'date'],
            'certification_level' => ['required', 'string', 'max:50'],
            'certification_number' => ['required', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
