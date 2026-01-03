@extends('layouts.guest')

@section('title', '로그인 - MyFit')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center space-x-2 group">
                <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-xl shadow-primary-500/20 group-hover:shadow-primary-500/40 transition-shadow duration-300">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="font-heading font-bold text-2xl text-gray-900">MyFit</span>
            </a>
            <h2 class="mt-6 text-3xl font-bold text-gray-900 font-heading">
                다시 오신 것을 환영해요
            </h2>
            <p class="mt-2 text-gray-500">
                계정에 로그인하여 건강 관리를 시작하세요
            </p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white/80 backdrop-blur-xl py-8 px-8 shadow-2xl shadow-gray-200/50 rounded-3xl border border-white/50">
            <div id="login-app">
                <form @submit.prevent="handleLogin" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            이메일
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                                placeholder="이메일을 입력하세요"
                            >
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-500">@{{ errors.email[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            비밀번호
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                                placeholder="비밀번호를 입력하세요"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-500">@{{ errors.password[0] }}</p>
                    </div>

                    <!-- Remember Me & Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 cursor-pointer"
                            >
                            <span class="ml-2 text-sm text-gray-600">로그인 상태 유지</span>
                        </label>
                        <a href="#" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                            비밀번호 찾기
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-500 to-accent-500 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 cursor-pointer"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            로그인 중...
                        </span>
                        <span v-else>로그인</span>
                    </button>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-100 rounded-xl">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700">@{{ errorMessage }}</p>
                        </div>
                    </div>
                </form>

                <!-- Social Login Divider -->
                <div class="my-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-400">또는</span>
                        </div>
                    </div>
                </div>

                <!-- Social Login Buttons -->
                <div class="grid grid-cols-3 gap-3">
                    <button @click="socialLogin('google')" class="flex items-center justify-center py-3 px-4 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M5.26620003,9.76452941 C6.19878754,6.93863203 8.85444915,4.90909091 12,4.90909091 C13.6909091,4.90909091 15.2181818,5.50909091 16.4181818,6.49090909 L19.9090909,3 C17.7818182,1.14545455 15.0545455,0 12,0 C7.27006974,0 3.1977497,2.69829785 1.23999023,6.65002441 L5.26620003,9.76452941 Z"/>
                            <path fill="#34A853" d="M16.0407269,18.0125889 C14.9509167,18.7163016 13.5660892,19.0909091 12,19.0909091 C8.86648613,19.0909091 6.21911939,17.076871 5.27698177,14.2678769 L1.23746264,17.3349879 C3.19279051,21.2936293 7.26500293,24 12,24 C14.9328362,24 17.7353462,22.9573905 19.834192,20.9995801 L16.0407269,18.0125889 Z"/>
                            <path fill="#4A90E2" d="M19.834192,20.9995801 C22.0291676,18.9520994 23.4545455,15.903663 23.4545455,12 C23.4545455,11.2909091 23.3454545,10.5272727 23.1818182,9.81818182 L12,9.81818182 L12,14.4545455 L18.4363636,14.4545455 C18.1187732,16.013626 17.2662994,17.2212117 16.0407269,18.0125889 L19.834192,20.9995801 Z"/>
                            <path fill="#FBBC05" d="M5.27698177,14.2678769 C5.03832634,13.556323 4.90909091,12.7937589 4.90909091,12 C4.90909091,11.2182781 5.03443647,10.4668121 5.26620003,9.76452941 L1.23999023,6.65002441 C0.43658717,8.26043162 0,10.0753848 0,12 C0,13.9195484 0.444780743,15.7## 1.23746264,17.3349879 L5.27698177,14.2678769 Z"/>
                        </svg>
                    </button>
                    <button @click="socialLogin('kakao')" class="flex items-center justify-center py-3 px-4 border border-gray-200 rounded-xl bg-[#FEE500] hover:bg-[#FDD800] transition-colors cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#3C1E1E">
                            <path d="M12 3C6.477 3 2 6.463 2 10.691c0 2.723 1.793 5.108 4.508 6.453-.199.748-.72 2.713-.825 3.13-.13.521.192.513.403.373.165-.11 2.627-1.786 3.696-2.51.714.104 1.453.159 2.218.159 5.523 0 10-3.463 10-7.605C22 6.463 17.523 3 12 3z"/>
                        </svg>
                    </button>
                    <button @click="socialLogin('naver')" class="flex items-center justify-center py-3 px-4 border border-gray-200 rounded-xl bg-[#03C75A] hover:bg-[#02B350] transition-colors cursor-pointer">
                        <span class="text-white font-bold text-lg">N</span>
                    </button>
                </div>

                <!-- Register Link -->
                <p class="mt-8 text-center text-sm text-gray-500">
                    계정이 없으신가요?
                    <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                        회원가입
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
Vue.createApp({
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
                    localStorage.setItem('auth_token', response.data.data.token);
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
