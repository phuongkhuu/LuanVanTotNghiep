<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';  // Thêm Link vào import

// Recent orders data
const recentOrders = ref([
    { code: '#ORD-001', customer: 'Nguyễn Văn A', type: '🛒 Bán lẻ', amount: '2.500.000₫', status: 'Hoàn thành', statusClass: 'bg-success/10 text-success' },
    { code: '#ORD-002', customer: 'Công ty ABC', type: '🏭 Bán sỉ', amount: '18.500.000₫', status: 'Đang giao', statusClass: 'bg-primary/10 text-primary' },
    { code: '#ORD-003', customer: 'Trần Thị B', type: '⏳ Pre-order', amount: '1.850.000₫', status: 'Chờ xác nhận', statusClass: 'bg-warning/10 text-warning' }
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

// Initialize chart
onMounted(() => {
    const canvas = document.getElementById('revenueByTypeChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                datasets: [
                    { label: 'Bán lẻ', data: [8, 10, 7, 12, 15, 18, 14], backgroundColor: '#ff6b00', borderRadius: 6 },
                    { label: 'Bán sỉ', data: [15, 18, 12, 22, 28, 35, 25], backgroundColor: '#436651', borderRadius: 6 },
                    { label: 'Pre-order', data: [5, 4, 6, 8, 10, 12, 7], backgroundColor: '#f59e0b', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Doanh thu (triệu ₫)' } } }
            }
        });
    }
});
</script>

<template>
    <Head title="Dashboard - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Welcome -->
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-on-surface">Chào mừng trở lại, Admin</h1>
                <p class="text-on-surface-variant text-sm mt-1">Đây là tổng quan hoạt động kinh doanh hôm nay</p>
            </div>

            <!-- Stats Cards - 3 loại hình bán hàng -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Bán lẻ -->
                <div class="stat-card bg-surface-white rounded-xl p-5 border-l-4 border-l-primary shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-on-surface-variant text-sm font-medium">🛒 Bán lẻ hôm nay</p>
                            <p class="text-2xl md:text-3xl font-bold text-on-surface mt-2">8.250.000₫</p>
                            <p class="text-xs text-success mt-2">
                                <span class="material-symbols-outlined text-sm align-middle">trending_up</span> +8.2%
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-hover-bg flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">shopping_cart</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-border-light">
                        <div class="flex justify-between text-xs text-outline">
                            <span>📦 12 đơn hàng</span>
                            <span>⭐ 4.8 rating</span>
                        </div>
                    </div>
                </div>

                <!-- Bán sỉ -->
                <div class="stat-card bg-surface-white rounded-xl p-5 border-l-4 border-l-secondary shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-on-surface-variant text-sm font-medium">🏭 Bán sỉ hôm nay</p>
                            <p class="text-2xl md:text-3xl font-bold text-on-surface mt-2">32.500.000₫</p>
                            <p class="text-xs text-success mt-2">
                                <span class="material-symbols-outlined text-sm align-middle">trending_up</span> +23.5%
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-secondary text-2xl">business</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-border-light">
                        <div class="flex justify-between text-xs text-outline">
                            <span>🏢 3 doanh nghiệp</span>
                            <span>📦 450 sản phẩm</span>
                        </div>
                    </div>
                </div>

                <!-- Pre-order -->
                <div class="stat-card bg-surface-white rounded-xl p-5 border-l-4 border-l-warning shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-on-surface-variant text-sm font-medium">⏳ Pre-order hôm nay</p>
                            <p class="text-2xl md:text-3xl font-bold text-on-surface mt-2">5.200.000₫</p>
                            <p class="text-xs text-warning mt-2">
                                <span class="material-symbols-outlined text-sm align-middle">schedule</span> 24 đơn chờ xác nhận
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-warning/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-warning text-2xl">pending</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-border-light">
                        <div class="flex justify-between text-xs text-outline">
                            <span>🚚 Dự kiến giao: 15/06</span>
                            <span>📊 85% hoàn thành</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-surface-white rounded-lg p-4 text-center border border-border-light">
                    <p class="text-2xl font-bold text-primary">156</p>
                    <p class="text-xs text-on-surface-variant">Khách hàng mới</p>
                </div>
                <div class="bg-surface-white rounded-lg p-4 text-center border border-border-light">
                    <p class="text-2xl font-bold text-primary">284</p>
                    <p class="text-xs text-on-surface-variant">Tổng đơn hàng</p>
                </div>
                <div class="bg-surface-white rounded-lg p-4 text-center border border-border-light">
                    <p class="text-2xl font-bold text-primary">98%</p>
                    <p class="text-xs text-on-surface-variant">Hài lòng khách hàng</p>
                </div>
                <div class="bg-surface-white rounded-lg p-4 text-center border border-border-light">
                    <p class="text-2xl font-bold text-primary">12</p>
                    <p class="text-xs text-on-surface-variant">Sản phẩm sắp hết</p>
                </div>
            </div>

            <!-- Charts & Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-surface-white rounded-xl p-6 border border-border-light shadow-sm">
                    <div class="flex justify-between mb-4">
                        <h3 class="font-semibold text-on-surface">Doanh thu theo loại hình</h3>
                        <select class="text-sm border border-border-light rounded-lg px-2 py-1 bg-white">
                            <option>7 ngày qua</option>
                            <option>Tháng này</option>
                        </select>
                    </div>
                    <canvas id="revenueByTypeChart" height="200"></canvas>
                </div>
                
                <div class="bg-surface-white rounded-xl p-6 border border-border-light shadow-sm">
                    <h3 class="font-semibold text-on-surface mb-4">Đơn hàng gần đây</h3>
                    <div class="space-y-3">
                        <div v-for="order in recentOrders" :key="order.code" class="flex justify-between items-center p-2 hover:bg-hover-bg rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-on-surface">{{ order.code }}</p>
                                <p class="text-xs text-outline">{{ order.customer }} - {{ order.type }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="order.statusClass">{{ order.status }}</span>
                            <span class="text-sm font-semibold text-on-surface">{{ order.amount }}</span>
                        </div>
                    </div>
                    <Link :href="route('admin.orders.index')" class="block text-center mt-3 text-sm text-primary hover:underline">Xem tất cả</Link>
                </div>
            </div>

            <!-- Top sản phẩm theo loại -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-surface-white rounded-xl p-5 border border-border-light">
                    <h3 class="font-semibold mb-3 flex items-center gap-2 text-on-surface">
                        <span class="text-primary">🛒</span> Top bán lẻ
                    </h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topRetail" :key="idx" class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">{{ idx+1 }}. {{ p.name }}</span>
                            <span class="font-semibold text-on-surface">{{ p.sold }} cái</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-surface-white rounded-xl p-5 border border-border-light">
                    <h3 class="font-semibold mb-3 flex items-center gap-2 text-on-surface">
                        <span class="text-secondary">🏭</span> Top bán sỉ
                    </h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topWholesale" :key="idx" class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">{{ idx+1 }}. {{ p.name }}</span>
                            <span class="font-semibold text-on-surface">{{ p.quantity }} đơn</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-surface-white rounded-xl p-5 border border-border-light">
                    <h3 class="font-semibold mb-3 flex items-center gap-2 text-on-surface">
                        <span class="text-warning">⏳</span> Pre-order nổi bật
                    </h3>
                    <div class="space-y-2">
                        <div v-for="(p, idx) in topPreorder" :key="idx" class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">{{ idx+1 }}. {{ p.name }}</span>
                            <span class="font-semibold text-on-surface">{{ p.preordered }} đơn</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>