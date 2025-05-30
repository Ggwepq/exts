<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public bool $showPassword = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div
        class="min-h-screen bg-[conic-gradient(at_top_right,_var(--tw-gradient-stops))] from-emerald-500 via-sky-200 to-blue-600 flex items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-[0_20px_50px_rgba(6,_182,_212,_0.5)]">
            <div class="grid md:grid-cols-2 grid-cols-1 bg-base-100 rounded-xl overflow-hidden">
                <!-- Left Side (Landing Intro) -->
                <div class="relative group">
                    <livewire:pages.auth.land-intro />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-base-100/50 via-emerald-500/20 to-transparent
                        group-hover:from-blue-600/30 group-hover:via-emerald-500/10 transition-all duration-500">
                    </div>
                </div>

                <!-- Right Side (Login Form) -->
                <div class="py-16 px-8 md:px-12 flex flex-col justify-center relative overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -mr-16 -mt-16 blur-2xl">
                    </div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-blue-600/10 rounded-full -ml-16 -mb-16 blur-2xl">
                    </div>

                    <div class="max-w-md mx-auto w-full relative">
                        <h2
                            class="text-3xl font-extrabold mb-2 text-center bg-gradient-to-r from-emerald-500 to-blue-600 bg-clip-text text-transparent leading-tight">
                            Welcome Back</h2>

                        <form wire:submit="login" class="space-y-8 mt-12">
                            <!-- Email -->
                            <div class="form-control relative group">
                                <x-input-label for="email" :value="__('Email')"
                                    class="text-sm font-medium mb-2 opacity-70 group-focus-within:text-emerald-600 transition-colors duration-200" />
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 group-focus-within:text-emerald-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </span>
                                    <input type="email" wire:model.live="form.email"
                                        class="input input-bordered w-full pl-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base"
                                        name="email" required autocomplete="username" placeholder="your@email.com" />
                                </div>
                                <x-input-error :messages="$errors->first('form.email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="form-control relative group" x-data="{ showPassword: false }">
                                <x-input-label for="password" :value="__('Password')"
                                    class="text-sm font-medium mb-2 opacity-70 group-focus-within:text-emerald-600 transition-colors duration-200" />
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 group-focus-within:text-emerald-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                    <input wire:model="form.password" id="password"
                                        class="input input-bordered w-full pl-10 pr-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base [&::-ms-reveal]:hidden [&::-ms-clear]:hidden"
                                        :type="showPassword ? 'text' : 'password'" name="password" required
                                        autocomplete="current-password" placeholder="••••••••" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-emerald-600 transition-colors duration-200">
                                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                            </div>

                            <!-- Remember Me and Forgot Password -->
                            <div class="flex items-center justify-between">
                                <label for="remember" class="inline-flex items-center hover:cursor-pointer group">
                                    <input wire:model="form.remember" id="remember" type="checkbox"
                                        class="checkbox checkbox-sm checkbox-success" name="remember">
                                    <span
                                        class="ms-2 text-sm text-base-content/70 group-hover:text-emerald-600 transition-colors duration-200">{{ __('Remember me') }}</span>
                                </label>

                                <a href="{{ route('password.request') }}"
                                    class="text-sm text-emerald-600 hover:text-blue-600 transition-colors duration-200">
                                    Forgot Password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="btn bg-gradient-to-r from-emerald-500 to-blue-600 border-0 text-white w-full hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 hover:from-emerald-600 hover:to-blue-700 h-12 text-base">
                                <span class="relative">
                                    Login
                                    <span wire:loading class="absolute -right-6">
                                        <span class="loading loading-dots loading-xs"></span>
                                    </span>
                                </span>
                            </button>

                            <!-- Register Link -->
                            <div class="text-center text-sm text-base-content/70">
                                Don't have an account?
                                <a href="{{ route('register') }}"
                                    class="font-medium text-emerald-600 hover:text-blue-600 transition-colors duration-200 ml-1">
                                    Register
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
