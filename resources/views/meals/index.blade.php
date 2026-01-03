@extends('layouts.app')

@section('title', '식사 기록 - MyFit')

@section('content')
<div id="meals-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header with Date Picker -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-heading mb-2">식사 기록</h1>
            <p class="text-gray-500">오늘 섭취한 영양소를 확인하고 관리하세요.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            <!-- Date Navigation -->
            <div class="flex items-center bg-white rounded-2xl shadow-sm border border-gray-100 p-1.5 w-full sm:w-auto">
                <button @click="changeDate(-1)" class="p-2 hover:bg-gray-50 text-gray-400 hover:text-gray-600 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="flex-1 text-center px-4">
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="loadMeals"
                        class="text-sm font-bold text-gray-900 bg-transparent border-none focus:ring-0 text-center w-32 cursor-pointer font-heading"
                    >
                </div>
                <button @click="changeDate(1)" class="p-2 hover:bg-gray-50 text-gray-400 hover:text-gray-600 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <button @click="setToday" class="ml-2 px-3 py-1.5 text-xs font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors cursor-pointer">
                    오늘
                </button>
            </div>

            <a href="{{ route('meals.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-2xl transition-all shadow-lg shadow-gray-900/20 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                식사 추가
            </a>
        </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-primary-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
    </div>

    <!-- Content -->
    <div v-else class="space-y-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Nutrition Summary Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 font-heading">오늘의 영양 밸런스</h2>
                    <div class="text-right" v-if="summary.target_calories">
                        <p class="text-xs text-gray-500 mb-1">남은 칼로리</p>
                        <p class="text-2xl font-bold" :class="summary.calories_remaining >= 0 ? 'text-primary-600' : 'text-rose-500'">
                            @{{ summary.calories_remaining }} <span class="text-sm text-gray-400 font-normal">kcal</span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <!-- Progress Section -->
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between text-sm mb-2 font-medium">
                                <span class="text-gray-600">총 섭취량</span>
                                <span class="text-gray-900">@{{ summary.total_calories }} / @{{ summary.target_calories || 2000 }} kcal</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                                <div class="h-4 rounded-full transition-all duration-1000 ease-out relative overflow-hidden"
                                     :class="summary.percentage <= 100 ? 'bg-gradient-to-r from-primary-400 to-primary-600' : 'bg-gradient-to-r from-rose-400 to-rose-600'"
                                     :style="{ width: Math.min(summary.percentage, 100) + '%' }">
                                     <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Macro Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                                <p class="text-xs font-medium text-blue-600 mb-1">단백질</p>
                                <p class="text-xl font-bold text-gray-900">@{{ summary.total_protein_g }}<span class="text-xs text-gray-500 font-normal ml-0.5">g</span></p>
                            </div>
                            <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-100/50">
                                <p class="text-xs font-medium text-amber-600 mb-1">탄수화물</p>
                                <p class="text-xl font-bold text-gray-900">@{{ summary.total_carbs_g }}<span class="text-xs text-gray-500 font-normal ml-0.5">g</span></p>
                            </div>
                            <div class="p-4 bg-orange-50/50 rounded-2xl border border-orange-100/50">
                                <p class="text-xs font-medium text-orange-600 mb-1">지방</p>
                                <p class="text-xl font-bold text-gray-900">@{{ summary.total_fat_g }}<span class="text-xs text-gray-500 font-normal ml-0.5">g</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Chart or Visual (Simple Circles for now) -->
                    <div class="hidden md:flex justify-center space-x-6">
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full border-4 border-blue-100 flex items-center justify-center mb-2">
                                <span class="text-blue-600 font-bold">P</span>
                            </div>
                            <span class="text-xs text-gray-500">Protein</span>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full border-4 border-amber-100 flex items-center justify-center mb-2">
                                <span class="text-amber-600 font-bold">C</span>
                            </div>
                            <span class="text-xs text-gray-500">Carbs</span>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full border-4 border-orange-100 flex items-center justify-center mb-2">
                                <span class="text-orange-600 font-bold">F</span>
                            </div>
                            <span class="text-xs text-gray-500">Fat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="meals.breakfast.length === 0 && meals.lunch.length === 0 && meals.dinner.length === 0 && meals.snack.length === 0" 
             class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">식사 기록이 없습니다</h3>
            <p class="text-gray-500 mb-6 max-w-sm mx-auto">오늘 무엇을 드셨나요? 건강한 하루를 위해 첫 식사를 기록해보세요.</p>
            <a href="{{ route('meals.create') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors cursor-pointer">
                첫 식사 기록하기
            </a>
        </div>

        <!-- Meal Lists Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Breakfast -->
            <meal-type-section
                title="아침"
                icon="sunrise"
                :meals="meals.breakfast"
                color="blue"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Lunch -->
            <meal-type-section
                title="점심"
                icon="sun"
                :meals="meals.lunch"
                color="amber"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Dinner -->
            <meal-type-section
                title="저녁"
                icon="moon"
                :meals="meals.dinner"
                color="indigo"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Snack -->
            <meal-type-section
                title="간식"
                icon="cake"
                :meals="meals.snack"
                color="rose"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl transform transition-all scale-100" @click.stop>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4 mx-auto">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">기록 삭제</h3>
            <p class="text-gray-500 text-center text-sm mb-6">
                정말 이 식사 기록을 삭제하시겠습니까?<br>삭제 후에는 복구할 수 없습니다.
            </p>
            <div class="flex space-x-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors cursor-pointer">
                    취소
                </button>
                <button @click="deleteMeal" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors cursor-pointer">
                    삭제
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Meal Type Section Component
const MealTypeSection = {
    props: ['title', 'icon', 'meals', 'color'],
    emits: ['edit', 'delete'],
    setup(props) {
        const getColorClass = (type) => {
            const colors = {
                blue: 'text-blue-600 bg-blue-50',
                amber: 'text-amber-600 bg-amber-50',
                indigo: 'text-indigo-600 bg-indigo-50',
                rose: 'text-rose-600 bg-rose-50'
            };
            return colors[props.color] || colors.blue;
        };
        return { getColorClass };
    },
    template: `
        <div v-if="meals.length > 0" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col h-full hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="getColorClass(color)">
                        <svg v-if="icon === 'sunrise'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg v-else-if="icon === 'sun'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg v-else-if="icon === 'moon'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 font-heading">@{{ title }}</h3>
                </div>
                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-lg">@{{ meals.length }} items</span>
            </div>

            <div class="space-y-3 flex-1">
                <div v-for="meal in meals" :key="meal.id" class="group relative bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 rounded-2xl p-4 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="font-bold text-gray-900 truncate">@{{ meal.food_name }}</h4>
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                @{{ meal.serving_size }}g
                                <span v-if="meal.meal_time" class="ml-2 pl-2 border-l border-gray-300">@{{ formatTime(meal.meal_time) }}</span>
                            </p>
                            
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                <span class="text-gray-900">@{{ meal.calories }} kcal</span>
                                <span class="text-gray-300">|</span>
                                <span class="text-blue-600">P @{{ meal.protein_g }}</span>
                                <span class="text-amber-600">C @{{ meal.carbs_g }}</span>
                                <span class="text-orange-600">F @{{ meal.fat_g }}</span>
                            </div>
                        </div>

                        <!-- Actions (Visible on Hover/Mobile) -->
                        <div class="flex flex-col space-y-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="$emit('edit', meal)" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click="$emit('delete', meal)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
            if (window.showToast) {
                window.showToast(message, type);
            }
        }
    }
}).mount('#meals-app');
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
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
@endpush