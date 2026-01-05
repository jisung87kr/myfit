@extends('layouts.app')

@section('title', '초기 설문 - MyFit')

@section('content')
<div class="min-h-screen  py-12 px-4 sm:px-6 lg:px-8">
    <div id="survey-app" class="max-w-4xl mx-auto">
        <!-- Header & Progress -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-gray-900 font-heading mb-4">
                <span class="text-primary-600">MyFit</span> 시작하기
            </h1>
            <div class="max-w-md mx-auto">
                <div class="flex justify-between text-xs font-medium text-gray-400 mb-2 uppercase tracking-wider">
                    <span>Start</span>
                    <span>Step @{{ currentStep }} of 5</span>
                    <span>Finish</span>
                </div>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div :style="`width: ${(currentStep / 5) * 100}%`" class="h-full bg-primary-500 rounded-full transition-all duration-500 ease-out"></div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl p-8 md:p-10 shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-gray-100 min-h-[500px] relative flex flex-col justify-between animate-fade-in-up">

            <!-- Step Content -->
            <div class="flex-grow">
                <!-- Step 1: Welcome & Basic Info -->
                <div v-if="currentStep === 1" class="space-y-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-primary-600">
                            <i class="fas fa-hand-sparkles text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">환영합니다!</h2>
                        <p class="text-gray-500">정확한 맞춤 플랜을 위해 기본 정보를 알려주세요.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                                나이
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    v-model.number="survey.age"
                                    required
                                    min="1"
                                    max="120"
                                    placeholder="25"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all text-lg font-medium placeholder-gray-400"
                                >
                                <span class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">세</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                                성별
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    @click="survey.gender = 'male'"
                                    :class="['p-4 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-center gap-2',
                                        survey.gender === 'male'
                                        ? 'border-primary-500 bg-primary-50 text-primary-700 ring-2 ring-primary-200'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-primary-200 hover:bg-gray-50']"
                                >
                                    <i class="fas fa-mars text-2xl"></i>
                                    <span class="font-bold">남성</span>
                                </button>
                                <button
                                    type="button"
                                    @click="survey.gender = 'female'"
                                    :class="['p-4 rounded-2xl border transition-all duration-300 flex flex-col items-center justify-center gap-2',
                                        survey.gender === 'female'
                                        ? 'border-primary-500 bg-primary-50 text-primary-700 ring-2 ring-primary-200'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-primary-200 hover:bg-gray-50']"
                                >
                                    <i class="fas fa-venus text-2xl"></i>
                                    <span class="font-bold">여성</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Body Measurements -->
                <div v-if="currentStep === 2" class="space-y-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-blue-600">
                            <i class="fas fa-ruler-vertical text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">신체 정보</h2>
                        <p class="text-gray-500">현재 신체 상태를 입력해주세요.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                                키
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    v-model.number="survey.height_cm"
                                    required
                                    min="0"
                                    step="0.1"
                                    placeholder="170"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-lg font-medium placeholder-gray-400"
                                >
                                <span class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                                현재 체중
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    v-model.number="survey.current_weight_kg"
                                    required
                                    min="0"
                                    step="0.1"
                                    placeholder="70"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-lg font-medium placeholder-gray-400"
                                >
                                <span class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">kg</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="bmi" class="bg-blue-50 rounded-2xl p-6 border border-blue-100 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-bold text-blue-600 block mb-1">BMI 분석</span>
                            <span class="text-2xl font-bold text-gray-900">@{{ bmi }}</span>
                        </div>
                        <div class="px-4 py-2 bg-white rounded-xl shadow-sm border border-blue-100">
                            <span class="text-blue-700 font-bold">@{{ bmiCategory }}</span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Goals -->
                <div v-if="currentStep === 3" class="space-y-8">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-orange-600">
                            <i class="fas fa-bullseye text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">목표 설정</h2>
                        <p class="text-gray-500">달성하고 싶은 목표를 선택해주세요.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button
                            type="button"
                            v-for="goal in goalOptions"
                            :key="goal.value"
                            @click="survey.goal_type = goal.value"
                            :class="['p-6 rounded-2xl border-2 transition-all duration-300 relative overflow-hidden text-left group',
                                survey.goal_type === goal.value
                                ? 'border-orange-500 bg-orange-50/50'
                                : 'border-gray-100 bg-white hover:border-orange-200 hover:shadow-md']"
                        >
                            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center mb-4 transition-colors',
                                survey.goal_type === goal.value ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-400 group-hover:bg-orange-50 group-hover:text-orange-500']">
                                <i :class="[goal.icon, 'text-xl']"></i>
                            </div>
                            <h3 :class="['font-bold text-lg mb-1', survey.goal_type === goal.value ? 'text-orange-900' : 'text-gray-900']">@{{ goal.label }}</h3>
                            <p :class="['text-sm', survey.goal_type === goal.value ? 'text-orange-700' : 'text-gray-500']">@{{ goal.description }}</p>

                            <div v-if="survey.goal_type === goal.value" class="absolute top-4 right-4 text-orange-500">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                        </button>
                    </div>

                    <div class="max-w-sm mx-auto pt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1 text-center">
                            목표 체중
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                v-model.number="survey.target_weight_kg"
                                required
                                min="0"
                                step="0.1"
                                placeholder="65"
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all text-lg font-medium placeholder-gray-400 text-center"
                            >
                            <span class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">kg</span>
                        </div>
                        <p v-if="weightDiff" class="text-center text-sm mt-3 font-medium" :class="weightDiff > 0 ? 'text-green-600' : 'text-rose-500'">
                            @{{ formatWeightDiff(weightDiff) }}
                        </p>
                    </div>
                </div>

                <!-- Step 4: Activity Level -->
                <div v-if="currentStep === 4" class="space-y-8 max-w-3xl mx-auto">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-green-600">
                            <i class="fas fa-running text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">활동 수준</h2>
                        <p class="text-gray-500">평소 하루 활동량을 선택해주세요.</p>
                    </div>

                    <div class="space-y-3">
                        <button
                            type="button"
                            v-for="activity in activityLevels"
                            :key="activity.value"
                            @click="survey.activity_level = activity.value"
                            :class="['w-full p-5 rounded-2xl border transition-all duration-300 flex items-center gap-5 text-left group hover:shadow-md',
                                survey.activity_level === activity.value
                                ? 'border-green-500 bg-green-50/30 ring-1 ring-green-500'
                                : 'border-gray-100 bg-white hover:border-green-200']"
                        >
                            <div :class="['w-14 h-14 rounded-xl flex-shrink-0 flex items-center justify-center transition-colors',
                                survey.activity_level === activity.value ? 'bg-green-100 text-green-600' : 'bg-gray-50 text-gray-400 group-hover:bg-green-50 group-hover:text-green-500']">
                                <i :class="[activity.icon, 'text-2xl']"></i>
                            </div>
                            <div class="flex-grow">
                                <h3 :class="['font-bold text-lg', survey.activity_level === activity.value ? 'text-green-900' : 'text-gray-900']">@{{ activity.label }}</h3>
                                <p :class="['text-sm mt-1', survey.activity_level === activity.value ? 'text-green-700' : 'text-gray-500']">@{{ activity.description }}</p>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                :class="survey.activity_level === activity.value ? 'border-green-500' : 'border-gray-200'">
                                <div v-if="survey.activity_level === activity.value" class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 5: Dietary Preferences -->
                <div v-if="currentStep === 5" class="space-y-8">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-purple-600">
                            <i class="fas fa-utensils text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">식단 선호도</h2>
                        <p class="text-gray-500">선호하는 식단 스타일을 알려주세요.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button
                            type="button"
                            v-for="diet in dietStyles"
                            :key="diet.value"
                            @click="survey.diet_style = diet.value"
                            :class="['p-6 rounded-2xl border transition-all duration-300 relative overflow-hidden text-left group hover:shadow-md',
                                survey.diet_style === diet.value
                                ? 'border-purple-500 bg-purple-50/30 ring-1 ring-purple-500'
                                : 'border-gray-100 bg-white hover:border-purple-200']"
                        >
                            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center mb-4 transition-colors',
                                survey.diet_style === diet.value ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-400 group-hover:bg-purple-50 group-hover:text-purple-500']">
                                <i :class="[diet.icon, 'text-xl']"></i>
                            </div>
                            <h3 :class="['font-bold text-lg mb-1', survey.diet_style === diet.value ? 'text-purple-900' : 'text-gray-900']">@{{ diet.label }}</h3>
                            <p :class="['text-sm', survey.diet_style === diet.value ? 'text-purple-700' : 'text-gray-500']">@{{ diet.description }}</p>
                        </button>
                    </div>

                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">
                            알레르기 또는 제외할 음식 (선택사항)
                        </label>
                        <textarea
                            v-model="survey.dietary_restrictions"
                            rows="3"
                            placeholder="예: 땅콩 알레르기, 유제품 제외"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none font-medium placeholder-gray-400"
                        ></textarea>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mt-6 rounded-2xl bg-rose-50 p-4 border border-rose-100 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                    <p class="text-sm text-rose-700 font-medium">@{{ errorMessage }}</p>
                </div>
            </div>

            <!-- Footer Navigation -->
            <div class="flex items-center justify-between mt-10 pt-6 border-t border-gray-100">
                <button
                    v-if="currentStep > 1"
                    @click="previousStep"
                    class="px-6 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-colors flex items-center gap-2"
                >
                    <i class="fas fa-arrow-left text-sm"></i>
                    <span>이전</span>
                </button>
                <div v-else></div>

                <button
                    v-if="currentStep < 5"
                    @click="nextStep"
                    :disabled="!canProceed"
                    class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <span>다음</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
                <button
                    v-else
                    @click="submitSurvey"
                    :disabled="!canProceed || submitting"
                    class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2"
                >
                    <span v-if="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>생성 중...</span>
                    <span v-else>완료 및 시작하기</span>
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
                { value: 'weight_loss', label: '체중 감량', description: '체지방을 줄이고 가볍게', icon: 'fas fa-arrow-down' },
                { value: 'muscle_gain', label: '근육 증가', description: '근육량을 늘리고 튼튼하게', icon: 'fas fa-arrow-up' },
                { value: 'maintenance', label: '현상 유지', description: '현재 건강 상태 유지', icon: 'fas fa-equals' }
            ],
            activityLevels: [
                { value: 'sedentary', label: '앉아서 생활', description: '운동을 거의 하지 않음', icon: 'fas fa-couch' },
                { value: 'lightly_active', label: '가벼운 활동', description: '주 1-3일 가벼운 운동', icon: 'fas fa-walking' },
                { value: 'moderately_active', label: '보통 활동', description: '주 3-5일 중강도 운동', icon: 'fas fa-running' },
                { value: 'very_active', label: '활발한 활동', description: '주 6-7일 고강도 운동', icon: 'fas fa-bicycle' },
                { value: 'extra_active', label: '매우 활발', description: '하루 2회 이상 운동', icon: 'fas fa-dumbbell' }
            ],
            dietStyles: [
                { value: 'balanced', label: '균형잡힌', description: '모든 영양소 골고루 섭취', icon: 'fas fa-balance-scale' },
                { value: 'low_carb', label: '저탄수화물', description: '탄수화물 섭취 제한', icon: 'fas fa-bread-slice' },
                { value: 'high_protein', label: '고단백', description: '단백질 위주의 식단', icon: 'fas fa-drumstick-bite' },
                { value: 'vegetarian', label: '채식', description: '육류 제외, 식물성 식단', icon: 'fas fa-leaf' }
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
                    if (window.showToast) {
                        window.showToast('설문이 완료되었습니다! 이제 맞춤 식단을 생성할 수 있습니다.', 'success');
                    }

                    setTimeout(() => {
                        window.location.href = '{{ route("diet-plan.index") }}';
                    }, 1500);
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

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
/* Disable number input spinner */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
@endpush
