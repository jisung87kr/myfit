@extends('layouts.app')

@section('title', '대시보드 - MyFit')

@section('content')
<div id="dashboard-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">데이터를 불러오는 중...</p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                안녕하세요, {{ auth()->user()->name }}님! 👋
            </h1>
            <p class="mt-2 text-gray-600">오늘의 활동을 확인하고 목표를 달성하세요.</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Today's Net Calories -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-primary/10 p-3">
                                <i class="fas fa-fire text-primary text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    오늘의 순 칼로리
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    @{{ dashboard.today ? dashboard.today.net_calories.toFixed(0) : 0 }} kcal
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Weight -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-secondary/10 p-3">
                                <i class="fas fa-weight text-secondary text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    현재 체중
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    @{{ dashboard.current_weight || '-' }} kg
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logging Streak -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-yellow-100 p-3">
                                <i class="fas fa-calendar-check text-yellow-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    연속 기록
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    @{{ dashboard.logging_streak_days || 0 }}일 🔥
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Entries -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-purple-100 p-3">
                                <i class="fas fa-database text-purple-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    총 기록 수
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    @{{ totalEntries }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calorie Balance Progress -->
        <div v-if="todayData.calorie_balance" class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-primary"></i>
                칼로리 밸런스
            </h2>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        @{{ todayData.calorie_balance.net_calories.toFixed(0) }} / @{{ todayData.calorie_balance.target_calories }} kcal
                    </span>
                    <span class="text-sm font-medium" :class="todayData.calorie_balance.percentage_of_target <= 100 ? 'text-primary' : 'text-red-600'">
                        @{{ todayData.calorie_balance.percentage_of_target.toFixed(1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div
                        class="h-4 rounded-full transition-all duration-500"
                        :class="todayData.calorie_balance.percentage_of_target <= 100 ? 'bg-primary' : 'bg-red-500'"
                        :style="{ width: Math.min(todayData.calorie_balance.percentage_of_target, 100) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600">섭취</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @{{ todayData.calorie_balance.calories_consumed.toFixed(0) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">소모</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @{{ todayData.calorie_balance.calories_burned.toFixed(0) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">남은 칼로리</p>
                    <p class="text-lg font-semibold" :class="todayData.calorie_balance.remaining_calories >= 0 ? 'text-primary' : 'text-red-600'">
                        @{{ todayData.calorie_balance.remaining_calories.toFixed(0) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Today's Summary Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Nutrition Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-utensils mr-2 text-primary"></i>
                        식사
                    </h3>
                    <a href="{{ route('meals.index') }}" class="text-sm text-primary hover:text-primary/80">
                        상세보기 →
                    </a>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">총 칼로리</span>
                        <span class="text-sm font-semibold">@{{ todayData.nutrition.calories_consumed }} kcal</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">단백질</span>
                        <span class="text-sm font-semibold">@{{ todayData.nutrition.protein_g }} g</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">탄수화물</span>
                        <span class="text-sm font-semibold">@{{ todayData.nutrition.carbs_g }} g</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">지방</span>
                        <span class="text-sm font-semibold">@{{ todayData.nutrition.fat_g }} g</span>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">식사 횟수</span>
                            <span class="font-semibold">@{{ todayData.nutrition.meal_count }}회</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            아침: @{{ todayData.nutrition.meals_by_type.breakfast }}
                            점심: @{{ todayData.nutrition.meals_by_type.lunch }}
                            저녁: @{{ todayData.nutrition.meals_by_type.dinner }}
                            간식: @{{ todayData.nutrition.meals_by_type.snack }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exercise Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-running mr-2 text-primary"></i>
                        운동
                    </h3>
                    <a href="{{ route('exercises.index') }}" class="text-sm text-primary hover:text-primary/80">
                        상세보기 →
                    </a>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">소모 칼로리</span>
                        <span class="text-sm font-semibold">@{{ todayData.exercise.calories_burned }} kcal</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">운동 시간</span>
                        <span class="text-sm font-semibold">@{{ todayData.exercise.duration_minutes }}분</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">운동 횟수</span>
                        <span class="text-sm font-semibold">@{{ todayData.exercise.exercise_count }}회</span>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <div class="text-sm text-gray-600 mb-2">강도별 운동</div>
                        <div class="space-y-1 text-xs text-gray-500">
                            <div class="flex justify-between">
                                <span>낮음</span>
                                <span>@{{ todayData.exercise.exercises_by_intensity['낮음'] }}회</span>
                            </div>
                            <div class="flex justify-between">
                                <span>보통</span>
                                <span>@{{ todayData.exercise.exercises_by_intensity['보통'] }}회</span>
                            </div>
                            <div class="flex justify-between">
                                <span>높음</span>
                                <span>@{{ todayData.exercise.exercises_by_intensity['높음'] }}회</span>
                            </div>
                            <div class="flex justify-between">
                                <span>매우 높음</span>
                                <span>@{{ todayData.exercise.exercises_by_intensity['매우 높음'] }}회</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-weight mr-2 text-primary"></i>
                        체중
                    </h3>
                    <a href="{{ route('weight.index') }}" class="text-sm text-primary hover:text-primary/80">
                        상세보기 →
                    </a>
                </div>

                <div v-if="todayData.weight" class="space-y-3">
                    <div class="text-center py-4">
                        <div class="text-4xl font-bold text-primary">
                            @{{ todayData.weight.current_weight }} kg
                        </div>
                        <div class="text-sm text-gray-500 mt-2">
                            @{{ todayData.weight.last_updated ? formatDate(todayData.weight.last_updated) : '오늘' }} 측정
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-400">
                    <i class="fas fa-weight text-4xl mb-3"></i>
                    <p class="text-sm">아직 체중 기록이 없습니다</p>
                    <a href="{{ route('weight.index') }}" class="mt-4 inline-block text-sm text-primary hover:text-primary/80">
                        체중 기록하기 →
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                빠른 기록
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('meals.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors">
                    <i class="fas fa-plus-circle text-3xl text-primary mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">식사 추가</span>
                </a>
                <a href="{{ route('exercises.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors">
                    <i class="fas fa-plus-circle text-3xl text-primary mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">운동 추가</span>
                </a>
                <a href="{{ route('weight.index') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors">
                    <i class="fas fa-plus-circle text-3xl text-primary mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">체중 기록</span>
                </a>
                <a href="{{ route('diet-plan.index') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors">
                    <i class="fas fa-calendar-alt text-3xl text-primary mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">플랜 보기</span>
                </a>
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
                    meals_by_type: {
                        breakfast: 0,
                        lunch: 0,
                        dinner: 0,
                        snack: 0
                    }
                },
                exercise: {
                    calories_burned: 0,
                    duration_minutes: 0,
                    exercise_count: 0,
                    exercises_by_intensity: {
                        '낮음': 0,
                        '보통': 0,
                        '높음': 0,
                        '매우 높음': 0
                    }
                },
                weight: null,
                calorie_balance: null
            }
        }
    },
    computed: {
        totalEntries() {
            if (!this.dashboard.total_entries) return 0;
            return this.dashboard.total_entries.meals +
                   this.dashboard.total_entries.exercises +
                   this.dashboard.total_entries.weights;
        }
    },
    async mounted() {
        await this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                // Load quick stats
                const statsResponse = await axios.get('/dashboard/quick-stats');
                this.dashboard = statsResponse.data.data;

                // Load today's dashboard
                const todayResponse = await axios.get('/dashboard/today');
                this.todayData = todayResponse.data.data;

            } catch (error) {
                console.error('Error loading dashboard:', error);
                this.showToast('대시보드 로드 중 오류가 발생했습니다.', 'error');
            } finally {
                this.loading = false;
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR');
        },
        showToast(message, type = 'success') {
            // Will use parent component's showToast method
            if (window.app && window.app.showToast) {
                window.app.showToast(message, type);
            }
        }
    }
}).mount('#dashboard-app');
</script>
@endpush
