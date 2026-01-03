@extends('layouts.app')

@section('title', '식사 추가 - MyFit')

@section('content')
<div id="meal-form-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="flex items-center space-x-4 mb-8 animate-fade-in-up">
        <a href="{{ route('meals.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 font-heading">식사 추가</h1>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Left Column: Input Method & Search -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Method Selector -->
            <div class="bg-white rounded-3xl p-2 shadow-sm border border-gray-100 flex p-1.5">
                <button
                    @click="inputMethod = 'search'"
                    :class="inputMethod === 'search' ? 'bg-primary-50 text-primary-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-3 px-4 rounded-2xl text-sm font-bold transition-all duration-200 flex flex-col items-center gap-1 cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    검색
                </button>
                <button
                    @click="inputMethod = 'manual'"
                    :class="inputMethod === 'manual' ? 'bg-primary-50 text-primary-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-3 px-4 rounded-2xl text-sm font-bold transition-all duration-200 flex flex-col items-center gap-1 cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    직접 입력
                </button>
            </div>

            <!-- Search Box (Search Mode) -->
            <div v-if="inputMethod === 'search'" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-4 font-heading">식품 검색</h3>
                <div class="relative mb-4">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="searchFoods"
                        placeholder="예: 닭가슴살, 현미밥"
                        class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border-none rounded-2xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500 transition-all"
                    >
                    <svg class="w-5 h-5 text-gray-400 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Results -->
                <div v-if="searchResults.length > 0" class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <div
                        v-for="food in searchResults"
                        :key="food.id"
                        @click="selectFood(food)"
                        class="p-4 rounded-2xl border border-gray-100 hover:border-primary-200 hover:bg-primary-50/50 cursor-pointer transition-all group"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors">@{{ food.name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">@{{ food.category }} · @{{ food.serving_size }}g</p>
                                <div class="flex gap-2 mt-2">
                                    <span class="text-xs font-medium text-gray-600">@{{ food.calories }} kcal</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-primary-600 group-hover:border-primary-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty/Loading States -->
                <div v-else-if="searching" class="text-center py-12">
                    <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-sm text-gray-400">검색 중...</p>
                </div>
                <div v-else-if="searchQuery && !searchResults.length" class="text-center py-12">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">검색 결과가 없습니다</p>
                </div>
                <div v-else class="text-center py-12">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">식품명을 검색해보세요</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="lg:col-span-8">
            <form @submit.prevent="saveMeal" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 font-heading">식사 정보 입력</h2>
                    <span v-if="inputMethod === 'search' && selectedFood" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        선택됨: @{{ selectedFood.name }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">날짜</label>
                        <input
                            type="date"
                            v-model="form.date"
                            required
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all"
                        >
                    </div>

                    <!-- Time -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">시간 (선택)</label>
                        <input
                            type="time"
                            v-model="form.meal_time"
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all"
                        >
                    </div>

                    <!-- Meal Type -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">식사 종류</label>
                        <div class="grid grid-cols-4 gap-3">
                            <button
                                type="button"
                                v-for="type in mealTypes"
                                :key="type.value"
                                @click="form.meal_type = type.value"
                                :class="form.meal_type === type.value ? 'bg-gray-900 text-white shadow-lg shadow-gray-900/20' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'"
                                class="py-3 rounded-2xl text-sm font-bold transition-all duration-200"
                            >
                                @{{ type.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Food Name (Manual) -->
                    <div v-if="inputMethod === 'manual'" class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">음식 이름</label>
                        <input
                            type="text"
                            v-model="form.food_name"
                            required
                            placeholder="예: 집밥 정식"
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all placeholder-gray-400"
                        >
                    </div>

                    <!-- Serving Size -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">섭취량 (g)</label>
                        <div class="relative">
                            <input
                                type="number"
                                v-model.number="form.serving_size"
                                @input="calculateNutrition"
                                required
                                min="1"
                                step="0.1"
                                class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-bold text-lg"
                            >
                            <span class="absolute right-4 top-3.5 text-gray-400 font-medium">g</span>
                        </div>
                    </div>
                </div>

                <!-- Nutrition Info Card -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-3xl p-6 mb-8 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-heading">영양 성분 상세</h3>
                    
                    <div v-if="inputMethod === 'manual'">
                         <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">칼로리</label>
                                <input type="number" v-model.number="form.calories" class="w-full px-3 py-2 bg-white rounded-xl border-none text-center font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">단백질</label>
                                <input type="number" v-model.number="form.protein_g" class="w-full px-3 py-2 bg-white rounded-xl border-none text-center font-bold text-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">탄수화물</label>
                                <input type="number" v-model.number="form.carbs_g" class="w-full px-3 py-2 bg-white rounded-xl border-none text-center font-bold text-amber-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">지방</label>
                                <input type="number" v-model.number="form.fat_g" class="w-full px-3 py-2 bg-white rounded-xl border-none text-center font-bold text-orange-600">
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-2xl text-center shadow-sm">
                            <p class="text-xs text-gray-500 mb-1">칼로리</p>
                            <p class="text-xl font-bold text-gray-900">@{{ form.calories }}</p>
                            <p class="text-xs text-gray-400">kcal</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl text-center shadow-sm border-b-2 border-blue-100">
                            <p class="text-xs text-gray-500 mb-1">단백질</p>
                            <p class="text-xl font-bold text-blue-600">@{{ form.protein_g }}</p>
                            <p class="text-xs text-gray-400">g</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl text-center shadow-sm border-b-2 border-amber-100">
                            <p class="text-xs text-gray-500 mb-1">탄수화물</p>
                            <p class="text-xl font-bold text-amber-600">@{{ form.carbs_g }}</p>
                            <p class="text-xs text-gray-400">g</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl text-center shadow-sm border-b-2 border-orange-100">
                            <p class="text-xs text-gray-500 mb-1">지방</p>
                            <p class="text-xl font-bold text-orange-600">@{{ form.fat_g }}</p>
                            <p class="text-xs text-gray-400">g</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-2">메모 (선택)</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="식사에 대한 간단한 메모를 남겨보세요."
                        class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all placeholder-gray-400 resize-none"
                    ></textarea>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button
                        type="button"
                        @click="$router.back()"
                        class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-colors cursor-pointer"
                    >
                        취소
                    </button>
                    <button
                        type="submit"
                        :disabled="saving || (inputMethod === 'search' && !selectedFood) || (inputMethod === 'manual' && !form.food_name)"
                        class="flex-[2] px-6 py-4 bg-gradient-to-r from-primary-600 to-accent-600 hover:from-primary-700 hover:to-accent-700 text-white font-bold rounded-2xl shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-[0.98] cursor-pointer"
                    >
                        <span v-if="saving" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            저장 중...
                        </span>
                        <span v-else>기록 저장하기</span>
                    </button>
                </div>
            </form>
        </div>
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
                { value: 'breakfast', label: '아침' },
                { value: 'lunch', label: '점심' },
                { value: 'dinner', label: '저녁' },
                { value: 'snack', label: '간식' }
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
            if (window.showToast) {
                window.showToast(message, type);
            }
        }
    }
}).mount('#meal-form-app');
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
    width: 6px;
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