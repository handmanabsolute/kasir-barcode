<x-layouts::auth :title="__('Reset Password')">
    <div class="flex flex-col items-center gap-8">
        <div class="flex size-28 items-center justify-center rounded-full bg-white shadow-lg">
            <img src="{{ asset('icon.svg') }}" alt="{{ config('app.name') }}" class="size-16 object-contain" />
        </div>

        <div class="text-center text-slate-800">
            <h1 class="text-2xl font-bold tracking-wide">Reset Password</h1>
            <p class="mt-2 text-sm text-slate-500">Masukkan password baru untuk akun Anda.</p>
        </div>

        @if ($errors->any())
            <div class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="flex w-full flex-col gap-5">
            @csrf

            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="kasir-input-group">
                <span class="kasir-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', request()->email) }}"
                    required
                    autocomplete="email"
                    placeholder="Email"
                    class="kasir-input"
                />
            </div>

            <div class="kasir-input-group">
                <span class="kasir-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Password baru"
                    class="kasir-input"
                />
            </div>

            <div class="kasir-input-group">
                <span class="kasir-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Konfirmasi password"
                    class="kasir-input"
                />
            </div>

            <button type="submit" class="kasir-button">
                Simpan password
            </button>
        </form>
    </div>
</x-layouts::auth>
