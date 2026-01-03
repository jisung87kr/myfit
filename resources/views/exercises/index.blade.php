@extends('layouts.app')

@section('title', '운동 기록 - MyFit')

@section('content')
<div id="exercise-list-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header with Date Picker -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-heading mb-2">운동 기록</h1>
            <p class="text-gray-500">오늘의 활동량을 확인하고 목표를 달성하세요.</p>
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
                        @change="loadExercises"
                        class="text-sm font-bold text-gray-900 bg-transparent border-none focus:ring-0 text-center w-32 cursor-pointer font-heading"
                    >
                </div>
                <button @click="changeDate(1)" class="p-2 hover:bg-gray-50 text-gray-400 hover:text-gray-600 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <button @click="goToToday" class="ml-2 px-3 py-1.5 text-xs font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors cursor-pointer">
                    오늘
                </button>
            </div>

            <a href="{{ route('exercises.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-2xl transition-all shadow-lg shadow-gray-900/20 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                운동 추가
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
        <!-- Daily Summary Card -->
        <div v-if="summary" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Duration -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">총 운동 시간</p>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 font-heading">@{{ summary.total_duration_minutes || 0 }}</span>
                        <span class="ml-1 text-sm text-gray-500">분</span>
                    </div>
                </div>
            </div>

            <!-- Calories Burned -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">소모 칼로리</p>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 font-heading">@{{ summary.total_calories_burned || 0 }}</span>
                        <span class="ml-1 text-sm text-gray-500">kcal</span>
                    </div>
                </div>
            </div>

            <!-- Exercise Count -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">운동 횟수</p>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 font-heading">@{{ summary.exercise_count || 0 }}</span>
                        <span class="ml-1 text-sm text-gray-500">회</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exercise List -->
        <div v-if="exercises.length > 0" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 font-heading">상세 기록</h3>
                <span class="text-xs font-bold text-gray-400 bg-white border border-gray-200 px-2 py-1 rounded-lg">@{{ exercises.length }} Activities</span>
            </div>
            <div class="divide-y divide-gray-100">
                <div v-for="exercise in exercises" :key="exercise.id" class="p-6 hover:bg-gray-50/80 transition-colors group">
                    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                        <!-- Icon & Main Info -->
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-md transform group-hover:scale-110 transition-transform duration-300" :class="getExerciseTypeClass(exercise.exercise_type)">
                                <svg v-if="exercise.exercise_type === 'cardio'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <svg v-else-if="exercise.exercise_type === 'strength'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-lg">@{{ exercise.exercise_name }}</h4>
                                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @{{ exercise.duration_minutes }}분
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                        @{{ exercise.calories_burned }}kcal
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Tags -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <span v-if="exercise.sets" class="px-3 py-1 bg-gray-100 rounded-lg text-xs font-bold text-gray-600">@{{ exercise.sets }} 세트</span>
                            <span v-if="exercise.reps" class="px-3 py-1 bg-gray-100 rounded-lg text-xs font-bold text-gray-600">@{{ exercise.reps }} 회</span>
                            <span v-if="exercise.distance_km" class="px-3 py-1 bg-gray-100 rounded-lg text-xs font-bold text-gray-600">@{{ exercise.distance_km }} km</span>
                            <span v-if="exercise.intensity" class="px-3 py-1 rounded-lg text-xs font-bold" :class="getIntensityClass(exercise.intensity)">@{{ getIntensityLabel(exercise.intensity) }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="editExercise(exercise.id)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="confirmDelete(exercise)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Notes -->
                    <div v-if="exercise.notes" class="mt-3 pl-4 border-l-2 border-gray-200">
                        <p class="text-sm text-gray-500 italic">"@{{ exercise.notes }}"</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">운동 기록이 없습니다</h3>
            <p class="text-gray-500 mb-6 max-w-sm mx-auto">오늘 어떤 활동을 하셨나요? 가벼운 산책도 훌륭한 운동입니다!</p>
            <a href="{{ route('exercises.create') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors cursor-pointer">
                운동 기록하기
            </a>
        </div>

        <!-- Floating Add Button (Mobile) -->
        <a href="{{ route('exercises.create') }}" class="fixed bottom-6 right-6 lg:hidden w-14 h-14 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-full shadow-lg shadow-primary-500/30 flex items-center justify-center hover:shadow-xl transition-all z-50 cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </a>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all scale-100">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2 font-heading">기록 삭제</h3>
                <p class="text-gray-500 text-center text-sm mb-6">
                    "<span class="font-bold text-gray-700">@{{ exerciseToDelete?.exercise_name }}</span>" 기록을 삭제하시겠습니까?<br>삭제 후에는 복구할 수 없습니다.
                </p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium cursor-pointer">
                        취소
                    </button>
                    <button @click="deleteExercise" :disabled="deleting" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 font-medium cursor-pointer">
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
                const exercisesResponse = await axios.get(`/api/daily-logs/exercises?date=${this.selectedDate}`);
                this.exercises = exercisesResponse.data.data.exercises || [];

                const summaryResponse = await axios.get(`/api/daily-logs/exercises/summary?date=${this.selectedDate}`);
                this.summary = summaryResponse.data.data || { total_duration: 0, total_calories: 0, total_count: 0 };
            } catch (error) {
                console.error('Failed to load exercises:', error);
                if (window.showToast) window.showToast('운동 기록을 불러오는데 실패했습니다.', 'error');
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
                await axios.delete(`/api/daily-logs/exercises/${this.exerciseToDelete.id}`);
                if (window.showToast) window.showToast('운동 기록이 삭제되었습니다.', 'success');

                this.showDeleteModal = false;
                this.exerciseToDelete = null;
                this.loadExercises();
            } catch (error) {
                console.error('Failed to delete exercise:', error);
                if (window.showToast) window.showToast('운동 기록 삭제에 실패했습니다.', 'error');
            } finally {
                this.deleting = false;
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
        getIntensityClass(intensity) {
            const classes = {
                'low': 'bg-green-100 text-green-700',
                'medium': 'bg-amber-100 text-amber-700',
                'high': 'bg-red-100 text-red-700'
            };
            return classes[intensity] || 'bg-gray-100 text-gray-700';
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

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate3d(0, 20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>
@endpush
