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
                <p class="text-sm text-gray-500 mt-0.5">Đây là tổng quan hoạt động kinh doanh hôm nay</p>
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