@extends('layouts.app')

@section('title', '회원가입 - MyFit')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/10 to-secondary/10 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Title -->
        <div class="text-center">
            <h1 class="text-4xl font-bold text-primary mb-2">
                <i class="fas fa-heartbeat"></i> MyFit
            </h1>
            <h2 class="text-3xl font-extrabold text-gray-900">
                회원가입
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                건강한 다이어트를 시작하세요
            </p>
        </div>

        <!-- Register Form -->
        <div class="mt-8 bg-white py-8 px-6 shadow-xl rounded-lg sm:px-10">
            <div id="register-app">
                <form @submit.prevent="handleRegister" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            이름
                        </label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="이름을 입력하세요"
                            >
                        </div>
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">@{{ errors.name[0] }}</p>
                    </div>

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
                                placeholder="비밀번호를 입력하세요 (최소 8자)"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">@{{ errors.password[0] }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            비밀번호 확인
                        </label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="비밀번호를 다시 입력하세요"
                            >
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-center">
                        <input
                            id="terms"
                            v-model="form.terms"
                            type="checkbox"
                            required
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        >
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            <a href="#" class="text-primary hover:text-primary/80">이용약관</a> 및
                            <a href="#" class="text-primary hover:text-primary/80">개인정보처리방침</a>에 동의합니다
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="loading || !form.terms"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="loading"><i class="fas fa-spinner fa-spin mr-2"></i>가입 중...</span>
                            <span v-else>회원가입</span>
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

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        이미 계정이 있으신가요?
                        <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/80">
                            로그인
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
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                terms: false
            },
            loading: false,
            errors: {},
            errorMessage: ''
        }
    },
    methods: {
        async handleRegister() {
            this.loading = true;
            this.errors = {};
            this.errorMessage = '';

            try {
                const response = await axios.post('/register', this.form);

                if (response.data.success) {
                    // Store token
                    localStorage.setItem('auth_token', response.data.data.token);

                    // Redirect to survey or dashboard
                    window.location.href = '{{ route("dashboard") }}';
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.data || {};
                    this.errorMessage = '입력한 정보를 확인해주세요.';
                } else {
                    this.errorMessage = '회원가입 중 오류가 발생했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.loading = false;
            }
        }
    }
}).mount('#register-app');
</script>
@endpush
