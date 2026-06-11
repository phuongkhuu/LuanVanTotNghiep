<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    type: {
        type: String,
        default: 'retail'
    }
});

// Search
const search = ref('');
const activeType = ref(['retail', 'wholesale'].includes(props.type) ? props.type : 'retail');

// Customer types tabs (chỉ 2 loại: retail và wholesale)
const customerTypes = [
    { value: 'retail', label: 'Khách lẻ', icon: '👤' },
    { value: 'wholesale', label: 'Khách doanh nghiệp', icon: '🏢' }
];

// Modal state
const showDetailModal = ref(false);
const selectedCustomer = ref(null);
const customerOrders = ref([]);
const detailLoading = ref(false);
const errorMessage = ref('');

// Lấy danh sách khách hàng từ props
const customersList = computed(() => {
    if (!props.customers || !props.customers.data || !Array.isArray(props.customers.data)) {
        return [];
    }
    return props.customers.data;
});

// Format price
const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

// Format date
const formatDate = (date) => {
    if (!date) return '---';
    if (typeof date === 'string' && date.includes('/')) return date;
    return date;
};

// Get count by type
const getTypeCount = (type) => {
    if (!props.customers?.data) return 0;
    // Chỉ hiển thị tổng số khách theo type đã được lọc từ server
    return props.customers.data.length;
};

// Xem chi tiết khách hàng - gọi API
const viewDetail = async (customer) => {
    if (!customer || !customer.phone) {
        errorMessage.value = 'Không có thông tin khách hàng';
        return;
    }
    selectedCustomer.value = customer;
    showDetailModal.value = true;
    detailLoading.value = true;
    errorMessage.value = '';
    try {
        const response = await fetch(`/admin/customers/${encodeURIComponent(customer.phone)}`);
        const data = await response.json();
        if (data && !data.error) {
            customerOrders.value = data.orders || [];
            selectedCustomer.value = {
                ...selectedCustomer.value,
                address: data.address || '',
                join_date: data.join_date || '',
                total_spent: data.total_spent || 0,
                orders_count: data.orders_count || 0,
                last_order_date: data.last_order_date || ''
            };
        } else {
            errorMessage.value = data.error || 'Không thể tải chi tiết';
            customerOrders.value = [];
        }
    } catch (error) {
        console.error(error);
        errorMessage.value = 'Không thể tải chi tiết khách hàng';
        customerOrders.value = [];
    } finally {
        detailLoading.value = false;
    }
};

// Export Excel
const exportExcel = () => {
    router.post('/admin/customers/export', { type: activeType.value });
};

// Hàm thay đổi loại khách hàng và cập nhật URL
const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    router.get(route('admin.customers.index', { type: typeValue, search: search.value }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Theo dõi thay đổi của props.type từ URL
watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
    }
});

// Tìm kiếm
watch(search, (val) => {
    router.get(route('admin.customers.index', { type: activeType.value, search: val }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});
</script>

<template>
    <Head title="Quản lý khách hàng - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khách hàng</h1>
                <p class="text-gray-600 text-sm mt-1">Quản lý thông tin khách hàng dựa trên đơn hàng</p>
            </div>

            <!-- Tab loại khách hàng -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in customerTypes" 
                    :key="tab.value" 
                    @click="changeActiveType(tab.value)"
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="activeType === tab.value ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ tab.icon }} {{ tab.label }} 
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ getTypeCount(tab.value) }}</span>
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên hoặc số điện thoại..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Danh sách khách hàng -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SĐT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">ĐƠN HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG CHI</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">LẦN CUỐI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cust in customersList" :key="cust.phone" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
                                            {{ cust.name ? cust.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ cust.name || 'Khách hàng' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.phone || '---' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.orders_count || 0 }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(cust.total_spent) }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ formatDate(cust.last_order_date) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(cust)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="customersList.length === 0">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    Không có khách hàng nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Hiển thị {{ customersList.length }} khách hàng</span>
                    <button 
                        @click="exportExcel" 
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết khách hàng -->
        <div 
            v-if="showDetailModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showDetailModal = false"
        >
            <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Chi tiết khách hàng</h3>
                    <button 
                        @click="showDetailModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none"
                    >
                        &times;
                    </button>
                </div>
                
                <div v-if="errorMessage" class="p-3 mb-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                    {{ errorMessage }}
                </div>
                
                <div v-if="detailLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                    <p class="mt-2 text-gray-500">Đang tải...</p>
                </div>
                
                <div v-else-if="selectedCustomer" class="space-y-4">
                    <!-- Avatar và tên -->
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-600">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">{{ selectedCustomer.name || 'Khách hàng' }}</h4>
                            <p class="text-sm text-gray-500">{{ selectedCustomer.phone || '---' }}</p>
                        </div>
                    </div>
                    
                    <!-- Thông tin khách hàng -->
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg">
                        <div>
                            <p class="text-xs text-gray-500">Địa chỉ</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.address || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Ngày tham gia</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.join_date || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng đơn hàng</p>
                            <p class="text-sm font-semibold text-gray-800">{{ selectedCustomer.orders_count || 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng chi tiêu</p>
                            <p class="text-sm font-semibold text-orange-600">{{ formatPrice(selectedCustomer.total_spent) }}</p>
                        </div>
                    </div>
                    
                    <!-- Lịch sử đơn hàng -->
                    <div>
                        <h5 class="font-semibold text-gray-800 mb-2">📦 Lịch sử đơn hàng</h5>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="order in customerOrders" :key="order.id" class="border rounded-lg p-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-800">Mã đơn #{{ order.id }}</span>
                                    <span :class="order.status == 2 ? 'text-green-600' : order.status == 1 ? 'text-yellow-600' : 'text-gray-500'">
                                        {{ order.status == 2 ? 'Hoàn thành' : order.status == 1 ? 'Đã xác nhận' : 'Chờ xử lý' }}
                                    </span>
                                </div>
                                <div class="text-gray-500 text-xs mt-1">Ngày: {{ order.created_at }} | Loại: {{ order.order_code }}</div>
                                <div class="text-gray-700">Tổng: {{ formatPrice(order.total_amount) }}</div>
                                <div class="text-gray-500 text-xs mt-1">Người nhận: {{ order.receiver_name }} - {{ order.receiver_phone }}</div>
                                <div class="text-gray-500 text-xs">Địa chỉ: {{ order.shipping_address }}</div>
                            </div>
                            <div v-if="customerOrders.length === 0" class="text-gray-400 text-center py-4">Chưa có đơn hàng nào</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
                    </button>
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
.animate-spin { animation: spin 1s linear infinite; }
</style>