<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->string('username'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password_hash)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan tidak sesuai dengan catatan kami.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Generate and store API token (built-in token guard, storage_key = api_token)
        $token = Str::random(60);
        $user->update([
            'api_token' => $token,
            'last_login_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $request->boolean('remember') ? 2592000 : 86400,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'employee_id' => $user->employee_id,
                    'roles' => $user->userRoles()->with('role')->get()->map(fn ($ur) => [
                        'role' => $ur->role->name,
                        'unit_id' => $ur->unit_id,
                    ]),
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('employee');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->employee?->email,
                'full_name' => $user->employee?->full_name,
                'nip' => $user->employee?->nip,
                'category' => $user->employee?->category,
                'unit_id' => $user->employee?->unit_id,
                'roles' => $user->userRoles()->with('role')->get()->map(fn ($ur) => [
                    'role' => $ur->role->name,
                    'unit_id' => $ur->unit_id,
                ]),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->update(['api_token' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $token = Str::random(60);
        $user->update(['api_token' => $token]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
            ],
        ]);
    }
}
