<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div tabindex="0" class="collapse collapse-arrow bg-base-100 p-4 sm:p-8 shadow sm:rounded-lg bg-base-100 ">
                <input type="checkbox" />
                <div class="collapse-title font-semibold">Themes</div>
                <div class="collapse-content text-sm">
                    <div class="max-w-xl">
                        <livewire:profile.set-theme />
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
