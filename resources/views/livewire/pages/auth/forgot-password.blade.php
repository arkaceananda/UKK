<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="min-h-screen flex w-full">
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 bg-paper dark:bg-ink">
        <div class="w-full max-w-sm mx-auto">
            <div class="flex items-center gap-2.5 mb-10">
                <div class="w-9 h-9 rounded-lg bg-accent flex items-center justify-center">
                    <svg class="w-5 h-5 text-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="font-display font-bold text-lg text-arang dark:text-kertas">BurjoOrder</span>
            </div>

            <h1 class="font-display font-bold text-2xl text-arang dark:text-kertas">Lupa password?</h1>
            <p class="text-muted-dark dark:text-muted-light text-sm mt-2 mb-6">Masukkan email yang terdaftar. Kami akan mengirim link untuk mengatur ulang password — link berlaku 60 menit, jangan bagikan ke orang lain.</p>

            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-lg shadow-sm p-6">
                <x-auth-session-status class="mb-5" :status="session('status')" />

                <form wire:submit="sendPasswordResetLink" class="space-y-5">
                    <div>
                        <x-input-label for="email" :value="__('Email terdaftar')" />
                        <x-text-input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="email" placeholder="nama@burjo.com" />
                        <p class="text-xs text-muted-dark dark:text-muted-light mt-1.5">Kami kirim ke email ini — cek juga folder Spam.</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Kirim link reset password') }}
                    </x-primary-button>

                    <div class="flex items-center justify-center gap-1 text-sm pt-1">
                        <span class="text-muted-dark dark:text-muted-light">Ingat password?</span>
                        <a href="{{ route('login') }}" wire:navigate class="text-accent hover:text-accent-dark">Kembali ke Masuk</a>
                    </div>
                </form>
            </div>
            <p class="text-xs text-muted-dark dark:text-muted-light text-center mt-6">Butuh bantuan? Hubungi owner/admin BurjoOrder.</p>
        </div>
    </div>
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center bg-kertas dark:bg-surface">
        <dotlottie-player src="{{ asset('storage/animations/login.lottie') }}" background="transparent" speed="1" loop autoplay class="w-[280px] h-[280px]"></dotlottie-player>
    </div>
</div>
