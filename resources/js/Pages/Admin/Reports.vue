<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Period filter
const period = ref('Tuần');
const periods = ['Tuần', 'Tháng', 'Năm'];
const isLoading = ref(false);

// Chart instances
let revenueChart = null;
let categoryChart = null;

// Data for charts
const revenueData = {
    retail: [8, 10, 12, 15, 18, 22, 20],
    wholesale: [15, 18, 22, 28, 35, 42, 38],
    preorder: [3, 4, 5, 7, 9, 12, 10]
};

// Summary data
const summaryData = ref({
    retail: { revenue: 45200000, growth: 12.5 },
    wholesale: { revenue: 128500000, growth: 23.8 },
    preorder: { revenue: 18300000, growth: 5.2 }
});

// Top products
const topProducts = ref([
    { name: 'Balo Doanh Nhân Elite', sold: 145, revenue: 464000000, revenueFormatted: '464.000.000₫' },
    { name: 'Túi Du Lịch Nomad', sold: 98, revenue: 181300000, revenueFormatted: '181.300.000₫' },
    { name: 'Balo Công Sở Commuter', sold: 87, revenue: 138330000, revenueFormatted: '138.330.000₫' },
    { name: 'Balo Tech Nova', sold: 56, revenue: 112000000, revenueFormatted: '112.000.000₫' },
    { name: 'Túi Da Cao Cấp', sold: 45, revenue: 67500000, revenueFormatted: '67.500.000₫' }
]);

// Top customers
const topCustomers = ref([
    { name: 'Công ty TNHH ABC', total: 156000000, totalFormatted: '156.000.000₫', orders: 8 },
    { name: 'Nguyễn Văn A', total: 28500000, totalFormatted: '28.500.000₫', orders: 12 },
    { name: 'Công ty TechPro', total: 45200000, totalFormatted: '45.200.000₫', orders: 5 },
    { name: 'Trần Thị B', total: 18900000, totalFormatted: '18.900.000₫', orders: 7 },
    { name: 'Doanh nghiệp XYZ', total: 89000000, totalFormatted: '89.000.000₫', orders: 4 }
]);

// Format currency
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Initialize revenue chart
const initRevenueChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: revenueData.retail, 
                    backgroundColor: '#ff6b00', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Bán sỉ', 
                    data: revenueData.wholesale, 
                    backgroundColor: '#436651', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Pre-order', 
                    data: revenueData.preorder, 
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value} triệu ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (triệu ₫)', font: { family: 'Montserrat' } },
                    grid: { color: '#e5e7eb' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { family: 'Montserrat' } }
                }
            }
        }
    });
};

// Initialize category chart
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Balo', 'Cặp - Túi', 'Phụ kiện'],
            datasets: [{
                data: [58, 28, 14],
                backgroundColor: ['#ff6b00', '#436651', '#89726c'],
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            return `${label}: ${value}%`;
                        }
                    }
                }
            }
        }
    });
};

// Load data by period
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        await router.get('/admin/reports/data', { period: period.value }, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.reportsData) {
                    updateChartsData(page.props.reportsData);
                }
            }
        });
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Update charts data
const updateChartsData = (data) => {
    if (revenueChart && data.revenue) {
        revenueChart.data.datasets[0].data = data.revenue.retail || revenueData.retail;
        revenueChart.data.datasets[1].data = data.revenue.wholesale || revenueData.wholesale;
        revenueChart.data.datasets[2].data = data.revenue.preorder || revenueData.preorder;
        revenueChart.update();
    }
    
    if (categoryChart && data.category) {
        categoryChart.data.datasets[0].data = data.category;
        categoryChart.update();
    }
    
    if (data.summary) {
        summaryData.value = data.summary;
    }
    
    if (data.topProducts) {
        topProducts.value = data.topProducts;
    }
    
    if (data.topCustomers) {
        topCustomers.value = data.topCustomers;
    }
};

// Export report
const exportReport = async () => {
    try {
        await router.post('/admin/reports/export', { period: period.value }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Xuất báo cáo thành công!');
            },
            onError: () => {
                alert('Có lỗi xảy ra khi xuất báo cáo');
            }
        });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Watch for period change
watch(period, () => {
    loadDataByPeriod();
});

// Initialize on mount
onMounted(() => {
    setTimeout(() => {
        initRevenueChart();
        initCategoryChart();
    }, 100);
});
</script>

<template>
    <Head title="Báo cáo thống kê - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Báo cáo thống kê</h1>
                <p class="text-gray-600 text-sm mt-1">Tổng quan doanh thu theo từng loại hình bán hàng</p>
            </div>

            <!-- Period Filter -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button 
                    v-for="p in periods" 
                    :key="p" 
                    @click="period = p" 
                    class="px-4 py-2 rounded-lg text-sm transition-all"
                    :class="period === p ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                    {{ p }}
                </button>
                <button 
                    @click="exportReport" 
                    class="ml-auto bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm flex items-center gap-1 text-gray-600 hover:bg-gray-50 transition-colors"
                >
                    <span class="material-symbols-outlined text-sm">download</span>
                    Xuất báo cáo
                </button>
            </div>

            <!-- Loading indicator -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
                <!-- Summary Cards -->
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

                <!-- Charts -->
              
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-4">Doanh thu theo loại hình</h3>
                        <canvas id="revenueByTypeChart" height="250"></canvas>
                    </div>
                    
                    
                

                <!-- Top Products & Customers -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Products -->
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
                                    <span class="font-semibold text-orange-600">{{ product.revenueFormatted || formatPrice(product.revenue) }}</span>
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

                    <!-- Top Customers -->
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
                                <div class="font-semibold text-orange-600">{{ customer.totalFormatted || formatPrice(customer.total) }}</div>
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
    </AdminLayout>
</template>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>