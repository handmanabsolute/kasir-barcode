<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Update password if new password field is filled
        if ($this->password !== '') {
            $this->validate([
                'password' => ['required', 'string', Password::default(), 'confirmed'],
            ]);

            $user->password = Hash::make($this->password);
        }

        $user->save();

        $this->reset('password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Profil dan sandi berhasil diperbarui.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }
}; ?>

<section class="w-full">
    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Perbarui profil dan kata sandi Anda')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Account Name (Mock) -->
            <flux:input label="Account Name" value="kasir-barcode.local" disabled readonly class="opacity-80" />

            <!-- Username (Real) -->
            <flux:input wire:model="name" :label="__('Username')" type="text" required autofocus autocomplete="name" />

            <!-- Profile Photo (Gravatar) -->
            <div class="space-y-2">
                <span class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Profile Photo</span>
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                        {{ auth()->user()->initials() }}
                    </div>
                    <span class="text-xs text-zinc-500">Profile photos are managed with Gravatar.</span>
                </div>
            </div>

            <!-- First Name (Mock) -->
            <flux:input label="First Name" value="Admin" placeholder="Masukkan nama depan" />

            <!-- Last Name (Mock with error validation mockup) -->
            <div class="relative">
                <flux:input label="Last Name" class="has-error-bg" placeholder="Last Name cannot be empty" value="" />
                <div class="absolute right-3 top-[34px] flex items-center text-red-500">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- Login Email (Real with mockup helper text) -->
            <div>
                <flux:input wire:model="email" :label="__('Login Email')" type="email" required autocomplete="email" />
                <span class="block text-xs text-zinc-400 mt-1.5 leading-relaxed">
                    This is the email address you will use to log into your account. If you change this you will no longer be able to log in with your prior email address. If you have notifications set up, this address will receive them.
                </span>

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Phone Number (Mock with verify button) -->
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <flux:input label="Phone Number" placeholder="+628123456789" />
                </div>
                <flux:button variant="outline" class="h-[38px] text-blue-600 border-blue-600 hover:bg-blue-50/50 dark:hover:bg-blue-950/20">Verify</flux:button>
            </div>

            <div class="w-full border-t border-zinc-200 dark:border-zinc-700 my-6"></div>

            <flux:heading size="lg" class="mt-8">Ubah Kata Sandi</flux:heading>
            <flux:text class="mb-4">Masukkan sandi baru di bawah jika Anda ingin mengganti kata sandi login saat ini.</flux:text>

            <flux:input
                wire:model="password"
                :label="__('Sandi Baru')"
                type="password"
                placeholder="Masukkan sandi baru"
                viewable
            />

            <flux:input
                wire:model="password_confirmation"
                :label="__('Konfirmasi Sandi Baru')"
                type="password"
                placeholder="Masukkan kembali sandi baru"
                viewable
            />

            <div class="flex items-center justify-between pt-4">
                <flux:button variant="primary" type="submit" class="px-6" data-test="update-profile-button">
                    {{ __('Simpan Perubahan') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>

