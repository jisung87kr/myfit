export default {
    name: 'SurveyNavigation',
    props: {
        currentStep: {
            type: Number,
            required: true
        },
        totalSteps: {
            type: Number,
            default: 5
        },
        canProceed: {
            type: Boolean,
            default: false
        },
        submitting: {
            type: Boolean,
            default: false
        }
    },
    emits: ['previous', 'next', 'submit'],
    computed: {
        isFirstStep() {
            return this.currentStep === 1;
        },
        isLastStep() {
            return this.currentStep === this.totalSteps;
        }
    },
    template: `
        <div class="flex items-center justify-between mt-10 pt-6 border-t border-gray-100">
            <!-- Previous Button -->
            <button
                v-if="!isFirstStep"
                @click="$emit('previous')"
                type="button"
                class="px-6 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-colors flex items-center gap-2"
            >
                <i class="fas fa-arrow-left text-sm"></i>
                <span>이전</span>
            </button>
            <div v-else></div>

            <!-- Next Button -->
            <button
                v-if="!isLastStep"
                @click="$emit('next')"
                :disabled="!canProceed"
                type="button"
                class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed flex items-center gap-2"
            >
                <span>다음</span>
                <i class="fas fa-arrow-right text-sm"></i>
            </button>

            <!-- Submit Button -->
            <button
                v-else
                @click="$emit('submit')"
                :disabled="!canProceed || submitting"
                type="button"
                class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2"
            >
                <span v-if="submitting">
                    <i class="fas fa-spinner fa-spin mr-2"></i>생성 중...
                </span>
                <span v-else>완료 및 시작하기</span>
            </button>
        </div>
    `
};
