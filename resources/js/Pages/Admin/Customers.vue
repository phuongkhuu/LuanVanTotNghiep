<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    type: {
        type: String,
        default: 'all'
    },
    counts: {
        type: Object,
        default: () => ({ all: 0, retail: 0, wholesale: 0, preorder: 0 })
    }
});

const search = ref('');
const activeType = ref(['retail', 'wholesale', 'preorder', 'all'].includes(props.type) ? props.type : 'all');

// Pagination - 5 items per page
const currentPage = ref(1);
const perPage = ref(5);

const customerTypes = [
    { value: 'all', label: 'Tất cả', icon: '👥' },
    { value: 'retail', label: 'Khách lẻ', icon: '👤' },
    { value: 'wholesale', label: 'Khách doanh nghiệp', icon: '🏢' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

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

// Lọc khách hàng theo tên hoặc số điện thoại (client-side)
const filteredCustomers = computed(() => {
    if (!customersList.value || customersList.value.length === 0) return [];
    if (!search.value) return customersList.value;
    
    const keyword = search.value.toLowerCase().trim();
    return customersList.value.filter(customer => {
        const name = (customer.name || '').toLowerCase();
        const phone = (customer.phone || '').toLowerCase();
        return name.includes(keyword) || phone.includes(keyword);
    });
});

// Pagination
const paginatedCustomers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredCustomers.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredCustomers.value.length / perPage.value);
});

// Hiển thị số trang (tối đa 5 trang)
const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const maxDisplay = 5;
    
    if (total <= maxDisplay) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + maxDisplay - 1);
    
    if (end - start < maxDisplay - 1) {
        start = Math.max(1, end - maxDisplay + 1);
    }
    
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Reset về trang 1 khi tìm kiếm
watch(search, () => {
    currentPage.value = 1;
});

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

const formatDate = (date) => {
    if (!date) return '---';
    if (typeof date === 'string' && date.includes('/')) return date;
    return date;
};

const getTypeCount = (type) => {
    return props.counts?.[type] || 0;
};

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
        // Gọi API với tham số type để lọc đơn hàng
        const url = `/admin/customers/${encodeURIComponent(customer.phone)}?type=${activeType.value}`;
        const response = await fetch(url);
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

const exportExcel = () => {
    router.post('/admin/customers/export', { type: activeType.value });
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    search.value = '';
    currentPage.value = 1;
    router.get(route('admin.customers.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Khi props.type thay đổi, cập nhật activeType
watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder', 'all'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
        currentPage.value = 1;
    }
});
</script>

<template>
    <Head title="Quản lý khách hàng - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khách hàng</h1>
                <button 
                    @click="exportExcel" 
                    class="bg-orange-600 text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">download</span>
                    Xuất Excel
                </button>
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">SĐT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">ĐƠN HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">TỔNG CHI</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">LẦN CUỐI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cust in paginatedCustomers" :key="cust.phone" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm flex-shrink-0">
                                            {{ cust.name ? cust.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ cust.name || 'Khách hàng' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ cust.phone || '---' }}</td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ cust.orders_count || 0 }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600 whitespace-nowrap">{{ formatPrice(cust.total_spent) }}</td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ formatDate(cust.last_order_date) }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button 
                                        @click="viewDetail(cust)" 
                                        class="px-3 py-1.5 text-xs text-orange-600 hover:bg-orange-100 rounded-lg transition-colors font-medium"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="paginatedCustomers.length === 0">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy khách hàng nào' : 'Không có khách hàng nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer với phân trang căn giữa -->
                <div class="p-4 border-t border-gray-200">
                    <!-- Thông tin số lượng -->
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedCustomers.length }} / {{ filteredCustomers.length }} khách hàng
                    </div>
                    
                    <!-- Phân trang căn giữa -->
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2">
                        <button
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in displayedPages"
                                :key="page"
                                @click="currentPage = page"
                                class="px-3.5 py-1.5 text-sm rounded-lg transition-colors font-medium"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
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
                    <!-- Avatar & tên -->
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-600 flex-shrink-0">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">{{ selectedCustomer.name || 'Khách hàng' }}</h4>
                            <p class="text-sm text-gray-500">{{ selectedCustomer.phone || '---' }}</p>
                        </div>
                    </div>
                    
                    <!-- Thông tin tổng quan -->
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
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium text-gray-800">Mã đơn #{{ order.id }}</span>
                                        <span class="ml-2 text-xs text-gray-500">({{ order.order_code }})</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                                        'bg-green-100 text-green-700': order.status === 2,
                                        'bg-yellow-100 text-yellow-700': order.status === 1,
                                        'bg-gray-100 text-gray-600': order.status === 0,
                                        'bg-red-100 text-red-700': order.status === 3
                                    }">
                                        {{ order.status_text || 'Chờ xử lý' }}
                                    </span>
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Ngày: {{ order.created_at }}
                                </div>
                                <div class="text-gray-700 font-semibold mt-1">
                                    Tổng: {{ formatPrice(order.total_amount) }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Người đặt: {{ order.customer_name || '---' }} - {{ order.customer_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Người nhận: {{ order.receiver_name || '---' }} - {{ order.receiver_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Địa chỉ: {{ order.shipping_address || '---' }}
                                </div>
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