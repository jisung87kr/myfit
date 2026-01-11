@extends('layouts.app')

@section('title', '식단 플랜 상세 - MyFit')

@section('content')
<div id="diet-plan-detail-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header with Back Button -->
    <header class="flex items-center gap-4 mb-8 animate-fade-in-up">
        <a href="{{ route('diet-plan.index') }}" class="p-2 bg-white rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-heading">식단 플랜 상세</h1>
            <p class="text-gray-500 text-sm">상세 식단과 운동 계획을 확인하세요.</p>
        </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-primary-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white rounded-3xl border border-dashed border-gray-200 p-16 text-center animate-fade-in-up">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2 font-heading">플랜을 불러올 수 없습니다</h3>
        <p class="text-gray-500 mb-8">@{{ error }}</p>
        <a href="{{ route('diet-plan.index') }}" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-2xl hover:bg-black transition-all">
            목록으로 돌아가기
        </a>
    </div>

    <!-- Diet Plan Content -->
    <div v-else-if="dietPlan" class="space-y-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Plan Summary Card -->
        <div class="bg-gradient-to-br from-primary-600 to-accent-600 rounded-3xl p-8 text-white shadow-xl shadow-primary-500/20 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-2xl font-bold font-heading">@{{ dietPlan.duration_days || 7 }}일 식단 플랜</h2>
                            <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-bold border border-white/10">
                                @{{ getStatusLabel(dietPlan.status) }}
                            </span>
                        </div>
                        <p class="text-primary-100 flex items-center text-sm font-medium">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @{{ formatDate(dietPlan.start_date) }} ~ @{{ formatDate(dietPlan.end_date) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <p class="text-xs text-primary-100 mb-1 font-medium">일평균 칼로리</p>
                        <p class="text-2xl font-bold">@{{ dietPlan.avg_calories_per_day || '-' }}<span class="text-sm font-normal text-primary-200 ml-1">kcal</span></p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <p class="text-xs text-primary-100 mb-1 font-medium">일평균 단백질</p>
                        <p class="text-2xl font-bold">@{{ dietPlan.avg_protein_g || '-' }}<span class="text-sm font-normal text-primary-200 ml-1">g</span></p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <p class="text-xs text-primary-100 mb-1 font-medium">일평균 탄수화물</p>
                        <p class="text-2xl font-bold">@{{ dietPlan.avg_carbs_g || '-' }}<span class="text-sm font-normal text-primary-200 ml-1">g</span></p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <p class="text-xs text-primary-100 mb-1 font-medium">일평균 지방</p>
                        <p class="text-2xl font-bold">@{{ dietPlan.avg_fat_g || '-' }}<span class="text-sm font-normal text-primary-200 ml-1">g</span></p>
                    </div>
                </div>

                <p v-if="dietPlan.ai_summary" class="mt-6 text-sm text-primary-100 italic bg-black/10 p-4 rounded-xl border border-white/5">
                    "@{{ dietPlan.ai_summary }}"
                </p>

                <!-- Estimated Weight Change -->
                <div v-if="dietPlan.estimated_weight_loss_kg !== null" class="mt-6 bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                    <div class="flex items-center gap-4">
                        <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center', dietPlan.estimated_weight_loss_kg > 0 ? 'bg-green-500/20' : dietPlan.estimated_weight_loss_kg < 0 ? 'bg-orange-500/20' : 'bg-gray-500/20']">
                            <svg v-if="dietPlan.estimated_weight_loss_kg > 0" class="w-7 h-7 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                            <svg v-else-if="dietPlan.estimated_weight_loss_kg < 0" class="w-7 h-7 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <svg v-else class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-primary-100 mb-1">플랜 완료 시 예상 체중 변화</p>
                            <p class="text-2xl font-bold">
                                <span v-if="dietPlan.estimated_weight_loss_kg > 0" class="text-green-300">-@{{ Math.abs(dietPlan.estimated_weight_loss_kg) }} kg</span>
                                <span v-else-if="dietPlan.estimated_weight_loss_kg < 0" class="text-orange-300">+@{{ Math.abs(dietPlan.estimated_weight_loss_kg) }} kg</span>
                                <span v-else class="text-gray-300">유지</span>
                            </p>
                        </div>
                        <div class="text-right text-xs text-primary-200">
                            <p v-if="dietPlan.estimated_weight_loss_kg > 0">감량 예상</p>
                            <p v-else-if="dietPlan.estimated_weight_loss_kg < 0">증량 예상</p>
                            <p v-else>체중 유지</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day Selector & Content -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Day Selector -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 sticky top-24">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 px-2 uppercase tracking-wider">일차 선택</h3>
                    <div class="flex lg:flex-col overflow-x-auto lg:overflow-visible gap-2 pb-2 lg:pb-0 custom-scrollbar">
                        <button
                            v-for="day in availableDays"
                            :key="day.value"
                            @click="selectedDay = day.value"
                            :class="['px-4 py-3 rounded-xl font-bold text-sm text-left transition-all whitespace-nowrap flex items-center justify-between group cursor-pointer', selectedDay === day.value ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900']"
                        >
                            <span>@{{ day.label }}</span>
                            <svg :class="['w-4 h-4 transition-transform', selectedDay === day.value ? 'text-white' : 'text-gray-300 group-hover:translate-x-1']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Meals List -->
            <div class="lg:col-span-9 space-y-6">
                <!-- Daily Summary Card -->
                <div v-if="currentDayPlan" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">@{{ selectedDay }}일차 요약</h3>
                            <p class="text-sm text-gray-500">@{{ formatDate(currentDayPlan.date) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div class="text-center px-4 py-2 bg-gray-50 rounded-xl">
                                <p class="text-xs text-gray-500 mb-0.5">칼로리</p>
                                <p class="text-lg font-bold text-gray-900">@{{ Math.round(currentDayPlan.total_calories) || '-' }}</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-blue-50 rounded-xl">
                                <p class="text-xs text-blue-500 mb-0.5">단백질</p>
                                <p class="text-lg font-bold text-blue-600">@{{ Math.round(currentDayPlan.total_protein_g) || '-' }}g</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-amber-50 rounded-xl">
                                <p class="text-xs text-amber-500 mb-0.5">탄수화물</p>
                                <p class="text-lg font-bold text-amber-600">@{{ Math.round(currentDayPlan.total_carbs_g) || '-' }}g</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-orange-50 rounded-xl">
                                <p class="text-xs text-orange-500 mb-0.5">지방</p>
                                <p class="text-lg font-bold text-orange-600">@{{ Math.round(currentDayPlan.total_fat_g) || '-' }}g</p>
                            </div>
                            <div v-if="currentDayExercises && currentDayExercises.length > 0" class="text-center px-4 py-2 bg-green-50 rounded-xl">
                                <p class="text-xs text-green-500 mb-0.5">운동</p>
                                <p class="text-lg font-bold text-green-600">@{{ currentDayExercises.length }}개</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Tips -->
                <div v-if="currentDayPlan && currentDayPlan.tips" class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl p-5 border border-amber-100">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-800 mb-1">오늘의 팁</h4>
                            <p class="text-sm text-amber-700">@{{ currentDayPlan.tips }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="dayMeals && dayMeals.length > 0">
                    <meal-type-section
                        title="아침"
                        icon="sunrise"
                        :meals="getMealsByType('breakfast')"
                    ></meal-type-section>

                    <meal-type-section
                        title="점심"
                        icon="sun"
                        :meals="getMealsByType('lunch')"
                        class="mt-6"
                    ></meal-type-section>

                    <meal-type-section
                        title="저녁"
                        icon="moon"
                        :meals="getMealsByType('dinner')"
                        class="mt-6"
                    ></meal-type-section>

                    <meal-type-section
                        title="간식"
                        icon="cake"
                        :meals="getMealsByType('snack')"
                        class="mt-6"
                    ></meal-type-section>
                </div>

                <div v-else class="bg-white rounded-3xl p-12 text-center border border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">식단 정보가 없습니다</h3>
                    <p class="text-gray-500 text-sm">다른 날짜를 선택해보세요.</p>
                </div>

                <!-- Exercise Plan -->
                <div v-if="currentDayExercises && currentDayExercises.length > 0" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        오늘의 운동 <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg">@{{ currentDayExercises.length }}</span>
                    </h3>

                    <div class="space-y-3">
                        <div v-for="exercise in currentDayExercises" :key="exercise.id" class="border border-gray-100 rounded-2xl p-4 hover:border-green-200 hover:bg-green-50/30 transition-all">
                            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 mb-2">@{{ exercise.exercise_name }}</h4>
                                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                                        <span class="text-green-600 bg-green-50 px-2 py-1 rounded-md flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @{{ exercise.duration_minutes }}분
                                        </span>
                                        <span class="text-orange-600 bg-orange-50 px-2 py-1 rounded-md flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                            @{{ Math.round(exercise.estimated_calories_burned) }} kcal
                                        </span>
                                        <span class="text-purple-600 bg-purple-50 px-2 py-1 rounded-md">
                                            강도: @{{ exercise.intensity }}
                                        </span>
                                    </div>
                                    <p v-if="exercise.notes" class="text-xs text-gray-500 mt-2 italic">"@{{ exercise.notes }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const MealTypeSection = {
    props: ['title', 'icon', 'meals'],
    template: `
        <div v-if="meals.length > 0" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                    <svg v-if="icon === 'sunrise'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg v-else-if="icon === 'sun'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg v-else-if="icon === 'moon'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
                </div>
                @{{ title }} <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg">@{{ meals.length }}</span>
            </h3>

            <div class="space-y-3">
                <div v-for="meal in meals" :key="meal.id" class="border border-gray-100 rounded-2xl p-4 hover:border-primary-200 hover:bg-primary-50/30 transition-all">
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">@{{ meal.food_name }}</h4>
                        <p v-if="meal.notes" class="text-xs text-gray-500 italic mb-2">"@{{ meal.notes }}"</p>

                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <span class="text-gray-900 bg-gray-100 px-2 py-1 rounded-md">@{{ Math.round(meal.calories) }} kcal</span>
                            <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded-md">P @{{ Math.round(meal.protein_g) }}g</span>
                            <span class="text-amber-600 bg-amber-50 px-2 py-1 rounded-md">C @{{ Math.round(meal.carbs_g) }}g</span>
                            <span class="text-orange-600 bg-orange-50 px-2 py-1 rounded-md">F @{{ Math.round(meal.fat_g) }}g</span>
                            <span class="text-gray-500 border border-gray-200 px-2 py-1 rounded-md">@{{ Math.round(meal.serving_size) }}g</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
};

Vue.createApp({
    components: { MealTypeSection },
    data() {
        return {
            planId: {{ $planId }},
            dietPlan: null,
            selectedDay: 1,
            availableDays: [],
            loading: false,
            error: null
        }
    },
    computed: {
        currentDayPlan() {
            if (!this.dietPlan || !this.dietPlan.daily_meal_plans) return null;
            return this.dietPlan.daily_meal_plans.find(plan => plan.day_number === this.selectedDay);
        },
        dayMeals() {
            if (!this.currentDayPlan || !this.currentDayPlan.meals) return [];
            const meals = this.currentDayPlan.meals;
            const allMeals = [];
            ['breakfast', 'lunch', 'dinner', 'snack'].forEach(type => {
                if (meals[type]) {
                    meals[type].forEach(meal => {
                        allMeals.push({ ...meal, meal_type: type });
                    });
                }
            });
            return allMeals;
        },
        currentDayExercises() {
            if (!this.dietPlan || !this.dietPlan.daily_exercise_plans) return [];
            const dayPlan = this.dietPlan.daily_exercise_plans.find(plan => plan.day_number === this.selectedDay);
            return dayPlan ? dayPlan.exercises : [];
        }
    },
    mounted() {
        this.loadDietPlan();
    },
    methods: {
        async loadDietPlan() {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get(`/api/diet-plans/${this.planId}`);
                if (response.data.data) {
                    this.dietPlan = response.data.data;
                    this.initializeDays();
                }
            } catch (error) {
                if (error.response?.status === 404) {
                    this.error = '플랜을 찾을 수 없습니다.';
                } else if (error.response?.status === 403) {
                    this.error = '이 플랜에 접근할 권한이 없습니다.';
                } else {
                    this.error = '플랜을 불러오는 중 오류가 발생했습니다.';
                }
            } finally {
                this.loading = false;
            }
        },
        initializeDays() {
            if (!this.dietPlan || !this.dietPlan.daily_meal_plans) return;
            this.availableDays = this.dietPlan.daily_meal_plans.map(plan => ({
                value: plan.day_number,
                label: `${plan.day_number}일차`
            }));
        },
        getMealsByType(type) {
            if (!this.currentDayPlan || !this.currentDayPlan.meals) return [];
            const meals = this.currentDayPlan.meals[type] || [];
            return meals.map(meal => ({ ...meal, meal_type: type }));
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR');
        },
        getStatusLabel(status) {
            const labels = { 'active': '진행중', 'completed': '완료', 'generating': '생성중', 'failed': '실패', 'archived': '보관됨' };
            return labels[status] || status;
        }
    }
}).mount('#diet-plan-detail-app');
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate3d(0, 20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}
</style>
@endpush
