<x-layouts.app>
    <div class="mx-auto max-w-md panel p-7" x-data="{ channel: '{{ old('channel', 'email') }}' }">
        <h1 class="text-2xl font-black">Masuk dengan OTP</h1><p class="mt-1 text-sm text-slate-500">Kode berlaku 5 menit dan maksimal lima percobaan.</p>
        <form method="POST" action="{{ route('otp.send') }}" class="mt-6 space-y-4">@csrf
            <x-ui.select label="Kanal" name="channel" x-model="channel"><option value="email">Email</option><option value="whatsapp">WhatsApp</option></x-ui.select>
            <x-ui.input label="Email atau nomor WhatsApp" name="identifier" value="{{ old('identifier') }}" required />
            <x-ui.button type="submit" class="w-full">Kirim kode OTP</x-ui.button>
        </form>
        @if(session('debug_otp'))<div class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-800"><strong>Mode lokal:</strong> kode OTP {{ session('debug_otp') }}</div>@endif
        @if(session('otp_sent'))
            <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4 border-t border-slate-200 pt-6 dark:border-slate-800">@csrf
                <input type="hidden" name="channel" value="{{ old('channel') }}"><input type="hidden" name="identifier" value="{{ old('identifier') }}">
                <x-ui.input label="Kode 6 digit" name="code" inputmode="numeric" maxlength="6" required autofocus />
                <x-ui.button type="submit" variant="success" class="w-full">Verifikasi dan masuk</x-ui.button>
            </form>
        @endif
    </div>
</x-layouts.app>
