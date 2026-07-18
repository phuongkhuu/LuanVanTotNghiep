<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

// Nhận props từ controller
const props = defineProps({
    stats: Object,          // { todayRevenue: { retail, wholesale, preorder }, totalOrders, totalCustomers, lowStockProducts }
    growth: Object,         // { retail, wholesale, preorder }
    recentOrders: Array,
    topRetail: Array,
    topWholesale: Array,
    topPreorder: Array,
    chartWeek: Object,      // { labels, retail, wholesale, preorder }
    chartMonth: Object,     // { labels, retail, wholesale, preorder }
    currentPeriod: String,  // 'week' or 'month'
});

const page = usePage();

// Period state
const selectedPeriod = ref(props.currentPeriod || 'week');
let revenueChart = null;

// Format currency
const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Format growth with sign
const formatGrowth = (value) => {
    if (value > 0) return `↑ +${value}% so với hôm qua`;
    if (value < 0) return `↓ ${value}% so với hôm qua`;
    return '0% so với hôm qua';
};

// Khởi tạo biểu đồ với dữ liệu từ props
const initChart = () => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    if (revenueChart) revenueChart.destroy();
    
    // Lấy dữ liệu tương ứng với period
    const data = selectedPeriod.value === 'week' ? props.chartWeek : props.chartMonth;
    
    // Nếu không có dữ liệu, dùng mảng rỗng
    const labels = data?.labels || [];
    const retail = data?.retail || [];
    const wholesale = data?.wholesale || [];
    const preorder = data?.preorder || [];
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { 
                    label: 'Bán lẻ', 
                    data: retail, 
                    backgroundColor: '#f97316', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Bán sỉ', 
                    data: wholesale, 
                    backgroundColor: '#436651', 
                    borderRadius: 6,
                    barPercentage: 0.7
                },
                { 
                    label: 'Pre-order', 
                    data: preorder, 
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
                            return context.dataset.label + ': ' + context.raw.toFixed(1) + ' triệu ₫';
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

// Xử lý khi đổi period: gọi lại controller để lấy dữ liệu mới
const handlePeriodChange = () => {
    router.visit(route('admin.dashboard'), {
        method: 'get',
        data: { period: selectedPeriod.value },
        preserveScroll: true,
        preserveState: true,
        only: ['chartWeek', 'chartMonth', 'currentPeriod'], // Chỉ cập nhật các props liên quan đến chart
        onSuccess: () => {
            // Sau khi cập nhật props, khởi tạo lại biểu đồ
            initChart();
        }
    });
};

// Theo dõi props thay đổi (khi có Inertia reload)
watch(() => [props.chartWeek, props.chartMonth, props.currentPeriod], () => {
    // Cập nhật selectedPeriod nếu currentPeriod thay đổi
    if (props.currentPeriod && props.currentPeriod !== selectedPeriod.value) {
        selectedPeriod.value = props.currentPeriod;
    }
    initChart();
}, { deep: true });

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
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.retail) }}</p>
                        <p class="text-xs" :class="growth.retail >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatGrowth(growth.retail) }}
                        </p>
                    </div>
                </div>

                <!-- Bán sỉ -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bán sỉ hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.wholesale) }}</p>
                        <p class="text-xs" :class="growth.wholesale >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatGrowth(growth.wholesale) }}
                        </p>
                    </div>
                </div>

                <!-- Pre-order -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Pre-order hôm nay</p>
                        <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(stats.todayRevenue.preorder) }}</p>
                        <p class="text-xs" :class="growth.preorder >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatGrowth(growth.preorder) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Thêm các thông số tổng quan (tùy chọn) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <p class="text-sm text-gray-500 font-medium">Tổng đơn hàng</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.totalOrders }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <p class="text-sm text-gray-500 font-medium">Tổng khách hàng</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.totalCustomers }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
                    <p class="text-sm text-gray-500 font-medium">Sản phẩm tồn kho thấp</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ stats.lowStockProducts }}</p>
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
                            <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
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
                            <span class="text-sm font-medium text-gray-800">{{ p.sold }} cái</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>