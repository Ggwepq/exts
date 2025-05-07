<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    // public string $phone_number = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            // 'phone_number' => ['string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // dd($validated);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="min-h-screen bg-base-200 flex items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="grid md:grid-cols-2 grid-cols-1 bg-base-100 rounded-xl">
                <!-- Left Side (Landing Intro) -->
                <div>
                    <livewire:pages.auth.land-intro />
                </div>

                <!-- Right Side (Login Form) -->
                <div class="py-10 px-10">
                    <h2 class="text-2xl font-semibold mb-2 text-center">Register</h2>

                    <form wire:submit="register">
                        <div class="mb-4">

                            <!-- First Name -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="first_name" :value="__('First Name')" />
                                <input type="text" wire:model.lazy="first_name"
                                    class="input input-bordered validator w-full" name="first_name" required
                                    autocomplete="Name" />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>

                            <!-- Last Name -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <input type="text" wire:model.lazy="last_name"
                                    class="input input-bordered validator w-full" name="last_name" required
                                    autocomplete="last_name" />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="email" :value="__('Email')" />
                                <input type="email" wire:model.lazy="email"
                                    class="input input-bordered validator w-full" name="email" required
                                    autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="password" :value="__('Password')" />
                                <input wire:model.lazy="password" id="password"
                                    class="input input-bordered validator w-full" type="password" name="password"
                                    required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-control w-full mt-4">
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                <input wire:model.lazy="password_confirmation" id="password_confirmation"
                                    class="input input-bordered validator w-full" type="password"
                                    name="password_confirmation" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn mt-2 w-full btn-primary">
                            Register
                            <span wire:loading.class="loading loading-dots loading-sm"></span>
                        </button>

                        <!-- Register Link -->
                        <div class="text-center mt-4">
                            Already have an account?
                            <a href="{{ route('login') }}"
                                class="inline-block text-primary hover:text-secondary hover:underline hover:cursor-pointer transition duration-200">
                                Log In
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
