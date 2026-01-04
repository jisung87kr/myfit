@extends('layouts.guest')

@section('title', '로그인 - MyFit')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-md w-full animate-fade-in-up">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 group mb-6">
                <div class="w-14 h-14 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-xl shadow-primary-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="font-heading font-bold text-3xl text-gray-900">MyFit</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-900 font-heading tracking-tight mb-2">
                다시 만나서 반가워요!
            </h2>
            <p class="text-gray-500">
                건강한 하루를 위한 여정을 이어가세요.
            </p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white p-8 sm:p-10 shadow-xl shadow-gray-200/50 rounded-[2rem] border border-gray-100">
            <div id="login-app">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            이메일
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-primary-500">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-transparent rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 font-medium"
                                placeholder="name@example.com"
                            >
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-500 font-medium ml-1">@{{ errors.email[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2 ml-1">
                            <label for="password" class="block text-sm font-bold text-gray-700">
                                비밀번호
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                                비밀번호를 잊으셨나요?
                            </a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-primary-500">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-transparent rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 font-medium"
                                placeholder="••••••••"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-500 font-medium ml-1">@{{ errors.password[0] }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center ml-1">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded-lg focus:ring-primary-500 cursor-pointer"
                        >
                        <label for="remember" class="ml-2.5 text-sm font-medium text-gray-600 cursor-pointer select-none">
                            로그인 상태 유지
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-4 px-6 bg-gray-900 text-white font-bold rounded-2xl shadow-lg shadow-gray-900/20 hover:bg-black hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 cursor-pointer"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            로그인 중...
                        </span>
                        <span v-else>로그인</span>
                    </button>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-2xl">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-5 h-5 mt-0.5">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-red-700">@{{ errorMessage }}</p>
                        </div>
                    </div>
                </form>

                <!-- Social Login Divider -->
                <div class="my-8 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-400 font-medium">소셜 계정으로 계속하기</span>
                    </div>
                </div>

                <!-- Social Login Buttons -->
                <div class="grid grid-cols-3 gap-4">
                    <button @click="socialLogin('google')" class="flex items-center justify-center py-3 px-4 border border-gray-100 rounded-2xl bg-white hover:bg-gray-50 hover:border-gray-300 transition-all cursor-pointer group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M5.26620003,9.76452941 C6.19878754,6.93863203 8.85444915,4.90909091 12,4.90909091 C13.6909091,4.90909091 15.2181818,5.50909091 16.4181818,6.49090909 L19.9090909,3 C17.7818182,1.14545455 15.0545455,0 12,0 C7.27006974,0 3.1977497,2.69829785 1.23999023,6.65002441 L5.26620003,9.76452941 Z"/>
                            <path fill="#34A853" d="M16.0407269,18.0125889 C14.9509167,18.7163016 13.5660892,19.0909091 12,19.0909091 C8.86648613,19.0909091 6.21911939,17.076871 5.27698177,14.2678769 L1.23746264,17.3349879 C3.19279051,21.2936293 7.26500293,24 12,24 C14.9328362,24 17.7353462,22.9573905 19.834192,20.9995801 L16.0407269,18.0125889 Z"/>
                            <path fill="#4A90E2" d="M19.834192,20.9995801 C22.0291676,18.9520994 23.4545455,15.903663 23.4545455,12 C23.4545455,11.2909091 23.3454545,10.5272727 23.1818182,9.81818182 L12,9.81818182 L12,14.4545455 C18.4363636,14.4545455 C18.1187732,16.013626 17.2662994,17.2212117 16.0407269,18.0125889 L19.834192,20.9995801 Z"/>
                            <path fill="#FBBC05" d="M5.27698177,14.2678769 C5.03832634,13.556323 4.90909091,12.7937589 4.90909091,11.2182781 5.03443647,10.4668121 5.26620003,9.76452941 L1.23999023,6.65002441 C0.43658717,8.26043162 0,10.0753848 0,12 C0,13.9195484 0.444780743,15.7## 1.23746264,17.3349879 L5.27698177,14.2678769 Z"/>
                        </svg>
                    </button>
                    <button @click="socialLogin('kakao')" class="flex items-center justify-center py-3 px-4 border border-[#FEE500] rounded-2xl bg-[#FEE500] hover:bg-[#fdd835] hover:shadow-md transition-all cursor-pointer group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="#3C1E1E">
                            <path d="M12 3C6.477 3 2 6.463 2 10.691c0 2.723 1.793 5.108 4.508 6.453-.199.748-.72 2.713-.825 3.13-.13.521.192.513.403.373.165-.11 2.627-1.786 3.696-2.51.714.104 1.453.159 2.218.159 5.523 0 10-3.463 10-7.605C22 6.463 17.523 3 12 3z"/>
                        </svg>
                    </button>
                    <button @click="socialLogin('naver')" class="flex items-center justify-center py-3 px-4 border border-[#03C75A] rounded-2xl bg-[#03C75A] hover:bg-[#02b351] hover:shadow-md transition-all cursor-pointer group">
                        <span class="text-white font-black text-xl group-hover:scale-110 transition-transform">N</span>
                    </button>
                </div>

                <!-- Register Link -->
                <p class="mt-8 text-center text-sm text-gray-500">
                    아직 계정이 없으신가요?
                    <a href="{{ route('register') }}" class="font-bold text-primary-600 hover:text-primary-700 transition-colors ml-1">
                        회원가입하기
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
                    if (response.data.data?.token) {
                        localStorage.setItem('auth_token', response.data.data.token);
                    }
                    window.location.href = '{{ route("dashboard") }}';
                }
            } catch (error) {
                const status = error.response?.status;
                const data = error.response?.data;

                if (status === 422) {
                    // Validation errors
                    this.errors = data?.errors || data?.data || {};
                    if (data?.message) {
                        this.errorMessage = data.message;
                    }
                } else if (status === 401) {
                    this.errorMessage = data?.message || '이메일 또는 비밀번호가 올바르지 않습니다.';
                } else if (status === 429) {
                    this.errorMessage = '너무 많은 시도입니다. 잠시 후 다시 시도해주세요.';
                } else {
                    this.errorMessage = data?.message || '로그인 중 오류가 발생했습니다. 다시 시도해주세요.';
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

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate3d(0, 20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>
@endpush
