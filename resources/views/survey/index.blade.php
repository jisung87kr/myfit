@extends('layouts.app')

@section('title', '초기 설문 - MyFit')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary/10 to-secondary/10 py-12 px-4">
    <div id="survey-app" class="container mx-auto max-w-3xl">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">진행률</span>
                <span class="text-sm font-medium text-primary">@{{ currentStep }}/5</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div :style="`width: ${(currentStep / 5) * 100}%`" class="bg-primary h-3 rounded-full transition-all duration-300"></div>
            </div>
        </div>

        <!-- Survey Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Step 1: Welcome & Basic Info -->
            <div v-if="currentStep === 1" class="space-y-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hand-sparkles text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">환영합니다!</h2>
                    <p class="text-gray-600">MyFit AI가 당신만을 위한 맞춤 플랜을 만들어드립니다</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            나이 <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="survey.age"
                            required
                            min="1"
                            max="120"
                            placeholder="25"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            성별 <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <button
                                type="button"
                                @click="survey.gender = 'male'"
                                :class="['p-4 rounded-lg border-2 transition-all', survey.gender === 'male' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                            >
                                <i :class="['fas fa-mars text-3xl mb-2', survey.gender === 'male' ? 'text-primary' : 'text-gray-400']"></i>
                                <p :class="['font-semibold', survey.gender === 'male' ? 'text-primary' : 'text-gray-700']">남성</p>
                            </button>
                            <button
                                type="button"
                                @click="survey.gender = 'female'"
                                :class="['p-4 rounded-lg border-2 transition-all', survey.gender === 'female' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                            >
                                <i :class="['fas fa-venus text-3xl mb-2', survey.gender === 'female' ? 'text-primary' : 'text-gray-400']"></i>
                                <p :class="['font-semibold', survey.gender === 'female' ? 'text-primary' : 'text-gray-700']">여성</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Body Measurements -->
            <div v-if="currentStep === 2" class="space-y-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ruler-vertical text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">신체 정보</h2>
                    <p class="text-gray-600">정확한 정보로 더 나은 플랜을 받으세요</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            키 (cm) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="survey.height_cm"
                            required
                            min="0"
                            step="0.1"
                            placeholder="170"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            현재 체중 (kg) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="survey.current_weight_kg"
                            required
                            min="0"
                            step="0.1"
                            placeholder="70"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <div v-if="bmi" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">BMI (체질량지수)</span>
                            <span class="text-xl font-bold text-primary">@{{ bmi }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">@{{ bmiCategory }}</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Goals -->
            <div v-if="currentStep === 3" class="space-y-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bullseye text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">목표 설정</h2>
                    <p class="text-gray-600">어떤 목표를 달성하고 싶으신가요?</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            주요 목표 <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <button
                                type="button"
                                v-for="goal in goalOptions"
                                :key="goal.value"
                                @click="survey.goal_type = goal.value"
                                :class="['p-4 rounded-lg border-2 transition-all text-center', survey.goal_type === goal.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                            >
                                <i :class="[goal.icon, 'text-3xl mb-2', survey.goal_type === goal.value ? 'text-primary' : 'text-gray-400']"></i>
                                <p :class="['font-semibold mb-1', survey.goal_type === goal.value ? 'text-primary' : 'text-gray-700']">@{{ goal.label }}</p>
                                <p class="text-xs text-gray-600">@{{ goal.description }}</p>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            목표 체중 (kg) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="survey.target_weight_kg"
                            required
                            min="0"
                            step="0.1"
                            placeholder="65"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        <p v-if="weightDiff" class="text-sm mt-2" :class="weightDiff > 0 ? 'text-red-600' : 'text-green-600'">
                            @{{ formatWeightDiff(weightDiff) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Activity Level -->
            <div v-if="currentStep === 4" class="space-y-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-running text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">활동 수준</h2>
                    <p class="text-gray-600">평소 활동량을 선택해주세요</p>
                </div>

                <div class="space-y-3">
                    <button
                        type="button"
                        v-for="activity in activityLevels"
                        :key="activity.value"
                        @click="survey.activity_level = activity.value"
                        :class="['w-full p-4 rounded-lg border-2 transition-all text-left', survey.activity_level === activity.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                    >
                        <div class="flex items-start gap-4">
                            <i :class="[activity.icon, 'text-2xl mt-1', survey.activity_level === activity.value ? 'text-primary' : 'text-gray-400']"></i>
                            <div class="flex-1">
                                <p :class="['font-semibold mb-1', survey.activity_level === activity.value ? 'text-primary' : 'text-gray-900']">@{{ activity.label }}</p>
                                <p class="text-sm text-gray-600">@{{ activity.description }}</p>
                            </div>
                            <i v-if="survey.activity_level === activity.value" class="fas fa-check-circle text-primary text-xl"></i>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Step 5: Dietary Preferences -->
            <div v-if="currentStep === 5" class="space-y-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-utensils text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">식단 선호도</h2>
                    <p class="text-gray-600">선호하는 식단 스타일을 선택해주세요</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            식단 스타일
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                type="button"
                                v-for="diet in dietStyles"
                                :key="diet.value"
                                @click="survey.diet_style = diet.value"
                                :class="['p-4 rounded-lg border-2 transition-all text-left', survey.diet_style === diet.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                            >
                                <div class="flex items-center gap-3">
                                    <i :class="[diet.icon, 'text-2xl', survey.diet_style === diet.value ? 'text-primary' : 'text-gray-400']"></i>
                                    <div class="flex-1">
                                        <p :class="['font-semibold', survey.diet_style === diet.value ? 'text-primary' : 'text-gray-900']">@{{ diet.label }}</p>
                                        <p class="text-xs text-gray-600">@{{ diet.description }}</p>
                                    </div>
                                    <i v-if="survey.diet_style === diet.value" class="fas fa-check-circle text-primary"></i>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            알레르기 또는 제외할 음식 (선택사항)
                        </label>
                        <textarea
                            v-model="survey.dietary_restrictions"
                            rows="3"
                            placeholder="예: 땅콩 알레르기, 유제품 제외"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mt-6 rounded-md bg-red-50 p-4 border border-red-200">
                <p class="text-sm text-red-800">@{{ errorMessage }}</p>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                <button
                    v-if="currentStep > 1"
                    @click="previousStep"
                    class="px-6 py-3 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    <i class="fas fa-arrow-left mr-2"></i>이전
                </button>
                <div v-else></div>

                <button
                    v-if="currentStep < 5"
                    @click="nextStep"
                    :disabled="!canProceed"
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    다음<i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button
                    v-else
                    @click="submitSurvey"
                    :disabled="!canProceed || submitting"
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium disabled:opacity-50"
                >
                    <span v-if="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>생성 중...</span>
                    <span v-else><i class="fas fa-check mr-2"></i>완료</span>
                </button>
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
            currentStep: 1,
            survey: {
                age: null,
                gender: '',
                height_cm: null,
                current_weight_kg: null,
                goal_type: 'weight_loss',
                target_weight_kg: null,
                activity_level: '',
                diet_style: 'balanced',
                dietary_restrictions: ''
            },
            goalOptions: [
                { value: 'weight_loss', label: '체중 감량', description: '건강한 다이어트', icon: 'fas fa-arrow-down' },
                { value: 'muscle_gain', label: '근육 증가', description: '벌크업 목표', icon: 'fas fa-arrow-up' },
                { value: 'maintenance', label: '현상 유지', description: '건강 관리', icon: 'fas fa-equals' }
            ],
            activityLevels: [
                { value: 'sedentary', label: '앉아서 생활', description: '운동을 거의 하지 않음', icon: 'fas fa-couch' },
                { value: 'lightly_active', label: '가벼운 활동', description: '주 1-3일 가벼운 운동', icon: 'fas fa-walking' },
                { value: 'moderately_active', label: '보통 활동', description: '주 3-5일 중강도 운동', icon: 'fas fa-running' },
                { value: 'very_active', label: '활발한 활동', description: '주 6-7일 고강도 운동', icon: 'fas fa-bicycle' },
                { value: 'extra_active', label: '매우 활발', description: '하루 2회 이상 운동', icon: 'fas fa-dumbbell' }
            ],
            dietStyles: [
                { value: 'balanced', label: '균형잡힌', description: '모든 영양소 골고루', icon: 'fas fa-balance-scale' },
                { value: 'low_carb', label: '저탄수화물', description: '탄수화물 제한', icon: 'fas fa-bread-slice' },
                { value: 'high_protein', label: '고단백', description: '단백질 중심', icon: 'fas fa-drumstick-bite' },
                { value: 'vegetarian', label: '채식', description: '식물성 식단', icon: 'fas fa-leaf' }
            ],
            submitting: false,
            errorMessage: ''
        }
    },
    computed: {
        bmi() {
            if (!this.survey.height_cm || !this.survey.current_weight_kg) return null;
            const heightM = this.survey.height_cm / 100;
            return (this.survey.current_weight_kg / (heightM * heightM)).toFixed(1);
        },
        bmiCategory() {
            if (!this.bmi) return '';
            const bmi = parseFloat(this.bmi);
            if (bmi < 18.5) return '저체중';
            if (bmi < 23) return '정상';
            if (bmi < 25) return '과체중';
            if (bmi < 30) return '비만';
            return '고도비만';
        },
        weightDiff() {
            if (!this.survey.current_weight_kg || !this.survey.target_weight_kg) return null;
            return this.survey.current_weight_kg - this.survey.target_weight_kg;
        },
        canProceed() {
            switch (this.currentStep) {
                case 1:
                    return this.survey.age && this.survey.gender;
                case 2:
                    return this.survey.height_cm && this.survey.current_weight_kg;
                case 3:
                    return this.survey.goal_type && this.survey.target_weight_kg;
                case 4:
                    return this.survey.activity_level;
                case 5:
                    return this.survey.diet_style;
                default:
                    return false;
            }
        }
    },
    methods: {
        nextStep() {
            if (this.canProceed && this.currentStep < 5) {
                this.currentStep++;
            }
        },
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        formatWeightDiff(diff) {
            const abs = Math.abs(diff);
            return diff > 0
                ? `현재보다 ${abs.toFixed(1)}kg 감량 목표`
                : `현재보다 ${abs.toFixed(1)}kg 증량 목표`;
        },
        async submitSurvey() {
            this.submitting = true;
            this.errorMessage = '';

            try {
                const response = await axios.post('/survey/complete', this.survey);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '설문이 완료되었습니다! AI가 맞춤 플랜을 생성합니다.', type: 'success' }
                    }));

                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard") }}';
                    }, 2000);
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.errorMessage = Object.values(errors).flat().join(' ');
                } else {
                    this.errorMessage = '설문 제출에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.submitting = false;
            }
        }
    }
}).mount('#survey-app');
</script>
@endpush
