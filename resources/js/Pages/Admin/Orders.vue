<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    initialOrders: {
        type: Array,
        default: () => []
    },
    type: {
        type: String,
        default: 'retail'
    }
});

const search = ref('');
const activeType = ref(['retail', 'wholesale', 'preorder'].includes(props.type) ? props.type : 'retail');
const statusFilter = ref('all');
const orders = ref(props.initialOrders);

// Phân trang
const currentPage = ref(1);
const itemsPerPage = 5;

const orderTypes = [
    { value: 'retail', label: 'Bán lẻ', icon: '🛒' },
    { value: 'wholesale', label: 'Bán sỉ', icon: '🏭' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

const statusOptions = {
    retail: [
        { value: 'pending', label: 'Chờ xử lý' },
        { value: 'processing', label: 'Đang xử lý' },
        { value: 'shipping', label: 'Đang giao' },
        { value: 'completed', label: 'Hoàn thành' },
        { value: 'cancelled', label: 'Đã hủy' }
    ],
    wholesale: [
        { value: 'pending', label: 'Chờ xác nhận' },
        { value: 'approved', label: 'Đã duyệt' },
        { value: 'production', label: 'Đang sản xuất' },
        { value: 'shipping', label: 'Đang giao' },
        { value: 'completed', label: 'Hoàn thành' },
        { value: 'cancelled', label: 'Đã hủy' }
    ],
    preorder: [
        { value: 'pending', label: 'Chờ xác nhận' },
        { value: 'confirmed', label: 'Đã xác nhận' },
        { value: 'waiting', label: 'Chờ hàng' },
        { value: 'shipping', label: 'Đang giao' },
        { value: 'completed', label: 'Hoàn thành' },
        { value: 'cancelled', label: 'Đã hủy' }
    ]
};

const statusFilters = computed(() => {
    const filters = ['all'];
    if (statusOptions[activeType.value]) {
        statusOptions[activeType.value].forEach(opt => {
            if (!filters.includes(opt.value)) filters.push(opt.value);
        });
    }
    return filters;
});

const showDetail = ref(false);
const selectedOrder = ref(null);
const originalStatus = ref(null);
const isUpdating = ref(false);

// Lọc đơn hàng (có tìm kiếm)
const filteredOrders = computed(() => {
    if (!orders.value || orders.value.length === 0) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return orders.value.filter(order => {
        const matchType = order.type === activeType.value;
        const matchStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        
        let matchSearch = true;
        if (keyword) {
            const code = (order.code || '').toLowerCase();
            const customer = (order.customer || '').toLowerCase();
            const receiver = (order.receiver || '').toLowerCase();
            const customerPhone = (order.customer_phone || '').toLowerCase();
            const receiverPhone = (order.receiver_phone || '').toLowerCase();
            
            matchSearch = code.includes(keyword) || 
                         customer.includes(keyword) || 
                         receiver.includes(keyword) ||
                         customerPhone.includes(keyword) ||
                         receiverPhone.includes(keyword);
        }
        
        return matchType && matchStatus && matchSearch;
    });
});

// Phân trang dữ liệu
const paginatedOrders = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredOrders.value.slice(start, end);
});

// Tổng số trang
const totalPages = computed(() => {
    return Math.ceil(filteredOrders.value.length / itemsPerPage);
});

// Reset về trang 1 khi thay đổi bộ lọc
const resetPage = () => {
    currentPage.value = 1;
};

// Chuyển trang
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const getTypeCount = (type) => {
    if (!orders.value) return 0;
    return orders.value.filter(o => o.type === type).length;
};

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        processing: 'bg-blue-100 text-blue-800 border-blue-300',
        shipping: 'bg-purple-100 text-purple-800 border-purple-300',
        completed: 'bg-green-100 text-green-800 border-green-300',
        cancelled: 'bg-red-100 text-red-800 border-red-300',
        approved: 'bg-emerald-100 text-emerald-800 border-emerald-300',
        production: 'bg-orange-100 text-orange-800 border-orange-300',
        confirmed: 'bg-cyan-100 text-cyan-800 border-cyan-300',
        waiting: 'bg-amber-100 text-amber-800 border-amber-300'
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300';
};

const getStatusIcon = (status) => {
    const icons = {
        pending: '⏳',
        processing: '🔄',
        shipping: '🚚',
        completed: '✅',
        cancelled: '❌',
        approved: '✓',
        production: '🏭',
        confirmed: '✔️',
        waiting: '⏰'
    };
    return icons[status] || '📋';
};

const getStatusLabel = (status, type) => {
    const options = statusOptions[type] || statusOptions.retail;
    const found = options.find(opt => opt.value === status);
    return found ? found.label : status;
};

const updateStatus = async (order) => {
    isUpdating.value = true;
    try {
        await router.put(`/admin/orders/${order.id}/status`, {
            status: order.status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                order.statusLabel = getStatusLabel(order.status, order.type);
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
                router.reload();
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
    } finally {
        isUpdating.value = false;
    }
};

const viewDetail = (order) => {
    selectedOrder.value = JSON.parse(JSON.stringify(order));
    originalStatus.value = order.status;
    showDetail.value = true;
};

const closeDetail = () => {
    if (selectedOrder.value && originalStatus.value !== null) {
        selectedOrder.value.status = originalStatus.value;
        selectedOrder.value.statusLabel = getStatusLabel(originalStatus.value, selectedOrder.value.type);
    }
    showDetail.value = false;
    selectedOrder.value = null;
    originalStatus.value = null;
};

const updateStatusFromDetail = async () => {
    if (!selectedOrder.value) return;
    
    if (selectedOrder.value.status === originalStatus.value) {
        alert('Trạng thái chưa được thay đổi!');
        return;
    }
    
    isUpdating.value = true;
    try {
        await router.put(`/admin/orders/${selectedOrder.value.id}/status`, {
            status: selectedOrder.value.status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                const newStatus = selectedOrder.value.status;
                selectedOrder.value.statusLabel = getStatusLabel(newStatus, selectedOrder.value.type);
                
                const index = orders.value.findIndex(o => o.id === selectedOrder.value.id);
                if (index !== -1) {
                    orders.value[index].status = newStatus;
                    orders.value[index].statusLabel = selectedOrder.value.statusLabel;
                }
                
                originalStatus.value = newStatus;
                alert('Cập nhật trạng thái thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
                selectedOrder.value.status = originalStatus.value;
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
        selectedOrder.value.status = originalStatus.value;
    } finally {
        isUpdating.value = false;
    }
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    statusFilter.value = 'all';
    search.value = '';
    resetPage();
    router.get(route('admin.orders.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Theo dõi thay đổi search và statusFilter để reset page
watch([search, statusFilter], () => {
    resetPage();
});

watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        statusFilter.value = 'all';
        search.value = '';
        resetPage();
    }
});

watch(() => props.initialOrders, (newOrders) => {
    orders.value = newOrders;
    resetPage();
}, { immediate: true, deep: true });

const exportAllOrders = () => {
    try {
        const url = '/admin/orders/export';
        window.open(url, '_blank');
    } catch (error) {
        console.error('Export all error:', error);
        alert('Có lỗi xảy ra khi xuất file');
    }
};

const exportFilteredOrders = () => {
    try {
        const params = new URLSearchParams({
            type: activeType.value,
            status: statusFilter.value,
        });
        
        const url = `/admin/orders/export/filtered?${params.toString()}`;
        window.open(url, '_blank');
    } catch (error) {
        console.error('Export filtered error:', error);
        alert('Có lỗi xảy ra khi xuất file');
    }
};

</script>

<template>
    <Head title="Quản lý đơn hàng - BigBag Admin" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý đơn hàng</h1>
            </div>

            <!-- Tab loại đơn hàng -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button
                    v-for="tab in orderTypes"
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
                        placeholder="Tìm theo mã đơn, tên hoặc SĐT người đặt/nhận..."
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Bộ lọc trạng thái -->
            <div class="flex flex-wrap justify-between gap-4 mb-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="status in statusFilters"
                        :key="status"
                        @click="statusFilter = status"
                        class="px-3 py-1 text-xs rounded-full transition-all"
                        :class="statusFilter === status ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                    >
                        {{ status === 'all' ? 'Tất cả' : getStatusLabel(status, activeType) }}
                    </button>
                </div>
            </div>

            <!-- Danh sách đơn hàng -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">MÃ ĐƠN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">NGƯỜI ĐẶT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">NGƯỜI NHẬN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">TỔNG TIỀN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">HÌNH THỨC</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in paginatedOrders" :key="order.id" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 whitespace-nowrap">{{ order.code }}</td>
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ order.customer || 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ order.customer_phone || '' }}</p>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ order.receiver || 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ order.receiver_phone || '' }}</p>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ order.date }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600 whitespace-nowrap">{{ formatPrice(order.amount) }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap" :class="order.paymentClass">{{ order.payment }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <select
                                        v-model="order.status"
                                        @change="updateStatus(order)"
                                        class="text-xs px-3 py-1.5 rounded-full border-2 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
                                        style="min-width: 120px;"
                                        :class="getStatusClass(order.status)"
                                        :disabled="isUpdating"
                                    >
                                        <option
                                            v-for="s in statusOptions[activeType]"
                                            :key="s.value"
                                            :value="s.value"
                                        >
                                            {{ s.label }}
                                        </option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button
                                        @click="viewDetail(order)"
                                        class="px-3 py-1.5 text-xs text-orange-600 hover:bg-orange-100 rounded-lg transition-colors font-medium"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                    <button
                                        class="px-3 py-1.5 text-xs text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors font-medium"
                                        title="In đơn hàng"
                                    >
                                        In
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="paginatedOrders.length === 0">
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy đơn hàng nào' : 'Không có đơn hàng nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer và Phân trang -->
                <div class="p-3 border-t border-gray-200 flex flex-wrap justify-between items-center gap-2">
                    <span class="text-sm text-gray-500">
                        Hiển thị {{ paginatedOrders.length }} / {{ filteredOrders.length }} đơn hàng
                    </span>
                    
                    <!-- Phân trang -->
                    <div v-if="totalPages > 1" class="flex items-center gap-2">
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                @click="goToPage(page)"
                                class="px-3 py-1 text-sm rounded-lg transition-colors"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
                    
                    <div class="flex gap-2">
                        <button
                            @click="exportAllOrders"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">download</span>
                            Xuất tất cả
                        </button>
                        <button
                            v-if="filteredOrders.length > 0"
                            @click="exportFilteredOrders"
                            class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-700 transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">filter_alt</span>
                            Xuất theo bộ lọc
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal chi tiết đơn hàng -->
            <div
                v-if="showDetail && selectedOrder"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="closeDetail"
            >
                <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-gray-800">Chi tiết đơn hàng {{ selectedOrder.code }}</h3>
                        <button
                            @click="closeDetail"
                            class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Trạng thái đơn hàng -->
                        <div class="bg-gray-50 rounded-lg p-4 border-2" :class="getStatusClass(selectedOrder.status)">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ getStatusIcon(selectedOrder.status) }}</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Trạng thái đơn hàng</p>
                                        <p class="font-bold text-lg" :class="getStatusClass(selectedOrder.status)">
                                            {{ getStatusLabel(selectedOrder.status, selectedOrder.type) }}
                                        </p>
                                    </div>
                                </div>
                                <select
                                    v-model="selectedOrder.status"
                                    class="text-sm px-3 py-2 rounded-lg border-2 font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
                                    style="min-width: 140px;"
                                    :class="getStatusClass(selectedOrder.status)"
                                    :disabled="isUpdating"
                                >
                                    <option
                                        v-for="s in statusOptions[selectedOrder.type]"
                                        :key="s.value"
                                        :value="s.value"
                                    >
                                        {{ s.label }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="selectedOrder.status !== originalStatus" class="mt-2 text-xs text-orange-600">
                                ⚠️ Bạn đã thay đổi trạng thái. Nhấn "Cập nhật trạng thái" để lưu.
                            </div>
                        </div>

                        <!-- Thông tin khách hàng -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Người đặt</p>
                                <p class="font-medium text-gray-800">{{ selectedOrder.customer || 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ selectedOrder.customer_phone || '' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Người nhận</p>
                                <p class="font-medium text-gray-800">{{ selectedOrder.receiver || 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ selectedOrder.receiver_phone || '' }}</p>
                            </div>
                        </div>

                        <!-- Ngày + Hình thức -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Ngày đặt</p>
                                <p class="text-gray-600">{{ selectedOrder.date }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Hình thức</p>
                                <p class="text-gray-600">{{ selectedOrder.payment }}</p>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm -->
                        <div class="border-t border-gray-200 pt-3">
                            <p class="font-medium text-gray-800 mb-2">Sản phẩm</p>
                            <div class="space-y-2 text-sm">
                                <div
                                    v-for="(product, idx) in selectedOrder.products"
                                    :key="idx"
                                    class="flex justify-between items-start border-b border-gray-100 pb-2"
                                >
                                    <div>
                                        <span class="text-gray-800 font-medium">{{ product.name }}</span>
                                        <span class="text-gray-500 ml-2">x{{ product.quantity }}</span>
                                        <div class="text-xs text-gray-400">{{ formatPrice(product.price) }} / cái</div>
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ formatPrice(product.subtotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tổng hợp chi phí -->
                        <div class="border-t border-gray-200 pt-3 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tạm tính</span>
                                <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phí vận chuyển</span>
                                <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder.shipping_fee) }}</span>
                            </div>
                            <div v-if="selectedOrder.discount_amount > 0" class="flex justify-between">
                                <span class="text-gray-600">Giảm giá</span>
                                <span class="font-medium text-red-600">-{{ formatPrice(selectedOrder.discount_amount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold pt-2 border-t border-gray-200">
                                <span class="text-gray-800">Tổng cộng</span>
                                <span class="text-orange-600">{{ formatPrice(selectedOrder.final_amount) }}</span>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="border-t border-gray-200 pt-3">
                            <p class="font-medium text-gray-800 mb-1">Địa chỉ giao hàng</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder.address }}</p>
                        </div>

                        <!-- Ghi chú -->
                        <div v-if="selectedOrder.note" class="border-t border-gray-200 pt-3">
                            <p class="font-medium text-gray-800 mb-1">Ghi chú</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder.note }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            @click="closeDetail"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                            Đóng
                        </button>
                        <button
                            @click="updateStatusFromDetail"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isUpdating || selectedOrder.status === originalStatus"
                        >
                            {{ isUpdating ? 'Đang cập nhật...' : 'Cập nhật trạng thái' }}
                        </button>
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

/* Responsive cho màn hình nhỏ */
@media (max-width: 768px) {
    select {
        font-size: 11px;
        padding: 4px 8px;
        min-width: 100px !important;
    }
}
</style>