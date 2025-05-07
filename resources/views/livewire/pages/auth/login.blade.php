<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

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
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="min-h-screen bg-base-200 flex items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="grid md:grid-cols-2 grid-cols-1 bg-base-100 rounded-xl">
                <!-- Left Side (Landing Intro) -->
                <div>
                    <livewire:pages.auth.land-intro />
                </div>

                <!-- Right Side (Login Form) -->
                <div class="py-10 px-10">
                    <h2 class="text-2xl font-semibold mb-2 text-center">Log In</h2>

                    <form wire:submit="login">
                        <div class="mb-4">
                            <!-- Email -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="email" :value="__('Email')" />
                                <input type="email" wire:model.live="form.email"
                                    class="input input-bordered validator w-full" name="email" required
                                    autocomplete="username" />
                                <x-input-error :messages="$errors->first('form.email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="password" :value="__('Password')" />
                                <input wire:model.live="form.password" id="password"
                                    class="input input-bordered validator w-full" type="password" name="password"
                                    required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                            </div>

                        </div>

                        <!-- Remember Me -->
                        <div class="block mt-4">
                            <label for="remember" class="inline-flex items-center">
                                <input wire:model="form.remember" id="remember" type="checkbox" class="checkbox"
                                    name="remember">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="text-right text-primary">
                            <a href="{{ route('password.request') }}"
                                class="text-sm text-primary inline-block hover:text-secondary hover:underline hover:cursor-pointer transition duration-200">
                                Forgot Password?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn mt-2 w-full btn-primary">
                            Login
                            <span wire:loading.class="loading loading-dots loading-sm"></span>
                        </button>

                        <!-- Register Link -->
                        <div class="text-center mt-4">
                            Don't have an account?
                            <a href="{{ route('register') }}"
                                class="inline-block text-primary hover:text-secondary hover:underline hover:cursor-pointer transition duration-200">
                                Register
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
