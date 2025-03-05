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
    public string $middle_name = '';
    public string $last_name = '';
    public string $phone_number = '';
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
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'phone_number' => ['string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // dd($validated);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <input type="text" wire:model="first_name" class="input mt-1 w-full" name="first_name" required autofocus
                autocomplete="Name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="middle_name" :value="__('Middle Name')" />
            <input type="text" wire:model="middle_name" class="input mt-1 w-full" name="middle_name" required
                autocomplete="middle_name" />
            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Last Name')" />
            <input type="text" wire:model="last_name" class="input mt-1 w-full" name="last_name" required
                autocomplete="last_name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <input type="text" wire:model="phone_number" class="input mt-1 w-full" name="phone_number" required
                autocomplete="phone_number" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <input type="email" wire:model="email" class="input mt-1 w-full" name="email" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <input wire:model="password" id="password" class="input mt-1 w-full" type="password" name="password"
                required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <input wire:model="password_confirmation" id="password_confirmation" class="input mt-1 w-full"
                type="password" name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
