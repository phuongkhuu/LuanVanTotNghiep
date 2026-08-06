<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    reportData: Object,
    currentPeriod: String,
});

const period = ref(props.currentPeriod || 'week');
const periods = ['day', 'week', 'month', 'year'];
const isLoading = ref(false);

let revenueChart = null;
let categoryChart = null;

const summaryData = ref({
    retail: { revenue: props.reportData?.summary?.retail?.revenue || 0, growth: props.reportData?.summary?.retail?.growth || 0 },
    wholesale: { revenue: props.reportData?.summary?.wholesale?.revenue || 0, growth: props.reportData?.summary?.wholesale?.growth || 0 },
    preorder: { revenue: props.reportData?.summary?.preorder?.revenue || 0, growth: props.reportData?.summary?.preorder?.growth || 0 }
});

const topProducts = ref(props.reportData?.topProducts || []);
const topCustomers = ref(props.reportData?.topCustomers || []);
const chartData = ref(props.reportData?.chartData || {
    labels: [],
    retail: [],
    wholesale: [],
    preorder: []
});

const categoryDistribution = ref(props.reportData?.categoryDistribution || []);

// Hàm định dạng giá tiền chuẩn phân cách dấu chấm: 2562000 -> 2.562.000₫
const formatPrice = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0₫';
    return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '₫';
};

const getCategoryData = () => {
    const dist = categoryDistribution.value;
    if (Array.isArray(dist) && dist.length > 0) {
        if (typeof dist[0] === 'object' && dist[0].label !== undefined) {
            return {
                labels: dist.map(item => item.label),
                data: dist.map(item => item.value)
            };
        }
        return {
            labels: ['Balo', 'Cặp - Túi', 'Phụ kiện'],
            data: dist
        };
    }
    return { labels: ['Chưa có dữ liệu'], data: [100] };
};

const initRevenueChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();

    const data = chartData.value;
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: data.retail || [], 
                    backgroundColor: '#ff6b00', 
                    borderRadius: 5,
                    barPercentage: 0.55
                },
                { 
                    label: 'Bán sỉ', 
                    data: data.wholesale || [], 
                    backgroundColor: '#436651', 
                    borderRadius: 5,
                    barPercentage: 0.55
                },
                { 
                    label: 'Pre-order', 
                    data: data.preorder || [], 
                    backgroundColor: '#f59e0b', 
                    borderRadius: 5,
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
                    labels: { font: { size: 9 }, padding: 6, boxWidth: 10 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatPrice(context.raw || 0);
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'VNĐ', font: { size: 8 } },
                    grid: { color: '#f0f0f0' },
                    ticks: { 
                        font: { size: 8 },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                            return value;
                        }
                    }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 8 } }
                }
            }
        }
    });
};

const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();

    const { labels, data } = getCategoryData();
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#ff6b00', '#436651', '#89726c', '#f59e0b', '#e74c3c'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { 
                    position: 'right',
                    labels: { 
                        font: { size: 9 }, 
                        padding: 6,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 7
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            }
        }
    });
};

const updateCharts = async () => {
    await nextTick();
    if (revenueChart) {
        revenueChart.data.labels = chartData.value.labels || [];
        revenueChart.data.datasets[0].data = chartData.value.retail || [];
        revenueChart.data.datasets[1].data = chartData.value.wholesale || [];
        revenueChart.data.datasets[2].data = chartData.value.preorder || [];
        revenueChart.update();
    } else {
        initRevenueChart();
    }

    if (categoryChart) {
        const { labels, data } = getCategoryData();
        categoryChart.data.labels = labels;
        categoryChart.data.datasets[0].data = data;
        categoryChart.update();
    } else {
        initCategoryChart();
    }
};

const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { period: period.value }
        });

        const newData = response.data;
        if (newData) {
            summaryData.value = newData.summary || {
                retail: { revenue: 0, growth: 0 },
                wholesale: { revenue: 0, growth: 0 },
                preorder: { revenue: 0, growth: 0 }
            };
            topProducts.value = newData.topProducts || [];
            topCustomers.value = newData.topCustomers || [];
            chartData.value = newData.chartData || { labels: [], retail: [], wholesale: [], preorder: [] };
            categoryDistribution.value = newData.categoryDistribution || [];
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu báo cáo:', error);
    } finally {
        isLoading.value = false;
        updateCharts();
    }
};

const exportReport = () => {
    window.location.href = route('admin.reports.export', { period: period.value });
};

watch(period, () => {
    loadDataByPeriod();
});

onMounted(async () => {
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    } else {
        await nextTick();
        initRevenueChart();
        initCategoryChart();
    }
});

onUnmounted(() => {
    if (revenueChart) revenueChart.destroy();
    if (categoryChart) categoryChart.destroy();
});
</script>

<template>
    <Head title="Báo cáo thống kê - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-3 md:p-4">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h1 class="text-lg font-bold text-gray-800">Báo cáo thống kê</h1>
                <div class="flex items-center gap-1">
                    <div class="flex bg-gray-100 rounded-lg p-0.5">
                        <button 
                            v-for="p in periods" 
                            :key="p" 
                            @click="period = p" 
                            class="px-2.5 py-1 rounded-lg text-[10px] font-medium transition-all"
                            :class="period === p ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        >
                            {{ p === 'day' ? 'Ngày' : p === 'week' ? 'Tuần' : p === 'month' ? 'Tháng' : 'Năm' }}
                        </button>
                    </div>
                    <button 
                        @click="exportReport" 
                        class="px-2.5 py-1 bg-white border border-gray-200 rounded-lg text-[10px] font-medium text-gray-600 hover:bg-gray-50 transition-all"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>

            <!-- Content + Loading Overlay -->
            <div class="relative">
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/75 backdrop-blur-sm z-10 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="inline-block w-6 h-6 border-3 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs text-gray-500">Đang tải dữ liệu...</p>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="bg-white rounded-lg p-2.5 border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-medium">Bán lẻ</p>
                        <p class="text-sm font-bold text-gray-800">{{ formatPrice(summaryData.retail.revenue) }}</p>
                    </div>
                    
                    <div class="bg-white rounded-lg p-2.5 border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-medium">Bán sỉ</p>
                        <p class="text-sm font-bold text-gray-800">{{ formatPrice(summaryData.wholesale.revenue) }}</p>
                    </div>
                    
                    <div class="bg-white rounded-lg p-2.5 border border-gray-100 shadow-sm">
                        <p class="text-[10px] text-gray-400 font-medium">Pre-order</p>
                        <p class="text-sm font-bold text-gray-800">{{ formatPrice(summaryData.preorder.revenue) }}</p>
                    </div>
                </div>

                <!-- Charts (Giảm kích thước 20%) -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-3 mb-3">
                    <!-- Biểu đồ Doanh thu -->
                    <div class="lg:col-span-3 bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <h3 class="text-xs font-semibold text-gray-600 mb-1 flex-none">Doanh thu theo loại hình</h3>
                        <div class="w-full h-[160px] relative">
                            <canvas id="revenueByTypeChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Biểu đồ Phân bố Danh mục -->
                    <div class="lg:col-span-2 bg-white rounded-lg p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <h3 class="text-xs font-semibold text-gray-600 mb-1 flex-none">Phân bố danh mục</h3>
                        <div class="w-full h-[160px] flex items-center justify-center">
                            <canvas id="categoryChart" class="w-full h-full max-w-[224px] max-h-[160px]"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Products & Customers -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <!-- Top Sản Phẩm -->
                    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-xs font-semibold text-gray-600">Top sản phẩm bán chạy</h3>
                            <span class="text-[9px] text-gray-400">Theo doanh thu</span>
                        </div>
                        <div class="space-y-1">
                            <div v-for="(product, idx) in topProducts.slice(0, 4)" :key="idx" 
                                class="flex justify-between items-center px-2 py-1 rounded hover:bg-orange-50/50 transition-colors text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold shrink-0"
                                        :class="[
                                            idx === 0 ? 'bg-orange-100 text-orange-600' :
                                            idx === 1 ? 'bg-gray-100 text-gray-600' :
                                            'bg-gray-50 text-gray-400'
                                        ]"
                                    >
                                        {{ idx + 1 }}
                                    </span>
                                    <span class="text-gray-700 truncate max-w-[140px]" :title="product.name">{{ product.name }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold text-orange-600 text-[10px]">{{ formatPrice(product.revenue) }}</span>
                                    <span class="text-[9px] text-gray-400 ml-1">({{ product.sold || 0 }})</span>
                                </div>
                            </div>
                            <div v-if="topProducts.length === 0" class="text-center py-3 text-[10px] text-gray-400">
                                Chưa có dữ liệu
                            </div>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-gray-100">
                            <Link :href="route('admin.products.index')" class="text-[10px] text-orange-500 hover:underline flex justify-end">
                                Xem tất cả →
                            </Link>
                        </div>
                    </div>

                    <!-- Top Khách Hàng -->
                    <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-xs font-semibold text-gray-600">Top khách hàng thân thiết</h3>
                            <span class="text-[9px] text-gray-400">Theo tổng chi tiêu</span>
                        </div>
                        <div class="space-y-1">
                            <div v-for="(customer, idx) in topCustomers.slice(0, 4)" :key="idx" 
                                class="flex justify-between items-center px-2 py-1 rounded hover:bg-orange-50/50 transition-colors text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold shrink-0"
                                        :class="[
                                            idx === 0 ? 'bg-orange-100 text-orange-600' :
                                            idx === 1 ? 'bg-gray-100 text-gray-600' :
                                            'bg-gray-50 text-gray-400'
                                        ]"
                                    >
                                        {{ idx + 1 }}
                                    </span>
                                    <div>
                                        <span class="text-gray-700 font-medium">{{ customer.name }}</span>
                                        <span class="text-[9px] text-gray-400 ml-1">({{ customer.orders || 0 }} đơn)</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-orange-600 text-[10px]">{{ formatPrice(customer.total) }}</span>
                            </div>
                            <div v-if="topCustomers.length === 0" class="text-center py-3 text-[10px] text-gray-400">
                                Chưa có dữ liệu
                            </div>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-gray-100">
                            <Link :href="route('admin.customers.index')" class="text-[10px] text-orange-500 hover:underline flex justify-end">
                                Xem tất cả →
                            </Link>
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
</style>