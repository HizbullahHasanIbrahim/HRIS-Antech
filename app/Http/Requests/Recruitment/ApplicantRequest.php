<?php

declare(strict_types=1);

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:applicants,email'],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'highest_education' => ['required', 'string', 'max:10'],
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }
}
