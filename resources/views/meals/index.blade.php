@extends('layouts.app')

@section('title', '식사 기록 - MyFit')

@section('content')
<div id="meals-app" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header with Date Picker -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-utensils text-primary mr-2"></i>
                식사 기록
            </h1>
            <a href="{{ route('meals.create') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>식사 추가
            </a>
        </div>

        <!-- Date Picker -->
        <div class="mt-4 flex items-center space-x-4">
            <button @click="changeDate(-1)" class="p-2 hover:bg-gray-100 rounded-full">
                <i class="fas fa-chevron-left text-gray-600"></i>
            </button>
            <input
                type="date"
                v-model="selectedDate"
                @change="loadMeals"
                class="border border-gray-300 rounded-md px-3 py-2 focus:ring-primary focus:border-primary"
            >
            <button @click="changeDate(1)" class="p-2 hover:bg-gray-100 rounded-full">
                <i class="fas fa-chevron-right text-gray-600"></i>
            </button>
            <button @click="setToday" class="px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-md">
                오늘
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">식사 기록을 불러오는 중...</p>
        </div>
    </div>

    <!-- Content -->
    <div v-else>
        <!-- Daily Nutrition Summary (Top) -->
        <div class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                오늘의 영양 요약
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600">칼로리</p>
                    <p class="text-2xl font-bold text-gray-900">@{{ summary.total_calories }}</p>
                    <p class="text-xs text-gray-500">kcal</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">단백질</p>
                    <p class="text-2xl font-bold text-blue-600">@{{ summary.total_protein_g }}g</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">탄수화물</p>
                    <p class="text-2xl font-bold text-yellow-600">@{{ summary.total_carbs_g }}g</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">지방</p>
                    <p class="text-2xl font-bold text-red-600">@{{ summary.total_fat_g }}g</p>
                </div>
            </div>

            <!-- Progress Bar (if target available) -->
            <div v-if="summary.target_calories" class="mt-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-700">목표 대비</span>
                    <span class="font-semibold" :class="summary.percentage <= 100 ? 'text-primary' : 'text-red-600'">
                        @{{ summary.percentage }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div
                        class="h-3 rounded-full transition-all duration-500"
                        :class="summary.percentage <= 100 ? 'bg-primary' : 'bg-red-500'"
                        :style="{ width: Math.min(summary.percentage, 100) + '%' }"
                    ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1 text-center">
                    @{{ summary.calories_remaining }} kcal 남음
                </p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="meals.breakfast.length === 0 && meals.lunch.length === 0 && meals.dinner.length === 0 && meals.snack.length === 0" class="text-center py-12">
            <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 mb-4">이 날짜에 기록된 식사가 없습니다</p>
            <a href="{{ route('meals.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-md">
                <i class="fas fa-plus mr-2"></i>첫 식사 기록하기
            </a>
        </div>

        <!-- Meal Sections -->
        <div v-else class="space-y-6">
            <!-- Breakfast -->
            <meal-type-section
                title="아침"
                icon="fa-sunrise"
                :meals="meals.breakfast"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Lunch -->
            <meal-type-section
                title="점심"
                icon="fa-sun"
                :meals="meals.lunch"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Dinner -->
            <meal-type-section
                title="저녁"
                icon="fa-moon"
                :meals="meals.dinner"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>

            <!-- Snack -->
            <meal-type-section
                title="간식"
                icon="fa-cookie-bite"
                :meals="meals.snack"
                @edit="editMeal"
                @delete="confirmDelete"
            ></meal-type-section>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showDeleteModal = false">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                삭제 확인
            </h3>
            <p class="text-gray-600 mb-6">
                이 식사 기록을 삭제하시겠습니까?<br>
                삭제된 데이터는 복구할 수 없습니다.
            </p>
            <div class="flex justify-end space-x-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    취소
                </button>
                <button @click="deleteMeal" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                    <i class="fas fa-trash mr-2"></i>삭제
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Add Button (Mobile) -->
    <a href="{{ route('meals.create') }}" class="fixed bottom-6 right-6 bg-primary hover:bg-primary/90 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center lg:hidden">
        <i class="fas fa-plus text-xl"></i>
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
        <div v-if="meals.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i :class="['fas', icon, 'text-primary mr-2']"></i>
                    {{ title }} <span class="text-sm font-normal text-gray-500">({{ meals.length }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-3">
                <div v-for="meal in meals" :key="meal.id" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ meal.food_name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ meal.serving_size }}g
                                <span v-if="meal.meal_time"> · {{ formatTime(meal.meal_time) }}</span>
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                    🔥 {{ meal.calories }} kcal
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                    P {{ meal.protein_g }}g
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                    C {{ meal.carbs_g }}g
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                    F {{ meal.fat_g }}g
                                </span>
                            </div>
                            <p v-if="meal.notes" class="text-sm text-gray-600 mt-2 italic">
                                {{ meal.notes }}
                            </p>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            <button @click="$emit('edit', meal)" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-md">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="$emit('delete', meal)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md">
                                <i class="fas fa-trash"></i>
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
            return time.substring(0, 5); // HH:MM
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

                // Get summary with target
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
            // TODO: Navigate to edit page
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
            // Use parent app's toast
            const event = new CustomEvent('show-toast', {
                detail: { message, type }
            });
            window.dispatchEvent(event);
        }
    }
}).mount('#meals-app');

// Listen for toast events
window.addEventListener('show-toast', (e) => {
    if (window.app && window.app.showToast) {
        window.app.showToast(e.detail.message, e.detail.type);
    }
});
</script>
@endpush
