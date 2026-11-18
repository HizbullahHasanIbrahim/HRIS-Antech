<?php

declare(strict_types=1);

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use Illuminate\Http\JsonResponse;

class AllowanceTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = AllowanceType::where('is_active', true)->get();

        return response()->json(['success' => true, 'data' => $types]);
    }
}
