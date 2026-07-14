<x-layouts.app>
    <div class="mx-auto max-w-md panel p-7">
        <h1 class="text-2xl font-black">Daftar akun warga</h1>
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">@csrf
            <x-ui.input label="Nama" name="name" value="{{ old('name') }}" required />
            <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" required />
            <x-ui.input label="Nomor WhatsApp" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" />
            <x-ui.input label="Kata sandi" name="password" type="password" required />
            <x-ui.input label="Konfirmasi kata sandi" name="password_confirmation" type="password" required />
            <x-ui.button type="submit" class="w-full">Buat akun</x-ui.button>
        </form>
        <p class="mt-5 text-center text-sm text-slate-500">Sudah punya akun? <a class="font-bold text-blue-600" href="{{ route('login') }}" wire:navigate>Masuk</a></p>
    </div>
</x-layouts.app>
