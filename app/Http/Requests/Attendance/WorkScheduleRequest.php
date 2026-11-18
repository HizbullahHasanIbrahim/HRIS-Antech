<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class WorkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'in:reguler,shift,khusus'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'is_published' => ['nullable', 'boolean'],
            'days' => ['required', 'array', 'min:1'],
            'days.*.day_of_week' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'days.*.start_time' => ['required', 'date_format:H:i:s'],
            'days.*.end_time' => ['required', 'date_format:H:i:s', 'after:days.*.start_time'],
            'days.*.break_start' => ['nullable', 'date_format:H:i:s'],
            'days.*.break_end' => ['nullable', 'date_format:H:i:s', 'after:days.*.break_start'],
        ];
    }
}