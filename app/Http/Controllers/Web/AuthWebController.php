<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        // Handle registration / activation request
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil didaftarkan. Silakan masuk.');
    }

    public function registerApplicant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:applicants,email',
            'phone' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'highest_education' => 'required|string|max:10',
            'category' => 'required|string|in:dosen,tendik',
        ]);

        Applicant::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'birth_date' => $validated['birth_date'],
            'highest_education' => $validated['highest_education'],
        ]);

        return redirect()->route('register')->with('success', 'Pendaftaran pelamar berhasil. Silakan cek email verifikasi.');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = User::where('username', $request->string('username'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password_hash)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial tidak sesuai.'],
            ]);
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'username' => 'Akun tidak aktif.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
