@extends('layouts.app')

@section('title', '운동 기록 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div id="exercise-list-app">
        <!-- Header with Date Picker -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-running text-primary mr-2"></i>운동 기록
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">오늘의 운동을 기록하고 관리하세요</p>
                </div>

                <!-- Date Navigation -->
                <div class="flex items-center gap-2">
                    <button @click="changeDate(-1)" class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="loadExercises"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                    <button @click="changeDate(1)" class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button @click="goToToday" class="ml-2 px-4 py-2 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                        오늘
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">운동 기록을 불러오는 중...</p>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Daily Summary -->
            <div v-if="summary" class="bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-line text-primary mr-2"></i>오늘의 운동 요약
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">총 운동 시간</p>
                        <p class="text-2xl font-bold text-primary">@{{ summary.total_duration || 0 }}<span class="text-sm font-normal text-gray-600">분</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">소모 칼로리</p>
                        <p class="text-2xl font-bold text-secondary">@{{ summary.total_calories || 0 }}<span class="text-sm font-normal text-gray-600">kcal</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">운동 횟수</p>
                        <p class="text-2xl font-bold text-accent">@{{ summary.total_count || 0 }}<span class="text-sm font-normal text-gray-600">회</span></p>
                    </div>
                </div>
            </div>

            <!-- Exercise List -->
            <div v-if="exercises.length > 0" class="space-y-4">
                <div v-for="exercise in exercises" :key="exercise.id" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg" :class="getExerciseTypeClass(exercise.exercise_type)">
                                    <i :class="getExerciseIcon(exercise.exercise_type)"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">@{{ exercise.exercise_name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-clock mr-1"></i>@{{ exercise.exercise_time || '시간 미지정' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Exercise Details -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                                    <i class="far fa-clock mr-1"></i>@{{ exercise.duration_minutes }}분
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-secondary/10 text-secondary">
                                    <i class="fas fa-fire mr-1"></i>@{{ exercise.calories_burned }}kcal
                                </span>
                                <span v-if="exercise.sets" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-accent/10 text-accent">
                                    <i class="fas fa-dumbbell mr-1"></i>@{{ exercise.sets }}세트
                                </span>
                                <span v-if="exercise.reps" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-redo mr-1"></i>@{{ exercise.reps }}회
                                </span>
                                <span v-if="exercise.distance_km" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-route mr-1"></i>@{{ exercise.distance_km }}km
                                </span>
                                <span v-if="exercise.intensity" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="getIntensityClass(exercise.intensity)">
                                    <i class="fas fa-tachometer-alt mr-1"></i>@{{ getIntensityLabel(exercise.intensity) }}
                                </span>
                            </div>

                            <!-- Notes -->
                            <p v-if="exercise.notes" class="text-sm text-gray-600 italic">
                                <i class="far fa-comment-dots mr-1"></i>@{{ exercise.notes }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 ml-4">
                            <button @click="editExercise(exercise.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="수정">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="confirmDelete(exercise)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="삭제">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-running text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">운동 기록이 없습니다</h3>
                <p class="text-gray-600 mb-6">오늘의 운동을 기록해보세요!</p>
                <a href="{{ route('exercises.create') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-plus mr-2"></i>운동 추가하기
                </a>
            </div>
        </div>

        <!-- Floating Add Button (Mobile) -->
        <a href="{{ route('exercises.create') }}" class="fixed bottom-6 right-6 lg:hidden w-14 h-14 bg-primary text-white rounded-full shadow-lg flex items-center justify-center hover:bg-primary/90 transition-colors z-50">
            <i class="fas fa-plus text-xl"></i>
        </a>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">운동 기록 삭제</h3>
                <p class="text-gray-600 mb-6">
                    "<span class="font-medium">@{{ exerciseToDelete?.exercise_name }}</span>" 기록을 삭제하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        취소
                    </button>
                    <button @click="deleteExercise" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                        <span v-if="deleting"><i class="fas fa-spinner fa-spin mr-1"></i>삭제 중...</span>
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
                // Load exercises for the selected date
                const exercisesResponse = await axios.get(`/daily-logs/exercises?date=${this.selectedDate}`);
                this.exercises = exercisesResponse.data.data || [];

                // Load summary
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
}).mount('#exercise-list-app');
</script>
@endpush
