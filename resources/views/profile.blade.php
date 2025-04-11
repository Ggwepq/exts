<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn m-1">
                            Theme
                            <svg width="12px" height="12px" class="inline-block h-2 w-2 fill-current opacity-60"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2048 2048">
                                <path d="M1799 349l242 241-1017 1017L7 590l242-241 775 775 775-775z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content bg-base-300 rounded-box z-1 w-52 p-2 shadow-2xl max-h-80 overflow-y-auto">
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Dark" value="dark" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Default" value="default" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Retro" value="retro" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Cyberpunk" value="cyberpunk" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Valentine" value="valentine" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Aqua" value="aqua" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Night" value="night" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Coffee" value="coffee" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Synthwave" value="synthwave" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Forest" value="forest" />
                            </li>
                            <li>
                                <input type="radio" name="theme-dropdown"
                                    class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start"
                                    aria-label="Luxury" value="luxury" />
                            </li>
                        </ul>
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
