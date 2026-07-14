<?php

namespace App\Services;

use App\Mail\OtpCodeMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class OtpService
{
    public function send(string $identifier, string $channel, ?string $ipAddress = null): string
    {
        $identifier = $this->normalizeIdentifier($identifier, $channel);
        $code = (string) random_int(100000, 999999);

        LoginOtp::query()
            ->where('identifier', $identifier)
            ->where('channel', $channel)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        LoginOtp::query()->create([
            'identifier' => $identifier,
            'channel' => $channel,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ipAddress,
        ]);

        if ($channel === 'email') {
            Mail::to($identifier)->send(new OtpCodeMail($code));
        } else {
            $this->sendWhatsApp($identifier, $code);
        }

        return $code;
    }

    public function verify(string $identifier, string $channel, string $code): User
    {
        $identifier = $this->normalizeIdentifier($identifier, $channel);
        $otp = LoginOtp::query()
            ->where('identifier', $identifier)
            ->where('channel', $channel)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || ! $otp->isUsable()) {
            throw new RuntimeException('Kode OTP tidak tersedia atau telah kedaluwarsa.');
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            throw new RuntimeException('Kode OTP tidak sesuai.');
        }

        $otp->update(['consumed_at' => now()]);

        if ($channel === 'email') {
            $user = User::query()->firstOrCreate(
                ['email' => $identifier],
                [
                    'name' => 'Warga '.Str::upper(Str::random(5)),
                    'role' => User::ROLE_MASYARAKAT,
                ]
            );
            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

            return $user;
        }

        $user = User::query()->firstOrCreate(
            ['phone' => $identifier],
            [
                'name' => 'Warga '.substr($identifier, -4),
                'role' => User::ROLE_MASYARAKAT,
            ]
        );
        $user->forceFill(['phone_verified_at' => $user->phone_verified_at ?? now()])->save();

        return $user;
    }

    public function normalizeIdentifier(string $identifier, string $channel): string
    {
        $identifier = trim($identifier);

        if ($channel === 'email') {
            return Str::lower($identifier);
        }

        $phone = preg_replace('/\D+/', '', $identifier) ?? '';

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    private function sendWhatsApp(string $phone, string $code): void
    {
        $url = (string) config('services.whatsapp.webhook_url');
        $token = (string) config('services.whatsapp.token');

        if ($url === '') {
            Log::info('OTP WhatsApp LaporKota GIS', ['phone' => $phone, 'code' => $code]);

            return;
        }

        $response = Http::timeout(10)
            ->withToken($token)
            ->post($url, [
                'to' => $phone,
                'sender_id' => config('services.whatsapp.sender_id'),
                'message' => "Kode OTP LaporKota GIS Anda: {$code}. Berlaku 5 menit.",
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Pengiriman OTP WhatsApp gagal.');
        }
    }
}
