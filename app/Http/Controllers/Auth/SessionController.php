<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        return redirect()->intended(route($user->dashboardRouteName()));
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function createAccount(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            ...$request->safe()->only(['name', 'email', 'phone', 'password']),
            'email_verified_at' => now(),
            'role' => User::ROLE_MASYARAKAT,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profile')->with('success', 'Akun berhasil dibuat.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
