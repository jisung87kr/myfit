@extends('layouts.app')

@section('title', '체중 기록 - MyFit')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <div id="weight-app">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-weight text-primary mr-2"></i>체중 관리
            </h1>
            <p class="text-sm text-gray-600 mt-1">체중 변화를 추적하고 목표를 달성하세요</p>
        </div>

        <!-- Weight Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600 mb-1">현재 체중</p>
                <p class="text-3xl font-bold text-primary">
                    @{{ stats.current_weight || '-' }}<span v-if="stats.current_weight" class="text-lg font-normal text-gray-600">kg</span>
                </p>
                <p v-if="stats.last_recorded" class="text-xs text-gray-500 mt-1">
                    @{{ formatDate(stats.last_recorded) }}
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600 mb-1">목표 체중</p>
                <p class="text-3xl font-bold text-secondary">
                    @{{ stats.target_weight || '-' }}<span v-if="stats.target_weight" class="text-lg font-normal text-gray-600">kg</span>
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600 mb-1">변화량</p>
                <p class="text-3xl font-bold" :class="getWeightChangeClass(stats.change_from_start)">
                    @{{ formatWeightChange(stats.change_from_start) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">시작일 기준</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-600 mb-1">남은 목표</p>
                <p class="text-3xl font-bold text-accent">
                    @{{ formatWeightChange(stats.remaining_to_goal) }}
                </p>
            </div>
        </div>

        <!-- Weight Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-line text-primary mr-2"></i>체중 변화 추이
                </h2>
                <div class="flex gap-2">
                    <button
                        v-for="period in chartPeriods"
                        :key="period.value"
                        @click="chartPeriod = period.value; loadWeightData()"
                        :class="['px-3 py-1 text-sm font-medium rounded-lg transition-colors', chartPeriod === period.value ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                    >
                        @{{ period.label }}
                    </button>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas ref="weightChart"></canvas>
            </div>
            <p v-if="!weightData || weightData.length === 0" class="text-center text-gray-500 py-12">
                체중 기록이 없습니다. 첫 기록을 추가해보세요!
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Weight Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-plus-circle text-primary mr-2"></i>@{{ isEditMode ? '체중 수정' : '체중 기록' }}
                    </h2>

                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                날짜 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                v-model="form.date"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                시간
                            </label>
                            <input
                                type="time"
                                v-model="form.time"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Weight -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                체중 (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model.number="form.weight_kg"
                                required
                                min="0"
                                step="0.1"
                                placeholder="65.5"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                메모
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="컨디션, 특이사항 등을 기록하세요..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                            ></textarea>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="rounded-md bg-red-50 p-3 border border-red-200">
                            <p class="text-sm text-red-800">@{{ errorMessage }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex-1 bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                            >
                                <span v-if="loading"><i class="fas fa-spinner fa-spin mr-1"></i>저장 중...</span>
                                <span v-else><i class="fas fa-save mr-1"></i>@{{ isEditMode ? '수정' : '추가' }}</span>
                            </button>
                            <button
                                v-if="isEditMode"
                                type="button"
                                @click="cancelEdit"
                                class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50"
                            >
                                취소
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Weight History -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history text-primary mr-2"></i>기록 내역
                    </h2>

                    <!-- Loading State -->
                    <div v-if="loadingHistory" class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
                        <p class="mt-4 text-gray-600">기록을 불러오는 중...</p>
                    </div>

                    <!-- History List -->
                    <div v-else-if="weightHistory.length > 0" class="space-y-3">
                        <div
                            v-for="(record, index) in weightHistory"
                            :key="record.id"
                            class="flex items-start justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl font-bold text-primary">@{{ record.weight_kg }}kg</span>
                                    <span
                                        v-if="index < weightHistory.length - 1"
                                        :class="['text-sm font-medium px-2 py-1 rounded-full', getWeightChangeClass(record.weight_kg - weightHistory[index + 1].weight_kg)]"
                                    >
                                        @{{ formatWeightChange(record.weight_kg - weightHistory[index + 1].weight_kg) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <i class="far fa-calendar mr-1"></i>@{{ formatDateTime(record.date, record.time) }}
                                </p>
                                <p v-if="record.notes" class="text-sm text-gray-600 mt-2 italic">
                                    <i class="far fa-comment-dots mr-1"></i>@{{ record.notes }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button @click="editWeight(record)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="수정">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete(record)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="삭제">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-weight text-5xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">기록이 없습니다</h3>
                        <p class="text-gray-600">왼쪽 폼에서 첫 체중을 기록해보세요!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">체중 기록 삭제</h3>
                <p class="text-gray-600 mb-6">
                    <span class="font-medium">@{{ recordToDelete?.weight_kg }}kg</span> 기록을 삭제하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        취소
                    </button>
                    <button @click="deleteWeight" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                        <span v-if="deleting"><i class="fas fa-spinner fa-spin mr-1"></i>삭제 중...</span>
                        <span v-else>삭제</span>
                    </button>
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
            stats: null,
            weightData: [],
            weightHistory: [],
            chartPeriod: 30,
            chartPeriods: [
                { value: 7, label: '1주일' },
                { value: 30, label: '1개월' },
                { value: 90, label: '3개월' },
                { value: 365, label: '1년' }
            ],
            chart: null,
            form: {
                date: new Date().toISOString().split('T')[0],
                time: new Date().toTimeString().slice(0, 5),
                weight_kg: null,
                notes: ''
            },
            isEditMode: false,
            editingId: null,
            loading: false,
            loadingHistory: false,
            errorMessage: '',
            showDeleteModal: false,
            recordToDelete: null,
            deleting: false
        }
    },
    mounted() {
        this.loadStats();
        this.loadWeightData();
        this.loadWeightHistory();
    },
    methods: {
        async loadStats() {
            try {
                const response = await axios.get('/daily-logs/weight/stats');
                this.stats = response.data.data || {};
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },
        async loadWeightData() {
            try {
                const response = await axios.get(`/daily-logs/weight?period=${this.chartPeriod}`);
                this.weightData = response.data.data || [];
                this.renderChart();
            } catch (error) {
                console.error('Failed to load weight data:', error);
            }
        },
        async loadWeightHistory() {
            this.loadingHistory = true;
            try {
                const response = await axios.get('/daily-logs/weight/history');
                this.weightHistory = response.data.data || [];
            } catch (error) {
                console.error('Failed to load weight history:', error);
            } finally {
                this.loadingHistory = false;
            }
        },
        renderChart() {
            if (this.chart) {
                this.chart.destroy();
            }

            if (!this.weightData || this.weightData.length === 0) {
                return;
            }

            const ctx = this.$refs.weightChart.getContext('2d');
            const labels = this.weightData.map(d => this.formatDate(d.date));
            const weights = this.weightData.map(d => d.weight_kg);
            const targetWeight = this.stats?.target_weight;

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '체중',
                            data: weights,
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        ...(targetWeight ? [{
                            label: '목표 체중',
                            data: new Array(weights.length).fill(targetWeight),
                            borderColor: 'rgb(239, 68, 68)',
                            borderDash: [5, 5],
                            tension: 0,
                            fill: false,
                            pointRadius: 0
                        }] : [])
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return value + 'kg';
                                }
                            }
                        }
                    }
                }
            });
        },
        async handleSubmit() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const url = this.isEditMode
                    ? `/daily-logs/weight/${this.editingId}`
                    : '/daily-logs/weight';

                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: {
                            message: this.isEditMode ? '체중이 수정되었습니다.' : '체중이 기록되었습니다.',
                            type: 'success'
                        }
                    }));

                    this.resetForm();
                    this.loadStats();
                    this.loadWeightData();
                    this.loadWeightHistory();
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.data || {};
                    this.errorMessage = Object.values(errors).flat().join(' ');
                } else {
                    this.errorMessage = '체중 기록 저장에 실패했습니다. 다시 시도해주세요.';
                }
            } finally {
                this.loading = false;
            }
        },
        editWeight(record) {
            this.isEditMode = true;
            this.editingId = record.id;
            this.form = {
                date: record.date,
                time: record.time || '',
                weight_kg: record.weight_kg,
                notes: record.notes || ''
            };

            // Scroll to form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        cancelEdit() {
            this.resetForm();
        },
        resetForm() {
            this.isEditMode = false;
            this.editingId = null;
            this.form = {
                date: new Date().toISOString().split('T')[0],
                time: new Date().toTimeString().slice(0, 5),
                weight_kg: null,
                notes: ''
            };
            this.errorMessage = '';
        },
        confirmDelete(record) {
            this.recordToDelete = record;
            this.showDeleteModal = true;
        },
        async deleteWeight() {
            if (!this.recordToDelete) return;

            this.deleting = true;
            try {
                await axios.delete(`/daily-logs/weight/${this.recordToDelete.id}`);

                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '체중 기록이 삭제되었습니다.', type: 'success' }
                }));

                this.showDeleteModal = false;
                this.recordToDelete = null;
                this.loadStats();
                this.loadWeightData();
                this.loadWeightHistory();
            } catch (error) {
                console.error('Failed to delete weight:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: '체중 기록 삭제에 실패했습니다.', type: 'error' }
                }));
            } finally {
                this.deleting = false;
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        },
        formatDateTime(date, time) {
            const dateStr = new Date(date).toLocaleDateString('ko-KR');
            return time ? `${dateStr} ${time}` : dateStr;
        },
        formatWeightChange(change) {
            if (!change) return '0kg';
            const prefix = change > 0 ? '+' : '';
            return `${prefix}${change.toFixed(1)}kg`;
        },
        getWeightChangeClass(change) {
            if (!change) return 'text-gray-600';
            return change > 0 ? 'text-red-600' : 'text-green-600';
        }
    }
}).mount('#weight-app');
</script>
@endpush
