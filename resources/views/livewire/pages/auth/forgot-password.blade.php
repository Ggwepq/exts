<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
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
        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
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
                    <h2 class="text-2xl text-center font-semibold mb-4">Forgot Password</h2>
                    @if (session('status'))
                        <div role="alert" class="alert alert-info alert-outline">
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    <form wire:submit="sendPasswordResetLink">
                        <!-- Email Address -->
                        <div class="form-control w-full mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <input type="email" wire:model="email" class="input input-bordered validator w-full"
                                name="email" required autocomplete="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8"> <button type="submit"
                                class="btn mt-2 btn-primary tooltip tooltip-left"
                                data-tip="Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.">
                                <span wire:loading.class="loading loading-spinner loading-lg">Password Reset
                                    Link</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
