<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>

    <div class="min-h-screen bg-base-200 flex items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="flex flex-col justify-center bg-base-100 rounded-xl ">

                <div class="hero min-h-full rounded-l-xl bg-base-200">
                    <div class="hero-content py-12">
                        <div class="max-w-md">

                            @if (session('status') == 'verification-link-sent')
                                <h1 class='text-3xl text-center font-bold '>Email Verification Resent!</h1>
                            @else
                                <h1 class='text-3xl text-center font-bold '>Email Verification Sent!</h1>
                            @endif
                            <h5 class='text-sm text-center '>Please check your email to complete this step.
                            </h5>


                            <div class="text-center mt-8"><img src="{{ asset('img/email-sent.png') }}"
                                    alt="Email Sent Icon" class="w-54 inline-block"></img></div>


                            <div class="mt-4 flex flex-col items-center justify-center">

                                <x-primary-button wire:click="sendVerification">
                                    {{ __('Resend Verification Email') }}
                                </x-primary-button>

                                <button wire:click="logout" type="submit"
                                    class="underline text-sm text-primary mt-2 hover:text-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Log Out') }}
                                </button>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
