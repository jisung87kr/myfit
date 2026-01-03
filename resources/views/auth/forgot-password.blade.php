@extends('layouts.guest')

@section('title', '비밀번호 찾기 - MyFit')

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
                비밀번호를 잊으셨나요?
            </h2>
            <p class="text-gray-500">
                가입하신 이메일을 입력하시면 재설정 링크를 보내드립니다.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white p-8 sm:p-10 shadow-xl shadow-gray-200/50 rounded-[2rem] border border-gray-100">
            <div id="forgot-password-app">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            이메일 주소
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

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-4 px-6 bg-gray-900 text-white font-bold rounded-2xl shadow-lg shadow-gray-900/20 hover:bg-black hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-300 cursor-pointer"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            전송 중...
                        </span>
                        <span v-else>비밀번호 재설정 링크 보내기</span>
                    </button>

                    <!-- Success Message -->
                    <div v-if="successMessage" class="p-4 bg-green-50 border border-green-100 rounded-2xl animate-pulse">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-bold text-green-700">@{{ successMessage }}</p>
                        </div>
                    </div>

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
                    비밀번호가 생각나셨나요?
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
                email: ''
            },
            loading: false,
            errors: {},
            errorMessage: '',
            successMessage: ''
        }
    },
    methods: {
        async handleSubmit() {
            this.loading = true;
            this.errors = {};
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const response = await axios.post('/forgot-password', this.form);

                if (response.data.success) {
                    this.successMessage = response.data.message;
                    this.form.email = '';
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    // If errors object has 'email', show it in form, otherwise show general error
                    if (!this.errors.email) {
                         this.errorMessage = error.response.data.message || '입력값을 확인해주세요.';
                    }
                } else {
                    this.errorMessage = error.response?.data?.message || '오류가 발생했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.loading = false;
            }
        }
    }
}).mount('#forgot-password-app');
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
