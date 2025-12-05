@extends('layouts.app')

@section('title', '로그인 - MyFit')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/10 to-secondary/10 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Title -->
        <div class="text-center">
            <h1 class="text-4xl font-bold text-primary mb-2">
                <i class="fas fa-heartbeat"></i> MyFit
            </h1>
            <h2 class="text-3xl font-extrabold text-gray-900">
                로그인
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                AI 기반 맞춤 다이어트 플래너
            </p>
        </div>

        <!-- Login Form -->
        <div class="mt-8 bg-white py-8 px-6 shadow-xl rounded-lg sm:px-10">
            <div id="login-app">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            이메일
                        </label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="이메일을 입력하세요"
                            >
                        </div>
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">@{{ errors.email[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호
                        </label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="비밀번호를 입력하세요"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">@{{ errors.password[0] }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                            >
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                로그인 상태 유지
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-primary hover:text-primary/80">
                                비밀번호 찾기
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="loading"><i class="fas fa-spinner fa-spin mr-2"></i>로그인 중...</span>
                            <span v-else>로그인</span>
                        </button>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800">@{{ errorMessage }}</p>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Social Login -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">또는</span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <button @click="socialLogin('google')" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fab fa-google text-red-500"></i>
                        </button>
                        <button @click="socialLogin('kakao')" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-comment text-yellow-500"></i>
                        </button>
                        <button @click="socialLogin('naver')" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="text-green-500 font-bold">N</span>
                        </button>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        계정이 없으신가요?
                        <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary/80">
                            회원가입
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            form: {
                email: '',
                password: '',
                remember: false
            },
            loading: false,
            errors: {},
            errorMessage: ''
        }
    },
    methods: {
        async handleLogin() {
            this.loading = true;
            this.errors = {};
            this.errorMessage = '';

            try {
                const response = await axios.post('/login', this.form);

                if (response.data.success) {
                    // Store token
                    localStorage.setItem('auth_token', response.data.data.token);

                    // Redirect to dashboard
                    window.location.href = '{{ route("dashboard") }}';
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.data || {};
                } else if (error.response && error.response.status === 401) {
                    this.errorMessage = '이메일 또는 비밀번호가 올바르지 않습니다.';
                } else {
                    this.errorMessage = '로그인 중 오류가 발생했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.loading = false;
            }
        },
        socialLogin(provider) {
            window.location.href = `/api/auth/${provider}/redirect`;
        }
    }
}).mount('#login-app');
</script>
@endpush
