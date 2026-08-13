<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div class="min-h-screen flex w-full">
    {{-- SISI KIRI — Form --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 bg-paper dark:bg-ink">
        <div class="w-full max-w-sm mx-auto">
            <div class="flex items-center gap-2.5 mb-10">
                <div class="w-9 h-9 rounded-lg bg-accent flex items-center justify-center">
                    <svg class="w-5 h-5 text-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="font-display font-bold text-lg text-arang dark:text-kertas">BurjoOrder</span>
            </div>

            <h1 class="font-display font-bold text-3xl text-arang dark:text-kertas">Selamat datang kembali!</h1>
            <p class="text-muted-dark dark:text-muted-light text-sm mt-2 mb-8">Masuk untuk kelola pesanan &amp; menu burjo kamu.</p>

            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-lg shadow-sm p-6">
                <h2 class="font-display font-semibold text-arang dark:text-kertas mb-5">Masuk ke Dashboard</h2>
                <x-auth-session-status class="mb-5" :status="session('status')" />
                <form wire:submit="login" class="space-y-5">
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@burjo.com" />
                        <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                        <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
                    </div>
                    <div class="flex items-center justify-between">
                        <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input wire:model="form.remember" id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-border-light dark:border-border-dark bg-paper dark:bg-ink text-accent focus:ring-2 focus:ring-accent focus:ring-offset-0">
                            <span class="text-sm text-muted-dark dark:text-muted-light">{{ __('Ingat saya') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-accent hover:text-accent-dark transition-colors">{{ __('Lupa password?') }}</a>
                        @endif
                    </div>
                    <x-primary-button class="w-full justify-center">
                        {{ __('Masuk') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>

    {{-- SISI KANAN — Lottie Animation --}}
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center bg-kertas dark:bg-surface">
        <dotlottie-player
            src="{{ asset('storage/animations/login.lottie') }}"
            background="transparent"
            speed="1"
            loop
            autoplay
            class="w-[280px] h-[280px]"
        ></dotlottie-player>
    </div>
</div>
