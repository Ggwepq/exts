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

                <!-- Right Side (Register Form) -->
                <div class="py-16 px-8 md:px-12 flex flex-col justify-center relative overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -mr-16 -mt-16 blur-2xl">
                    </div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-blue-600/10 rounded-full -ml-16 -mb-16 blur-2xl">
                    </div>

                    <div class="max-w-sm mx-auto w-full relative">
                        <h2
                            class="text-3xl font-extrabold mb-1 text-center bg-gradient-to-r from-emerald-500 to-blue-600 bg-clip-text text-transparent leading-tight">
                            Register</h2>

                        <form wire:submit="register" class="space-y-6 mt-12">
                            <!-- First Name -->
                            <div class="form-control relative group">
                                <x-input-label for="first_name" :value="__('First Name')"
                                    class="text-sm font-medium mb-2 opacity-70 group-focus-within:text-emerald-600 transition-colors duration-200" />
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 group-focus-within:text-emerald-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                    <input type="text" wire:model.lazy="first_name"
                                        class="input input-bordered w-full pl-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base"
                                        name="first_name" required autocomplete="given-name" placeholder="John" />
                                </div>
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>

                            <!-- Last Name -->
                            <div class="form-control relative group">
                                <x-input-label for="last_name" :value="__('Last Name')"
                                    class="text-sm font-medium mb-2 opacity-70 group-focus-within:text-emerald-600 transition-colors duration-200" />
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 group-focus-within:text-emerald-600 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                    <input type="text" wire:model.lazy="last_name"
                                        class="input input-bordered w-full pl-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base"
                                        name="last_name" required autocomplete="family-name" placeholder="Doe" />
                                </div>
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>

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
                                    <input type="email" wire:model.lazy="email"
                                        class="input input-bordered w-full pl-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base"
                                        name="email" required autocomplete="username" placeholder="your@email.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="form-control relative group" x-data="{
                                showPassword: false,
                                password: '',
                                strength: 0,
                                getStrength() {
                                    let score = 0;
                                    // Length check
                                    if (this.password.length >= 8) score++;
                                    // Contains number
                                    if (/[0-9]/.test(this.password)) score++;
                                    // Contains lowercase
                                    if (/[a-z]/.test(this.password)) score++;
                                    // Contains uppercase
                                    if (/[A-Z]/.test(this.password)) score++;
                                    // Contains special char
                                    if (/[^A-Za-z0-9]/.test(this.password)) score++;
                                    return score;
                                }
                            }">
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
                                    <input wire:model.live="password" id="password" x-model="password"
                                        class="input input-bordered w-full pl-10 pr-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base [&::-ms-reveal]:hidden [&::-ms-clear]:hidden"
                                        :type="showPassword ? 'text' : 'password'" name="password" required
                                        autocomplete="new-password" placeholder="••••••••"
                                        @input="strength = getStrength()" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-emerald-600 transition-colors duration-200">
                                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Password Requirements and Strength Indicator -->
                                <div class="mt-2 space-y-2">
                                    <div class="text-xs text-base-content/70 space-y-1">
                                        <p class="font-medium">Password must include:</p>
                                        <div class="flex items-center space-x-1">
                                            <span x-bind:class="{ 'text-green-500': password.length >= 8 }"
                                                class="transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"
                                                        x-show="password.length >= 8" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                                        x-show="password.length < 8" />
                                                </svg>
                                            </span>
                                            <span>At least 8 characters</span>
                                        </div>
                                    </div>

                                    <!-- Strength Indicator -->
                                    <div class="flex items-center space-x-2">
                                        <div class="h-1.5 flex-1 rounded-full bg-base-300 overflow-hidden">
                                            <div x-bind:class="{
                                                'w-1/5 bg-red-500': strength === 1,
                                                'w-2/5 bg-orange-500': strength === 2,
                                                'w-3/5 bg-yellow-500': strength === 3,
                                                'w-4/5 bg-emerald-500': strength === 4,
                                                'w-full bg-green-500': strength === 5
                                            }"
                                                class="h-full transition-all duration-300"></div>
                                        </div>
                                        <span x-text="strength < 3 ? 'Weak' : strength < 5 ? 'Medium' : 'Strong'"
                                            x-bind:class="{
                                                'text-red-500': strength < 3,
                                                'text-yellow-500': strength === 3,
                                                'text-green-500': strength >= 4
                                            }"
                                            class="text-xs font-medium min-w-[3rem]"></span>
                                    </div>
                                </div>

                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-control relative group" x-data="{ showPassword: false }">
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')"
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
                                    <input wire:model.lazy="password_confirmation" id="password_confirmation"
                                        class="input input-bordered w-full pl-10 pr-10 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-base [&::-ms-reveal]:hidden [&::-ms-clear]:hidden"
                                        :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                                        required autocomplete="new-password" placeholder="••••••••" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-emerald-600 transition-colors duration-200">
                                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="btn bg-gradient-to-r from-emerald-500 to-blue-600 border-0 text-white w-full hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 hover:from-emerald-600 hover:to-blue-700 h-12 text-base">
                                <span class="relative">
                                    Register
                                    <span wire:loading class="absolute -right-6">
                                        <span class="loading loading-dots loading-xs"></span>
                                    </span>
                                </span>
                            </button>

                            <!-- Login Link -->
                            <div class="text-center text-sm text-base-content/70">
                                Already have an account?
                                <a href="{{ route('login') }}"
                                    class="font-medium text-emerald-600 hover:text-blue-600 transition-colors duration-200 ml-1">
                                    Log In
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
