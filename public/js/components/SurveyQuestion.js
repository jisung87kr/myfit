export default {
    name: 'SurveyQuestion',
    props: {
        question: {
            type: Object,
            required: true
        },
        modelValue: {
            type: [String, Number, Array],
            default: null
        },
        themeColor: {
            type: String,
            default: 'primary'
        }
    },
    emits: ['update:modelValue'],
    computed: {
        localValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
        colorClasses() {
            const colors = {
                primary: {
                    focus: 'focus:ring-primary-500',
                    selected: 'border-primary-500 bg-primary-50 text-primary-700 ring-2 ring-primary-200',
                    hover: 'hover:border-primary-200 hover:bg-gray-50',
                    icon: 'bg-primary-100 text-primary-600',
                    iconHover: 'group-hover:bg-primary-50 group-hover:text-primary-500'
                },
                orange: {
                    focus: 'focus:ring-orange-500',
                    selected: 'border-orange-500 bg-orange-50 text-orange-700 ring-2 ring-orange-200',
                    hover: 'hover:border-orange-200 hover:bg-gray-50',
                    icon: 'bg-orange-100 text-orange-600',
                    iconHover: 'group-hover:bg-orange-50 group-hover:text-orange-500'
                },
                green: {
                    focus: 'focus:ring-green-500',
                    selected: 'border-green-500 bg-green-50/30 ring-1 ring-green-500',
                    hover: 'hover:border-green-200',
                    icon: 'bg-green-100 text-green-600',
                    iconHover: 'group-hover:bg-green-50 group-hover:text-green-500'
                },
                purple: {
                    focus: 'focus:ring-purple-500',
                    selected: 'border-purple-500 bg-purple-50/30 ring-1 ring-purple-500',
                    hover: 'hover:border-purple-200',
                    icon: 'bg-purple-100 text-purple-600',
                    iconHover: 'group-hover:bg-purple-50 group-hover:text-purple-500'
                },
                blue: {
                    focus: 'focus:ring-blue-500',
                    selected: 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-200',
                    hover: 'hover:border-blue-200 hover:bg-gray-50',
                    icon: 'bg-blue-100 text-blue-600',
                    iconHover: 'group-hover:bg-blue-50 group-hover:text-blue-500'
                }
            };
            return colors[this.themeColor] || colors.primary;
        },
        unitSuffix() {
            const text = this.question.question_text.toLowerCase();
            if (text.includes('나이') || text.includes('세')) return '세';
            if (text.includes('키') || text.includes('cm')) return 'cm';
            if (text.includes('체중') || text.includes('kg')) return 'kg';
            if (text.includes('시간')) return '시간';
            return '';
        }
    },
    methods: {
        isSelected(option) {
            if (this.question.question_type === 'multi_select') {
                return Array.isArray(this.localValue) && this.localValue.includes(option);
            }
            return this.localValue === option;
        },
        toggleOption(option) {
            if (this.question.question_type === 'multi_select') {
                const current = Array.isArray(this.localValue) ? [...this.localValue] : [];
                const index = current.indexOf(option);
                if (index > -1) {
                    current.splice(index, 1);
                } else {
                    current.push(option);
                }
                this.localValue = current;
            } else {
                this.localValue = option;
            }
        }
    },
    template: `
        <div class="!mb-5">
            <label class="block text-lg font-bold text-gray-900 mb-2">
                {{ question.question_text }}
                <span v-if="question.is_required" class="text-rose-500 ml-1">*</span>
            </label>

            <!-- Text Input -->
            <div v-if="question.question_type === 'text'">
                <textarea
                    v-model="localValue"
                    rows="3"
                    :placeholder="'답변을 입력해주세요'"
                    :class="['w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:border-transparent transition-all resize-none font-medium placeholder-gray-400', colorClasses.focus]"
                ></textarea>
            </div>

            <!-- Number Input -->
            <div v-else-if="question.question_type === 'number'" class="relative">
                <input
                    type="number"
                    v-model.number="localValue"
                    :required="question.is_required"
                    min="0"
                    step="0.1"
                    :placeholder="'입력'"
                    :class="['w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:border-transparent transition-all text-lg font-medium placeholder-gray-400', colorClasses.focus]"
                >
                <span v-if="unitSuffix" class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">
                    {{ unitSuffix }}
                </span>
            </div>

            <!-- Select (단일 선택) -->
            <div v-else-if="question.question_type === 'select'" class="space-y-3">
                <button
                    v-for="option in question.options"
                    :key="option"
                    type="button"
                    @click="toggleOption(option)"
                    :class="['w-full p-4 rounded-2xl border transition-all duration-300 flex items-center gap-4 text-left group hover:shadow-md',
                        isSelected(option)
                        ? colorClasses.selected
                        : 'border-gray-100 bg-white ' + colorClasses.hover]"
                >
                    <div class="flex-grow">
                        <span :class="['font-medium', isSelected(option) ? '' : 'text-gray-900']">{{ option }}</span>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                        :class="isSelected(option) ? 'border-current' : 'border-gray-200'">
                        <div v-if="isSelected(option)" class="w-3 h-3 rounded-full bg-current"></div>
                    </div>
                </button>
            </div>

            <!-- Multi Select (다중 선택) -->
            <div v-else-if="question.question_type === 'multi_select'" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button
                    v-for="option in question.options"
                    :key="option"
                    type="button"
                    @click="toggleOption(option)"
                    :class="['p-4 rounded-2xl border transition-all duration-300 text-center group hover:shadow-md',
                        isSelected(option)
                        ? colorClasses.selected
                        : 'border-gray-100 bg-white ' + colorClasses.hover]"
                >
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0"
                            :class="isSelected(option) ? 'border-current bg-current' : 'border-gray-300'">
                            <svg v-if="isSelected(option)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span :class="['font-medium text-sm', isSelected(option) ? '' : 'text-gray-700']">{{ option }}</span>
                    </div>
                </button>
            </div>
        </div>
    `
};
