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

    <!-- No Plan State -->
    <div v-else-if="!hasActivePlan" class="animate-fade-in-up">
        <!-- Header -->
        <header class="mb-8">
            <p class="text-gray-500 font-medium mb-1">Welcome back</p>
            <h1 class="text-4xl font-bold text-gray-900 font-heading tracking-tight">
                {{ auth()->user()->name }}님, <span class="text-primary-600">환영합니다!</span>
            </h1>
        </header>

        <!-- CTA Section -->
        <div class="bg-gradient-to-br from-primary-600 to-accent-600 rounded-3xl p-12 text-center text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 400 400" fill="currentColor">
                    <circle cx="200" cy="200" r="150" opacity="0.3"/>
                    <circle cx="50" cy="350" r="100" opacity="0.2"/>
                    <circle cx="350" cy="50" r="80" opacity="0.2"/>
                </svg>
            </div>

            <div class="relative z-10">
                <div class="w-24 h-24 mx-auto mb-6 bg-white/20 backdrop-blur rounded-3xl flex items-center justify-center">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>

                <h2 class="text-3xl font-bold mb-4 font-heading">맞춤 플랜을 시작해보세요</h2>
                <p class="text-primary-100 mb-8 max-w-md mx-auto text-lg">
                    간단한 설문으로 AI가 당신만의 식단과 운동 계획을 만들어 드립니다.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a v-if="!surveyStatus.is_completed" :href="'/survey/' + surveyStatus.survey_id" class="px-8 py-4 bg-white text-primary-600 font-bold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                        설문 시작하기
                    </a>
                    <a v-else href="{{ route('diet-plan.index') }}" class="px-8 py-4 bg-white text-primary-600 font-bold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                        플랜 생성하기
                    </a>
                    <a href="{{ route('diet-plan.index') }}" class="px-8 py-4 bg-white/20 backdrop-blur text-white font-bold rounded-2xl border border-white/30 hover:bg-white/30 transition-all">
                        플랜 목록 보기
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('survey.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-primary-200 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-bold text-gray-900">설문하기</h3>
                <p class="text-sm text-gray-500 mt-1">맞춤 플랜을 위한 건강 설문</p>
            </a>
            <a href="{{ route('diet-plan.index') }}" class="bg-gradient-to-br from-primary-50 to-accent-50 rounded-2xl p-6 shadow-sm border-2 border-primary-200 hover:shadow-lg hover:border-primary-300 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 text-white flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-bold text-gray-900">플랜 생성하기</h3>
                <p class="text-sm text-gray-500 mt-1">AI 맞춤 식단 & 운동 플랜</p>
            </a>
        </div>
    </div>

    <!-- Has Plan State -->
    <div v-else class="space-y-8 animate-fade-in-up">
        <!-- Header -->
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

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Goal Card -->
            <div class="lg:col-span-2 bg-gradient-to-br from-primary-600 to-accent-600 rounded-3xl p-8 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                    <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-bold">
                            @{{ activePlan.today ? activePlan.today.day_number + '일차' : '오늘' }}
                        </span>
                        <span v-if="activePlan.today && isAllCompletedToday" class="px-3 py-1 bg-green-400/30 backdrop-blur rounded-full text-xs font-bold text-green-100">
                            완료!
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold mb-2 font-heading">오늘의 목표</h2>

                    <div v-if="activePlan.today" class="grid grid-cols-2 gap-4 mt-6">
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-primary-100 font-medium">식단</span>
                                <button
                                    @click="toggleCompletion('meal')"
                                    :class="['w-8 h-8 rounded-full flex items-center justify-center transition-all cursor-pointer', activePlan.today.meal_completed ? 'bg-green-400 text-white' : 'bg-white/20 text-white hover:bg-white/30']"
                                    :disabled="completing"
                                >
                                    <svg v-if="activePlan.today.meal_completed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </button>
                            </div>
                            <p class="text-3xl font-bold">@{{ activePlan.today.total_calories }}<span class="text-lg font-normal text-primary-200 ml-1">kcal</span></p>
                        </div>

                        <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/10">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-primary-100 font-medium">운동</span>
                                <button
                                    v-if="activePlan.today.has_exercise"
                                    @click="toggleCompletion('exercise')"
                                    :class="['w-8 h-8 rounded-full flex items-center justify-center transition-all cursor-pointer', activePlan.today.exercise_completed ? 'bg-green-400 text-white' : 'bg-white/20 text-white hover:bg-white/30']"
                                    :disabled="completing"
                                >
                                    <svg v-if="activePlan.today.exercise_completed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </button>
                                <span v-else class="text-xs text-primary-200">휴식일</span>
                            </div>
                            <p class="text-xl font-bold">@{{ activePlan.today.exercise_name || '휴식' }}</p>
                        </div>
                    </div>

                    <div v-else class="mt-6 p-6 bg-white/10 backdrop-blur rounded-2xl border border-white/10 text-center">
                        <p class="text-primary-100">오늘은 플랜 기간이 아닙니다</p>
                    </div>

                    <a :href="'/diet-plan/' + activePlan.id" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-white hover:text-primary-100 transition-colors">
                        상세 플랜 보기
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Stats Column -->
            <div class="space-y-6">
                <!-- Progress Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">플랜 진행률</h3>
                            <p class="text-sm text-gray-500">@{{ activePlan.progress.completed_days }}/@{{ activePlan.progress.total_days }}일 완료</p>
                        </div>
                        <span class="text-2xl font-bold text-primary-600">@{{ activePlan.progress.completion_rate }}%</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary-500 to-accent-500 rounded-full transition-all duration-500" :style="{ width: activePlan.progress.completion_rate + '%' }"></div>
                    </div>
                </div>

                <!-- Streak Card -->
                <div class="bg-gradient-to-br from-orange-500 to-amber-500 rounded-3xl p-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                            @{{ activePlan.progress.current_streak > 0 ? '🔥' : '💪' }}
                        </div>
                        <div>
                            <p class="text-orange-100 text-sm font-medium">연속 달성</p>
                            <p class="text-3xl font-bold">@{{ activePlan.progress.current_streak }}일</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/20 flex justify-between text-sm">
                        <span class="text-orange-100">최장 기록</span>
                        <span class="font-bold">@{{ activePlan.progress.longest_streak }}일</span>
                    </div>
                </div>

                <!-- Weight Loss Card -->
                <div v-if="activePlan.progress.estimated_weight_loss_kg !== null" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center', activePlan.progress.estimated_weight_loss_kg > 0 ? 'bg-green-50 text-green-600' : 'bg-orange-50 text-orange-600']">
                            <svg v-if="activePlan.progress.estimated_weight_loss_kg > 0" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">예상 체중 변화</p>
                            <p class="text-xl font-bold" :class="activePlan.progress.estimated_weight_loss_kg > 0 ? 'text-green-600' : 'text-orange-600'">
                                @{{ activePlan.progress.estimated_weight_loss_kg > 0 ? '-' : '+' }}@{{ Math.abs(activePlan.progress.estimated_weight_loss_kg) }} kg
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 font-heading">플랜 캘린더</h3>
                <div class="flex items-center gap-2">
                    <button @click="changeMonth(-1)" class="p-2 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-bold text-gray-900 min-w-[120px] text-center">@{{ currentMonthLabel }}</span>
                    <button @click="changeMonth(1)" class="p-2 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-1">
                <!-- Day Headers -->
                <div v-for="day in ['일', '월', '화', '수', '목', '금', '토']" :key="day" class="text-center text-xs font-bold text-gray-400 py-2">
                    @{{ day }}
                </div>

                <!-- Calendar Days -->
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    @click="day.date && openDayModal(day)"
                    :class="[
                        'relative min-h-[80px] p-2 rounded-xl transition-all',
                        day.date ? 'cursor-pointer hover:bg-gray-50' : '',
                        day.isToday ? 'ring-2 ring-primary-500 ring-offset-2' : '',
                        day.isInPlan ? 'bg-primary-50/50' : '',
                        day.isPast && day.isInPlan && !day.isCompleted ? 'bg-red-50/50' : ''
                    ]"
                >
                    <template v-if="day.date">
                        <div class="text-right">
                            <span :class="['inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-bold', day.isToday ? 'bg-primary-500 text-white' : 'text-gray-700']">
                                @{{ day.dayOfMonth }}
                            </span>
                        </div>

                        <div v-if="day.isInPlan && day.planData" class="mt-1 space-y-1">
                            <div class="text-xs text-gray-500 font-medium truncate">
                                @{{ day.planData.total_calories }} kcal
                            </div>
                            <div class="flex gap-1">
                                <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-xs', day.planData.meal_completed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400']">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span v-if="day.planData.has_exercise" :class="['w-5 h-5 rounded-full flex items-center justify-center text-xs', day.planData.exercise_completed ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400']">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 flex flex-wrap gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-primary-50 border border-primary-200"></div>
                    <span>플랜 기간</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                    <span>완료</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-gray-200"></div>
                    <span>미완료</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded ring-2 ring-primary-500"></div>
                    <span>오늘</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-900 mb-4 font-heading">빠른 메뉴</h3>
            <div class="grid grid-cols-2 gap-4">
                <a :href="'/diet-plan/' + activePlan.id" class="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-br from-primary-50 to-accent-50 border-2 border-primary-200 hover:shadow-md hover:border-primary-300 transition-all group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 text-white flex items-center justify-center group-hover:scale-110 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <span class="font-bold text-gray-900">상세 플랜</span>
                        <p class="text-xs text-gray-500">식단 & 운동 상세 보기</p>
                    </div>
                </a>
                <a href="{{ route('diet-plan.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-200 hover:shadow-md hover:border-gray-300 transition-all group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-600 flex items-center justify-center group-hover:bg-gray-200 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <span class="font-bold text-gray-900">새 플랜</span>
                        <p class="text-xs text-gray-500">새로운 플랜 생성하기</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Day Detail Modal -->
    <div v-if="showDayModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDayModal = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary-600 to-accent-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-primary-100 text-sm font-medium">@{{ selectedDay?.planData?.day_number }}일차</p>
                        <h3 class="text-xl font-bold">@{{ formatModalDate(selectedDay?.date) }}</h3>
                    </div>
                    <button @click="showDayModal = false" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div v-if="modalLoading" class="flex justify-center py-8">
                    <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div v-else-if="dayDetailData">
                    <!-- Meals Section -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-gray-900">식단</h4>
                            <button
                                @click="toggleModalCompletion('meal')"
                                :class="['px-4 py-2 rounded-xl text-sm font-bold transition-all cursor-pointer', dayDetailData.meal_completed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                                :disabled="modalCompleting"
                            >
                                @{{ dayDetailData.meal_completed ? '완료됨' : '완료하기' }}
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(meals, type) in groupedMeals" :key="type" class="bg-gray-50 rounded-xl p-4">
                                <h5 class="text-xs font-bold text-gray-500 uppercase mb-2">@{{ mealTypeLabel(type) }}</h5>
                                <div class="space-y-2">
                                    <div v-for="meal in meals" :key="meal.id" class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700">@{{ meal.food_name }}</span>
                                        <span class="text-xs text-gray-500">@{{ Math.round(meal.calories) }} kcal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-primary-50 rounded-xl">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">총 칼로리</span>
                                <span class="font-bold text-primary-700">@{{ dayDetailData.total_calories }} kcal</span>
                            </div>
                        </div>
                    </div>

                    <!-- Exercise Section -->
                    <div v-if="dayDetailData.exercises && dayDetailData.exercises.length > 0">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-gray-900">운동</h4>
                            <button
                                @click="toggleModalCompletion('exercise')"
                                :class="['px-4 py-2 rounded-xl text-sm font-bold transition-all cursor-pointer', dayDetailData.exercise_completed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                                :disabled="modalCompleting"
                            >
                                @{{ dayDetailData.exercise_completed ? '완료됨' : '완료하기' }}
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div v-for="exercise in dayDetailData.exercises" :key="exercise.id" class="bg-gray-50 rounded-xl p-4">
                                <h5 class="font-bold text-gray-900 mb-2">@{{ exercise.exercise_name }}</h5>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg">@{{ exercise.duration_minutes }}분</span>
                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-lg">@{{ Math.round(exercise.estimated_calories_burned) }} kcal</span>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg">@{{ exercise.intensity }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <p>오늘은 휴식일입니다</p>
                    </div>

                    <!-- Tips -->
                    <div v-if="dayDetailData.tips" class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">💡</span>
                            <div>
                                <h5 class="text-sm font-bold text-amber-800 mb-1">오늘의 팁</h5>
                                <p class="text-sm text-amber-700">@{{ dayDetailData.tips }}</p>
                            </div>
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
Vue.createApp({
    data() {
        return {
            loading: true,
            hasActivePlan: false,
            activePlan: null,
            surveyStatus: { has_survey: false, is_completed: false, survey_id: null },
            calendarData: null,
            currentMonth: new Date(),
            showDayModal: false,
            selectedDay: null,
            dayDetailData: null,
            modalLoading: false,
            modalCompleting: false,
            completing: false
        }
    },
    computed: {
        currentMonthLabel() {
            return `${this.currentMonth.getFullYear()}년 ${this.currentMonth.getMonth() + 1}월`;
        },
        calendarDays() {
            const year = this.currentMonth.getFullYear();
            const month = this.currentMonth.getMonth();

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startPadding = firstDay.getDay();
            const totalDays = lastDay.getDate();

            const days = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Padding for days before the first of the month
            for (let i = 0; i < startPadding; i++) {
                days.push({ date: null });
            }

            // Actual days
            for (let d = 1; d <= totalDays; d++) {
                const date = new Date(year, month, d);
                const dateStr = date.toISOString().split('T')[0];

                let planData = null;
                let isInPlan = false;
                let isCompleted = false;

                if (this.calendarData && this.calendarData.days) {
                    planData = this.calendarData.days.find(day => day.date === dateStr);
                    if (planData) {
                        isInPlan = true;
                        isCompleted = planData.meal_completed && (!planData.has_exercise || planData.exercise_completed);
                    }
                }

                days.push({
                    date: date,
                    dayOfMonth: d,
                    isToday: date.getTime() === today.getTime(),
                    isPast: date < today,
                    isInPlan,
                    isCompleted,
                    planData
                });
            }

            return days;
        },
        isAllCompletedToday() {
            if (!this.activePlan?.today) return false;
            const t = this.activePlan.today;
            return t.meal_completed && (!t.has_exercise || t.exercise_completed);
        },
        groupedMeals() {
            if (!this.dayDetailData?.meals) return {};
            return this.dayDetailData.meals;
        }
    },
    async mounted() {
        await this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const response = await axios.get('/api/dashboard/plan-overview');
                const data = response.data.data;

                this.hasActivePlan = data.has_active_plan;
                this.activePlan = data.active_plan;
                this.surveyStatus = data.survey_status;

                if (this.hasActivePlan && this.activePlan) {
                    await this.loadCalendarData();
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
        async loadCalendarData() {
            try {
                const response = await axios.get(`/api/diet-plans/${this.activePlan.id}/calendar`);
                this.calendarData = response.data.data;
            } catch (error) {
                console.error('Error loading calendar:', error);
            }
        },
        changeMonth(delta) {
            const newMonth = new Date(this.currentMonth);
            newMonth.setMonth(newMonth.getMonth() + delta);
            this.currentMonth = newMonth;
        },
        async openDayModal(day) {
            if (!day.isInPlan || !day.planData) return;

            this.selectedDay = day;
            this.showDayModal = true;
            this.modalLoading = true;

            try {
                const response = await axios.get(`/api/diet-plans/${this.activePlan.id}/day/${day.planData.day_number}`);
                this.dayDetailData = {
                    ...response.data.data,
                    meal_completed: day.planData.meal_completed,
                    exercise_completed: day.planData.exercise_completed
                };
            } catch (error) {
                console.error('Error loading day detail:', error);
                if (window.showToast) {
                    window.showToast('상세 정보를 불러올 수 없습니다.', 'error');
                }
            } finally {
                this.modalLoading = false;
            }
        },
        async toggleCompletion(type) {
            if (!this.activePlan?.today) return;

            this.completing = true;
            const currentStatus = type === 'meal' ? this.activePlan.today.meal_completed : this.activePlan.today.exercise_completed;

            try {
                const response = await axios.post(`/api/diet-plans/${this.activePlan.id}/days/${this.activePlan.today.day_number}/complete`, {
                    type,
                    completed: !currentStatus
                });

                // Update local state
                if (type === 'meal') {
                    this.activePlan.today.meal_completed = response.data.data.meal_completed;
                } else {
                    this.activePlan.today.exercise_completed = response.data.data.exercise_completed;
                }

                // Reload calendar and progress
                await this.loadCalendarData();
                await this.loadDashboard();

                if (window.showToast) {
                    window.showToast(response.data.message, 'success');
                }
            } catch (error) {
                console.error('Error toggling completion:', error);
                if (window.showToast) {
                    window.showToast('오류가 발생했습니다.', 'error');
                }
            } finally {
                this.completing = false;
            }
        },
        async toggleModalCompletion(type) {
            if (!this.dayDetailData || !this.selectedDay) return;

            this.modalCompleting = true;
            const currentStatus = type === 'meal' ? this.dayDetailData.meal_completed : this.dayDetailData.exercise_completed;

            try {
                const response = await axios.post(`/api/diet-plans/${this.activePlan.id}/days/${this.selectedDay.planData.day_number}/complete`, {
                    type,
                    completed: !currentStatus
                });

                // Update modal state
                this.dayDetailData.meal_completed = response.data.data.meal_completed;
                this.dayDetailData.exercise_completed = response.data.data.exercise_completed;

                // Update calendar state
                this.selectedDay.planData.meal_completed = response.data.data.meal_completed;
                this.selectedDay.planData.exercise_completed = response.data.data.exercise_completed;

                // Reload dashboard
                await this.loadDashboard();

                if (window.showToast) {
                    window.showToast(response.data.message, 'success');
                }
            } catch (error) {
                console.error('Error toggling completion:', error);
                if (window.showToast) {
                    window.showToast('오류가 발생했습니다.', 'error');
                }
            } finally {
                this.modalCompleting = false;
            }
        },
        formatModalDate(date) {
            if (!date) return '';
            return date.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' });
        },
        mealTypeLabel(type) {
            const labels = { breakfast: '아침', lunch: '점심', dinner: '저녁', snack: '간식' };
            return labels[type] || type;
        }
    }
}).mount('#dashboard-app');
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
