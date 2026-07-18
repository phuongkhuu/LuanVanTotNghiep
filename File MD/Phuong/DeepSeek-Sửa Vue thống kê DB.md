# Sửa Vue thống kê DB

**Created:** 07/15/2026 12:39  
**Updated:** 07/16/2026 15:12  
**Exported:** 07/16/2026 15:17  
**Link:** [https://chat.deepseek.com/a/chat/s/836d4dca-912b-481d-b1c9-062cb9fc9a8c](https://chat.deepseek.com/a/chat/s/836d4dca-912b-481d-b1c9-062cb9fc9a8c)  

## User:
07/16/2026 11:52

> File: web_ba_lo (1).sql

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Period state
const selectedPeriod = ref('week');
let revenueChart = null;

// Recent orders data
const recentOrders = ref([
    { code: '#ORD-001', customer: 'Nguyễn Văn A', type: 'Bán lẻ', amount: '2.500.000₫', status: 'Hoàn thành', statusClass: 'bg-green-100 text-green-700' },
    { code: '#ORD-002', customer: 'Công ty ABC', type: 'Bán sỉ', amount: '18.500.000₫', status: 'Đang giao', statusClass: 'bg-blue-100 text-blue-700' },
    { code: '#ORD-003', customer: 'Trần Thị B', type: 'Pre-order', amount: '1.850.000₫', status: 'Chờ xác nhận', statusClass: 'bg-yellow-100 text-yellow-700' }
]);

// Top products
const topRetail = ref([
    { name: 'Balo Doanh Nhân Elite', sold: 145 },
    { name: 'Túi Du Lịch Nomad', sold: 98 },
    { name: 'Balo Công Sở Commuter', sold: 87 }
]);

const topWholesale = ref([
    { name: 'Balo Elite - Order số lượng lớn', quantity: 12 },
    { name: 'Túi Nomad - Doanh nghiệp', quantity: 8 },
    { name: 'Balo Tech Nova - Đối tác', quantity: 5 }
]);

const topPreorder = ref([
    { name: 'Balo Limited Edition 2025', preordered: 45 },
    { name: 'Túi Da Cao Cấp', preordered: 32 },
    { name: 'Set Balo + Ví', preordered: 28 }
]);

// Data cho các khoảng thời gian
const chartData = {
    week: {
        labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'],
        retail: [8, 10, 7, 12, 15, 18, 14],
        wholesale: [15, 18, 12, 22, 28, 35, 25],
        preorder: [5, 4, 6, 8, 10, 12, 7]
    },
    month: {
        labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
        retail: [45, 52, 48, 58],
        wholesale: [85, 92, 78, 98],
        preorder: [28, 32, 35, 42]
    }
};

// Hàm khởi tạo biểu đồ
const initChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();
    
    const data = chartData[selectedPeriod.value];
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: data.retail, 
                    backgroundColor: '#f97316', 
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
                            return context.dataset.label + ': ' + context.raw + ' triệu ₫';
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (triệu ₫)', font: { size: 11 } },
                    grid: { color: '#e5e7eb' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
};

// Xử lý khi đổi period
const handlePeriodChange = () => {
    initChart();
};

onMounted(() => {
    setTimeout(() => initChart(), 100);
});
</script>

<template>
    <Head title="Dashboard - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-5">
            <!-- Welcome -->
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Chào mừng trở lại, Admin</h1>
            </div>

            <!-- Stats Cards - 3 loại hình bán hàng -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <!-- Bán lẻ -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bán lẻ hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">8.250.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +12.5% so với hôm qua</p>
                    </div>
                </div>

                <!-- Bán sỉ -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bán sỉ hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">32.500.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +23.5% so với hôm qua</p>
                    </div>
                </div>

                <!-- Pre-order -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Pre-order hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">5.200.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +5.2% so với hôm qua</p>
                    </div>
                </div>
            </div>

            <!-- Charts & Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-800">Doanh thu theo loại hình</h3>
                        <select 
                            v-model="selectedPeriod" 
                            @change="handlePeriodChange"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-gray-400"
                        >
                            <option value="week">7 ngày qua</option>
                            <option value="month">4 tuần qua</option>
                        </select>
                    </div>
                    <canvas id="revenueByTypeChart" style="height: 280px; width: 100%;"></canvas>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-800">Đơn hàng gần đây</h3>
                        <Link :href="route('admin.orders.index')" class="text-sm text-orange-600 hover:underline">Xem tất cả →</Link>
                    </div>
                    <div class="space-y-2">
                        <div v-for="order in recentOrders" :key="order.code" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ order.code }}</p>
                                <p class="text-xs text-gray-400">{{ order.customer }} - {{ order.type }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="order.statusClass">{{ order.status }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ order.amount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top sản phẩm theo loại -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Top bán lẻ</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topRetail" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Top bán sỉ</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topWholesale" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.quantity }} đơn</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Pre-order nổi bật</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topPreorder" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.preordered }} đơn</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

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

Đối với db này, tôi cần phải chỉnh sửa 2 page vue này như thế nào để thống kê được dữ liệu thật + xuất file excel cho thống kê dựa trên mẫu tương tự dưới đây?
<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $type;
    protected $orders;

    public function __construct($type = 'retail', $orders = null)
    {
        $this->type = $type;
        $this->orders = $orders;
    }

    public function collection()
    {
        if ($this->orders) {
            return $this->orders;
        }

        $query = Order::with(['details.productVariant.product', 'payment']);
        
        // Nếu type là 'all', lấy tất cả, không filter
        if ($this->type !== 'all') {
            $query->where('order_code', $this->type);
        }
        
        return $query->latest()
            ->get()
            ->map(function ($order) {
                return $this->formatOrder($order);
            });
    }

    protected function formatOrder($order)
    {
        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        }

        // Lấy danh sách sản phẩm dạng text
        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        return (object) [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'type' => $order->order_code ?? 'retail',
            'customer_name' => $order->customer_name ?? $order->receiver_name,
            'customer_phone' => $order->customer_phone ?? $order->receiver_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'created_date' => $order->created_at->format('d/m/Y H:i'),
            'products' => $productList,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment,
            'status' => $order->getStatusLabel(),
            'note' => $order->note ?? '',
        ];
    }

    public function headings(): array
    {
        $typeLabels = [
            'retail' => 'BÁN LẺ',
            'wholesale' => 'BÁN SỈ',
            'preorder' => 'PRE-ORDER',
            'all' => 'TẤT CẢ'
        ];

        $typeLabel = $typeLabels[$this->type] ?? 'Đơn hàng';

        return [
            ['DANH SÁCH ĐƠN HÀNG ' . strtoupper($typeLabel)],
            [''],
            [
                'STT',
                'Mã đơn hàng',
                'Loại',
                'Người đặt',
                'SĐT đặt',
                'Người nhận',
                'SĐT nhận',
                'Địa chỉ giao',
                'Ngày đặt',
                'Sản phẩm',
                'Tạm tính',
                'Phí ship',
                'Giảm giá',
                'Tổng cộng',
                'Hình thức TT',
                'Trạng thái',
                'Ghi chú'
            ]
        ];
    }

    public function map($order): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $order->code,
            $this->getTypeLabel($order->type),
            $order->customer_name,
            $order->customer_phone,
            $order->receiver_name,
            $order->receiver_phone,
            $order->shipping_address,
            $order->created_date,
            $order->products,
            number_format($order->subtotal) . 'đ',
            number_format($order->shipping_fee) . 'đ',
            number_format($order->discount_amount) . 'đ',
            number_format($order->final_amount) . 'đ',
            $order->payment_method,
            $order->status,
            $order->note,
        ];
    }

    protected function getTypeLabel($type)
    {
        $labels = [
            'retail' => 'Bán lẻ',
            'wholesale' => 'Bán sỉ',
            'preorder' => 'Pre-order'
        ];
        return $labels[$type] ?? $type;
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title row
        $sheet->mergeCells('A1:Q1');
        
        // Style title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF6B35'], // Orange
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Style header row
        $sheet->getStyle('A3:Q3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'], // Dark blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '34495E'],
                ],
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Style data rows
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:Q' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Zebra striping
        for ($row = 4; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Style status column
        $statusColumn = 'P';
        $statusColors = [
            'Chờ xử lý' => 'F39C12',
            'Đang xử lý' => '3498DB',
            'Đang giao' => '9B59B6',
            'Hoàn thành' => '27AE60',
            'Đã hủy' => 'E74C3C',
            'Chờ xác nhận' => 'F39C12',
            'Đã duyệt' => '27AE60',
            'Đang sản xuất' => 'E67E22',
            'Đã xác nhận' => '3498DB',
            'Chờ hàng' => 'F1C40F',
        ];

        for ($row = 4; $row <= $lastRow; $row++) {
            $status = $sheet->getCell($statusColumn . $row)->getValue();
            if (isset($statusColors[$status])) {
                $sheet->getStyle($statusColumn . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => $statusColors[$status]],
                        'bold' => true,
                    ],
                ]);
            }
        }

        // Style total column
        $totalColumn = 'N';
        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle($totalColumn . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FF6B35'],
                ],
            ]);
        }

        // Style "Loại" column - in đậm
        $typeColumn = 'C';
        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle($typeColumn . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '2C3E50'],
                ],
            ]);
        }

        // Wrap text for Products and Address columns
        $sheet->getStyle('J4:J' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('H4:H' . $lastRow)->getAlignment()->setWrapText(true);

        // Auto-size columns
        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A4');

        // Add auto filter
        $sheet->setAutoFilter('A3:Q3');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 15,  // Mã đơn
            'C' => 12,  // Loại
            'D' => 20,  // Người đặt
            'E' => 15,  // SĐT đặt
            'F' => 20,  // Người nhận
            'G' => 15,  // SĐT nhận
            'H' => 35,  // Địa chỉ giao
            'I' => 18,  // Ngày đặt
            'J' => 50,  // Sản phẩm
            'K' => 15,  // Tạm tính
            'L' => 12,  // Phí ship
            'M' => 15,  // Giảm giá
            'N' => 15,  // Tổng cộng
            'O' => 15,  // Hình thức TT
            'P' => 15,  // Trạng thái
            'Q' => 30,  // Ghi chú
        ];
    }
}


## Assistant:
07/16/2026 11:52

> **Thinking:**
> We need to adapt the two Vue pages (Dashboard and Reports) to use real data from the database, and also implement Excel export for reports. The provided SQL dump gives us the database schema and some sample data.
> 
> We have two Vue components: one for Dashboard (first part) and one for Reports (second part). They are both using Inertia.js with Laravel backend.
> 
> We need to:
> 
> 1. Modify the Dashboard page to fetch real stats (today's revenue for retail, wholesale, preorder), recent orders, top products by each type, and show a chart with revenue by type over time (week/month). The current code uses static data.
> 
> 2. Modify the Reports page similarly: fetch real data for summary, top products, top customers, and the chart data. Also implement export to Excel.
> 
> 3. The provided Export class (OrdersExport) is for exporting orders, but for reports we might need a different export (e.g., revenue summary, top products). However, the Reports page has an "Export report" button - we need to decide what to export. Possibly export a summary report including the charts data, top products, etc. But the given OrdersExport is for orders list. Could be used for exporting order list filtered by type/period. The Reports page might have filters for period and type; we could export a detailed report (like orders or summary).
> 
> Given the context, we need to adjust both pages to use real data from the backend via Inertia. The current code uses static refs. We need to fetch data from controllers.
> 
> We'll need to define routes and controllers to serve the data. For example, an API endpoint or Inertia page props.
> 
> Since this is a Vue + Inertia app, we'll pass data from the controller when rendering the page. For dynamic updates (like changing period), we can use Inertia's router to fetch new data or use axios.
> 
> We'll keep the structure: Dashboard and Reports are separate pages, each with their own controller methods.
> 
> Let's plan:
> 
> - For Dashboard: In the controller method (e.g., `AdminController::dashboard`), we compute the required data:
>   - Stats: today's revenue for each type (retail, wholesale, preorder) and growth compared to yesterday.
>   - Recent orders: latest orders (maybe 5) with status, customer, type, amount, etc.
>   - Top products: for each type, top products by quantity sold (retail) or revenue? 
>   - Chart data: revenue per day for last 7 days or 4 weeks, grouped by type.
> 
> - For Reports: Similar but more comprehensive. The page has period filter (Tuần, Tháng, Năm) and likely type filter (retail, wholesale, preorder, all). We need to compute summary data, top products, top customers, and chart data based on selected filters. Also export functionality.
> 
> Now, the export: The OrdersExport class is for exporting orders. For the reports page, we might want to export a summary report, not just orders. But we could also export the list of orders that match the filters. In the given OrdersExport, it accepts $type and $orders, and can export orders. We can reuse that for exporting orders filtered by type and period. Or we can create a separate export for summary.
> 
> Given the requirement: "xuất file excel cho thống kê dựa trên mẫu tương tự dưới đây" and they provide OrdersExport, they might want to export orders list with the same style (with title, headers, styling). That seems reasonable. So when user clicks "Xuất báo cáo" on the Reports page, we export the orders that match the selected period and type (and maybe all if 'all').
> 
> Now, we need to implement the backend logic.
> 
> Since we don't have the actual Laravel project code, we'll write pseudo-code and suggestions for modifications.
> 
> We'll focus on the Vue components: how to fetch data, bind to refs, and handle export.
> 
> But we can provide concrete code changes for the Vue files:
> 
> - Remove static data.
> - Add props to receive data from the controller.
> - Use Inertia's `usePage` to access props.
> - For chart updates on period change, use Inertia.visit to reload page with new period parameter, or fetch via axios and update charts manually.
> - For export, use a button that triggers a download via a route that returns Excel file (using Maatwebsite Excel).
> 
> We'll also need to format currency properly.
> 
> Now let's provide a detailed answer: explain the required changes, code modifications, and the backend requirements.
> 
> Since the question is: "Đối với db này, tôi cần phải chỉnh sửa 2 page vue này như thế nào để thống kê được dữ liệu thật + xuất file excel cho thống kê dựa trên mẫu tương tự dưới đây?" (With this DB, I need to edit these 2 Vue pages to get real data statistics + export Excel file for statistics based on the similar template below). They show the OrdersExport class.
> 
> So we need to provide a step-by-step guide and code changes.
> 
> Let's craft a comprehensive answer:
> 
> 1. Backend preparation: Create controller methods to fetch data for dashboard and reports, pass as props.
> 2. Dashboard page: replace static data with props; implement chart update using axios or Inertia.
> 3. Reports page: similar.
> 4. Export functionality: add route to export orders using the provided Export class (or extended version).
> 5. Provide sample controller code.
> 
> We'll write the answer in Vietnamese to match the user's language.
> 
> Let's produce the answer.

Để thống kê dữ liệu thật và xuất Excel cho 2 trang **Dashboard** và **Báo cáo thống kê**, bạn cần thay đổi cả phía Backend (Controller) lẫn Frontend (Vue). Dưới đây là hướng dẫn chi tiết.

---

## 1. Chuẩn bị dữ liệu từ Database

### 1.1. Các bảng cần sử dụng
- `orders`: chứa thông tin đơn hàng, `order_code` phân loại (`retail`, `wholesale`, `preorder`).
- `order_details`: chi tiết sản phẩm, kết nối với `product_variants` và `products`.
- `payments`: phương thức thanh toán.
- `users`: khách hàng.

### 1.2. Quy ước
- `order_status`:  
  0 = Chờ xác nhận, 1 = Đang xử lý, 2 = Đang giao, 3 = Hoàn thành, 4 = Đã hủy.
- `order_code`: `retail`, `wholesale`, `preorder`.

---

## 2. Backend – Controller (Laravel)

### 2.1. Dashboard Controller
Tạo hoặc sửa method `dashboard()` trong `AdminController` để trả về dữ liệu:

```php
use App\Models\Order;
use App\Models\ProductVariant;
use Carbon\Carbon;

public function dashboard()
{
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();

    // Doanh thu theo loại hình hôm nay
    $stats = [
        'retail' => $this->getRevenueByType('retail', $today),
        'wholesale' => $this->getRevenueByType('wholesale', $today),
        'preorder' => $this->getRevenueByType('preorder', $today),
    ];

    // Tăng trưởng so với hôm qua
    $growth = [
        'retail' => $this->calcGrowth('retail', $today, $yesterday),
        'wholesale' => $this->calcGrowth('wholesale', $today, $yesterday),
        'preorder' => $this->calcGrowth('preorder', $today, $yesterday),
    ];

    // Đơn hàng gần đây (5 đơn)
    $recentOrders = Order::with('details.productVariant.product')
        ->latest()
        ->limit(5)
        ->get()
        ->map(function ($order) {
            return [
                'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'customer' => $order->customer_name ?? $order->receiver_name,
                'type' => $this->getTypeLabel($order->order_code),
                'amount' => number_format($order->final_amount, 0, ',', '.') . '₫',
                'status' => $this->getStatusLabel($order->order_status),
                'statusClass' => $this->getStatusClass($order->order_status),
            ];
        });

    // Top sản phẩm bán chạy theo từng loại (số lượng bán)
    $topRetail = $this->getTopProducts('retail', 3);
    $topWholesale = $this->getTopProducts('wholesale', 3);
    $topPreorder = $this->getTopProducts('preorder', 3);

    // Dữ liệu biểu đồ doanh thu theo tuần (7 ngày qua)
    $chartWeek = $this->getRevenueChartData('week');
    // Dữ liệu biểu đồ theo tháng (4 tuần qua)
    $chartMonth = $this->getRevenueChartData('month');

    return inertia('Admin/Dashboard', [
        'stats' => $stats,
        'growth' => $growth,
        'recentOrders' => $recentOrders,
        'topRetail' => $topRetail,
        'topWholesale' => $topWholesale,
        'topPreorder' => $topPreorder,
        'chartWeek' => $chartWeek,
        'chartMonth' => $chartMonth,
    ]);
}

// Helper methods
private function getRevenueByType($type, $date)
{
    return Order::where('order_code', $type)
        ->whereDate('created_at', $date)
        ->sum('final_amount');
}

private function calcGrowth($type, $today, $yesterday)
{
    $todayRevenue = $this->getRevenueByType($type, $today);
    $yesterdayRevenue = $this->getRevenueByType($type, $yesterday);
    if ($yesterdayRevenue == 0) return 0;
    return round(($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue * 100, 1);
}

private function getTopProducts($type, $limit)
{
    return Order::where('order_code', $type)
        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
        ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->select('products.name', \DB::raw('SUM(order_details.quantity) as sold'))
        ->groupBy('products.id', 'products.name')
        ->orderByDesc('sold')
        ->limit($limit)
        ->get();
}

private function getRevenueChartData($period)
{
    // Trả về mảng labels và dữ liệu retail, wholesale, preorder
    // Ví dụ cho week: 7 ngày gần nhất
    $labels = [];
    $retail = [];
    $wholesale = [];
    $preorder = [];
    if ($period == 'week') {
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D'); // Thứ
            $retail[] = $this->getRevenueByType('retail', $date);
            $wholesale[] = $this->getRevenueByType('wholesale', $date);
            $preorder[] = $this->getRevenueByType('preorder', $date);
        }
    } else { // month: 4 tuần
        for ($i = 3; $i >= 0; $i--) {
            $start = Carbon::today()->subWeeks($i)->startOfWeek();
            $end = Carbon::today()->subWeeks($i)->endOfWeek();
            $labels[] = 'Tuần ' . (4 - $i);
            $retail[] = Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            $wholesale[] = Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            $preorder[] = Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
        }
    }
    return ['labels' => $labels, 'retail' => $retail, 'wholesale' => $wholesale, 'preorder' => $preorder];
}

private function getTypeLabel($type) {
    $map = ['retail' => 'Bán lẻ', 'wholesale' => 'Bán sỉ', 'preorder' => 'Pre-order'];
    return $map[$type] ?? $type;
}

private function getStatusLabel($status) {
    $map = [0 => 'Chờ xác nhận', 1 => 'Đang xử lý', 2 => 'Đang giao', 3 => 'Hoàn thành', 4 => 'Đã hủy'];
    return $map[$status] ?? 'Không xác định';
}

private function getStatusClass($status) {
    $map = [
        0 => 'bg-yellow-100 text-yellow-700',
        1 => 'bg-blue-100 text-blue-700',
        2 => 'bg-purple-100 text-purple-700',
        3 => 'bg-green-100 text-green-700',
        4 => 'bg-red-100 text-red-700',
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-700';
}
```

### 2.2. Reports Controller
Tương tự, nhưng có thêm tham số `period` và `type` để lọc.

```php
public function reports(Request $request)
{
    $period = $request->input('period', 'week'); // week, month, year
    $type = $request->input('type', 'all'); // retail, wholesale, preorder, all

    $data = $this->getReportData($period, $type);

    return inertia('Admin/Reports', [
        'reportData' => $data,
        'currentPeriod' => $period,
        'currentType' => $type,
    ]);
}

private function getReportData($period, $type)
{
    // Tương tự dashboard nhưng có thể mở rộng thêm: top customers, top products theo doanh thu, phân phối danh mục, v.v.
    // Trả về summary, topProducts, topCustomers, chartData, v.v.
}
```

### 2.3. Export Excel
Tạo route xuất file, sử dụng `OrdersExport` (hoặc mở rộng).

```php
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportOrders(Request $request)
{
    $type = $request->input('type', 'all');
    $period = $request->input('period', 'week');

    // Lọc đơn hàng theo period và type
    $query = Order::query();
    if ($type !== 'all') {
        $query->where('order_code', $type);
    }
    // Lọc theo period
    if ($period == 'week') {
        $query->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()]);
    } elseif ($period == 'month') {
        $query->whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()]);
    } elseif ($period == 'year') {
        $query->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()]);
    }
    $orders = $query->latest()->get();

    return Excel::download(new OrdersExport($type, $orders), 'don_hang_' . $period . '.xlsx');
}
```

---

## 3. Frontend – Vue (Inertia)

### 3.1. Dashboard.vue

**Thay đổi chính:**
- Nhận props từ controller thay vì dùng static data.
- Sử dụng `usePage().props` để lấy dữ liệu.
- Khi chọn period (tuần/tháng), gọi API (hoặc Inertia.visit) để cập nhật biểu đồ.
- Định dạng số tiền.

**Code mẫu cho Dashboard.vue (phần script):**

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

// Props từ controller
const props = defineProps({
    stats: Object,
    growth: Object,
    recentOrders: Array,
    topRetail: Array,
    topWholesale: Array,
    topPreorder: Array,
    chartWeek: Object,
    chartMonth: Object,
});

const page = usePage();

const selectedPeriod = ref('week');
let revenueChart = null;

// Dữ liệu biểu đồ hiện tại
const chartData = ref({
    labels: [],
    retail: [],
    wholesale: [],
    preorder: [],
});

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Khởi tạo biểu đồ
const initChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();

    const data = selectedPeriod.value === 'week' ? props.chartWeek : props.chartMonth;
    chartData.value = data;

    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: data.retail, 
                    backgroundColor: '#f97316', 
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
                legend: { position: 'top' },
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
                }
            }
        }
    });
};

// Khi đổi period, gọi lại controller để lấy dữ liệu mới
const handlePeriodChange = () => {
    router.visit(route('admin.dashboard'), {
        method: 'get',
        data: { period: selectedPeriod.value },
        preserveScroll: true,
        onSuccess: () => {
            initChart();
        }
    });
};

onMounted(() => {
    setTimeout(initChart, 100);
});

// Nếu props thay đổi (do Inertia reload), cập nhật biểu đồ
watch(() => props, () => {
    initChart();
}, { deep: true });
</script>
```

**Phần template:** Giữ nguyên như cũ, nhưng thay vì sử dụng các biến static, dùng `props.stats`, `props.recentOrders`, v.v.

Ví dụ:
```vue
<!-- Stats Cards -->
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
    <p class="text-sm text-gray-500 font-medium">Bán lẻ hôm nay</p>
    <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(props.stats.retail) }}</p>
    <p class="text-xs text-green-600 mt-2">↑ +{{ props.growth.retail }}% so với hôm qua</p>
</div>
```

### 3.2. Reports.vue

Tương tự, nhận props `reportData`, `currentPeriod`, `currentType`.

Thêm chức năng xuất Excel:

```vue
<template>
    <!-- ... -->
    <button @click="exportReport" class="...">
        Xuất báo cáo
    </button>
    <!-- ... -->
</template>

<script>
const exportReport = () => {
    // Gửi request tới route export với các tham số filter hiện tại
    window.location.href = route('admin.reports.export', {
        period: selectedPeriod.value,
        type: selectedType.value,
    });
};
</script>
```

Hoặc dùng axios để tải file:

```javascript
import axios from 'axios';

const exportReport = async () => {
    try {
        const response = await axios.get(route('admin.reports.export'), {
            params: { period: selectedPeriod.value, type: selectedType.value },
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'bao_cao_thong_ke.xlsx');
        document.body.appendChild(link);
        link.click();
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Xuất báo cáo thất bại');
    }
};
```

---

## 4. Export Class – OrdersExport

Bạn đã có sẵn `OrdersExport` – có thể dùng nguyên hoặc mở rộng để thêm các trường cần thiết. Trong controller export, gọi:

```php
return Excel::download(new OrdersExport($type, $orders), 'don_hang.xlsx');
```

---

## 5. Tóm tắt các bước cần làm

1. **Tạo/Edition Controller** để trả dữ liệu thống kê cho 2 trang.
2. **Sửa Vue** để nhận dữ liệu từ props, thay thế static data.
3. **Thêm logic cập nhật biểu đồ** khi thay đổi period (gọi lại controller qua Inertia.visit hoặc axios).
4. **Thêm route export Excel** và gọi từ frontend.
5. **Kiểm tra** các truy vấn database để đảm bảo hiệu năng (có thể cache).

---

Với hướng dẫn trên, bạn sẽ có được dữ liệu thật từ database và xuất Excel chuẩn như yêu cầu. Nếu cần chi tiết từng file Vue hoặc Controller, hãy cung cấp thêm mã nguồn hiện tại để tôi có thể chỉnh sửa cụ thể hơn.


## User:
07/16/2026 11:58

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'todayRevenue' => 12500000,
                'totalOrders' => 284,
                'totalCustomers' => 156,
                'lowStockProducts' => 12,
            ]

        ]);
    }
}
Chỉnh sửa dashboard controller


## Assistant:
07/16/2026 11:58

> **Thinking:**
> Chúng ta cần chỉnh sửa DashboardController để lấy dữ liệu thực từ database, phù hợp với yêu cầu thống kê thực tế như đã mô tả trong các file Vue.
> 
> Tôi sẽ viết code cho DashboardController với các phương thức lấy dữ liệu thực từ các bảng: orders, order_details, product_variants, products, users, v.v. Đồng thời cung cấp dữ liệu cho biểu đồ, top sản phẩm, đơn hàng gần đây, v.v. như trong Dashboard.vue.
> 
> Cần đảm bảo dữ liệu trả về đúng cấu trúc mà Vue đang sử dụng (stats, recentOrders, topRetail, topWholesale, topPreorder, chartWeek, chartMonth). Ngoài ra, cần xử lý lọc theo period (tuần/tháng) có thể thông qua request.
> 
> Tôi sẽ viết toàn bộ nội dung file DashboardController.php với các helper methods bên trong.
> 
> Lưu ý: sử dụng Carbon, các model Order, ProductVariant, Product, v.v.
> 
> Tôi sẽ viết code hoàn chỉnh.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // 'week' or 'month'

        // Thống kê tổng quan
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Doanh thu theo loại hình hôm nay
        $todayRevenue = [
            'retail' => $this->getRevenueByType('retail', $today),
            'wholesale' => $this->getRevenueByType('wholesale', $today),
            'preorder' => $this->getRevenueByType('preorder', $today),
        ];

        // Tăng trưởng so với hôm qua
        $growth = [
            'retail' => $this->calcGrowth('retail', $today, $yesterday),
            'wholesale' => $this->calcGrowth('wholesale', $today, $yesterday),
            'preorder' => $this->calcGrowth('preorder', $today, $yesterday),
        ];

        // Tổng số đơn hàng, khách hàng, sản phẩm tồn kho thấp
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $lowStockProducts = ProductVariant::where('stock', '<', 10)->count();

        // Đơn hàng gần đây (5 đơn)
        $recentOrders = Order::with(['details.productVariant.product'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                    'customer' => $order->customer_name ?? $order->receiver_name,
                    'type' => $this->getTypeLabel($order->order_code),
                    'amount' => number_format($order->final_amount, 0, ',', '.') . '₫',
                    'status' => $this->getStatusLabel($order->order_status),
                    'statusClass' => $this->getStatusClass($order->order_status),
                ];
            });

        // Top sản phẩm bán chạy theo loại hình (số lượng bán)
        $topRetail = $this->getTopProducts('retail', 3);
        $topWholesale = $this->getTopProducts('wholesale', 3);
        $topPreorder = $this->getTopProducts('preorder', 3);

        // Dữ liệu biểu đồ theo period
        if ($period === 'week') {
            $chartData = $this->getRevenueChartData('week');
        } else {
            $chartData = $this->getRevenueChartData('month');
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'todayRevenue' => $todayRevenue,
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'lowStockProducts' => $lowStockProducts,
            ],
            'growth' => $growth,
            'recentOrders' => $recentOrders,
            'topRetail' => $topRetail,
            'topWholesale' => $topWholesale,
            'topPreorder' => $topPreorder,
            'chartWeek' => $period === 'week' ? $chartData : null,
            'chartMonth' => $period === 'month' ? $chartData : null,
            'currentPeriod' => $period,
        ]);
    }

    // ==================== Helper Methods ====================

    /**
     * Lấy tổng doanh thu (final_amount) theo loại hình và ngày
     */
    private function getRevenueByType($type, $date)
    {
        return Order::where('order_code', $type)
            ->whereDate('created_at', $date)
            ->sum('final_amount') ?: 0;
    }

    /**
     * Tính phần trăm tăng trưởng so với hôm qua
     */
    private function calcGrowth($type, $today, $yesterday)
    {
        $todayRevenue = $this->getRevenueByType($type, $today);
        $yesterdayRevenue = $this->getRevenueByType($type, $yesterday);

        if ($yesterdayRevenue == 0) {
            return $todayRevenue > 0 ? 100 : 0;
        }

        return round(($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue * 100, 1);
    }

    /**
     * Top sản phẩm bán chạy theo loại hình (dựa trên số lượng bán)
     */
    private function getTopProducts($type, $limit)
    {
        return Order::where('order_code', $type)
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(order_details.quantity) as sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                ];
            });
    }

    /**
     * Dữ liệu biểu đồ doanh thu theo tuần hoặc tháng
     */
    private function getRevenueChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = $this->getRevenueByType('retail', $date) / 1000000; // triệu
                $wholesale[] = $this->getRevenueByType('wholesale', $date) / 1000000;
                $preorder[] = $this->getRevenueByType('preorder', $date) / 1000000;
            }
        } else {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = Order::where('order_code', 'retail')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
                $wholesale[] = Order::where('order_code', 'wholesale')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
                $preorder[] = Order::where('order_code', 'preorder')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Chuyển đổi số thứ tự ngày trong tuần sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [
            0 => 'CN',
            1 => 'T2',
            2 => 'T3',
            3 => 'T4',
            4 => 'T5',
            5 => 'T6',
            6 => 'T7',
        ];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }

    /**
     * Nhãn cho loại đơn hàng
     */
    private function getTypeLabel($type)
    {
        $map = [
            'retail' => 'Bán lẻ',
            'wholesale' => 'Bán sỉ',
            'preorder' => 'Pre-order',
        ];
        return $map[$type] ?? $type;
    }

    /**
     * Nhãn trạng thái đơn hàng
     */
    private function getStatusLabel($status)
    {
        $map = [
            0 => 'Chờ xác nhận',
            1 => 'Đang xử lý',
            2 => 'Đang giao',
            3 => 'Hoàn thành',
            4 => 'Đã hủy',
        ];
        return $map[$status] ?? 'Không xác định';
    }

    /**
     * Class CSS cho trạng thái
     */
    private function getStatusClass($status)
    {
        $map = [
            0 => 'bg-yellow-100 text-yellow-700',
            1 => 'bg-blue-100 text-blue-700',
            2 => 'bg-purple-100 text-purple-700',
            3 => 'bg-green-100 text-green-700',
            4 => 'bg-red-100 text-red-700',
        ];
        return $map[$status] ?? 'bg-gray-100 text-gray-700';
    }
}
```


## User:
07/16/2026 12:01

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Period state
const selectedPeriod = ref('week');
let revenueChart = null;

// Recent orders data
const recentOrders = ref([
    { code: '#ORD-001', customer: 'Nguyễn Văn A', type: 'Bán lẻ', amount: '2.500.000₫', status: 'Hoàn thành', statusClass: 'bg-green-100 text-green-700' },
    { code: '#ORD-002', customer: 'Công ty ABC', type: 'Bán sỉ', amount: '18.500.000₫', status: 'Đang giao', statusClass: 'bg-blue-100 text-blue-700' },
    { code: '#ORD-003', customer: 'Trần Thị B', type: 'Pre-order', amount: '1.850.000₫', status: 'Chờ xác nhận', statusClass: 'bg-yellow-100 text-yellow-700' }
]);

// Top products
const topRetail = ref([
    { name: 'Balo Doanh Nhân Elite', sold: 145 },
    { name: 'Túi Du Lịch Nomad', sold: 98 },
    { name: 'Balo Công Sở Commuter', sold: 87 }
]);

const topWholesale = ref([
    { name: 'Balo Elite - Order số lượng lớn', quantity: 12 },
    { name: 'Túi Nomad - Doanh nghiệp', quantity: 8 },
    { name: 'Balo Tech Nova - Đối tác', quantity: 5 }
]);

const topPreorder = ref([
    { name: 'Balo Limited Edition 2025', preordered: 45 },
    { name: 'Túi Da Cao Cấp', preordered: 32 },
    { name: 'Set Balo + Ví', preordered: 28 }
]);

// Data cho các khoảng thời gian
const chartData = {
    week: {
        labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'],
        retail: [8, 10, 7, 12, 15, 18, 14],
        wholesale: [15, 18, 12, 22, 28, 35, 25],
        preorder: [5, 4, 6, 8, 10, 12, 7]
    },
    month: {
        labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
        retail: [45, 52, 48, 58],
        wholesale: [85, 92, 78, 98],
        preorder: [28, 32, 35, 42]
    }
};

// Hàm khởi tạo biểu đồ
const initChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();
    
    const data = chartData[selectedPeriod.value];
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: data.retail, 
                    backgroundColor: '#f97316', 
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
                            return context.dataset.label + ': ' + context.raw + ' triệu ₫';
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (triệu ₫)', font: { size: 11 } },
                    grid: { color: '#e5e7eb' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
};

// Xử lý khi đổi period
const handlePeriodChange = () => {
    initChart();
};

onMounted(() => {
    setTimeout(() => initChart(), 100);
});
</script>

<template>
    <Head title="Dashboard - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-5">
            <!-- Welcome -->
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800">Chào mừng trở lại, Admin</h1>
            </div>

            <!-- Stats Cards - 3 loại hình bán hàng -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <!-- Bán lẻ -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bán lẻ hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">8.250.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +12.5% so với hôm qua</p>
                    </div>
                </div>

                <!-- Bán sỉ -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bán sỉ hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">32.500.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +23.5% so với hôm qua</p>
                    </div>
                </div>

                <!-- Pre-order -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Pre-order hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">5.200.000₫</p>
                        <p class="text-xs text-green-600 mt-2">↑ +5.2% so với hôm qua</p>
                    </div>
                </div>
            </div>

            <!-- Charts & Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-800">Doanh thu theo loại hình</h3>
                        <select 
                            v-model="selectedPeriod" 
                            @change="handlePeriodChange"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-gray-400"
                        >
                            <option value="week">7 ngày qua</option>
                            <option value="month">4 tuần qua</option>
                        </select>
                    </div>
                    <canvas id="revenueByTypeChart" style="height: 280px; width: 100%;"></canvas>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-800">Đơn hàng gần đây</h3>
                        <Link :href="route('admin.orders.index')" class="text-sm text-orange-600 hover:underline">Xem tất cả →</Link>
                    </div>
                    <div class="space-y-2">
                        <div v-for="order in recentOrders" :key="order.code" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ order.code }}</p>
                                <p class="text-xs text-gray-400">{{ order.customer }} - {{ order.type }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="order.statusClass">{{ order.status }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ order.amount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top sản phẩm theo loại -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Top bán lẻ</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topRetail" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Top bán sỉ</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topWholesale" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.quantity }} đơn</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Pre-order nổi bật</h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topPreorder" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
                                <span class="text-sm text-gray-700">{{ p.name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ p.preordered }} đơn</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

Chỉnh lại dashboard để lấy dữ liệu thật cho các biểu đồ và thông số


## Assistant:
07/16/2026 12:01

> **Thinking:**
> Tôi sẽ chỉnh lại Dashboard.vue để sử dụng dữ liệu thật từ props mà controller đã truyền vào, thay vì dữ liệu giả. Đồng thời cập nhật logic khi thay đổi period (tuần/tháng) để gọi lại controller.
> 
> ```vue
> <script setup>
> import { ref, onMounted, watch } from 'vue';
> import AdminLayout from '@/Layouts/AdminLayout.vue';
> import { Head, Link, usePage, router } from '@inertiajs/vue3';
> 
> // Nhận props từ controller
> const props = defineProps({
>     stats: Object,          // { todayRevenue: { retail, wholesale, preorder }, totalOrders, totalCustomers, lowStockProducts }
>     growth: Object,         // { retail, wholesale, preorder }
>     recentOrders: Array,
>     topRetail: Array,
>     topWholesale: Array,
>     topPreorder: Array,
>     chartWeek: Object,      // { labels, retail, wholesale, preorder }
>     chartMonth: Object,     // { labels, retail, wholesale, preorder }
>     currentPeriod: String,  // 'week' or 'month'
> });
> 
> const page = usePage();
> 
> // Period state
> const selectedPeriod = ref(props.currentPeriod || 'week');
> let revenueChart = null;
> 
> // Format currency
> const formatPrice = (value) => {
>     if (!value && value !== 0) return '0₫';
>     return value.toLocaleString('vi-VN') + '₫';
> };
> 
> // Format growth with sign
> const formatGrowth = (value) => {
>     if (value > 0) return `↑ +${value}% so với hôm qua`;
>     if (value < 0) return `↓ ${value}% so với hôm qua`;
>     return '0% so với hôm qua';
> };
> 
> // Khởi tạo biểu đồ với dữ liệu từ props
> const initChart = () => {
>     const canvas = document.getElementById('revenueByTypeChart');
>     if (!canvas) return;
>     
>     const ctx = canvas.getContext('2d');
>     if (revenueChart) revenueChart.destroy();
>     
>     // Lấy dữ liệu tương ứng với period
>     const data = selectedPeriod.value === 'week' ? props.chartWeek : props.chartMonth;
>     
>     // Nếu không có dữ liệu, dùng mảng rỗng
>     const labels = data?.labels || [];
>     const retail = data?.retail || [];
>     const wholesale = data?.wholesale || [];
>     const preorder = data?.preorder || [];
>     
>     revenueChart = new Chart(ctx, {
>         type: 'bar',
>         data: {
>             labels: labels,
>             datasets: [
>                 { 
>                     label: 'Bán lẻ', 
>                     data: retail, 
>                     backgroundColor: '#f97316', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Bán sỉ', 
>                     data: wholesale, 
>                     backgroundColor: '#436651', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Pre-order', 
>                     data: preorder, 
>                     backgroundColor: '#f59e0b', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 }
>             ]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: { 
>                 legend: { 
>                     position: 'top',
>                     labels: { font: { size: 12 } }
>                 },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             return context.dataset.label + ': ' + context.raw.toFixed(1) + ' triệu ₫';
>                         }
>                     }
>                 }
>             },
>             scales: { 
>                 y: { 
>                     beginAtZero: true, 
>                     title: { display: true, text: 'Doanh thu (triệu ₫)', font: { size: 11 } },
>                     grid: { color: '#e5e7eb' }
>                 },
>                 x: { 
>                     grid: { display: false },
>                     ticks: { font: { size: 11 } }
>                 }
>             }
>         }
>     });
> };
> 
> // Xử lý khi đổi period: gọi lại controller để lấy dữ liệu mới
> const handlePeriodChange = () => {
>     router.visit(route('admin.dashboard'), {
>         method: 'get',
>         data: { period: selectedPeriod.value },
>         preserveScroll: true,
>         preserveState: true,
>         only: ['chartWeek', 'chartMonth', 'currentPeriod'], // Chỉ cập nhật các props liên quan đến chart
>         onSuccess: () => {
>             // Sau khi cập nhật props, khởi tạo lại biểu đồ
>             initChart();
>         }
>     });
> };
> 
> // Theo dõi props thay đổi (khi có Inertia reload)
> watch(() => [props.chartWeek, props.chartMonth, props.currentPeriod], () => {
>     // Cập nhật selectedPeriod nếu currentPeriod thay đổi
>     if (props.currentPeriod && props.currentPeriod !== selectedPeriod.value) {
>         selectedPeriod.value = props.currentPeriod;
>     }
>     initChart();
> }, { deep: true });
> 
> onMounted(() => {
>     setTimeout(() => initChart(), 100);
> });
> </script>
> 
> <template>
>     <Head title="Dashboard - BigBag Admin" />
>     
>     <AdminLayout>
>         <div class="p-5">
>             <!-- Welcome -->
>             <div class="mb-6">
>                 <h1 class="text-xl font-semibold text-gray-800">Chào mừng trở lại, Admin</h1>
>             </div>
> 
>             <!-- Stats Cards - 3 loại hình bán hàng -->
>             <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
>                 <!-- Bán lẻ -->
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <div>
>                         <p class="text-sm text-gray-500 font-medium">Bán lẻ hôm nay</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.retail) }}</p>
>                         <p class="text-xs" :class="growth.retail >= 0 ? 'text-green-600' : 'text-red-600'">
>                             {{ formatGrowth(growth.retail) }}
>                         </p>
>                     </div>
>                 </div>
> 
>                 <!-- Bán sỉ -->
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <div>
>                         <p class="text-sm text-gray-500 font-medium">Bán sỉ hôm nay</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.wholesale) }}</p>
>                         <p class="text-xs" :class="growth.wholesale >= 0 ? 'text-green-600' : 'text-red-600'">
>                             {{ formatGrowth(growth.wholesale) }}
>                         </p>
>                     </div>
>                 </div>
> 
>                 <!-- Pre-order -->
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <div>
>                         <p class="text-sm text-gray-500 font-medium">Pre-order hôm nay</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.preorder) }}</p>
>                         <p class="text-xs" :class="growth.preorder >= 0 ? 'text-green-600' : 'text-red-600'">
>                             {{ formatGrowth(growth.preorder) }}
>                         </p>
>                     </div>
>                 </div>
>             </div>
> 
>             <!-- Thêm các thông số tổng quan (tùy chọn) -->
>             <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <p class="text-sm text-gray-500 font-medium">Tổng đơn hàng</p>
>                     <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.totalOrders }}</p>
>                 </div>
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <p class="text-sm text-gray-500 font-medium">Tổng khách hàng</p>
>                     <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.totalCustomers }}</p>
>                 </div>
>                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
>                     <p class="text-sm text-gray-500 font-medium">Sản phẩm tồn kho thấp</p>
>                     <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.lowStockProducts }}</p>
>                 </div>
>             </div>
> 
>             <!-- Charts & Recent Orders -->
>             <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
>                 <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                     <div class="flex justify-between items-center mb-4">
>                         <h3 class="font-semibold text-gray-800">Doanh thu theo loại hình</h3>
>                         <select 
>                             v-model="selectedPeriod" 
>                             @change="handlePeriodChange"
>                             class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-gray-400"
>                         >
>                             <option value="week">7 ngày qua</option>
>                             <option value="month">4 tuần qua</option>
>                         </select>
>                     </div>
>                     <canvas id="revenueByTypeChart" style="height: 280px; width: 100%;"></canvas>
>                 </div>
>                 
>                 <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                     <div class="flex justify-between items-center mb-4">
>                         <h3 class="font-semibold text-gray-800">Đơn hàng gần đây</h3>
>                         <Link :href="route('admin.orders.index')" class="text-sm text-orange-600 hover:underline">Xem tất cả →</Link>
>                     </div>
>                     <div class="space-y-2">
>                         <div v-for="order in recentOrders" :key="order.code" class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
>                             <div>
>                                 <p class="text-sm font-medium text-gray-800">{{ order.code }}</p>
>                                 <p class="text-xs text-gray-400">{{ order.customer }} - {{ order.type }}</p>
>                             </div>
>                             <span class="text-xs px-2 py-1 rounded-full" :class="order.statusClass">{{ order.status }}</span>
>                             <span class="text-sm font-semibold text-gray-800">{{ order.amount }}</span>
>                         </div>
>                     </div>
>                 </div>
>             </div>
> 
>             <!-- Top sản phẩm theo loại -->
>             <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
>                 <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                     <h3 class="font-semibold text-gray-800 mb-3">Top bán lẻ</h3>
>                     <div class="space-y-2">
>                         <div v-for="(p, idx) in topRetail" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
>                             <div class="flex items-center gap-2">
>                                 <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
>                                 <span class="text-sm text-gray-700">{{ p.name }}</span>
>                             </div>
>                             <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
>                         </div>
>                     </div>
>                 </div>
>                 
>                 <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                     <h3 class="font-semibold text-gray-800 mb-3">Top bán sỉ</h3>
>                     <div class="space-y-2">
>                         <div v-for="(p, idx) in topWholesale" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
>                             <div class="flex items-center gap-2">
>                                 <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
>                                 <span class="text-sm text-gray-700">{{ p.name }}</span>
>                             </div>
>                             <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
>                         </div>
>                     </div>
>                 </div>
>                 
>                 <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                     <h3 class="font-semibold text-gray-800 mb-3">Pre-order nổi bật</h3>
>                     <div class="space-y-2">
>                         <div v-for="(p, idx) in topPreorder" :key="idx" class="flex justify-between items-center py-2 border-b border-gray-100">
>                             <div class="flex items-center gap-2">
>                                 <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-medium">{{ idx+1 }}</span>
>                                 <span class="text-sm text-gray-700">{{ p.name }}</span>
>                             </div>
>                             <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
>                         </div>
>                     </div>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> ```
> 
> **Giải thích các thay đổi:**
> 
> 1. **Nhận props từ controller**: 
>    - `stats`, `growth`, `recentOrders`, `topRetail`, `topWholesale`, `topPreorder`, `chartWeek`, `chartMonth`, `currentPeriod`.
> 
> 2. **Loại bỏ dữ liệu giả**: Xóa các `ref` static và dùng dữ liệu từ props.
> 
> 3. **Format tiền và tăng trưởng**: 
>    - `formatPrice` hiển thị số tiền có dấu phân cách.
>    - `formatGrowth` hiển thị dấu và phần trăm.
> 
> 4. **Khởi tạo biểu đồ với dữ liệu thật**: 
>    - Lấy dữ liệu từ `chartWeek` hoặc `chartMonth` dựa vào `selectedPeriod`.
>    - Nếu không có dữ liệu, dùng mảng rỗng để tránh lỗi.
> 
> 5. **Xử lý đổi period**: 
>    - Gọi `router.visit()` tới route `admin.dashboard` với tham số `period`.
>    - Chỉ cập nhật các props liên quan đến biểu đồ (`only`).
>    - Sau khi thành công, gọi lại `initChart()`.
> 
> 6. **Theo dõi props thay đổi**: Sử dụng `watch` để cập nhật biểu đồ khi có dữ liệu mới từ Inertia.
> 
> 7. **Template**: 
>    - Sử dụng `stats.todayRevenue.retail`, `growth.retail`, v.v. để hiển thị số liệu.
>    - Thêm các thẻ `totalOrders`, `totalCustomers`, `lowStockProducts` (nếu có).
>    - Đảm bảo `topWholesale` và `topPreorder` sử dụng `sold` thay vì `quantity`/`preordered` để đồng nhất (bạn có thể điều chỉnh trong controller).
> 
> Với các thay đổi này, Dashboard sẽ hiển thị dữ liệu thật từ database và có thể tương tác với period.


## User:
07/16/2026 12:12

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@bigbag.vn',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer One',
                'email' => 'customer1@example.com',
                'phone' => '0912345678',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Two',
                'email' => 'customer2@example.com',
                'phone' => '0923456789',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'My',
                'email' => 'admin@gmail.com',
                'phone' => '0923456785',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thanh Phuong Khuu',
                'email' => 'thanhphuongkhuu@gmail.com',
                'phone' => '0123456789',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],


        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']], // điều kiện tìm
                $user                       // dữ liệu insert hoặc update
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    public function run()
    {

        $user = DB::table('users')->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Default User',
                'email' => 'user@bigbag.vn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }


        $discount = DB::table('discounts')->where('order_code', 'retail')->first();
        $discountId = $discount ? $discount->id : null;

        DB::table('orders')->insert([
            [
                'user_id' => $userId,
                'customer_name' => 'Nguyễn Văn A',         // Người đặt
                'customer_phone' => '0901234567',
                'discount_id' => $discountId,
                'campaign_id' => null,
                'order_code' => 'retail',
                'receiver_name' => 'Trần Thị B',           // Người nhận (có thể khác)
                'receiver_phone' => '0912345678',
                'shipping_fee' => 30000,
                'total_amount' => 1450000,
                'discount_amount' => 0,
                'final_amount' => 1480000,
                'order_status' => 2,
                'shipping_address' => '123 Đường Láng, Đống Đa, Hà Nội',
                'note' => 'Gọi trước khi giao',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'customer_name' => 'Công ty TNHH ABC',    // Người đặt
                'customer_phone' => '0987654321',
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => 'wholesale',
                'receiver_name' => 'Nguyễn Văn C',          // Người nhận (có thể khác)
                'receiver_phone' => '0978123456',
                'shipping_fee' => 0,
                'total_amount' => 2100000,
                'discount_amount' => 0,
                'final_amount' => 2100000,
                'order_status' => 1,
                'shipping_address' => '456 Nguyễn Trãi, Quận 1, TP.HCM',
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'customer_name' => 'Trần Thị B',           // Người đặt (có thể trùng với người nhận ở đơn khác)
                'customer_phone' => '0912345678',
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => 'preorder',
                'receiver_name' => 'Lê Văn D',              // Người nhận
                'receiver_phone' => '0934567890',
                'shipping_fee' => 0,
                'total_amount' => 3500000,
                'discount_amount' => 0,
                'final_amount' => 3500000,
                'order_status' => 0,
                'shipping_address' => '789 Lê Lợi, Quận 3, TP.HCM',
                'note' => 'Giao hàng sau ngày 15/06',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    public function run()
    {
        $orders = DB::table('orders')->get();
        $productVariants = DB::table('product_variants')->get();

        if ($orders->isEmpty() || $productVariants->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {

            $numProducts = rand(1, 3);
            $usedVariants = [];

            for ($i = 0; $i < $numProducts; $i++) {
                $variant = $productVariants->random();

                while (in_array($variant->id, $usedVariants) && $productVariants->count() > $usedVariants) {
                    $variant = $productVariants->random();
                }
                $usedVariants[] = $variant->id;

                $quantity = rand(1, 3);
                $unitPrice = $variant->price;
                $subtotal = $unitPrice * $quantity;

                DB::table('order_details')->insert([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

Cập nhật seeder để test xem dashboard đã hiển thị đúng hay chưa?


## Assistant:
07/16/2026 12:12

> **Thinking:**
> Chúng ta cần cập nhật seeder để tạo dữ liệu đa dạng hơn, đặc biệt là các đơn hàng với các ngày khác nhau (trong 7 ngày qua, 4 tuần qua) để dashboard hiển thị số liệu thực tế.
> 
> Hiện tại OrderSeeder chỉ tạo 3 đơn hàng với created_at là now(), nên biểu đồ sẽ chỉ có dữ liệu 1 ngày. Để test dashboard, cần tạo nhiều đơn hàng với ngày khác nhau.
> 
> Ngoài ra, các seeder hiện tại không tạo dữ liệu cho bảng payments, reviews, v.v. nhưng dashboard không cần. Chỉ cần orders và order_details là đủ.
> 
> Tôi sẽ cập nhật OrderSeeder để tạo nhiều đơn hàng với ngày tháng phân bố trong 7 ngày qua và 4 tuần qua, với các loại order_code khác nhau.
> 
> Đồng thời, cần đảm bảo product_variants và products đã có dữ liệu (từ seeder khác). Có vẻ như bạn đã có các seeder cho categories, brands, products, colors, product_variants từ file SQL dump. Nên chỉ cần cập nhật OrderSeeder và OrderDetailSeeder.
> 
> Dưới đây là code cập nhật cho OrderSeeder và OrderDetailSeeder, cùng với việc thêm seeder cho payments (nếu cần) và reviews (không cần thiết). 
> 
> Tôi sẽ viết lại OrderSeeder để tạo khoảng 30-50 đơn hàng với ngày tháng ngẫu nhiên trong 30 ngày qua, với đủ 3 loại. Đảm bảo có dữ liệu cho biểu đồ.
> 
> Ngoài ra, cần tạo thêm một số đơn hàng cũ hơn để kiểm tra bộ lọc tháng? Nhưng dashboard chỉ hiển thị 7 ngày hoặc 4 tuần, nên cần đủ dữ liệu trong khoảng đó.
> 
> Cập nhật OrderSeeder:
> 
> - Sử dụng vòng lặp để tạo khoảng 50 đơn hàng.
> - Mỗi đơn hàng có order_code ngẫu nhiên (retail, wholesale, preorder) với tỉ lệ nhất định.
> - created_at random trong 30 ngày qua.
> - total_amount và final_amount ngẫu nhiên dựa trên sản phẩm.
> 
> Cũng cần cập nhật OrderDetailSeeder để tạo chi tiết cho từng đơn hàng với sản phẩm ngẫu nhiên.
> 
> Cuối cùng, thêm seeder Payments để có dữ liệu thanh toán (không bắt buộc).
> 
> Tôi sẽ viết code chi tiết.

Để kiểm tra dashboard với dữ liệu thực, bạn cần **cập nhật các Seeder** để tạo dữ liệu đa dạng về ngày tháng, loại đơn hàng và sản phẩm. Dưới đây là các Seeder đã được chỉnh sửa.

---

## 1. Cập nhật `OrderSeeder`

Tạo nhiều đơn hàng với `created_at` ngẫu nhiên trong 30 ngày qua, đủ 3 loại hình.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Lấy user đầu tiên (hoặc tạo mới nếu chưa có)
        $user = DB::table('users')->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Default User',
                'email' => 'user@bigbag.vn',
                'password' => bcrypt('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

        // Lấy danh sách sản phẩm (để tính tổng tiền sau)
        $variants = DB::table('product_variants')->get();

        // Danh sách khách hàng mẫu
        $customers = [
            ['name' => 'Nguyễn Văn A', 'phone' => '0901234567'],
            ['name' => 'Công ty TNHH ABC', 'phone' => '0987654321'],
            ['name' => 'Trần Thị B', 'phone' => '0912345678'],
            ['name' => 'Lê Văn C', 'phone' => '0934567890'],
            ['name' => 'Phạm Thị D', 'phone' => '0945678901'],
            ['name' => 'Doanh nghiệp XYZ', 'phone' => '0956789012'],
            ['name' => 'Ngô Văn E', 'phone' => '0967890123'],
            ['name' => 'Công ty TNHH Tech', 'phone' => '0978901234'],
        ];

        $orderCodes = ['retail', 'wholesale', 'preorder'];
        $statuses = [0, 1, 2, 3, 4];

        // Tạo 50 đơn hàng với ngày tháng ngẫu nhiên trong 30 ngày qua
        for ($i = 0; $i < 50; $i++) {
            // Chọn ngẫu nhiên ngày trong 30 ngày qua
            $createdAt = Carbon::now()->subDays(rand(0, 30))->setTime(rand(8, 22), rand(0, 59), rand(0, 59));

            // Chọn loại đơn hàng (ưu tiên retail nhiều hơn)
            $orderCode = $orderCodes[array_rand($orderCodes)];
            // Điều chỉnh tỉ lệ: retail 50%, wholesale 30%, preorder 20%
            $rand = rand(1, 10);
            if ($rand <= 5) $orderCode = 'retail';
            elseif ($rand <= 8) $orderCode = 'wholesale';
            else $orderCode = 'preorder';

            // Chọn khách hàng ngẫu nhiên
            $customer = $customers[array_rand($customers)];
            $receiver = $customers[array_rand($customers)];

            // Tạo đơn hàng (sau đó sẽ cập nhật total_amount và final_amount)
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderCode,
                'receiver_name' => $receiver['name'],
                'receiver_phone' => $receiver['phone'],
                'shipping_fee' => $orderCode == 'retail' ? rand(20000, 50000) : 0,
                'total_amount' => 0, // sẽ tính sau
                'discount_amount' => 0,
                'final_amount' => 0,  // sẽ tính sau
                'order_status' => $statuses[array_rand($statuses)],
                'shipping_address' => 'Địa chỉ ' . rand(1, 100) . ', Quận ' . rand(1, 12) . ', TP.HCM',
                'note' => rand(0, 1) ? 'Ghi chú đơn hàng ' . $i : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Tạo chi tiết đơn hàng (gọi riêng hoặc để OrderDetailSeeder xử lý)
            // Ở đây ta sẽ tạo trực tiếp để có thể tính total ngay
            $numItems = rand(1, 3);
            $total = 0;
            $usedVariantIds = [];
            for ($j = 0; $j < $numItems; $j++) {
                // Chọn variant ngẫu nhiên chưa dùng
                $variant = $variants->filter(function ($v) use ($usedVariantIds) {
                    return !in_array($v->id, $usedVariantIds);
                })->random();
                $usedVariantIds[] = $variant->id;

                $quantity = rand(1, 5);
                $unitPrice = $variant->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                DB::table('order_details')->insert([
                    'order_id' => $orderId,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // Cập nhật total và final cho đơn hàng
            $shippingFee = DB::table('orders')->where('id', $orderId)->value('shipping_fee');
            $final = $total + $shippingFee;
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'total_amount' => $total,
                    'final_amount' => $final,
                ]);
        }
    }
}
```

---

## 2. Cập nhật `OrderDetailSeeder` (không cần thiết nếu đã tạo ở trên, nhưng giữ lại để chạy riêng)

Nếu bạn muốn tách biệt, bạn có thể bỏ qua `OrderDetailSeeder` vì đã tạo trực tiếp trong `OrderSeeder`. Tuy nhiên, nếu vẫn muốn giữ, hãy xóa logic tạo detail trong `OrderSeeder` và để `OrderDetailSeeder` chạy sau. Nhưng để đơn giản, tôi khuyên bạn gộp vào `OrderSeeder` như trên.

---

## 3. Thêm `PaymentSeeder` (tùy chọn)

Nếu dashboard không hiển thị thanh toán, bạn có thể bỏ qua. Nhưng nếu có, hãy thêm seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $orders = DB::table('orders')->get();
        $methods = ['cod', 'bank_transfer', 'momo', 'vnpay'];
        $statuses = ['pending', 'success', 'failed'];

        foreach ($orders as $order) {
            if (rand(0, 1)) { // 50% số đơn có thanh toán
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'transaction_code' => 'TXN' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'payment_method' => $methods[array_rand($methods)],
                    'amount' => $order->final_amount,
                    'payment_date' => $order->created_at,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
        }
    }
}
```

---

## 4. Cập nhật `DatabaseSeeder` để gọi các seeder mới

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ColorSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            OrderSeeder::class,        // đã bao gồm order_details
            // PaymentSeeder::class,   // nếu có
            // ReviewSeeder::class,    // không cần cho dashboard
        ]);
    }
}
```

---

## 5. Chạy Seeder

```bash
php artisan db:seed
```

Hoặc nếu muốn xóa dữ liệu cũ và chạy lại:

```bash
php artisan migrate:fresh --seed
```

---

## 6. Kiểm tra Dashboard

Sau khi seed, bạn sẽ có khoảng 50 đơn hàng với ngày tháng trải dài 30 ngày. Dashboard sẽ hiển thị:

- **3 thẻ thống kê**: doanh thu hôm nay của từng loại (sẽ có dữ liệu nếu có đơn hàng trong ngày).
- **Biểu đồ**: 7 ngày hoặc 4 tuần sẽ hiển thị cột doanh thu.
- **Đơn hàng gần đây**: 5 đơn mới nhất.
- **Top sản phẩm**: bán chạy theo từng loại (dựa trên số lượng bán).

---

## 7. Lưu ý

- Nếu bạn muốn có dữ liệu đa dạng hơn, hãy tăng số lượng đơn hàng (ví dụ 100-200).
- Đảm bảo bảng `product_variants` đã có dữ liệu (bạn đã có từ file SQL).
- Nếu dashboard yêu cầu dữ liệu từ bảng `discounts` hay `campaigns`, bạn có thể thêm vào seeder tương ứng.

Sau khi chạy seeder, dashboard sẽ hiển thị số liệu thực tế và bạn có thể kiểm tra biểu đồ.


## User:
07/16/2026 12:55

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

Cập nhật lại page report


## Assistant:
07/16/2026 12:55

> **Thinking:**
> Chúng ta cần cập nhật page Report để lấy dữ liệu thực từ controller. Hiện tại, page đang dùng dữ liệu giả (revenueData, summaryData, topProducts, topCustomers). Chúng ta sẽ sửa để nhận dữ liệu từ props truyền từ controller, và khi thay đổi period, gọi lại controller để lấy dữ liệu mới.
> 
> Trước hết, cần tạo controller cho Reports (hoặc sửa DashboardController để phục vụ cả hai). Nhưng theo yêu cầu, bạn đã có DashboardController, nhưng page Report có các thông tin khác (top sản phẩm theo doanh thu, top khách hàng). Vì vậy, nên tạo riêng ReportController hoặc thêm method trong AdminController.
> 
> Tôi sẽ hướng dẫn chỉnh sửa page Report để nhận props và gọi API.
> 
> Đầu tiên, cần định nghĩa props cho component. Trong Inertia, bạn có thể nhận props từ controller. Ví dụ:
> 
> ```php
> // ReportController.php
> public function index(Request $request)
> {
>     $period = $request->input('period', 'week'); // week, month, year
>     $data = $this->getReportData($period);
>     return Inertia::render('Admin/Reports', [
>         'reportData' => $data,
>         'currentPeriod' => $period,
>     ]);
> }
> ```
> 
> Sau đó, trong Vue, bạn nhận props `reportData` và `currentPeriod`.
> 
> Dưới đây là code cập nhật cho Reports.vue:
> 
> ```vue
> <script setup>
> import { ref, onMounted, watch } from 'vue';
> import AdminLayout from '@/Layouts/AdminLayout.vue';
> import { Head, router, usePage } from '@inertiajs/vue3';
> 
> // Props từ controller
> const props = defineProps({
>     reportData: Object,
>     currentPeriod: String,
> });
> 
> // Period filter
> const period = ref(props.currentPeriod || 'week');
> const periods = ['week', 'month', 'year'];
> const isLoading = ref(false);
> 
> // Chart instances
> let revenueChart = null;
> let categoryChart = null;
> 
> // Data from props
> const summaryData = ref(props.reportData?.summary || {
>     retail: { revenue: 0, growth: 0 },
>     wholesale: { revenue: 0, growth: 0 },
>     preorder: { revenue: 0, growth: 0 }
> });
> 
> const topProducts = ref(props.reportData?.topProducts || []);
> const topCustomers = ref(props.reportData?.topCustomers || []);
> const chartData = ref(props.reportData?.chartData || {
>     labels: [],
>     retail: [],
>     wholesale: [],
>     preorder: []
> });
> 
> // Format currency
> const formatPrice = (value) => {
>     if (!value) return '0₫';
>     return value.toLocaleString('vi-VN') + '₫';
> };
> 
> // Initialize revenue chart
> const initRevenueChart = () => {
>     const canvas = document.getElementById('revenueByTypeChart');
>     if (!canvas) return;
>     
>     const ctx = canvas.getContext('2d');
>     if (revenueChart) revenueChart.destroy();
>     
>     const data = chartData.value;
>     revenueChart = new Chart(ctx, {
>         type: 'bar',
>         data: {
>             labels: data.labels,
>             datasets: [
>                 { 
>                     label: 'Bán lẻ', 
>                     data: data.retail, 
>                     backgroundColor: '#ff6b00', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Bán sỉ', 
>                     data: data.wholesale, 
>                     backgroundColor: '#436651', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Pre-order', 
>                     data: data.preorder, 
>                     backgroundColor: '#f59e0b', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 }
>             ]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { 
>                     position: 'top',
>                     labels: { font: { family: 'Montserrat', size: 12 } }
>                 },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             let label = context.dataset.label || '';
>                             let value = context.raw;
>                             return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
>                         }
>                     }
>                 }
>             },
>             scales: {
>                 y: { 
>                     beginAtZero: true, 
>                     title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
>                     grid: { color: '#e5e7eb' }
>                 },
>                 x: { 
>                     grid: { display: false },
>                     ticks: { font: { family: 'Montserrat' } }
>                 }
>             }
>         }
>     });
> };
> 
> // Initialize category chart (có thể lấy từ props nếu có)
> const initCategoryChart = () => {
>     const canvas = document.getElementById('categoryChart');
>     if (!canvas) return;
>     
>     const ctx = canvas.getContext('2d');
>     if (categoryChart) categoryChart.destroy();
>     
>     const categoryData = props.reportData?.categoryDistribution || [58, 28, 14];
>     const categoryLabels = props.reportData?.categoryLabels || ['Balo', 'Cặp - Túi', 'Phụ kiện'];
>     
>     categoryChart = new Chart(ctx, {
>         type: 'doughnut',
>         data: {
>             labels: categoryLabels,
>             datasets: [{
>                 data: categoryData,
>                 backgroundColor: ['#ff6b00', '#436651', '#89726c'],
>                 borderWidth: 0,
>                 hoverOffset: 10
>             }]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { 
>                     position: 'bottom',
>                     labels: { font: { family: 'Montserrat', size: 12 } }
>                 },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             let label = context.label || '';
>                             let value = context.raw;
>                             return `${label}: ${value}%`;
>                         }
>                     }
>                 }
>             }
>         }
>     });
> };
> 
> // Load data by period
> const loadDataByPeriod = async () => {
>     isLoading.value = true;
>     try {
>         await router.get('/admin/reports', { period: period.value }, {
>             preserveScroll: true,
>             preserveState: true,
>             onSuccess: (page) => {
>                 // Dữ liệu mới sẽ được cập nhật qua props
>                 // Vì Inertia sẽ reload component với props mới
>                 // Ta cần cập nhật các ref từ props mới
>                 const newData = page.props.reportData;
>                 if (newData) {
>                     summaryData.value = newData.summary || summaryData.value;
>                     topProducts.value = newData.topProducts || [];
>                     topCustomers.value = newData.topCustomers || [];
>                     chartData.value = newData.chartData || chartData.value;
>                     // Cập nhật biểu đồ
>                     if (revenueChart) {
>                         revenueChart.data.labels = chartData.value.labels;
>                         revenueChart.data.datasets[0].data = chartData.value.retail;
>                         revenueChart.data.datasets[1].data = chartData.value.wholesale;
>                         revenueChart.data.datasets[2].data = chartData.value.preorder;
>                         revenueChart.update();
>                     }
>                     if (categoryChart && newData.categoryDistribution) {
>                         categoryChart.data.datasets[0].data = newData.categoryDistribution;
>                         categoryChart.update();
>                     }
>                 }
>             },
>             onError: () => {
>                 console.error('Lỗi tải dữ liệu');
>             }
>         });
>     } catch (error) {
>         console.error('Lỗi tải dữ liệu:', error);
>     } finally {
>         isLoading.value = false;
>     }
> };
> 
> // Export report
> const exportReport = async () => {
>     try {
>         // Tải file Excel trực tiếp qua route
>         window.location.href = route('admin.reports.export', { period: period.value });
>     } catch (error) {
>         console.error('Lỗi xuất báo cáo:', error);
>         alert('Có lỗi xảy ra khi xuất báo cáo');
>     }
> };
> 
> // Watch for period change
> watch(period, () => {
>     loadDataByPeriod();
> });
> 
> // Initialize on mount
> onMounted(() => {
>     setTimeout(() => {
>         initRevenueChart();
>         initCategoryChart();
>     }, 100);
> });
> </script>
> 
> <template>
>     <Head title="Báo cáo thống kê - BigBag Admin" />
>     
>     <AdminLayout>
>         <div class="p-4 md:p-8">
>             <!-- Header -->
>             <div class="mb-6">
>                 <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Báo cáo thống kê</h1>
>             </div>
> 
>             <!-- Period Filter -->
>             <div class="flex flex-wrap items-center gap-2 mb-6">
>                 <button 
>                     v-for="p in periods" 
>                     :key="p" 
>                     @click="period = p" 
>                     class="px-4 py-2 rounded-lg text-sm transition-all"
>                     :class="period === p ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
>                 >
>                     {{ p === 'week' ? 'Tuần' : p === 'month' ? 'Tháng' : 'Năm' }}
>                 </button>
>                 <button 
>                     @click="exportReport" 
>                     class="ml-auto bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm flex items-center gap-1 text-gray-600 hover:bg-gray-50 transition-colors"
>                 >
>                     <span class="material-symbols-outlined text-sm">download</span>
>                     Xuất báo cáo
>                 </button>
>             </div>
> 
>             <!-- Loading indicator -->
>             <div v-if="isLoading" class="text-center py-8">
>                 <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
>                 <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
>             </div>
> 
>             <div v-else>
>                 <!-- Summary Cards -->
>                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
>                     <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
>                         <p class="text-sm text-gray-500 flex items-center gap-1">Bán lẻ</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.retail.revenue) }}</p>
>                         <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
>                             <span class="material-symbols-outlined text-sm">trending_up</span>
>                             +{{ summaryData.retail.growth }}%
>                         </p>
>                     </div>
>                     
>                     <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
>                         <p class="text-sm text-gray-500 flex items-center gap-1">Bán sỉ</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.wholesale.revenue) }}</p>
>                         <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
>                             <span class="material-symbols-outlined text-sm">trending_up</span>
>                             +{{ summaryData.wholesale.growth }}%
>                         </p>
>                     </div>
>                     
>                     <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
>                         <p class="text-sm text-gray-500 flex items-center gap-1">Pre-order</p>
>                         <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.preorder.revenue) }}</p>
>                         <p class="text-xs text-yellow-600 mt-1 flex items-center gap-1">
>                             <span class="material-symbols-outlined text-sm">trending_up</span>
>                             +{{ summaryData.preorder.growth }}%
>                         </p>
>                     </div>
>                 </div>
> 
>                 <!-- Charts -->
>                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
>                     <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                         <h3 class="font-semibold text-gray-800 mb-4">Doanh thu theo loại hình</h3>
>                         <canvas id="revenueByTypeChart" height="250"></canvas>
>                     </div>
>                     <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                         <h3 class="font-semibold text-gray-800 mb-4">Phân bố danh mục</h3>
>                         <canvas id="categoryChart" height="250"></canvas>
>                     </div>
>                 </div>
> 
>                 <!-- Top Products & Customers -->
>                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
>                     <!-- Top Products -->
>                     <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                         <div class="flex justify-between items-center mb-4">
>                             <h3 class="font-semibold text-gray-800">Top sản phẩm bán chạy</h3>
>                             <span class="text-xs text-gray-500">Theo doanh thu</span>
>                         </div>
>                         <div class="space-y-3">
>                             <div 
>                                 v-for="(product, idx) in topProducts" 
>                                 :key="idx" 
>                                 class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg transition-colors"
>                             >
>                                 <div class="flex items-center gap-2">
>                                     <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
>                                     <span class="text-gray-700">{{ product.name }}</span>
>                                 </div>
>                                 <div class="text-right">
>                                     <span class="font-semibold text-orange-600">{{ formatPrice(product.revenue) }}</span>
>                                     <span class="text-xs text-gray-400 ml-2">({{ product.sold }} cái)</span>
>                                 </div>
>                             </div>
>                         </div>
>                         <div class="mt-4 pt-3 border-t border-gray-100">
>                             <Link :href="route('admin.products.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">
>                                 Xem tất cả sản phẩm
>                                 <span class="material-symbols-outlined text-sm">arrow_forward</span>
>                             </Link>
>                         </div>
>                     </div>
> 
>                     <!-- Top Customers -->
>                     <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                         <div class="flex justify-between items-center mb-4">
>                             <h3 class="font-semibold text-gray-800">Top khách hàng thân thiết</h3>
>                             <span class="text-xs text-gray-500">Theo tổng chi tiêu</span>
>                         </div>
>                         <div class="space-y-3">
>                             <div 
>                                 v-for="(customer, idx) in topCustomers" 
>                                 :key="idx" 
>                                 class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg transition-colors"
>                             >
>                                 <div class="flex items-center gap-2">
>                                     <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
>                                     <div>
>                                         <span class="text-gray-700">{{ customer.name }}</span>
>                                         <span class="text-xs text-gray-400 ml-2">({{ customer.orders || 0 }} đơn)</span>
>                                     </div>
>                                 </div>
>                                 <div class="font-semibold text-orange-600">{{ formatPrice(customer.total) }}</div>
>                             </div>
>                         </div>
>                         <div class="mt-4 pt-3 border-t border-gray-100">
>                             <Link :href="route('admin.customers.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">
>                                 Xem tất cả khách hàng
>                                 <span class="material-symbols-outlined text-sm">arrow_forward</span>
>                             </Link>
>                         </div>
>                     </div>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> 
> <style scoped>
> @keyframes spin {
>     from {
>         transform: rotate(0deg);
>     }
>     to {
>         transform: rotate(360deg);
>     }
> }
> 
> .animate-spin {
>     animation: spin 1s linear infinite;
> }
> </style>
> ```
> 
> Lưu ý: Bạn cần có route `admin.reports.export` để xuất Excel. Đồng thời, controller phải trả về đúng cấu trúc dữ liệu như `reportData` bao gồm `summary`, `topProducts`, `topCustomers`, `chartData`, `categoryDistribution`.
> 
> Nếu bạn chưa có controller cho Reports, hãy tạo mới và implement các method lấy dữ liệu. Bạn có thể sử dụng các helper đã viết trong DashboardController để tái sử dụng.

Tôi sẽ hướng dẫn bạn chỉnh sửa **Report page** để lấy dữ liệu thực từ database, đồng thời tích hợp xuất Excel. Dưới đây là file Vue hoàn chỉnh với các thay đổi cần thiết.

---

## File `Reports.vue` (cập nhật)

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

// Props từ controller
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

// Dữ liệu từ props
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

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryData = props.reportData?.categoryDistribution || [0, 0, 0];
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
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

// Tải dữ liệu theo period
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        await router.get('/admin/reports', { period: period.value }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                const newData = page.props.reportData;
                if (newData) {
                    summaryData.value = newData.summary || summaryData.value;
                    topProducts.value = newData.topProducts || [];
                    topCustomers.value = newData.topCustomers || [];
                    chartData.value = newData.chartData || chartData.value;
                    
                    // Cập nhật biểu đồ doanh thu
                    if (revenueChart) {
                        revenueChart.data.labels = chartData.value.labels;
                        revenueChart.data.datasets[0].data = chartData.value.retail;
                        revenueChart.data.datasets[1].data = chartData.value.wholesale;
                        revenueChart.data.datasets[2].data = chartData.value.preorder;
                        revenueChart.update();
                    }
                    
                    // Cập nhật biểu đồ danh mục nếu có
                    if (categoryChart && newData.categoryDistribution) {
                        categoryChart.data.datasets[0].data = newData.categoryDistribution;
                        categoryChart.update();
                    }
                }
            },
            onError: () => {
                console.error('Lỗi tải dữ liệu');
            }
        });
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        // Dùng window.location để tải file trực tiếp
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
```

---

## Các bước cần làm ở Backend

1. **Tạo Controller cho Reports** (ví dụ `ReportController`) với 2 method: `index()` (hiển thị trang) và `export()` (xuất Excel).

2. **Định nghĩa route** trong `web.php`:

```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
});
```

3. **Triển khai logic lấy dữ liệu** tương tự như đã làm trong `DashboardController`, nhưng mở rộng thêm:
   - `summary` (doanh thu & tăng trưởng của 3 loại)
   - `topProducts` (top 5 theo doanh thu)
   - `topCustomers` (top 5 theo tổng chi tiêu)
   - `chartData` (labels và dữ liệu biểu đồ theo period)
   - `categoryDistribution` (phần trăm doanh thu theo danh mục)

4. **Xuất Excel**: sử dụng package `maatwebsite/excel` và class `OrdersExport` (bạn đã có) hoặc tạo một export riêng cho báo cáo tổng hợp.

---

Với những thay đổi trên, Report page sẽ hiển thị dữ liệu thực từ database và cho phép xuất Excel theo period đã chọn. Bạn có thể tùy chỉnh thêm các chỉ số khác nếu cần.


## User:
07/16/2026 13:06

<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController as WebCategoryController;
use App\Http\Controllers\ProductController as WebProductController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ReviewController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

// ==================== ROUTE ĐỂ PHỤC VỤ ẢNH ====================
Route::get('/image/{filename}', function ($filename) {
    $path = base_path('image/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
})->where('filename', '.*');

// ==================== WEB ROUTES (Frontend - Public) ====================

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tim-kiem', function (Request $request) {
    return Inertia::render('Web/Category', ['search' => $request->get('q')]);
})->name('search');

// Product routes - PUBLIC
Route::get('/san-pham/{slug}', [WebProductController::class, 'show'])->name('product.detail');
Route::get('/danh-muc/{slug}', [WebCategoryController::class, 'show'])->name('category');

// Other public frontend routes
Route::get('/mua-si', function () {
    return Inertia::render('Web/Wholesale');
})->name('wholesale');

// Promotion route - Sử dụng PromotionController (Web)
Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Web/Customize');
})->name('customize');

// ==================== LỊCH SỬ ĐƠN HÀNG ROUTES (Yêu cầu đăng nhập) ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/lich-su-don-hang', [OrderHistoryController::class, 'index'])->name('orders.history');
    Route::get('/lich-su-don-hang/data', [OrderHistoryController::class, 'getOrders'])->name('orders.history.data');
});

// ==================== CART ROUTES ====================
Route::get('/gio-hang', function (Request $request) {
    $voucherCode = $request->session()->get('voucher_code', null);
    $voucherDiscount = $request->session()->get('voucher_discount', 0);
    
    return Inertia::render('Web/Cart', [
        'voucher_code' => $voucherCode,
        'voucher_discount' => $voucherDiscount,
    ]);
})->name('cart')->middleware('auth');

Route::prefix('api')->group(function () {
    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{variantId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);
    Route::get('/cart/get-coupon', [CartController::class, 'getCoupon']);
    Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon']); // <-- ROUTE NÀY

    Route::post('/campaigns/clear-cache', function() {
        Cache::forget('active_campaigns_with_configs');
        return response()->json(['success' => true]);
    });
    
    Route::post('/pre-order/session', function (Request $request) {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        session([
            'pre_order_checkout' => true,
            'pre_order_variant_id' => $request->variant_id,
            'pre_order_quantity' => $request->quantity,
        ]);
        session()->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Pre-order session saved'
        ]);
    });
});

// ==================== CHECKOUT ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/thanh-toan', [PaymentController::class, 'index'])->name('checkout');
    Route::post('/thanh-toan', [PaymentController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/thanh-cong', [PaymentController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/apply-voucher', [PaymentController::class, 'applyVoucher']);
    Route::post('/checkout/remove-voucher', [PaymentController::class, 'removeVoucher']);
});

// ==================== AUTHENTICATED WEB ROUTES ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/don-hang', [OrderController::class, 'history'])->name('orders.history.old');
    Route::get('/don-hang/{id}', [OrderController::class, 'show'])->name('orders.show');
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    
    Route::prefix('orders')->group(function () {
        Route::get('/{type?}', [AdminOrderController::class, 'index'])
            ->where('type', 'retail|wholesale|preorder')
            ->name('orders.index');
        Route::get('/{id}', [AdminOrderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('orders.show');
        Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('orders.export');
        Route::get('/export/filtered', [AdminOrderController::class, 'exportWithFilters'])->name('orders.export-filtered');
    });
    
    Route::prefix('products')->group(function () {
        Route::get('/{type?}', [AdminProductController::class, 'index'])
            ->where('type', 'normal|preorder')
            ->name('products.index');
        Route::post('/', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    });
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('/colors/data', [ColorController::class, 'getColors'])->name('colors.data');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::put('/colors/{id}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{id}', [ColorController::class, 'destroy'])->name('colors.destroy');
        
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/data', [BrandController::class, 'getBrands'])->name('brands.data');
        Route::post('/', [BrandController::class, 'store'])->name('brands.store');
        Route::put('/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::get('/search', [BrandController::class, 'search'])->name('brands.search');
    });
    
    Route::prefix('customers')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/retail', [AdminCustomerController::class, 'retail'])->name('customers.retail');
        Route::get('/business', [AdminCustomerController::class, 'business'])->name('customers.business');
        Route::get('/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::put('/{id}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::post('/export', [AdminCustomerController::class, 'export'])->name('customers.export');
    });
    
    Route::prefix('customize')->group(function () {
        Route::get('/', [AdminCustomizeController::class, 'index'])->name('customize.index');
        Route::put('/{id}/status', [AdminCustomizeController::class, 'updateStatus'])->name('customize.update-status');
        Route::put('/{id}/approve', [AdminCustomizeController::class, 'approve'])->name('customize.approve');
        Route::post('/send-quote', [AdminCustomizeController::class, 'sendQuote'])->name('customize.send-quote');
    });

    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('news.index');
        Route::get('/data', [NewsController::class, 'getNews'])->name('news.data');
        Route::post('/', [NewsController::class, 'store'])->name('news.store');
        Route::put('/{id}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
        Route::patch('/{id}/status', [NewsController::class, 'updateStatus'])->name('news.update-status');
    });

    Route::prefix('reviews')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::get('/banners/campaigns', [BannerController::class, 'getCampaigns'])->name('banners.campaigns');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/banners/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.status');
    Route::patch('/banners/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.order');
    Route::post('/banners/check-status', [BannerController::class, 'checkAndUpdateStatus'])->name('banners.check-status');

    Route::prefix('promotions')->group(function () {
        Route::get('/', [AdminPromotionController::class, 'index'])->name('promotions.index');
        
        Route::post('/campaign', [AdminPromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
        Route::put('/campaign/{id}', [AdminPromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
        Route::delete('/campaign/{id}', [AdminPromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
        Route::put('/campaign/{id}/status', [AdminPromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');
        Route::get('/campaigns/list', [AdminPromotionController::class, 'getCampaignsList'])->name('promotions.campaigns.list');

        Route::post('/voucher', [AdminPromotionController::class, 'storeVoucher'])->name('promotions.voucher.store');
        Route::put('/voucher/{id}', [AdminPromotionController::class, 'updateVoucher'])->name('promotions.voucher.update');
        Route::delete('/voucher/{id}', [AdminPromotionController::class, 'deleteVoucher'])->name('promotions.voucher.delete');
        Route::put('/voucher/{id}/toggle', [AdminPromotionController::class, 'toggleVoucher'])->name('promotions.voucher.toggle');

        Route::post('/preorder', [AdminPromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
        Route::put('/preorder/{id}', [AdminPromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
        Route::delete('/preorder/{id}', [AdminPromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
        Route::put('/preorder/{id}/toggle', [AdminPromotionController::class, 'togglePreorder'])->name('promotions.preorder.toggle');
        
        Route::post('/discount', [AdminPromotionController::class, 'storeDiscount'])->name('admin.promotions.discount.store');
        Route::put('/discount/{id}', [AdminPromotionController::class, 'updateDiscount'])->name('admin.promotions.discount.update');
        Route::delete('/discount/{id}', [AdminPromotionController::class, 'deleteDiscount'])->name('admin.promotions.discount.delete');
        Route::put('/discount/{id}/toggle', [AdminPromotionController::class, 'toggleDiscount'])->name('admin.promotions.discount.toggle');

        Route::post('/preorder/{id}/increment-buyers', [AdminPromotionController::class, 'incrementPreorderBuyers'])
            ->name('promotions.preorder.increment');

        Route::post('/check', [AdminPromotionController::class, 'checkPromotion'])->name('promotions.check');
        Route::get('/preorder-info', [AdminPromotionController::class, 'getPreorderInfo'])->name('promotions.preorder.info');
    });


    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('reports.index');
    
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.updateGeneral');
    Route::put('/settings/password', [SettingController::class, 'changePassword'])->name('settings.changePassword');

    Route::get('/settings/users', [SettingController::class, 'getUsers'])->name('settings.users');
    Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.storeUser');
    Route::put('/settings/users/{id}', [SettingController::class, 'updateUser'])->name('settings.updateUser');
    Route::delete('/settings/users/{id}', [SettingController::class, 'destroyUser'])->name('settings.destroyUser');
    Route::patch('/settings/users/{id}/toggle', [SettingController::class, 'toggleUserStatus'])->name('settings.toggleUser');
});

Route::get('/media/{path}', function ($path) {
    $fullPath = base_path('media/' . $path);
    if (!File::exists($fullPath)) {
        abort(404);
    }
    $mime = File::mimeType($fullPath);
    return Response::file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*');

// Review
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('auth');

require __DIR__.'/auth.php';
Điều chỉnh lại route để sử dụng controller. Đồng thời, cập nhật lại controller
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reports');
    }
    
    public function getData()
    {

        $data = [
            'revenue' => [
                'retail' => [8, 10, 12, 15, 18, 22, 20],
                'wholesale' => [15, 18, 22, 28, 35, 42, 38],
                'preorder' => [3, 4, 5, 7, 9, 12, 10]
            ],
            'category' => [58, 28, 14],
            'summary' => [
                'retail' => ['revenue' => 45200000, 'growth' => 12.5],
                'wholesale' => ['revenue' => 128500000, 'growth' => 23.8],
                'preorder' => ['revenue' => 18300000, 'growth' => 5.2]
            ],
            'topProducts' => [
                ['name' => 'Balo Doanh Nhân Elite', 'sold' => 145, 'revenue' => 464000000],
                ['name' => 'Túi Du Lịch Nomad', 'sold' => 98, 'revenue' => 181300000],
                ['name' => 'Balo Công Sở Commuter', 'sold' => 87, 'revenue' => 138330000]
            ],
            'topCustomers' => [
                ['name' => 'Công ty ABC', 'total' => 156000000, 'orders' => 8],
                ['name' => 'Nguyễn Văn A', 'total' => 28500000, 'orders' => 12],
                ['name' => 'TechPro', 'total' => 45200000, 'orders' => 5]
            ]
        ];
        
        return response()->json($data);
    }
    
    public function export()
    {

        return back()->with('success', 'Xuất báo cáo thành công!');
    }
}


## Assistant:
07/16/2026 13:06

> **Thinking:**
> Chúng ta cần điều chỉnh route và controller để phù hợp với logic đã có. Dựa trên file route và ReportController hiện tại, cần tích hợp các method lấy dữ liệu thực từ database. Đồng thời, route cần gọi đúng controller.
> 
> Trước hết, cần cập nhật route cho reports:
> 
> - Route GET /admin/reports hiện tại đang dùng closure trả về Inertia render. Cần chuyển sang dùng ReportController@index.
> - Thêm route GET /admin/reports/data để lấy dữ liệu JSON cho biểu đồ (nếu cần, nhưng trong vue đã dùng router.get('/admin/reports', ...) với preserveState).
> - Thêm route GET /admin/reports/export để xuất Excel.
> 
> Trong route hiện tại có:
> 
> ```php
> Route::get('/reports', function () {
>     return Inertia::render('Admin/Reports');
> })->name('reports.index');
> ```
> 
> Cần thay bằng:
> 
> ```php
> Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
> Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');
> Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
> ```
> 
> Đồng thời cập nhật ReportController với các method lấy dữ liệu thực, tương tự như DashboardController nhưng có thêm lọc theo period, top sản phẩm, khách hàng, phân bố danh mục.
> 
> Cũng cần đảm bảo Vue trong Report.vue gọi đúng route (hiện tại đang gọi '/admin/reports/data' và '/admin/reports/export' - nên sửa lại cho khớp).
> 
> Tôi sẽ đưa ra code cập nhật cho route và controller.
> 
> Hãy xem xét route hiện tại: trong code của Vue có gọi:
> 
> ```js
> await router.get('/admin/reports', { period: period.value }, {...})
> ```
> 
> Và export:
> 
> ```js
> window.location.href = route('admin.reports.export', { period: period.value });
> ```
> 
> Vậy cần đặt tên route là 'admin.reports' cho index, và 'admin.reports.export' cho export.
> 
> Tuy nhiên, trong route hiện tại có `name('reports.index')`, nên Vue có thể dùng `route('admin.reports')` hay không? Tên route đang là 'admin.reports.index'? Thực tế trong route group `admin` có prefix và name `admin.`, nên route hiện tại có tên là `admin.reports.index`. Nếu Vue gọi `route('admin.reports')` sẽ không tồn tại. Cần sửa lại trong Vue hoặc route.
> 
> Tôi sẽ chỉnh Vue để dùng tên route chuẩn: `route('admin.reports.index')` và `route('admin.reports.export')`.
> 
> Hoặc có thể thêm alias. Để đơn giản, tôi sẽ điều chỉnh route để có tên 'admin.reports' cho index và 'admin.reports.export' cho export.
> 
> Đề xuất:
> 
> ```php
> Route::get('/reports', [ReportController::class, 'index'])->name('reports');
> Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');
> Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
> ```
> 
> Trong Vue, sử dụng `route('admin.reports')` (vì group name là admin), `route('admin.reports.export')`.
> 
> Tôi sẽ cập nhật route và controller hoàn chỉnh.
> 
> Bây giờ, tôi sẽ viết câu trả lời với các thay đổi cụ thể.

Chúng ta sẽ điều chỉnh **route** và **ReportController** để lấy dữ liệu thực từ database và hỗ trợ xuất Excel theo đúng cấu trúc đã thiết kế trong Vue.

---

## 1. Cập nhật route (trong `web.php`)

Thay đoạn route hiện tại:

```php
Route::get('/reports', function () {
    return Inertia::render('Admin/Reports');
})->name('reports.index');
```

Thành:

```php
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports');
    Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
```

Lưu ý: Vì nằm trong group `admin` có prefix `/admin` và name `admin.`, nên các route sẽ có tên đầy đủ:
- `admin.reports` (cho trang chính)
- `admin.reports.data` (cho API lấy dữ liệu JSON)
- `admin.reports.export` (cho xuất Excel)

---

## 2. Cập nhật `ReportController`

Thay thế toàn bộ nội dung `ReportController` bằng code dưới đây:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year

        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * API trả về dữ liệu dạng JSON (cho Vue gọi khi đổi period)
     */
    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

    /**
     * Xuất báo cáo Excel
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'week');

        // Lọc đơn hàng theo period
        $query = Order::query();
        $startDate = $this->getStartDate($period);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $orders = $query->latest()->get();

        // Sử dụng Export class có sẵn (hoặc tạo mới)
        return Excel::download(new OrdersExport('all', $orders), 'bao_cao_thong_ke_' . $period . '.xlsx');
    }

    // ==================== Phương thức lấy dữ liệu ====================

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // 1. Tổng quan doanh thu và tăng trưởng theo loại hình
        $summary = $this->getSummary($period);

        // 2. Dữ liệu biểu đồ doanh thu
        $chartData = $this->getChartData($period);

        // 3. Top sản phẩm bán chạy (theo doanh thu)
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);

        // 4. Top khách hàng (theo tổng chi tiêu)
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);

        // 5. Phân bố danh mục (tính theo % doanh thu)
        $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

        return [
            'summary' => $summary,
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categoryDistribution' => $categoryDistribution,
        ];
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }

    // ==================== Các helper chi tiết ====================

    /**
     * Tổng doanh thu và tăng trưởng của 3 loại hình
     */
    private function getSummary($period)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Nếu period là week, month, year thì có thể lấy doanh thu trong kỳ thay vì hôm nay
        // Ở đây ta vẫn lấy hôm nay để hiển thị trên thẻ
        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueToday = Order::where('order_code', $type)
                ->whereDate('created_at', $today)
                ->sum('final_amount');

            $revenueYesterday = Order::where('order_code', $type)
                ->whereDate('created_at', $yesterday)
                ->sum('final_amount');

            $growth = 0;
            if ($revenueYesterday > 0) {
                $growth = round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 1);
            } elseif ($revenueToday > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueToday,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    /**
     * Dữ liệu biểu đồ cột doanh thu theo tuần/tháng/năm
     */
    private function getChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } else { // year
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Top sản phẩm theo doanh thu
     */
    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                \DB::raw('SUM(order_details.quantity) as sold'),
                \DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                    'revenue' => (int) $item->revenue,
                ];
            });
    }

    /**
     * Top khách hàng theo tổng chi tiêu
     */
    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.customer_name as name',
                \DB::raw('COUNT(orders.id) as orders'),
                \DB::raw('SUM(orders.final_amount) as total')
            )
            ->groupBy('orders.customer_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');

        if ($total == 0) {
            return [0, 0, 0];
        }

        // Lấy doanh thu theo từng danh mục (giả sử có 3 danh mục chính)
        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->get();

        // Map thành mảng 3 phần tử tương ứng: Balo, Cặp - Túi, Phụ kiện (có thể tùy chỉnh)
        // Giả sử bạn có danh mục id: 1=balo, 2=túi, 3=phụ kiện...
        // Ở đây làm đơn giản: lấy 3 danh mục có doanh thu cao nhất
        $top3 = $categories->sortByDesc('revenue')->take(3)->values();

        // Nếu ít hơn 3, bổ sung 0
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = isset($top3[$i]) ? round($top3[$i]->revenue / $total * 100) : 0;
        }

        return $result;
    }

    /**
     * Chuyển đổi số thứ tự ngày sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}
```

---

## 3. Cập nhật `Report.vue` (đã có sẵn từ trước)

Bạn đã có file Vue đúng. Chỉ cần đảm bảo rằng các route được gọi đúng tên:

- `route('admin.reports')` cho trang chính (đã có)
- `route('admin.reports.data')` cho API lấy dữ liệu (trong Vue hiện tại đang gọi `/admin/reports/data` – hãy đổi thành `route('admin.reports.data')` nếu cần)
- `route('admin.reports.export')` cho xuất Excel (hiện tại Vue dùng `route('admin.reports.export', { period: period.value })` – đúng nếu đặt tên như trên)

Trong Vue, tại `loadDataByPeriod` bạn đang gọi `router.get('/admin/reports/data', ...)`. Hãy thay bằng:

```js
await router.get(route('admin.reports.data'), { period: period.value }, { ... })
```

Và `exportReport`:

```js
window.location.href = route('admin.reports.export', { period: period.value });
```

Nếu chưa có helper `route` trong Vue, bạn đã import `route` từ `@inertiajs/vue3` rồi.

---

## 4. Kiểm tra

- Chạy seeder để có dữ liệu (đã cập nhật ở bước trước).
- Truy cập `/admin/reports` – sẽ thấy dữ liệu thực từ database.
- Đổi period, biểu đồ và thẻ tổng quan sẽ thay đổi theo.
- Nhấn "Xuất báo cáo" để tải file Excel.

---

**Lưu ý:** Nếu bạn muốn xuất file Excel với định dạng đẹp như mẫu, hãy sử dụng class `OrdersExport` đã có. Nếu cần thêm chỉ số khác (doanh thu theo loại, top sản phẩm...), bạn có thể tạo một Export riêng cho báo cáo tổng hợp.

Mọi thắc mắc, bạn có thể hỏi thêm.


## User:
07/16/2026 13:44

<App>
warn$1 @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1751
logError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1938
handleError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1932
renderComponentRoot @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4724
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
mountChildren @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5399
processFragment @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5483
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5284
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
render @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5919
mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4516
app.mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:8520
setup @ app.js:25
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:9394
Promise.then
createInertiaApp @ @inertiajs_vue3.js?v=456e2a13:9378
(anonymous) @ app.js:13
index.esm.js:1 Uncaught (in promise) Error: Ziggy error: route 'admin.reports.index' is not in the route list.
    at new r (index.esm.js:1:14309)
    at B (index.esm.js:1:20932)
    at Proxy.n (index.esm.js:1:21049)
    at Proxy._sfc_render (AdminLayout.vue:187:34)
    at renderComponentRoot (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4707:35)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426:16)
    at setupRenderEffect (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628:3)
    at mountComponent (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512:10)
    at processComponent (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492:8)
r @ index.esm.js:1
B @ index.esm.js:1
(anonymous) @ index.esm.js:1
_sfc_render @ AdminLayout.vue:187
renderComponentRoot @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4707
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
mountChildren @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5399
processFragment @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5483
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5284
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
render @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5919
mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4516
app.mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:8520
setup @ app.js:25
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:9394
Promise.then
createInertiaApp @ @inertiajs_vue3.js?v=456e2a13:9378
(anonymous) @ app.js:13
app.js:25 [Vue warn]: Unhandled error during execution of render function 
  at <AdminLayout> 
  at <Reports errors= {} auth= {user: {…}} categories= (5) [{…}, {…}, {…}, {…}, {…}]  ... > 
  at <Inertia initialPage= {component: 'Admin/Reports', props: {…}, url: '/admin/reports', version: 'c25421ec1f078dbcaca38c8d5cd4e63a', sharedProps: Array(4)} initialComponent= {__name: 'Reports', props: {…}, __hmrId: '88e2f324', setup: ƒ, render: ƒ, …} resolveComponent=fn<resolveComponent>  ... > 
  at <App>
warn$1 @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1751
logError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1938
handleError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1932
renderComponentRoot @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4724
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
mountChildren @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5399
processFragment @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5483
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5284
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
runIfDirty @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:453
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1888
flushJobs @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:2037
Promise.then
queueFlush @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1979
queueJob @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1975
effect.scheduler @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5624
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:446
endBatch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:496
notify @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:711
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:703
set value @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1360
swapComponent @ @inertiajs_vue3.js?v=456e2a13:9311
doSwap @ @inertiajs_vue3.js?v=456e2a13:5842
swap @ @inertiajs_vue3.js?v=456e2a13:5847
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5776
Promise.then
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5763
Promise.then
set @ @inertiajs_vue3.js?v=456e2a13:5752
handleDefault @ @inertiajs_vue3.js?v=456e2a13:6254
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:6204
handle @ @inertiajs_vue3.js?v=456e2a13:6204
init @ @inertiajs_vue3.js?v=456e2a13:6970
setup @ @inertiajs_vue3.js?v=456e2a13:9306
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1888
setupStatefulComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6734
setupComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6710
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5502
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
render @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5919
mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4516
app.mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:8520
setup @ app.js:25
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:9394
Promise.then
createInertiaApp @ @inertiajs_vue3.js?v=456e2a13:9378
(anonymous) @ app.js:13
app.js:25 [Vue warn]: Unhandled error during execution of component update 
  at <Inertia initialPage= {component: 'Admin/Reports', props: {…}, url: '/admin/reports', version: 'c25421ec1f078dbcaca38c8d5cd4e63a', sharedProps: Array(4)} initialComponent= {__name: 'Reports', props: {…}, __hmrId: '88e2f324', setup: ƒ, render: ƒ, …} resolveComponent=fn<resolveComponent>  ... > 
  at <App>
warn$1 @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1751
logError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1938
handleError @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1932
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1890
flushJobs @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:2037
Promise.then
queueFlush @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1979
queueJob @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1975
effect.scheduler @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5624
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:446
endBatch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:496
notify @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:711
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:703
set value @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1360
swapComponent @ @inertiajs_vue3.js?v=456e2a13:9311
doSwap @ @inertiajs_vue3.js?v=456e2a13:5842
swap @ @inertiajs_vue3.js?v=456e2a13:5847
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5776
Promise.then
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5763
Promise.then
set @ @inertiajs_vue3.js?v=456e2a13:5752
handleDefault @ @inertiajs_vue3.js?v=456e2a13:6254
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:6204
handle @ @inertiajs_vue3.js?v=456e2a13:6204
init @ @inertiajs_vue3.js?v=456e2a13:6970
setup @ @inertiajs_vue3.js?v=456e2a13:9306
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1888
setupStatefulComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6734
setupComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6710
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5502
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
render @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5919
mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4516
app.mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:8520
setup @ app.js:25
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:9394
Promise.then
createInertiaApp @ @inertiajs_vue3.js?v=456e2a13:9378
(anonymous) @ app.js:13
index.esm.js:1 Uncaught (in promise) Error: Ziggy error: route 'admin.reports.index' is not in the route list.
    at new r (index.esm.js:1:14309)
    at B (index.esm.js:1:20932)
    at Proxy.n (index.esm.js:1:21049)
    at Proxy._sfc_render (AdminLayout.vue:187:34)
    at renderComponentRoot (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4707:35)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426:16)
    at setupRenderEffect (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628:3)
    at mountComponent (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512:10)
    at processComponent (vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492:8)
r @ index.esm.js:1
B @ index.esm.js:1
(anonymous) @ index.esm.js:1
_sfc_render @ AdminLayout.vue:187
renderComponentRoot @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4707
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5557
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
mountChildren @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5399
processFragment @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5483
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5284
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
runIfDirty @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:453
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1888
flushJobs @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:2037
Promise.then
queueFlush @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1979
queueJob @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1975
effect.scheduler @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5624
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:446
endBatch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:496
notify @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:711
trigger @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:703
set value @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1360
swapComponent @ @inertiajs_vue3.js?v=456e2a13:9311
doSwap @ @inertiajs_vue3.js?v=456e2a13:5842
swap @ @inertiajs_vue3.js?v=456e2a13:5847
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5776
Promise.then
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:5763
Promise.then
set @ @inertiajs_vue3.js?v=456e2a13:5752
handleDefault @ @inertiajs_vue3.js?v=456e2a13:6254
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:6204
handle @ @inertiajs_vue3.js?v=456e2a13:6204
init @ @inertiajs_vue3.js?v=456e2a13:6970
setup @ @inertiajs_vue3.js?v=456e2a13:9306
callWithErrorHandling @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:1888
setupStatefulComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6734
setupComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:6710
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5502
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
componentUpdateFn @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5560
run @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:426
setupRenderEffect @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5628
mountComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5512
processComponent @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5492
patch @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5287
render @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:5919
mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:4516
app.mount @ vue.runtime.esm-bundler-CJPQuahF.js?v=456e2a13:8520
setup @ app.js:25
(anonymous) @ @inertiajs_vue3.js?v=456e2a13:9394
Promise.then
createInertiaApp @ @inertiajs_vue3.js?v=456e2a13:9378
(anonymous) @ app.js:13
<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController as WebCategoryController;
use App\Http\Controllers\ProductController as WebProductController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ReviewController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

// ==================== ROUTE ĐỂ PHỤC VỤ ẢNH ====================
Route::get('/image/{filename}', function ($filename) {
    $path = base_path('image/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
})->where('filename', '.*');

// ==================== WEB ROUTES (Frontend - Public) ====================

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tim-kiem', function (Request $request) {
    return Inertia::render('Web/Category', ['search' => $request->get('q')]);
})->name('search');

// Product routes - PUBLIC
Route::get('/san-pham/{slug}', [WebProductController::class, 'show'])->name('product.detail');
Route::get('/danh-muc/{slug}', [WebCategoryController::class, 'show'])->name('category');

// Other public frontend routes
Route::get('/mua-si', function () {
    return Inertia::render('Web/Wholesale');
})->name('wholesale');

// Promotion route - Sử dụng PromotionController (Web)
Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Web/Customize');
})->name('customize');

// ==================== LỊCH SỬ ĐƠN HÀNG ROUTES (Yêu cầu đăng nhập) ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/lich-su-don-hang', [OrderHistoryController::class, 'index'])->name('orders.history');
    Route::get('/lich-su-don-hang/data', [OrderHistoryController::class, 'getOrders'])->name('orders.history.data');
});

// ==================== CART ROUTES ====================
Route::get('/gio-hang', function (Request $request) {
    $voucherCode = $request->session()->get('voucher_code', null);
    $voucherDiscount = $request->session()->get('voucher_discount', 0);
    
    return Inertia::render('Web/Cart', [
        'voucher_code' => $voucherCode,
        'voucher_discount' => $voucherDiscount,
    ]);
})->name('cart')->middleware('auth');

Route::prefix('api')->group(function () {
    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{variantId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);
    Route::get('/cart/get-coupon', [CartController::class, 'getCoupon']);
    Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon']); // <-- ROUTE NÀY

    Route::post('/campaigns/clear-cache', function() {
        Cache::forget('active_campaigns_with_configs');
        return response()->json(['success' => true]);
    });
    
    Route::post('/pre-order/session', function (Request $request) {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        session([
            'pre_order_checkout' => true,
            'pre_order_variant_id' => $request->variant_id,
            'pre_order_quantity' => $request->quantity,
        ]);
        session()->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Pre-order session saved'
        ]);
    });
});

// ==================== CHECKOUT ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/thanh-toan', [PaymentController::class, 'index'])->name('checkout');
    Route::post('/thanh-toan', [PaymentController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/thanh-cong', [PaymentController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/apply-voucher', [PaymentController::class, 'applyVoucher']);
    Route::post('/checkout/remove-voucher', [PaymentController::class, 'removeVoucher']);
});

// ==================== AUTHENTICATED WEB ROUTES ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/don-hang', [OrderController::class, 'history'])->name('orders.history.old');
    Route::get('/don-hang/{id}', [OrderController::class, 'show'])->name('orders.show');
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    
    Route::prefix('orders')->group(function () {
        Route::get('/{type?}', [AdminOrderController::class, 'index'])
            ->where('type', 'retail|wholesale|preorder')
            ->name('orders.index');
        Route::get('/{id}', [AdminOrderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('orders.show');
        Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('orders.export');
        Route::get('/export/filtered', [AdminOrderController::class, 'exportWithFilters'])->name('orders.export-filtered');
    });
    
    Route::prefix('products')->group(function () {
        Route::get('/{type?}', [AdminProductController::class, 'index'])
            ->where('type', 'normal|preorder')
            ->name('products.index');
        Route::post('/', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    });
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('/colors/data', [ColorController::class, 'getColors'])->name('colors.data');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::put('/colors/{id}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{id}', [ColorController::class, 'destroy'])->name('colors.destroy');
        
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/data', [BrandController::class, 'getBrands'])->name('brands.data');
        Route::post('/', [BrandController::class, 'store'])->name('brands.store');
        Route::put('/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::get('/search', [BrandController::class, 'search'])->name('brands.search');
    });
    
    Route::prefix('customers')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/retail', [AdminCustomerController::class, 'retail'])->name('customers.retail');
        Route::get('/business', [AdminCustomerController::class, 'business'])->name('customers.business');
        Route::get('/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::put('/{id}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::post('/export', [AdminCustomerController::class, 'export'])->name('customers.export');
    });
    
    Route::prefix('customize')->group(function () {
        Route::get('/', [AdminCustomizeController::class, 'index'])->name('customize.index');
        Route::put('/{id}/status', [AdminCustomizeController::class, 'updateStatus'])->name('customize.update-status');
        Route::put('/{id}/approve', [AdminCustomizeController::class, 'approve'])->name('customize.approve');
        Route::post('/send-quote', [AdminCustomizeController::class, 'sendQuote'])->name('customize.send-quote');
    });

    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('news.index');
        Route::get('/data', [NewsController::class, 'getNews'])->name('news.data');
        Route::post('/', [NewsController::class, 'store'])->name('news.store');
        Route::put('/{id}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
        Route::patch('/{id}/status', [NewsController::class, 'updateStatus'])->name('news.update-status');
    });

    Route::prefix('reviews')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::get('/banners/campaigns', [BannerController::class, 'getCampaigns'])->name('banners.campaigns');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/banners/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.status');
    Route::patch('/banners/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.order');
    Route::post('/banners/check-status', [BannerController::class, 'checkAndUpdateStatus'])->name('banners.check-status');

    Route::prefix('promotions')->group(function () {
        Route::get('/', [AdminPromotionController::class, 'index'])->name('promotions.index');
        
        Route::post('/campaign', [AdminPromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
        Route::put('/campaign/{id}', [AdminPromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
        Route::delete('/campaign/{id}', [AdminPromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
        Route::put('/campaign/{id}/status', [AdminPromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');
        Route::get('/campaigns/list', [AdminPromotionController::class, 'getCampaignsList'])->name('promotions.campaigns.list');

        Route::post('/voucher', [AdminPromotionController::class, 'storeVoucher'])->name('promotions.voucher.store');
        Route::put('/voucher/{id}', [AdminPromotionController::class, 'updateVoucher'])->name('promotions.voucher.update');
        Route::delete('/voucher/{id}', [AdminPromotionController::class, 'deleteVoucher'])->name('promotions.voucher.delete');
        Route::put('/voucher/{id}/toggle', [AdminPromotionController::class, 'toggleVoucher'])->name('promotions.voucher.toggle');

        Route::post('/preorder', [AdminPromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
        Route::put('/preorder/{id}', [AdminPromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
        Route::delete('/preorder/{id}', [AdminPromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
        Route::put('/preorder/{id}/toggle', [AdminPromotionController::class, 'togglePreorder'])->name('promotions.preorder.toggle');
        
        Route::post('/discount', [AdminPromotionController::class, 'storeDiscount'])->name('admin.promotions.discount.store');
        Route::put('/discount/{id}', [AdminPromotionController::class, 'updateDiscount'])->name('admin.promotions.discount.update');
        Route::delete('/discount/{id}', [AdminPromotionController::class, 'deleteDiscount'])->name('admin.promotions.discount.delete');
        Route::put('/discount/{id}/toggle', [AdminPromotionController::class, 'toggleDiscount'])->name('admin.promotions.discount.toggle');

        Route::post('/preorder/{id}/increment-buyers', [AdminPromotionController::class, 'incrementPreorderBuyers'])
            ->name('promotions.preorder.increment');

        Route::post('/check', [AdminPromotionController::class, 'checkPromotion'])->name('promotions.check');
        Route::get('/preorder-info', [AdminPromotionController::class, 'getPreorderInfo'])->name('promotions.preorder.info');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports');
        Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');
        Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
    });
    
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.updateGeneral');
    Route::put('/settings/password', [SettingController::class, 'changePassword'])->name('settings.changePassword');

    Route::get('/settings/users', [SettingController::class, 'getUsers'])->name('settings.users');
    Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.storeUser');
    Route::put('/settings/users/{id}', [SettingController::class, 'updateUser'])->name('settings.updateUser');
    Route::delete('/settings/users/{id}', [SettingController::class, 'destroyUser'])->name('settings.destroyUser');
    Route::patch('/settings/users/{id}/toggle', [SettingController::class, 'toggleUserStatus'])->name('settings.toggleUser');
});

Route::get('/media/{path}', function ($path) {
    $fullPath = base_path('media/' . $path);
    if (!File::exists($fullPath)) {
        abort(404);
    }
    $mime = File::mimeType($fullPath);
    return Response::file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*');

// Review
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('auth');

require __DIR__.'/auth.php';


## Assistant:
07/16/2026 13:44

> **Thinking:**
> Chúng ta thấy lỗi: `Ziggy error: route 'admin.reports.index' is not in the route list.` 
> 
> Lỗi này xuất phát từ `AdminLayout.vue` (dòng 187) đang sử dụng route `admin.reports.index` nhưng trong route hiện tại, ta đã định nghĩa route `admin.reports` (không có `.index`). 
> 
> Trong `AdminLayout.vue`, có thể có một link hoặc hàm nào đó gọi `route('admin.reports.index')`. Hãy tìm và sửa thành `route('admin.reports')` (vì tên route là `admin.reports`). 
> 
> Ngoài ra, kiểm tra lại tất cả các route trong file web.php, đảm bảo rằng tên route `admin.reports` đã được khai báo đúng. 
> 
> Ta đã thêm:
> 
> ```php
> Route::prefix('reports')->group(function () {
>     Route::get('/', [ReportController::class, 'index'])->name('reports');
>     Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');
>     Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
> });
> ```
> 
> Vậy tên route đầy đủ sẽ là: `admin.reports`, `admin.reports.data`, `admin.reports.export`.
> 
> Trong Vue, nếu có gọi `route('admin.reports.index')` thì phải sửa thành `route('admin.reports')`.
> 
> Ngoài ra, trong `AdminLayout.vue` có thể có một mục menu "Báo cáo" hoặc "Thống kê" đang sử dụng route sai. Ta cần sửa nó.
> 
> Hãy cung cấp đoạn code của `AdminLayout.vue` (hoặc ít nhất là phần menu) để biết chính xác. Nhưng với lỗi này, cách khắc phục đơn giản là tìm và thay thế tất cả `route('admin.reports.index')` thành `route('admin.reports')`.
> 
> Tôi cũng khuyên bạn nên kiểm tra tất cả các route trong file web.php để đảm bảo không có xung đột.
> 
> Ngoài ra, lỗi còn có thể do `route` helper trong Ziggy không tìm thấy route. Vì vậy, sau khi sửa route, chạy `php artisan route:clear` và `php artisan ziggy:generate` (nếu dùng Ziggy) để cập nhật danh sách route.
> 
> Hãy thực hiện các bước sau:
> 
> 1. Mở `AdminLayout.vue`, tìm `route('admin.reports.index')` và thay bằng `route('admin.reports')`.
> 2. Nếu có các route khác cũng bị lỗi tương tự, sửa tương tự.
> 3. Chạy `php artisan route:clear` và `php artisan cache:clear`.
> 4. Nếu dùng Ziggy, chạy `php artisan ziggy:generate` để cập nhật file ziggy.js.
> 
> Nếu vẫn còn lỗi, kiểm tra lại tên route trong `web.php` có bị trùng hay không. Hiện tại tôi thấy bạn đã import `ReportController` và định nghĩa route đúng.
> 
> Hãy kiểm tra xem trong `AdminLayout.vue` có đoạn nào dùng `route('admin.reports.index')` không. Có thể là trong phần sidebar.
> 
> Bạn có thể gửi nội dung `AdminLayout.vue` để tôi hỗ trợ cụ thể hơn.

Vấn đề là route `admin.reports.index` không tồn tại. Bạn đã định nghĩa route `admin.reports` (không có `.index`). Sửa trong `AdminLayout.vue` (hoặc bất kỳ file nào gọi route đó) thành `admin.reports`.

Ngoài ra, kiểm tra lại các route khác trong nhóm `admin` để đảm bảo tất cả đều khớp.

**Hướng dẫn sửa nhanh:**

1. Mở file `AdminLayout.vue` (thường ở `resources/js/Layouts/AdminLayout.vue`).
2. Tìm `route('admin.reports.index')` và đổi thành `route('admin.reports')`.
3. Nếu có các route khác cũng sai tương tự (ví dụ: `admin.reports.data`, `admin.reports.export`), hãy đảm bảo tên route khớp với những gì đã định nghĩa.
4. Chạy lệnh: `php artisan route:clear` và `php artisan ziggy:generate` (nếu dùng Ziggy) để cập nhật.

Nếu bạn không tìm thấy `AdminLayout.vue`, có thể nó nằm trong `resources/js/Pages/Admin/AdminLayout.vue` hoặc tương tự. Hãy tìm kiếm trong toàn bộ project từ khóa `'admin.reports.index'`.

Sau khi sửa, trang báo cáo sẽ hiển thị. Nếu vẫn gặp lỗi khác, vui lòng cung cấp thêm thông tin.


## User:
07/16/2026 13:46

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Sidebar state
const sidebarCollapsed = ref(false);

// Submenu states
const orderSubmenuOpen = ref(false);
const productSubmenuOpen = ref(false);
const customerSubmenuOpen = ref(false);
const attributeSubmenuOpen = ref(false);
const contentSubmenuOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const currentRoute = computed(() => page.component);

const isActive = (routeNames) => {
    if (typeof routeNames === 'string') {
        return route().current(routeNames);
    }
    if (Array.isArray(routeNames)) {
        return routeNames.some(name => route().current(name));
    }
    return false;
};

const updateSubmenuState = () => {
    const route = currentRoute.value;
    orderSubmenuOpen.value = (route === 'Admin/Orders' || route?.includes('Orders'));
    productSubmenuOpen.value = (route === 'Admin/Products' || route?.includes('Products'));
    customerSubmenuOpen.value = (route === 'Admin/Customers' || route?.includes('Customers'));
    attributeSubmenuOpen.value = (route === 'Admin/Categories' || route === 'Admin/Colors' || route === 'Admin/Brands' || 
        route?.includes('Categories') || route?.includes('Colors') || route?.includes('Brands'));
    contentSubmenuOpen.value = (route === 'Admin/Banners' || route === 'Admin/News' || 
        route?.includes('Banners') || route?.includes('News'));
};

const toggleOrderSubmenu = () => { orderSubmenuOpen.value = !orderSubmenuOpen.value; };
const toggleProductSubmenu = () => { productSubmenuOpen.value = !productSubmenuOpen.value; };
const toggleCustomerSubmenu = () => { customerSubmenuOpen.value = !customerSubmenuOpen.value; };
const toggleAttributeSubmenu = () => { attributeSubmenuOpen.value = !attributeSubmenuOpen.value; };
const toggleContentSubmenu = () => { contentSubmenuOpen.value = !contentSubmenuOpen.value; };


watch(currentRoute, () => {
    updateSubmenuState();
}, { immediate: true });

onMounted(() => {
    updateSubmenuState();
});
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <aside 
            class="w-72 bg-white border-r border-border-light fixed h-full z-20 transition-all duration-300 flex flex-col"
            :class="{ 'hidden lg:block': sidebarCollapsed, 'block': !sidebarCollapsed }"
        >
            <div class="p-6 border-b border-border-light flex-shrink-0">
                <h1 class="text-2xl font-bold text-primary">BigBag<span class="text-on-surface">.vn</span></h1>
                <p class="text-xs text-on-surface-variant mt-1">Admin Portal</p>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                <nav class="p-4 space-y-1">
                    <!-- Dashboard -->
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.dashboard') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="flex-1 text-sm font-medium">Dashboard</span>
                    </Link>
                    
                    <!-- Đơn hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleOrderSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.orders.index', 'admin.orders.show']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">receipt_long</span>
                            <span class="flex-1 text-sm font-medium">Đơn hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': orderSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="orderSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.orders.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🛒 Đơn bán lẻ</Link>
                            <Link :href="route('admin.orders.index', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'wholesale' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🏭 Đơn bán sỉ</Link>
                            <Link :href="route('admin.orders.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">⏳ Đơn Pre-order</Link>
                        </div>
                    </div>

                    <!-- Sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleProductSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.products.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">inventory_2</span>
                            <span class="flex-1 text-sm font-medium">Sản phẩm</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': productSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="productSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.products.index', { type: 'normal' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'normal' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🎒 Sản phẩm thường</Link>
                            <Link :href="route('admin.products.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🔮 Sản phẩm Pre-order</Link>
                        </div>
                    </div>
                    
                    <!-- Thuộc tính sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleAttributeSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.categories.index', 'admin.colors.index', 'admin.brands.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">settings_input_component</span>
                            <span class="flex-1 text-sm font-medium">Thuộc tính</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': attributeSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="attributeSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.categories.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.categories.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">Danh mục</Link>
                            <Link :href="route('admin.colors.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.colors.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">Màu sắc</Link>
                            <Link :href="route('admin.brands.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.brands.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">Thương hiệu</Link>
                        </div>
                    </div>

                    <!-- Khách hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleCustomerSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.customers.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">group</span>
                            <span class="flex-1 text-sm font-medium">Khách hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': customerSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="customerSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.customers.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">👤 Khách lẻ</Link>
                            <Link :href="route('admin.customers.index', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'wholesale' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🏢 Khách doanh nghiệp</Link>
                            <Link :href="route('admin.customers.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">⏳ Pre-order</Link>
                        </div>
                    </div>

                    <!-- Đánh giá -->
                    <Link :href="route('admin.reviews.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reviews.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">rate_review</span>
                        <span class="flex-1 text-sm font-medium">Đánh giá</span>
                    </Link>

                    <!-- Tùy chỉnh -->
                    <Link :href="route('admin.customize.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.customize.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">palette</span>
                        <span class="flex-1 text-sm font-medium">Tùy chỉnh</span>
                    </Link>

                    <!-- Khuyến mãi -->
                    <Link :href="route('admin.promotions.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.promotions.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">local_offer</span>
                        <span class="flex-1 text-sm font-medium">Khuyến mãi</span>
                    </Link>

                    <!-- Nội dung (Banner & Tin tức) -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleContentSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.banners.index', 'admin.news.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">description</span>
                            <span class="flex-1 text-sm font-medium">Nội dung</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': contentSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="contentSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.banners.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.banners.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                <span class="material-symbols-outlined">image</span> Banner
                            </Link>
                            <Link :href="route('admin.news.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.news.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                <span class="material-symbols-outlined">newspaper</span> Tin tức
                            </Link>
                        </div>
                    </div>

                    <!-- Báo cáo -->
                    <Link :href="route('admin.reports.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">bar_chart</span>
                        <span class="flex-1 text-sm font-medium">Báo cáo</span>
                    </Link>

                    <!-- Cài đặt -->
                    <Link :href="route('admin.settings.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.settings.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="flex-1 text-sm font-medium">Cài đặt</span>
                    </Link>

                    <!-- Về trang chủ -->
                    <Link :href="route('home')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-on-surface-variant hover:bg-hover-bg hover:text-primary">
                        <span class="material-symbols-outlined">home</span>
                        <span class="flex-1 text-sm font-medium">Về trang chủ</span>
                    </Link>
                </nav>
            </div>
            
            <!-- User info -->
            <div class="flex-shrink-0 p-4 border-t border-border-light bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-on-surface truncate">{{ user?.name || 'Admin User' }}</p>
                        <p class="text-xs text-outline truncate">{{ user?.email || 'admin@bigbag.vn' }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary transition-colors flex-shrink-0">
                        logout
                    </Link>
                </div>
            </div>
        </aside>

        <div v-if="!sidebarCollapsed" @click="sidebarCollapsed = true" class="fixed inset-0 bg-black/50 z-10 lg:hidden"></div>

        <main class="flex-1 lg:ml-72 transition-all duration-300">
            <header class="sticky top-0 z-10 glass-header border-b border-border-light px-4 md:px-8 py-4 flex justify-between items-center bg-white/95 backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-on-surface-variant hover:text-primary lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="relative hidden md:block">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
                        <input type="text" placeholder="Tìm kiếm..." class="pl-10 pr-4 py-2 bg-white border border-border-light rounded-full w-80 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-4">               
                    <div class="flex items-center gap-2 pl-2 border-l border-border-light">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                        </div>
                        <span class="text-sm font-medium text-on-surface hidden md:block">{{ user?.name || 'Admin' }}</span>
                    </div>
                </div>
            </header>
            <slot />
        </main>
    </div>
</template>

<style scoped>
.sidebar-item-active {
    background-color: #fff5f2;
    color: #ff6b00;
}
.rotate-180 {
    transform: rotate(180deg);
}
.glass-header {
    backdrop-filter: blur(12px);
    background-color: rgba(251, 249, 245, 0.95);
}
aside .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}
aside .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}
aside .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
aside .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #ff6b00;
}
</style>


## Assistant:
07/16/2026 13:46

> **Thinking:**
> Chúng ta cần sửa tên route trong `AdminLayout.vue` từ `admin.reports.index` thành `admin.reports` (vì route đã được định nghĩa là `admin.reports`). Tìm trong file này các chỗ gọi `route('admin.reports.index')` và thay thành `route('admin.reports')`.
> 
> Ngoài ra, kiểm tra các route khác trong menu: ví dụ `admin.orders.index`, `admin.products.index`, v.v. đều đúng.
> 
> Trong file `AdminLayout.vue` trên, ở phần menu "Báo cáo" đang dùng:
> 
> ```html
> <Link :href="route('admin.reports.index')" ...>
> ```
> 
> Cần sửa thành:
> 
> ```html
> <Link :href="route('admin.reports')" ...>
> ```
> 
> Và trong `isActive` có thể giữ nguyên `'admin.reports.index'` không cần thiết, nhưng tốt nhất nên sửa thành `'admin.reports'`. Tuy nhiên, vì route hiện tại là `admin.reports` và component của page là `Admin/Reports`, nên `isActive` với tên route sẽ không khớp. Có thể thay bằng `isActive('admin.reports')`.
> 
> Hãy sửa trong file `AdminLayout.vue`:
> 
> - Dòng menu "Báo cáo": 
>   ```html
>   <Link :href="route('admin.reports')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
>   ```
> - Cập nhật `isActive` nếu cần.
> 
> Ngoài ra, kiểm tra xem còn chỗ nào gọi `admin.reports.index` không, sửa hết.
> 
> Sau khi sửa, route sẽ hoạt động.
> 
> Nếu vẫn lỗi, hãy kiểm tra lại tên route trong `web.php` (đã có `admin.reports`). Đảm bảo chạy `php artisan route:clear` và `php artisan ziggy:generate` để cập nhật.

Vấn đề là trong `AdminLayout.vue`, bạn đang gọi route `admin.reports.index` nhưng route đó chưa được định nghĩa. Bạn đã định nghĩa route `admin.reports` (không có `.index`). Cần sửa tất cả các lần gọi `admin.reports.index` thành `admin.reports`.

Dưới đây là phần cần sửa trong file `AdminLayout.vue` (tìm và thay đổi):

### 1. Sửa link menu "Báo cáo"
Tìm dòng:
```html
<Link :href="route('admin.reports.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
```
Sửa thành:
```html
<Link :href="route('admin.reports')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
```

### 2. (Nếu có) Sửa các chỗ khác gọi `admin.reports.index`
Trong file này chỉ có một chỗ duy nhất, nên chỉ cần sửa như trên.

### 3. Kiểm tra lại các route khác
- `admin.orders.index` đã tồn tại (có tham số type).
- `admin.products.index` đã tồn tại.
- `admin.customers.index` đã tồn tại.
- `admin.reviews.index` đã tồn tại.
- `admin.customize.index` đã tồn tại.
- `admin.promotions.index` đã tồn tại.
- `admin.banners.index` đã tồn tại.
- `admin.news.index` đã tồn tại.
- `admin.settings.index` đã tồn tại.
- `admin.dashboard` đã tồn tại.

Tất cả đều ổn, chỉ có `admin.reports` là cần sửa tên.

### 4. Sau khi sửa, chạy lệnh:
```bash
php artisan route:clear
php artisan ziggy:generate
```

Khởi động lại dev server nếu cần.

Điều này sẽ giải quyết lỗi `Ziggy error: route 'admin.reports.index' is not in the route list`.


## User:
07/16/2026 13:52

<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

// Props từ controller
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

// Dữ liệu từ props
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

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryData = props.reportData?.categoryDistribution || [0, 0, 0];
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
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

// Tải dữ liệu theo period
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        await router.get('/admin/reports', { period: period.value }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                const newData = page.props.reportData;
                if (newData) {
                    summaryData.value = newData.summary || summaryData.value;
                    topProducts.value = newData.topProducts || [];
                    topCustomers.value = newData.topCustomers || [];
                    chartData.value = newData.chartData || chartData.value;
                    
                    // Cập nhật biểu đồ doanh thu
                    if (revenueChart) {
                        revenueChart.data.labels = chartData.value.labels;
                        revenueChart.data.datasets[0].data = chartData.value.retail;
                        revenueChart.data.datasets[1].data = chartData.value.wholesale;
                        revenueChart.data.datasets[2].data = chartData.value.preorder;
                        revenueChart.update();
                    }
                    
                    // Cập nhật biểu đồ danh mục nếu có
                    if (categoryChart && newData.categoryDistribution) {
                        categoryChart.data.datasets[0].data = newData.categoryDistribution;
                        categoryChart.update();
                    }
                }
            },
            onError: () => {
                console.error('Lỗi tải dữ liệu');
            }
        });
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        // Dùng window.location để tải file trực tiếp
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
Nút báo cáo theo tuần/tháng/năm đang bị lỗi không hiển thị dữ liệu khi bấm.


## Assistant:
07/16/2026 13:52

> **Thinking:**
> Chúng ta cần xác định nguyên nhân khi bấm nút period (Tuần/Tháng/Năm) không cập nhật dữ liệu. Hiện tại, component đang gọi `loadDataByPeriod()` khi `period` thay đổi. Trong `loadDataByPeriod`, nó gọi `router.get('/admin/reports', { period: period.value })`. Điều này có thể đang gửi request nhưng response có thể không trả về dữ liệu mới đúng cách.
> 
> Một vấn đề có thể là route `/admin/reports` đã được định nghĩa và controller `ReportController@index` đã nhận tham số `period`. Nhưng để kiểm tra, ta cần đảm bảo rằng controller trả về dữ liệu đúng.
> 
> Đồng thời, nếu request thành công, `onSuccess` sẽ cập nhật `summaryData`, `topProducts`, `topCustomers`, `chartData` và cập nhật biểu đồ.
> 
> Có thể lỗi là vì `period.value` không được truyền đúng hoặc controller không nhận được. Hoặc do Vue không re-render.
> 
> Hãy kiểm tra lại:
> 
> 1. Trong `loadDataByPeriod`, router.get với tham số period. Có thể sử dụng `router.get(route('admin.reports'), { period: period.value })` thay vì đường dẫn cứng.
> 2. Đảm bảo trong controller, `$request->input('period')` được xử lý.
> 3. Khi nhận dữ liệu mới, cần cập nhật biểu đồ đúng cách.
> 
> Để debug, thêm console.log trong `onSuccess` và trong controller để xem dữ liệu trả về.
> 
> Ngoài ra, lưu ý rằng khi `period` thay đổi, nó sẽ gọi `loadDataByPeriod()`, nhưng `watch` được khai báo với `{ immediate: true }`? Ở đây không có immediate, nên chỉ chạy khi giá trị thay đổi.
> 
> Vấn đề có thể là do `router.get` không trả về dữ liệu vì route không đúng hoặc controller không xử lý. Hãy sửa route trong vue: thay vì `/admin/reports`, sử dụng `route('admin.reports')`.
> 
> Ngoài ra, kiểm tra xem `ReportController` có implement `getData` không? Trong route đã định nghĩa `Route::get('/data', [ReportController::class, 'getData'])` nhưng vue đang gọi `/admin/reports` (route index) với query parameter `period`. Vậy controller index nhận period và trả về trang inertia, không phải JSON. Điều đó có nghĩa khi gọi `router.get('/admin/reports', { period })`, nó sẽ render lại toàn bộ trang chứ không chỉ trả về dữ liệu JSON, và có thể không cập nhật dữ liệu trong component hiện tại (vì Inertia sẽ thay thế trang, không giữ state). Để cập nhật dữ liệu mà không reload trang, nên gọi route `/admin/reports/data` với method GET để lấy JSON.
> 
> Vì vậy, cần sửa `loadDataByPeriod` để gọi `route('admin.reports.data')` thay vì `route('admin.reports')`.
> 
> Sửa:
> 
> ```javascript
> await router.get(route('admin.reports.data'), { period: period.value }, {
>     preserveScroll: true,
>     preserveState: true,
>     onSuccess: (page) => {
>         // page.props sẽ chứa dữ liệu trả về từ getData (JSON)
>     }
> });
> ```
> 
> Nhưng `getData` trả về `response()->json(...)`, không phải Inertia response, nên `router.get` có thể không xử lý được kiểu JSON trả về. Thay vào đó, nên dùng `axios` hoặc `fetch` để gọi API.
> 
> Cách tốt nhất: Sử dụng `axios` để gọi API `/admin/reports/data?period=...` và nhận JSON, sau đó cập nhật dữ liệu thủ công.
> 
> Hoặc giữ nguyên cách dùng Inertia nhưng thay vì gọi route index, gọi route data nhưng phải đảm bảo `getData` trả về Inertia response với props data. Nhưng `getData` hiện tại trả về JSON, không phải Inertia. Vì vậy cần sửa.
> 
> Sửa lại `ReportController::getData` để trả về Inertia::render? Không, vì đó là API. Vậy nên dùng axios.
> 
> Hãy cập nhật file vue:
> 
> - Import axios: `import axios from 'axios';`
> - Trong `loadDataByPeriod`, thay vì `router.get`, dùng `axios.get(route('admin.reports.data'), { params: { period: period.value } })`.
> - Khi nhận response, cập nhật dữ liệu và biểu đồ.
> 
> Đồng thời, bỏ `onSuccess`, `onError` của router.
> 
> Đây là cách tối ưu.
> 
> Vậy tôi sẽ hướng dẫn sửa file `Reports.vue` để dùng axios.
> 
> Đồng thời, kiểm tra route data đã được định nghĩa đúng chưa.
> 
> Nếu vẫn không được, có thể do lỗi khác.
> 
> Tôi sẽ đưa ra code sửa cụ thể.

Vấn đề là khi bạn bấm nút thay đổi period, `loadDataByPeriod()` gọi `router.get('/admin/reports', { period })`. Điều này sẽ gửi request tới route `admin.reports` (index) – route này trả về trang Inertia, không phải JSON. Do đó, dữ liệu không được cập nhật đúng cách trong component hiện tại. Để khắc phục, bạn cần tách riêng API lấy dữ liệu JSON và gọi nó bằng `axios` hoặc `fetch`.

Dưới đây là các bước sửa chi tiết:

---

## 1. Cài đặt Axios (nếu chưa có)

```bash
npm install axios
```

---

## 2. Sửa file `Reports.vue` – thay đổi cách lấy dữ liệu

**Thay thế phần `loadDataByPeriod`** và thêm import `axios`:

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios'; // <-- Thêm import

// ... giữ nguyên các biến khác ...

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
            
            // Cập nhật biểu đồ doanh thu
            if (revenueChart) {
                revenueChart.data.labels = chartData.value.labels;
                revenueChart.data.datasets[0].data = chartData.value.retail;
                revenueChart.data.datasets[1].data = chartData.value.wholesale;
                revenueChart.data.datasets[2].data = chartData.value.preorder;
                revenueChart.update();
            }
            
            // Cập nhật biểu đồ danh mục nếu có
            if (categoryChart && newData.categoryDistribution) {
                categoryChart.data.datasets[0].data = newData.categoryDistribution;
                categoryChart.update();
            }
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Lần đầu load dữ liệu (nếu chưa có từ props)
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    }
    setTimeout(() => {
        initRevenueChart();
        initCategoryChart();
    }, 100);
});
</script>
```

---

## 3. Sửa `ReportController` – đảm bảo `getData` trả về đúng định dạng

Trong `ReportController`, method `getData` đã trả về JSON, không cần sửa. Nhưng hãy chắc chắn rằng route `admin.reports.data` đang trỏ đúng tới method đó (bạn đã có trong web.php).

---

## 4. Kiểm tra route trong `web.php`

Đoạn route đã có:

```php
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports');
    Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
```

Vậy `route('admin.reports.data')` sẽ trả về `/admin/reports/data`, đúng như yêu cầu.

---

## 5. (Tùy chọn) Xóa `onSuccess` và `onError` cũ

Vì đã dùng axios, bạn có thể bỏ hoàn toàn các callback `onSuccess`, `onError` trong `loadDataByPeriod`.

---

## 6. Chạy lại và kiểm tra

Sau khi sửa, bấm các nút Tuần/Tháng/Năm sẽ gọi API và cập nhật dữ liệu mà không reload trang. Biểu đồ sẽ tự động cập nhật.

---

**Lưu ý:** Nếu vẫn không hiển thị dữ liệu, hãy mở DevTools (F12) → Network để xem request `/admin/reports/data?period=week` trả về dữ liệu gì. Kiểm tra console để xem lỗi. Nếu controller trả về dữ liệu rỗng, hãy kiểm tra lại database và logic trong `getReportData`.


## User:
07/16/2026 13:53

<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

// Props từ controller
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

// Dữ liệu từ props
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

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryData = props.reportData?.categoryDistribution || [0, 0, 0];
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
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

// Tải dữ liệu theo period
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        await router.get('/admin/reports', { period: period.value }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                const newData = page.props.reportData;
                if (newData) {
                    summaryData.value = newData.summary || summaryData.value;
                    topProducts.value = newData.topProducts || [];
                    topCustomers.value = newData.topCustomers || [];
                    chartData.value = newData.chartData || chartData.value;
                    
                    // Cập nhật biểu đồ doanh thu
                    if (revenueChart) {
                        revenueChart.data.labels = chartData.value.labels;
                        revenueChart.data.datasets[0].data = chartData.value.retail;
                        revenueChart.data.datasets[1].data = chartData.value.wholesale;
                        revenueChart.data.datasets[2].data = chartData.value.preorder;
                        revenueChart.update();
                    }
                    
                    // Cập nhật biểu đồ danh mục nếu có
                    if (categoryChart && newData.categoryDistribution) {
                        categoryChart.data.datasets[0].data = newData.categoryDistribution;
                        categoryChart.update();
                    }
                }
            },
            onError: () => {
                console.error('Lỗi tải dữ liệu');
            }
        });
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        // Dùng window.location để tải file trực tiếp
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
Cập nhật page report


## Assistant:
07/16/2026 13:53

> **Thinking:**
> Chúng ta cần cập nhật file `Reports.vue` để dùng `axios` thay vì `router.get` để gọi API lấy dữ liệu khi thay đổi period. Đồng thời đảm bảo route đúng.
> 
> **Các bước:**
> 
> 1. Cài đặt `axios` nếu chưa có.
> 2. Sửa `loadDataByPeriod` để dùng `axios.get(route('admin.reports.data'), { params: { period: period.value } })`.
> 3. Xóa các `onSuccess/onError` cũ.
> 4. Đảm bảo `exportReport` dùng `route('admin.reports.export')`.
> 
> Ngoài ra, cần kiểm tra lại `ReportController` để đảm bảo `getData` trả về dữ liệu đúng.
> 
> Sẽ cung cấp file Vue hoàn chỉnh đã sửa.

Để khắc phục lỗi không hiển thị dữ liệu khi chọn period, bạn cần thay đổi cách gọi API: thay vì `router.get` (Inertia) -> gây reload trang, hãy dùng `axios` để gọi endpoint JSON.

Dưới đây là file **Reports.vue** đã được cập nhật hoàn chỉnh:

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Props từ controller
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

// Dữ liệu từ props (khởi tạo ban đầu)
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

const categoryDistribution = ref(props.reportData?.categoryDistribution || [0, 0, 0]);

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryDistribution.value,
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

// Tải dữ liệu theo period (dùng axios gọi API JSON)
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { period: period.value }
        });

        const newData = response.data;
        if (newData) {
            // Cập nhật dữ liệu
            summaryData.value = newData.summary || summaryData.value;
            topProducts.value = newData.topProducts || [];
            topCustomers.value = newData.topCustomers || [];
            chartData.value = newData.chartData || chartData.value;
            categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
            
            // Cập nhật biểu đồ doanh thu
            if (revenueChart) {
                revenueChart.data.labels = chartData.value.labels;
                revenueChart.data.datasets[0].data = chartData.value.retail;
                revenueChart.data.datasets[1].data = chartData.value.wholesale;
                revenueChart.data.datasets[2].data = chartData.value.preorder;
                revenueChart.update();
            }
            
            // Cập nhật biểu đồ danh mục
            if (categoryChart) {
                categoryChart.data.datasets[0].data = categoryDistribution.value;
                categoryChart.update();
            }
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    }
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
```

**Các thay đổi chính:**

1. **Import axios** và dùng `axios.get` để gọi API `/admin/reports/data?period=...` thay vì `router.get`.
2. **Xóa `onSuccess/onError`** và xử lý dữ liệu trả về trực tiếp trong `try/catch`.
3. **Thêm biến `categoryDistribution`** để cập nhật biểu đồ doughnut.
4. **Kiểm tra dữ liệu ban đầu**: nếu `props.reportData` rỗng thì gọi `loadDataByPeriod()` để tải lần đầu.

Bây giờ khi bấm nút Tuần/Tháng/Năm, dữ liệu sẽ được cập nhật mà không reload trang và biểu đồ sẽ tự động cập nhật.


## User:
07/16/2026 13:55

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year

        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * API trả về dữ liệu dạng JSON (cho Vue gọi khi đổi period)
     */
    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

    /**
     * Xuất báo cáo Excel
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'week');

        // Lọc đơn hàng theo period
        $query = Order::query();
        $startDate = $this->getStartDate($period);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $orders = $query->latest()->get();

        // Sử dụng Export class có sẵn (hoặc tạo mới)
        return Excel::download(new OrdersExport('all', $orders), 'bao_cao_thong_ke_' . $period . '.xlsx');
    }

    // ==================== Phương thức lấy dữ liệu ====================

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // 1. Tổng quan doanh thu và tăng trưởng theo loại hình
        $summary = $this->getSummary($period);

        // 2. Dữ liệu biểu đồ doanh thu
        $chartData = $this->getChartData($period);

        // 3. Top sản phẩm bán chạy (theo doanh thu)
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);

        // 4. Top khách hàng (theo tổng chi tiêu)
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);

        // 5. Phân bố danh mục (tính theo % doanh thu)
        $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

        return [
            'summary' => $summary,
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categoryDistribution' => $categoryDistribution,
        ];
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }

    // ==================== Các helper chi tiết ====================

    /**
     * Tổng doanh thu và tăng trưởng của 3 loại hình
     */
    private function getSummary($period)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Nếu period là week, month, year thì có thể lấy doanh thu trong kỳ thay vì hôm nay
        // Ở đây ta vẫn lấy hôm nay để hiển thị trên thẻ
        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueToday = Order::where('order_code', $type)
                ->whereDate('created_at', $today)
                ->sum('final_amount');

            $revenueYesterday = Order::where('order_code', $type)
                ->whereDate('created_at', $yesterday)
                ->sum('final_amount');

            $growth = 0;
            if ($revenueYesterday > 0) {
                $growth = round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 1);
            } elseif ($revenueToday > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueToday,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    /**
     * Dữ liệu biểu đồ cột doanh thu theo tuần/tháng/năm
     */
    private function getChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } else { // year
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Top sản phẩm theo doanh thu
     */
    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                \DB::raw('SUM(order_details.quantity) as sold'),
                \DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                    'revenue' => (int) $item->revenue,
                ];
            });
    }

    /**
     * Top khách hàng theo tổng chi tiêu
     */
    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.customer_name as name',
                \DB::raw('COUNT(orders.id) as orders'),
                \DB::raw('SUM(orders.final_amount) as total')
            )
            ->groupBy('orders.customer_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');

        if ($total == 0) {
            return [0, 0, 0];
        }

        // Lấy doanh thu theo từng danh mục (giả sử có 3 danh mục chính)
        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->get();

        // Map thành mảng 3 phần tử tương ứng: Balo, Cặp - Túi, Phụ kiện (có thể tùy chỉnh)
        // Giả sử bạn có danh mục id: 1=balo, 2=túi, 3=phụ kiện...
        // Ở đây làm đơn giản: lấy 3 danh mục có doanh thu cao nhất
        $top3 = $categories->sortByDesc('revenue')->take(3)->values();

        // Nếu ít hơn 3, bổ sung 0
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = isset($top3[$i]) ? round($top3[$i]->revenue / $total * 100) : 0;
        }

        return $result;
    }

    /**
     * Chuyển đổi số thứ tự ngày sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}
Kiểm tra lại controller


## Assistant:
07/16/2026 13:55

> **Thinking:**
> Chúng ta cần kiểm tra controller để đảm bảo dữ liệu trả về đúng định dạng và có dữ liệu thực. Tôi sẽ rà soát các điểm chính:
> 
> 1. **`getStartDate`** – trả về đúng ngày bắt đầu cho từng period.
> 2. **`getSummary`** – tính doanh thu hôm nay và tăng trưởng so với hôm qua. OK.
> 3. **`getChartData`** – dữ liệu biểu đồ: labels và các mảng retail/wholesale/preorder. OK.
> 4. **`getTopProducts`** – lấy top sản phẩm theo doanh thu trong khoảng thời gian. OK.
> 5. **`getTopCustomers`** – lấy top khách hàng theo tổng chi tiêu. OK.
> 6. **`getCategoryDistribution`** – tính % doanh thu theo danh mục. OK.
> 
> Tuy nhiên, cần lưu ý một số vấn đề:
> 
> - Trong **`getChartData`** cho 'week', labels là thứ (T2, T3...) nhưng `$date->dayOfWeek` trả về 0 (Chủ nhật) đến 6 (Thứ bảy). Hàm `getVietnameseDayOfWeek` ánh xạ đúng.
> - Trong **`getCategoryDistribution`**, nếu danh mục có tên khác không khớp với 'Balo', 'Cặp - Túi', 'Phụ kiện', thì vẫn lấy top 3 danh mục. Nhưng dữ liệu mẫu có 5 danh mục (1: Balo Laptop, 2: Balo Du lịch, 3: Túi đeo chéo, 4: Balo thời trang, 5: Balo chống sốc) – như vậy tên không khớp. Tuy nhiên vì lấy top 3, kết quả trả về vẫn là 3 con số % tương ứng. Trong Vue chỉ hiển thị 3 nhãn cố định "Balo", "Cặp - Túi", "Phụ kiện" – điều này có thể không khớp với dữ liệu thực. Bạn có thể sửa lại nhãn trong Vue hoặc thay đổi logic để nhóm danh mục.
> 
> - **`getTopCustomers`** group by `customer_name`. Nếu có giá trị NULL thì sẽ gộp chung, OK.
> 
> - **`getTopProducts`** sử dụng `order_details.subtotal` là giá trị đã nhân số lượng, OK.
> 
> - **`export`** đang gọi `OrdersExport` với tham số thứ hai là collection các orders. `OrdersExport` constructor nhận `$type` và `$orders` – trong file export bạn đã có `public function __construct($type = 'retail', $orders = null)`, OK.
> 
> - Cần đảm bảo import `\DB` hoặc sử dụng `Illuminate\Support\Facades\DB`.
> 
> - Trong `getCategoryDistribution`, `$total` có thể là 0 nên phải kiểm tra.
> 
> - Trong `getChartData`, các giá trị trả về là integer, nhưng có thể lên tới hàng triệu, đồ thị hiển thị bình thường.
> 
> - Có thể thêm `try-catch` để bắt lỗi.
> 
> Nhìn chung controller đúng, nhưng cần kiểm tra dữ liệu seed có đủ để test không. Đảm bảo có đơn hàng trong khoảng thời gian được chọn.
> 
> Nếu dữ liệu seed chưa đủ (ví dụ chỉ có 3 đơn hàng cũ), thì khi chọn 'week' có thể không có dữ liệu, nên biểu đồ hiển thị toàn 0. Bạn nên tạo thêm dữ liệu giả trong 30 ngày gần nhất để test.
> 
> Vậy controller OK. Cần chạy seeder mới (đã cập nhật) để có dữ liệu đa dạng.

Controller của bạn hoạt động tốt về logic. Tuy nhiên, cần kiểm tra và tối ưu một số điểm để đảm bảo dữ liệu hiển thị chính xác:

---

## 1. Vấn đề phân bố danh mục (`categoryDistribution`)

Trong Vue, nhãn của biểu đồ doughnut là `['Balo', 'Cặp - Túi', 'Phụ kiện']` – nhưng thực tế danh mục trong database là:
- Balo Laptop
- Balo Du lịch
- Túi đeo chéo
- Balo thời trang
- Balo chống sốc

→ Kết quả `categoryDistribution` trả về top 3 danh mục (bất kỳ tên gì) sẽ không khớp với nhãn cố định. Để khắc phục, bạn có thể:

**Cách 1:** Sửa nhãn trong Vue thành động, lấy từ tên danh mục thực tế.

**Cách 2:** Nhóm danh mục lại theo ý nghĩa (ví dụ: nhóm các danh mục có chứa từ "Balo" vào chung, "Túi" vào chung). Tuy nhiên cách này phức tạp.

**Khuyến nghị:** Sửa Vue để hiển thị tên danh mục thực tế. Trong `getCategoryDistribution`, thay vì chỉ trả về mảng số, bạn có thể trả về mảng các object `{ label, value }`.

**Sửa `getCategoryDistribution` trong `ReportController`:**
```php
private function getCategoryDistribution($startDate, $endDate)
{
    $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
    if ($total == 0) {
        return [];
    }

    $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
        ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
        ->groupBy('categories.name')
        ->get();

    $result = $categories->map(function ($item) use ($total) {
        return [
            'label' => $item->name,
            'value' => round($item->revenue / $total * 100)
        ];
    })->sortByDesc('value')->values()->take(3);

    return $result;
}
```

Sau đó, cập nhật Vue: nhận `categoryDistribution` là mảng object và hiển thị label tương ứng.

**Hoặc nếu muốn đơn giản, giữ nguyên như hiện tại** – biểu đồ vẫn hiển thị 3 phần, chỉ là nhãn không khớp, nhưng vẫn có ý nghĩa thống kê.

---

## 2. Đảm bảo dữ liệu seed đủ để test

Bạn đã có seeder tạo 50 đơn hàng với ngày tháng ngẫu nhiên trong 30 ngày. Vậy khi chọn 'week' sẽ có dữ liệu. Nếu không, hãy kiểm tra lại `OrderSeeder` đã chạy thành công và có đơn hàng với `created_at` trong 7 ngày gần nhất.

---

## 3. Kiểm tra route và tên route

Trong `Report.vue` bạn gọi `route('admin.reports.data')` – đúng với định nghĩa trong `web.php`:
```php
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports');
    Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');
    Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
});
```
Và đã có prefix `admin.` từ group cha, nên tên đầy đủ là `admin.reports.data`. OK.

---

## 4. Xuất Excel – `OrdersExport`

Bạn đã có `OrdersExport` nhận `$type` và `$orders`. Trong `export`, gọi `new OrdersExport('all', $orders)` – OK.

Lưu ý: Nếu `$orders` là collection rỗng, file vẫn xuất được nhưng chỉ có header. Bạn có thể thêm kiểm tra.

---

## 5. Tối ưu hiệu năng

Các truy vấn trong `getChartData` lặp lại nhiều lần (7 ngày x 3 loại = 21 truy vấn). Bạn có thể tối ưu bằng cách lấy dữ liệu một lần và nhóm, nhưng với số lượng đơn hàng không lớn thì không cần thiết.

---

## 6. Kiểm tra lỗi tiềm ẩn

- Trong `getTopCustomers`, nếu `customer_name` là NULL thì `GROUP BY` sẽ gộp chung. Nếu bạn muốn hiển thị "Khách lẻ" thì đã xử lý ở `map`.
- Đảm bảo `Carbon::today()` trả về đúng múi giờ (có thể set trong `config/app.php`).

---

## Kết luận

Controller của bạn **chính xác về logic**. Vấn đề không hiển thị dữ liệu khi bấm nút period thường nằm ở frontend (đã được fix bằng axios). Nếu vẫn không thấy dữ liệu, hãy:

1. Mở DevTools → Network → xem request `/admin/reports/data?period=week` trả về JSON gì.
2. Nếu dữ liệu trả về toàn 0 hoặc rỗng, hãy kiểm tra database có đơn hàng trong khoảng thời gian đó không.
3. Nếu có lỗi PHP, kiểm tra `storage/logs/laravel.log`.

Bạn có thể thêm `dd($reportData)` vào cuối `getReportData` để debug nếu cần. Sau khi kiểm tra, hãy xóa `dd` trước khi deploy.


## User:
07/16/2026 13:57

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year

        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * API trả về dữ liệu dạng JSON (cho Vue gọi khi đổi period)
     */
    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

    /**
     * Xuất báo cáo Excel
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'week');

        // Lọc đơn hàng theo period
        $query = Order::query();
        $startDate = $this->getStartDate($period);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $orders = $query->latest()->get();

        // Sử dụng Export class có sẵn (hoặc tạo mới)
        return Excel::download(new OrdersExport('all', $orders), 'bao_cao_thong_ke_' . $period . '.xlsx');
    }

    // ==================== Phương thức lấy dữ liệu ====================

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // 1. Tổng quan doanh thu và tăng trưởng theo loại hình
        $summary = $this->getSummary($period);

        // 2. Dữ liệu biểu đồ doanh thu
        $chartData = $this->getChartData($period);

        // 3. Top sản phẩm bán chạy (theo doanh thu)
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);

        // 4. Top khách hàng (theo tổng chi tiêu)
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);

        // 5. Phân bố danh mục (tính theo % doanh thu)
        $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

        return [
            'summary' => $summary,
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categoryDistribution' => $categoryDistribution,
        ];
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }

    // ==================== Các helper chi tiết ====================

    /**
     * Tổng doanh thu và tăng trưởng của 3 loại hình
     */
    private function getSummary($period)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Nếu period là week, month, year thì có thể lấy doanh thu trong kỳ thay vì hôm nay
        // Ở đây ta vẫn lấy hôm nay để hiển thị trên thẻ
        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueToday = Order::where('order_code', $type)
                ->whereDate('created_at', $today)
                ->sum('final_amount');

            $revenueYesterday = Order::where('order_code', $type)
                ->whereDate('created_at', $yesterday)
                ->sum('final_amount');

            $growth = 0;
            if ($revenueYesterday > 0) {
                $growth = round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 1);
            } elseif ($revenueToday > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueToday,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    /**
     * Dữ liệu biểu đồ cột doanh thu theo tuần/tháng/năm
     */
    private function getChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } else { // year
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Top sản phẩm theo doanh thu
     */
    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                \DB::raw('SUM(order_details.quantity) as sold'),
                \DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                    'revenue' => (int) $item->revenue,
                ];
            });
    }

    /**
     * Top khách hàng theo tổng chi tiêu
     */
    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.customer_name as name',
                \DB::raw('COUNT(orders.id) as orders'),
                \DB::raw('SUM(orders.final_amount) as total')
            )
            ->groupBy('orders.customer_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
        if ($total == 0) {
            return [];
        }

        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->get();

        $result = $categories->map(function ($item) use ($total) {
            return [
                'label' => $item->name,
                'value' => round($item->revenue / $total * 100)
            ];
        })->sortByDesc('value')->values()->take(3);

        return $result;
    }

    /**
     * Chuyển đổi số thứ tự ngày sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}
Có liên quan gì đến việc function ở private không?


## Assistant:
07/16/2026 13:57

> **Thinking:**
> Chúng ta đang phân tích lỗi: user hỏi "Có liên quan gì đến việc function ở private không?" – họ đang thắc mắc liệu việc các method trong ReportController là private có ảnh hưởng gì không (ví dụ gọi từ route, từ Vue, hay từ bên ngoài). 
> 
> Trong Laravel, controller method public là những method được gọi từ router. Các method private chỉ dùng nội bộ, không ảnh hưởng đến việc gọi từ bên ngoài. Tuy nhiên, cần kiểm tra xem các route gọi method nào: index, getData, export – đều là public. Các private helper không liên quan. Vậy câu trả lời là: không, việc dùng private không ảnh hưởng, vì route chỉ gọi các method public.
> 
> Ngoài ra, có thể họ đang lo lắng về việc private method không thể gọi từ bên ngoài, nhưng không ảnh hưởng vì chỉ gọi nội bộ.
> 
> Tôi sẽ trả lời: "Không, các hàm private chỉ dùng nội bộ, không ảnh hưởng đến việc gọi API hay hiển thị dữ liệu. Các route gọi các method public index, getData, export là đúng."
> 
> Cũng có thể họ đang hỏi về lỗi không hiển thị dữ liệu, nhưng vấn đề đã được fix ở frontend. Tôi sẽ xác nhận controller đúng và private không ảnh hưởng.
> 
> Tôi sẽ viết câu trả lời ngắn gọn, rõ ràng.

Không, việc sử dụng **`private`** cho các hàm helper trong `ReportController` **không ảnh hưởng** đến chức năng báo cáo.

### Lý do:
- Các route chỉ gọi **public method**: `index()`, `getData()`, `export()`.
- Các hàm `private` như `getReportData()`, `getSummary()`, `getChartData()`… chỉ được gọi **nội bộ** trong cùng controller, nên việc khai báo `private` là đúng và an toàn.
- Không có route nào gọi trực tiếp các hàm `private` từ bên ngoài, nên không gây lỗi.

### Vấn đề bạn gặp (không hiển thị dữ liệu khi bấm nút period) **không liên quan** đến `private`, mà do cách gọi API ở frontend (đã sửa bằng axios) và dữ liệu trong database.

Nếu vẫn không thấy dữ liệu, hãy kiểm tra:
1. **Network tab** trong DevTools để xem response của `/admin/reports/data?period=week` có trả về dữ liệu không.
2. **Database** có đơn hàng trong khoảng thời gian đã chọn không.
3. **Log** Laravel (`storage/logs/laravel.log`) có lỗi gì không.

Tóm lại: `private` không phải nguyên nhân, controller của bạn hoạt động đúng.


## User:
07/16/2026 14:02

<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Props từ controller
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

// Dữ liệu từ props (khởi tạo ban đầu)
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

const categoryDistribution = ref(props.reportData?.categoryDistribution || [0, 0, 0]);

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryDistribution.value,
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

// Tải dữ liệu theo period (dùng axios gọi API JSON)
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { period: period.value }
        });

        const newData = response.data;
        if (newData) {
            // Cập nhật dữ liệu
            summaryData.value = newData.summary || summaryData.value;
            topProducts.value = newData.topProducts || [];
            topCustomers.value = newData.topCustomers || [];
            chartData.value = newData.chartData || chartData.value;
            categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
            
            // Cập nhật biểu đồ doanh thu
            if (revenueChart) {
                revenueChart.data.labels = chartData.value.labels;
                revenueChart.data.datasets[0].data = chartData.value.retail;
                revenueChart.data.datasets[1].data = chartData.value.wholesale;
                revenueChart.data.datasets[2].data = chartData.value.preorder;
                revenueChart.update();
            }
            
            // Cập nhật biểu đồ danh mục
            if (categoryChart) {
                categoryChart.data.datasets[0].data = categoryDistribution.value;
                categoryChart.update();
            }
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    }
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
Kiểm tra lại vue


## Assistant:
07/16/2026 14:02

> **Thinking:**
> Chúng ta sẽ kiểm tra các điểm có thể gây lỗi trong file Vue này. Vấn đề chính là khi bấm nút period, dữ liệu không được cập nhật. 
> 
> Các điểm cần kiểm tra:
> 
> 1. **Route name** - `route('admin.reports.data')` có tồn tại không? Trong route file, ta có `Route::prefix('reports')->group(...)` và trong group đó có `Route::get('/data', [ReportController::class, 'getData'])->name('reports.data');`. Và group admin có prefix `admin` và name `admin.`, nên tên đầy đủ là `admin.reports.data`. Đúng.
> 
> 2. **Axios request** - có thể bị lỗi CORS? Không, vì cùng domain.
> 
> 3. **Dữ liệu trả về** - kiểm tra xem response.data có đúng cấu trúc không.
> 
> 4. **Phương thức khởi tạo biểu đồ** - cần kiểm tra xem chart có được khởi tạo đúng sau khi lấy dữ liệu không.
> 
> 5. **Watch period** - sẽ gọi `loadDataByPeriod` khi period thay đổi. Đúng.
> 
> 6. **OnMounted** - nếu props.reportData có dữ liệu, biểu đồ được vẽ, nhưng nếu không có dữ liệu thì gọi loadDataByPeriod. Tuy nhiên sau khi gọi load, cần vẽ lại biểu đồ. Trong `loadDataByPeriod` đã cập nhật lại dữ liệu và gọi `revenueChart.update()` và `categoryChart.update()` nếu chart đã tồn tại. Nhưng nếu chart chưa tồn tại (chưa được khởi tạo) thì sẽ không update được. Vậy cần đảm bảo chart được khởi tạo trước khi update, hoặc gọi khởi tạo lại.
> 
> Trong onMounted, chúng ta gọi `setTimeout(() => { initRevenueChart(); initCategoryChart(); }, 100);` - tức là sau 100ms, chart được khởi tạo. Trong khi đó, nếu `props.reportData` có dữ liệu, chart được khởi tạo với dữ liệu đó. Nếu không có, loadDataByPeriod sẽ gọi, nhưng có thể xảy ra race condition: loadData hoàn thành trước khi chart được khởi tạo? Vì setTimeout 100ms, trong khi axios request mất vài trăm ms, nên chart sẽ được khởi tạo trước khi load hoàn thành. Nhưng trong loadData, chúng ta chỉ update chứ không tạo mới chart. Nếu chart chưa tồn tại, `revenueChart` sẽ là null, và dòng `if (revenueChart)` sẽ không chạy, nên không update. Kết quả là chart vẫn hiển thị dữ liệu cũ (hoặc trống). Đó chính là vấn đề.
> 
> Giải pháp: Nếu chart chưa tồn tại, thì sau khi nhận dữ liệu, gọi `initRevenueChart()` và `initCategoryChart()`.
> 
> **Sửa**:
> 
> Trong `loadDataByPeriod`, sau khi cập nhật dữ liệu, thay vì chỉ update, hãy kiểm tra nếu chart chưa tồn tại thì khởi tạo lại.
> 
> ```js
> // Sau khi cập nhật dữ liệu:
> if (!revenueChart) {
>     initRevenueChart();
> } else {
>     revenueChart.update();
> }
> if (!categoryChart) {
>     initCategoryChart();
> } else {
>     categoryChart.update();
> }
> ```
> 
> Tuy nhiên, `initRevenueChart` và `initCategoryChart` sử dụng `chartData.value` và `categoryDistribution.value` hiện tại, nhưng do tính chất bất đồng bộ của Vue, có thể dữ liệu chưa được cập nhật trước khi gọi. Nhưng trong loadData, chúng ta đã gán `chartData.value = ...` trước khi gọi init, nên OK.
> 
> Ngoài ra, cũng cần lưu ý rằng `categoryDistribution` từ controller hiện đang trả về mảng object {label, value}, nhưng Vue đang mong đợi một mảng số (vì khởi tạo với [0,0,0] và sử dụng trong chart với data: categoryDistribution.value). Nếu controller trả về mảng object, chart sẽ bị lỗi. Cần đảm bảo controller trả về mảng số, hoặc Vue xử lý.
> 
> Trong controller `getCategoryDistribution` đã sửa thành trả về mảng các object có label và value, nhưng ở Vue, `categoryDistribution` ban đầu là mảng số. Vậy hiện tại controller trả về object mà Vue không xử lý, nên chart sẽ lỗi. Ta có thể sửa Vue để nhận mảng object đó, hoặc sửa controller để trả về mảng số (chỉ lấy giá trị). Tạm thời, nếu muốn giữ nhãn động, ta sẽ sửa Vue.
> 
> Nhưng để đơn giản, tôi khuyên giữ nguyên controller trả về mảng số (như cũ) và không thay đổi. Vậy nên ta sẽ revert `getCategoryDistribution` về phiên bản trả về mảng số, vì Vue hiện đang dùng mảng số.
> 
> Do đó, trong controller, sửa lại `getCategoryDistribution` trả về mảng số như cũ.
> 
> Hoặc nếu muốn giữ label động, sửa Vue:
> 
> - `categoryDistribution` sẽ là mảng object.
> - Trong `initCategoryChart`, lấy labels từ dữ liệu.
> - Trong template không cần hiển thị gì thêm.
> 
> Tuy nhiên, để nhanh chóng, tôi khuyên giữ controller trả về mảng số.
> 
> Vậy ta sẽ giữ `getCategoryDistribution` như cũ (trả về mảng số). Còn trong controller hiện tại, tôi đã sửa thành trả về object nhưng chưa revert. Nên cần sửa lại.
> 
> Tóm lại, các bước sửa:
> 
> 1. **Sửa `loadDataByPeriod`** để khởi tạo chart nếu chưa tồn tại.
> 2. **Đảm bảo `categoryDistribution` là mảng số** (có thể sửa controller hoặc xử lý trong Vue).
> 
> Tôi sẽ đưa ra phiên bản sửa cuối cùng cho cả controller và Vue.
> 
> ---
> 
> **Controller:** sửa `getCategoryDistribution` về phiên bản cũ (trả về mảng số) để khớp với Vue.
> 
> ```php
> private function getCategoryDistribution($startDate, $endDate)
> {
>     $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
>     if ($total == 0) {
>         return [0, 0, 0];
>     }
> 
>     $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
>         ->join('order_details', 'orders.id', '=', 'order_details.order_id')
>         ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
>         ->join('products', 'product_variants.product_id', '=', 'products.id')
>         ->join('categories', 'products.category_id', '=', 'categories.id')
>         ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
>         ->groupBy('categories.name')
>         ->get();
> 
>     $top3 = $categories->sortByDesc('revenue')->take(3)->values();
>     $result = [];
>     for ($i = 0; $i < 3; $i++) {
>         $result[] = isset($top3[$i]) ? round($top3[$i]->revenue / $total * 100) : 0;
>     }
>     return $result;
> }
> ```
> 
> **Vue:** sửa `loadDataByPeriod` để khởi tạo chart nếu chưa tồn tại.
> 
> ```js
> // Trong loadDataByPeriod, sau khi cập nhật dữ liệu:
> if (revenueChart) {
>     revenueChart.data.labels = chartData.value.labels;
>     revenueChart.data.datasets[0].data = chartData.value.retail;
>     revenueChart.data.datasets[1].data = chartData.value.wholesale;
>     revenueChart.data.datasets[2].data = chartData.value.preorder;
>     revenueChart.update();
> } else {
>     initRevenueChart();
> }
> 
> if (categoryChart) {
>     categoryChart.data.datasets[0].data = categoryDistribution.value;
>     categoryChart.update();
> } else {
>     initCategoryChart();
> }
> ```
> 
> Ngoài ra, cần đảm bảo `categoryDistribution.value` là mảng số.
> 
> Đó là giải pháp. Tôi sẽ viết lại toàn bộ file Vue với những thay đổi này.

Sau khi kiểm tra, nguyên nhân chính dẫn đến việc bấm nút period không cập nhật dữ liệu là **biểu đồ chưa được khởi tạo** khi dữ liệu mới về (nếu khởi tạo sau lần gọi API đầu tiên). Ngoài ra, `categoryDistribution` từ controller có thể trả về mảng object, nhưng Vue đang mong đợi mảng số → gây lỗi.

---

### 1. Sửa `ReportController` – đảm bảo `categoryDistribution` là mảng số

```php
private function getCategoryDistribution($startDate, $endDate)
{
    $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
    if ($total == 0) {
        return [0, 0, 0];
    }

    $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
        ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
        ->groupBy('categories.name')
        ->get();

    $top3 = $categories->sortByDesc('revenue')->take(3)->values();
    $result = [];
    for ($i = 0; $i < 3; $i++) {
        $result[] = isset($top3[$i]) ? round($top3[$i]->revenue / $total * 100) : 0;
    }
    return $result;
}
```

---

### 2. Cập nhật `Reports.vue` – xử lý khởi tạo biểu đồ đúng cách

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Props từ controller
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

// Dữ liệu từ props (khởi tạo ban đầu)
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

const categoryDistribution = ref(props.reportData?.categoryDistribution || [0, 0, 0]);

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryDistribution.value,
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

// Tải dữ liệu theo period (dùng axios gọi API JSON)
const loadDataByPeriod = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.data'), {
            params: { period: period.value }
        });

        const newData = response.data;
        if (newData) {
            // Cập nhật dữ liệu
            summaryData.value = newData.summary || summaryData.value;
            topProducts.value = newData.topProducts || [];
            topCustomers.value = newData.topCustomers || [];
            chartData.value = newData.chartData || chartData.value;
            categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
            
            // Nếu chart đã tồn tại, cập nhật dữ liệu; nếu chưa, khởi tạo lại
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
                categoryChart.data.datasets[0].data = categoryDistribution.value;
                categoryChart.update();
            } else {
                initCategoryChart();
            }
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    }
    setTimeout(() => {
        initRevenueChart();
        initCategoryChart();
    }, 100);
});
</script>
```

---

### 3. Kiểm tra các lỗi tiềm ẩn khác

- **Route name**: `route('admin.reports.data')` phải tồn tại (đã có).
- **Dữ liệu mặc định**: Nếu `props.reportData` rỗng, `loadDataByPeriod` sẽ được gọi, nhưng `onMounted` vẫn khởi tạo chart với dữ liệu rỗng → sẽ không có lỗi.
- **Xử lý `categoryDistribution`**: Nếu controller trả về mảng object, Vue sẽ bị lỗi vì `categoryDistribution.value` không phải mảng số. Đã sửa controller.

---

Với những thay đổi này, bấm nút Tuần/Tháng/Năm sẽ cập nhật dữ liệu và biểu đồ ngay lập tức.


## User:
07/16/2026 14:07

![image.png](data:image/webp;base64,UklGRqIiAABXRUJQVlA4IJYiAABwTQGdASrnBdQBPlEokkcjoqGhIDXYEHAKCWlu/r1+ff3HqHln59/eP6v/fP8P+//s39L/Vz9B/v36u/1n/+9kv+L/Iz/AdD7VH9V/CD4Av0P+H/yv+q/rz/av/79yGQOqvUB/Lv47/dv7N/iP+v+//rP+u/4D/S/4D8//iu7o/1393+AD+G/yv++f2v/M/9X+1//////LXywukv9B7EP5r/cP+P/e/3/+NX7Z/tf8B/lPz////yG/N/7h/uP8D/lv+38if5d/Ov9L/b/89/6v3/+sX2l/2h///ut/wD98Pb1gDVN8N/FPsR+gudSbb/sZ8AGwO4Fwv+Df2b+V/qr+Q+sJf4n8h+Mp+wH4AfIv/AP69+GXqY/QDpE/4d/Tvw+3wb+Af2/8RfaC/oP4B/AP8B6u/xX9kPYR/C/4j/a/6//Y/7l/W//34gHoT/oB/eP0590yxP9/fuAHDH8TexvI7fy9AH+O9QD/KdADnOf7B+sfuI/aD2AP6P/sfVA/x3//9wP+l/33/ze4B/Iv7b/5vXZ9hv+wf9b2Cv3L////x9wD//+oB/9pgGQ9/hsylNSHHJRm+ax/T+Ur5p/5/uDfrN/s8WBbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbLZbec7+1n4dfNv6+Xe18u9r5d7Xy72vl3tfLva+Xe18u9r5d7Xy72vl3tfLRQfor39fLva+Xe18u9r5d7Xy72vl3tfLva+Xe18u9r5d7Xy72vl3tfLvTmxZ0qhnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodza9Tp9BDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQsyr1oVDtlTTW00w8wj6CGdSodzhFBDOpUO5wighnUqHb/rWoGtYuO3t3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wigfLkjOngp2o2X7xs2VA2o+lWsVZHmizq1aJFIiLvlb+fR9I8lWRu43b6GEIZ1gLj6CGdSodzhFBDOpUO5wighnefIuoKdTvifeuLxK1irDCD6PpVrFWCQj6BZKYuXEM6lQ7nCKCGdSodzhFBDOpUO5wigpLbsVQ6/J9zdyrU/wHaahFBDOpUO5wighnUqHc4RQQzqVDufnsooeAWTi5RB4tYC4+ghnUqHc4RQQzqVDucIoIZ1Kh3POzW2Mtx9BDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lR+6DGfQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUaH5qGLWAuPoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnefI/HCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1MDPJV/yodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEOO4hisBcfQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDcu4DKLP6nWAuPoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wise7/lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoKS27FUO1NSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OF722L1OsBcfQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFRC3Ge5cQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5+eyiz+p1gLj6CGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucKyWvt4iKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh5J09y4hnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSoeIH0UhisBcfQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDO8+R+OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqYGeSr/lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIcdxDFYC4+ghnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCG5dwGUWf1OsBcfQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFY93/Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQUlt2KodqalQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucL3tsXqdYC4+ghnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKiFuM9y4hnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodz89lFn9TrAXH0EM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4VktfbxEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDyTp7lxDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ8QPopDFYC4+ghnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGd58j8cIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUwM8lX/Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQ47iGKwFx9BDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUENy7gMos/qdYC4+ghnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKx7v+VDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wigpLbsVQ7U1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4XvbYvU6wFx9BDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEVELcZ7lxDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7n57KLP6nWAuPoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wrJa+3iIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHknT3LiGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh4gfRSGKwFx9BDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM7z5H44RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpgZ4lwdBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQtDTMn0EM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqUWGKscDoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnSfds+xn0EM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQyfmhwfor39fLva+Xe18u9r5d7Xy72vl3tfLva+Xe18u9r5d7Xy72vl3tfLvXGBk9K5N3Vybmnj28/Ds5uDXzb+vl3tfLva+Xe18u9r5d7Xy72vl3tfLvTu4dzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEM6lQ7nCKCGdSodzhFBDOpUO5wighnUqHc4RQQzqVDucIoIZ1Kh3OEUEMQAA/vsl8UQY8DWbezG6tN2PF/nK1+bv+L/OVr83f8X+crX5u/4v85Wvzd/xf5ytfm7/i/zla/N3/F/nK1+bv+L/OVr83f8X+crX5u/4v85Wvzd/xf5ytfm7/i/zla/N3/F/nK1+bv+L/OVr83f8X+crX5u/4v85Wvzd/xf5ytfm7/i/zla/N3/F/nK1+bv+L/OVr83f8X+crX5u/4v85Wvzd/xf5KB1bnJxHMB7xTOswO7QJVxQmguNtk7cecTkT0Sb1QbQxd4YlUfHqZeL49TLxfHqZeL49TLxfHqZeL49TLxfHqZeL49TLxfHqZeL49Tv1yM0AF0qQzkVbI1+joZ+jC/3yr4OQrdOFlMRRlaMWJxHMB7xTOswO7QJVxQmguNtk7cecTkT0Sb1QbQxd4YlUfHqZeL49TLxfHqZeL49TLxfHqZeL49TLxfHqZeL49TLxfHqZeL49TuhZfS1g3z7ozV6wrD6YvNnpJozdbxUmxUfUDvgSm7yZXSVkdoHXDEai6AbpRT/sB/KNxdlRsAk8aMmMfuW8xrmdxHFv4T+BWwZrZmjvfhBtRcvCjw9txYiwiulg0CJxf/rRqBM/pPfooMST4bpRT/sB/KNxdlRsAk8aMmMfuW8xrmdxHFvuLlMG8+uJ4mxlciNM3jsZFK28g2IWeWS0eGUIYX8STi83no0k5DdI7CTDtiU5U5qyQGvzVmViOlFuL/Ou9rTs9O3AK1jMCEmIiOWQ0EgnmU4hXnnY7NkHNiF3kngwFWpzZmd8m4FecW4ZvpONSbR9p42NbgJZaOAYrbOlebKE9gl04D07C618lRmbdggsNsrlSMDTjtaal3gBXKaPupjVANMtpNknqE+UvXeYM5s68AjAc/SFB9JxVqlZ4cBXvhJKmrUIcq9qjQiU1aDiWPOn6xzuUCI2QAkciYYWcCb/9clvhj52ZrYG9G+TcCvOLcM30nGpNo+08bHL4yK2+2imlFjLYF93BDFbM2CQBKNvu++xkVX3DQ5fmiM5rgx1vOqTRHKw+Az7Ok8SHL8MtL0s77oS5bef71yaDIFV9D26Q/e4pfKI+1TSGsxSyV1GFqOobi83no0k5DdI1pN7fpKcqc1ZIDX5qzKxHSi3Rl/6ycKgf0U1IYzlDmXyqUQOopMFWL1qAMIDJAtc/JnA7pk1fNuNiEcy+lT2NmOq2mBoghbZiqThr5JkX2529mimiyTLITcvzRGc1wYvXni/1o42sZgQkxERyyGgkE8ynEK9KYC1ybwJ6gs837rRIpe1v75fZ4Mgn3VmUu8odnsiq/cgLyqpCl9RrEsLRNVZOlmDWiyegbxlp5XulHrhi5fRvwVK7Qi5XRKlwYcYHP+ySVHAgGGCagQbni8GAq1ObMzvk3Arzi3DN9JxqTaPtPGxwQBwwzgEeDYf0FceMZFfGtZmZ6XPGFTf21RHSit/Ek5OoLK2CB9hZNitF5VQf4szLcvcXM1WVbxjp//9WSnZLwYqee75qJmkfWCeg+TQPi0yiQO2fT1DzscbYS73mUHgreDqSTrTLw6fb33ov84p475U6InTdvEyL/vhf/gOoVng9ffGLPo8VB2sMceKDqJ8QEa4ov/uPQZmVoSiDP9PK1ONOtuoy4XlGdrXiFXx3pYyQ7Z3CIIQmJvSp4U8CUZwMQxGpV5jga84pCuubS7IL240aRGWLrRtyN4XwUauv54aA5XCVLVxBe5W+V1+I9CWanrUYo6hGPunEzeUzIvJv+w0YQn174zEYoP5+7yDAplKrJuvnZtef3ijwhoR68H5woccbN34ejTzU4GvcM0OUaqsSk4GE3LiX18QR0LaA+Jr4lFXb//6AQVXVBxsbwuEXrt0AqU/Hp5BPikCpbS8UkRPNPGFm86WgHTri0Cidlriq1y19H1mGzHFwUJAiXMvqXcBxlwsVcDeH/uaiJsgCScnop4ZqjjbkNdaGCsiN+DutFdQZ6PVBMm6P1bkJip/ntd25BrJk/xCu6+uWcjGA4y+VCaQRjBTwYoQdJmMxBRLiy/Tl530HBnmJYy5l8FkoerC2EQV4kmjVZIoP2vBIlEnCBA/9eG3cEKtZ6sE8///bShMKEuqFgdZ+jJYL8qFkSgKQYBru4i63OMAd5e9F/SnevpjUmLJ6KN9q6KARV3sHE1iWNfg5d76wFfviClYtMRNxIcJT3cs+9Mwim6wgNqImyzAwH+9ihSCXExdAyVoVak/1huvTL2aUhKMG72r9po8xmdgTWxEQOLg3iVdtquhmcZIQD68yQ/dlUn9Kk8pq6oE39byZsmWzkExCDdWVAMui063PPehF7m1qA/4Y7Kui/2blGGZLvgfMT9Hx/NUoU4F7E9Q5pSts+9M5z243izKf1m0ZgIzLG+wt/gMPQHwXQlLd82z4wn7vivwbEtRoI4F+dPIG3KRm0sLKg09CsiAM2mYOqpvFrOYLdNiBEV5onIjN03b2HEYq8lfp2jv0r1sdBPncAMa3KNNaYPnqANsYKMSc73fFPGwdodUXyxzN6kQV2VVFs0vD/cwKNcmSz83ZvZ0lLEaQ3s6JKLu4GlyHrPYvu+/rwfjviQISU6EZqev2rzUTcbOC6Q50745GPaSGKQNXmLZtDblUWWDBRijhRY7OtjljCZh5GxtOfYUz+yi/zXXCqXlv9bzL8A9bsVb/0Kjb0UDIDhZ6QK2igZAcLPSBP/IFh8WV1LIFYyAXEdHcL3nVN52WZV9E33KndwUfbyJtd/rcBP7BqrkWnAux5l8DDbtHzgfu/OfOElgmoS/PrbfayUrVRYaWg6JkkSimR2eOJKfInEXFVApux43jDiyjzkfnta0UxTxpfH9URE0YuR8drjwzPO0LKta/ODBrdk9Z7NWgqSpAknSOstBmhxe23INe4zslkrUvavZu6W2N5rsGUbjSNjhBrZ6yfHVAsUHKv9OuuU1ppWiA1nXcnLzR10w2esH1VT97azsT9nX9YWJwmA0nwIkz9LNgQ+yGqNEUxCQKZsXkWg6aVbmgoKZi5UYG7the7oYh6A0F/aZETGCM2/9ui8VrkLKQJ5pc8TIWGWCbW56c9z07yOUO5pNBHiFYe8810JLggNstt1xqFlhMYoVdPNLoC7mgR5Yz00jII7yj62JcIr2lBeWJEs61/Vvz1+GFIppReWuOQnLc1ebIcAChvgf2l1lU1x+EZlaygAPzc0OiMYh9ht4C2CRW1ocLyHwETuEgNgd5L9WJRyAxfHc6610UQJDzcSYuu0iU7p1DcYt9Y7hGJ4SeRcHY20RAy9Wt7yUG/yPBSJGrROM2y9Rjreh+h5jG/JriTzqyUhj2tp3S5q1WNSvxY2ZZ6+dsc0DkYvWa6p1B6QTz6W04zEoN7ZkV/ve/NBNbL3Y80Dd9g6MZaf3Kts8qQJ83Mj/0nx4mtjwb6vhdlcF/nNfWQFzwjef8/Q+ebGDBhyVkLk5l6BWyJ8duLdzz6Adp6Fd7JJso4oSiKTHQquS/lJlktE1/ACHINqx2Ng4le+5XAWR3WuFH6uGkOwbK2QNUzpXW4/9cKg4Fu9YGu6JR535qtuKtsm5V26PNByc6K/QB2VI4/kVy39aNhwZbKpnLb5PvSXAbbnL6REifhGJMvH7J+5brciXmfnnPDjdtgXLaI/iVTu5J/LWG7ZqZcZRd2fGm+SEe8m3jhwKlAKxseLHfzCSkfQLaQhEOCkxqsyxvHIP7qVgDPqMtBzaKg/VFbKQxW5mzw5+MzLGfM94mUWb8C7GBLG7zkz55efsMLiDLVOWdQ7Q2a5Hz8zY3YpJamMChFsNyfQOUyqqH2c5pNDjMyHOvgP/7ht3m28fYPUsnzkHk6Ar65GCkiQuhBWCoTdMpyfNkCtJni4fue4+lIVjvkTft7K+kcBufe05UylFsFl08+y9Qm5sSS+swOC75TnaWcxU+uYE6hlqpXMUgNyBLP41EM6FrBomMqTfgJAGOM+ZHOzm5WhRPLuoJ45TfdMMIaDFULdvioah/6PGU1cz7It7TJyGalh+0YzEb8Sc73fFPGwdwW4XQlLodJhGbn59PFqYrNHVv91dZa7Z3WePahC6IVQgViz8xNhSvmSvSU9H+q0aMrfPQ05riiLRy2puuBhy4P1RPwXXCE0x2Ywjjn1rTfrjxOw2BdcGVvpycP+bc5d/P2BZi7v2ZIWGOM/ByVeg0mCgpsTC+wHvlV8nnzuUOMC7R1GGVo/a8WSKOYUTh0eN+AeTwJ8RAPrOtlN5RG3jYs5Hgi86xp4sHA2VV1StyA/ogHjnAQat4qQpORFP8874kdcTUiJyzBskfRJOwfklMVaa0gfMr8XrVrUSYO5lz1G909ZYlodQuUCnM9f5eGnlNdI7ATaxsJpYvVyk7OOa1tuwcb3DB9G/weblb25e/x2Ki6VtIbM8x+F+ATw+wAA6dB2VVaZrKPnQcBejDqAhAppFj5USDKi142GQFZMFUq9KM0xMuT/E3ieFEEqHtguqpXOiVWC3lUQ9IaG/lRD/t+m/Yk9NP5QSzW/k6V1uoJnXYU5rIWcz9jcQAxegbj/AiiNghAACnOJ1VVvKtSkw9aBHbTgYn599cyRRM3CE8bQKI6UXw5n7DcN51Vf/tG0fV0/S8bOUK551n5inw5OldCYk/35YIS0KtPhOX7zqHXc33HtifjfFMnH4fv9ob6s09j5E9pOie2EcrEo/N9XmXacidsC50epi85a9Tjqc1r4BRqqQZxAGvcBkSm0pkB/4FfFv455QUUKqxqyO1d+zi5rx2kPq8/MZoZjWpL5KNF4mAcuKOSjOKrxWHqrmgHCva4Sprf167Ex37gUDoWg8qFJo7eTelNXmjYXWYht+3nBJydOeGODGy5Z2Zy/Ae3Vx2tQKRfo/SdlgFP4T6RzLZDWHSuYQeXZM3Z0ytkfu6gvdhyt6A+A71FybrHztNyoK7vty24okevg0N5Tr4DDLbVUSiC+VWOHgf/iIIe8ArYRik0lQyS8QnhoKDRf0cjC98nA+J+MpU93aT2xShJgsAPfqifYz84b74q7Nq9Y4+FZ1z0luQLmS5KniW5TAwoEw/PAgN+sMa/HSVtiYZCxkXrdRcC7+6P108pnxf2DlBHaOFby83kTJAlHIT434HMu+zUgL1AcDDAsGqmW0muCWFAtOfuJWFjq1P389d/J0PD9jZ6WBnsZdq28jgld0j6Lsb5MeN2w1ol7c6FX8VfVPfF/wIWIJP79uFhLfS8Ks4/Y4fxuXif92KlTDPL5znt4sv+0AanJvmGgo0VTYVAxhhq/CyfLwey5ygFfdY65AWE5UbeWWujaN7qWYsgrT78kJJimbRnCFVsrDnTK4R5SQjqHuZikAHXoDUHpDMf4ELEEn+cRnbRLPFvV+IPbUgLAk2Vl+FcC/OHLKMT/g4j3abTiLt7ewFKAvS7XNo+1TCWBrTaIaFuiAK70FP/OqLuBhra12bQ4D+Z9dbkzZVium9nTkPS1NDskSXcQvYIwMuNMZd6sYKMw+Z2iOf43o/xOhbUNNs4mXW62mGtmW8EV/JxNb1b8EUEUhw6em6FtBsqIBBMNT7PZke87SL3Kedl5t92mzEJrLP0WaV7OCMRi4coDS216qLLu6wjnSOJVfL1ZuPxa/Ly80U+HjNTeL7aLcDrt7Y7uEeD4yHNwfvnetTtNSsO2v/9ATG0ddDTohEJ5Ncz31En3z3Pn2FBupIbbmzs/i0yZBQi95Xby04GS4bdRdRV5S2nbY3FxwFDKatnINyA4hj5FLc4g2hF0riYBehyIKHBAznmo3eyhCcwAwl6oJ9Imc8bAaKCJGQ0lDmieWXjsQSi1IJGPt7ZqFDset0WLweZUzEDYCqW//5P6M+XnRPvdvIdPtLQKy8yLuWEhUnw5ECcOoihE1H3RB4exYmilEAHIoxGfCBxFd8vj56uemvvcP3DqLabKf/hhODHxZ0UzBmBgwC30ssp9fKEFwYS8phBH9lkb/O28gKOiQXR7LLqZvikVmgERyIa2bWZzheI26UvTpOcCl5FW2OGBpFdhvykNIP9V4MBoQO39XdMSvv9iUXf5ADTPNfvD5LZ1fVZnRsancR8VvDz8Eu2iP/vTPcSWrHUGE2s55PMagkKlBonGvyKjLaSpaLwjyzQg+aHQzNzxjf9KrT0XuAzApxUY0R7Ua01w7n7H1QBbbMT255iAG74apwCbhwyTIbsfYXxPSE4zbZe/XjKJxm2y9+vKgodH+jyRZ2bhANXwmxl+9oiZlsByccbLYgX2D2Co5jr75Yf1V+D8Ku+CVERbSPZaa5hlSPA1anfwGFLn3Ej8tyNMQUdQN8rSI1g+a1RDLOA26lNdLJcVVApW+mP+2t8kMzNK9GB3kIIibnflGhuDKIc39Xm7qKcdoED3Ye9+/tI1vn49PCuHG4nDE6sD8PBful2F6NXALroA2Q8nOWOTQ4KF9P0tExyZFzaft/zAP1KAfTWrTz6WMmXpCVdmju7LRTns2QSScUbVgrzMW61maTaJF73xBjRZN+qGysf/nbgorsyGS811bwhQCVvW/oSOiP9pD2eQissqeDC4qTz4j4lFuZwmLYWJhxKWMhTKg2bhMQb0Rs8Em0WKWxPmzaUQNt1p9kMRf+AhOQv1XrOcujLhcEBNttP9VG7A6y4Tt1m2KYc8Z1u+ZonTy65QX1o+8LnWOZCbXa9nqqN/YBmP5cDOmd+UZruTXW6vwnkz4VpnPZwm9NX25jY+9lBTWeYmFyGBzBRrBcIJ0TFzuf2imBEJITAzkBEVnEbjN+9QE1B5Rav2V8H1yOlwmUF1rUEgMBTmRz0lcHulGl1gbX/5/L6BbzyWrhLyRvGizDvrvwRHx3PqtBL84ESMX6aYW6sQOtGz+E8EnPbqY4S9ajxH7NJZhIwQRAa2fMQrOOHsY177PuUVBk9Qsg0CcEOn9TXPCU2mo7vCUAxqHi5xnsTqc6hoenhylT7tOmsH5XWRox1SchMrd5wTW8D4DaeQgvM9OKXPJNm0OAmbwR4g9Hu5CvY8n6TgC7i03qiyn9rXbn/lD//FvX59OvSuAW26CzQ8z32qtyeEYQeSww4REMtceegxPYPC6R+VmURY14wch6h7usZyECq07FX9eDbYUXIwv2exiQLcbPxXdKUNkgBs9uHX8ucEX8Pukz8foAA5OeapVQ9sAVtFZz/mrQ2n+AFofG59A6UoY5YyOdBjdZ+KPN7QUhJsl+qeZUK6B/CnD0KOilrs/cMv1AwA+qziulWXhHcGXrVNgCaV1GDzdsP0puxlsP1ruQAiZY4lojS7tEVfGpPuGDLXwSbMX+yEthSeAguQ57YwGGSJCQ/z+NdKG0RRQsQF9ezqqzlDt5EA0gcD1uUraKBdujpgsdh9A0IwDisKeIjtfa/lPKafkZREM23ZiJwffq1j9JD79Wt47eQBA82wlZ0BTEZcjDAuqI6StV0x6nEr5GGAnzlUR0mctdyB8rpj1OJXyMMBPnKojpM5a7kD5XTHqcSvkYYCfOVRHSZy13IHyumPU4lfIwwE+cqiOkzlruQPldMepxK+RhgJ85VEdJnLXcgfK6Y9TiV8jDAT5yqI6TOWu5A+V0x6nEr5GGAnzlUR0mctdyB8rpj1OJXyMMBPnKojpM5a7kD5XTHqcSvkYYCfOVRHSZy13IHyumPU4lfIwwE+sKb2vzW80ZZIP+1Q0LymLbQoErvm8wSoZ5GGDXb765O6YFl1RjLzUETIyWs04deCFGrWFE8xtI6KKBkRERlkg/0dyEjfpF9o5qW9XNAYuJ3qcYr/mZFPWYGm2ZvA4ezdjoG6oexVTZz1a8eZ90hABf+dDzfi0Rkvwu7scEhEtio44VNN0ZI6mIOsLe9e23va/fj/DDb10NSc3y5qbHg4thuRUYasurtvaF8fVnTRjb/8oyS+L/YYBXK5qNwHsVU2c9WvHmfdIQAX/nQ834tEZL8Lu7HBIRLYqOOFTTdGSOpiDrC3vXtt72v384jxtQumrL7n5Gm36VXxVl5fPuBIFw233nRvDABkkdD5AKCQUznYv/B9FZ7VPjWCYZrNzkY4Jf96vRaBQlY/OcG33YztdjO12M7XYztdjO12M7XYztdjO12M7XYztdjO12M7XYyuCTfU76KqWrVnoXVec4pFg3rD3EOOfY2MawTDNZucjHBL/vV6LQKErH5zg2+7GdrsZ2uxna7GdrsZ2uxna7GdrsZ2uxna7GdrsZ2uxna7GXxz7G3Z7bMO3eH8RCtXM4xwS+URCzhlKk/NwAAA)

Chart bị mất khi chuyển tab tuần/tháng/năm


## Assistant:
07/16/2026 14:07

> **Thinking:**
> Chúng ta cần xử lý việc biểu đồ bị mất khi chuyển tab tuần/tháng/năm. Vấn đề này thường xảy ra do:
> 
> 1. **DOM element `canvas` không tồn tại** khi gọi `initChart` (ví dụ: canvas chưa được render do `v-if` hoặc v-show).
> 2. **Gọi `destroy()` rồi tạo mới** nhưng bị sai thứ tự.
> 3. **Không gọi `initChart` sau khi dữ liệu cập nhật**.
> 
> Trong code hiện tại, chúng ta gọi `initRevenueChart()` và `initCategoryChart()` trong `onMounted` và khi `loadDataByPeriod` cập nhật dữ liệu, chúng ta chỉ gọi `update()` nếu chart đã tồn tại, hoặc khởi tạo lại nếu chưa. Điều này đã được xử lý.
> 
> Tuy nhiên, có thể `canvas` đã bị ghi đè hoặc không tìm thấy sau khi thay đổi period. Để an toàn, ta nên:
> 
> - **Luôn hủy và tạo mới biểu đồ** mỗi khi dữ liệu thay đổi thay vì chỉ `update()`.
> - Đảm bảo canvas tồn tại trước khi khởi tạo.
> 
> Sửa lại `loadDataByPeriod`:
> 
> ```javascript
> const loadDataByPeriod = async () => {
>     isLoading.value = true;
>     try {
>         const response = await axios.get(route('admin.reports.data'), {
>             params: { period: period.value }
>         });
> 
>         const newData = response.data;
>         if (newData) {
>             // Cập nhật dữ liệu
>             summaryData.value = newData.summary || summaryData.value;
>             topProducts.value = newData.topProducts || [];
>             topCustomers.value = newData.topCustomers || [];
>             chartData.value = newData.chartData || chartData.value;
>             categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
>             
>             // Hủy và tạo lại biểu đồ
>             setTimeout(() => {
>                 initRevenueChart();
>                 initCategoryChart();
>             }, 50);
>         }
>     } catch (error) {
>         console.error('Lỗi tải dữ liệu:', error);
>     } finally {
>         isLoading.value = false;
>     }
> };
> ```
> 
> Ngoài ra, đảm bảo các canvas có `id` duy nhất và không bị ẩn.
> 
> **Lưu ý**: Đảm bảo rằng `<canvas>` được render (không bị `v-if` ẩn) khi gọi `initChart`. Nếu dùng `v-if` cho phần chứa biểu đồ, hãy đổi thành `v-show` để DOM luôn tồn tại.
> 
> Hãy kiểm tra lại template: biểu đồ nằm trong `<div v-else>` – vậy nếu `isLoading` là `false` thì biểu đồ mới hiện. Khi bấm period, `isLoading` đặt thành `true` nên biểu đồ bị ẩn, sau khi load xong `isLoading` là `false` và biểu đồ hiện lại, nhưng lúc đó canvas đã được tạo lại → cần gọi `initChart` sau khi `isLoading` về `false`. Trong code hiện tại, chúng ta gọi `initChart` ngay khi dữ liệu về, nhưng biểu đồ vẫn bị ẩn (vì `isLoading` đang `true`), đến khi `finally` đặt `isLoading = false` thì biểu đồ hiện ra nhưng không có chart.
> 
> Do đó, **cần gọi `initChart` sau khi `isLoading` được set là `false`**.
> 
> Sửa lại:
> 
> ```javascript
> const loadDataByPeriod = async () => {
>     isLoading.value = true;
>     try {
>         const response = await axios.get(route('admin.reports.data'), {
>             params: { period: period.value }
>         });
> 
>         const newData = response.data;
>         if (newData) {
>             // Cập nhật dữ liệu
>             summaryData.value = newData.summary || summaryData.value;
>             topProducts.value = newData.topProducts || [];
>             topCustomers.value = newData.topCustomers || [];
>             chartData.value = newData.chartData || chartData.value;
>             categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
>         }
>     } catch (error) {
>         console.error('Lỗi tải dữ liệu:', error);
>     } finally {
>         isLoading.value = false;
>         // Sau khi isLoading = false, DOM sẽ hiển thị lại canvas, nên khởi tạo chart
>         setTimeout(() => {
>             initRevenueChart();
>             initCategoryChart();
>         }, 100); // delay để đảm bảo DOM đã render lại
>     }
> };
> ```
> 
> Như vậy, chart sẽ được khởi tạo sau khi `isLoading` false, đảm bảo canvas tồn tại. Đây là cách khắc phục chính xác cho lỗi "biểu đồ bị mất khi chuyển tab".

Đúng vậy, nguyên nhân chính là khi `isLoading` được đặt thành `true`, phần `v-else` của biểu đồ bị ẩn (vì template dùng `v-if` / `v-else`). Khi dữ liệu về, `isLoading` được đặt thành `false`, biểu đồ hiện lại nhưng **không có biểu đồ nào được vẽ** vì các hàm khởi tạo đã chạy trước khi DOM được render lại.

Sửa như sau:

---

### **Sửa trong `loadDataByPeriod`**

```javascript
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
            categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
        // Đợi DOM render lại, sau đó khởi tạo biểu đồ
        await nextTick();
        initRevenueChart();
        initCategoryChart();
    }
};
```

Cần import `nextTick` từ Vue:

```javascript
import { ref, onMounted, watch, nextTick } from 'vue';
```

---

### **Tại sao phải dùng `nextTick`?**

- Khi `isLoading.value = false`, Vue sẽ cập nhật DOM (hiển thị lại biểu đồ).
- `nextTick` đảm bảo callback chạy **sau khi DOM đã được cập nhật**, do đó canvas đã tồn tại.

---

### **Nếu vẫn mất biểu đồ**

Đảm bảo rằng các canvas có **`id` duy nhất** và không bị thay đổi. Ngoài ra, có thể thêm logic kiểm tra:

```javascript
const initRevenueChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) {
        console.warn('Canvas revenueByTypeChart not found');
        return;
    }
    // ... rest
};
```

Và gọi `initRevenueChart` và `initCategoryChart` trong `finally` như trên.

---

Với cách này, mỗi lần đổi period, biểu đồ sẽ được vẽ lại chính xác sau khi dữ liệu cập nhật và DOM hiển thị xong.


## User:
07/16/2026 14:45

Class "Maatwebsite\Excel\Facades\Excel" not found


## Assistant:
07/16/2026 14:45

> **Thinking:**
> Chúng ta cần cài đặt và cấu hình package `maatwebsite/excel` để sử dụng Facade `Excel`. Lỗi này xuất hiện vì bạn đã sử dụng `use Maatwebsite\Excel\Facades\Excel;` nhưng package chưa được cài đặt hoặc chưa đăng ký alias.
> 
> **Giải pháp:**
> 
> 1. Cài đặt package:
> ```bash
> composer require maatwebsite/excel
> ```
> 
> 2. Nếu bạn đang dùng Laravel 11 trở lên, package sẽ tự động đăng ký service provider và alias. Nếu không, thêm vào `config/app.php`:
> ```php
> 'providers' => [
>     // ...
>     Maatwebsite\Excel\ExcelServiceProvider::class,
> ],
> 'aliases' => [
>     // ...
>     'Excel' => Maatwebsite\Excel\Facades\Excel::class,
> ],
> ```
> 
> 3. Chạy lệnh publish config (tùy chọn):
> ```bash
> php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
> ```
> 
> 4. Xóa cache config:
> ```bash
> php artisan config:clear
> ```
> 
> Sau đó kiểm tra lại. Nếu vẫn lỗi, hãy kiểm tra file `composer.json` đã có `"maatwebsite/excel": "^3.1"` chưa. Nếu chưa, thêm vào và chạy `composer update`.
> 
> Nếu bạn muốn tạm thời bỏ qua phần export Excel để test các phần khác, bạn có thể comment hoặc xóa code liên quan đến `Excel::download` trong `export()` method của `ReportController`, và trả về một response đơn giản.
> 
> Tuy nhiên, vì đây là yêu cầu chức năng, bạn nên cài đặt package.

Bạn cần cài đặt package `maatwebsite/excel` để sử dụng Facade `Excel`. Dưới đây là hướng dẫn chi tiết:

---

### 1. Cài đặt package

```bash
composer require maatwebsite/excel
```

---

### 2. Đăng ký Service Provider và Alias (nếu Laravel < 11)

Trong file `config/app.php`, thêm vào mảng `providers`:

```php
'providers' => [
    // ...
    Maatwebsite\Excel\ExcelServiceProvider::class,
],
```

Và vào mảng `aliases`:

```php
'aliases' => [
    // ...
    'Excel' => Maatwebsite\Excel\Facades\Excel::class,
],
```

> **Lưu ý:** Với Laravel 11 trở lên, package tự động đăng ký (auto-discovery) nên không cần thêm thủ công.

---

### 3. Xuất bản cấu hình (tùy chọn)

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

---

### 4. Xóa cache cấu hình

```bash
php artisan config:clear
```

---

### 5. Kiểm tra lại

Sau khi cài đặt, bạn có thể sử dụng `Excel::download()` trong controller.

---

### Tạm thời bỏ qua lỗi này để test các phần khác

Nếu bạn muốn test phần hiển thị báo cáo mà không quan tâm đến xuất Excel, bạn có thể comment hoặc sửa tạm method `export` trong `ReportController`:

```php
public function export(Request $request)
{
    // Tạm thời trả về thông báo thay vì download file
    return response()->json(['message' => 'Tính năng xuất Excel đang được phát triển']);
}
```

Sau đó, khi cài đặt xong, bạn khôi phục code cũ.

---

**Kết luận:** Lỗi `Class "Maatwebsite\Excel\Facades\Excel" not found` là do chưa cài package. Hãy cài đặt theo hướng dẫn trên.


## User:
07/16/2026 14:51

<!-- Badge giảm giá -->
                  <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                    {{ product.badge }}
                  </span>
                  
                  <!-- Badge Pre-order -->
                  <span v-if="product.is_preorder" class="absolute top-4 right-4 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">
                    Pre-Order
                  </span>
Điều chỉnh để 2 badge hiển thị kế nhau nếu cùng tồn tại


## Assistant:
07/16/2026 14:51

> **Thinking:**
> Vấn đề: Khi vừa có badge giảm giá và badge pre-order, cả hai đều được đặt ở vị trí top, nhưng khác bên trái/phải. Hiện tại code sử dụng `absolute top-4` nhưng không có sự điều chỉnh khi cả hai xuất hiện. Để hai badge hiển thị kế nhau khi cùng tồn tại, có thể đặt chúng trong cùng một container và sắp xếp theo chiều ngang.
> 
> Có thể sử dụng flex container với position tuyệt đối để chứa cả hai badge. Điều chỉnh:
> 
> ```vue
> <!-- Badge container -->
> <div class="absolute top-4 left-4 flex gap-2">
>   <!-- Badge giảm giá -->
>   <span v-if="product.badge" class="px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
>     {{ product.badge }}
>   </span>
>   <!-- Badge Pre-order -->
>   <span v-if="product.is_preorder" class="px-3 py-1 text-xs rounded-full bg-purple-600 text-white font-bold">
>     Pre-Order
>   </span>
> </div>
> ```
> 
> Như vậy, nếu có cả hai, chúng sẽ xếp cạnh nhau; nếu chỉ có một, nó vẫn ở vị trí top-left. Nếu cần giữ vị trí riêng cho từng badge (vẫn left và right như cũ) thì có thể giữ nguyên, nhưng vấn đề có thể là chúng bị chồng lên nếu cùng lúc. Cách tiếp cận container flex sẽ giải quyết.
> 
> Đề xuất sửa code như trên. Tùy vào bố cục mong muốn mà điều chỉnh.

Để hai badge hiển thị cạnh nhau khi cùng tồn tại, bạn có thể đặt chúng trong cùng một container với `flex` và `gap`. Dưới đây là cách sửa:

```vue
<!-- Badge container -->
<div class="absolute top-4 left-4 flex flex-wrap gap-2">
  <!-- Badge giảm giá -->
  <span v-if="product.badge" class="px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
    {{ product.badge }}
  </span>
  <!-- Badge Pre-order -->
  <span v-if="product.is_preorder" class="px-3 py-1 text-xs rounded-full bg-purple-600 text-white font-bold">
    Pre-Order
  </span>
</div>
```

**Giải thích:**
- Dùng `flex` và `gap-2` để hai badge nằm cạnh nhau, cách nhau một khoảng nhỏ.
- `flex-wrap` đảm bảo nếu màn hình hẹp, badge sẽ xuống dòng.
- Bỏ `right-4` khỏi badge pre-order, đặt cả hai vào cùng container top-left.

Nếu bạn muốn pre-order vẫn ở bên phải nhưng cạnh badge giảm giá, có thể dùng `flex-row-reverse` hoặc điều chỉnh `justify-end`. Tuy nhiên, cách trên đơn giản và đẹp nhất.


## User:
07/16/2026 15:05

public function export(Request $request)
    {
        $period = $request->input('period', 'week');

        // Lọc đơn hàng theo period
        $query = Order::query();
        $startDate = $this->getStartDate($period);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $orders = $query->latest()->get();

        // Sử dụng Export class có sẵn (hoặc tạo mới)
        return Excel::download(new OrdersExport('all', $orders), 'bao_cao_thong_ke_' . $period . '.xlsx');
    }

Hãy tạo 1 class mới để export thống kê doanh thu đang được hiển thị thay vì sử dụng orderExport


## Assistant:
07/16/2026 15:05

> **Thinking:**
> Chúng ta cần tạo một Export class mới để xuất báo cáo thống kê đang hiển thị trên trang Reports, thay vì sử dụng OrdersExport. OrdersExport hiện tại xuất danh sách đơn hàng chi tiết, nhưng báo cáo thống kê của chúng ta gồm các chỉ số tổng hợp: doanh thu theo loại hình, top sản phẩm, top khách hàng, phân bố danh mục, v.v.
> 
> Ta sẽ tạo một class mới, ví dụ: `ReportExport`, implement các interface `FromCollection, WithHeadings, WithStyles, ShouldAutoSize`. Class này sẽ nhận dữ liệu báo cáo và xuất ra Excel với các sheet hoặc các bảng tương ứng.
> 
> Cấu trúc export mong muốn:
> - Sheet 1: Tổng quan: Doanh thu theo loại hình (retail, wholesale, preorder) với giá trị và tăng trưởng.
> - Sheet 2: Biểu đồ dữ liệu: Doanh thu theo tuần/tháng/năm (labels và các cột).
> - Sheet 3: Top sản phẩm: Tên, số lượng bán, doanh thu.
> - Sheet 4: Top khách hàng: Tên, số đơn, tổng chi tiêu.
> - Sheet 5: Phân bố danh mục: Danh mục và phần trăm.
> 
> Ta có thể tạo một class duy nhất với nhiều sheet, hoặc tạo nhiều class riêng. Vì đơn giản, ta sẽ tạo một class `ReportExport` với phương thức `collection` trả về một collection các dòng dữ liệu tóm tắt, nhưng để có nhiều sheet, ta cần dùng `WithMultipleSheets`.
> 
> Tôi sẽ hướng dẫn tạo một Export với nhiều sheet.
> 
> **Bước 1: Tạo class `ReportExport`**
> 
> ```bash
> php artisan make:export ReportExport --model=Order
> ```
> 
> Nhưng vì không dùng model trực tiếp, ta sẽ sửa thủ công.
> 
> **Bước 2: Viết class với `WithMultipleSheets`**
> 
> ```php
> <?php
> 
> namespace App\Exports;
> 
> use Maatwebsite\Excel\Concerns\WithMultipleSheets;
> 
> class ReportExport implements WithMultipleSheets
> {
>     protected $period;
>     protected $reportData;
> 
>     public function __construct($period, $reportData)
>     {
>         $this->period = $period;
>         $this->reportData = $reportData;
>     }
> 
>     public function sheets(): array
>     {
>         return [
>             new ReportSummarySheet($this->reportData['summary']),
>             new ReportChartSheet($this->reportData['chartData'], $this->period),
>             new ReportTopProductsSheet($this->reportData['topProducts']),
>             new ReportTopCustomersSheet($this->reportData['topCustomers']),
>             new ReportCategorySheet($this->reportData['categoryDistribution']),
>         ];
>     }
> }
> ```
> 
> **Bước 3: Tạo các sheet class tương ứng.**
> 
> Mỗi sheet sẽ implement `FromCollection, WithHeadings, ShouldAutoSize, WithStyles`.
> 
> Ví dụ: `ReportSummarySheet`:
> 
> ```php
> <?php
> 
> namespace App\Exports;
> 
> use Maatwebsite\Excel\Concerns\FromCollection;
> use Maatwebsite\Excel\Concerns\WithHeadings;
> use Maatwebsite\Excel\Concerns\ShouldAutoSize;
> use Maatwebsite\Excel\Concerns\WithStyles;
> use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
> 
> class ReportSummarySheet implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
> {
>     protected $summary;
> 
>     public function __construct($summary)
>     {
>         $this->summary = $summary;
>     }
> 
>     public function collection()
>     {
>         return collect([
>             [
>                 'Bán lẻ',
>                 number_format($this->summary['retail']['revenue']) . '₫',
>                 $this->summary['retail']['growth'] . '%',
>             ],
>             [
>                 'Bán sỉ',
>                 number_format($this->summary['wholesale']['revenue']) . '₫',
>                 $this->summary['wholesale']['growth'] . '%',
>             ],
>             [
>                 'Pre-order',
>                 number_format($this->summary['preorder']['revenue']) . '₫',
>                 $this->summary['preorder']['growth'] . '%',
>             ],
>         ]);
>     }
> 
>     public function headings(): array
>     {
>         return ['Loại hình', 'Doanh thu hôm nay', 'Tăng trưởng'];
>     }
> 
>     public function styles(Worksheet $sheet)
>     {
>         return [
>             1 => ['font' => ['bold' => true]],
>         ];
>     }
> }
> ```
> 
> Tương tự cho các sheet khác: `ReportChartSheet`, `ReportTopProductsSheet`, `ReportTopCustomersSheet`, `ReportCategorySheet`.
> 
> **Bước 4: Sửa phương thức `export` trong `ReportController`:**
> 
> ```php
> public function export(Request $request)
> {
>     $period = $request->input('period', 'week');
>     // Lấy dữ liệu báo cáo
>     $reportData = $this->getReportData($period);
> 
>     return Excel::download(new ReportExport($period, $reportData), 'bao_cao_thong_ke_' . $period . '.xlsx');
> }
> ```
> 
> **Lưu ý:** Đảm bảo đã import đúng namespace.
> 
> Với cách này, file Excel xuất ra sẽ có 5 sheet tương ứng với các phần dữ liệu thống kê.
> 
> **Cài đặt thêm style cho các sheet nếu muốn đẹp hơn.** Mỗi sheet có thể custom style riêng.
> 
> Bạn có thể viết tất cả vào một file hoặc tạo từng file riêng để dễ quản lý. Tôi khuyên nên tạo thư mục `Exports/ReportSheets` để chứa các sheet con.

Ta sẽ tạo một Export class mới để xuất báo cáo thống kê tổng hợp, không phải danh sách đơn hàng chi tiết. Dưới đây là hướng dẫn chi tiết:

---

## 1. Tạo Export class mới: `ReportExport`

```bash
php artisan make:export ReportExport
```

---

## 2. Nội dung file `app/Exports/ReportExport.php`

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $period;
    protected $reportData;

    public function __construct($period, $reportData)
    {
        $this->period = $period;
        $this->reportData = $reportData;
    }

    public function title(): string
    {
        return 'Báo cáo thống kê';
    }

    public function headings(): array
    {
        return [
            'Chỉ tiêu',
            'Bán lẻ',
            'Bán sỉ',
            'Pre-order'
        ];
    }

    public function array(): array
    {
        $summary = $this->reportData['summary'];
        $chartData = $this->reportData['chartData'];
        $topProducts = $this->reportData['topProducts'];
        $topCustomers = $this->reportData['topCustomers'];
        $categoryDistribution = $this->reportData['categoryDistribution'];

        $rows = [];

        // 1. Tiêu đề thời gian
        $periodLabel = $this->getPeriodLabel($this->period);
        $rows[] = ['BÁO CÁO THỐNG KÊ - ' . strtoupper($periodLabel)];
        $rows[] = []; // dòng trống

        // 2. Tổng quan doanh thu
        $rows[] = ['I. TỔNG QUAN DOANH THU'];
        $rows[] = ['Doanh thu hôm nay', 
            number_format($summary['retail']['revenue']) . '₫',
            number_format($summary['wholesale']['revenue']) . '₫',
            number_format($summary['preorder']['revenue']) . '₫'
        ];
        $rows[] = ['Tăng trưởng so với hôm qua',
            $summary['retail']['growth'] . '%',
            $summary['wholesale']['growth'] . '%',
            $summary['preorder']['growth'] . '%'
        ];
        $rows[] = []; // dòng trống

        // 3. Biểu đồ doanh thu theo ngày/tuần/tháng
        $rows[] = ['II. DOANH THU THEO THỜI GIAN'];
        $labels = $chartData['labels'];
        $retail = $chartData['retail'];
        $wholesale = $chartData['wholesale'];
        $preorder = $chartData['preorder'];

        // Header cho biểu đồ
        $header = ['Kỳ'];
        foreach ($labels as $label) {
            $header[] = $label;
        }
        $rows[] = $header;
        
        // Dòng dữ liệu: Bán lẻ
        $rowRetail = ['Bán lẻ'];
        foreach ($retail as $value) {
            $rowRetail[] = number_format($value) . '₫';
        }
        $rows[] = $rowRetail;

        // Bán sỉ
        $rowWholesale = ['Bán sỉ'];
        foreach ($wholesale as $value) {
            $rowWholesale[] = number_format($value) . '₫';
        }
        $rows[] = $rowWholesale;

        // Pre-order
        $rowPreorder = ['Pre-order'];
        foreach ($preorder as $value) {
            $rowPreorder[] = number_format($value) . '₫';
        }
        $rows[] = $rowPreorder;
        $rows[] = []; // dòng trống

        // 4. Top sản phẩm
        $rows[] = ['III. TOP SẢN PHẨM BÁN CHẠY'];
        $rows[] = ['STT', 'Tên sản phẩm', 'Số lượng bán', 'Doanh thu'];
        foreach ($topProducts as $index => $product) {
            $rows[] = [
                $index + 1,
                $product['name'],
                $product['sold'],
                number_format($product['revenue']) . '₫'
            ];
        }
        $rows[] = []; // dòng trống

        // 5. Top khách hàng
        $rows[] = ['IV. TOP KHÁCH HÀNG THÂN THIẾT'];
        $rows[] = ['STT', 'Tên khách hàng', 'Số đơn hàng', 'Tổng chi tiêu'];
        foreach ($topCustomers as $index => $customer) {
            $rows[] = [
                $index + 1,
                $customer['name'],
                $customer['orders'],
                number_format($customer['total']) . '₫'
            ];
        }
        $rows[] = []; // dòng trống

        // 6. Phân bố danh mục
        $rows[] = ['V. PHÂN BỐ DANH MỤC'];
        $rows[] = ['Danh mục', 'Tỷ lệ (%)'];
        foreach ($categoryDistribution as $category) {
            $rows[] = [
                $category['label'] ?? 'Khác',
                $category['value'] ?? 0
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Merge tiêu đề
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF6B35'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Các tiêu đề phần (I, II, III...)
        $sectionRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (preg_match('/^[I,V]+\./', $cellValue)) {
                $sectionRows[] = $row->getRowIndex();
            }
        }

        foreach ($sectionRows as $rowIndex) {
            $sheet->getStyle('A' . $rowIndex . ':' . $highestColumn . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '2C3E50'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5'],
                ],
            ]);
        }

        // Style header bảng (dòng có STT, Tên sản phẩm...)
        $headerRows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            if (in_array($cellValue, ['STT', 'Kỳ', 'Danh mục'])) {
                $headerRows[] = $row->getRowIndex();
            }
        }

        foreach ($headerRows as $rowIndex) {
            $sheet->getStyle('A' . $rowIndex . ':' . $highestColumn . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Kẻ khung cho tất cả dữ liệu
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7'],
                ],
            ],
        ];

        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray($styleArray);

        // Tự động căn chỉnh cột
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    private function getPeriodLabel($period)
    {
        $labels = [
            'week' => 'Tuần',
            'month' => 'Tháng',
            'year' => 'Năm'
        ];
        return $labels[$period] ?? $period;
    }
}
```

---

## 3. Cập nhật `ReportController` để sử dụng class mới

```php
use App\Exports\ReportExport; // thêm use

public function export(Request $request)
{
    $period = $request->input('period', 'week');
    $reportData = $this->getReportData($period);

    return Excel::download(new ReportExport($period, $reportData), 'bao_cao_thong_ke_' . $period . '.xlsx');
}
```

---

## 4. Xóa method `getCategoryDistribution` trả về mảng object nếu chưa làm

Trong `ReportController`, đảm bảo `categoryDistribution` là mảng các object có `label` và `value` (nếu không, sửa lại). Hiện tại code của bạn đã đúng với cấu trúc này.

---

## 5. Cài đặt package Excel (nếu chưa)

```bash
composer require maatwebsite/excel
```

---

## Kết quả

File Excel xuất ra sẽ có 1 sheet duy nhất với các phần:

- **Tiêu đề** BÁO CÁO THỐNG KÊ - TUẦN
- **I. Tổng quan doanh thu**: Doanh thu hôm nay và tăng trưởng của 3 loại hình.
- **II. Doanh thu theo thời gian**: Bảng dữ liệu biểu đồ với các cột theo từng kỳ.
- **III. Top sản phẩm bán chạy**: Danh sách 5 sản phẩm.
- **IV. Top khách hàng thân thiết**: Danh sách 5 khách hàng.
- **V. Phân bố danh mục**: Tỷ lệ phần trăm của các danh mục.

File Excel có màu sắc và định dạng đẹp mắt. Bạn có thể tùy chỉnh thêm theo nhu cầu.


## User:
07/16/2026 15:07

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year

        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * API trả về dữ liệu dạng JSON (cho Vue gọi khi đổi period)
     */
    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

    /**
     * Xuất báo cáo Excel
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);

        return Excel::download(new ReportExport($period, $reportData), 'bao_cao_thong_ke_' . $period . '.xlsx');
    }

    // ==================== Phương thức lấy dữ liệu ====================

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // 1. Tổng quan doanh thu và tăng trưởng theo loại hình
        $summary = $this->getSummary($period);

        // 2. Dữ liệu biểu đồ doanh thu
        $chartData = $this->getChartData($period);

        // 3. Top sản phẩm bán chạy (theo doanh thu)
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);

        // 4. Top khách hàng (theo tổng chi tiêu)
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);

        // 5. Phân bố danh mục (tính theo % doanh thu)
        $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

        return [
            'summary' => $summary,
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categoryDistribution' => $categoryDistribution,
        ];
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }

    // ==================== Các helper chi tiết ====================

    /**
     * Tổng doanh thu và tăng trưởng của 3 loại hình
     */
    private function getSummary($period)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Nếu period là week, month, year thì có thể lấy doanh thu trong kỳ thay vì hôm nay
        // Ở đây ta vẫn lấy hôm nay để hiển thị trên thẻ
        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueToday = Order::where('order_code', $type)
                ->whereDate('created_at', $today)
                ->sum('final_amount');

            $revenueYesterday = Order::where('order_code', $type)
                ->whereDate('created_at', $yesterday)
                ->sum('final_amount');

            $growth = 0;
            if ($revenueYesterday > 0) {
                $growth = round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 1);
            } elseif ($revenueToday > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueToday,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    /**
     * Dữ liệu biểu đồ cột doanh thu theo tuần/tháng/năm
     */
    private function getChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } else { // year
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Top sản phẩm theo doanh thu
     */
    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                \DB::raw('SUM(order_details.quantity) as sold'),
                \DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                    'revenue' => (int) $item->revenue,
                ];
            });
    }

    /**
     * Top khách hàng theo tổng chi tiêu
     */
    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.customer_name as name',
                \DB::raw('COUNT(orders.id) as orders'),
                \DB::raw('SUM(orders.final_amount) as total')
            )
            ->groupBy('orders.customer_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
        if ($total == 0) {
            return [0, 0, 0];
        }

        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->get();

        $top3 = $categories->sortByDesc('revenue')->take(3)->values();
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = isset($top3[$i]) ? round($top3[$i]->revenue / $total * 100) : 0;
        }
        return $result;
    }

    /**
     * Chuyển đổi số thứ tự ngày sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}
Còn cần chỉnh gì không?


## Assistant:
07/16/2026 15:07

> **Thinking:**
> Chúng ta đã có controller hoàn chỉnh, export class mới, và frontend đã sửa. Tuy nhiên, vẫn còn một số điểm cần kiểm tra và tối ưu:
> 
> 1. **`categoryDistribution`**: Trong controller, `getCategoryDistribution` trả về mảng số nguyên (ví dụ `[45, 30, 25]`). Trong `ReportExport`, class này đang mong đợi `$categoryDistribution` là mảng các object với `label` và `value`. Nếu không sửa, export sẽ bị lỗi. Cần thống nhất giữa controller và export.
> 
> 2. **Phương thức `getCategoryDistribution`** hiện tại trả về mảng số, nhưng `ReportExport` lại xử lý nó như mảng object. Bạn cần sửa một trong hai.
> 
> 3. **Tối ưu query**: Các vòng lặp trong `getChartData` có thể gây ra nhiều truy vấn (lên đến 21 query). Có thể tối ưu bằng cách group by ngày, nhưng với dữ liệu nhỏ thì không đáng lo.
> 
> 4. **Kiểm tra `ReportExport`**: Đảm bảo class export xử lý đúng định dạng dữ liệu.
> 
> ---
> 
> **Đề xuất sửa:**
> 
> - Sửa `getCategoryDistribution` trong controller để trả về mảng object có `label` và `value`:
> 
> ```php
> private function getCategoryDistribution($startDate, $endDate)
> {
>     $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
>     if ($total == 0) {
>         return [['label' => 'Chưa có dữ liệu', 'value' => 100]];
>     }
> 
>     $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
>         ->join('order_details', 'orders.id', '=', 'order_details.order_id')
>         ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
>         ->join('products', 'product_variants.product_id', '=', 'products.id')
>         ->join('categories', 'products.category_id', '=', 'categories.id')
>         ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
>         ->groupBy('categories.name')
>         ->get();
> 
>     $result = $categories->map(function ($item) use ($total) {
>         return [
>             'label' => $item->name,
>             'value' => round($item->revenue / $total * 100)
>         ];
>     })->sortByDesc('value')->values()->take(3);
> 
>     if ($result->isEmpty()) {
>         return [['label' => 'Chưa có dữ liệu', 'value' => 100]];
>     }
> 
>     return $result;
> }
> ```
> 
> - Cập nhật `ReportExport` để xử lý đúng kiểu dữ liệu (nếu bạn giữ nguyên mảng số thì sửa export).
> 
> ---
> 
> **Tóm lại**: Controller hiện tại đã đủ, chỉ cần điều chỉnh đồng bộ dữ liệu giữa `getCategoryDistribution` và `ReportExport`. Ngoài ra, có thể thêm validation cho `period` để tránh lỗi.
> 
> Bạn có muốn tôi chỉnh sửa chi tiết file `ReportExport` hay controller? Hãy cho tôi biết.

Controller của bạn đã hoạt động tốt. Tuy nhiên, vẫn còn một số điểm cần chỉnh sửa để **đồng bộ dữ liệu** giữa controller và export class, cũng như tối ưu hiệu năng.

---

## 1. Sửa `getCategoryDistribution` trả về mảng object (để dùng chung cho cả Vue và Export)

```php
private function getCategoryDistribution($startDate, $endDate)
{
    $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
    if ($total == 0) {
        return [
            ['label' => 'Chưa có dữ liệu', 'value' => 100]
        ];
    }

    $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
        ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
        ->groupBy('categories.name')
        ->get();

    $result = $categories->map(function ($item) use ($total) {
        return [
            'label' => $item->name,
            'value' => round($item->revenue / $total * 100)
        ];
    })->sortByDesc('value')->values()->take(3);

    return $result->isEmpty() ? [['label' => 'Chưa có dữ liệu', 'value' => 100]] : $result;
}
```

Sau khi sửa, `categoryDistribution` sẽ là mảng các object `{ label, value }` để frontend và export có thể dùng chung.

---

## 2. Cập nhật `Reports.vue` để xử lý `categoryDistribution` mới

Trong Vue, `categoryDistribution` giờ là mảng object, bạn cần lấy `value` để vẽ biểu đồ:

```javascript
// Sửa khi khởi tạo categoryDistribution
const categoryDistribution = ref(props.reportData?.categoryDistribution || []);

// Khi cập nhật data từ API
categoryDistribution.value = newData.categoryDistribution || [];

// Khi vẽ biểu đồ doughnut, lấy giá trị value
const initCategoryChart = () => {
    // ...
    const data = categoryDistribution.value.map(item => item.value);
    categoryChart = new Chart(ctx, {
        data: {
            labels: categoryDistribution.value.map(item => item.label),
            datasets: [{
                data: data,
                // ...
            }]
        }
    });
};

// Khi update chart
if (categoryChart) {
    categoryChart.data.labels = categoryDistribution.value.map(item => item.label);
    categoryChart.data.datasets[0].data = categoryDistribution.value.map(item => item.value);
    categoryChart.update();
}
```

---

## 3. `ReportExport` – không cần sửa vì đã xử lý đúng mảng object (nếu bạn đã viết theo mẫu trước đó).

---

## 4. Tối ưu `getChartData` (giảm số lượng query)

```php
private function getChartData($period)
{
    $labels = [];
    $retail = [];
    $wholesale = [];
    $preorder = [];

    if ($period === 'week') {
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
            $retail[] = 0;
            $wholesale[] = 0;
            $preorder[] = 0;
        }
        // Lấy tất cả dữ liệu trong 7 ngày một lần
        $results = Order::where('created_at', '>=', Carbon::today()->subDays(6))
            ->select('order_code', \DB::raw('DATE(created_at) as date'), \DB::raw('SUM(final_amount) as total'))
            ->groupBy('order_code', 'date')
            ->get();
        // Map vào mảng
        foreach ($results as $row) {
            $index = array_search($row->date, $labelsByDate);
            if ($index !== false) {
                ${$row->order_code}[$index] = (int) $row->total;
            }
        }
    }
    // Tương tự cho month và year
}
```

Tuy nhiên, với dữ liệu nhỏ, bạn có thể giữ nguyên vòng lặp cho đơn giản.

---

## 5. Thêm validation cho `period`

```php
public function index(Request $request)
{
    $period = $request->input('period', 'week');
    if (!in_array($period, ['week', 'month', 'year'])) {
        $period = 'week';
    }
    // ...
}
```

---

## Kết luận

Controller hiện tại đã đủ để chạy. Bạn chỉ cần:

1. Sửa `getCategoryDistribution` để trả về mảng object.
2. Cập nhật Vue để xử lý dữ liệu mới.
3. Đảm bảo `ReportExport` xử lý đúng kiểu dữ liệu.

Nếu bạn làm theo các bước trên, cả trang báo cáo và file Excel sẽ hoạt động chính xác.


## User:
07/16/2026 15:08

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Props từ controller
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

// Dữ liệu từ props (khởi tạo ban đầu)
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

const categoryDistribution = ref(props.reportData?.categoryDistribution || [0, 0, 0]);

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (categoryChart) categoryChart.destroy();
    
    const categoryLabels = ['Balo', 'Cặp - Túi', 'Phụ kiện'];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryDistribution.value,
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

// Tải dữ liệu theo period (dùng axios gọi API JSON)
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
            categoryDistribution.value = newData.categoryDistribution || [0, 0, 0];
        }
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
        // Đợi DOM render lại, sau đó khởi tạo biểu đồ
        await nextTick();
        initRevenueChart();
        initCategoryChart();
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    }
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

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <div v-else>
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
Chỉnh lại


## Assistant:
07/16/2026 15:08

> **Thinking:**
> Chúng ta cần sửa Vue để xử lý `categoryDistribution` là mảng object (nếu controller đã sửa), và đảm bảo biểu đồ hiển thị lại khi đổi period. Ngoài ra, cần kiểm tra logic khởi tạo và cập nhật.
> 
> Dưới đây là file đã sửa hoàn chỉnh:
> 
> ```vue
> <script setup>
> import { ref, onMounted, watch, nextTick } from 'vue';
> import AdminLayout from '@/Layouts/AdminLayout.vue';
> import { Head, Link } from '@inertiajs/vue3';
> import axios from 'axios';
> 
> // Props từ controller
> const props = defineProps({
>     reportData: Object,
>     currentPeriod: String,
> });
> 
> // Period filter
> const period = ref(props.currentPeriod || 'week');
> const periods = ['week', 'month', 'year'];
> const isLoading = ref(false);
> 
> // Chart instances
> let revenueChart = null;
> let categoryChart = null;
> 
> // Dữ liệu từ props (khởi tạo ban đầu)
> const summaryData = ref(props.reportData?.summary || {
>     retail: { revenue: 0, growth: 0 },
>     wholesale: { revenue: 0, growth: 0 },
>     preorder: { revenue: 0, growth: 0 }
> });
> 
> const topProducts = ref(props.reportData?.topProducts || []);
> const topCustomers = ref(props.reportData?.topCustomers || []);
> const chartData = ref(props.reportData?.chartData || {
>     labels: [],
>     retail: [],
>     wholesale: [],
>     preorder: []
> });
> 
> // categoryDistribution có thể là mảng số hoặc mảng object
> const categoryDistribution = ref(props.reportData?.categoryDistribution || []);
> 
> // Format tiền
> const formatPrice = (value) => {
>     if (!value) return '0₫';
>     return value.toLocaleString('vi-VN') + '₫';
> };
> 
> // Lấy dữ liệu cho biểu đồ doughnut
> const getCategoryData = () => {
>     const dist = categoryDistribution.value;
>     if (Array.isArray(dist) && dist.length > 0) {
>         // Nếu là mảng object { label, value }
>         if (typeof dist[0] === 'object') {
>             return {
>                 labels: dist.map(item => item.label),
>                 data: dist.map(item => item.value)
>             };
>         }
>         // Nếu là mảng số
>         return {
>             labels: ['Balo', 'Cặp - Túi', 'Phụ kiện'],
>             data: dist
>         };
>     }
>     return { labels: ['Chưa có dữ liệu'], data: [100] };
> };
> 
> // Khởi tạo biểu đồ doanh thu
> const initRevenueChart = () => {
>     const canvas = document.getElementById('revenueByTypeChart');
>     if (!canvas) {
>         console.warn('Canvas revenueByTypeChart not found');
>         return;
>     }
>     
>     const ctx = canvas.getContext('2d');
>     if (revenueChart) revenueChart.destroy();
>     
>     const data = chartData.value;
>     revenueChart = new Chart(ctx, {
>         type: 'bar',
>         data: {
>             labels: data.labels,
>             datasets: [
>                 { 
>                     label: 'Bán lẻ', 
>                     data: data.retail, 
>                     backgroundColor: '#ff6b00', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Bán sỉ', 
>                     data: data.wholesale, 
>                     backgroundColor: '#436651', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 },
>                 { 
>                     label: 'Pre-order', 
>                     data: data.preorder, 
>                     backgroundColor: '#f59e0b', 
>                     borderRadius: 6,
>                     barPercentage: 0.7
>                 }
>             ]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { 
>                     position: 'top',
>                     labels: { font: { family: 'Montserrat', size: 12 } }
>                 },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             let label = context.dataset.label || '';
>                             let value = context.raw;
>                             return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
>                         }
>                     }
>                 }
>             },
>             scales: {
>                 y: { 
>                     beginAtZero: true, 
>                     title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
>                     grid: { color: '#e5e7eb' }
>                 },
>                 x: { 
>                     grid: { display: false },
>                     ticks: { font: { family: 'Montserrat' } }
>                 }
>             }
>         }
>     });
> };
> 
> // Khởi tạo biểu đồ danh mục (doughnut)
> const initCategoryChart = () => {
>     const canvas = document.getElementById('categoryChart');
>     if (!canvas) {
>         console.warn('Canvas categoryChart not found');
>         return;
>     }
>     
>     const ctx = canvas.getContext('2d');
>     if (categoryChart) categoryChart.destroy();
>     
>     const { labels, data } = getCategoryData();
>     
>     categoryChart = new Chart(ctx, {
>         type: 'doughnut',
>         data: {
>             labels: labels,
>             datasets: [{
>                 data: data,
>                 backgroundColor: ['#ff6b00', '#436651', '#89726c', '#f59e0b', '#e74c3c'],
>                 borderWidth: 0,
>                 hoverOffset: 10
>             }]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { 
>                     position: 'bottom',
>                     labels: { font: { family: 'Montserrat', size: 12 } }
>                 },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             let label = context.label || '';
>                             let value = context.raw;
>                             return `${label}: ${value}%`;
>                         }
>                     }
>                 }
>             }
>         }
>     });
> };
> 
> // Cập nhật biểu đồ
> const updateCharts = () => {
>     if (revenueChart) {
>         revenueChart.data.labels = chartData.value.labels;
>         revenueChart.data.datasets[0].data = chartData.value.retail;
>         revenueChart.data.datasets[1].data = chartData.value.wholesale;
>         revenueChart.data.datasets[2].data = chartData.value.preorder;
>         revenueChart.update();
>     } else {
>         initRevenueChart();
>     }
> 
>     if (categoryChart) {
>         const { labels, data } = getCategoryData();
>         categoryChart.data.labels = labels;
>         categoryChart.data.datasets[0].data = data;
>         categoryChart.update();
>     } else {
>         initCategoryChart();
>     }
> };
> 
> // Tải dữ liệu theo period (dùng axios gọi API JSON)
> const loadDataByPeriod = async () => {
>     isLoading.value = true;
>     try {
>         const response = await axios.get(route('admin.reports.data'), {
>             params: { period: period.value }
>         });
> 
>         const newData = response.data;
>         if (newData) {
>             summaryData.value = newData.summary || summaryData.value;
>             topProducts.value = newData.topProducts || [];
>             topCustomers.value = newData.topCustomers || [];
>             chartData.value = newData.chartData || chartData.value;
>             categoryDistribution.value = newData.categoryDistribution || [];
>         }
>     } catch (error) {
>         console.error('Lỗi tải dữ liệu:', error);
>     } finally {
>         isLoading.value = false;
>         // Đợi DOM render lại, sau đó cập nhật biểu đồ
>         await nextTick();
>         updateCharts();
>     }
> };
> 
> // Xuất báo cáo Excel
> const exportReport = async () => {
>     try {
>         window.location.href = route('admin.reports.export', { period: period.value });
>     } catch (error) {
>         console.error('Lỗi xuất báo cáo:', error);
>         alert('Có lỗi xảy ra khi xuất báo cáo');
>     }
> };
> 
> // Theo dõi thay đổi period
> watch(period, () => {
>     loadDataByPeriod();
> });
> 
> // Khởi tạo khi mount
> onMounted(() => {
>     // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
>     if (!props.reportData || Object.keys(props.reportData).length === 0) {
>         loadDataByPeriod();
>     } else {
>         // Nếu có dữ liệu, vẫn cần khởi tạo biểu đồ sau khi DOM render
>         setTimeout(() => {
>             initRevenueChart();
>             initCategoryChart();
>         }, 100);
>     }
> });
> </script>
> ```
> 
> **Các thay đổi chính:**
> 
> 1. **Xử lý categoryDistribution linh hoạt**: Hàm `getCategoryData()` kiểm tra nếu là mảng object `{ label, value }` thì dùng label động, nếu là mảng số thì dùng label mặc định.
> 
> 2. **Tách riêng `updateCharts()`** để tái sử dụng, đảm bảo cập nhật cả hai biểu đồ khi dữ liệu mới về mà không cần destroy/recreate.
> 
> 3. **Trong `finally` của `loadDataByPeriod`**: gọi `await nextTick()` và `updateCharts()` để đợi DOM render rồi mới cập nhật biểu đồ.
> 
> 4. **Bổ sung màu sắc** cho doughnut (nếu có nhiều hơn 3 danh mục).
> 
> 5. **Xóa `usePage` không dùng**.
> 
> Với những điều chỉnh này, khi bấm nút Tuần/Tháng/Năm, biểu đồ sẽ cập nhật trơn tru mà không bị mất.


## User:
07/16/2026 15:11

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

// Props từ controller
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

// Dữ liệu từ props (khởi tạo ban đầu)
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

// categoryDistribution có thể là mảng số hoặc mảng object
const categoryDistribution = ref(props.reportData?.categoryDistribution || []);

// Format tiền
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Lấy dữ liệu cho biểu đồ doughnut
const getCategoryData = () => {
    const dist = categoryDistribution.value;
    if (Array.isArray(dist) && dist.length > 0) {
        // Nếu là mảng object { label, value }
        if (typeof dist[0] === 'object') {
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
    if (!canvas) {
        console.warn('Canvas revenueByTypeChart not found');
        return;
    }
    
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
                    labels: { font: { family: 'Montserrat', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            return `${label}: ${value.toLocaleString('vi-VN')} ₫`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Doanh thu (VNĐ)', font: { family: 'Montserrat' } },
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

// Khởi tạo biểu đồ danh mục (doughnut)
const initCategoryChart = () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) {
        console.warn('Canvas categoryChart not found');
        return;
    }
    
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

// Cập nhật biểu đồ
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

// Tải dữ liệu theo period (dùng axios gọi API JSON)
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
        // Đợi DOM render lại, sau đó cập nhật biểu đồ
        await nextTick();
        updateCharts();
    }
};

// Xuất báo cáo Excel
const exportReport = async () => {
    try {
        window.location.href = route('admin.reports.export', { period: period.value });
    } catch (error) {
        console.error('Lỗi xuất báo cáo:', error);
        alert('Có lỗi xảy ra khi xuất báo cáo');
    }
};

// Theo dõi thay đổi period
watch(period, () => {
    loadDataByPeriod();
});

// Khởi tạo khi mount
onMounted(() => {
    // Nếu chưa có dữ liệu từ props thì gọi API lấy lần đầu
    if (!props.reportData || Object.keys(props.reportData).length === 0) {
        loadDataByPeriod();
    } else {
        // Nếu có dữ liệu, vẫn cần khởi tạo biểu đồ sau khi DOM render
        setTimeout(() => {
            initRevenueChart();
            initCategoryChart();
        }, 100);
    }
});
</script>
Chart lại mất khi chuyển tab


## Assistant:
07/16/2026 15:11

> **Thinking:**
> Chúng ta đã có code với logic cập nhật biểu đồ trong `finally` của `loadDataByPeriod`, nhưng có thể vấn đề là `chartData` và `categoryDistribution` được cập nhật nhưng `updateCharts` gọi `initRevenueChart` và `initCategoryChart` mà không kiểm tra xem canvas đã tồn tại hay chưa (có kiểm tra trong hàm init). Tuy nhiên, sau khi `isLoading` trở về false, DOM được render lại, canvas tồn tại, nhưng có thể các biểu đồ đã bị hủy và cần được tạo lại. Vấn đề có thể là `updateCharts` khi `revenueChart` vẫn còn nhưng `chartData` thay đổi, nó gọi `update()`, không vấn đề. Nhưng nếu bị mất khi chuyển tab, có thể do tab bị ẩn đi và canvas không còn trong DOM nên Chart không vẽ được. Tuy nhiên, nếu tab bị ẩn, khi quay lại, biểu đồ vẫn giữ nguyên.
> 
> Có thể lý do là `isLoading` set `true`, khi `v-if` ẩn biểu đồ, các canvas bị xóa khỏi DOM. Khi `isLoading` false, `v-else` hiện lại, nhưng các canvas mới được tạo, cần gọi lại `initChart`. Điều đó đã được làm trong `finally`.
> 
> Có thể có lỗi là `await nextTick()` không đảm bảo canvas đã được render hoàn toàn, nên thỉnh thoảng bị lỗi. Ta có thể thêm `setTimeout` thay vì `nextTick` để đảm bảo.
> 
> Ngoài ra, nếu dùng `v-if` và `v-else`, khi `isLoading` false, phần `v-else` được render, nhưng nếu `isLoading` true, phần `v-else` bị ẩn và khi false, nó render lại toàn bộ, do đó các hàm init cần được gọi.
> 
> Để cải thiện, ta có thể sử dụng `v-show` thay vì `v-if` để giữ nguyên canvas trong DOM, nhưng nếu biểu đồ bị ẩn đi, Chart.js vẫn giữ nguyên, khi show lại không cần khởi tạo lại. Nhưng thay đổi period cần cập nhật dữ liệu, vẫn cần gọi update.
> 
> Tôi khuyên dùng `v-show` thay vì `v-if` để giữ canvas, và khi load xong chỉ cần update dữ liệu, không cần tạo lại biểu đồ.
> 
> Ta sẽ sửa template: thay `v-if` / `v-else` bằng `v-show` và dùng biến `isLoading` để hiển thị loading overlay hoặc spinner trên cùng.
> 
> Ví dụ:
> 
> ```vue
> <div class="relative">
>     <!-- Loading spinner -->
>     <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/75 z-10">
>         <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
>     </div>
>     <!-- Nội dung chính -->
>     <div>
>         <!-- 3 thẻ tổng quan -->
>         ...
>         <!-- Biểu đồ -->
>         ...
>     </div>
> </div>
> ```
> 
> Như vậy, các canvas không bị xóa khỏi DOM, và khi cập nhật dữ liệu, ta chỉ cần gọi `updateCharts()` mà không cần tạo lại biểu đồ. Cũng không cần `nextTick` hay `setTimeout` phức tạp.
> 
> Ta sẽ điều chỉnh code Vue như sau:
> 
> 1. Thay đổi template: bỏ `v-if="isLoading"` và `v-else`, thay bằng overlay loading.
> 2. Trong `loadDataByPeriod`, sau khi cập nhật dữ liệu, gọi `updateCharts()` ngay lập tức (không cần `nextTick` vì canvas không bị xóa).
> 3. Ở `onMounted`, nếu có dữ liệu từ props thì gọi `initRevenueChart` và `initCategoryChart`, nếu không thì gọi `loadDataByPeriod` (sẽ tự gọi update).
> 
> Đây là phiên bản cải tiến:
> 
> ```vue
> <script setup>
> import { ref, onMounted, watch } from 'vue';
> import AdminLayout from '@/Layouts/AdminLayout.vue';
> import { Head, Link } from '@inertiajs/vue3';
> import axios from 'axios';
> 
> const props = defineProps({
>     reportData: Object,
>     currentPeriod: String,
> });
> 
> const period = ref(props.currentPeriod || 'week');
> const periods = ['week', 'month', 'year'];
> const isLoading = ref(false);
> 
> let revenueChart = null;
> let categoryChart = null;
> 
> const summaryData = ref(props.reportData?.summary || {
>     retail: { revenue: 0, growth: 0 },
>     wholesale: { revenue: 0, growth: 0 },
>     preorder: { revenue: 0, growth: 0 }
> });
> 
> const topProducts = ref(props.reportData?.topProducts || []);
> const topCustomers = ref(props.reportData?.topCustomers || []);
> const chartData = ref(props.reportData?.chartData || {
>     labels: [],
>     retail: [],
>     wholesale: [],
>     preorder: []
> });
> 
> const categoryDistribution = ref(props.reportData?.categoryDistribution || []);
> 
> const formatPrice = (value) => {
>     if (!value) return '0₫';
>     return value.toLocaleString('vi-VN') + '₫';
> };
> 
> const getCategoryData = () => {
>     const dist = categoryDistribution.value;
>     if (Array.isArray(dist) && dist.length > 0) {
>         if (typeof dist[0] === 'object') {
>             return {
>                 labels: dist.map(item => item.label),
>                 data: dist.map(item => item.value)
>             };
>         }
>         return {
>             labels: ['Balo', 'Cặp - Túi', 'Phụ kiện'],
>             data: dist
>         };
>     }
>     return { labels: ['Chưa có dữ liệu'], data: [100] };
> };
> 
> const initRevenueChart = () => {
>     const canvas = document.getElementById('revenueByTypeChart');
>     if (!canvas) return;
>     const ctx = canvas.getContext('2d');
>     if (revenueChart) revenueChart.destroy();
>     const data = chartData.value;
>     revenueChart = new Chart(ctx, {
>         type: 'bar',
>         data: {
>             labels: data.labels,
>             datasets: [
>                 { label: 'Bán lẻ', data: data.retail, backgroundColor: '#ff6b00', borderRadius: 6, barPercentage: 0.7 },
>                 { label: 'Bán sỉ', data: data.wholesale, backgroundColor: '#436651', borderRadius: 6, barPercentage: 0.7 },
>                 { label: 'Pre-order', data: data.preorder, backgroundColor: '#f59e0b', borderRadius: 6, barPercentage: 0.7 }
>             ]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { position: 'top', labels: { font: { size: 12 } } },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' ₫';
>                         }
>                     }
>                 }
>             },
>             scales: {
>                 y: { beginAtZero: true, title: { display: true, text: 'Doanh thu (VNĐ)' }, grid: { color: '#e5e7eb' } },
>                 x: { grid: { display: false } }
>             }
>         }
>     });
> };
> 
> const initCategoryChart = () => {
>     const canvas = document.getElementById('categoryChart');
>     if (!canvas) return;
>     const ctx = canvas.getContext('2d');
>     if (categoryChart) categoryChart.destroy();
>     const { labels, data } = getCategoryData();
>     categoryChart = new Chart(ctx, {
>         type: 'doughnut',
>         data: {
>             labels: labels,
>             datasets: [{
>                 data: data,
>                 backgroundColor: ['#ff6b00', '#436651', '#89726c', '#f59e0b', '#e74c3c'],
>                 borderWidth: 0,
>                 hoverOffset: 10
>             }]
>         },
>         options: {
>             responsive: true,
>             maintainAspectRatio: true,
>             plugins: {
>                 legend: { position: 'bottom', labels: { font: { size: 12 } } },
>                 tooltip: {
>                     callbacks: {
>                         label: function(context) {
>                             return context.label + ': ' + context.raw + '%';
>                         }
>                     }
>                 }
>             }
>         }
>     });
> };
> 
> const updateCharts = () => {
>     if (revenueChart) {
>         revenueChart.data.labels = chartData.value.labels;
>         revenueChart.data.datasets[0].data = chartData.value.retail;
>         revenueChart.data.datasets[1].data = chartData.value.wholesale;
>         revenueChart.data.datasets[2].data = chartData.value.preorder;
>         revenueChart.update();
>     } else {
>         initRevenueChart();
>     }
> 
>     if (categoryChart) {
>         const { labels, data } = getCategoryData();
>         categoryChart.data.labels = labels;
>         categoryChart.data.datasets[0].data = data;
>         categoryChart.update();
>     } else {
>         initCategoryChart();
>     }
> };
> 
> const loadDataByPeriod = async () => {
>     isLoading.value = true;
>     try {
>         const response = await axios.get(route('admin.reports.data'), {
>             params: { period: period.value }
>         });
>         const newData = response.data;
>         if (newData) {
>             summaryData.value = newData.summary || summaryData.value;
>             topProducts.value = newData.topProducts || [];
>             topCustomers.value = newData.topCustomers || [];
>             chartData.value = newData.chartData || chartData.value;
>             categoryDistribution.value = newData.categoryDistribution || [];
>         }
>     } catch (error) {
>         console.error('Lỗi tải dữ liệu:', error);
>     } finally {
>         isLoading.value = false;
>         // Cập nhật biểu đồ ngay sau khi dữ liệu thay đổi
>         updateCharts();
>     }
> };
> 
> const exportReport = () => {
>     window.location.href = route('admin.reports.export', { period: period.value });
> };
> 
> watch(period, () => {
>     loadDataByPeriod();
> });
> 
> onMounted(() => {
>     if (!props.reportData || Object.keys(props.reportData).length === 0) {
>         loadDataByPeriod();
>     } else {
>         // Nếu có dữ liệu sẵn, khởi tạo biểu đồ
>         setTimeout(() => {
>             initRevenueChart();
>             initCategoryChart();
>         }, 100);
>     }
> });
> </script>
> ```
> 
> Và template:
> 
> ```vue
> <template>
>     <Head title="Báo cáo thống kê - BigBag Admin" />
>     <AdminLayout>
>         <div class="p-4 md:p-8">
>             <div class="mb-6">
>                 <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Báo cáo thống kê</h1>
>             </div>
> 
>             <!-- Bộ lọc -->
>             <div class="flex flex-wrap items-center gap-2 mb-6">
>                 <button v-for="p in periods" :key="p" @click="period = p" 
>                     class="px-4 py-2 rounded-lg text-sm transition-all"
>                     :class="period === p ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'">
>                     {{ p === 'week' ? 'Tuần' : p === 'month' ? 'Tháng' : 'Năm' }}
>                 </button>
>                 <button @click="exportReport" class="ml-auto bg-white border border-gray-300 px-4 py-2 rounded-lg text-sm flex items-center gap-1 text-gray-600 hover:bg-gray-50">
>                     <span class="material-symbols-outlined text-sm">download</span> Xuất báo cáo
>                 </button>
>             </div>
> 
>             <!-- Nội dung chính với overlay loading -->
>             <div class="relative">
>                 <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/75 z-10">
>                     <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
>                     <p class="ml-2 text-gray-500">Đang tải dữ liệu...</p>
>                 </div>
> 
>                 <div>
>                     <!-- 3 thẻ tổng quan -->
>                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
>                         <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                             <p class="text-sm text-gray-500">Bán lẻ</p>
>                             <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.retail.revenue) }}</p>
>                             <p class="text-xs text-green-600 mt-1">↑ +{{ summaryData.retail.growth }}%</p>
>                         </div>
>                         <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                             <p class="text-sm text-gray-500">Bán sỉ</p>
>                             <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.wholesale.revenue) }}</p>
>                             <p class="text-xs text-green-600 mt-1">↑ +{{ summaryData.wholesale.growth }}%</p>
>                         </div>
>                         <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
>                             <p class="text-sm text-gray-500">Pre-order</p>
>                             <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(summaryData.preorder.revenue) }}</p>
>                             <p class="text-xs text-yellow-600 mt-1">↑ +{{ summaryData.preorder.growth }}%</p>
>                         </div>
>                     </div>
> 
>                     <!-- Biểu đồ -->
>                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
>                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                             <h3 class="font-semibold text-gray-800 mb-4">Doanh thu theo loại hình</h3>
>                             <canvas id="revenueByTypeChart" height="250"></canvas>
>                         </div>
>                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                             <h3 class="font-semibold text-gray-800 mb-4">Phân bố danh mục</h3>
>                             <canvas id="categoryChart" height="250"></canvas>
>                         </div>
>                     </div>
> 
>                     <!-- Top sản phẩm & khách hàng -->
>                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
>                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                             <div class="flex justify-between items-center mb-4">
>                                 <h3 class="font-semibold text-gray-800">Top sản phẩm bán chạy</h3>
>                                 <span class="text-xs text-gray-500">Theo doanh thu</span>
>                             </div>
>                             <div class="space-y-3">
>                                 <div v-for="(product, idx) in topProducts" :key="idx" class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg">
>                                     <div class="flex items-center gap-2">
>                                         <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
>                                         <span class="text-gray-700">{{ product.name }}</span>
>                                     </div>
>                                     <div class="text-right">
>                                         <span class="font-semibold text-orange-600">{{ formatPrice(product.revenue) }}</span>
>                                         <span class="text-xs text-gray-400 ml-2">({{ product.sold }} cái)</span>
>                                     </div>
>                                 </div>
>                             </div>
>                             <div class="mt-4 pt-3 border-t">
>                                 <Link :href="route('admin.products.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">Xem tất cả sản phẩm <span class="material-symbols-outlined text-sm">arrow_forward</span></Link>
>                             </div>
>                         </div>
>                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
>                             <div class="flex justify-between items-center mb-4">
>                                 <h3 class="font-semibold text-gray-800">Top khách hàng thân thiết</h3>
>                                 <span class="text-xs text-gray-500">Theo tổng chi tiêu</span>
>                             </div>
>                             <div class="space-y-3">
>                                 <div v-for="(customer, idx) in topCustomers" :key="idx" class="flex justify-between items-center p-2 hover:bg-orange-50 rounded-lg">
>                                     <div class="flex items-center gap-2">
>                                         <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
>                                         <div>
>                                             <span class="text-gray-700">{{ customer.name }}</span>
>                                             <span class="text-xs text-gray-400 ml-2">({{ customer.orders || 0 }} đơn)</span>
>                                         </div>
>                                     </div>
>                                     <div class="font-semibold text-orange-600">{{ formatPrice(customer.total) }}</div>
>                                 </div>
>                             </div>
>                             <div class="mt-4 pt-3 border-t">
>                                 <Link :href="route('admin.customers.index')" class="text-sm text-orange-600 hover:underline flex items-center justify-end gap-1">Xem tất cả khách hàng <span class="material-symbols-outlined text-sm">arrow_forward</span></Link>
>                             </div>
>                         </div>
>                     </div>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> ```
> 
> Với cách này, canvas không bị xóa khi loading, biểu đồ được cập nhật trực tiếp mà không cần tạo lại. Khi chuyển tab, biểu đồ vẫn giữ nguyên và cập nhật dữ liệu khi có sự thay đổi.

Tôi đã chỉnh lại toàn bộ file `Reports.vue` để khắc phục vấn đề **biểu đồ bị mất khi chuyển tab**. Giải pháp:

1. **Sử dụng `v-show` thay vì `v-if`/`v-else`** – giữ canvas trong DOM, không bị xóa khi loading.
2. **Overlay loading** – hiển thị spinner phủ lên nội dung thay vì ẩn toàn bộ.
3. **Cập nhật biểu đồ ngay khi dữ liệu về** – không cần `nextTick` hay `setTimeout`.
4. **Hàm `updateCharts`** – cập nhật dữ liệu trực tiếp, không tạo lại Chart nếu đã tồn tại.

Dưới đây là code hoàn chỉnh bạn có thể thay thế:

```vue
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
```

**Điểm mới quan trọng:**

1. **Overlay loading** – giữ canvas không bị xóa khi loading.
2. **`updateCharts()`** – cập nhật dữ liệu thay vì tạo mới Chart.
3. **Xử lý `categoryDistribution`** – hỗ trợ cả mảng số và mảng object.

Bây giờ biểu đồ sẽ không bị mất khi chuyển tab và cập nhật dữ liệu mượt mà.



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
