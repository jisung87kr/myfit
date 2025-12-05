@extends('layouts.app')

@section('title', '운동 추가 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div id="exercise-form-app">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-running text-primary mr-2"></i>@{{ isEditMode ? '운동 수정' : '운동 추가' }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">오늘의 운동을 기록하세요</p>
                </div>
                <a href="{{ route('exercises.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>
        </div>

        <!-- Input Method Selector -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">입력 방식 선택</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button
                    @click="inputMethod = 'search'"
                    :class="['p-4 rounded-lg border-2 transition-all', inputMethod === 'search' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                >
                    <i :class="['fas fa-search text-2xl mb-2', inputMethod === 'search' ? 'text-primary' : 'text-gray-400']"></i>
                    <p :class="['font-semibold', inputMethod === 'search' ? 'text-primary' : 'text-gray-700']">운동 검색</p>
                    <p class="text-xs text-gray-600 mt-1">데이터베이스에서 검색</p>
                </button>
                <button
                    @click="inputMethod = 'manual'"
                    :class="['p-4 rounded-lg border-2 transition-all', inputMethod === 'manual' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                >
                    <i :class="['fas fa-keyboard text-2xl mb-2', inputMethod === 'manual' ? 'text-primary' : 'text-gray-400']"></i>
                    <p :class="['font-semibold', inputMethod === 'manual' ? 'text-primary' : 'text-gray-700']">직접 입력</p>
                    <p class="text-xs text-gray-600 mt-1">모든 정보 수동 입력</p>
                </button>
            </div>
        </div>

        <!-- Exercise Search (Search Method) -->
        <div v-if="inputMethod === 'search'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">운동 검색</h2>

            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="searchExercises"
                    placeholder="운동 이름을 입력하세요 (예: 런닝, 벤치프레스, 요가)"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                >
            </div>

            <!-- Search Results -->
            <div v-if="searchResults.length > 0" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                <div
                    v-for="exercise in searchResults"
                    :key="exercise.id"
                    @click="selectExercise(exercise)"
                    class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm" :class="getExerciseTypeClass(exercise.exercise_type)">
                                    <i :class="getExerciseIcon(exercise.exercise_type)"></i>
                                </span>
                                <h3 class="font-semibold text-gray-900">@{{ exercise.name }}</h3>
                            </div>
                            <p class="text-sm text-gray-600 ml-10">@{{ getExerciseTypeLabel(exercise.exercise_type) }} · @{{ exercise.calories_per_hour }}kcal/시간</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
            </div>
            <p v-else-if="searchQuery && !searching" class="text-center text-gray-500 py-8">
                검색 결과가 없습니다.
            </p>
            <p v-else-if="searching" class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin mr-2"></i>검색 중...
            </p>
        </div>

        <!-- Exercise Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">기본 정보</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            날짜 <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.date"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Time -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            시간
                        </label>
                        <input
                            type="time"
                            v-model="form.exercise_time"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Exercise Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            운동 이름 <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.exercise_name"
                            :readonly="inputMethod === 'search' && selectedExercise"
                            required
                            placeholder="예: 런닝, 벤치프레스, 요가"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Exercise Type -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            운동 종류 <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <button
                                v-for="type in exerciseTypes"
                                :key="type.value"
                                type="button"
                                @click="form.exercise_type = type.value"
                                :class="['p-3 rounded-lg border-2 transition-all text-center', form.exercise_type === type.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300']"
                            >
                                <i :class="[type.icon, 'text-xl mb-1', form.exercise_type === type.value ? 'text-primary' : 'text-gray-400']"></i>
                                <p :class="['text-sm font-medium', form.exercise_type === type.value ? 'text-primary' : 'text-gray-700']">@{{ type.label }}</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exercise Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">운동 상세</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            운동 시간 (분) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="form.duration_minutes"
                            @input="calculateCalories"
                            required
                            min="1"
                            placeholder="30"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Calories Burned -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            소모 칼로리 (kcal) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model.number="form.calories_burned"
                            required
                            min="0"
                            step="0.1"
                            placeholder="200"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        <p v-if="selectedExercise" class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>@{{ selectedExercise.calories_per_hour }}kcal/시간 기준으로 자동 계산
                        </p>
                    </div>

                    <!-- Sets (for strength training) -->
                    <div v-if="form.exercise_type === 'strength'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            세트 수
                        </label>
                        <input
                            type="number"
                            v-model.number="form.sets"
                            min="1"
                            placeholder="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Reps (for strength training) -->
                    <div v-if="form.exercise_type === 'strength'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            반복 횟수
                        </label>
                        <input
                            type="number"
                            v-model.number="form.reps"
                            min="1"
                            placeholder="12"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Distance (for cardio) -->
                    <div v-if="form.exercise_type === 'cardio'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            거리 (km)
                        </label>
                        <input
                            type="number"
                            v-model.number="form.distance_km"
                            min="0"
                            step="0.1"
                            placeholder="5.0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>

                    <!-- Intensity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            강도
                        </label>
                        <select
                            v-model="form.intensity"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">선택 안함</option>
                            <option value="low">낮음</option>
                            <option value="medium">보통</option>
                            <option value="high">높음</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            메모
                        </label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            placeholder="운동에 대한 메모를 입력하세요..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Exercise Preview -->
            <div v-if="form.exercise_name && form.duration_minutes > 0" class="bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-eye text-primary mr-2"></i>미리보기
                </h2>
                <div class="bg-white rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg" :class="getExerciseTypeClass(form.exercise_type)">
                            <i :class="getExerciseIcon(form.exercise_type)"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">@{{ form.exercise_name }}</h3>
                            <p class="text-sm text-gray-500">@{{ getExerciseTypeLabel(form.exercise_type) }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                            <i class="far fa-clock mr-1"></i>@{{ form.duration_minutes }}분
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-secondary/10 text-secondary">
                            <i class="fas fa-fire mr-1"></i>@{{ form.calories_burned }}kcal
                        </span>
                        <span v-if="form.sets" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent/10 text-accent">
                            <i class="fas fa-dumbbell mr-1"></i>@{{ form.sets }}세트
                        </span>
                        <span v-if="form.reps" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-redo mr-1"></i>@{{ form.reps }}회
                        </span>
                        <span v-if="form.distance_km" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-route mr-1"></i>@{{ form.distance_km }}km
                        </span>
                        <span v-if="form.intensity" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="getIntensityClass(form.intensity)">
                            <i class="fas fa-tachometer-alt mr-1"></i>@{{ getIntensityLabel(form.intensity) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">@{{ errorMessage }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    :disabled="loading"
                    class="flex-1 bg-primary text-white py-3 px-6 rounded-lg font-medium hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="loading"><i class="fas fa-spinner fa-spin mr-2"></i>저장 중...</span>
                    <span v-else><i class="fas fa-save mr-2"></i>@{{ isEditMode ? '수정하기' : '추가하기' }}</span>
                </button>
                <a
                    href="{{ route('exercises.index') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center"
                >
                    취소
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const { createApp } = Vue;

createApp({
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
                { value: 'cardio', label: '유산소', icon: 'fas fa-running' },
                { value: 'strength', label: '근력', icon: 'fas fa-dumbbell' },
                { value: 'flexibility', label: '유연성', icon: 'fas fa-heart' },
                { value: 'sports', label: '스포츠', icon: 'fas fa-basketball-ball' },
                { value: 'other', label: '기타', icon: 'fas fa-shoe-prints' }
            ]
        }
    },
    mounted() {
        // Check if editing existing exercise
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
                const response = await axios.get(`/exercises?search=${encodeURIComponent(this.searchQuery)}`);
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

            // Auto-calculate calories if duration is set
            if (this.form.duration_minutes) {
                this.calculateCalories();
            }
        },
        calculateCalories() {
            if (!this.selectedExercise || !this.form.duration_minutes) return;

            // Calculate based on calories per hour
            this.form.calories_burned = Math.round(
                (this.selectedExercise.calories_per_hour / 60) * this.form.duration_minutes * 10
            ) / 10;
        },
        async loadExercise(id) {
            try {
                const response = await axios.get(`/daily-logs/exercises/${id}`);
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
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '운동 정보를 불러오는데 실패했습니다.', type: 'error' }
                }));
            }
        },
        async handleSubmit() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const url = this.isEditMode
                    ? `/daily-logs/exercises/${new URLSearchParams(window.location.search).get('id')}`
                    : '/daily-logs/exercises';

                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: {
                            message: this.isEditMode ? '운동 기록이 수정되었습니다.' : '운동 기록이 추가되었습니다.',
                            type: 'success'
                        }
                    }));

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
                'cardio': 'bg-red-500',
                'strength': 'bg-blue-500',
                'flexibility': 'bg-green-500',
                'sports': 'bg-orange-500',
                'other': 'bg-gray-500'
            };
            return types[type] || 'bg-gray-500';
        },
        getExerciseIcon(type) {
            const icons = {
                'cardio': 'fas fa-running',
                'strength': 'fas fa-dumbbell',
                'flexibility': 'fas fa-heart',
                'sports': 'fas fa-basketball-ball',
                'other': 'fas fa-shoe-prints'
            };
            return icons[type] || 'fas fa-shoe-prints';
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
@endpush
