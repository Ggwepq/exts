<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'default';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            // Handle dark mode class for themes that might need it
            if (savedTheme === 'dark' || savedTheme === 'night' || savedTheme === 'coffee' || savedTheme === 'forest' || savedTheme === 'luxury' || savedTheme === 'synthwave') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('img/sample-logo.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-roboto antialiased">

    <div class="drawer lg:drawer-open" x-data="{
        detailSidebarOpen: false,
        init() {
            // Restore sidebar state from localStorage if exists
            this.detailSidebarOpen = localStorage.getItem('detailSidebarOpen') === 'true';

            // Save state to localStorage when it changes
            this.$watch('detailSidebarOpen', (value) => {
                localStorage.setItem('detailSidebarOpen', value);
            });
        }
    }">
        <input id="left-sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <main class="min-h-screen bg-base-200 relative overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>

        <!-- Left Sidebar -->
        <livewire:pages.user.containers.sidebar />

    </div>

    <x-toaster-hub />
</body>

</html>
