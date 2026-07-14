<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    public function create(): View
    {
        return view('auth.otp');
    }

    public function send(SendOtpRequest $request): RedirectResponse
    {
        $code = $this->otpService->send(
            $request->string('identifier')->toString(),
            $request->string('channel')->toString(),
            $request->ip()
        );

        $redirect = back()
            ->withInput($request->safe()->only(['identifier', 'channel']))
            ->with('otp_sent', true)
            ->with('success', 'Kode OTP telah dikirim dan berlaku selama 5 menit.');

        if (app()->isLocal()) {
            $redirect->with('debug_otp', $code);
        }

        return $redirect;
    }

    public function verify(VerifyOtpRequest $request): RedirectResponse
    {
        try {
            $user = $this->otpService->verify(
                $request->string('identifier')->toString(),
                $request->string('channel')->toString(),
                $request->string('code')->toString()
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRouteName()));
    }
}
