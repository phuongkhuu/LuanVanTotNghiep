<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    initialCustomers: {
        type: Array,
        default: () => []
    }
});

// Search and filters
const search = ref('');
const activeType = ref('retail');

// Customer types tabs
const customerTypes = [
    { value: 'retail', label: 'Khách lẻ', icon: '👤' },
    { value: 'business', label: 'Doanh nghiệp (B2B)', icon: '🏢' }
];

// Tier options for each customer type
const tierOptions = {
    retail: ['Thường', 'VIP', 'Thân thiết'],
    business: ['Đối tác mới', 'Đối tác thân thiết', 'Đối tác chiến lược']
};

// Customers data
const customers = ref(props.initialCustomers.length > 0 ? props.initialCustomers : [
    { 
        id: 1, 
        name: 'Nguyễn Văn A', 
        email: 'nguyenvana@email.com', 
        phone: '0901234567', 
        orders: 12, 
        totalSpent: 28500000, 
        tier: 'VIP', 
        type: 'retail',
        address: '123 Đường ABC, Quận 1, TP.HCM',
        joinDate: '01/01/2024',
        lastOrder: '04/06/2025'
    },
    { 
        id: 2, 
        name: 'Công ty TNHH ABC', 
        email: 'contact@abc.com', 
        phone: '0912345678', 
        orders: 8, 
        totalSpent: 156000000, 
        tier: 'Đối tác thân thiết', 
        type: 'business',
        address: '456 Đường XYZ, Quận 2, TP.HCM',
        taxCode: '0123456789',
        contactPerson: 'Trần Văn B',
        joinDate: '15/03/2024',
        lastOrder: '03/06/2025'
    },
    { 
        id: 3, 
        name: 'Trần Thị B', 
        email: 'tranb@email.com', 
        phone: '0923456789', 
        orders: 5, 
        totalSpent: 8900000, 
        tier: 'Thường', 
        type: 'retail',
        address: '789 Đường DEF, Quận 3, TP.HCM',
        joinDate: '20/02/2024',
        lastOrder: '01/06/2025'
    },
    { 
        id: 4, 
        name: 'Lê Thị C', 
        email: 'lethic@email.com', 
        phone: '0934567890', 
        orders: 18, 
        totalSpent: 45200000, 
        tier: 'Thân thiết', 
        type: 'retail',
        address: '321 Đường GHI, Quận 4, TP.HCM',
        joinDate: '10/11/2023',
        lastOrder: '05/06/2025'
    },
    { 
        id: 5, 
        name: 'Doanh nghiệp XYZ', 
        email: 'info@xyz.com', 
        phone: '0945678901', 
        orders: 15, 
        totalSpent: 389000000, 
        tier: 'Đối tác chiến lược', 
        type: 'business',
        address: '654 Đường JKL, Quận 5, TP.HCM',
        taxCode: '9876543210',
        contactPerson: 'Phạm Văn D',
        joinDate: '05/05/2023',
        lastOrder: '02/06/2025'
    }
]);

// Modal state
const showDetailModal = ref(false);
const selectedCustomer = ref(null);
const isUpdating = ref(false);

// Computed: filtered customers
const filteredCustomers = computed(() => {
    if (!customers.value || customers.value.length === 0) return [];
    
    return customers.value.filter(customer => {
        const matchType = customer.type === activeType.value;
        const matchSearch = !search.value || 
            customer.name.toLowerCase().includes(search.value.toLowerCase()) || 
            customer.email.toLowerCase().includes(search.value.toLowerCase()) ||
            customer.phone.includes(search.value);
        return matchType && matchSearch;
    });
});

// Get count by type
const getTypeCount = (type) => {
    if (!customers.value) return 0;
    return customers.value.filter(c => c.type === type).length;
};

// Statistics
const newCustomers = computed(() => {
    return filteredCustomers.value.filter(c => c.orders <= 2).length;
});

const vipCount = computed(() => {
    return filteredCustomers.value.filter(c => 
        c.tier === 'VIP' || 
        c.tier === 'Thân thiết' || 
        c.tier === 'Đối tác thân thiết' || 
        c.tier === 'Đối tác chiến lược'
    ).length;
});

const avgSpent = computed(() => {
    if (filteredCustomers.value.length === 0) return 0;
    const total = filteredCustomers.value.reduce((sum, c) => sum + c.totalSpent, 0);
    return total / filteredCustomers.value.length;
});

// Format price to VND
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Get tier badge class
const getTierClass = (tier, type) => {
    const vipTiers = ['VIP', 'Thân thiết', 'Đối tác thân thiết', 'Đối tác chiến lược'];
    if (vipTiers.includes(tier)) {
        return 'bg-orange-100 text-orange-700';
    }
    return 'bg-gray-100 text-gray-600';
};

// Update customer tier
const updateTier = async (customer) => {
    isUpdating.value = true;
    try {
        await router.put(`/admin/customers/${customer.id}`, {
            tier: customer.tier
        }, {
            preserveScroll: true,
            onSuccess: () => {
                console.log(`Đã cập nhật hạng cho khách hàng ${customer.name}`);
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật hạng khách hàng');
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật hạng khách hàng');
    } finally {
        isUpdating.value = false;
    }
};

// View customer detail
const viewDetail = (customer) => {
    selectedCustomer.value = customer;
    showDetailModal.value = true;
};

// Export to Excel
const exportExcel = async () => {
    try {
        await router.post('/admin/customers/export', {
            type: activeType.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Xuất file Excel thành công!');
            }
        });
    } catch (error) {
        alert('Có lỗi xảy ra khi xuất file');
    }
};

// Send email to customer
const sendEmail = (customer) => {
    // Mở modal gửi email hoặc chuyển hướng
    alert(`Chức năng gửi email đến ${customer.email} đang được phát triển`);
};

// Format date
const formatDate = (date) => {
    if (!date) return '---';
    return date;
};
</script>

<template>
    <Head title="Quản lý khách hàng - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khách hàng</h1>
                    <p class="text-gray-600 text-sm mt-1">Quản lý khách hàng lẻ và doanh nghiệp (B2B)</p>
                </div>
                <button 
                    @click="exportExcel" 
                    class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors"
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
                    @click="activeType = tab.value" 
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="activeType === tab.value ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ tab.icon }} {{ tab.label }} 
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ getTypeCount(tab.value) }}</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm kiếm khách hàng theo tên, email hoặc số điện thoại..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Tổng {{ activeType === 'retail' ? 'KH lẻ' : 'KH DN' }}</span>
                        <span class="material-symbols-outlined text-orange-500">{{ activeType === 'retail' ? 'people' : 'business' }}</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ filteredCustomers.length }}</p>
                </div>
                
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">KH mới (tháng)</span>
                        <span class="material-symbols-outlined text-orange-500">person_add</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ newCustomers }}</p>
                </div>
                
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">{{ activeType === 'retail' ? 'KH VIP' : 'Đối tác thân thiết' }}</span>
                        <span class="material-symbols-outlined text-orange-500">{{ activeType === 'retail' ? 'star' : 'handshake' }}</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ vipCount }}</p>
                </div>
                
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Doanh thu TB</span>
                        <span class="material-symbols-outlined text-orange-500">payments</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatPrice(avgSpent) }}</p>
                </div>
            </div>

            <!-- Danh sách khách hàng -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">{{ activeType === 'retail' ? 'KHÁCH HÀNG' : 'TÊN CÔNG TY' }}</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">EMAIL</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SĐT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">ĐƠN HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG CHI</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">HẠNG</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="customer in filteredCustomers" 
                                :key="customer.id" 
                                class="border-b border-gray-200 hover:bg-orange-50 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
                                            {{ customer.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ customer.name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ customer.email }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ customer.phone }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ customer.orders }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(customer.totalSpent) }}</td>
                                <td class="py-3 px-4">
                                    <select 
                                        v-model="customer.tier" 
                                        @change="updateTier(customer)"
                                        class="text-xs px-2 py-1 rounded-full font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
                                        :class="getTierClass(customer.tier, customer.type)"
                                        :disabled="isUpdating"
                                    >
                                        <option 
                                            v-for="tier in tierOptions[activeType]" 
                                            :key="tier" 
                                            :value="tier"
                                            class="text-gray-800"
                                        >
                                            {{ tier }}
                                        </option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(customer)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </button>
                                    <button 
                                        @click="sendEmail(customer)" 
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="Gửi email"
                                    >
                                        <span class="material-symbols-outlined text-lg">mail</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredCustomers.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    Không có khách hàng nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết khách hàng -->
        <div 
            v-if="showDetailModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showDetailModal = false"
        >
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Chi tiết khách hàng</h3>
                    <button 
                        @click="showDetailModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Avatar và tên -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-200">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-2xl">
                            {{ selectedCustomer?.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">{{ selectedCustomer?.name }}</h4>
                            <p class="text-sm text-gray-500">
                                {{ activeType === 'retail' ? 'Khách hàng lẻ' : 'Khách hàng doanh nghiệp' }}
                                • Hạng: <span class="font-medium" :class="getTierClass(selectedCustomer?.tier, selectedCustomer?.type)">{{ selectedCustomer?.tier }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Thông tin liên hệ -->
                    <div>
                        <h5 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-lg">contact_mail</span>
                            Thông tin liên hệ
                        </h5>
                        <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm font-medium text-gray-800">{{ selectedCustomer?.email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Số điện thoại</p>
                                <p class="text-sm font-medium text-gray-800">{{ selectedCustomer?.phone }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500">Địa chỉ</p>
                                <p class="text-sm text-gray-800">{{ selectedCustomer?.address || 'Chưa cập nhật' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin doanh nghiệp (nếu có) -->
                    <div v-if="activeType === 'business' && selectedCustomer?.taxCode">
                        <h5 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-lg">business</span>
                            Thông tin doanh nghiệp
                        </h5>
                        <div class="bg-gray-50 p-3 rounded-lg space-y-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Mã số thuế</p>
                                    <p class="text-sm font-medium text-gray-800">{{ selectedCustomer?.taxCode }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Người liên hệ</p>
                                    <p class="text-sm font-medium text-gray-800">{{ selectedCustomer?.contactPerson || '---' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thống kê mua hàng -->
                    <div>
                        <h5 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-lg">shopping_bag</span>
                            Thống kê mua hàng
                        </h5>
                        <div class="grid grid-cols-3 gap-3 bg-gray-50 p-3 rounded-lg">
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Đơn hàng</p>
                                <p class="text-lg font-bold text-orange-600">{{ selectedCustomer?.orders }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Tổng chi tiêu</p>
                                <p class="text-lg font-bold text-orange-600">{{ formatPrice(selectedCustomer?.totalSpent) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Đơn hàng cuối</p>
                                <p class="text-sm font-medium text-gray-800">{{ formatDate(selectedCustomer?.lastOrder) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ngày tham gia -->
                    <div v-if="selectedCustomer?.joinDate">
                        <p class="text-xs text-gray-500">Ngày tham gia</p>
                        <p class="text-sm text-gray-800">{{ selectedCustomer?.joinDate }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
                    </button>
                    <button 
                        @click="sendEmail(selectedCustomer)" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                    >
                        Gửi email
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* No additional styles needed - using Tailwind classes */
</style>