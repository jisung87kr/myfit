import SurveyProgress from './SurveyProgress.js';
import SurveyStepHeader from './SurveyStepHeader.js';
import SurveyQuestion from './SurveyQuestion.js';
import SurveyNavigation from './SurveyNavigation.js';

export default {
    name: 'SurveyComponent',
    components: {
        SurveyProgress,
        SurveyStepHeader,
        SurveyQuestion,
        SurveyNavigation
    },
    props: {
        apiBaseUrl: {
            type: String,
            default: '/api'
        },
        redirectUrl: {
            type: String,
            default: '/diet-plan'
        },
        authToken: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            survey: null,
            currentStep: 1,
            totalSteps: 5,
            stepInfo: {
                step: 1,
                step_name: '',
                description: ''
            },
            questions: [],
            answers: {},
            loading: true,
            submitting: false,
            errorMessage: ''
        };
    },
    computed: {
        themeColor() {
            const colors = {
                1: 'primary',
                2: 'orange',
                3: 'green',
                4: 'purple',
                5: 'blue'
            };
            return colors[this.currentStep] || 'primary';
        },
        canProceed() {
            if (!this.questions.length) return false;

            return this.questions.every(q => {
                if (!q.is_required) return true;
                const answer = this.answers[q.question_text];
                if (answer === null || answer === undefined || answer === '') return false;
                if (Array.isArray(answer) && answer.length === 0) return false;
                return true;
            });
        },
        axiosConfig() {
            const config = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };
            if (this.authToken) {
                config.headers['Authorization'] = `Bearer ${this.authToken}`;
            }
            return config;
        }
    },
    async mounted() {
        await this.fetchSurvey();
    },
    methods: {
        async fetchSurvey() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await axios.get(`${this.apiBaseUrl}/surveys`, this.axiosConfig);

                if (response.data.success && response.data.data.survey) {
                    this.survey = response.data.data.survey;
                    await this.fetchQuestions();
                } else {
                    this.errorMessage = '활성화된 설문이 없습니다.';
                }
            } catch (error) {
                console.error('Failed to fetch survey:', error);
                this.errorMessage = '설문을 불러오는데 실패했습니다.';
            } finally {
                this.loading = false;
            }
        },
        async fetchQuestions() {
            if (!this.survey) return;

            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await axios.get(
                    `${this.apiBaseUrl}/surveys/${this.survey.id}/questions`,
                    {
                        ...this.axiosConfig,
                        params: { step: this.currentStep }
                    }
                );

                if (response.data.success) {
                    const data = response.data.data;
                    this.stepInfo = {
                        step: data.step,
                        step_name: data.step_name,
                        description: data.description
                    };
                    this.questions = data.questions || [];

                    // 기존 답변이 없는 질문들 초기화
                    this.questions.forEach(q => {
                        if (!(q.question_text in this.answers)) {
                            this.answers[q.question_text] = q.question_type === 'multi_select' ? [] : null;
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to fetch questions:', error);
                this.errorMessage = '질문을 불러오는데 실패했습니다.';
            } finally {
                this.loading = false;
            }
        },
        async nextStep() {
            if (this.canProceed && this.currentStep < this.totalSteps) {
                this.currentStep++;
                await this.fetchQuestions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        async previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                await this.fetchQuestions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        async submitSurvey() {
            if (!this.canProceed || this.submitting) return;

            this.submitting = true;
            this.errorMessage = '';

            try {
                const response = await axios.post(
                    `${this.apiBaseUrl}/surveys/${this.survey.id}/submit`,
                    { answers: this.answers },
                    this.axiosConfig
                );

                if (response.data.success) {
                    if (window.showToast) {
                        window.showToast('설문이 완료되었습니다! 이제 맞춤 식단을 생성할 수 있습니다.', 'success');
                    }

                    setTimeout(() => {
                        window.location.href = this.redirectUrl;
                    }, 1500);
                }
            } catch (error) {
                console.error('Failed to submit survey:', error);
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || error.response.data.errors || {};
                    this.errorMessage = Object.values(errors).flat().join(' ');
                } else {
                    this.errorMessage = '설문 제출에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.submitting = false;
            }
        },
        updateAnswer(questionText, value) {
            this.answers[questionText] = value;
        }
    },
    template: `
        <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header & Progress -->
                <div class="mb-10 text-center">
                    <h1 class="text-3xl font-bold text-gray-900 font-heading mb-4">
                        <span class="text-primary-600">MyFit</span> 시작하기
                    </h1>
                    <survey-progress
                        :current-step="currentStep"
                        :total-steps="totalSteps"
                    />
                </div>

                <!-- Main Card -->
                <div class="bg-white rounded-3xl p-8 md:p-10 shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-gray-100 min-h-[500px] relative flex flex-col justify-between animate-fade-in-up">

                    <!-- Loading State -->
                    <div v-if="loading" class="flex-grow flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-primary-500 mb-4"></i>
                            <p class="text-gray-500">설문을 불러오는 중...</p>
                        </div>
                    </div>

                    <!-- No Survey State -->
                    <div v-else-if="!survey" class="flex-grow flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6 text-gray-400">
                                <i class="fas fa-clipboard-list text-3xl"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-2">설문이 없습니다</h2>
                            <p class="text-gray-500">{{ errorMessage || '현재 활성화된 설문이 없습니다.' }}</p>
                        </div>
                    </div>

                    <!-- Survey Content -->
                    <template v-else>
                        <div class="flex-grow">
                            <!-- Step Header -->
                            <survey-step-header
                                :step="currentStep"
                                :step-name="stepInfo.step_name"
                                :description="stepInfo.description"
                                class="mb-8"
                            />

                            <!-- Questions -->
                            <div class="max-w-2xl mx-auto space-y-2">
                                <survey-question
                                    v-for="question in questions"
                                    :key="question.id"
                                    :question="question"
                                    :model-value="answers[question.question_text]"
                                    :theme-color="themeColor"
                                    @update:model-value="updateAnswer(question.question_text, $event)"
                                />
                            </div>

                            <!-- Error Message -->
                            <div v-if="errorMessage" class="mt-6 rounded-2xl bg-rose-50 p-4 border border-rose-100 flex items-start gap-3 max-w-2xl mx-auto">
                                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                                <p class="text-sm text-rose-700 font-medium">{{ errorMessage }}</p>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <survey-navigation
                            :current-step="currentStep"
                            :total-steps="totalSteps"
                            :can-proceed="canProceed"
                            :submitting="submitting"
                            @previous="previousStep"
                            @next="nextStep"
                            @submit="submitSurvey"
                        />
                    </template>
                </div>
            </div>
        </div>
    `
};
