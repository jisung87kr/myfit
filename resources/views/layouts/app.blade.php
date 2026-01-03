<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MyFit - AI 다이어트 플래너')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981',
                        secondary: '#3b82f6',
                    }
                }
            }
        }
    </script>

    <!-- Custom Styles -->
    <style>
        [v-cloak] {
            display: none;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="app" v-cloak>
        <!-- Navigation -->
        @auth
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-primary">
                                <i class="fas fa-heartbeat"></i> MyFit
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}"
                               class="@if(request()->routeIs('dashboard')) border-primary text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-home mr-2"></i> 대시보드
                            </a>
                            <a href="{{ route('meals.index') }}"
                               class="@if(request()->routeIs('meals.*')) border-primary text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-utensils mr-2"></i> 식사 기록
                            </a>
                            <a href="{{ route('exercises.index') }}"
                               class="@if(request()->routeIs('exercises.*')) border-primary text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-running mr-2"></i> 운동 기록
                            </a>
                            <a href="{{ route('weight.index') }}"
                               class="@if(request()->routeIs('weight.*')) border-primary text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-weight mr-2"></i> 체중 기록
                            </a>
                            <a href="{{ route('diet-plan.index') }}"
                               class="@if(request()->routeIs('diet-plan.*')) border-primary text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-calendar-alt mr-2"></i> 다이어트 플랜
                            </a>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div>
                                <button @click="toggleDropdown" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <span class="mr-2 text-gray-700">{{ auth()->user()->name }}</span>
                                    <i class="fas fa-user-circle text-2xl text-gray-600"></i>
                                </button>
                            </div>

                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div v-if="dropdownOpen"
                                     v-click-outside="closeDropdown"
                                     class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i> 프로필
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i> 로그아웃
                                        </button>
                                    </form>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <!-- Page Content -->
        <main class="@auth py-6 @endauth">
            @yield('content')
        </main>

        <!-- Toast Notifications -->
        <div v-if="toast.show"
             class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i :class="toast.type === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500'" class="text-xl"></i>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">@{{ toast.message }}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="hideToast" class="inline-flex text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Base Vue App -->
    <script>
        const { createApp } = Vue;

        // Axios setup
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.baseURL = '/api';

        @auth
        // Set auth token from localStorage or session
        const token = localStorage.getItem('auth_token') || '{{ session("auth_token") }}';
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        @endauth

        // Click outside directive
        const clickOutside = {
            beforeMount(el, binding) {
                el._clickOutside = (event) => {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value(event);
                    }
                };
                document.addEventListener('click', el._clickOutside);
            },
            unmounted(el) {
                document.removeEventListener('click', el._clickOutside);
            }
        };

        const app = createApp({
            data() {
                return {
                    loading: false,
                    dropdownOpen: false,
                    toast: {
                        show: false,
                        message: '',
                        type: 'success'
                    }
                }
            },
            methods: {
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                },
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => this.hideToast(), 3000);
                },
                hideToast() {
                    this.toast.show = false;
                },
                formatNumber(num) {
                    return Number(num).toFixed(1);
                },
                formatDate(date) {
                    return new Date(date).toLocaleDateString('ko-KR');
                }
            }
        });

        app.directive('click-outside', clickOutside);
        app.mount('#app');
    </script>

    @stack('scripts')
</body>
</html>
