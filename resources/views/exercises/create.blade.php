@extends('layouts.app')

@section('title', '운동 추가 - MyFit')

@section('content')
<div id="exercise-form-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="flex items-center space-x-4 mb-8 animate-fade-in-up">
        <a href="{{ route('exercises.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 font-heading">
            @{{ isEditMode ? '운동 수정' : '운동 추가' }}
        </h1>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Left Column: Method & Search -->
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

            <!-- Search Box -->
            <div v-if="inputMethod === 'search'" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-4 font-heading">운동 검색</h3>
                <div class="relative mb-4">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="searchExercises"
                        placeholder="예: 런닝, 요가"
                        class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border-none rounded-2xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500 transition-all"
                    >
                    <svg class="w-5 h-5 text-gray-400 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Results -->
                <div v-if="searchResults.length > 0" class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <div
                        v-for="exercise in searchResults"
                        :key="exercise.id"
                        @click="selectExercise(exercise)"
                        class="p-4 rounded-2xl border border-gray-100 hover:border-primary-200 hover:bg-primary-50/50 cursor-pointer transition-all group"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-gray-900 group-hover:text-primary-700 transition-colors">@{{ exercise.name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    @{{ getExerciseTypeLabel(exercise.exercise_type) }} · @{{ exercise.calories_per_hour }} kcal/hr
                                </p>
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
                    <p class="text-sm text-gray-400">운동명을 검색해보세요</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="lg:col-span-8">
            <form @submit.prevent="handleSubmit" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 font-heading">상세 정보 입력</h2>
                    <span v-if="inputMethod === 'search' && selectedExercise" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        선택됨: @{{ selectedExercise.name }}
                    </span>
                </div>

                <!-- Basic Fields -->
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
                            v-model="form.exercise_time"
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all"
                        >
                    </div>

                    <!-- Exercise Type -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">운동 종류</label>
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                            <button
                                type="button"
                                v-for="type in exerciseTypes"
                                :key="type.value"
                                @click="form.exercise_type = type.value"
                                :class="form.exercise_type === type.value ? 'bg-gray-900 text-white shadow-lg shadow-gray-900/20' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'"
                                class="py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 flex flex-col sm:flex-row items-center justify-center gap-2"
                            >
                                <span>@{{ type.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Name (Manual) -->
                    <div v-if="inputMethod === 'manual'" class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">운동 이름</label>
                        <input
                            type="text"
                            v-model="form.exercise_name"
                            required
                            placeholder="예: 아침 조깅"
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all placeholder-gray-400"
                        >
                    </div>
                </div>

                <!-- Stats Fields -->
                <div class="bg-gray-50 rounded-3xl p-6 mb-8 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-heading">활동 수치</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">운동 시간 (분)</label>
                            <input
                                type="number"
                                v-model.number="form.duration_minutes"
                                @input="calculateCalories"
                                required
                                min="1"
                                class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-bold text-lg"
                            >
                        </div>

                        <!-- Calories -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">소모 칼로리 (kcal)</label>
                            <input
                                type="number"
                                v-model.number="form.calories_burned"
                                required
                                min="0"
                                class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-bold text-lg text-accent-600"
                            >
                            <p v-if="selectedExercise" class="text-xs text-gray-500 mt-2">
                                * 자동 계산됨 (@{{ selectedExercise.calories_per_hour }} kcal/hr 기준)
                            </p>
                        </div>

                        <!-- Dynamic Fields based on Type -->
                        <template v-if="form.exercise_type === 'strength'">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">세트 수</label>
                                <input type="number" v-model.number="form.sets" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">반복 횟수</label>
                                <input type="number" v-model.number="form.reps" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all">
                            </div>
                        </template>

                        <div v-if="form.exercise_type === 'cardio'">
                            <label class="block text-sm font-bold text-gray-700 mb-2">거리 (km)</label>
                            <input type="number" v-model.number="form.distance_km" step="0.1" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">강도</label>
                            <select v-model="form.intensity" class="w-full px-4 py-3 bg-white border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all">
                                <option value="">선택 안함</option>
                                <option value="low">낮음</option>
                                <option value="medium">보통</option>
                                <option value="high">높음</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div v-if="form.exercise_name && form.duration_minutes > 0" class="bg-gradient-to-br from-primary-50 to-accent-50 rounded-3xl p-6 mb-8 border border-primary-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-heading flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        기록 미리보기
                    </h3>
                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" :class="getExerciseTypeClass(form.exercise_type)">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-lg">@{{ form.exercise_name }}</h4>
                                <p class="text-sm text-gray-600">@{{ getExerciseTypeLabel(form.exercise_type) }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-white rounded-lg text-sm font-bold text-gray-700 shadow-sm border border-gray-100">
                                @{{ form.duration_minutes }}분
                            </span>
                            <span class="px-3 py-1 bg-white rounded-lg text-sm font-bold text-accent-600 shadow-sm border border-gray-100">
                                @{{ form.calories_burned }} kcal
                            </span>
                            <span v-if="form.distance_km" class="px-3 py-1 bg-white rounded-lg text-sm font-bold text-blue-600 shadow-sm border border-gray-100">
                                @{{ form.distance_km }} km
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mb-6 p-4 rounded-2xl bg-red-50 text-red-600 text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @{{ errorMessage }}
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-2">메모</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all resize-none"
                        placeholder="메모를 남겨보세요"
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
                        :disabled="loading || (inputMethod === 'search' && !selectedExercise) || (inputMethod === 'manual' && !form.exercise_name)"
                        class="flex-[2] px-6 py-4 bg-gradient-to-r from-primary-600 to-accent-600 hover:from-primary-700 hover:to-accent-700 text-white font-bold rounded-2xl shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-[0.98] cursor-pointer"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            저장 중...
                        </span>
                        <span v-else>@{{ isEditMode ? '수정 완료' : '기록 저장' }}</span>
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
            searchResults: [],
            searching: false,
            selectedExercise: null,
            isEditMode: false,
            form: {
                date: new Date().toISOString().split('T')[0],
                exercise_time: '',
                exercise_id: null,
                exercise_name: '',
                exercise_type: 'cardio',
                duration_minutes: null,
                calories_burned: 0,
                sets: null,
                reps: null,
                distance_km: null,
                intensity: '',
                notes: ''
            },
            loading: false,
            errorMessage: '',
            exerciseTypes: [
                { value: 'cardio', label: '유산소' },
                { value: 'strength', label: '근력' },
                { value: 'flexibility', label: '유연성' },
                { value: 'sports', label: '스포츠' },
                { value: 'other', label: '기타' }
            ]
        }
    },
    mounted() {
        const urlParams = new URLSearchParams(window.location.search);
        const exerciseId = urlParams.get('id');
        if (exerciseId) {
            this.isEditMode = true;
            this.loadExercise(exerciseId);
        }
    },
    methods: {
        async searchExercises() {
            if (!this.searchQuery || this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const response = await axios.get(`/api/exercises?search=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = response.data.data || [];
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                this.searching = false;
            }
        },
        selectExercise(exercise) {
            this.selectedExercise = exercise;
            this.form.exercise_id = exercise.id;
            this.form.exercise_name = exercise.name;
            this.form.exercise_type = exercise.exercise_type;
            this.searchQuery = exercise.name;
            this.searchResults = [];

            if (this.form.duration_minutes) {
                this.calculateCalories();
            }
        },
        calculateCalories() {
            if (!this.selectedExercise || !this.form.duration_minutes) return;

            this.form.calories_burned = Math.round(
                (this.selectedExercise.calories_per_hour / 60) * this.form.duration_minutes * 10
            ) / 10;
        },
        async loadExercise(id) {
            try {
                const response = await axios.get(`/api/daily-logs/exercises/${id}`);
                const exercise = response.data.data;

                this.form = {
                    date: exercise.date,
                    exercise_time: exercise.exercise_time || '',
                    exercise_id: exercise.exercise_id,
                    exercise_name: exercise.exercise_name,
                    exercise_type: exercise.exercise_type,
                    duration_minutes: exercise.duration_minutes,
                    calories_burned: exercise.calories_burned,
                    sets: exercise.sets,
                    reps: exercise.reps,
                    distance_km: exercise.distance_km,
                    intensity: exercise.intensity || '',
                    notes: exercise.notes || ''
                };

                if (exercise.exercise_id) {
                    this.inputMethod = 'search';
                } else {
                    this.inputMethod = 'manual';
                }
            } catch (error) {
                console.error('Failed to load exercise:', error);
                if (window.showToast) window.showToast('운동 정보를 불러오는데 실패했습니다.', 'error');
            }
        },
        async handleSubmit() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const url = this.isEditMode
                    ? `/api/daily-logs/exercises/${new URLSearchParams(window.location.search).get('id')}`
                    : '/api/daily-logs/exercises';

                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    if (window.showToast) window.showToast(this.isEditMode ? '운동 기록이 수정되었습니다.' : '운동 기록이 추가되었습니다.', 'success');

                    setTimeout(() => {
                        window.location.href = '{{ route("exercises.index") }}';
                    }, 1000);
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.errorMessage = Object.values(errors).flat().join(' ');
                } else {
                    this.errorMessage = '운동 기록 저장에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.loading = false;
            }
        },
        getExerciseTypeClass(type) {
            const types = {
                'cardio': 'bg-gradient-to-br from-red-400 to-orange-500 shadow-orange-500/30',
                'strength': 'bg-gradient-to-br from-blue-400 to-indigo-500 shadow-blue-500/30',
                'flexibility': 'bg-gradient-to-br from-emerald-400 to-teal-500 shadow-emerald-500/30',
                'sports': 'bg-gradient-to-br from-amber-400 to-yellow-500 shadow-amber-500/30',
                'other': 'bg-gradient-to-br from-gray-400 to-slate-500 shadow-gray-500/30'
            };
            return types[type] || types['other'];
        },
        getExerciseTypeLabel(type) {
            const labels = {
                'cardio': '유산소',
                'strength': '근력',
                'flexibility': '유연성',
                'sports': '스포츠',
                'other': '기타'
            };
            return labels[type] || type;
        },
        getIntensityClass(intensity) {
            const classes = {
                'low': 'bg-green-100 text-green-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'high': 'bg-red-100 text-red-800'
            };
            return classes[intensity] || 'bg-gray-100 text-gray-800';
        },
        getIntensityLabel(intensity) {
            const labels = {
                'low': '낮음',
                'medium': '보통',
                'high': '높음'
            };
            return labels[intensity] || intensity;
        }
    }
}).mount('#exercise-form-app');
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