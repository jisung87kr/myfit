@extends('layouts.app')

@section('title', '칼로리 계산기 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div id="calculator-app">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-calculator text-primary mr-2"></i>칼로리 계산기
            </h1>
            <p class="text-sm text-gray-600 mt-1">하루 필요 칼로리를 계산하세요</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calculator Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user text-primary mr-2"></i>기본 정보
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gender -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                성별 <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    @click="form.gender = 'male'"
                                    :class="['p-4 rounded-lg border-2 transition-all', form.gender === 'male' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <i :class="['fas fa-mars text-2xl mb-2', form.gender === 'male' ? 'text-primary' : 'text-gray-400']"></i>
                                    <p :class="['font-semibold', form.gender === 'male' ? 'text-primary' : 'text-gray-700']">남성</p>
                                </button>
                                <button
                                    type="button"
                                    @click="form.gender = 'female'"
                                    :class="['p-4 rounded-lg border-2 transition-all', form.gender === 'female' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <i :class="['fas fa-venus text-2xl mb-2', form.gender === 'female' ? 'text-primary' : 'text-gray-400']"></i>
                                    <p :class="['font-semibold', form.gender === 'female' ? 'text-primary' : 'text-gray-700']">여성</p>
                                </button>
                            </div>
                        </div>

                        <!-- Age -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                나이 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model.number="form.age"
                                @input="calculate"
                                min="1"
                                max="120"
                                placeholder="25"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Weight -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                체중 (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model.number="form.weight"
                                @input="calculate"
                                min="0"
                                step="0.1"
                                placeholder="70"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Height -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                키 (cm) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model.number="form.height"
                                @input="calculate"
                                min="0"
                                step="0.1"
                                placeholder="170"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- BMI Display -->
                        <div v-if="bmi" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">BMI</span>
                                <span class="text-xl font-bold text-primary">@{{ bmi }}</span>
                            </div>
                            <p class="text-xs text-gray-600">@{{ bmiCategory }}</p>
                        </div>
                    </div>
                </div>

                <!-- Activity Level -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-running text-primary mr-2"></i>활동 수준
                    </h2>

                    <div class="space-y-3">
                        <button
                            type="button"
                            v-for="activity in activityLevels"
                            :key="activity.value"
                            @click="form.activity_level = activity.value; calculate()"
                            :class="['w-full p-4 rounded-lg border-2 transition-all text-left', form.activity_level === activity.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                        >
                            <div class="flex items-start gap-4">
                                <i :class="[activity.icon, 'text-2xl mt-1', form.activity_level === activity.value ? 'text-primary' : 'text-gray-400']"></i>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <p :class="['font-semibold', form.activity_level === activity.value ? 'text-primary' : 'text-gray-900']">@{{ activity.label }}</p>
                                        <span class="text-xs text-gray-500">x @{{ activity.multiplier }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">@{{ activity.description }}</p>
                                </div>
                                <i v-if="form.activity_level === activity.value" class="fas fa-check-circle text-primary text-xl"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Goal Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-bullseye text-primary mr-2"></i>목표
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <button
                            type="button"
                            v-for="goal in goals"
                            :key="goal.value"
                            @click="selectedGoal = goal.value; calculate()"
                            :class="['p-4 rounded-lg border-2 transition-all text-center', selectedGoal === goal.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                        >
                            <i :class="[goal.icon, 'text-2xl mb-2', selectedGoal === goal.value ? 'text-primary' : 'text-gray-400']"></i>
                            <p :class="['font-semibold mb-1', selectedGoal === goal.value ? 'text-primary' : 'text-gray-700']">@{{ goal.label }}</p>
                            <p class="text-xs text-gray-600">@{{ goal.adjustment }}</p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Panel -->
            <div class="lg:col-span-1">
                <div class="bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>계산 결과
                    </h2>

                    <div v-if="results.bmr" class="space-y-4">
                        <!-- BMR -->
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">기초대사량 (BMR)</p>
                            <p class="text-3xl font-bold text-primary">@{{ Math.round(results.bmr) }}</p>
                            <p class="text-xs text-gray-500 mt-1">kcal/일</p>
                        </div>

                        <!-- TDEE -->
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">일일 소모 칼로리 (TDEE)</p>
                            <p class="text-3xl font-bold text-secondary">@{{ Math.round(results.tdee) }}</p>
                            <p class="text-xs text-gray-500 mt-1">kcal/일</p>
                        </div>

                        <!-- Goal Calories -->
                        <div class="bg-white rounded-lg p-4 border-2 border-primary">
                            <p class="text-sm text-gray-600 mb-1">목표 칼로리</p>
                            <p class="text-3xl font-bold text-accent">@{{ Math.round(results.goalCalories) }}</p>
                            <p class="text-xs text-gray-500 mt-1">kcal/일</p>
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-600">@{{ goalDescription }}</p>
                            </div>
                        </div>

                        <!-- Macros Breakdown -->
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-900 mb-3">권장 영양소 비율</p>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-gray-600">단백질</span>
                                        <span class="text-sm font-bold text-blue-600">@{{ results.macros.protein }}g</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: 30%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">30% (@{{ Math.round(results.macros.protein * 4) }}kcal)</p>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-gray-600">탄수화물</span>
                                        <span class="text-sm font-bold text-yellow-600">@{{ results.macros.carbs }}g</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-600 h-2 rounded-full" style="width: 40%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">40% (@{{ Math.round(results.macros.carbs * 4) }}kcal)</p>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-gray-600">지방</span>
                                        <span class="text-sm font-bold text-orange-600">@{{ results.macros.fat }}g</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-orange-600 h-2 rounded-full" style="width: 30%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">30% (@{{ Math.round(results.macros.fat * 9) }}kcal)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <button
                            @click="applyToProfile"
                            :disabled="applying"
                            class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 font-medium"
                        >
                            <span v-if="applying"><i class="fas fa-spinner fa-spin mr-2"></i>적용 중...</span>
                            <span v-else><i class="fas fa-check mr-2"></i>프로필에 적용</span>
                        </button>
                    </div>

                    <div v-else class="text-center py-12">
                        <i class="fas fa-calculator text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-600">정보를 입력하면<br>계산 결과가 표시됩니다</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="mt-6 bg-blue-50 rounded-lg p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>계산 방법
            </h3>
            <div class="space-y-2 text-sm text-gray-700">
                <p><strong>BMR (기초대사량):</strong> Mifflin-St Jeor 공식을 사용하여 계산합니다. 생명 유지에 필요한 최소 칼로리입니다.</p>
                <p><strong>TDEE (일일 총 소모 칼로리):</strong> BMR에 활동 계수를 곱한 값입니다.</p>
                <p><strong>목표 칼로리:</strong> TDEE에서 목표에 따라 칼로리를 조정합니다.</p>
                <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                    <li>체중 감량: TDEE - 500kcal (주 0.5kg 감량)</li>
                    <li>근육 증가: TDEE + 300kcal (주 0.25kg 증량)</li>
                    <li>현상 유지: TDEE</li>
                </ul>
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
                gender: 'male',
                age: null,
                weight: null,
                height: null,
                activity_level: 'moderately_active'
            },
            selectedGoal: 'maintenance',
            activityLevels: [
                { value: 'sedentary', label: '앉아서 생활', description: '운동을 거의 하지 않음', multiplier: 1.2, icon: 'fas fa-couch' },
                { value: 'lightly_active', label: '가벼운 활동', description: '주 1-3일 가벼운 운동', multiplier: 1.375, icon: 'fas fa-walking' },
                { value: 'moderately_active', label: '보통 활동', description: '주 3-5일 중강도 운동', multiplier: 1.55, icon: 'fas fa-running' },
                { value: 'very_active', label: '활발한 활동', description: '주 6-7일 고강도 운동', multiplier: 1.725, icon: 'fas fa-bicycle' },
                { value: 'extra_active', label: '매우 활발', description: '하루 2회 이상 운동', multiplier: 1.9, icon: 'fas fa-dumbbell' }
            ],
            goals: [
                { value: 'weight_loss', label: '체중 감량', adjustment: '-500kcal', icon: 'fas fa-arrow-down' },
                { value: 'maintenance', label: '현상 유지', adjustment: '±0kcal', icon: 'fas fa-equals' },
                { value: 'muscle_gain', label: '근육 증가', adjustment: '+300kcal', icon: 'fas fa-arrow-up' }
            ],
            results: {
                bmr: 0,
                tdee: 0,
                goalCalories: 0,
                macros: {
                    protein: 0,
                    carbs: 0,
                    fat: 0
                }
            },
            applying: false
        }
    },
    computed: {
        bmi() {
            if (!this.form.height || !this.form.weight) return null;
            const heightM = this.form.height / 100;
            return (this.form.weight / (heightM * heightM)).toFixed(1);
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
        goalDescription() {
            const descriptions = {
                'weight_loss': '주당 약 0.5kg 감량 목표',
                'maintenance': '현재 체중 유지',
                'muscle_gain': '주당 약 0.25kg 증량 목표'
            };
            return descriptions[this.selectedGoal] || '';
        }
    },
    mounted() {
        this.loadUserData();
    },
    methods: {
        async loadUserData() {
            try {
                const response = await axios.get('/user/profile');
                const data = response.data.data;

                if (data.age) this.form.age = data.age;
                if (data.gender) this.form.gender = data.gender;
                if (data.height_cm) this.form.height = data.height_cm;
                if (data.current_weight_kg) this.form.weight = data.current_weight_kg;
                if (data.activity_level) this.form.activity_level = data.activity_level;
                if (data.goal_type) this.selectedGoal = data.goal_type;

                this.calculate();
            } catch (error) {
                console.error('Failed to load user data:', error);
            }
        },
        calculate() {
            if (!this.form.age || !this.form.weight || !this.form.height) return;

            // Calculate BMR using Mifflin-St Jeor Equation
            let bmr;
            if (this.form.gender === 'male') {
                bmr = (10 * this.form.weight) + (6.25 * this.form.height) - (5 * this.form.age) + 5;
            } else {
                bmr = (10 * this.form.weight) + (6.25 * this.form.height) - (5 * this.form.age) - 161;
            }

            // Calculate TDEE
            const activityMultiplier = this.activityLevels.find(a => a.value === this.form.activity_level)?.multiplier || 1.55;
            const tdee = bmr * activityMultiplier;

            // Calculate goal calories
            let goalCalories = tdee;
            if (this.selectedGoal === 'weight_loss') {
                goalCalories = tdee - 500;
            } else if (this.selectedGoal === 'muscle_gain') {
                goalCalories = tdee + 300;
            }

            // Calculate macros (30% protein, 40% carbs, 30% fat)
            const protein = Math.round((goalCalories * 0.30) / 4);
            const carbs = Math.round((goalCalories * 0.40) / 4);
            const fat = Math.round((goalCalories * 0.30) / 9);

            this.results = {
                bmr: bmr,
                tdee: tdee,
                goalCalories: goalCalories,
                macros: {
                    protein: protein,
                    carbs: carbs,
                    fat: fat
                }
            };
        },
        async applyToProfile() {
            this.applying = true;

            try {
                const payload = {
                    age: this.form.age,
                    gender: this.form.gender,
                    height_cm: this.form.height,
                    current_weight_kg: this.form.weight,
                    activity_level: this.form.activity_level,
                    goal_type: this.selectedGoal,
                    target_calories: Math.round(this.results.goalCalories)
                };

                const response = await axios.put('/user/profile', payload);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '프로필에 적용되었습니다!', type: 'success' }
                    }));
                }
            } catch (error) {
                console.error('Failed to apply to profile:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '적용에 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.applying = false;
            }
        }
    }
}).mount('#calculator-app');
</script>
@endpush
