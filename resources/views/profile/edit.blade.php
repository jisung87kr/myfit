@extends('layouts.app')

@section('title', '프로필 관리 - MyFit')

@section('content')
<div class="max-w-5xl mx-auto">
    <div id="profile-app">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 font-heading flex items-center">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                프로필 관리
            </h1>
            <p class="text-gray-500 mt-1 ml-13">개인정보 및 목표를 관리하세요</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-16">
            <div class="w-12 h-12 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-500">프로필을 불러오는 중...</p>
        </div>

        <!-- Profile Content -->
        <div v-else class="space-y-6">
            <!-- Profile Information Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 font-heading flex items-center">
                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    기본 정보
                </h2>

                <form @submit.prevent="updateProfile">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                이름 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="profileForm.name"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                이메일
                            </label>
                            <input
                                type="email"
                                :value="profileForm.email"
                                readonly
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                나이
                            </label>
                            <input
                                type="number"
                                v-model.number="profileForm.age"
                                min="1"
                                max="120"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                성별
                            </label>
                            <select
                                v-model="profileForm.gender"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                                <option value="">선택 안함</option>
                                <option value="male">남성</option>
                                <option value="female">여성</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                키 (cm)
                            </label>
                            <input
                                type="number"
                                v-model.number="profileForm.height_cm"
                                min="0"
                                step="0.1"
                                placeholder="170"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                현재 체중 (kg)
                            </label>
                            <input
                                type="number"
                                v-model.number="profileForm.current_weight_kg"
                                min="0"
                                step="0.1"
                                placeholder="70"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>

                    <div v-if="profileError" class="mt-4 p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-sm text-red-700">@{{ profileError }}</p>
                    </div>

                    <div v-if="profileSuccess" class="mt-4 p-4 bg-green-50 border border-green-100 rounded-xl">
                        <p class="text-sm text-green-700">@{{ profileSuccess }}</p>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="updatingProfile"
                            class="px-6 py-3 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-primary-500/25 transition-all disabled:opacity-50 cursor-pointer"
                        >
                            <span v-if="updatingProfile">저장 중...</span>
                            <span v-else>저장하기</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Goals Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 font-heading flex items-center">
                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    목표 설정
                </h2>

                <form @submit.prevent="updateGoals">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                목표
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button
                                    type="button"
                                    v-for="goal in goalTypes"
                                    :key="goal.value"
                                    @click="goalsForm.goal_type = goal.value"
                                    :class="['p-4 rounded-xl border-2 transition-all text-center cursor-pointer', goalsForm.goal_type === goal.value ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <component :is="goal.iconComponent" :class="['w-8 h-8 mx-auto mb-2', goalsForm.goal_type === goal.value ? 'text-primary-600' : 'text-gray-400']"></component>
                                    <p :class="['font-semibold', goalsForm.goal_type === goal.value ? 'text-primary-600' : 'text-gray-700']">@{{ goal.label }}</p>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                목표 체중 (kg)
                            </label>
                            <input
                                type="number"
                                v-model.number="goalsForm.target_weight_kg"
                                min="0"
                                step="0.1"
                                placeholder="65"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                목표 칼로리 (kcal/일)
                            </label>
                            <input
                                type="number"
                                v-model.number="goalsForm.target_calories"
                                min="0"
                                placeholder="2000"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                활동 수준
                            </label>
                            <select
                                v-model="goalsForm.activity_level"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                                <option value="">선택 안함</option>
                                <option value="sedentary">앉아서 생활 (운동 거의 안함)</option>
                                <option value="lightly_active">가벼운 활동 (주 1-3일 운동)</option>
                                <option value="moderately_active">보통 활동 (주 3-5일 운동)</option>
                                <option value="very_active">활발한 활동 (주 6-7일 운동)</option>
                                <option value="extra_active">매우 활발 (하루 2회 운동)</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="goalsError" class="mt-4 p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-sm text-red-700">@{{ goalsError }}</p>
                    </div>

                    <div v-if="goalsSuccess" class="mt-4 p-4 bg-green-50 border border-green-100 rounded-xl">
                        <p class="text-sm text-green-700">@{{ goalsSuccess }}</p>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="updatingGoals"
                            class="px-6 py-3 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-primary-500/25 transition-all disabled:opacity-50 cursor-pointer"
                        >
                            <span v-if="updatingGoals">저장 중...</span>
                            <span v-else>목표 저장</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 font-heading flex items-center">
                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    비밀번호 변경
                </h2>

                <form @submit.prevent="updatePassword">
                    <div class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                현재 비밀번호 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.current_password"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.new_password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                            <p class="text-xs text-gray-500 mt-1">최소 8자 이상</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호 확인 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.new_password_confirmation"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div v-if="passwordError" class="p-4 bg-red-50 border border-red-100 rounded-xl">
                            <p class="text-sm text-red-700">@{{ passwordError }}</p>
                        </div>

                        <div v-if="passwordSuccess" class="p-4 bg-green-50 border border-green-100 rounded-xl">
                            <p class="text-sm text-green-700">@{{ passwordSuccess }}</p>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="updatingPassword"
                                class="px-6 py-3 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-primary-500/25 transition-all disabled:opacity-50 cursor-pointer"
                            >
                                <span v-if="updatingPassword">변경 중...</span>
                                <span v-else>비밀번호 변경</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-6">
                <h2 class="text-lg font-semibold text-red-600 mb-4 font-heading flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    위험 구역
                </h2>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="font-medium text-gray-900">계정 삭제</p>
                        <p class="text-sm text-gray-600 mt-1">계정을 삭제하면 모든 데이터가 영구적으로 삭제됩니다.</p>
                    </div>
                    <button
                        @click="showDeleteModal = true"
                        class="px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors whitespace-nowrap font-medium cursor-pointer"
                    >
                        계정 삭제
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 font-heading">계정을 삭제하시겠습니까?</h3>
                    <p class="text-sm text-gray-600">
                        이 작업은 되돌릴 수 없습니다.<br>
                        모든 데이터가 영구적으로 삭제됩니다.
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        확인을 위해 "삭제"를 입력하세요
                    </label>
                    <input
                        type="text"
                        v-model="deleteConfirmText"
                        placeholder="삭제"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                </div>

                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium cursor-pointer"
                    >
                        취소
                    </button>
                    <button
                        @click="deleteAccount"
                        :disabled="deleteConfirmText !== '삭제' || deleting"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 font-medium cursor-pointer"
                    >
                        <span v-if="deleting">삭제 중...</span>
                        <span v-else>계정 삭제</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Goal Type Icon Components
const ArrowDownIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>`
};

const ArrowUpIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>`
};

const EqualsIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M20 8H4"/></svg>`
};

Vue.createApp({
    components: {
        ArrowDownIcon,
        ArrowUpIcon,
        EqualsIcon
    },
    data() {
        return {
            loading: false,
            profileForm: {
                name: '',
                email: '',
                age: null,
                gender: '',
                height_cm: null,
                current_weight_kg: null
            },
            goalsForm: {
                goal_type: 'weight_loss',
                target_weight_kg: null,
                target_calories: null,
                activity_level: ''
            },
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            goalTypes: [
                { value: 'weight_loss', label: '체중 감량', iconComponent: 'ArrowDownIcon' },
                { value: 'muscle_gain', label: '근육 증가', iconComponent: 'ArrowUpIcon' },
                { value: 'maintenance', label: '현상 유지', iconComponent: 'EqualsIcon' }
            ],
            updatingProfile: false,
            updatingGoals: false,
            updatingPassword: false,
            profileError: '',
            goalsError: '',
            passwordError: '',
            profileSuccess: '',
            goalsSuccess: '',
            passwordSuccess: '',
            showDeleteModal: false,
            deleteConfirmText: '',
            deleting: false
        }
    },
    mounted() {
        this.loadProfile();
    },
    methods: {
        async loadProfile() {
            this.loading = true;
            try {
                const response = await axios.get('/user/profile');
                const data = response.data.data;

                this.profileForm = {
                    name: data.name || '',
                    email: data.email || '',
                    age: data.age,
                    gender: data.gender || '',
                    height_cm: data.height_cm,
                    current_weight_kg: data.current_weight_kg
                };

                this.goalsForm = {
                    goal_type: data.goal_type || 'weight_loss',
                    target_weight_kg: data.target_weight_kg,
                    target_calories: data.target_calories,
                    activity_level: data.activity_level || ''
                };
            } catch (error) {
                console.error('Failed to load profile:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '프로필을 불러오는데 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.loading = false;
            }
        },
        async updateProfile() {
            this.updatingProfile = true;
            this.profileError = '';
            this.profileSuccess = '';

            try {
                const response = await axios.put('/user/profile', this.profileForm);

                if (response.data.success) {
                    this.profileSuccess = '프로필이 저장되었습니다.';
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '프로필이 저장되었습니다.', type: 'success' }
                    }));
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.profileError = Object.values(errors).flat().join(' ');
                } else {
                    this.profileError = '프로필 저장에 실패했습니다.';
                }
            } finally {
                this.updatingProfile = false;
            }
        },
        async updateGoals() {
            this.updatingGoals = true;
            this.goalsError = '';
            this.goalsSuccess = '';

            try {
                const response = await axios.put('/user/goals', this.goalsForm);

                if (response.data.success) {
                    this.goalsSuccess = '목표가 저장되었습니다.';
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '목표가 저장되었습니다.', type: 'success' }
                    }));
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.goalsError = Object.values(errors).flat().join(' ');
                } else {
                    this.goalsError = '목표 저장에 실패했습니다.';
                }
            } finally {
                this.updatingGoals = false;
            }
        },
        async updatePassword() {
            this.updatingPassword = true;
            this.passwordError = '';
            this.passwordSuccess = '';

            try {
                const response = await axios.put('/user/password', this.passwordForm);

                if (response.data.success) {
                    this.passwordSuccess = '비밀번호가 변경되었습니다.';
                    this.passwordForm = {
                        current_password: '',
                        new_password: '',
                        new_password_confirmation: ''
                    };
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '비밀번호가 변경되었습니다.', type: 'success' }
                    }));
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.passwordError = Object.values(errors).flat().join(' ');
                } else {
                    this.passwordError = '비밀번호 변경에 실패했습니다.';
                }
            } finally {
                this.updatingPassword = false;
            }
        },
        async deleteAccount() {
            if (this.deleteConfirmText !== '삭제') return;

            this.deleting = true;
            try {
                await axios.delete('/user/account');

                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '계정이 삭제되었습니다.', type: 'success' }
                }));

                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } catch (error) {
                console.error('Failed to delete account:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '계정 삭제에 실패했습니다.', type: 'error' }
                }));
                this.showDeleteModal = false;
            } finally {
                this.deleting = false;
            }
        }
    }
}).mount('#profile-app');
</script>
@endpush
