<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

// Props
const props = defineProps({
    campaigns: {
        type: Array,
        default: () => []
    },
    vouchers: {
        type: Array,
        default: () => []
    },
    preorders: {
        type: Array,
        default: () => []
    },
    banners: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    },
    productVariants: {
        type: Array,
        default: () => []
    },
    preorderProducts: {
        type: Array,
        default: () => []
    }
});

// Flash messages
const page = usePage();
const flash = computed(() => page.props.flash || {});
const showSuccess = ref(false);
const successMessage = ref('');

// State
const campaigns = ref(props.campaigns || []);
const vouchers = ref(props.vouchers || []);
const preorders = ref(props.preorders || []);
const banners = ref(props.banners || []);
const products = ref(props.products || []);
const productVariants = ref(props.productVariants || []);
const preorderProducts = ref(props.preorderProducts || []);

// Search & Filter
const searchQuery = ref('');
const activeTab = ref('campaigns');
const activeStatusTab = ref('all');

// Modal
const showModal = ref(false);
const showVoucherModal = ref(false);
const showPreorderModal = ref(false);
const editingCampaign = ref(null);
const editingVoucher = ref(null);
const editingPreorder = ref(null);
const isSubmitting = ref(false);
const errorMessage = ref('');

// Status options
const statusOptions = [
    { value: 'all', label: 'Tất cả' },
    { value: 'active', label: 'Đang diễn ra' },
    { value: 'ended', label: 'Đã kết thúc' },
    { value: 'scheduled', label: 'Sắp diễn ra' }
];

// Campaign Form
const campaignForm = ref({
    id: null,
    name: '',
    type: 'seasonal',
    description: '',
    startDate: '',
    endDate: '',
    status: 'scheduled',
    priority: 0,
    featured: false,
    quantity: 1,
    discountPercent: 0,
    products: []
});

// Voucher Form
const voucherForm = ref({
    id: null,
    code: '',
    target_type: 'all',
    discount_type: 'percent',
    discount_value: 0,
    min_order: 0,
    limit: 100,
    expiry: '',
    active: true,
    description: '',
    campaign_id: null
});

// Preorder Form
const preorderForm = ref({
    id: null,
    name: '',
    product_id: null,
    tiers: [
        { from: 1, to: 10, discount: 20 },
        { from: 11, to: 20, discount: 10 },
        { from: 21, to: 30, discount: 5 }
    ],
    start_date: '',
    end_date: '',
    active: true,
    min_order: 0,
    campaign_id: null
});

// ==================== HELPER FUNCTIONS ====================

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatPrice = (value) => {
    if (!value) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

const getStatusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-700',
        ended: 'bg-gray-100 text-gray-600',
        scheduled: 'bg-blue-100 text-blue-700'
    };
    return classes[status] || 'bg-gray-100 text-gray-600';
};

const getStatusLabel = (status) => {
    const labels = {
        active: 'Đang diễn ra',
        ended: 'Đã kết thúc',
        scheduled: 'Sắp diễn ra'
    };
    return labels[status] || status;
};

const getDiscountTypeLabel = (type) => {
    const labels = {
        fixed: 'Giảm trực tiếp',
        percent: 'Giảm theo %',
        freeship: 'Miễn phí ship'
    };
    return labels[type] || type;
};

const getTargetTypeLabel = (type) => {
    const labels = {
        retail: 'Bán lẻ',
        wholesale: 'Bán sỉ',
        preorder: 'Pre-order',
        all: 'Tất cả'
    };
    return labels[type] || type;
};

const getProductName = (productId) => {
    if (!productId) return 'Chưa chọn';
    const product = preorderProducts.value.find(p => p.id === productId);
    return product ? product.name : 'Sản phẩm không tồn tại';
};

// ==================== FUNCTION TÍNH TRẠNG THÁI ====================

const calculateCampaignStatus = (startDate, endDate) => {
    if (!startDate) return 'scheduled';
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const start = new Date(startDate);
    start.setHours(0, 0, 0, 0);
    
    if (start.getTime() > today.getTime()) {
        return 'scheduled';
    }
    
    if (endDate) {
        const end = new Date(endDate);
        end.setHours(0, 0, 0, 0);
        
        if (end.getTime() < today.getTime()) {
            return 'ended';
        }
        
        if (end.getTime() >= today.getTime() && start.getTime() <= today.getTime()) {
            return 'active';
        }
    }
    
    if (start.getTime() <= today.getTime()) {
        return 'active';
    }
    
    return 'scheduled';
};

// ==================== DATE FUNCTIONS ====================

const today = new Date().toISOString().split('T')[0];

const minEndDate = computed(() => {
    return campaignForm.value.startDate || today;
});

const updateStatusFromDates = () => {
    const startDate = campaignForm.value.startDate;
    const endDate = campaignForm.value.endDate;
    campaignForm.value.status = calculateCampaignStatus(startDate, endDate);
};

watch(() => campaignForm.value.startDate, updateStatusFromDates);
watch(() => campaignForm.value.endDate, updateStatusFromDates);

// ==================== RELOAD FUNCTION ====================

const reloadPage = () => {
    window.location.reload();
};

// ==================== CAMPAIGN FUNCTIONS ====================

const openCampaignModal = (campaign = null) => {
    editingCampaign.value = campaign;
    errorMessage.value = '';
    
    if (campaign) {
        campaignForm.value = {
            id: campaign.id,
            name: campaign.name || '',
            type: campaign.type || 'seasonal',
            description: campaign.description || '',
            startDate: campaign.startDate || '',
            endDate: campaign.endDate || '',
            status: campaign.status || 'scheduled',
            priority: campaign.priority || 0,
            featured: campaign.featured || false,
            quantity: campaign.quantity || 1,
            discountPercent: campaign.discountPercent || 0,
            products: campaign.products || []
        };
    } else {
        campaignForm.value = {
            id: null,
            name: '',
            type: 'seasonal',
            description: '',
            startDate: '',
            endDate: '',
            status: 'scheduled',
            priority: 0,
            featured: false,
            quantity: 1,
            discountPercent: 0,
            products: []
        };
    }
    showModal.value = true;
};

const saveCampaign = async () => {
    if (!campaignForm.value.name) {
        errorMessage.value = 'Vui lòng nhập tên chiến dịch';
        return;
    }
    
    if (!campaignForm.value.quantity || campaignForm.value.quantity <= 0) {
        errorMessage.value = 'Số lượng tối thiểu phải lớn hơn 0';
        return;
    }
    
    if (campaignForm.value.discountPercent < 0 || campaignForm.value.discountPercent > 100) {
        errorMessage.value = 'Giảm giá phải từ 0% đến 100%';
        return;
    }
    
    if (campaignForm.value.startDate && campaignForm.value.endDate) {
        if (new Date(campaignForm.value.endDate) < new Date(campaignForm.value.startDate)) {
            errorMessage.value = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu';
            return;
        }
    }
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        const status = calculateCampaignStatus(campaignForm.value.startDate, campaignForm.value.endDate);
        
        const data = {
            name: campaignForm.value.name,
            type: campaignForm.value.type,
            description: campaignForm.value.description,
            startDate: campaignForm.value.startDate,
            endDate: campaignForm.value.endDate,
            status: status,
            priority: parseInt(campaignForm.value.priority) || 0,
            featured: campaignForm.value.featured || false,
            quantity: parseInt(campaignForm.value.quantity) || 1,
            discountPercent: parseFloat(campaignForm.value.discountPercent) || 0,
            products: Array.isArray(campaignForm.value.products) ? campaignForm.value.products : []
        };
        
        if (editingCampaign.value) {
            await router.put(`/admin/promotions/campaign/${editingCampaign.value.id}`, data);
        } else {
            await router.post('/admin/promotions/campaign', data);
        }
        
        closeModal();
        reloadPage();
        
    } catch (error) {
        console.error('Lỗi:', error);
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            errorMessage.value = Object.values(errors).flat().join(', ');
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
        }
        isSubmitting.value = false;
    }
};

const deleteCampaign = async (id) => {
    const campaign = campaigns.value.find(c => c.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chiến dịch "${campaign?.name}"?`)) {
        return;
    }
    
    try {
        await router.delete(`/admin/promotions/campaign/${id}`);
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra khi xóa');
    }
};

const toggleCampaignStatus = async (campaign) => {
    const newStatus = campaign.status === 'active' ? 'ended' : 'active';
    try {
        await router.put(`/admin/promotions/campaign/${campaign.id}/status`, { status: newStatus });
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// ==================== PRODUCT SELECTION ====================

const toggleProduct = (variantId) => {
    const index = campaignForm.value.products.indexOf(variantId);
    if (index > -1) {
        campaignForm.value.products.splice(index, 1);
    } else {
        campaignForm.value.products.push(variantId);
    }
};

const isProductSelected = (variantId) => {
    return campaignForm.value.products.includes(variantId);
};

// ==================== VOUCHER FUNCTIONS ====================

const openVoucherModal = (voucher = null) => {
    editingVoucher.value = voucher;
    errorMessage.value = '';
    
    if (voucher) {
        voucherForm.value = {
            id: voucher.id,
            code: voucher.code || '',
            target_type: voucher.target_type || 'all',
            discount_type: voucher.discount_type || 'percent',
            discount_value: voucher.discount_value || 0,
            min_order: voucher.min_order || 0,
            limit: voucher.limit || 100,
            expiry: voucher.expiry || '',
            active: voucher.status === 'active',
            description: voucher.description || '',
            campaign_id: voucher.campaign_id || null
        };
    } else {
        voucherForm.value = {
            id: null,
            code: '',
            target_type: 'all',
            discount_type: 'percent',
            discount_value: 0,
            min_order: 0,
            limit: 100,
            expiry: '',
            active: true,
            description: '',
            campaign_id: null
        };
    }
    showVoucherModal.value = true;
};

const saveVoucher = async () => {
    if (!voucherForm.value.code) {
        errorMessage.value = 'Vui lòng nhập mã giảm giá';
        return;
    }
    
    if (voucherForm.value.discount_value < 0) {
        errorMessage.value = 'Giá trị giảm giá không được âm';
        return;
    }
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        if (editingVoucher.value) {
            await router.put(`/admin/promotions/voucher/${editingVoucher.value.id}`, voucherForm.value);
        } else {
            await router.post('/admin/promotions/voucher', voucherForm.value);
        }
        closeVoucherModal();
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
        isSubmitting.value = false;
    }
};

const deleteVoucher = async (id) => {
    const voucher = vouchers.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa mã "${voucher?.code}"?`)) {
        return;
    }
    
    try {
        await router.delete(`/admin/promotions/voucher/${id}`);
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra khi xóa');
    }
};

const toggleVoucher = async (voucher) => {
    try {
        await router.put(`/admin/promotions/voucher/${voucher.id}/toggle`);
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// ==================== PRE-ORDER FUNCTIONS ====================

const openPreorderModal = (preorder = null) => {
    editingPreorder.value = preorder;
    errorMessage.value = '';
    
    if (preorder) {
        preorderForm.value = {
            id: preorder.id,
            name: preorder.name || '',
            product_id: preorder.product_id || null,
            tiers: preorder.tiers || [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: preorder.start_date || '',
            end_date: preorder.end_date || '',
            active: preorder.status === 'active',
            min_order: preorder.min_order || 0,
            campaign_id: preorder.campaign_id || null
        };
    } else {
        preorderForm.value = {
            id: null,
            name: '',
            product_id: null,
            tiers: [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: '',
            end_date: '',
            active: true,
            min_order: 0,
            campaign_id: null
        };
    }
    showPreorderModal.value = true;
};

const savePreorder = async () => {
    if (!preorderForm.value.name) {
        errorMessage.value = 'Vui lòng nhập tên chương trình';
        return;
    }
    if (!preorderForm.value.product_id) {
        errorMessage.value = 'Vui lòng chọn sản phẩm pre-order';
        return;
    }
    
    if (preorderForm.value.start_date && preorderForm.value.end_date) {
        if (new Date(preorderForm.value.end_date) < new Date(preorderForm.value.start_date)) {
            errorMessage.value = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu';
            return;
        }
    }
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        if (editingPreorder.value) {
            await router.put(`/admin/promotions/preorder/${editingPreorder.value.id}`, preorderForm.value);
        } else {
            await router.post('/admin/promotions/preorder', preorderForm.value);
        }
        closePreorderModal();
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
        isSubmitting.value = false;
    }
};

const deletePreorder = async (id) => {
    const preorder = preorders.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chương trình "${preorder?.name}"?`)) {
        return;
    }
    
    try {
        await router.delete(`/admin/promotions/preorder/${id}`);
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra khi xóa');
    }
};

const togglePreorder = async (preorder) => {
    try {
        await router.put(`/admin/promotions/preorder/${preorder.id}/toggle`);
        reloadPage();
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

const addTier = () => {
    const tiers = preorderForm.value.tiers;
    const lastTier = tiers[tiers.length - 1];
    const newFrom = lastTier ? lastTier.to + 1 : 1;
    const newTo = newFrom + 9;
    preorderForm.value.tiers.push({
        from: newFrom,
        to: newTo,
        discount: 5
    });
};

const removeTier = (index) => {
    if (preorderForm.value.tiers.length > 1) {
        preorderForm.value.tiers.splice(index, 1);
    }
};

// ==================== MODAL FUNCTIONS ====================

const closeModal = () => {
    showModal.value = false;
    editingCampaign.value = null;
    errorMessage.value = '';
    isSubmitting.value = false;
};

const closeVoucherModal = () => {
    showVoucherModal.value = false;
    editingVoucher.value = null;
    errorMessage.value = '';
    isSubmitting.value = false;
};

const closePreorderModal = () => {
    showPreorderModal.value = false;
    editingPreorder.value = null;
    errorMessage.value = '';
    isSubmitting.value = false;
};

// Show flash message
const showFlashMessage = () => {
    if (flash.value && flash.value.success && flash.value.message) {
        successMessage.value = flash.value.message;
        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
        }, 5000);
    }
};

watch(() => page.props.flash, () => {
    showFlashMessage();
}, { deep: true });

// ==================== COMPUTED ====================

const processedCampaigns = computed(() => {
    return campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
});

const filteredCampaigns = computed(() => {
    let filtered = processedCampaigns.value || [];
    
    if (activeStatusTab.value !== 'all') {
        filtered = filtered.filter(c => c.status === activeStatusTab.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(c => 
            (c.name && c.name.toLowerCase().includes(query)) ||
            (c.description && c.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const campaignCounts = computed(() => {
    const counts = {
        all: campaigns.value.length,
        active: 0,
        ended: 0,
        scheduled: 0
    };
    
    processedCampaigns.value.forEach(c => {
        if (c.status === 'active') counts.active++;
        else if (c.status === 'ended') counts.ended++;
        else if (c.status === 'scheduled') counts.scheduled++;
    });
    
    return counts;
});

// Vouchers
const filteredVouchers = computed(() => {
    let filtered = vouchers.value || [];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const voucherCounts = computed(() => {
    const vouchersData = vouchers.value || [];
    return {
        all: vouchersData.length,
        active: vouchersData.filter(v => v.status === 'active').length,
        inactive: vouchersData.filter(v => v.status !== 'active').length
    };
});

// Pre-orders
const filteredPreorders = computed(() => {
    let filtered = preorders.value || [];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const preorderCounts = computed(() => {
    const preordersData = preorders.value || [];
    return {
        all: preordersData.length,
        active: preordersData.filter(p => p.status === 'active').length,
        inactive: preordersData.filter(p => p.status !== 'active').length
    };
});

onMounted(() => {
    showFlashMessage();
});
</script>

<template>
    <Head title="Quản lý khuyến mãi - BigBag Admin" />
    
    <AdminLayout>
        <!-- Flash Message -->
        <div v-if="showSuccess" class="fixed top-20 right-4 z-50 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl shadow-lg max-w-md animate-slide-in">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-green-500">check_circle</span>
                <span>{{ successMessage }}</span>
            </div>
        </div>

        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khuyến mãi</h1>
                    <p class="text-sm text-gray-500 mt-1">Quản lý chiến dịch, mã giảm giá và pre-order</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button @click="openCampaignModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-blue-700 transition-colors text-sm">
                        <span class="material-symbols-outlined text-lg">add</span>
                        Chiến dịch
                    </button>
                    <button @click="openVoucherModal()" class="bg-orange-600 text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors text-sm">
                        <span class="material-symbols-outlined text-lg">local_offer</span>
                        Mã giảm giá
                    </button>
                    <button @click="openPreorderModal()" class="bg-purple-600 text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-purple-700 transition-colors text-sm">
                        <span class="material-symbols-outlined text-lg">schedule</span>
                        Pre-order
                    </button>
                </div>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Tìm kiếm..." 
                        class="pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Main Tabs -->
            <div class="flex gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
                <button 
                    @click="activeTab = 'campaigns'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'campaigns' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    🎯 Chiến dịch
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ campaigns.length }}</span>
                </button>
                <button 
                    @click="activeTab = 'vouchers'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'vouchers' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    🎫 Mã giảm giá
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ vouchers.length }}</span>
                </button>
                <button 
                    @click="activeTab = 'preorder'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'preorder' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    ⏳ Pre-order
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ preorders.length }}</span>
                </button>
            </div>

            <!-- Status tabs for campaigns -->
            <div v-if="activeTab === 'campaigns'" class="flex gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="status in statusOptions" 
                    :key="status.value"
                    @click="activeStatusTab = status.value"
                    class="px-4 py-2 text-sm font-medium transition-all"
                    :class="activeStatusTab === status.value ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ status.label }}
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">
                        {{ campaignCounts[status.value] }}
                    </span>
                </button>
            </div>

            <!-- ==================== CAMPAIGNS LIST ==================== -->
            <div v-if="activeTab === 'campaigns'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-36 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="w-full h-full flex items-center justify-center text-blue-300">
                            <span class="material-symbols-outlined text-5xl">campaign</span>
                        </div>
                        
                        <div class="absolute top-2 right-2 flex gap-1 flex-wrap">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium" :class="getStatusClass(campaign.status)">
                                {{ getStatusLabel(campaign.status) }}
                            </span>
                            <span v-if="campaign.featured" class="text-[10px] px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">⭐</span>
                        </div>
                    </div>

                    <div class="p-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm text-gray-800 truncate">{{ campaign.name }}</h3>
                                <p class="text-[10px] text-gray-500">Loại: {{ campaign.type || 'Seasonal' }}</p>
                            </div>
                            <div class="flex gap-0.5 ml-1 flex-shrink-0">
                                <button @click="openCampaignModal(campaign)" class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteCampaign(campaign.id)" class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ campaign.description || 'Không có mô tả' }}</p>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <div class="bg-orange-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-gray-500">Giảm giá</p>
                                <p class="text-sm font-bold text-orange-600">{{ campaign.discount || '0%' }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-gray-500">SL tối thiểu</p>
                                <p class="text-sm font-bold text-blue-600">{{ campaign.quantity || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center gap-1 text-[10px] text-gray-500">
                            <span class="material-symbols-outlined text-xs">calendar_today</span>
                            <span>{{ formatDate(campaign.startDate) }} - {{ formatDate(campaign.endDate) }}</span>
                        </div>

                        <div class="mt-0.5 flex items-center gap-1 text-[10px] text-gray-500">
                            <span class="material-symbols-outlined text-xs">inventory_2</span>
                            <span>{{ campaign.products?.length || 0 }} sản phẩm</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleCampaignStatus(campaign)" 
                                class="text-[10px] px-3 py-1 rounded-full transition-colors font-medium"
                                :class="campaign.status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'"
                            >
                                {{ campaign.status === 'active' ? '✅ Đang diễn ra' : '🔄 Kích hoạt' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredCampaigns.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">campaign</span>
                    <p class="text-lg font-medium">Không có chiến dịch nào</p>
                    <button @click="openCampaignModal()" class="mt-3 text-blue-600 hover:underline font-medium">Thêm chiến dịch</button>
                </div>
            </div>

            <!-- ==================== VOUCHERS LIST ==================== -->
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="voucher in filteredVouchers" :key="voucher.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-28 bg-gradient-to-r from-orange-50 to-orange-100">
                        <div class="w-full h-full flex items-center justify-center text-orange-300">
                            <span class="material-symbols-outlined text-4xl">local_offer</span>
                        </div>
                        <div class="absolute top-2 right-2">
                            <span class="text-[10px] px-2 py-0.5 rounded-full" :class="voucher.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ voucher.status === 'active' ? '🟢 Hoạt động' : '🔴 Đã tắt' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-base text-gray-800 truncate">{{ voucher.code }}</h3>
                                <p class="text-[10px] text-gray-500">Loại: {{ getDiscountTypeLabel(voucher.discount_type) }}</p>
                            </div>
                            <div class="flex gap-0.5 ml-1 flex-shrink-0">
                                <button @click="openVoucherModal(voucher)" class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteVoucher(voucher.id)" class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ voucher.description || 'Không có mô tả' }}</p>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <div class="bg-orange-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-gray-500">Giá trị</p>
                                <p class="text-sm font-bold text-orange-600">
                                    {{ voucher.discount_type === 'percent' ? voucher.discount_value + '%' : formatPrice(voucher.discount_value) }}
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-gray-500">Đã dùng</p>
                                <p class="text-sm font-bold text-blue-600">{{ voucher.used }}/{{ voucher.limit }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center gap-1 text-[10px] text-gray-500">
                            <span class="material-symbols-outlined text-xs">event</span>
                            <span>HSD: {{ formatDate(voucher.expiry) }}</span>
                        </div>

                        <div class="mt-1 flex items-center gap-1 text-[10px] text-gray-500">
                            <span class="material-symbols-outlined text-xs">target</span>
                            <span>{{ getTargetTypeLabel(voucher.target_type) }}</span>
                            <span v-if="voucher.min_order > 0" class="ml-1">- Đơn tối thiểu: {{ formatPrice(voucher.min_order) }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleVoucher(voucher)" 
                                :class="voucher.status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                class="text-[10px] px-3 py-1 rounded-full transition-colors"
                            >
                                {{ voucher.status === 'active' ? '✅ Kích hoạt' : '🔄 Kích hoạt' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredVouchers.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">local_offer</span>
                    <p class="text-lg font-medium">Không có mã giảm giá nào</p>
                    <button @click="openVoucherModal()" class="mt-3 text-orange-600 hover:underline font-medium">Thêm mã giảm giá</button>
                </div>
            </div>

            <!-- ==================== PRE-ORDERS LIST ==================== -->
            <div v-if="activeTab === 'preorder'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="preorder in filteredPreorders" :key="preorder.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-28 bg-gradient-to-r from-purple-50 to-purple-100">
                        <div class="w-full h-full flex items-center justify-center text-purple-300">
                            <span class="material-symbols-outlined text-4xl">schedule</span>
                        </div>
                        <div class="absolute top-2 right-2">
                            <span class="text-[10px] px-2 py-0.5 rounded-full" :class="preorder.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ preorder.status === 'active' ? '🟢 Hoạt động' : '🔴 Đã tắt' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm text-gray-800 truncate">⏳ {{ preorder.code }}</h3>
                                <p class="text-[10px] text-gray-500 truncate">Sản phẩm: {{ getProductName(preorder.product_id) }}</p>
                            </div>
                            <div class="flex gap-0.5 ml-1 flex-shrink-0">
                                <button @click="openPreorderModal(preorder)" class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deletePreorder(preorder.id)" class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] font-medium">Đã đặt: {{ preorder.current_buyers || 0 }} lượt</span>
                                <span class="text-xs font-bold text-blue-600">
                                    {{ preorder.tiers?.find(t => (preorder.current_buyers || 0) >= t.from && (preorder.current_buyers || 0) <= t.to)?.discount || 0 }}%
                                </span>
                            </div>

                            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all"
                                    :style="{ width: Math.min(((preorder.current_buyers || 0) / (preorder.tiers?.[preorder.tiers.length - 1]?.to || 100)) * 100, 100) + '%' }"
                                ></div>
                            </div>

                            <div class="mt-1.5 grid grid-cols-3 gap-1 text-[10px]">
                                <div v-for="tier in preorder.tiers" :key="tier.from" 
                                    class="text-center p-1 bg-white rounded border"
                                    :class="(preorder.current_buyers || 0) >= tier.from && (preorder.current_buyers || 0) <= tier.to ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                                >
                                    <div class="font-bold">{{ tier.discount }}%</div>
                                    <div class="text-gray-500 text-[8px]">#{{ tier.from }}-{{ tier.to }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 flex justify-between items-center text-[10px] text-gray-500">
                            <span>📅 {{ formatDate(preorder.start_date) }} - {{ formatDate(preorder.end_date) }}</span>
                            <span v-if="preorder.min_order > 0">💰 {{ formatPrice(preorder.min_order) }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleVoucher(preorder)"
                                :class="preorder.status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                class="text-[10px] px-3 py-1 rounded-full transition-colors"
                            >
                                {{ preorder.status === 'active' ? '✅ Kích hoạt' : '🔄 Kích hoạt' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredPreorders.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">schedule</span>
                    <p class="text-lg font-medium">Không có chương trình pre-order nào</p>
                    <button @click="openPreorderModal()" class="mt-3 text-purple-600 hover:underline font-medium">Thêm pre-order</button>
                </div>
            </div>
        </div>

        <!-- ==================== CAMPAIGN MODAL ==================== -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ editingCampaign ? 'Sửa chiến dịch' : 'Thêm chiến dịch mới' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Tên chiến dịch *</label>
                        <input 
                            v-model="campaignForm.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="VD: Sale Tết 2025"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Loại chiến dịch</label>
                        <select 
                            v-model="campaignForm.type" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="seasonal">Theo mùa</option>
                            <option value="flash_sale">Flash Sale</option>
                            <option value="anniversary">Kỷ niệm</option>
                            <option value="holiday">Ngày lễ</option>
                            <option value="product_launch">Ra mắt sản phẩm</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mô tả</label>
                        <textarea 
                            v-model="campaignForm.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-none"
                            placeholder="Mô tả chiến dịch..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày bắt đầu</label>
                            <input 
                                v-model="campaignForm.startDate" 
                                type="date" 
                                :min="today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                            <p class="text-xs text-gray-500 mt-1">Không thể chọn ngày trong quá khứ</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày kết thúc</label>
                            <input 
                                v-model="campaignForm.endDate" 
                                type="date" 
                                :min="minEndDate"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                            <p class="text-xs text-gray-500 mt-1">Có thể chọn cùng ngày hoặc sau ngày bắt đầu</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Trạng thái (Tự động)</label>
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getStatusClass(campaignForm.status)">
                                {{ getStatusLabel(campaignForm.status) }}
                            </span>
                            <span class="text-xs text-gray-500">Dựa trên ngày bắt đầu và kết thúc</span>
                        </div>
                        <input type="hidden" v-model="campaignForm.status">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Số lượng tối thiểu *</label>
                            <input 
                                v-model.number="campaignForm.quantity" 
                                type="number" 
                                min="1"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="1"
                            >
                            <p class="text-xs text-gray-500 mt-1">Số sản phẩm tối thiểu để áp dụng (phải lớn hơn 0)</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Giảm giá (%)</label>
                            <input 
                                v-model.number="campaignForm.discountPercent" 
                                type="number" 
                                min="0" 
                                max="100"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="0"
                            >
                            <p class="text-xs text-gray-500 mt-1">Phần trăm giảm giá (0-100%). Có thể để 0%</p>
                        </div>
                    </div>

                    <!-- Sản phẩm áp dụng -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm áp dụng</label>
                        
                        <div v-if="productVariants.filter(v => !v.product?.is_preorder).length > 0" class="mb-2">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">📦 Thường</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => !v.product?.is_preorder).length }})</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-1.5 max-h-28 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => !v.product?.is_preorder)" :key="variant.id" 
                                     class="flex items-center gap-1.5 py-0.5 hover:bg-gray-50 px-1.5 rounded text-xs">
                                    <input 
                                        type="checkbox" 
                                        :id="'normal-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500"
                                    >
                                    <label :for="'normal-' + variant.id" class="text-xs cursor-pointer flex-1 flex items-center gap-1 truncate">
                                        <span class="font-medium truncate max-w-[100px]">{{ variant.product?.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-[10px]">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-blue-600 text-[10px] font-medium">{{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.filter(v => v.product?.is_preorder).length > 0" class="mb-2">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">⏳ Pre-order</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => v.product?.is_preorder).length }})</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-1.5 max-h-28 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => v.product?.is_preorder)" :key="variant.id" 
                                     class="flex items-center gap-1.5 py-0.5 hover:bg-gray-50 px-1.5 rounded text-xs">
                                    <input 
                                        type="checkbox" 
                                        :id="'preorder-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-3 h-3 text-purple-600 rounded focus:ring-purple-500"
                                    >
                                    <label :for="'preorder-' + variant.id" class="text-xs cursor-pointer flex-1 flex items-center gap-1 truncate">
                                        <span class="font-medium truncate max-w-[100px]">{{ variant.product?.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-[10px]">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-purple-600 text-[10px] font-medium">{{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.length === 0" class="border border-gray-300 rounded-lg p-3 text-center text-gray-400 text-sm">
                            <span class="material-symbols-outlined text-2xl block mb-1">inventory_2</span>
                            <p class="text-xs">Không có sản phẩm nào</p>
                            <p class="text-[10px] mt-0.5">Vui lòng thêm sản phẩm và biến thể</p>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-1">Tổng: <span class="font-semibold">{{ campaignForm.products.length }}</span> sản phẩm</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Độ ưu tiên</label>
                            <input 
                                v-model.number="campaignForm.priority" 
                                type="number" 
                                min="0"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="0"
                            >
                            <p class="text-xs text-gray-500 mt-1">Số càng nhỏ càng ưu tiên</p>
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    v-model="campaignForm.featured" 
                                    class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                >
                                <span class="text-sm text-gray-700">⭐ Nổi bật</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button 
                        @click="closeModal" 
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                        :disabled="isSubmitting"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveCampaign" 
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu chiến dịch' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== VOUCHER MODAL ==================== -->
        <div v-if="showVoucherModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeVoucherModal">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ editingVoucher ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá mới' }}
                    </h3>
                    <button @click="closeVoucherModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mã code *</label>
                        <input 
                            v-model="voucherForm.code" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 uppercase focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="NHAPMA"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Áp dụng cho</label>
                        <select 
                            v-model="voucherForm.target_type" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
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
                            v-model="voucherForm.discount_type" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="fixed">Giảm trực tiếp (₫)</option>
                            <option value="percent">Giảm theo %</option>
                            <option value="freeship">Miễn phí ship</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá trị giảm</label>
                        <input 
                            v-model.number="voucherForm.discount_value" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="50000"
                        >
                        <p class="text-xs text-gray-500 mt-1">Có thể để 0 nếu không giảm</p>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Điều kiện (đơn tối thiểu)</label>
                        <input 
                            v-model.number="voucherForm.min_order" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="500000"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Số lượng mã</label>
                        <input 
                            v-model.number="voucherForm.limit" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="100"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày kết thúc</label>
                        <input 
                            v-model="voucherForm.expiry" 
                            type="date" 
                            :min="today"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Liên kết chiến dịch</label>
                        <select 
                            v-model="voucherForm.campaign_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option :value="null">-- Không liên kết --</option>
                            <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">{{ camp.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mô tả</label>
                        <textarea 
                            v-model="voucherForm.description" 
                            rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 resize-none"
                            placeholder="Mô tả mã giảm giá..."
                        ></textarea>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="voucherForm.active" class="w-5 h-5 text-orange-600 rounded focus:ring-orange-500">
                        <span class="text-sm text-gray-700">Kích hoạt ngay</span>
                    </label>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button 
                        @click="closeVoucherModal" 
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                        :disabled="isSubmitting"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveVoucher" 
                        class="px-5 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center gap-2"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu mã' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== PRE-ORDER MODAL ==================== -->
        <div v-if="showPreorderModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closePreorderModal">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ editingPreorder ? 'Sửa pre-order' : 'Thêm pre-order mới' }}
                    </h3>
                    <button @click="closePreorderModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Tên chương trình *</label>
                        <input 
                            v-model="preorderForm.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            placeholder="Pre-order Sale 2025"
                        >
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm áp dụng *</label>
                        <div v-if="preorderProducts && preorderProducts.length > 0">
                            <select 
                                v-model="preorderForm.product_id" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            >
                                <option :value="null">-- Chọn sản phẩm pre-order --</option>
                                <option 
                                    v-for="product in preorderProducts" 
                                    :key="product.id" 
                                    :value="product.id"
                                >
                                    ⏳ {{ product.name }} 
                                    <span v-if="product.variants && product.variants.length > 0" class="text-gray-400 text-xs">
                                        ({{ product.variants.length }} biến thể)
                                    </span>
                                </option>
                            </select>
                        </div>
                        <div v-else class="border border-gray-300 rounded-lg p-6 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl block mb-2">inventory_2</span>
                            <p>Không có sản phẩm pre-order nào</p>
                            <p class="text-xs mt-1">Vui lòng thêm sản phẩm pre-order (is_preorder = true) trước khi tạo chương trình</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Chỉ hiển thị sản phẩm pre-order (is_preorder = true)</p>
                        
                        <div v-if="preorderForm.product_id" class="mt-2 p-2 bg-purple-50 rounded-lg border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">
                                ✅ Đã chọn: {{ getProductName(preorderForm.product_id) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ID: {{ preorderForm.product_id }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Đơn hàng tối thiểu</label>
                        <input 
                            v-model.number="preorderForm.min_order" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            placeholder="0"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày bắt đầu</label>
                            <input 
                                v-model="preorderForm.start_date" 
                                type="date" 
                                :min="today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            >
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Ngày kết thúc</label>
                            <input 
                                v-model="preorderForm.end_date" 
                                type="date" 
                                :min="preorderForm.start_date || today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            >
                            <p class="text-xs text-gray-500 mt-1">Có thể chọn cùng ngày hoặc sau ngày bắt đầu</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Liên kết chiến dịch</label>
                        <select 
                            v-model="preorderForm.campaign_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                        >
                            <option :value="null">-- Không liên kết --</option>
                            <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">{{ camp.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-medium text-gray-700">Các mức giảm giá theo lượt</label>
                            <button @click="addTier" type="button" class="text-sm text-purple-600 hover:text-purple-700 font-medium">+ Thêm mức</button>
                        </div>
                        
                        <div class="space-y-2">
                            <div v-for="(tier, index) in preorderForm.tiers" :key="index" class="flex items-center gap-2 bg-gray-50 p-3 rounded-lg">
                                <div class="flex-1 grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="text-xs text-gray-500">Từ</label>
                                        <input 
                                            v-model="tier.from" 
                                            type="number" 
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                            min="1"
                                        >
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Đến</label>
                                        <input 
                                            v-model="tier.to" 
                                            type="number" 
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                            min="1"
                                        >
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Giảm %</label>
                                        <input 
                                            v-model="tier.discount" 
                                            type="number" 
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                            min="0"
                                            max="100"
                                        >
                                    </div>
                                </div>
                                <button 
                                    @click="removeTier(index)" 
                                    type="button"
                                    class="text-red-500 hover:text-red-700 text-sm p-1"
                                    :disabled="preorderForm.tiers.length <= 1"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="preorderForm.active" class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Kích hoạt ngay</span>
                    </label>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button 
                        @click="closePreorderModal" 
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                        :disabled="isSubmitting"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="savePreorder" 
                        class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu pre-order' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 0.8s linear infinite;
}
</style>