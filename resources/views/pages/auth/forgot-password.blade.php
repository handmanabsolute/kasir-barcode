<x-layouts::auth :title="__('Lupa Password')">
    <div class="flex flex-col items-center gap-8">
        <div class="flex size-28 items-center justify-center rounded-full bg-white shadow-lg">
            <img src="{{ asset('icon.svg') }}" alt="{{ config('app.name') }}" class="size-16 object-contain" />
        </div>

        <div class="text-center text-white">
            <h1 class="text-2xl font-semibold tracking-wide">Lupa Password</h1>
            <p class="mt-2 text-sm text-white/80">Masukkan username atau email untuk menerima link reset password.</p>
        </div>

        @if (session('status'))
            <div class="w-full rounded-lg border border-white/30 bg-white/10 px-4 py-3 text-center text-sm text-white">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="w-full rounded-lg border border-red-300/50 bg-red-500/20 px-4 py-3 text-center text-sm text-white">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex w-full flex-col gap-5">
            @csrf

            <div class="kasir-input-group">
                <span class="kasir-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="text"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Username atau email"
                    class="kasir-input"
                />
            </div>

            <button type="submit" class="kasir-button" data-test="email-password-reset-link-button">
                Kirim link reset
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-white/90 transition hover:text-white">
                    Kembali ke Login
                </a>
            </div>
        </form>
    </div>
</x-layouts::auth>
