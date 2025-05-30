<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>TrackWise</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600&display=swap"
        rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom animations and styles */
        .bg-gradient-custom {
            background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);
        }

        .bg-gradient-card {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delay-1 {
            animation: float 6s ease-in-out 1s infinite;
        }

        .animate-float-delay-2 {
            animation: float 6s ease-in-out 2s infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-spin-slow {
            animation: spin 20s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .blur-backdrop {
            backdrop-filter: blur(8px);
        }

        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-image: linear-gradient(to right, #0ea5e9, #10b981);
        }

        /* Add scroll reveal animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="antialiased font-sans bg-slate-50 dark:bg-slate-900">
    <!-- Background elements -->
    <div class="fixed inset-0 overflow-hidden -z-10">
        <div
            class="absolute w-[500px] h-[500px] bg-sky-400/30 rounded-full blur-3xl top-[-100px] left-[-200px] animate-spin-slow">
        </div>
        <div
            class="absolute w-[600px] h-[600px] bg-emerald-400/20 rounded-full blur-3xl bottom-[-200px] right-[-200px] animate-spin-slow">
        </div>
    </div>

    <!-- Hero section -->
    <div class="relative min-h-screen">
        <header class="relative z-10 pt-6 px-6 lg:px-8">
            <nav class="flex items-center justify-between mx-auto max-w-7xl">
                <div class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-lg bg-gradient-custom flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-slate-800 dark:text-white">TrackWise</span>
                </div>

                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        <div class="flex items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="px-4 py-2 font-medium text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="px-4 py-2 font-medium text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white border border-slate-200 dark:border-slate-700 rounded-lg">Log
                                    in</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="px-4 py-2 rounded-lg bg-gradient-custom text-white font-medium shadow-md hover:shadow-lg transition-all">Register</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </nav>
        </header>

        <main class="relative overflow-hidden">
            <!-- Hero content -->
            <div class="px-6 pt-14 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                        <div class="lg:col-span-6 px-0 sm:px-6 flex flex-col justify-center">
                            <div>
                                <h1
                                    class="text-4xl font-bold tracking-tight text-slate-800 dark:text-white sm:text-5xl md:text-6xl">
                                    <span class="text-gradient">TrackWise</span>
                                </h1>
                                <p class="mt-6 text-lg text-slate-600 dark:text-slate-300">
                                    TrackWise provides expense monitoring and tracking tools to help you manage
                                    your finances, track spending, and improve budgeting.
                                </p>
                                <div class="mt-10 flex gap-4">
                                    <a href="#features"
                                        class="px-6 py-3 rounded-lg bg-gradient-custom text-white font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                                        Get Started
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 lg:mt-0 lg:col-span-6 relative">
                            <!-- Hero illustration/dashboard mockup -->
                            <div class="relative h-[500px] w-full flex items-center justify-center">
                                <div
                                    class="absolute z-10 w-72 h-60 bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-4 transform rotate-3 animate-float">
                                    <div class="h-4 w-24 bg-sky-200 dark:bg-sky-900 rounded mb-4"></div>
                                    <div class="h-20 w-full bg-gradient-custom rounded-lg"></div>
                                    <div class="mt-4 flex gap-2">
                                        <div class="h-8 w-8 rounded-full bg-emerald-200 dark:bg-emerald-900"></div>
                                        <div class="h-8 w-20 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                    </div>
                                </div>
                                <div
                                    class="absolute z-20 w-64 h-56 bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-4 top-40 right-10 transform -rotate-6 animate-float-delay-1">
                                    <div class="h-4 w-16 bg-emerald-200 dark:bg-emerald-900 rounded mb-4"></div>
                                    <div
                                        class="h-16 w-full bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-8 w-8 text-slate-400 dark:text-slate-500" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <div class="h-6 w-full bg-sky-200 dark:bg-sky-900 rounded"></div>
                                        <div class="h-6 w-full bg-emerald-200 dark:bg-emerald-900 rounded"></div>
                                    </div>
                                </div>
                                <div
                                    class="absolute z-0 w-56 h-52 bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-4 bottom-10 left-10 transform rotate-12 animate-float-delay-2">
                                    <div class="h-4 w-20 bg-sky-200 dark:bg-sky-900 rounded mb-4"></div>
                                    <div class="flex gap-2 mb-3">
                                        <div class="h-6 w-6 rounded-full bg-emerald-400"></div>
                                        <div class="h-6 w-28 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                    </div>
                                    <div class="h-14 w-full bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                                    <div class="mt-3 h-10 w-full bg-gradient-custom rounded-lg"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features section -->
            <div id="features" class="py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-slate-800 dark:text-white sm:text-4xl reveal">
                            Simple Features for Expense Tracking
                        </h2>
                        <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-300 reveal">
                            TrackWise offers comprehensive tools to manage your finances and gain insights into your
                            spending habits.
                        </p>
                    </div>

                    <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Feature 1 -->
                        <div
                            class="flex flex-col rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 reveal">
                            <div class="h-12 w-12 rounded-xl bg-gradient-custom flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Expense Tracking</h3>
                            <p class="mt-4 flex-1 text-slate-600 dark:text-slate-300">
                                Record and categorize your expenses with ease. Track every transaction with date,
                                category, and payment method.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div
                            class="flex flex-col rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 reveal">
                            <div class="h-12 w-12 rounded-xl bg-gradient-custom flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Income Tracking</h3>
                            <p class="mt-4 flex-1 text-slate-600 dark:text-slate-300">
                                Monitor all your income sources in one place. Easily record salary, freelance work, and
                                investments.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div
                            class="flex flex-col rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 reveal">
                            <div class="h-12 w-12 rounded-xl bg-gradient-custom flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Spending Insights</h3>
                            <p class="mt-4 flex-1 text-slate-600 dark:text-slate-300">
                                Visualize your spending patterns with simple charts and identify opportunities to save
                                with our intuitive dashboard.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA section -->
            <div class="mt-24 mb-24 reveal">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div
                        class="mx-auto max-w-2xl rounded-2xl bg-gradient-custom p-10 text-center text-white shadow-xl">
                        <h2 class="text-2xl font-bold sm:text-3xl">Ready to take control of your finances?</h2>
                        <p class="mt-4 text-lg">
                            Join TrackWise today and start tracking your expenses.
                        </p>
                        <div class="mt-8">
                            <a href="#"
                                class="inline-block rounded-lg bg-white px-6 py-3 text-base font-medium text-sky-600 shadow-sm hover:bg-slate-50 transition-all transform hover:-translate-y-1">
                                Start Free Trial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-200 dark:border-slate-800 py-12">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center gap-2 mb-6 md:mb-0">
                        <div class="h-8 w-8 rounded-lg bg-gradient-custom flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <span class="text-base font-bold text-slate-800 dark:text-white">TrackWise</span>
                    </div>

                    <div class="text-center md:text-right text-sm text-slate-500 dark:text-slate-400">
                        &copy; {{ date('Y') }} TrackWise. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Simple scroll reveal
        document.addEventListener('DOMContentLoaded', function() {
            const reveals = document.querySelectorAll('.reveal');

            function revealContent() {
                reveals.forEach(reveal => {
                    const windowHeight = window.innerHeight;
                    const revealTop = reveal.getBoundingClientRect().top;
                    const revealPoint = 150;

                    if (revealTop < windowHeight - revealPoint) {
                        reveal.classList.add('active');
                    }
                });
            }

            window.addEventListener('scroll', revealContent);
            // Trigger once on load
            revealContent();
        });
    </script>
</body>

</html>
