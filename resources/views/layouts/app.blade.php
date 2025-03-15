<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-roboto antialiased">

    <div class="drawer lg:drawer-open">
        <input id="left-sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Page Content -->
        <div class="drawer-content flex flex-col" x-data="{ isOpen: false }">
            <livewire:pages.user.containers.main-header />

            <main class="flex-1 overflow-y-auto  bg-base-200">
                {{ $slot }}
                <div class="h-16"></div>
            </main>
        </div>

        <!-- Left Sidebar -->
        <livewire:pages.user.containers.sidebar />
    </div>
</body>

</html>
