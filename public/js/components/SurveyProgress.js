export default {
    name: 'SurveyProgress',
    props: {
        currentStep: {
            type: Number,
            required: true
        },
        totalSteps: {
            type: Number,
            default: 5
        }
    },
    computed: {
        progressPercent() {
            return (this.currentStep / this.totalSteps) * 100;
        }
    },
    template: `
        <div class="max-w-md mx-auto">
            <div class="flex justify-between text-xs font-medium text-gray-400 mb-2 uppercase tracking-wider">
                <span>Start</span>
                <span>Step {{ currentStep }} of {{ totalSteps }}</span>
                <span>Finish</span>
            </div>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                    :style="{ width: progressPercent + '%' }"
                    class="h-full bg-primary-500 rounded-full transition-all duration-500 ease-out"
                ></div>
            </div>
        </div>
    `
};
