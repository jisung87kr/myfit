@extends('layouts.app')

@section('title', '체중 기록 - MyFit')

@section('content')
<div id="weight-app" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-10 animate-fade-in-up">
        <h1 class="text-3xl font-bold text-gray-900 font-heading mb-2">체중 관리</h1>
        <p class="text-gray-500">체중 변화를 추적하고 건강한 목표를 달성하세요.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        <!-- Left Column: Stats & Chart (Span 8) -->
        <div class="lg:col-span-8 space-y-8">
            <!-- Stats Grid -->
            <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Current -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
                    <div class="flex items-center gap-2 mb-3 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                        현재 체중
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-gray-900 font-heading">@{{ stats.current_weight || '-' }}</span>
                        <span class="text-sm text-gray-500 font-medium">kg</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">@{{ stats.last_recorded ? formatDate(stats.last_recorded) : '-' }}</p>
                </div>

                <!-- Goal -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
                    <div class="flex items-center gap-2 mb-3 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <div class="w-2 h-2 rounded-full bg-accent-500"></div>
                        목표 체중
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-gray-900 font-heading">@{{ stats.target_weight || '-' }}</span>
                        <span class="text-sm text-gray-500 font-medium">kg</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Goal</p>
                </div>

                <!-- Change -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
                    <div class="flex items-center gap-2 mb-3 text-gray-500 text-xs font-bold uppercase tracking-wider">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        총 변화
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-heading" :class="getWeightChangeClass(stats.change_from_start)">
                            @{{ formatWeightChange(stats.change_from_start) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Since Start</p>
                </div>

                <!-- Remaining -->
                <div class="bg-gradient-to-br from-primary-600 to-accent-600 rounded-3xl p-5 shadow-lg shadow-primary-500/20 text-white transform hover:-translate-y-1 transition-transform">
                    <div class="flex items-center gap-2 mb-3 text-primary-100 text-xs font-bold uppercase tracking-wider">
                        <div class="w-2 h-2 rounded-full bg-white"></div>
                        남은 목표
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-heading">@{{ formatWeightChange(stats.remaining_to_goal) }}</span>
                    </div>
                    <p class="text-xs text-primary-100 mt-2">To Go</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 font-heading">체중 변화 추이</h2>
                    <div class="bg-gray-100 p-1 rounded-xl flex gap-1">
                        <button
                            v-for="period in chartPeriods"
                            :key="period.value"
                            @click="chartPeriod = period.value; loadWeightData()"
                            :class="['px-3 py-1.5 text-xs font-bold rounded-lg transition-all', chartPeriod === period.value ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                        >
                            @{{ period.label }}
                        </button>
                    </div>
                </div>
                <div class="relative h-[300px] w-full">
                    <canvas ref="weightChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column: Form & History (Span 4) -->
        <div class="lg:col-span-4 space-y-8">
            <!-- Add Weight Form -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h2 class="text-lg font-bold text-gray-900 mb-6 font-heading flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    @{{ isEditMode ? '기록 수정' : '체중 기록' }}
                </h2>

                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase">날짜</label>
                        <input
                            type="date"
                            v-model="form.date"
                            required
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-medium text-gray-900"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase">시간</label>
                            <input
                                type="time"
                                v-model="form.time"
                                class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-medium text-gray-900"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase">체중 (kg)</label>
                            <input
                                type="number"
                                v-model.number="form.weight_kg"
                                required
                                min="0"
                                step="0.1"
                                placeholder="0.0"
                                class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all font-bold text-gray-900"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase">메모</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="메모를 남겨보세요"
                            class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 transition-all resize-none text-sm"
                        ></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="flex-1 py-3.5 px-4 bg-gray-900 text-white rounded-2xl font-bold text-sm hover:bg-black transition-all shadow-lg shadow-gray-900/20 disabled:opacity-50 cursor-pointer"
                        >
                            <span v-if="loading">저장 중...</span>
                            <span v-else>@{{ isEditMode ? '수정 완료' : '기록 저장' }}</span>
                        </button>
                        <button
                            v-if="isEditMode"
                            type="button"
                            @click="cancelEdit"
                            class="px-4 py-3.5 bg-gray-100 text-gray-600 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all cursor-pointer"
                        >
                            취소
                        </button>
                    </div>
                </form>
            </div>

            <!-- History List -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 font-heading">최근 기록</h3>
                </div>
                
                <div v-if="loadingHistory" class="p-8 text-center">
                    <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                </div>

                <div v-else-if="weightHistory.length > 0" class="max-h-[400px] overflow-y-auto custom-scrollbar">
                    <div v-for="(record, index) in weightHistory" :key="record.id" class="p-4 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-sm" :class="getWeightChangeIconClass(record, index)">
                                    <svg v-if="getWeightDelta(record, index) < 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                                    <svg v-else-if="getWeightDelta(record, index) > 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">@{{ record.weight_kg }}kg</p>
                                    <p class="text-xs text-gray-500">@{{ formatDateTime(record.date) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <span v-if="index < weightHistory.length - 1" class="text-xs font-bold" :class="getWeightDelta(record, index) <= 0 ? 'text-green-600' : 'text-red-500'">
                                    @{{ formatWeightChange(getWeightDelta(record, index)) }}
                                </span>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editWeight(record)" class="p-1.5 text-gray-400 hover:text-primary-600 bg-white hover:bg-primary-50 border border-gray-200 rounded-lg cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="confirmDelete(record)" class="p-1.5 text-gray-400 hover:text-red-600 bg-white hover:bg-red-50 border border-gray-200 rounded-lg cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="p-8 text-center text-gray-500 text-sm">
                    기록이 없습니다.
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" @click.self="showDeleteModal = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all scale-100">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4 mx-auto">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2 font-heading">기록 삭제</h3>
            <p class="text-gray-500 text-center text-sm mb-6">
                <span class="font-bold text-gray-700">@{{ recordToDelete?.weight_kg }}kg</span> 기록을 삭제하시겠습니까?<br>복구할 수 없습니다.
            </p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors font-medium cursor-pointer">
                    취소
                </button>
                <button @click="deleteWeight" :disabled="deleting" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 font-medium cursor-pointer">
                    <span v-if="deleting">삭제 중...</span>
                    <span v-else>삭제</span>
                </button>
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
                { value: 7, label: '1주' },
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
            
            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
            gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');

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
                            borderColor: '#0891b2', // primary-600
                            backgroundColor: gradient,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#0891b2',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            borderWidth: 3
                        },
                        ...(targetWeight ? [{
                            label: '목표',
                            data: new Array(weights.length).fill(targetWeight),
                            borderColor: '#14b8a6', // accent-500
                            borderDash: [5, 5],
                            borderWidth: 2,
                            tension: 0,
                            fill: false,
                            pointRadius: 0
                        }] : [])
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#1f2937',
                            bodyColor: '#1f2937',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxTicksLimit: 7 }
                        },
                        y: {
                            grid: { color: '#f3f4f6' },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        },
        async handleSubmit() {
            this.loading = true;
            try {
                const url = this.isEditMode
                    ? `/daily-logs/weight/${this.editingId}`
                    : '/daily-logs/weight';
                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                if (response.data.success) {
                    if (window.showToast) window.showToast(this.isEditMode ? '체중이 수정되었습니다.' : '체중이 기록되었습니다.', 'success');
                    this.resetForm();
                    this.loadStats();
                    this.loadWeightData();
                    this.loadWeightHistory();
                }
            } catch (error) {
                console.error('Save failed:', error);
                if (window.showToast) window.showToast('저장에 실패했습니다.', 'error');
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
                if (window.showToast) window.showToast('체중 기록이 삭제되었습니다.', 'success');
                this.showDeleteModal = false;
                this.recordToDelete = null;
                this.loadStats();
                this.loadWeightData();
                this.loadWeightHistory();
            } catch (error) {
                if (window.showToast) window.showToast('삭제에 실패했습니다.', 'error');
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
            if (delta < 0) return 'bg-gradient-to-br from-emerald-400 to-teal-500 shadow-emerald-500/30';
            if (delta > 0) return 'bg-gradient-to-br from-rose-400 to-red-500 shadow-rose-500/30';
            return 'bg-gray-300';
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        },
        formatDateTime(date) {
            return new Date(date).toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
        },
        formatWeightChange(change) {
            if (!change) return '0kg';
            const prefix = change > 0 ? '+' : '';
            return `${prefix}${Number(change).toFixed(1)}kg`;
        },
        getWeightChangeClass(change) {
            if (!change) return 'text-gray-400';
            return change > 0 ? 'text-rose-500' : 'text-emerald-500';
        }
    }
}).mount('#weight-app');
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate3d(0, 20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}
</style>
@endpush