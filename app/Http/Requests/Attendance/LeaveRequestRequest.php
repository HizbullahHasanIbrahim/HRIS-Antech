<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by Policy later. For now, assume middleware/policy covers it.
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', Rule::exists('leave_types', 'id')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
            'approver_id' => ['required', 'integer'],
        ];
    }
}
