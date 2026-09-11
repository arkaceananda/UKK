<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="min-h-screen flex w-full">
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 bg-paper dark:bg-ink">
        <div class="w-full max-w-sm mx-auto">
            <div class="flex items-center gap-2.5 mb-10">
                <div class="w-9 h-9 rounded-lg bg-accent flex items-center justify-center">
                    <svg class="w-5 h-5 text-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <span class="font-display font-bold text-lg text-arang dark:text-kertas">BurjoOrder</span>
            </div>

            <h1 class="font-display font-bold text-2xl text-arang dark:text-kertas">Atur ulang password</h1>
            <p class="text-muted-dark dark:text-muted-light text-sm mt-2 mb-6">Masukkan email dan password baru. Link hanya berlaku 60 menit — jangan bagikan link email ini.</p>

            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-lg shadow-sm p-6">
                <form wire:submit="resetPassword" class="space-y-5">
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@burjo.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Password baru')" />
                        <x-text-input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi password')" />
                        <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Simpan password baru') }}
                    </x-primary-button>
                </form>
            </div>
            <p class="text-xs text-muted-dark dark:text-muted-light text-center mt-6">Setelah berhasil, kamu akan diarahkan ke halaman masuk.</p>
        </div>
    </div>
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center bg-kertas dark:bg-surface">
        <dotlottie-player src="{{ asset('storage/animations/login.lottie') }}" background="transparent" speed="1" loop autoplay class="w-[280px] h-[280px]"></dotlottie-player>
    </div>
</div>
