@extends('layouts.app')

@section('title', '운동 기록 - MyFit')

@section('content')
<div class="max-w-6xl mx-auto">
    <div id="exercise-list-app">
        <!-- Header with Date Picker -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 font-heading flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        운동 기록
                    </h1>
                    <p class="text-gray-500 mt-1 ml-13">오늘의 운동을 기록하고 관리하세요</p>
                </div>

                <!-- Date Navigation -->
                <div class="flex items-center gap-2">
                    <button @click="changeDate(-1)" class="p-2.5 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="loadExercises"
                        class="px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                    <button @click="changeDate(1)" class="p-2.5 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <button @click="goToToday" class="ml-2 px-4 py-2.5 text-sm font-medium text-primary-600 border border-primary-200 rounded-xl hover:bg-primary-50 transition-colors cursor-pointer">
                        오늘
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-16">
            <div class="w-12 h-12 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-500">운동 기록을 불러오는 중...</p>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Daily Summary -->
            <div v-if="summary" class="bg-gradient-to-br from-primary-50 to-accent-50 rounded-2xl shadow-sm border border-primary-100/50 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 font-heading flex items-center">
                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    오늘의 운동 요약
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <p class="text-sm text-gray-600 mb-1">총 운동 시간</p>
                        <p class="text-2xl font-bold text-primary-600">@{{ summary.total_duration || 0 }}<span class="text-sm font-normal text-gray-600">분</span></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <p class="text-sm text-gray-600 mb-1">소모 칼로리</p>
                        <p class="text-2xl font-bold text-accent-600">@{{ summary.total_calories || 0 }}<span class="text-sm font-normal text-gray-600">kcal</span></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur rounded-xl p-4">
                        <p class="text-sm text-gray-600 mb-1">운동 횟수</p>
                        <p class="text-2xl font-bold text-blue-600">@{{ summary.total_count || 0 }}<span class="text-sm font-normal text-gray-600">회</span></p>
                    </div>
                </div>
            </div>

            <!-- Exercise List -->
            <div v-if="exercises.length > 0" class="space-y-4">
                <div v-for="exercise in exercises" :key="exercise.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-primary-200 transition-all cursor-pointer">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg" :class="getExerciseTypeClass(exercise.exercise_type)">
                                    <svg v-if="exercise.exercise_type === 'cardio'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <svg v-else-if="exercise.exercise_type === 'strength'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                    </svg>
                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">@{{ exercise.exercise_name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        @{{ exercise.exercise_time || '시간 미지정' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Exercise Details -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-primary-50 text-primary-700">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @{{ exercise.duration_minutes }}분
                                </span>
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-accent-50 text-accent-700">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                    </svg>
                                    @{{ exercise.calories_burned }}kcal
                                </span>
                                <span v-if="exercise.sets" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
                                    @{{ exercise.sets }}세트
                                </span>
                                <span v-if="exercise.reps" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-indigo-50 text-indigo-700">
                                    @{{ exercise.reps }}회
                                </span>
                                <span v-if="exercise.distance_km" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-50 text-green-700">
                                    @{{ exercise.distance_km }}km
                                </span>
                                <span v-if="exercise.intensity" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium" :class="getIntensityClass(exercise.intensity)">
                                    @{{ getIntensityLabel(exercise.intensity) }}
                                </span>
                            </div>

                            <!-- Notes -->
                            <p v-if="exercise.notes" class="text-sm text-gray-600 italic">
                                @{{ exercise.notes }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 ml-4">
                            <button @click="editExercise(exercise.id)" class="p-2.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="confirmDelete(exercise)" class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2 font-heading">운동 기록이 없습니다</h3>
                <p class="text-gray-500 mb-6">오늘의 운동을 기록해보세요!</p>
                <a href="{{ route('exercises.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-accent-500 text-white font-medium rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-xl transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    운동 추가하기
                </a>
            </div>
        </div>

        <!-- Floating Add Button (Mobile) -->
        <a href="{{ route('exercises.create') }}" class="fixed bottom-6 right-6 lg:hidden w-14 h-14 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-full shadow-lg shadow-primary-500/30 flex items-center justify-center hover:shadow-xl transition-all z-50 cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 font-heading">운동 기록 삭제</h3>
                <p class="text-gray-600 mb-6">
                    "<span class="font-medium">@{{ exerciseToDelete?.exercise_name }}</span>" 기록을 삭제하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium cursor-pointer">
                        취소
                    </button>
                    <button @click="deleteExercise" :disabled="deleting" class="px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 font-medium cursor-pointer">
                        <span v-if="deleting">삭제 중...</span>
                        <span v-else>삭제</span>
                    </button>
                </div>
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
            selectedDate: new Date().toISOString().split('T')[0],
            exercises: [],
            summary: null,
            loading: false,
            showDeleteModal: false,
            exerciseToDelete: null,
            deleting: false
        }
    },
    mounted() {
        this.loadExercises();
    },
    methods: {
        async loadExercises() {
            this.loading = true;
            try {
                const exercisesResponse = await axios.get(`/daily-logs/exercises?date=${this.selectedDate}`);
                this.exercises = exercisesResponse.data.data || [];

                const summaryResponse = await axios.get(`/daily-logs/exercises/summary?date=${this.selectedDate}`);
                this.summary = summaryResponse.data.data || { total_duration: 0, total_calories: 0, total_count: 0 };
            } catch (error) {
                console.error('Failed to load exercises:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '운동 기록을 불러오는데 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.loading = false;
            }
        },
        changeDate(days) {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() + days);
            this.selectedDate = date.toISOString().split('T')[0];
            this.loadExercises();
        },
        goToToday() {
            this.selectedDate = new Date().toISOString().split('T')[0];
            this.loadExercises();
        },
        editExercise(id) {
            window.location.href = `{{ route('exercises.create') }}?id=${id}`;
        },
        confirmDelete(exercise) {
            this.exerciseToDelete = exercise;
            this.showDeleteModal = true;
        },
        async deleteExercise() {
            if (!this.exerciseToDelete) return;

            this.deleting = true;
            try {
                await axios.delete(`/daily-logs/exercises/${this.exerciseToDelete.id}`);

                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '운동 기록이 삭제되었습니다.', type: 'success' }
                }));

                this.showDeleteModal = false;
                this.exerciseToDelete = null;
                this.loadExercises();
            } catch (error) {
                console.error('Failed to delete exercise:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '운동 기록 삭제에 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.deleting = false;
            }
        },
        getExerciseTypeClass(type) {
            const types = {
                'cardio': 'bg-gradient-to-br from-red-500 to-orange-500',
                'strength': 'bg-gradient-to-br from-blue-500 to-indigo-500',
                'flexibility': 'bg-gradient-to-br from-green-500 to-teal-500',
                'sports': 'bg-gradient-to-br from-amber-500 to-orange-500',
                'other': 'bg-gradient-to-br from-gray-500 to-slate-500'
            };
            return types[type] || 'bg-gradient-to-br from-gray-500 to-slate-500';
        },
        getIntensityClass(intensity) {
            const classes = {
                'low': 'bg-green-50 text-green-700',
                'medium': 'bg-amber-50 text-amber-700',
                'high': 'bg-red-50 text-red-700'
            };
            return classes[intensity] || 'bg-gray-50 text-gray-700';
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
}).mount('#exercise-list-app');
</script>
@endpush
