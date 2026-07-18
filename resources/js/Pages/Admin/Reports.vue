<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

// Props
const props = defineProps({
    reportData: Object,
    currentPeriod: String,
});

// Period filter
const period = ref(props.currentPeriod || 'week');
const periods = ['week', 'month', 'year'];
const isLoading = ref(false);

// Chart instances
let revenueChart = null;
let categoryChart = null;

// Dữ liệu
const summaryData = ref(props.reportData?.summary || {
    retail: { revenue: 0, growth: 0 },
    wholesale: { revenue: 0, growth: 0 },
    preorder: { revenue: 0, growth: 0 }
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

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Lấy dữ liệu cho doughnut chart
const getCategoryData = () => {
    const dist = categoryDistribution.value;
    if (Array.isArray(dist) && dist.length > 0) {
        // Nếu là mảng object { label, value }
        if (typeof dist[0] === 'object' && dist[0].label !== undefined) {
            return {
                labels: dist.map(item => item.label),
                data: dist.map(item => item.value)
            };
        }
        // Nếu là mảng số
        return {
            labels: ['Balo', 'Cặp - Túi', 'Phụ kiện'],
            data: dist
        };
    }
    return { labels: ['Chưa có dữ liệu'], data: [100] };
};

// Khởi tạo biểu đồ doanh thu
const initRevenueChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();

    const data = chartData.value;
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: data.retail, 
                    backgroundColor: '#ff6b00', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Bán sỉ', 
                    data: data.wholesale, 
                    backgroundColor: '#436651', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Pre-order', 
                    data: data.preorder, 
                    backgroundColor: '#f59e0b', 
                    borderRadius: 6,
                    barPercentage: 0.7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)' },
                    grid: { color: '#e5e7eb' }
                },
                x: { 
                    grid: { display: false }
                }
            }
        }
    });
};

// Khởi tạo biểu đồ danh mục (doughnut)
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
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { font: { size: 12 } }
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

// Cập nhật biểu đồ (không tạo lại nếu đã tồn tại)
const updateCharts = () => {
    if (revenueChart) {
        revenueChart.data.labels = chartData.value.labels;
        revenueChart.data.datasets[0].data = chartData.value.retail;
        revenueChart.data.datasets[1].data = chartData.value.wholesale;
        revenueChart.data.datasets[2].data = chartData.value.preorder;
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

// Tải dữ liệu theo period
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { period: period.value }
        });

        const newData = response.data;
        if (newData) {
            summaryData.value = newData.summary || summaryData.value;
            topProducts.value = newData.topProducts || [];
            topCustomers.value = newData.topCustomers || [];
            chartData.value = newData.chartData || chartData.value;
            categoryDistribution.value = newData.categoryDistribution || [];
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
        // Cập nhật biểu đồ ngay sau khi dữ liệu về
        updateCharts();
    }
};

// Xuất báo cáo Excel
const exportReport = () => {
    window.location.href = route('admin.reports.export', { period: period.value });
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo
onMounted(() => {
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    } else {
        setTimeout(() => {
            initRevenueChart();
            initCategoryChart();
        }, 100);
    }
});
</script>

<template>
    <Head title="Báo cáo thống kê - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Báo cáo thống kê</h1>
            </div>

            <!-- Bộ lọc thời gian -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button 
                    v-for="p in periods" 
                    :key="p" 
                    @click="period = p" 
                    class="px-4 py-2 rounded-lg text-sm transition-all"
                    :class="period === p ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                    {{ p === 'week' ? 'Tuần' : p === 'month' ? 'Tháng' : 'Năm' }}
                </button>
                <button 
                    @click="exportReport" 
                    class="ml-auto bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm flex items-center gap-1 text-gray-600 hover:bg-gray-50 transition-colors"
                >
                    <span class="material-symbols-outlined text-sm">download</span>
                    Xuất báo cáo
                </button>
            </div>

            <!-- Nội dung chính + overlay loading -->
            <div class="relative">
                <!-- Loading overlay -->
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/75 z-10 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                        <p class="text-gray-500">Đang tải dữ liệu...</p>
                    </div>
                </div>

                <!-- Nội dung chính -->
                <div>
                    <!-- 3 thẻ tổng quan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <p class="text-sm text-gray-500 flex items-center gap-1">Bán lẻ</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.retail.revenue) }}</p>
                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">trending_up</span>
                                +{{ summaryData.retail.growth }}%
                            </p>
                        </div>
                        
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <p class="text-sm text-gray-500 flex items-center gap-1">Bán sỉ</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.wholesale.revenue) }}</p>
                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">trending_up</span>
                                +{{ summaryData.wholesale.growth }}%
                            </p>
                        </div>
                        
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <p class="text-sm text-gray-500 flex items-center gap-1">Pre-order</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.preorder.revenue) }}</p>
                            <p class="text-xs text-yellow-600 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">trending_up</span>
                                +{{ summaryData.preorder.growth }}%
                            </p>
                        </div>
                    </div>

                    <!-- Biểu đồ -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4">Doanh thu theo loại hình</h3>
                            <canvas id="revenueByTypeChart" height="250"></canvas>
                        </div>
                        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4">Phân bố danh mục</h3>
                            <canvas id="categoryChart" height="250"></canvas>
                        </div>
                    </div>

                    <!-- Top sản phẩm & khách hàng -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-gray-800">Top sản phẩm bán chạy</h3>
                                <span class="text-xs text-gray-500">Theo doanh thu</span>
                            </div>
                            <div class="space-y-3">
                                <div 
                                    v-for="(product, idx) in topProducts" 
                                    :key="idx" 
                                    class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
                                        <span class="text-gray-700">{{ product.name }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold text-orange-600">{{ formatPrice(product.revenue) }}</span>
                                        <span class="text-xs text-gray-400 ml-2">({{ product.sold }} cái)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <Link :href="route('admin.products.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">
                                    Xem tất cả sản phẩm
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </Link>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-gray-800">Top khách hàng thân thiết</h3>
                                <span class="text-xs text-gray-500">Theo tổng chi tiêu</span>
                            </div>
                            <div class="space-y-3">
                                <div 
                                    v-for="(customer, idx) in topCustomers" 
                                    :key="idx" 
                                    class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
                                        <div>
                                            <span class="text-gray-700">{{ customer.name }}</span>
                                            <span class="text-xs text-gray-400 ml-2">({{ customer.orders || 0 }} đơn)</span>
                                        </div>
                                    </div>
                                    <div class="font-semibold text-orange-600">{{ formatPrice(customer.total) }}</div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <Link :href="route('admin.customers.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">
                                    Xem tất cả khách hàng
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </Link>
                            </div>
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