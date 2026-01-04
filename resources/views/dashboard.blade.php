@extends('layouts.app')

@section('title', '대시보드 - MyFit')

@section('content')
<div id="dashboard-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-primary-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="space-y-8 animate-fade-in-up">
        <!-- Header Section -->
        <header class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-gray-500 font-medium mb-1">Welcome back</p>
                <h1 class="text-4xl font-bold text-gray-900 font-heading tracking-tight">
                    {{ auth()->user()->name }}님, <span class="text-primary-600">오늘도 화이팅!</span>
                </h1>
            </div>
            <div class="flex items-center space-x-3 bg-white/80 backdrop-blur-sm px-5 py-2.5 rounded-2xl shadow-sm border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-sm">
                    <p class="text-gray-500 font-medium">오늘 날짜</p>
                    <p class="text-gray-900 font-bold">{{ now()->format('Y년 m월 d일') }}</p>
                </div>
            </div>
        </header>

        <!-- Main Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- 1. Hero: Calorie Balance (Span 6 on desktop) -->
            <div class="md:col-span-8 lg:col-span-6 bg-white rounded-3xl p-8 shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden group">
{{--                <div class="absolute top-0 right-0 p-8 opacity-5">--}}
{{--                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>--}}
{{--                </div>--}}

                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 font-heading">오늘의 에너지</h2>
                            <p class="text-gray-500 text-sm mt-1">목표 달성까지</p>
                        </div>
                        <div class="px-3 py-1 bg-primary-50 text-primary-700 rounded-full text-xs font-bold uppercase tracking-wider">
                            Daily Goal
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center py-4">
                        <div class="relative w-48 h-48 md:w-56 md:h-56">
                            <!-- Circular Progress SVG -->
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="50%" cy="50%" r="45%" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100" />
                                <circle cx="50%" cy="50%" r="45%" stroke="currentColor" stroke-width="12" fill="transparent"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="circumference - (calorieBalance.percentage / 100) * circumference"
                                    class="text-primary-500 transition-all duration-1000 ease-out"
                                    stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-4xl md:text-5xl font-bold text-gray-900 tracking-tighter">
                                    @{{ calorieBalance.percentage }}<span class="text-2xl md:text-3xl text-gray-400 font-medium">%</span>
                                </span>
                                <span class="text-sm text-gray-500 font-medium mt-1">
                                    @{{ calorieBalance.netCalories }} / @{{ calorieBalance.targetCalories }} kcal
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-6">
                        <div class="text-center p-3 rounded-2xl bg-gray-50 group-hover:bg-primary-50 transition-colors duration-300">
                            <p class="text-xs text-gray-500 mb-1">섭취</p>
                            <p class="text-lg font-bold text-gray-900">@{{ calorieBalance.consumed }}</p>
                        </div>
                        <div class="text-center p-3 rounded-2xl bg-gray-50 group-hover:bg-accent-50 transition-colors duration-300">
                            <p class="text-xs text-gray-500 mb-1">소모</p>
                            <p class="text-lg font-bold text-gray-900">@{{ calorieBalance.burned }}</p>
                        </div>
                        <div class="text-center p-3 rounded-2xl bg-gray-50 group-hover:bg-gray-100 transition-colors duration-300">
                            <p class="text-xs text-gray-500 mb-1">잔여</p>
                            <p class="text-lg font-bold" :class="calorieBalance.remaining >= 0 ? 'text-green-600' : 'text-rose-500'">
                                @{{ calorieBalance.remaining }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Stats Grid (Span 6) -->
            <div class="md:col-span-4 lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Weight Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-lg">Recent</span>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">현재 체중</p>
                    <div class="flex items-end space-x-2 mt-1">
                        <h3 class="text-3xl font-bold text-gray-900">
                            @{{ todayData.weight?.current_weight || dashboard.current_weight || '-' }}
                        </h3>
                        <span class="text-gray-400 font-medium mb-1">kg</span>
                    </div>
                    <a href="{{ route('weight.index') }}" class="absolute bottom-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- Streak Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center text-orange-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                        </div>
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-lg">Hot</span>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">연속 기록</p>
                    <div class="flex items-end space-x-2 mt-1">
                        <h3 class="text-3xl font-bold text-gray-900">
                            @{{ dashboard.logging_streak_days || 0 }}
                        </h3>
                        <span class="text-gray-400 font-medium mb-1">일째</span>
                    </div>
                </div>

                <!-- Quick Actions (Span 2) -->
                <div class="col-span-1 sm:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 font-heading">빠른 기록</h3>
                    <div class="grid grid-cols-4 gap-4">
                        <a href="{{ route('meals.create') }}" class="flex flex-col items-center gap-2 group cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-100 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <span class="text-xs font-medium text-gray-600">식사</span>
                        </a>
                        <a href="{{ route('exercises.create') }}" class="flex flex-col items-center gap-2 group cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-100 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-gray-600">운동</span>
                        </a>
                        <a href="{{ route('weight.index') }}" class="flex flex-col items-center gap-2 group cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-100 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            </div>
                            <span class="text-xs font-medium text-gray-600">체중</span>
                        </a>
                        <a href="{{ route('diet-plan.index') }}" class="flex flex-col items-center gap-2 group cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center group-hover:bg-gray-100 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span class="text-xs font-medium text-gray-600">플랜</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Grid: Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Nutrition Breakdown -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 font-heading">영양 섭취</h3>
                    <a href="{{ route('meals.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">상세보기</a>
                </div>

                <div class="space-y-5">
                    <!-- Protein -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 font-medium">단백질</span>
                            <span class="text-gray-900 font-bold">@{{ todayData.nutrition.protein_g }}g</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-blue-400 h-2.5 rounded-full" style="width: 30%"></div>
                        </div>
                    </div>
                    <!-- Carbs -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 font-medium">탄수화물</span>
                            <span class="text-gray-900 font-bold">@{{ todayData.nutrition.carbs_g }}g</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-green-400 h-2.5 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>
                    <!-- Fat -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 font-medium">지방</span>
                            <span class="text-gray-900 font-bold">@{{ todayData.nutrition.fat_g }}g</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                    <span class="text-sm text-gray-500">오늘 식사 횟수</span>
                    <span class="text-lg font-bold text-gray-900">@{{ todayData.nutrition.meal_count }}회</span>
                </div>
            </div>

            <!-- Exercise Activity -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 font-heading">운동 활동</h3>
                    <a href="{{ route('exercises.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">상세보기</a>
                </div>

                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full border-4 border-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900">@{{ todayData.exercise.calories_burned }}</p>
                        <p class="text-sm text-gray-500">소모 칼로리 (kcal)</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-indigo-50 rounded-2xl">
                        <p class="text-xs text-indigo-600 mb-1 font-medium">운동 시간</p>
                        <p class="text-xl font-bold text-gray-900">@{{ todayData.exercise.duration_minutes }}<span class="text-sm font-normal text-gray-500 ml-1">분</span></p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-2xl">
                        <p class="text-xs text-blue-600 mb-1 font-medium">운동 횟수</p>
                        <p class="text-xl font-bold text-gray-900">@{{ todayData.exercise.exercise_count }}<span class="text-sm font-normal text-gray-500 ml-1">회</span></p>
                    </div>
                </div>
            </div>

             <!-- Weekly Trend (Placeholder or Total Entries) -->
             <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
                 <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">총 기록</h3>
                    <p class="text-gray-500 text-sm mb-6">나의 꾸준함이 만든 결과</p>
                    <p class="text-4xl font-bold text-gray-900 mb-2">@{{ totalEntries }}</p>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Total Entries</p>
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
            loading: true,
            dashboard: {},
            todayData: {
                nutrition: {
                    calories_consumed: 0,
                    protein_g: 0,
                    carbs_g: 0,
                    fat_g: 0,
                    meal_count: 0,
                    meals_by_type: { breakfast: 0, lunch: 0, dinner: 0, snack: 0 }
                },
                exercise: {
                    calories_burned: 0,
                    duration_minutes: 0,
                    exercise_count: 0,
                    exercises_by_intensity: {}
                },
                weight: null
            }
        }
    },
    computed: {
        totalEntries() {
            if (!this.dashboard.total_entries) return 0;
            return this.dashboard.total_entries.meals +
                   this.dashboard.total_entries.exercises +
                   this.dashboard.total_entries.weights;
        },
        circumference() {
            return 2 * Math.PI * 45;
        },
        calorieBalance() {
            const consumed = Math.round(this.todayData.nutrition?.calories_consumed || 0);
            const burned = Math.round(this.todayData.exercise?.calories_burned || 0);
            const targetCalories = this.dashboard.target_calories || 2000;
            const netCalories = consumed - burned;
            const percentage = Math.min(Math.round((consumed / targetCalories) * 100), 100);
            const remaining = targetCalories - consumed;

            return {
                consumed,
                burned,
                targetCalories,
                netCalories,
                percentage,
                remaining
            };
        }
    },
    async mounted() {
        await this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const statsResponse = await axios.get('/api/dashboard/quick-stats');
                this.dashboard = statsResponse.data.data;

                const todayResponse = await axios.get('/api/dashboard/today');
                if (todayResponse.data.data) {
                    this.todayData = todayResponse.data.data;
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                if (window.showToast) {
                    window.showToast('대시보드 로드 중 오류가 발생했습니다.', 'error');
                }
            } finally {
                this.loading = false;
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR');
        }
    }
}).mount('#dashboard-app');
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
</style>
@endpush
