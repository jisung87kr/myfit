@extends('layouts.app')

@section('title', '식사 추가 - MyFit')

@section('content')
<div id="meal-form-app" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('meals.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">
                식사 추가
            </h1>
        </div>
    </div>

    <!-- Input Method Selector -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">입력 방식 선택</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button
                @click="inputMethod = 'search'"
                :class="inputMethod === 'search' ? 'border-primary bg-primary/5' : 'border-gray-300'"
                class="border-2 rounded-lg p-4 hover:border-primary transition-colors"
            >
                <i class="fas fa-search text-2xl mb-2" :class="inputMethod === 'search' ? 'text-primary' : 'text-gray-400'"></i>
                <p class="font-medium text-gray-900">식품 검색</p>
                <p class="text-xs text-gray-500 mt-1">데이터베이스에서 찾기</p>
            </button>

            <button
                @click="inputMethod = 'manual'"
                :class="inputMethod === 'manual' ? 'border-primary bg-primary/5' : 'border-gray-300'"
                class="border-2 rounded-lg p-4 hover:border-primary transition-colors"
            >
                <i class="fas fa-keyboard text-2xl mb-2" :class="inputMethod === 'manual' ? 'text-primary' : 'text-gray-400'"></i>
                <p class="font-medium text-gray-900">직접 입력</p>
                <p class="text-xs text-gray-500 mt-1">수동으로 입력하기</p>
            </button>

            <button
                @click="inputMethod = 'plan'"
                :class="inputMethod === 'plan' ? 'border-primary bg-primary/5' : 'border-gray-300'"
                class="border-2 rounded-lg p-4 hover:border-primary transition-colors"
            >
                <i class="fas fa-calendar-alt text-2xl mb-2" :class="inputMethod === 'plan' ? 'text-primary' : 'text-gray-400'"></i>
                <p class="font-medium text-gray-900">플랜에서</p>
                <p class="text-xs text-gray-500 mt-1">다이어트 플랜 활용</p>
            </button>
        </div>
    </div>

    <!-- Food Search Mode -->
    <div v-if="inputMethod === 'search'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">식품 검색</h3>

        <!-- Search Input -->
        <div class="mb-4">
            <div class="relative">
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="searchFoods"
                    placeholder="식품 이름을 입력하세요 (예: 닭가슴살, 현미밥)"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                >
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>
        </div>

        <!-- Search Results -->
        <div v-if="searchResults.length > 0" class="space-y-2 max-h-96 overflow-y-auto">
            <div
                v-for="food in searchResults"
                :key="food.id"
                @click="selectFood(food)"
                class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:bg-primary/5 cursor-pointer transition-colors"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-medium text-gray-900">@{{ food.name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            @{{ food.category }} · @{{ food.serving_size }}g 기준
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">
                                @{{ food.calories }} kcal
                            </span>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                P @{{ food.protein_g }}g
                            </span>
                            <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">
                                C @{{ food.carbs_g }}g
                            </span>
                            <span class="text-xs px-2 py-1 bg-purple-100 text-purple-800 rounded-full">
                                F @{{ food.fat_g }}g
                            </span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div v-else-if="searchQuery && !searching" class="text-center py-8 text-gray-500">
            <i class="fas fa-search text-4xl mb-2"></i>
            <p>검색 결과가 없습니다</p>
        </div>

        <!-- Searching -->
        <div v-if="searching" class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-primary"></i>
        </div>
    </div>

    <!-- Meal Form -->
    <div v-if="inputMethod !== 'plan'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">식사 정보</h3>

        <form @submit.prevent="saveMeal" class="space-y-4">
            <!-- Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    날짜 *
                </label>
                <input
                    type="date"
                    v-model="form.date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                >
            </div>

            <!-- Meal Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    식사 종류 *
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button
                        type="button"
                        v-for="type in mealTypes"
                        :key="type.value"
                        @click="form.meal_type = type.value"
                        :class="form.meal_type === type.value ? 'border-primary bg-primary text-white' : 'border-gray-300 hover:border-primary'"
                        class="border-2 rounded-lg py-3 font-medium transition-colors"
                    >
                        <i :class="type.icon" class="mr-2"></i>
                        @{{ type.label }}
                    </button>
                </div>
            </div>

            <!-- Food Name (Manual Mode) -->
            <div v-if="inputMethod === 'manual'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    음식 이름 *
                </label>
                <input
                    type="text"
                    v-model="form.food_name"
                    required
                    placeholder="예: 현미밥"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                >
            </div>

            <!-- Selected Food (Search Mode) -->
            <div v-if="inputMethod === 'search' && selectedFood">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    선택한 음식
                </label>
                <div class="border border-primary rounded-lg p-4 bg-primary/5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium text-gray-900">@{{ selectedFood.name }}</h4>
                            <p class="text-sm text-gray-500">@{{ selectedFood.category }}</p>
                        </div>
                        <button type="button" @click="selectedFood = null; form.food_id = null" class="text-gray-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Serving Size -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    섭취량 (g) *
                </label>
                <input
                    type="number"
                    v-model.number="form.serving_size"
                    @input="calculateNutrition"
                    required
                    min="1"
                    step="0.1"
                    placeholder="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                >
            </div>

            <!-- Nutrition (Manual Mode) -->
            <div v-if="inputMethod === 'manual'" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        칼로리 (kcal) *
                    </label>
                    <input
                        type="number"
                        v-model.number="form.calories"
                        required
                        min="0"
                        step="0.1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        단백질 (g) *
                    </label>
                    <input
                        type="number"
                        v-model.number="form.protein_g"
                        required
                        min="0"
                        step="0.1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        탄수화물 (g) *
                    </label>
                    <input
                        type="number"
                        v-model.number="form.carbs_g"
                        required
                        min="0"
                        step="0.1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        지방 (g) *
                    </label>
                    <input
                        type="number"
                        v-model.number="form.fat_g"
                        required
                        min="0"
                        step="0.1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>

            <!-- Meal Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    식사 시간 (선택)
                </label>
                <input
                    type="time"
                    v-model="form.meal_time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                >
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    메모 (선택)
                </label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="식사에 대한 메모를 입력하세요"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                ></textarea>
            </div>

            <!-- Nutrition Preview -->
            <div v-if="form.calories > 0" class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">영양 정보 미리보기</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="text-center">
                        <p class="text-xs text-gray-600">칼로리</p>
                        <p class="text-lg font-bold text-gray-900">@{{ form.calories }}</p>
                        <p class="text-xs text-gray-500">kcal</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600">단백질</p>
                        <p class="text-lg font-bold text-blue-600">@{{ form.protein_g }}g</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600">탄수화물</p>
                        <p class="text-lg font-bold text-yellow-600">@{{ form.carbs_g }}g</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600">지방</p>
                        <p class="text-lg font-bold text-red-600">@{{ form.fat_g }}g</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-3 pt-4">
                <button
                    type="button"
                    @click="$router.back()"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 font-medium"
                >
                    취소
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="flex-1 px-4 py-3 bg-primary hover:bg-primary/90 text-white rounded-md font-medium disabled:opacity-50"
                >
                    <span v-if="saving"><i class="fas fa-spinner fa-spin mr-2"></i>저장 중...</span>
                    <span v-else><i class="fas fa-save mr-2"></i>저장</span>
                </button>
            </div>
        </form>
    </div>

    <!-- From Plan Mode -->
    <div v-if="inputMethod === 'plan'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">다이어트 플랜에서 추가</h3>
        <p class="text-gray-600 mb-4">이 기능은 곧 추가될 예정입니다.</p>
        <a href="{{ route('diet-plan.index') }}" class="inline-flex items-center text-primary hover:text-primary/80">
            <i class="fas fa-calendar-alt mr-2"></i>
            다이어트 플랜 보러가기 →
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
Vue.createApp({
    data() {
        return {
            inputMethod: 'search',
            searchQuery: '',
            searching: false,
            searchResults: [],
            selectedFood: null,
            saving: false,
            mealTypes: [
                { value: 'breakfast', label: '아침', icon: 'fas fa-sunrise' },
                { value: 'lunch', label: '점심', icon: 'fas fa-sun' },
                { value: 'dinner', label: '저녁', icon: 'fas fa-moon' },
                { value: 'snack', label: '간식', icon: 'fas fa-cookie-bite' }
            ],
            form: {
                date: new Date().toISOString().split('T')[0],
                meal_type: 'breakfast',
                food_id: null,
                food_name: '',
                serving_size: 100,
                calories: 0,
                protein_g: 0,
                carbs_g: 0,
                fat_g: 0,
                meal_time: '',
                notes: ''
            }
        }
    },
    methods: {
        async searchFoods() {
            if (!this.searchQuery || this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const response = await axios.get(`/foods?search=${this.searchQuery}&limit=10`);
                this.searchResults = response.data.data.data || [];
            } catch (error) {
                console.error('Error searching foods:', error);
            } finally {
                this.searching = false;
            }
        },
        selectFood(food) {
            this.selectedFood = food;
            this.form.food_id = food.id;
            this.form.food_name = food.name;
            this.form.serving_size = food.serving_size;
            this.calculateNutrition();
            this.searchQuery = '';
            this.searchResults = [];
        },
        calculateNutrition() {
            if (!this.selectedFood || !this.form.serving_size) return;

            const ratio = this.form.serving_size / this.selectedFood.serving_size;

            this.form.calories = Math.round(this.selectedFood.calories * ratio * 10) / 10;
            this.form.protein_g = Math.round(this.selectedFood.protein_g * ratio * 10) / 10;
            this.form.carbs_g = Math.round(this.selectedFood.carbs_g * ratio * 10) / 10;
            this.form.fat_g = Math.round(this.selectedFood.fat_g * ratio * 10) / 10;
        },
        async saveMeal() {
            this.saving = true;
            try {
                await axios.post('/daily-logs/meals', this.form);
                this.showToast('식사가 기록되었습니다.', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("meals.index") }}';
                }, 500);
            } catch (error) {
                console.error('Error saving meal:', error);
                this.showToast('저장에 실패했습니다. 다시 시도해주세요.', 'error');
            } finally {
                this.saving = false;
            }
        },
        showToast(message, type) {
            const event = new CustomEvent('show-toast', {
                detail: { message, type }
            });
            window.dispatchEvent(event);
        }
    }
}).mount('#meal-form-app');

window.addEventListener('show-toast', (e) => {
    if (window.app && window.app.showToast) {
        window.app.showToast(e.detail.message, e.detail.type);
    }
});
</script>
@endpush
