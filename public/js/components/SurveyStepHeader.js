export default {
    name: 'SurveyStepHeader',
    props: {
        step: {
            type: Number,
            required: true
        },
        stepName: {
            type: String,
            default: ''
        },
        description: {
            type: String,
            default: ''
        }
    },
    computed: {
        stepConfig() {
            const configs = {
                1: {
                    icon: 'fas fa-hand-sparkles',
                    bgColor: 'bg-primary-50',
                    textColor: 'text-primary-600'
                },
                2: {
                    icon: 'fas fa-bullseye',
                    bgColor: 'bg-orange-50',
                    textColor: 'text-orange-600'
                },
                3: {
                    icon: 'fas fa-running',
                    bgColor: 'bg-green-50',
                    textColor: 'text-green-600'
                },
                4: {
                    icon: 'fas fa-utensils',
                    bgColor: 'bg-purple-50',
                    textColor: 'text-purple-600'
                },
                5: {
                    icon: 'fas fa-clipboard-check',
                    bgColor: 'bg-blue-50',
                    textColor: 'text-blue-600'
                }
            };
            return configs[this.step] || configs[1];
        }
    },
    template: `
        <div class="text-center">
            <div :class="[stepConfig.bgColor, stepConfig.textColor, 'w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6']">
                <i :class="[stepConfig.icon, 'text-3xl']"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 font-heading mb-2">{{ stepName }}</h2>
            <p class="text-gray-500">{{ description }}</p>
        </div>
    `
};
