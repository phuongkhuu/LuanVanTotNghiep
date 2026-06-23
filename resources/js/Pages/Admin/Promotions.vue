<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    initialPromotions: {
        type: Array,
        default: () => []
    }
});

// Filter state
const activePromoType = ref('all');
const searchQuery = ref('');

// Promotion types
const promoTypes = [
    { value: 'all', label: 'Tất cả', icon: '📦' },
    { value: 'retail', label: 'Bán lẻ', icon: '🛒' },
    { value: 'wholesale', label: 'Bán sỉ', icon: '🏭' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

// Promotions data
const promotions = ref(props.initialPromotions.length > 0 ? props.initialPromotions : [
    { 
        id: 1, 
        code: 'BIGBAG50', 
        targetType: 'retail', 
        discountType: 'fixed', 
        discount: '50.000₫', 
        discountValue: 50000,
        minOrder: 500000, 
        desc: 'Giảm 50k cho đơn từ 500k', 
        expiry: '30/06/2025', 
        used: 45, 
        limit: 100, 
        active: true 
    },
    { 
        id: 2, 
        code: 'WHOLESALE10', 
        targetType: 'wholesale', 
        discountType: 'percent', 
        discount: '10%', 
        discountValue: 10,
        minOrder: 5000000, 
        desc: 'Giảm 10% cho đơn sỉ từ 5tr', 
        expiry: '31/07/2025', 
        used: 12, 
        limit: 50, 
        active: true 
    },
    { 
        id: 3, 
        code: 'PREORDER5', 
        targetType: 'preorder', 
        discountType: 'fixed', 
        discount: '100.000₫', 
        discountValue: 100000,
        minOrder: 1000000, 
        desc: 'Ưu đãi đặt trước 100k', 
        expiry: '15/06/2025', 
        used: 28, 
        limit: 200, 
        active: true 
    },
    { 
        id: 4, 
        code: 'FREESHIP', 
        targetType: 'all', 
        discountType: 'freeship', 
        discount: 'Miễn phí ship', 
        discountValue: 0,
        minOrder: 300000, 
        desc: 'Miễn phí vận chuyển cho đơn từ 300k', 
        expiry: '31/12/2025', 
        used: 156, 
        limit: 999, 
        active: true 
    }
]);

// Modal state
const showModal = ref(false);
const editingPromo = ref(null);
const isSubmitting = ref(false);

// Form data
const form = ref({
    code: '',
    targetType: 'all',
    discountType: 'fixed',
    discount: '',
    discountValue: 0,
    minOrder: 0,
    limit: 100,
    expiry: '',
    active: true
});

// Computed: filtered promotions
const filteredPromotions = computed(() => {
    let filtered = promotions.value;
    
    // Filter by type
    if (activePromoType.value !== 'all') {
        filtered = filtered.filter(p => p.targetType === activePromoType.value);
    }
    
    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            p.code.toLowerCase().includes(query) ||
            p.desc.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

// Get target type label
const getTargetTypeLabel = (type) => {
    const labels = {
        retail: 'Bán lẻ',
        wholesale: 'Bán sỉ',
        preorder: 'Pre-order',
        all: 'Tất cả'
    };
    return labels[type] || type;
};

// Get target type class
const getTargetTypeClass = (type) => {
    const classes = {
        retail: 'bg-orange-100 text-orange-700',
        wholesale: 'bg-green-100 text-green-700',
        preorder: 'bg-blue-100 text-blue-700',
        all: 'bg-purple-100 text-purple-700'
    };
    return classes[type] || 'bg-gray-100 text-gray-600';
};

// Get status class
const getStatusClass = (active) => {
    return active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
};

// Get status label
const getStatusLabel = (active) => {
    return active ? 'Đang hoạt động' : 'Đã tắt';
};

// Get used percentage
const getUsedPercentage = (used, limit) => {
    return (used / limit) * 100;
};

// Format currency
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// Open modal for add/edit
const openModal = (promo = null) => {
    editingPromo.value = promo;
    
    if (promo) {
        form.value = { ...promo };
    } else {
        form.value = {
            code: '',
            targetType: 'all',
            discountType: 'fixed',
            discount: '',
            discountValue: 0,
            minOrder: 0,
            limit: 100,
            expiry: '',
            active: true
        };
    }
    showModal.value = true;
};

// Edit promotion
const editPromo = (promo) => {
    openModal(promo);
};

// Save promotion
const savePromo = async () => {
    // Validate
    if (!form.value.code) {
        alert('Vui lòng nhập mã giảm giá');
        return;
    }
    if (!form.value.discount && form.value.discountType !== 'freeship') {
        alert('Vui lòng nhập giá trị giảm giá');
        return;
    }
    
    isSubmitting.value = true;
    
    try {
        if (editingPromo.value) {
            // Update existing promotion
            const index = promotions.value.findIndex(p => p.id === editingPromo.value.id);
            if (index !== -1) {
                const discountText = form.value.discountType === 'percent' 
                    ? form.value.discount + '%' 
                    : form.value.discountType === 'freeship' 
                        ? 'Miễn phí ship' 
                        : formatPrice(form.value.discountValue);
                
                promotions.value[index] = {
                    ...form.value,
                    id: editingPromo.value.id,
                    discount: discountText,
                    desc: `${discountText} cho đơn từ ${formatPrice(form.value.minOrder)}`,
                    used: editingPromo.value.used
                };
            }
            
            // Call API update
            await router.put(`/admin/promotions/${editingPromo.value.id}`, form.value, {
                preserveScroll: true,
                onSuccess: () => {
                    alert('Cập nhật mã giảm giá thành công!');
                },
                onError: (errors) => {
                    console.error('Lỗi cập nhật:', errors);
                    alert('Có lỗi xảy ra khi cập nhật');
                }
            });
        } else {
            // Add new promotion
            const discountText = form.value.discountType === 'percent' 
                ? form.value.discount + '%' 
                : form.value.discountType === 'freeship' 
                    ? 'Miễn phí ship' 
                    : formatPrice(form.value.discountValue);
            
            const newPromo = {
                ...form.value,
                id: Date.now(),
                discount: discountText,
                desc: `${discountText} cho đơn từ ${formatPrice(form.value.minOrder)}`,
                used: 0
            };
            promotions.value.push(newPromo);
            
            // Call API create
            await router.post('/admin/promotions', form.value, {
                preserveScroll: true,
                onSuccess: () => {
                    alert('Thêm mã giảm giá thành công!');
                },
                onError: (errors) => {
                    console.error('Lỗi thêm mới:', errors);
                    alert('Có lỗi xảy ra khi thêm mã');
                }
            });
        }
        
        showModal.value = false;
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    } finally {
        isSubmitting.value = false;
    }
};

// Delete promotion
const deletePromo = async (id) => {
    const promo = promotions.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa mã "${promo?.code}"?`)) {
        return;
    }
    
    try {
        await router.delete(`/admin/promotions/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                promotions.value = promotions.value.filter(p => p.id !== id);
                alert('Xóa mã giảm giá thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi xóa:', errors);
                alert('Có lỗi xảy ra khi xóa mã');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// Toggle promotion status
const toggleStatus = async (promo) => {
    try {
        await router.put(`/admin/promotions/${promo.id}/toggle`, {
            active: !promo.active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                promo.active = !promo.active;
                alert(promo.active ? 'Đã kích hoạt mã!' : 'Đã tắt mã!');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// Close modal
const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <Head title="Quản lý khuyến mãi - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Khuyến mãi & Mã giảm giá</h1>
                    <p class="text-gray-600 text-sm mt-1">Tạo và quản lý chương trình khuyến mãi cho bán lẻ, bán sỉ, pre-order</p>
                </div>
                <button 
                    @click="openModal()" 
                    class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm mã
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Tìm mã giảm giá..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Tab loại khuyến mãi -->
            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="type in promoTypes" 
                    :key="type.value" 
                    @click="activePromoType = type.value" 
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="activePromoType === type.value ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ type.icon }} {{ type.label }}
                </button>
            </div>

            <!-- Danh sách khuyến mãi -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="promo in filteredPromotions" 
                    :key="promo.id" 
                    class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <span 
                                class="text-xs px-2 py-1 rounded-full"
                                :class="getStatusClass(promo.active)"
                            >
                                {{ getStatusLabel(promo.active) }}
                            </span>
                            <h3 class="font-bold text-xl text-gray-800 mt-2">{{ promo.code }}</h3>
                        </div>
                        <div class="flex gap-1">
                            <button 
                                @click="editPromo(promo)" 
                                class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                title="Sửa"
                            >Sửa
                            </button>
                            <button 
                                @click="deletePromo(promo.id)" 
                                class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                title="Xóa"
                            >Xóa
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 mb-3">{{ promo.desc }}</p>
                    
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">🎯</span>
                            <span 
                                class="text-xs px-2 py-0.5 rounded-full"
                                :class="getTargetTypeClass(promo.targetType)"
                            >
                                {{ getTargetTypeLabel(promo.targetType) }}
                            </span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">💰</span>
                            <span class="text-orange-600 font-semibold">Giảm: {{ promo.discount }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📅</span>
                            <span>HSD: {{ promo.expiry }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📊</span>
                            <span>Đã dùng: {{ promo.used }}/{{ promo.limit }}</span>
                        </span>
                    </div>
                    
                    <!-- Progress bar -->
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-orange-500 rounded-full transition-all"
                                :style="{ width: getUsedPercentage(promo.used, promo.limit) + '%' }"
                            ></div>
                        </div>
                    </div>
                    
                    <!-- Toggle switch -->
                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleStatus(promo)"
                            :class="promo.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ promo.active ? 'Kích hoạt' : 'Đã tắt' }}
                        </button>
                    </div>
                </div>
                
                <!-- Empty state -->
                <div v-if="filteredPromotions.length === 0" class="col-span-full text-center py-12 text-gray-500">
                    <span class="material-symbols-outlined text-5xl mb-2">local_offer</span>
                    <p>Không có mã giảm giá nào</p>
                    <button 
                        @click="openModal()" 
                        class="mt-3 text-orange-600 hover:underline"
                    >
                        Thêm mã giảm giá đầu tiên
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa mã giảm giá -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ editingPromo ? 'Sửa mã' : 'Thêm mã giảm giá' }}</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mã code</label>
                        <input 
                            v-model="form.code" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 uppercase focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="NHAPMA"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Áp dụng cho</label>
                        <select 
                            v-model="form.targetType" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="retail">🛒 Bán lẻ</option>
                            <option value="wholesale">🏭 Bán sỉ</option>
                            <option value="preorder">⏳ Pre-order</option>
                            <option value="all">📦 Tất cả</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Loại giảm giá</label>
                        <select 
                            v-model="form.discountType" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="fixed">Giảm trực tiếp (₫)</option>
                            <option value="percent">Giảm theo %</option>
                            <option value="freeship">Miễn phí ship</option>
                        </select>
                    </div>
                    
                    <div v-if="form.discountType !== 'freeship'">
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá trị giảm</label>
                        <input 
                            v-model="form.discount" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            :placeholder="form.discountType === 'percent' ? '10' : '50000'"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            {{ form.discountType === 'percent' ? 'Nhập số phần trăm (VD: 10)' : 'Nhập số tiền (VD: 50000)' }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Điều kiện (đơn tối thiểu)</label>
                        <input 
                            v-model="form.minOrder" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="500.000"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Số lượng mã</label>
                        <input 
                            v-model="form.limit" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="100"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày kết thúc</label>
                        <input 
                            v-model="form.expiry" 
                            type="date" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                    </div>
                    
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.active" class="w-4 h-4 text-orange-600 rounded">
                        <span class="text-sm text-gray-700">Kích hoạt ngay</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="savePromo" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>

</style>