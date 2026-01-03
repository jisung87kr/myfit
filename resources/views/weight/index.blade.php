@extends('layouts.app')

@section('title', '체중 기록 - MyFit')

@section('content')
<div class="max-w-6xl mx-auto">
    <div id="weight-app">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 font-heading flex items-center">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                체중 관리
            </h1>
            <p class="text-gray-500 mt-1 ml-13">체중 변화를 추적하고 목표를 달성하세요</p>
        </div>

        <!-- Weight Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">현재 체중</p>
                    <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-primary-600">
                    @{{ stats.current_weight || '-' }}<span v-if="stats.current_weight" class="text-lg font-normal text-gray-500">kg</span>
                </p>
                <p v-if="stats.last_recorded" class="text-xs text-gray-500 mt-2">
                    @{{ formatDate(stats.last_recorded) }}
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">목표 체중</p>
                    <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-accent-600">
                    @{{ stats.target_weight || '-' }}<span v-if="stats.target_weight" class="text-lg font-normal text-gray-500">kg</span>
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">변화량</p>
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold" :class="getWeightChangeClass(stats.change_from_start)">
                    @{{ formatWeightChange(stats.change_from_start) }}
                </p>
                <p class="text-xs text-gray-500 mt-2">시작일 기준</p>
            </div>

            <div class="bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl shadow-lg shadow-primary-500/20 p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-white/80">남은 목표</p>
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold">
                    @{{ formatWeightChange(stats.remaining_to_goal) }}
                </p>
            </div>
        </div>

        <!-- Weight Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 font-heading flex items-center">
                    <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    체중 변화 추이
                </h2>
                <div class="flex gap-2">
                    <button
                        v-for="period in chartPeriods"
                        :key="period.value"
                        @click="chartPeriod = period.value; loadWeightData()"
                        :class="['px-4 py-2 text-sm font-medium rounded-xl transition-all cursor-pointer', chartPeriod === period.value ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-lg shadow-primary-500/25' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
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
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6 font-heading flex items-center">
                        <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        @{{ isEditMode ? '체중 수정' : '체중 기록' }}
                    </h2>

                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                날짜 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                v-model="form.date"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                시간
                            </label>
                            <input
                                type="time"
                                v-model="form.time"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

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
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                메모
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="컨디션, 특이사항 등을 기록하세요..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none transition-all"
                            ></textarea>
                        </div>

                        <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-100 rounded-xl">
                            <p class="text-sm text-red-700">@{{ errorMessage }}</p>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex-1 py-3 px-4 bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-primary-500/25 transition-all disabled:opacity-50 cursor-pointer"
                            >
                                <span v-if="loading">저장 중...</span>
                                <span v-else>@{{ isEditMode ? '수정' : '추가' }}</span>
                            </button>
                            <button
                                v-if="isEditMode"
                                type="button"
                                @click="cancelEdit"
                                class="px-4 py-3 border border-gray-200 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-all cursor-pointer"
                            >
                                취소
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Weight History -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-lg font-semibold text-gray-900 font-heading flex items-center">
                            <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            기록 내역
                        </h2>
                    </div>

                    <div v-if="loadingHistory" class="text-center py-12">
                        <div class="w-10 h-10 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin mx-auto"></div>
                        <p class="mt-4 text-gray-500">기록을 불러오는 중...</p>
                    </div>

                    <div v-else-if="weightHistory.length > 0" class="divide-y divide-gray-100">
                        <div
                            v-for="(record, index) in weightHistory"
                            :key="record.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" :class="getWeightChangeIconClass(record, index)">
                                        <svg v-if="getWeightDelta(record, index) < 0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                        </svg>
                                        <svg v-else-if="getWeightDelta(record, index) > 0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-gray-900">@{{ record.weight_kg }}kg</p>
                                        <p class="text-sm text-gray-500">@{{ formatDateTime(record.date, record.time) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span
                                        v-if="index < weightHistory.length - 1"
                                        class="px-3 py-1 rounded-full text-sm font-medium"
                                        :class="getWeightDelta(record, index) <= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    >
                                        @{{ formatWeightChange(getWeightDelta(record, index)) }}
                                    </span>
                                    <div class="flex gap-1">
                                        <button @click="editWeight(record)" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(record)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p v-if="record.notes" class="text-sm text-gray-600 mt-3 ml-16 italic">
                                @{{ record.notes }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center py-16">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 font-heading">기록이 없습니다</h3>
                        <p class="text-gray-500">왼쪽 폼에서 첫 체중을 기록해보세요!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 font-heading">체중 기록 삭제</h3>
                <p class="text-gray-600 mb-6">
                    <span class="font-medium">@{{ recordToDelete?.weight_kg }}kg</span> 기록을 삭제하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium cursor-pointer">
                        취소
                    </button>
                    <button @click="deleteWeight" :disabled="deleting" class="px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 font-medium cursor-pointer">
                        <span v-if="deleting">삭제 중...</span>
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
                            borderColor: 'rgb(6, 182, 212)',
                            backgroundColor: 'rgba(6, 182, 212, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'white',
                            pointBorderColor: 'rgb(6, 182, 212)',
                            pointBorderWidth: 2
                        },
                        ...(targetWeight ? [{
                            label: '목표 체중',
                            data: new Array(weights.length).fill(targetWeight),
                            borderColor: 'rgb(20, 184, 166)',
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
                        legend: { display: true, position: 'top' }
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
            } finally {
                this.deleting = false;
            }
        },
        getWeightDelta(record, index) {
            if (index >= this.weightHistory.length - 1) return 0;
            return record.weight_kg - this.weightHistory[index + 1].weight_kg;
        },
        getWeightChangeIconClass(record, index) {
            const delta = this.getWeightDelta(record, index);
            if (delta < 0) return 'bg-gradient-to-br from-green-500 to-emerald-500';
            if (delta > 0) return 'bg-gradient-to-br from-red-500 to-orange-500';
            return 'bg-gradient-to-br from-gray-400 to-gray-500';
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
