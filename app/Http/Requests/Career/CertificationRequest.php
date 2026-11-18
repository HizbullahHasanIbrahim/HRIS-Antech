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
            'cert_type' => ['required', 'string', 'max:50'],
            'cert_number' => ['required', 'string', 'max:100'],
            'cert_level' => ['required', 'string', 'max:50'],
            'submitted_date' => ['required', 'date'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}