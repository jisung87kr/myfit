@extends('layouts.app')

@section('title', '식단 플랜 목록 - MyFit')

@section('content')
<div id="diet-plan-list-app" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Survey Reminder Banner -->
    <div v-if="!loading && surveyStatus.has_survey && !surveyStatus.is_completed" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 animate-fade-in-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-amber-800">맞춤 식단 생성을 위한 설문이 필요합니다</p>
                <p class="text-sm text-amber-600">설문을 완료하시면 AI가 당신에게 딱 맞는 식단을 만들어 드립니다.</p>
            </div>
            <button @click="openGenerateModal" class="px-4 py-2 bg-amber-500 text-white font-bold text-sm rounded-xl hover:bg-amber-600 transition-colors flex-shrink-0 cursor-pointer">
                설문 시작하기
            </button>
        </div>
    </div>

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-heading mb-2">나의 식단 플랜</h1>
            <p class="text-gray-500">AI가 생성한 맞춤 식단 플랜을 관리하세요.</p>
        </div>
        <div>
            <button @click="openGenerateModal" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-black transition-all shadow-lg shadow-gray-900/20 flex items-center cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                새 플랜 생성
            </button>
        </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-primary-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="dietPlans.length === 0" class="bg-white rounded-3xl border border-dashed border-gray-200 p-16 text-center animate-fade-in-up">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2 font-heading">아직 식단 플랜이 없습니다</h3>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">AI가 당신의 목표와 신체 정보에 맞춰 최적의 식단을 생성해드립니다.</p>
        <button @click="openGenerateModal" class="px-6 py-3 bg-gradient-to-r from-primary-500 to-purple-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 cursor-pointer">
            맞춤 식단 생성하기
        </button>
    </div>

    <!-- Plan List -->
    <div v-else class="space-y-4 animate-fade-in-up">
        <div v-for="plan in dietPlans" :key="plan.id" :class="['bg-white rounded-2xl border shadow-sm transition-all overflow-hidden', plan.status === 'generating' ? 'border-amber-200 bg-amber-50/30' : 'border-gray-100 hover:shadow-md hover:border-primary-200']">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                    <!-- Plan Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <!-- Generating Spinner -->
                            <div v-if="plan.status === 'generating'" class="w-6 h-6 flex-shrink-0">
                                <div class="w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">@{{ plan.duration_days }}일 식단 플랜</h3>
                            <span :class="['px-2.5 py-1 text-xs font-bold rounded-lg', getStatusClass(plan.status)]">
                                @{{ getStatusLabel(plan.status) }}
                            </span>
                        </div>
                        <!-- Generating Message -->
                        <div v-if="plan.status === 'generating'" class="flex items-center gap-2 text-amber-700 text-sm mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>AI가 맞춤 식단을 생성하고 있습니다. 잠시만 기다려주세요...</span>
                        </div>
                        <p v-else class="text-sm text-gray-500 mb-3 flex items-center gap-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @{{ formatDate(plan.start_date) }} ~ @{{ formatDate(plan.end_date) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                @{{ Math.round(plan.target_calories_per_day) }} kcal/일
                            </span>
                        </p>
                        <p v-if="plan.ai_summary && plan.status !== 'generating'" class="text-sm text-gray-600 line-clamp-2">@{{ plan.ai_summary }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <template v-if="plan.status === 'generating'">
                            <span class="px-4 py-2 bg-amber-100 text-amber-600 font-bold text-sm rounded-xl">
                                생성중...
                            </span>
                        </template>
                        <template v-else>
                            <a :href="'/diet-plan/' + plan.id" class="px-4 py-2 bg-primary-50 text-primary-700 font-bold text-sm rounded-xl hover:bg-primary-100 transition-colors">
                                상세보기
                            </a>
                            <button @click="confirmDelete(plan)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <!-- Progress Bar for Active Plans -->
            <div v-if="plan.status === 'active'" class="h-1 bg-gray-100">
                <div class="h-full bg-gradient-to-r from-primary-500 to-purple-500" :style="{ width: getProgressPercent(plan) + '%' }"></div>
            </div>
            <!-- Generating Progress Bar -->
            <div v-if="plan.status === 'generating'" class="h-1 bg-amber-100 overflow-hidden">
                <div class="h-full w-1/3 bg-gradient-to-r from-amber-400 to-amber-500 animate-pulse rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- Generate Modal - Step Based -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="closeGenerateModal">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
            <!-- Step Indicator -->
            <div class="flex items-center justify-center gap-2 mb-6">
                <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all', modalStep === 1 ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-400']">1</div>
                <div class="w-8 h-0.5 bg-gray-200"></div>
                <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all', modalStep === 2 ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-400']">2</div>
            </div>

            <!-- Step 1: Survey Selection -->
            <div v-if="modalStep === 1">
                <h3 class="text-xl font-bold text-gray-900 mb-2 font-heading flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    설문 선택
                </h3>
                <p class="text-gray-500 text-sm mb-6">식단 플랜 생성에 사용할 설문을 선택하세요.</p>

                <div class="space-y-3 mb-6">
                    <!-- New Survey Option -->
                    <button
                        type="button"
                        @click="selectSurveyOption('new')"
                        :class="['w-full p-4 rounded-2xl border-2 text-left transition-all cursor-pointer', generateForm.surveyOption === 'new' ? 'border-primary-500 bg-primary-50' : 'border-gray-100 hover:border-gray-300']"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', generateForm.surveyOption === 'new' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-500']">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">새 설문 작성</p>
                                <p class="text-sm text-gray-500">새로운 설문을 작성하여 맞춤 식단을 받습니다</p>
                            </div>
                        </div>
                    </button>

                    <!-- Existing Submissions -->
                    <template v-if="surveyStatus.submissions && surveyStatus.submissions.length > 0">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                            <div class="relative flex justify-center"><span class="px-3 bg-white text-xs font-bold text-gray-400 uppercase">이전 설문 사용</span></div>
                        </div>
                        <button
                            v-for="submission in surveyStatus.submissions"
                            :key="submission.id"
                            type="button"
                            @click="selectSurveyOption(submission.id)"
                            :class="['w-full p-4 rounded-2xl border-2 text-left transition-all cursor-pointer', generateForm.surveyOption === submission.id ? 'border-primary-500 bg-primary-50' : 'border-gray-100 hover:border-gray-300']"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', generateForm.surveyOption === submission.id ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-500']">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 truncate">@{{ submission.summary }}</p>
                                    <p class="text-sm text-gray-500">@{{ formatDateTime(submission.submitted_at) }}</p>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="closeGenerateModal" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition-colors cursor-pointer">취소</button>
                    <button type="button" @click="proceedToStep2" :disabled="!generateForm.surveyOption" class="flex-[2] py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-colors disabled:opacity-50 cursor-pointer">
                        다음
                    </button>
                </div>
            </div>

            <!-- Step 2: Duration Selection -->
            <div v-if="modalStep === 2">
                <h3 class="text-xl font-bold text-gray-900 mb-2 font-heading flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    기간 설정
                </h3>
                <p class="text-gray-500 text-sm mb-6">식단 플랜의 기간을 선택하세요.</p>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">플랜 기간</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="generateForm.duration_days = 7" :class="['py-3 rounded-xl text-sm font-bold border-2 transition-all cursor-pointer', generateForm.duration_days === 7 ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-100 text-gray-500 hover:border-gray-300']">
                                <span class="block text-lg">7일</span>
                                <span class="block text-xs text-gray-400 mt-0.5">1주</span>
                            </button>
                            <button type="button" @click="generateForm.duration_days = 14" :class="['py-3 rounded-xl text-sm font-bold border-2 transition-all cursor-pointer', generateForm.duration_days === 14 ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-100 text-gray-500 hover:border-gray-300']">
                                <span class="block text-lg">14일</span>
                                <span class="block text-xs text-gray-400 mt-0.5">2주</span>
                            </button>
                            <button type="button" @click="generateForm.duration_days = 30" :class="['py-3 rounded-xl text-sm font-bold border-2 transition-all cursor-pointer', generateForm.duration_days === 30 ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-100 text-gray-500 hover:border-gray-300']">
                                <span class="block text-lg">30일</span>
                                <span class="block text-xs text-gray-400 mt-0.5">1달</span>
                            </button>
                        </div>
                    </div>

                    <!-- Selected Survey Summary -->
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">선택된 설문</p>
                        <p class="font-bold text-gray-900">@{{ getSelectedSurveySummary() }}</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 rounded-xl text-red-600 text-sm font-medium">
                        @{{ errorMessage }}
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modalStep = 1" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition-colors cursor-pointer">이전</button>
                        <button type="button" @click="generateDietPlan" class="flex-[2] py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-colors cursor-pointer">
                            AI 생성 시작
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Modal (for new survey) -->
    <div v-if="showSurveyModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">새 설문 작성</h3>
                <button @click="closeSurveyModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto" style="max-height: calc(90vh - 60px);">
                <survey :embed-mode="true" @survey-completed="onSurveyCompleted"></survey>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2 font-heading">플랜을 삭제하시겠습니까?</h3>
            <p class="text-gray-500 mb-6">이 작업은 되돌릴 수 없습니다. 모든 식단과 운동 계획이 삭제됩니다.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition-colors cursor-pointer">취소</button>
                <button @click="deletePlan" :disabled="deleting" class="flex-1 py-3 bg-red-500 text-white rounded-2xl font-bold hover:bg-red-600 transition-colors disabled:opacity-50 cursor-pointer">
                    <span v-if="deleting">삭제 중...</span>
                    <span v-else>삭제</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">

import Survey from '{{ asset('/js/components/SurveyComponent.js') }}';

Vue.createApp({
    components: { Survey },
    data() {
        return {
            dietPlans: [],
            loading: false,
            showGenerateModal: false,
            showDeleteModal: false,
            showSurveyModal: false,
            selectedPlan: null,
            generatingPlanId: null,
            pollInterval: null,
            deleting: false,
            errorMessage: '',
            modalStep: 1,
            generateForm: {
                duration_days: 7,
                surveyOption: null,
                survey_submission_id: null
            },
            surveyStatus: {
                has_survey: false,
                is_completed: false,
                survey_id: null,
                submissions: []
            }
        }
    },
    mounted() {
        this.loadDietPlans().then(() => this.checkGeneratingPlans());
    },
    beforeUnmount() {
        this.stopPolling();
    },
    methods: {
        async loadDietPlans() {
            this.loading = true;
            try {
                const response = await axios.get('/api/diet-plans');
                if (response.data.data) {
                    this.dietPlans = response.data.data.diet_plans || [];
                    this.surveyStatus = response.data.data.survey_status || this.surveyStatus;
                }
            } catch (error) {
                console.error('Failed to load diet plans:', error);
            } finally {
                this.loading = false;
            }
        },
        openGenerateModal() {
            this.modalStep = 1;
            this.generateForm.surveyOption = null;
            this.generateForm.survey_submission_id = null;
            this.generateForm.duration_days = 7;
            this.errorMessage = '';
            this.showGenerateModal = true;
        },
        closeGenerateModal() {
            this.showGenerateModal = false;
            this.modalStep = 1;
        },
        selectSurveyOption(option) {
            this.generateForm.surveyOption = option;
        },
        proceedToStep2() {
            if (this.generateForm.surveyOption === 'new') {
                this.showGenerateModal = false;
                this.showSurveyModal = true;
            } else {
                this.generateForm.survey_submission_id = this.generateForm.surveyOption;
                this.modalStep = 2;
            }
        },
        closeSurveyModal() {
            this.showSurveyModal = false;
            this.showGenerateModal = true;
        },
        onSurveyCompleted(submissionData) {
            this.showSurveyModal = false;
            this.generateForm.survey_submission_id = submissionData.submission_id;
            this.generateForm.surveyOption = submissionData.submission_id;
            this.modalStep = 2;
            this.showGenerateModal = true;
            this.loadDietPlans();
        },
        getSelectedSurveySummary() {
            if (this.generateForm.surveyOption === 'new') {
                return '새 설문 작성';
            }
            const submission = this.surveyStatus.submissions?.find(s => s.id === this.generateForm.surveyOption);
            if (submission) {
                return submission.summary;
            }
            return '선택된 설문 없음';
        },
        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('ko-KR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        async generateDietPlan() {
            this.showGenerateModal = false;
            this.errorMessage = '';
            try {
                const payload = {
                    duration_days: this.generateForm.duration_days,
                    survey_submission_id: this.generateForm.survey_submission_id
                };
                const response = await axios.post('/api/diet-plans/generate', payload);
                if (response.data.success) {
                    this.generatingPlanId = response.data.data.diet_plan_id;
                    if (window.showToast) window.showToast('식단 생성이 시작되었습니다. 완료되면 알려드릴게요!', 'info');
                    await this.loadDietPlans();
                    this.startPolling();
                }
            } catch (error) {
                this.errorMessage = error.response?.data?.message || '식단 생성에 실패했습니다.';
                if (window.showToast) window.showToast(this.errorMessage, 'error');
            }
        },
        checkGeneratingPlans() {
            const generatingPlan = this.dietPlans.find(p => p.status === 'generating');
            if (generatingPlan && !this.pollInterval) {
                this.generatingPlanId = generatingPlan.id;
                this.startPolling();
            }
        },
        startPolling() {
            this.stopPolling();
            this.pollInterval = setInterval(() => this.pollGenerationStatus(), 5000);
        },
        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },
        async pollGenerationStatus() {
            if (!this.generatingPlanId) {
                this.stopPolling();
                return;
            }
            try {
                const response = await axios.get(`/api/diet-plans/generation-status/${this.generatingPlanId}`);
                const status = response.data.data.status;

                if (status === 'active') {
                    this.stopPolling();
                    this.generatingPlanId = null;
                    if (window.showToast) window.showToast('식단 플랜이 생성되었습니다!', 'success');
                    await this.loadDietPlans();
                } else if (status === 'failed') {
                    this.stopPolling();
                    this.generatingPlanId = null;
                    if (window.showToast) window.showToast('식단 생성에 실패했습니다. 다시 시도해주세요.', 'error');
                    await this.loadDietPlans();
                }
            } catch (error) {
                console.error('Failed to poll status:', error);
            }
        },
        confirmDelete(plan) {
            this.selectedPlan = plan;
            this.showDeleteModal = true;
        },
        async deletePlan() {
            if (!this.selectedPlan) return;
            this.deleting = true;
            try {
                await axios.delete(`/api/diet-plans/${this.selectedPlan.id}`);
                if (window.showToast) window.showToast('플랜이 삭제되었습니다.', 'success');
                this.dietPlans = this.dietPlans.filter(p => p.id !== this.selectedPlan.id);
                this.showDeleteModal = false;
                this.selectedPlan = null;
            } catch (error) {
                if (window.showToast) window.showToast('삭제에 실패했습니다.', 'error');
            } finally {
                this.deleting = false;
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        },
        getStatusLabel(status) {
            const labels = { 'active': '진행중', 'completed': '완료', 'generating': '생성중', 'failed': '실패', 'archived': '보관됨' };
            return labels[status] || status;
        },
        getStatusClass(status) {
            const classes = {
                'active': 'bg-green-50 text-green-700',
                'completed': 'bg-blue-50 text-blue-700',
                'generating': 'bg-amber-50 text-amber-700',
                'failed': 'bg-red-50 text-red-700',
                'archived': 'bg-gray-50 text-gray-700'
            };
            return classes[status] || 'bg-gray-50 text-gray-700';
        },
        getProgressPercent(plan) {
            const start = new Date(plan.start_date);
            const end = new Date(plan.end_date);
            const now = new Date();
            const total = end - start;
            const elapsed = now - start;
            return Math.min(100, Math.max(0, (elapsed / total) * 100));
        }
    }
}).mount('#diet-plan-list-app');
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate3d(0, 20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
