<?php

declare(strict_types=1);

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class StaffRankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rank_level' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:diajukan,disetujui,dibatalkan'],
            'approver_id' => ['required', 'integer'],
        ];
    }
}
