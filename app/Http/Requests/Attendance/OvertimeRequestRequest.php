<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'overtime_date' => ['required', 'date'],
            'duration_hours' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:500'],
            'approver_id' => ['required', 'integer'],
        ];
    }
}
