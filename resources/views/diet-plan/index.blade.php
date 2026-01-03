@extends('layouts.app')

@section('title', '식단 계획 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div id="diet-plan-app">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-utensils text-primary mr-2"></i>나의 식단 계획
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">AI가 생성한 맞춤 식단을 확인하세요</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showGenerateModal = true" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <i class="fas fa-magic mr-2"></i>새 식단 생성
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">식단을 불러오는 중...</p>
        </div>

        <!-- No Plan State -->
        <div v-else-if="!dietPlan" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-utensils text-6xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">아직 식단 계획이 없습니다</h3>
            <p class="text-gray-600 mb-6">AI가 당신만을 위한 맞춤 식단을 생성해드립니다</p>
            <button @click="showGenerateModal = true" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                <i class="fas fa-magic mr-2"></i>식단 생성하기
            </button>
        </div>

        <!-- Diet Plan Content -->
        <div v-else>
            <!-- Plan Summary -->
            <div class="bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">@{{ dietPlan.name }}</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            <i class="far fa-calendar mr-1"></i>@{{ formatDate(dietPlan.start_date) }} ~ @{{ formatDate(dietPlan.end_date) }}
                        </p>
                    </div>
                    <span :class="['px-4 py-2 rounded-full text-sm font-medium', dietPlan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                        @{{ getStatusLabel(dietPlan.status) }}
                    </span>
                </div>

                <!-- Nutrition Targets -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">목표 칼로리</p>
                        <p class="text-2xl font-bold text-primary">@{{ dietPlan.target_calories }}<span class="text-sm font-normal text-gray-600">kcal</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">단백질</p>
                        <p class="text-2xl font-bold text-blue-600">@{{ dietPlan.target_protein }}<span class="text-sm font-normal text-gray-600">g</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">탄수화물</p>
                        <p class="text-2xl font-bold text-yellow-600">@{{ dietPlan.target_carbs }}<span class="text-sm font-normal text-gray-600">g</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">지방</p>
                        <p class="text-2xl font-bold text-orange-600">@{{ dietPlan.target_fat }}<span class="text-sm font-normal text-gray-600">g</span></p>
                    </div>
                </div>

                <p v-if="dietPlan.description" class="mt-4 text-sm text-gray-700 italic">
                    <i class="fas fa-info-circle mr-1"></i>@{{ dietPlan.description }}
                </p>
            </div>

            <!-- Day Selector -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    <button
                        v-for="day in availableDays"
                        :key="day.value"
                        @click="selectedDay = day.value; loadDayMeals()"
                        :class="['px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all flex-shrink-0', selectedDay === day.value ? 'bg-primary text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        @{{ day.label }}
                    </button>
                </div>
            </div>

            <!-- Day Meals -->
            <div v-if="dayMeals && dayMeals.length > 0" class="space-y-6">
                <!-- Breakfast -->
                <meal-type-section
                    title="아침"
                    icon="fa-mug-hot"
                    :meals="getMealsByType('breakfast')"
                    v-on:use-meal="useMeal"
                ></meal-type-section>

                <!-- Lunch -->
                <meal-type-section
                    title="점심"
                    icon="fa-sun"
                    :meals="getMealsByType('lunch')"
                    v-on:use-meal="useMeal"
                ></meal-type-section>

                <!-- Dinner -->
                <meal-type-section
                    title="저녁"
                    icon="fa-moon"
                    :meals="getMealsByType('dinner')"
                    v-on:use-meal="useMeal"
                ></meal-type-section>

                <!-- Snack -->
                <meal-type-section
                    title="간식"
                    icon="fa-cookie"
                    :meals="getMealsByType('snack')"
                    v-on:use-meal="useMeal"
                ></meal-type-section>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-utensils text-5xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">이 날짜의 식단이 없습니다</h3>
                <p class="text-gray-600">다른 날짜를 선택해주세요</p>
            </div>
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showGenerateModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-magic text-primary mr-2"></i>새 식단 생성
                </h3>

                <form @submit.prevent="generateDietPlan">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                식단 기간 <span class="text-red-500">*</span>
                            </label>
                            <select v-model="generateForm.duration_days" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="7">1주일 (7일)</option>
                                <option value="14">2주일 (14일)</option>
                                <option value="30">1개월 (30일)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                목표 <span class="text-red-500">*</span>
                            </label>
                            <select v-model="generateForm.goal" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="weight_loss">체중 감량</option>
                                <option value="muscle_gain">근육 증가</option>
                                <option value="maintenance">현상 유지</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                식단 스타일
                            </label>
                            <select v-model="generateForm.diet_style" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="balanced">균형잡힌</option>
                                <option value="low_carb">저탄수화물</option>
                                <option value="high_protein">고단백</option>
                                <option value="vegetarian">채식</option>
                            </select>
                        </div>

                        <div v-if="errorMessage" class="rounded-md bg-red-50 p-3 border border-red-200">
                            <p class="text-sm text-red-800">@{{ errorMessage }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end mt-6">
                        <button type="button" @click="showGenerateModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            취소
                        </button>
                        <button type="submit" :disabled="generating" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                            <span v-if="generating"><i class="fas fa-spinner fa-spin mr-1"></i>생성 중...</span>
                            <span v-else><i class="fas fa-magic mr-1"></i>생성하기</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Use Meal Modal -->
        <div v-if="showUseMealModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showUseMealModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar-plus text-primary mr-2"></i>식단 적용
                </h3>

                <form @submit.prevent="applyMeal">
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">@{{ selectedMeal?.food_name }}</h4>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-sm">
                                    <i class="fas fa-fire mr-1"></i>@{{ selectedMeal?.calories }}kcal
                                </span>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    P: @{{ selectedMeal?.protein_g }}g
                                </span>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                    C: @{{ selectedMeal?.carbs_g }}g
                                </span>
                                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
                                    F: @{{ selectedMeal?.fat_g }}g
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                적용할 날짜 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                v-model="useMealForm.date"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                시간
                            </label>
                            <input
                                type="time"
                                v-model="useMealForm.time"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>

                        <div v-if="useMealError" class="rounded-md bg-red-50 p-3 border border-red-200">
                            <p class="text-sm text-red-800">@{{ useMealError }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end mt-6">
                        <button type="button" @click="showUseMealModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            취소
                        </button>
                        <button type="submit" :disabled="applying" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                            <span v-if="applying"><i class="fas fa-spinner fa-spin mr-1"></i>적용 중...</span>
                            <span v-else><i class="fas fa-check mr-1"></i>적용하기</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

// Meal Type Section Component
const MealTypeSection = {
    props: ['title', 'icon', 'meals'],
    emits: ['useMeal'],
    template: `
        <div v-if="meals.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i :class="['fas', icon, 'text-primary mr-2']"></i>
                    @{{ title }} <span class="text-sm font-normal text-gray-500">(@{{ meals.length }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-3">
                <div v-for="meal in meals" :key="meal.id" class="border border-gray-200 rounded-lg p-4 hover:border-primary transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-2">@{{ meal.food_name }}</h4>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                                    <i class="fas fa-fire mr-1"></i>@{{ meal.calories }}kcal
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    P: @{{ meal.protein_g }}g
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    C: @{{ meal.carbs_g }}g
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                    F: @{{ meal.fat_g }}g
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                    <i class="fas fa-balance-scale mr-1"></i>@{{ meal.serving_size }}@{{ meal.serving_unit }}
                                </span>
                            </div>
                            <p v-if="meal.description" class="text-sm text-gray-600 italic">@{{ meal.description }}</p>
                        </div>
                        <button @click="$emit('useMeal', meal)" class="ml-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i>사용
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `
};

Vue.createApp({
    components: {
        MealTypeSection
    },
    data() {
        return {
            dietPlan: null,
            dayMeals: [],
            selectedDay: 1,
            availableDays: [],
            loading: false,
            showGenerateModal: false,
            showUseMealModal: false,
            selectedMeal: null,
            generating: false,
            applying: false,
            errorMessage: '',
            useMealError: '',
            generateForm: {
                duration_days: 7,
                goal: 'weight_loss',
                diet_style: 'balanced'
            },
            useMealForm: {
                date: new Date().toISOString().split('T')[0],
                time: ''
            }
        }
    },
    mounted() {
        this.loadDietPlan();
    },
    methods: {
        async loadDietPlan() {
            this.loading = true;
            try {
                const response = await axios.get('/diet-plan/active');
                if (response.data.data) {
                    this.dietPlan = response.data.data;
                    this.initializeDays();
                    this.loadDayMeals();
                }
            } catch (error) {
                console.error('Failed to load diet plan:', error);
                if (error.response && error.response.status !== 404) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '식단을 불러오는데 실패했습니다.', type: 'error' }
                    }));
                }
            } finally {
                this.loading = false;
            }
        },
        initializeDays() {
            if (!this.dietPlan) return;

            const start = new Date(this.dietPlan.start_date);
            const end = new Date(this.dietPlan.end_date);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            this.availableDays = Array.from({ length: days }, (_, i) => ({
                value: i + 1,
                label: `${i + 1}일차`
            }));
        },
        async loadDayMeals() {
            if (!this.dietPlan) return;

            try {
                const response = await axios.get(`/diet-plan/${this.dietPlan.id}/meals?day=${this.selectedDay}`);
                this.dayMeals = response.data.data || [];
            } catch (error) {
                console.error('Failed to load day meals:', error);
                this.dayMeals = [];
            }
        },
        getMealsByType(type) {
            return this.dayMeals.filter(meal => meal.meal_type === type);
        },
        async generateDietPlan() {
            this.generating = true;
            this.errorMessage = '';

            try {
                const response = await axios.post('/diet-plan/generate', this.generateForm);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '새 식단이 생성되었습니다!', type: 'success' }
                    }));

                    this.showGenerateModal = false;
                    this.loadDietPlan();
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.errorMessage = Object.values(errors).flat().join(' ');
                } else {
                    this.errorMessage = '식단 생성에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.generating = false;
            }
        },
        useMeal(meal) {
            this.selectedMeal = meal;
            this.useMealForm.date = new Date().toISOString().split('T')[0];
            this.useMealForm.time = '';
            this.useMealError = '';
            this.showUseMealModal = true;
        },
        async applyMeal() {
            this.applying = true;
            this.useMealError = '';

            try {
                const payload = {
                    date: this.useMealForm.date,
                    meal_type: this.selectedMeal.meal_type,
                    food_id: this.selectedMeal.food_id,
                    food_name: this.selectedMeal.food_name,
                    serving_size: this.selectedMeal.serving_size,
                    calories: this.selectedMeal.calories,
                    protein_g: this.selectedMeal.protein_g,
                    carbs_g: this.selectedMeal.carbs_g,
                    fat_g: this.selectedMeal.fat_g,
                    meal_time: this.useMealForm.time || null
                };

                const response = await axios.post('/daily-logs/meals', payload);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: '식단이 적용되었습니다!', type: 'success' }
                    }));

                    this.showUseMealModal = false;
                    this.selectedMeal = null;
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.useMealError = Object.values(errors).flat().join(' ');
                } else {
                    this.useMealError = '식단 적용에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.applying = false;
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR');
        },
        getStatusLabel(status) {
            const labels = {
                'active': '진행중',
                'completed': '완료',
                'pending': '대기중'
            };
            return labels[status] || status;
        }
    }
}).mount('#diet-plan-app');
</script>
@endpush
