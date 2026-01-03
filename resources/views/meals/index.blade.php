@extends('layouts.app')

@section('title', '식사 기록 - MyFit')

@section('content')
<div id="meals-app" class="max-w-4xl mx-auto">
    <!-- Header with Date Picker -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 font-heading flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    식사 기록
                </h1>
                <p class="text-gray-500 mt-1 ml-13">오늘의 식사를 기록하고 관리하세요</p>
            </div>
            <a href="{{ route('meals.create') }}" class="hidden lg:inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-primary-500 to-accent-500 text-white font-medium rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 transition-all duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                식사 추가
            </a>
        </div>

        <!-- Date Picker -->
        <div class="mt-6 flex items-center space-x-3">
            <button @click="changeDate(-1)" class="p-2.5 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <input
                type="date"
                v-model="selectedDate"
                @change="loadMeals"
                class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
            >
            <button @click="changeDate(1)" class="p-2.5 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <button @click="setToday" class="px-4 py-2.5 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-xl transition-colors cursor-pointer">
                오늘
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-16">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-500">식사 기록을 불러오는 중...</p>
        </div>
    </div>

    <!-- Content -->
    <div v-else>
        <!-- Daily Nutrition Summary -->
        <div class="bg-gradient-to-br from-primary-50 to-accent-50 rounded-2xl p-6 mb-6 border border-primary-100/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 font-heading">
                오늘의 영양 요약
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white/80 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600">칼로리</p>
                    <p class="text-2xl font-bold text-gray-900">@{{ summary.total_calories }}</p>
                    <p class="text-xs text-gray-500">kcal</p>
                </div>
                <div class="bg-white/80 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600">단백질</p>
                    <p class="text-2xl font-bold text-blue-600">@{{ summary.total_protein_g }}g</p>
                </div>
                <div class="bg-white/80 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600">탄수화물</p>
                    <p class="text-2xl font-bold text-amber-600">@{{ summary.total_carbs_g }}g</p>
                </div>
                <div class="bg-white/80 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600">지방</p>
                    <p class="text-2xl font-bold text-orange-600">@{{ summary.total_fat_g }}g</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div v-if="summary.target_calories" class="mt-5">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-700">목표 대비</span>
                    <span class="font-semibold" :class="summary.percentage <= 100 ? 'text-primary-600' : 'text-red-600'">
                        @{{ summary.percentage }}%
                    </span>
                </div>
                <div class="w-full bg-white/60 rounded-full h-3 overflow-hidden">
                    <div
                        class="h-3 rounded-full transition-all duration-500"
                        :class="summary.percentage <= 100 ? 'bg-gradient-to-r from-primary-500 to-accent-500' : 'bg-red-500'"
                        :style="{ width: Math.min(summary.percentage, 100) + '%' }"
                    ></div>
                </div>
                <p class="text-xs text-gray-500 mt-2 text-center">
                    @{{ summary.calories_remaining }} kcal 남음
                </p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="meals.breakfast.length === 0 && meals.lunch.length === 0 && meals.dinner.length === 0 && meals.snack.length === 0" class="text-center py-16">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <p class="text-gray-500 mb-6">이 날짜에 기록된 식사가 없습니다</p>
            <a href="{{ route('meals.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-primary-500 to-accent-500 text-white font-medium rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-xl transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                첫 식사 기록하기
            </a>
        </div>

        <!-- Meal Sections -->
        <div v-else class="space-y-6">
            <!-- Breakfast -->
            <meal-type-section
                title="아침"
                icon="sunrise"
                :meals="meals.breakfast"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Lunch -->
            <meal-type-section
                title="점심"
                icon="sun"
                :meals="meals.lunch"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Dinner -->
            <meal-type-section
                title="저녁"
                icon="moon"
                :meals="meals.dinner"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Snack -->
            <meal-type-section
                title="간식"
                icon="cake"
                :meals="meals.snack"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="showDeleteModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.stop>
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">삭제 확인</h3>
            </div>
            <p class="text-gray-600 mb-6">
                이 식사 기록을 삭제하시겠습니까?<br>
                삭제된 데이터는 복구할 수 없습니다.
            </p>
            <div class="flex justify-end space-x-3">
                <button @click="showDeleteModal = false" class="px-4 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors cursor-pointer">
                    취소
                </button>
                <button @click="deleteMeal" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors cursor-pointer">
                    삭제
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Add Button (Mobile) -->
    <a href="{{ route('meals.create') }}" class="fixed bottom-6 right-6 bg-gradient-to-r from-primary-500 to-accent-500 text-white w-14 h-14 rounded-full shadow-lg shadow-primary-500/30 flex items-center justify-center lg:hidden hover:shadow-xl transition-all cursor-pointer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </a>
</div>
@endsection

@push('scripts')
<script>

// Meal Type Section Component
const MealTypeSection = {
    props: ['title', 'icon', 'meals'],
    emits: ['edit', 'delete'],
    template: `
        <div v-if="meals.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mr-3">
                        <svg v-if="icon === 'sunrise'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg v-else-if="icon === 'sun'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg v-else-if="icon === 'moon'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/>
                        </svg>
                    </span>
                    @{{ title }} <span class="text-sm font-normal text-gray-500 ml-2">(@{{ meals.length }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-3">
                <div v-for="meal in meals" :key="meal.id" class="border border-gray-100 rounded-xl p-4 hover:shadow-md hover:border-primary-200 transition-all cursor-pointer">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">@{{ meal.food_name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                @{{ meal.serving_size }}g
                                <span v-if="meal.meal_time"> · @{{ formatTime(meal.meal_time) }}</span>
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                    @{{ meal.calories }} kcal
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    P @{{ meal.protein_g }}g
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                    C @{{ meal.carbs_g }}g
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700">
                                    F @{{ meal.fat_g }}g
                                </span>
                            </div>
                            <p v-if="meal.notes" class="text-sm text-gray-600 mt-3 italic">
                                @{{ meal.notes }}
                            </p>
                        </div>
                        <div class="ml-4 flex space-x-1">
                            <button @click="$emit('edit', meal)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="$emit('delete', meal)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,
    methods: {
        formatTime(time) {
            if (!time) return '';
            return time.substring(0, 5);
        }
    }
};

Vue.createApp({
    components: {
        'meal-type-section': MealTypeSection
    },
    data() {
        return {
            selectedDate: new Date().toISOString().split('T')[0],
            loading: false,
            meals: {
                breakfast: [],
                lunch: [],
                dinner: [],
                snack: []
            },
            summary: {
                total_calories: 0,
                total_protein_g: 0,
                total_carbs_g: 0,
                total_fat_g: 0,
                target_calories: null,
                calories_remaining: 0,
                percentage: 0
            },
            showDeleteModal: false,
            mealToDelete: null
        }
    },
    async mounted() {
        await this.loadMeals();
    },
    methods: {
        async loadMeals() {
            this.loading = true;
            try {
                const response = await axios.get(`/daily-logs/meals?date=${this.selectedDate}`);
                const data = response.data.data;

                this.meals = data.meals;
                this.summary = {
                    total_calories: data.total_count > 0 ? this.calculateTotal('calories') : 0,
                    total_protein_g: data.total_count > 0 ? this.calculateTotal('protein_g') : 0,
                    total_carbs_g: data.total_count > 0 ? this.calculateTotal('carbs_g') : 0,
                    total_fat_g: data.total_count > 0 ? this.calculateTotal('fat_g') : 0
                };

                const summaryResponse = await axios.get(`/daily-logs/meals/summary?date=${this.selectedDate}`);
                const summaryData = summaryResponse.data.data;

                if (summaryData.target_calories) {
                    this.summary.target_calories = summaryData.target_calories;
                    this.summary.calories_remaining = summaryData.calories_remaining;
                    this.summary.percentage = summaryData.percentage;
                }

            } catch (error) {
                console.error('Error loading meals:', error);
                this.showToast('식사 기록을 불러오는데 실패했습니다.', 'error');
            } finally {
                this.loading = false;
            }
        },
        calculateTotal(field) {
            let total = 0;
            ['breakfast', 'lunch', 'dinner', 'snack'].forEach(type => {
                this.meals[type].forEach(meal => {
                    total += parseFloat(meal[field]) || 0;
                });
            });
            return Math.round(total * 10) / 10;
        },
        changeDate(days) {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() + days);
            this.selectedDate = date.toISOString().split('T')[0];
            this.loadMeals();
        },
        setToday() {
            this.selectedDate = new Date().toISOString().split('T')[0];
            this.loadMeals();
        },
        editMeal(meal) {
            window.location.href = `/meals/${meal.id}/edit`;
        },
        confirmDelete(meal) {
            this.mealToDelete = meal;
            this.showDeleteModal = true;
        },
        async deleteMeal() {
            if (!this.mealToDelete) return;

            try {
                await axios.delete(`/daily-logs/meals/${this.mealToDelete.id}`);
                this.showToast('식사 기록이 삭제되었습니다.', 'success');
                this.showDeleteModal = false;
                this.mealToDelete = null;
                await this.loadMeals();
            } catch (error) {
                console.error('Error deleting meal:', error);
                this.showToast('삭제에 실패했습니다.', 'error');
            }
        },
        showToast(message, type = 'success') {
            const event = new CustomEvent('show-toast', {
                detail: { message, type }
            });
            window.dispatchEvent(event);
        }
    }
}).mount('#meals-app');
</script>
@endpush
