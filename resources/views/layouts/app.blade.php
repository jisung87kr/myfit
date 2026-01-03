<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MyFit - AI 다이어트 플래너')</title>

    <!-- Google Fonts - Outfit & Work Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/vue@2.0.18/dist/cjs/index.js" defer></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Outfit', 'sans-serif'],
                        body: ['Work Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        },
                        accent: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        success: {
                            500: '#22c55e',
                            600: '#16a34a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Work Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
        .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        /* Smooth transitions */
        * {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Subtle gradient background */
        .bg-gradient-subtle {
            background: linear-gradient(135deg, #f0fdfa 0%, #ecfeff 50%, #f8fafc 100%);
        }
        /* Glass effect for navbar */
        .nav-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gradient-subtle min-h-screen">
    <div id="app">
        @auth
        <!-- Modern Floating Navigation -->
        <nav class="nav-glass fixed top-0 left-0 right-0 z-50 border-b border-gray-100/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo & Nav Links -->
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 group">
                            <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:shadow-primary-500/40 transition-shadow duration-300">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <span class="font-heading font-bold text-xl text-gray-900">MyFit</span>
                        </a>

                        <!-- Navigation Links -->
                        <div class="hidden md:flex md:ml-10 md:space-x-1">
                            <a href="{{ route('dashboard') }}"
                               class="@if(request()->routeIs('dashboard')) bg-primary-50 text-primary-700 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>대시보드</span>
                            </a>
                            <a href="{{ route('meals.index') }}"
                               class="@if(request()->routeIs('meals.*')) bg-primary-50 text-primary-700 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>식사 기록</span>
                            </a>
                            <a href="{{ route('exercises.index') }}"
                               class="@if(request()->routeIs('exercises.*')) bg-primary-50 text-primary-700 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>운동</span>
                            </a>
                            <a href="{{ route('weight.index') }}"
                               class="@if(request()->routeIs('weight.*')) bg-primary-50 text-primary-700 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                                <span>체중</span>
                            </a>
                            <a href="{{ route('diet-plan.index') }}"
                               class="@if(request()->routeIs('diet-plan.*')) bg-primary-50 text-primary-700 @else text-gray-600 hover:text-gray-900 hover:bg-gray-50 @endif px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span>플랜</span>
                            </a>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-3 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white text-sm font-medium shadow-md">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl ring-1 ring-black/5 py-2 z-50"
                                 style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>프로필 설정</span>
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center space-x-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>로그아웃</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center md:hidden">
                        <button type="button" class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors" x-data @click="$dispatch('toggle-mobile-menu')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="md:hidden border-t border-gray-100" x-data="{ open: false }" @toggle-mobile-menu.window="open = !open" x-show="open" x-collapse>
                <div class="px-4 py-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) bg-primary-50 text-primary-700 @else text-gray-600 @endif block px-4 py-3 rounded-xl text-sm font-medium">대시보드</a>
                    <a href="{{ route('meals.index') }}" class="@if(request()->routeIs('meals.*')) bg-primary-50 text-primary-700 @else text-gray-600 @endif block px-4 py-3 rounded-xl text-sm font-medium">식사 기록</a>
                    <a href="{{ route('exercises.index') }}" class="@if(request()->routeIs('exercises.*')) bg-primary-50 text-primary-700 @else text-gray-600 @endif block px-4 py-3 rounded-xl text-sm font-medium">운동</a>
                    <a href="{{ route('weight.index') }}" class="@if(request()->routeIs('weight.*')) bg-primary-50 text-primary-700 @else text-gray-600 @endif block px-4 py-3 rounded-xl text-sm font-medium">체중</a>
                    <a href="{{ route('diet-plan.index') }}" class="@if(request()->routeIs('diet-plan.*')) bg-primary-50 text-primary-700 @else text-gray-600 @endif block px-4 py-3 rounded-xl text-sm font-medium">플랜</a>
                </div>
            </div>
        </nav>
        @endauth

        <!-- Page Content -->
        <main class="@auth pt-20 pb-8 @endauth">
            @yield('content')
        </main>

        <!-- Toast Notification -->
        <div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 p-4 flex items-center space-x-3 max-w-sm">
                <div id="toast-icon" class="w-10 h-10 rounded-full flex items-center justify-center"></div>
                <p id="toast-message" class="text-sm font-medium text-gray-900"></p>
            </div>
        </div>
    </div>

    <!-- Alpine.js for interactions -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Global Scripts -->
    <script>
        // Axios setup
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.baseURL = '/api';

        @auth
        const token = localStorage.getItem('auth_token') || '{{ session("auth_token") }}';
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        @endauth

        // Toast function
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const msg = document.getElementById('toast-message');

            msg.textContent = message;
            icon.innerHTML = type === 'success'
                ? '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            icon.className = `w-10 h-10 rounded-full flex items-center justify-center ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        window.showToast = showToast;
    </script>

    @stack('scripts')
</body>
</html>