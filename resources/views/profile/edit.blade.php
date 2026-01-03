@extends('layouts.app')

@section('title', '프로필 관리 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div id="profile-app">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-circle text-primary mr-2"></i>프로필 관리
            </h1>
            <p class="text-sm text-gray-600 mt-1">개인정보 및 목표를 관리하세요</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">프로필을 불러오는 중...</p>
        </div>

        <!-- Profile Content -->
        <div v-else class="space-y-6">
            <!-- Profile Information Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-id-card text-primary mr-2"></i>기본 정보
                </h2>

                <form @submit.prevent="updateProfile">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                이름 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="profileForm.name"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Email (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                이메일
                            </label>
                            <input
                                type="email"
                                :value="profileForm.email"
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                            >
                        </div>

                        <!-- Age -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                나이
                            </label>
                            <input
                                type="number"
                                v-model.number="profileForm.age"
                                min="1"
                                max="120"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                성별
                            </label>
                            <select
                                v-model="profileForm.gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                                <option value="">선택 안함</option>
                                <option value="male">남성</option>
                                <option value="female">여성</option>
                            </select>
                        </div>

                        <!-- Height -->
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Current Weight -->
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="profileError" class="mt-4 rounded-md bg-red-50 p-4 border border-red-200">
                        <p class="text-sm text-red-800">@{{ profileError }}</p>
                    </div>

                    <!-- Success Message -->
                    <div v-if="profileSuccess" class="mt-4 rounded-md bg-green-50 p-4 border border-green-200">
                        <p class="text-sm text-green-800">@{{ profileSuccess }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="updatingProfile"
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                        >
                            <span v-if="updatingProfile"><i class="fas fa-spinner fa-spin mr-2"></i>저장 중...</span>
                            <span v-else><i class="fas fa-save mr-2"></i>저장하기</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Goals Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-bullseye text-primary mr-2"></i>목표 설정
                </h2>

                <form @submit.prevent="updateGoals">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Goal Type -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                목표
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button
                                    type="button"
                                    v-for="goal in goalTypes"
                                    :key="goal.value"
                                    @click="goalsForm.goal_type = goal.value"
                                    :class="['p-4 rounded-lg border-2 transition-all text-center', goalsForm.goal_type === goal.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <i :class="[goal.icon, 'text-2xl mb-2', goalsForm.goal_type === goal.value ? 'text-primary' : 'text-gray-400']"></i>
                                    <p :class="['font-semibold', goalsForm.goal_type === goal.value ? 'text-primary' : 'text-gray-700']">@{{ goal.label }}</p>
                                </button>
                            </div>
                        </div>

                        <!-- Target Weight -->
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Target Calories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                목표 칼로리 (kcal/일)
                            </label>
                            <input
                                type="number"
                                v-model.number="goalsForm.target_calories"
                                min="0"
                                placeholder="2000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Activity Level -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                활동 수준
                            </label>
                            <select
                                v-model="goalsForm.activity_level"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
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

                    <!-- Error Message -->
                    <div v-if="goalsError" class="mt-4 rounded-md bg-red-50 p-4 border border-red-200">
                        <p class="text-sm text-red-800">@{{ goalsError }}</p>
                    </div>

                    <!-- Success Message -->
                    <div v-if="goalsSuccess" class="mt-4 rounded-md bg-green-50 p-4 border border-green-200">
                        <p class="text-sm text-green-800">@{{ goalsSuccess }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="updatingGoals"
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                        >
                            <span v-if="updatingGoals"><i class="fas fa-spinner fa-spin mr-2"></i>저장 중...</span>
                            <span v-else><i class="fas fa-save mr-2"></i>목표 저장</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-lock text-primary mr-2"></i>비밀번호 변경
                </h2>

                <form @submit.prevent="updatePassword">
                    <div class="space-y-4 max-w-md">
                        <!-- Current Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                현재 비밀번호 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.current_password"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.new_password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                            <p class="text-xs text-gray-500 mt-1">최소 8자 이상</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                새 비밀번호 확인 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="passwordForm.new_password_confirmation"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Error Message -->
                        <div v-if="passwordError" class="rounded-md bg-red-50 p-4 border border-red-200">
                            <p class="text-sm text-red-800">@{{ passwordError }}</p>
                        </div>

                        <!-- Success Message -->
                        <div v-if="passwordSuccess" class="rounded-md bg-green-50 p-4 border border-green-200">
                            <p class="text-sm text-green-800">@{{ passwordSuccess }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                                type="submit"
                                :disabled="updatingPassword"
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                            >
                                <span v-if="updatingPassword"><i class="fas fa-spinner fa-spin mr-2"></i>변경 중...</span>
                                <span v-else><i class="fas fa-key mr-2"></i>비밀번호 변경</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                <h2 class="text-lg font-semibold text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>위험 구역
                </h2>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="font-medium text-gray-900">계정 삭제</p>
                        <p class="text-sm text-gray-600 mt-1">계정을 삭제하면 모든 데이터가 영구적으로 삭제됩니다.</p>
                    </div>
                    <button
                        @click="showDeleteModal = true"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors whitespace-nowrap"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>계정 삭제
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">계정을 삭제하시겠습니까?</h3>
                    <p class="text-sm text-gray-600">
                        이 작업은 되돌릴 수 없습니다.<br>
                        모든 데이터가 영구적으로 삭제됩니다.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        확인을 위해 "삭제"를 입력하세요
                    </label>
                    <input
                        type="text"
                        v-model="deleteConfirmText"
                        placeholder="삭제"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                </div>

                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        취소
                    </button>
                    <button
                        @click="deleteAccount"
                        :disabled="deleteConfirmText !== '삭제' || deleting"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        <span v-if="deleting"><i class="fas fa-spinner fa-spin mr-1"></i>삭제 중...</span>
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
Vue.createApp({
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
                { value: 'weight_loss', label: '체중 감량', icon: 'fas fa-arrow-down' },
                { value: 'muscle_gain', label: '근육 증가', icon: 'fas fa-arrow-up' },
                { value: 'maintenance', label: '현상 유지', icon: 'fas fa-equals' }
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
