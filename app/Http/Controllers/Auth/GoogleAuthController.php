<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse|RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->with('error', 'Google SSO belum dikonfigurasi.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', 'Autentikasi Google gagal atau dibatalkan.');
        }

        $email = $googleUser->getEmail()
            ? Str::lower($googleUser->getEmail())
            : null;

        $user = User::query()
            ->where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if (! $user && $email) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $user = new User([
                'role' => User::ROLE_MASYARAKAT,
            ]);
        }

        $user->fill([
            'name' => $googleUser->getName() ?: $user->name ?: 'Pengguna Google',
            'email' => $email ?: $user->email,
            'email_verified_at' => $email ? now() : $user->email_verified_at,
            'provider' => 'google',
            'provider_id' => $googleUser->getId(),
            'avatar_url' => $googleUser->getAvatar(),
        ]);
        $user->save();

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route($user->dashboardRouteName()));
    }
}
