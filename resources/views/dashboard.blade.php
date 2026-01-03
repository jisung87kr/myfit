@extends('layouts.app')

@section('title', '대시보드 - MyFit')

@section('content')
<div id="dashboard-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 relative">
                <div class="absolute inset-0 rounded-full border-4 border-primary-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-primary-500 border-t-transparent animate-spin"></div>
            </div>
            <p class="text-gray-500 font-medium">데이터를 불러오는 중...</p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-600 mb-1">Welcome back</p>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 font-heading">
                        {{ auth()->user()->name }}님, 안녕하세요
                    </h1>
                    <p class="mt-2 text-gray-500">오늘의 활동을 확인하고 건강한 하루를 보내세요.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <span class="inline-flex items-center px-4 py-2 bg-white rounded-xl shadow-sm text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ now()->format('Y년 m월 d일') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <!-- Today's Net Calories -->
            <div class="group bg-white rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-rose-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-1">오늘의 순 칼로리</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">
                    @{{ dashboard.today ? dashboard.today.net_calories.toFixed(0) : 0 }}
                    <span class="text-sm font-normal text-gray-400">kcal</span>
                </p>
            </div>

            <!-- Current Weight -->
            <div class="group bg-white rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-1">현재 체중</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">
                    @{{ dashboard.current_weight || '-' }}
                    <span class="text-sm font-normal text-gray-400">kg</span>
                </p>
            </div>

            <!-- Logging Streak -->
            <div class="group bg-white rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-1">연속 기록</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">
                    @{{ dashboard.logging_streak_days || 0 }}
                    <span class="text-sm font-normal text-gray-400">일</span>
                </p>
            </div>

            <!-- Total Entries -->
            <div class="group bg-white rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-1">총 기록 수</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">
                    @{{ totalEntries }}
                    <span class="text-sm font-normal text-gray-400">개</span>
                </p>
            </div>
        </div>

        <!-- Calorie Balance Progress -->
        <div v-if="todayData.calorie_balance" class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 font-heading">칼로리 밸런스</h2>
                    <p class="text-sm text-gray-500 mt-1">오늘의 칼로리 섭취 및 소모 현황</p>
                </div>
                <div class="hidden sm:flex items-center space-x-4 text-sm">
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-primary-500 rounded-full mr-2"></span>
                        <span class="text-gray-600">섭취</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-accent-500 rounded-full mr-2"></span>
                        <span class="text-gray-600">소모</span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between mb-3">
                    <span class="text-lg font-semibold text-gray-900">
                        @{{ todayData.calorie_balance.net_calories.toFixed(0) }}
                        <span class="text-gray-400 font-normal">/ @{{ todayData.calorie_balance.target_calories }} kcal</span>
                    </span>
                    <span class="text-lg font-bold" :class="todayData.calorie_balance.percentage_of_target <= 100 ? 'text-primary-600' : 'text-rose-500'">
                        @{{ todayData.calorie_balance.percentage_of_target.toFixed(0) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                    <div
                        class="h-4 rounded-full transition-all duration-700 ease-out"
                        :class="todayData.calorie_balance.percentage_of_target <= 100 ? 'bg-gradient-to-r from-primary-400 to-accent-500' : 'bg-gradient-to-r from-rose-400 to-rose-500'"
                        :style="{ width: Math.min(todayData.calorie_balance.percentage_of_target, 100) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4 sm:gap-8">
                <div class="text-center p-4 bg-primary-50/50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 bg-primary-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">섭취</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">
                        @{{ todayData.calorie_balance.calories_consumed.toFixed(0) }}
                    </p>
                </div>
                <div class="text-center p-4 bg-accent-50/50 rounded-xl">
                    <div class="w-10 h-10 mx-auto mb-2 bg-accent-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">소모</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">
                        @{{ todayData.calorie_balance.calories_burned.toFixed(0) }}
                    </p>
                </div>
                <div class="text-center p-4 rounded-xl" :class="todayData.calorie_balance.remaining_calories >= 0 ? 'bg-green-50/50' : 'bg-rose-50/50'">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center" :class="todayData.calorie_balance.remaining_calories >= 0 ? 'bg-green-100' : 'bg-rose-100'">
                        <svg class="w-5 h-5" :class="todayData.calorie_balance.remaining_calories >= 0 ? 'text-green-600' : 'text-rose-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mb-1">남은 칼로리</p>
                    <p class="text-lg sm:text-xl font-bold" :class="todayData.calorie_balance.remaining_calories >= 0 ? 'text-green-600' : 'text-rose-600'">
                        @{{ todayData.calorie_balance.remaining_calories.toFixed(0) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Today's Summary Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Nutrition Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 font-heading">식사</h3>
                    </div>
                    <a href="{{ route('meals.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center cursor-pointer">
                        상세보기
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">총 칼로리</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.nutrition.calories_consumed }} kcal</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">단백질</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.nutrition.protein_g }} g</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">탄수화물</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.nutrition.carbs_g }} g</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">지방</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.nutrition.fat_g }} g</span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">오늘 식사 횟수</span>
                        <span class="font-semibold text-primary-600">@{{ todayData.nutrition.meal_count }}회</span>
                    </div>
                </div>
            </div>

            <!-- Exercise Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 font-heading">운동</h3>
                    </div>
                    <a href="{{ route('exercises.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center cursor-pointer">
                        상세보기
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">소모 칼로리</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.exercise.calories_burned }} kcal</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">운동 시간</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.exercise.duration_minutes }}분</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">운동 횟수</span>
                        <span class="font-semibold text-gray-900">@{{ todayData.exercise.exercise_count }}회</span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-3">강도별 운동</p>
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div class="p-2 bg-green-50 rounded-lg">
                            <p class="text-xs text-gray-500">낮음</p>
                            <p class="font-semibold text-green-600">@{{ todayData.exercise.exercises_by_intensity['낮음'] }}</p>
                        </div>
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">보통</p>
                            <p class="font-semibold text-blue-600">@{{ todayData.exercise.exercises_by_intensity['보통'] }}</p>
                        </div>
                        <div class="p-2 bg-orange-50 rounded-lg">
                            <p class="text-xs text-gray-500">높음</p>
                            <p class="font-semibold text-orange-600">@{{ todayData.exercise.exercises_by_intensity['높음'] }}</p>
                        </div>
                        <div class="p-2 bg-rose-50 rounded-lg">
                            <p class="text-xs text-gray-500">최고</p>
                            <p class="font-semibold text-rose-600">@{{ todayData.exercise.exercises_by_intensity['매우 높음'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 font-heading">체중</h3>
                    </div>
                    <a href="{{ route('weight.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center cursor-pointer">
                        상세보기
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div v-if="todayData.weight" class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-primary-50 to-accent-50 rounded-full mb-4">
                        <span class="text-3xl font-bold text-primary-600">@{{ todayData.weight.current_weight }}</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        kg · @{{ todayData.weight.last_updated ? formatDate(todayData.weight.last_updated) : '오늘' }} 측정
                    </p>
                </div>
                <div v-else class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 mb-4">아직 체중 기록이 없습니다</p>
                    <a href="{{ route('weight.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-500 text-white rounded-xl text-sm font-medium hover:bg-primary-600 transition-colors cursor-pointer">
                        체중 기록하기
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 font-heading mb-6">빠른 기록</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('meals.create') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl hover:from-emerald-100 hover:to-teal-100 transition-all duration-300 cursor-pointer">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">식사 추가</span>
                </a>
                <a href="{{ route('exercises.create') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 cursor-pointer">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">운동 추가</span>
                </a>
                <a href="{{ route('weight.index') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-primary-50 to-cyan-50 rounded-2xl hover:from-primary-100 hover:to-cyan-100 transition-all duration-300 cursor-pointer">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">체중 기록</span>
                </a>
                <a href="{{ route('diet-plan.index') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl hover:from-violet-100 hover:to-purple-100 transition-all duration-300 cursor-pointer">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-md flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
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
                const statsResponse = await axios.get('/dashboard/quick-stats');
                this.dashboard = statsResponse.data.data;

                const todayResponse = await axios.get('/dashboard/today');
                this.todayData = todayResponse.data.data;

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
@endpush
