@extends('layouts.app')

@section('title', '주간 요약 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <div id="weekly-summary-app">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>주간 요약
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">지난 주의 활동을 분석합니다</p>
                </div>

                <!-- Week Navigator -->
                <div class="flex items-center gap-3">
                    <button @click="changeWeek(-1)" class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="text-center">
                        <p class="font-semibold text-gray-900">@{{ weekLabel }}</p>
                        <p class="text-xs text-gray-500">@{{ formatDate(weekStart) }} - @{{ formatDate(weekEnd) }}</p>
                    </div>
                    <button @click="changeWeek(1)" :disabled="isCurrentWeek" class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-30">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button @click="goToCurrentWeek" class="ml-2 px-4 py-2 text-sm font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                        이번 주
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
            <p class="mt-4 text-gray-600">데이터를 불러오는 중...</p>
        </div>

        <!-- Summary Content -->
        <div v-else>
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-600">평균 칼로리</p>
                        <i class="fas fa-fire text-primary text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">@{{ summary.avg_calories || 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">kcal/일</p>
                    <div v-if="summary.calorie_trend" :class="['text-sm font-medium mt-2', summary.calorie_trend > 0 ? 'text-red-600' : 'text-green-600']">
                        <i :class="['fas', summary.calorie_trend > 0 ? 'fa-arrow-up' : 'fa-arrow-down', 'mr-1']"></i>
                        @{{ Math.abs(summary.calorie_trend) }}% vs 지난주
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-600">총 운동시간</p>
                        <i class="fas fa-running text-secondary text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">@{{ summary.total_exercise_minutes || 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">분 (@{{ summary.exercise_days || 0 }}일)</p>
                    <div v-if="summary.exercise_trend" :class="['text-sm font-medium mt-2', summary.exercise_trend > 0 ? 'text-green-600' : 'text-red-600']">
                        <i :class="['fas', summary.exercise_trend > 0 ? 'fa-arrow-up' : 'fa-arrow-down', 'mr-1']"></i>
                        @{{ Math.abs(summary.exercise_trend) }}% vs 지난주
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-600">체중 변화</p>
                        <i class="fas fa-weight text-accent text-xl"></i>
                    </div>
                    <p :class="['text-3xl font-bold', getWeightChangeClass(summary.weight_change)]">
                        @{{ formatWeightChange(summary.weight_change) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">이번 주</p>
                    <p v-if="summary.current_weight" class="text-sm text-gray-600 mt-2">
                        현재: @{{ summary.current_weight }}kg
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-600">기록 일수</p>
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">@{{ summary.logging_days || 0 }}<span class="text-lg font-normal text-gray-600">/7</span></p>
                    <p class="text-xs text-gray-500 mt-1">일</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div :style="`width: ${(summary.logging_days || 0) / 7 * 100}%`" class="bg-blue-600 h-2 rounded-full transition-all"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Daily Calories Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-line text-primary mr-2"></i>일일 칼로리
                    </h2>
                    <div class="relative" style="height: 300px;">
                        <canvas ref="caloriesChart"></canvas>
                    </div>
                </div>

                <!-- Macros Distribution Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>영양소 비율
                    </h2>
                    <div class="relative" style="height: 300px;">
                        <canvas ref="macrosChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Exercise Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-dumbbell text-primary mr-2"></i>일일 운동
                </h2>
                <div class="relative" style="height: 300px;">
                    <canvas ref="exerciseChart"></canvas>
                </div>
            </div>

            <!-- Daily Breakdown -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-list text-primary mr-2"></i>일별 상세
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">날짜</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">칼로리</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">운동</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">체중</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">기록</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="day in dailyBreakdown" :key="day.date" :class="[isToday(day.date) ? 'bg-primary/5' : '']">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">@{{ formatDayLabel(day.date) }}</div>
                                    <div class="text-xs text-gray-500">@{{ formatDate(day.date) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">@{{ day.total_calories || '-' }}</div>
                                    <div v-if="day.total_calories" class="text-xs text-gray-500">
                                        P: @{{ day.total_protein }}g | C: @{{ day.total_carbs }}g | F: @{{ day.total_fat }}g
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="day.exercise_minutes" class="text-sm font-semibold text-gray-900">@{{ day.exercise_minutes }}분</div>
                                    <div v-else class="text-sm text-gray-400">-</div>
                                    <div v-if="day.calories_burned" class="text-xs text-gray-500">@{{ day.calories_burned }}kcal 소모</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="day.weight" class="text-sm font-semibold text-gray-900">@{{ day.weight }}kg</div>
                                    <div v-else class="text-sm text-gray-400">-</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-1">
                                        <span v-if="day.has_meals" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-utensils mr-1"></i>식사
                                        </span>
                                        <span v-if="day.has_exercises" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-running mr-1"></i>운동
                                        </span>
                                        <span v-if="day.has_weight" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-weight mr-1"></i>체중
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Insights -->
            <div v-if="insights && insights.length > 0" class="bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>인사이트
                </h2>
                <div class="space-y-3">
                    <div v-for="(insight, index) in insights" :key="index" class="bg-white rounded-lg p-4 flex items-start gap-3">
                        <i :class="['fas', insight.icon, insight.color, 'text-xl mt-1']"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">@{{ insight.title }}</h3>
                            <p class="text-sm text-gray-600">@{{ insight.message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            weekOffset: 0,
            weekStart: null,
            weekEnd: null,
            weekLabel: '',
            summary: {},
            dailyBreakdown: [],
            insights: [],
            loading: false,
            caloriesChart: null,
            macrosChart: null,
            exerciseChart: null
        }
    },
    computed: {
        isCurrentWeek() {
            return this.weekOffset === 0;
        }
    },
    mounted() {
        this.initializeWeek();
        this.loadWeeklySummary();
    },
    methods: {
        initializeWeek() {
            const today = new Date();
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Monday as start of week

            const start = new Date(today);
            start.setDate(today.getDate() + diff + (this.weekOffset * 7));
            start.setHours(0, 0, 0, 0);

            const end = new Date(start);
            end.setDate(start.getDate() + 6);
            end.setHours(23, 59, 59, 999);

            this.weekStart = start;
            this.weekEnd = end;

            if (this.weekOffset === 0) {
                this.weekLabel = '이번 주';
            } else if (this.weekOffset === -1) {
                this.weekLabel = '지난 주';
            } else {
                this.weekLabel = `${Math.abs(this.weekOffset)}주 ${this.weekOffset < 0 ? '전' : '후'}`;
            }
        },
        changeWeek(offset) {
            this.weekOffset += offset;
            this.initializeWeek();
            this.loadWeeklySummary();
        },
        goToCurrentWeek() {
            this.weekOffset = 0;
            this.initializeWeek();
            this.loadWeeklySummary();
        },
        async loadWeeklySummary() {
            this.loading = true;
            try {
                const startDate = this.weekStart.toISOString().split('T')[0];
                const endDate = this.weekEnd.toISOString().split('T')[0];

                const response = await axios.get(`/summary/weekly?start_date=${startDate}&end_date=${endDate}`);
                const data = response.data.data;

                this.summary = data.summary || {};
                this.dailyBreakdown = data.daily_breakdown || [];
                this.insights = data.insights || [];

                this.renderCharts();
            } catch (error) {
                console.error('Failed to load weekly summary:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '주간 요약을 불러오는데 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.loading = false;
            }
        },
        renderCharts() {
            this.renderCaloriesChart();
            this.renderMacrosChart();
            this.renderExerciseChart();
        },
        renderCaloriesChart() {
            if (this.caloriesChart) {
                this.caloriesChart.destroy();
            }

            const ctx = this.$refs.caloriesChart.getContext('2d');
            const labels = this.dailyBreakdown.map(d => this.formatDayLabel(d.date, true));
            const calories = this.dailyBreakdown.map(d => d.total_calories || 0);
            const target = this.summary.target_calories;

            this.caloriesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '섭취 칼로리',
                            data: calories,
                            backgroundColor: 'rgba(99, 102, 241, 0.5)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 2
                        },
                        ...(target ? [{
                            label: '목표',
                            data: new Array(calories.length).fill(target),
                            type: 'line',
                            borderColor: 'rgb(239, 68, 68)',
                            borderDash: [5, 5],
                            fill: false,
                            pointRadius: 0
                        }] : [])
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'kcal';
                                }
                            }
                        }
                    }
                }
            });
        },
        renderMacrosChart() {
            if (this.macrosChart) {
                this.macrosChart.destroy();
            }

            const ctx = this.$refs.macrosChart.getContext('2d');
            const totalProtein = this.summary.total_protein || 0;
            const totalCarbs = this.summary.total_carbs || 0;
            const totalFat = this.summary.total_fat || 0;

            this.macrosChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['단백질', '탄수화물', '지방'],
                    datasets: [{
                        data: [totalProtein * 4, totalCarbs * 4, totalFat * 9], // Convert to calories
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(234, 179, 8, 0.7)',
                            'rgba(249, 115, 22, 0.7)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(234, 179, 8)',
                            'rgb(249, 115, 22)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = Math.round(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value}kcal (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },
        renderExerciseChart() {
            if (this.exerciseChart) {
                this.exerciseChart.destroy();
            }

            const ctx = this.$refs.exerciseChart.getContext('2d');
            const labels = this.dailyBreakdown.map(d => this.formatDayLabel(d.date, true));
            const minutes = this.dailyBreakdown.map(d => d.exercise_minutes || 0);
            const caloriesBurned = this.dailyBreakdown.map(d => d.calories_burned || 0);

            this.exerciseChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '운동 시간 (분)',
                            data: minutes,
                            backgroundColor: 'rgba(16, 185, 129, 0.5)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: '소모 칼로리 (kcal)',
                            data: caloriesBurned,
                            type: 'line',
                            borderColor: 'rgb(249, 115, 22)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '시간 (분)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: '칼로리 (kcal)'
                            }
                        }
                    }
                }
            });
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        },
        formatDayLabel(date, short = false) {
            const days = ['일', '월', '화', '수', '목', '금', '토'];
            const d = new Date(date);
            const dayName = days[d.getDay()];
            return short ? dayName : `${dayName}요일`;
        },
        formatWeightChange(change) {
            if (!change) return '0kg';
            const prefix = change > 0 ? '+' : '';
            return `${prefix}${change.toFixed(1)}kg`;
        },
        getWeightChangeClass(change) {
            if (!change) return 'text-gray-600';
            return change > 0 ? 'text-red-600' : 'text-green-600';
        },
        isToday(date) {
            const today = new Date().toISOString().split('T')[0];
            return date === today;
        }
    }
}).mount('#weekly-summary-app');
</script>
@endpush
