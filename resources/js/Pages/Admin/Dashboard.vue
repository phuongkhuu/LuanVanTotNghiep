<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    growth: Object,
    recentOrders: Array,
    topRetail: Array,
    topWholesale: Array,
    topPreorder: Array,
    chartWeek: Object,
    chartMonth: Object,
    currentPeriod: String,
});

const page = usePage();
const selectedPeriod = ref(props.currentPeriod || 'week');
let revenueChart = null;

// ===== COMPUTED: Lấy dữ liệu biểu đồ theo kỳ =====
const chartData = computed(() => {
    return selectedPeriod.value === 'week' ? props.chartWeek : props.chartMonth;
});

// ===== COMPUTED: Tổng doanh thu từng loại (đơn vị: triệu VNĐ) =====
const totalRevenueByType = computed(() => {
    const data = chartData.value;
    if (!data) {
        return { retail: 0, wholesale: 0, preorder: 0 };
    }
    const retailTotal = (data.retail || []).reduce((sum, val) => sum + val, 0);
    const wholesaleTotal = (data.wholesale || []).reduce((sum, val) => sum + val, 0);
    const preorderTotal = (data.preorder || []).reduce((sum, val) => sum + val, 0);
    return {
        retail: retailTotal,
        wholesale: wholesaleTotal,
        preorder: preorderTotal
    };
});

// ===== FORMAT TIỀN =====
const formatPrice = (value) => {
    if (value === undefined || value === null || isNaN(value)) return '0₫';
    return new Intl.NumberFormat('vi-VN').format(value) + '₫';
};

// ===== CHART =====
const initChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();
    
    const data = chartData.value;
    const labels = data?.labels || [];
    const retail = data?.retail || [];
    const wholesale = data?.wholesale || [];
    const preorder = data?.preorder || [];
    
    const chartFont = {
        family: "Inter, ui-sans-serif, system-ui, -apple-system, sans-serif"
    };

    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: retail, 
                    backgroundColor: '#ea580c', 
                    borderRadius: 6,
                    barPercentage: 0.55
                },
                { 
                    label: 'Bán sỉ', 
                    data: wholesale, 
                    backgroundColor: '#059669', 
                    borderRadius: 6,
                    barPercentage: 0.55
                },
                { 
                    label: 'Pre-order', 
                    data: preorder, 
                    backgroundColor: '#d97706', 
                    borderRadius: 6,
                    barPercentage: 0.55
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { 
                        font: { ...chartFont, size: 12, weight: '500' }, 
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { ...chartFont, size: 12, weight: 'bold' },
                    bodyFont: { ...chartFont, size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: ${context.raw.toFixed(1)} triệu ₫`;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Đơn vị: Triệu ₫', font: { ...chartFont, size: 11, weight: '500' }, color: '#64748b' },
                    grid: { color: '#f1f5f9' },
                    border: { dash: [4, 4] },
                    ticks: { font: { ...chartFont, size: 11 }, color: '#64748b' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { ...chartFont, size: 11 }, color: '#64748b' }
                }
            }
        }
    });
};

// ===== THAY ĐỔI KỲ =====
const handlePeriodChange = () => {
    router.visit(route('admin.dashboard'), {
        method: 'get',
        data: { period: selectedPeriod.value },
        preserveScroll: true,
        preserveState: true,
        only: ['chartWeek', 'chartMonth', 'currentPeriod'],
        onSuccess: () => {
            initChart();
        }
    });
};

// ===== WATCH =====
watch(() => [props.chartWeek, props.chartMonth, props.currentPeriod], () => {
    if (props.currentPeriod && props.currentPeriod !== selectedPeriod.value) {
        selectedPeriod.value = props.currentPeriod;
    }
    initChart();
}, { deep: true });

// ===== MOUNTED =====
onMounted(() => {
    setTimeout(() => initChart(), 100);
});
</script>

<template>
    <Head title="Dashboard - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-6 space-y-6 bg-slate-50/50 min-h-screen">
            <!-- Header Welcome -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1 border-b border-gray-200 pb-4 mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Chào mừng trở lại, Admin</h1>
                </div>
            </div>

            <!-- Stats Cards - 6 thẻ chỉ số -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
                <!-- Bán lẻ -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Bán lẻ</p>
                    <p class="text-lg font-bold text-gray-800 my-1.5 tracking-tight tabular-nums">
                        {{ formatPrice(totalRevenueByType.retail * 1000000) }}
                    </p>
                </div>

                <!-- Bán sỉ -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Bán sỉ</p>
                    <p class="text-lg font-bold text-gray-800 my-1.5 tracking-tight tabular-nums">
                        {{ formatPrice(totalRevenueByType.wholesale * 1000000) }}
                    </p>
                </div>

                <!-- Pre-order -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Pre-order</p>
                    <p class="text-lg font-bold text-gray-800 my-1.5 tracking-tight tabular-nums">
                        {{ formatPrice(totalRevenueByType.preorder * 1000000) }}
                    </p>
                </div>

                <!-- Đơn hàng (giữ nguyên) -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Đơn hàng</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1 tabular-nums">{{ stats.totalOrders }}</p>
                </div>

                <!-- Khách hàng (giữ nguyên) -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Khách hàng</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1 tabular-nums">{{ stats.totalCustomers }}</p>
                </div>

                <!-- Tồn kho thấp (giữ nguyên) -->
                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <p class="text-[11px] text-gray-600 font-semibold uppercase tracking-wider">Tồn kho thấp</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1 tabular-nums">{{ stats.lowStockProducts }}</p>
                </div>
            </div>

            <!-- Charts & Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">
                <!-- Biểu đồ doanh thu -->
                <div class="lg:col-span-3 bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Doanh thu theo loại hình</h3>
                            <p class="text-[11px] text-gray-500">Thống kê phân bỏ doanh thu kinh doanh</p>
                        </div>
                        <select 
                            v-model="selectedPeriod" 
                            @change="handlePeriodChange"
                            class="text-xs border border-gray-300 rounded-lg px-8 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer"
                        >
                            <option value="week">7 ngày qua</option>
                            <option value="month">4 tuần qua</option>
                        </select>
                    </div>
                    <div class="relative w-full h-[240px]">
                        <canvas id="revenueByTypeChart"></canvas>
                    </div>
                </div>
                
                <!-- Đơn hàng gần đây -->
                <div class="lg:col-span-2 bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Đơn hàng gần đây</h3>
                            <p class="text-[11px] text-gray-500">Cập nhật danh sách đơn mới</p>
                        </div>
                        <Link :href="route('admin.orders.index')" class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                            Xem tất cả &rarr;
                        </Link>
                    </div>

                    <div class="space-y-2 overflow-y-auto max-h-[240px] pr-1">
                        <div 
                            v-for="order in recentOrders.slice(0, 5)" 
                            :key="order.code" 
                            class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-orange-50/30 hover:border-orange-100 transition-all text-xs"
                        >
                            <div class="flex flex-col min-w-0 pr-2">
                                <span class="font-medium text-gray-800 truncate text-[12px] tabular-nums">{{ order.code }}</span>
                                <span class="text-gray-500 truncate text-[11px]">{{ order.customer }}</span>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="font-semibold text-gray-800 text-[12px] tabular-nums">{{ order.amount }}</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full font-medium tracking-tight border" :class="order.statusClass">
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Top Bán Lẻ -->
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                    <div class="mb-3 pb-2 border-b border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Top bán lẻ</h3>
                    </div>
                    <div class="space-y-1.5">
                        <div v-for="(p, idx) in topRetail.slice(0, 4)" :key="idx" 
                            class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 transition-colors text-xs border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <span class="w-5 h-5 rounded-md text-xs font-bold flex items-center justify-center flex-shrink-0 tabular-nums"
                                      :class="idx === 0 ? 'bg-amber-100 text-amber-700' : idx === 1 ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400'">
                                    {{ idx + 1 }}
                                </span>
                                <span class="text-gray-700 font-medium truncate text-[12px]">{{ p.name }}</span>
                            </div>
                            <span class="text-gray-800 font-semibold bg-gray-100 px-2 py-0.5 rounded text-[11px] flex-shrink-0 tabular-nums">{{ p.sold }} SP</span>
                        </div>
                    </div>
                </div>
                
                <!-- Top Bán Sỉ -->
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                    <div class="mb-3 pb-2 border-b border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Top bán sỉ</h3>
                    </div>
                    <div class="space-y-1.5">
                        <div v-for="(p, idx) in topWholesale.slice(0, 4)" :key="idx" 
                            class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 transition-colors text-xs border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <span class="w-5 h-5 rounded-md text-xs font-bold flex items-center justify-center flex-shrink-0 tabular-nums"
                                      :class="idx === 0 ? 'bg-amber-100 text-amber-700' : idx === 1 ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400'">
                                    {{ idx + 1 }}
                                </span>
                                <span class="text-gray-700 font-medium truncate text-[12px]">{{ p.name }}</span>
                            </div>
                            <span class="text-gray-800 font-semibold bg-gray-100 px-2 py-0.5 rounded text-[11px] flex-shrink-0 tabular-nums">{{ p.sold }} SP</span>
                        </div>
                    </div>
                </div>
                
                <!-- Top Pre-order -->
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                    <div class="mb-3 pb-2 border-b border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Pre-order nổi bật</h3>
                    </div>
                    <div class="space-y-1.5">
                        <div v-for="(p, idx) in topPreorder.slice(0, 4)" :key="idx" 
                            class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 transition-colors text-xs border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <span class="w-5 h-5 rounded-md text-xs font-bold flex items-center justify-center flex-shrink-0 tabular-nums"
                                      :class="idx === 0 ? 'bg-amber-100 text-amber-700' : idx === 1 ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400'">
                                    {{ idx + 1 }}
                                </span>
                                <span class="text-gray-700 font-medium truncate text-[12px]">{{ p.name }}</span>
                            </div>
                            <span class="text-gray-800 font-semibold bg-gray-100 px-2 py-0.5 rounded text-[11px] flex-shrink-0 tabular-nums">{{ p.sold }} SP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>