@extends('layouts.guest')

@section('title', '회원가입 - MyFit')

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
                새로운 시작을 함께해요
            </h2>
            <p class="text-gray-500">
                무료로 가입하고 스마트한 건강 관리를 시작하세요.
            </p>
        </div>

        <!-- Register Form Card -->
        <div class="bg-white p-8 sm:p-10 shadow-xl shadow-gray-200/50 rounded-[2rem] border border-gray-100">
            <div id="register-app">
                <form @submit.prevent="handleRegister" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            이름
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-primary-500">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-transparent rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 font-medium"
                                placeholder="홍길동"
                            >
                        </div>
                        <p v-if="errors.name" class="mt-2 text-sm text-red-500 font-medium ml-1">@{{ errors.name[0] }}</p>
                    </div>

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
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            비밀번호
                        </label>
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
                                placeholder="최소 8자 이상"
                            >
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-500 font-medium ml-1">@{{ errors.password[0] }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            비밀번호 확인
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-primary-500">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-transparent rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 font-medium"
                                placeholder="다시 한번 입력해주세요"
                            >
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-start ml-1">
                        <input
                            id="terms"
                            v-model="form.terms"
                            type="checkbox"
                            required
                            class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded-lg focus:ring-primary-500 cursor-pointer"
                        >
                        <label for="terms" class="ml-3 text-sm font-medium text-gray-600 cursor-pointer select-none">
                            <a href="#" class="text-primary-600 hover:underline">이용약관</a> 및 
                            <a href="#" class="text-primary-600 hover:underline">개인정보처리방침</a>에 동의합니다.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading || !form.terms"
                        class="w-full py-4 px-6 bg-gray-900 text-white font-bold rounded-2xl shadow-lg shadow-gray-900/20 hover:bg-black hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 cursor-pointer"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            가입 처리 중...
                        </span>
                        <span v-else>회원가입하기</span>
                    </button>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-100 rounded-2xl animate-pulse">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-bold text-red-600">@{{ errorMessage }}</p>
                        </div>
                    </div>
                </form>

                <!-- Login Link -->
                <p class="mt-8 text-center text-sm text-gray-500">
                    이미 계정이 있으신가요?
                    <a href="{{ route('login') }}" class="font-bold text-primary-600 hover:text-primary-700 transition-colors ml-1">
                        로그인하기
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
                    localStorage.setItem('auth_token', response.data.data.token);
                    window.location.href = '{{ route("dashboard") }}';
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.data || {};
                    this.errorMessage = '입력한 정보를 다시 확인해주세요.';
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