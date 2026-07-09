# Fix lỗi promotions

**Created:** 07/07/2026 07:11  
**Updated:** 07/07/2026 07:55  
**Exported:** 07/09/2026 21:18  
**Link:** [https://chat.deepseek.com/a/chat/s/8851facc-f959-4b05-b09a-7fded434e074](https://chat.deepseek.com/a/chat/s/8851facc-f959-4b05-b09a-7fded434e074)  

## User:
07/07/2026 07:16

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
    promotions: {
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
const promotions = ref(props.promotions || []);
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
    banner: '',
    banner_id: null,
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
    banner: '',
    banner_id: null,
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

const getBannerByCampaign = (campaignId) => {
    if (!campaignId) return null;
    return banners.value.find(b => b.campaign_id === campaignId);
};

const getProductName = (productId) => {
    if (!productId) return 'Chưa chọn';
    const product = products.value.find(p => p.id === productId);
    return product ? product.name : 'Sản phẩm không tồn tại';
};

// ==================== FUNCTION TÍNH TRẠNG THÁI ====================

const calculateCampaignStatus = (startDate, endDate) => {
    if (!startDate) return 'scheduled';
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const start = new Date(startDate);
    start.setHours(0, 0, 0, 0);
    
    // Nếu ngày bắt đầu > ngày hiện tại => Sắp diễn ra
    if (start.getTime() > today.getTime()) {
        return 'scheduled';
    }
    
    // Nếu có ngày kết thúc
    if (endDate) {
        const end = new Date(endDate);
        end.setHours(0, 0, 0, 0);
        
        // Nếu ngày kết thúc < ngày hiện tại => Đã kết thúc
        if (end.getTime() < today.getTime()) {
            return 'ended';
        }
    }
    
    // Ngày bắt đầu <= ngày hiện tại và (chưa có ngày kết thúc hoặc ngày kết thúc >= ngày hiện tại) => Đang diễn ra
    return 'active';
};

// Cập nhật trạng thái cho tất cả campaigns
const updateAllCampaignsStatus = () => {
    campaigns.value = campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
};

// ==================== BANNER FUNCTIONS ====================

const selectedBanner = computed(() => {
    if (!campaignForm.value.banner_id) return null;
    return banners.value.find(b => b.id === campaignForm.value.banner_id);
});

const selectedPreorderBanner = computed(() => {
    if (!preorderForm.value.banner_id) return null;
    return banners.value.find(b => b.id === preorderForm.value.banner_id);
});

const onBannerSelect = () => {
    const bannerId = campaignForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            campaignForm.value.banner = selected.image;
        }
    } else {
        campaignForm.value.banner = '';
    }
};

const onPreorderBannerSelect = () => {
    const bannerId = preorderForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            preorderForm.value.banner = selected.image;
        }
    } else {
        preorderForm.value.banner = '';
    }
};

const onBannerUrlInput = () => {
    if (campaignForm.value.banner) {
        campaignForm.value.banner_id = null;
    }
};

const onPreorderBannerUrlInput = () => {
    if (preorderForm.value.banner) {
        preorderForm.value.banner_id = null;
    }
};

// ==================== DATE FUNCTIONS ====================

// Lấy ngày hiện tại để giới hạn input
const today = new Date().toISOString().split('T')[0];

// Cập nhật trạng thái dựa trên ngày trong form
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
            banner: campaign.banner || '',
            banner_id: campaign.banner_id || null,
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
            banner: '',
            banner_id: null,
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
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        // Tính toán lại status trước khi lưu
        const status = calculateCampaignStatus(campaignForm.value.startDate, campaignForm.value.endDate);
        
        const data = {
            ...campaignForm.value,
            status: status,
            discountPercent: parseFloat(campaignForm.value.discountPercent) || 0,
            quantity: parseInt(campaignForm.value.quantity) || 1,
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
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
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
            active: voucher.active !== undefined ? voucher.active : true,
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
    
    if (voucherForm.value.discount_value <= 0) {
        errorMessage.value = 'Giá trị giảm giá phải lớn hơn 0';
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
    const voucher = promotions.value.find(p => p.id === id);
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
            name: preorder.code || '',
            product_id: preorder.product_id || null,
            banner: preorder.banner || '',
            banner_id: preorder.banner_id || null,
            tiers: preorder.tiers || [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: preorder.start_date || '',
            end_date: preorder.end_date || '',
            active: preorder.active !== undefined ? preorder.active : true,
            min_order: preorder.min_order || 0,
            campaign_id: preorder.campaign_id || null
        };
    } else {
        preorderForm.value = {
            id: null,
            name: '',
            product_id: null,
            banner: '',
            banner_id: null,
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
    const preorder = promotions.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chương trình "${preorder?.code}"?`)) {
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

// Cập nhật trạng thái campaigns mỗi khi campaigns thay đổi
watch(campaigns, () => {
    updateAllCampaignsStatus();
}, { deep: true });

// Lọc campaigns theo status
const filteredCampaigns = computed(() => {
    let filtered = campaigns.value || [];
    
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

const filteredVouchers = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'voucher');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const filteredPreorders = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'preorder_tier');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

onMounted(() => {
    showFlashMessage();
    // Cập nhật trạng thái cho tất cả campaigns khi load trang
    updateAllCampaignsStatus();
});
</script>

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
    promotions: {
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
const promotions = ref(props.promotions || []);
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
    banner: '',
    banner_id: null,
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
    banner: '',
    banner_id: null,
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

const getBannerByCampaign = (campaignId) => {
    if (!campaignId) return null;
    return banners.value.find(b => b.campaign_id === campaignId);
};

const getProductName = (productId) => {
    if (!productId) return 'Chưa chọn';
    const product = products.value.find(p => p.id === productId);
    return product ? product.name : 'Sản phẩm không tồn tại';
};

// ==================== FUNCTION TÍNH TRẠNG THÁI ====================

const calculateCampaignStatus = (startDate, endDate) => {
    if (!startDate) return 'scheduled';
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const start = new Date(startDate);
    start.setHours(0, 0, 0, 0);
    
    // Nếu ngày bắt đầu > ngày hiện tại => Sắp diễn ra
    if (start.getTime() > today.getTime()) {
        return 'scheduled';
    }
    
    // Nếu có ngày kết thúc
    if (endDate) {
        const end = new Date(endDate);
        end.setHours(0, 0, 0, 0);
        
        // Nếu ngày kết thúc < ngày hiện tại => Đã kết thúc
        if (end.getTime() < today.getTime()) {
            return 'ended';
        }
    }
    
    // Ngày bắt đầu <= ngày hiện tại và (chưa có ngày kết thúc hoặc ngày kết thúc >= ngày hiện tại) => Đang diễn ra
    return 'active';
};

// Cập nhật trạng thái cho tất cả campaigns
const updateAllCampaignsStatus = () => {
    campaigns.value = campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
};

// ==================== BANNER FUNCTIONS ====================

const selectedBanner = computed(() => {
    if (!campaignForm.value.banner_id) return null;
    return banners.value.find(b => b.id === campaignForm.value.banner_id);
});

const selectedPreorderBanner = computed(() => {
    if (!preorderForm.value.banner_id) return null;
    return banners.value.find(b => b.id === preorderForm.value.banner_id);
});

const onBannerSelect = () => {
    const bannerId = campaignForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            campaignForm.value.banner = selected.image;
        }
    } else {
        campaignForm.value.banner = '';
    }
};

const onPreorderBannerSelect = () => {
    const bannerId = preorderForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            preorderForm.value.banner = selected.image;
        }
    } else {
        preorderForm.value.banner = '';
    }
};

const onBannerUrlInput = () => {
    if (campaignForm.value.banner) {
        campaignForm.value.banner_id = null;
    }
};

const onPreorderBannerUrlInput = () => {
    if (preorderForm.value.banner) {
        preorderForm.value.banner_id = null;
    }
};

// ==================== DATE FUNCTIONS ====================

// Lấy ngày hiện tại để giới hạn input
const today = new Date().toISOString().split('T')[0];

// Cập nhật trạng thái dựa trên ngày trong form
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
            banner: campaign.banner || '',
            banner_id: campaign.banner_id || null,
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
            banner: '',
            banner_id: null,
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
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        // Tính toán lại status trước khi lưu
        const status = calculateCampaignStatus(campaignForm.value.startDate, campaignForm.value.endDate);
        
        const data = {
            ...campaignForm.value,
            status: status,
            discountPercent: parseFloat(campaignForm.value.discountPercent) || 0,
            quantity: parseInt(campaignForm.value.quantity) || 1,
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
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
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
            active: voucher.active !== undefined ? voucher.active : true,
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
    
    if (voucherForm.value.discount_value <= 0) {
        errorMessage.value = 'Giá trị giảm giá phải lớn hơn 0';
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
    const voucher = promotions.value.find(p => p.id === id);
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
            name: preorder.code || '',
            product_id: preorder.product_id || null,
            banner: preorder.banner || '',
            banner_id: preorder.banner_id || null,
            tiers: preorder.tiers || [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: preorder.start_date || '',
            end_date: preorder.end_date || '',
            active: preorder.active !== undefined ? preorder.active : true,
            min_order: preorder.min_order || 0,
            campaign_id: preorder.campaign_id || null
        };
    } else {
        preorderForm.value = {
            id: null,
            name: '',
            product_id: null,
            banner: '',
            banner_id: null,
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
    const preorder = promotions.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chương trình "${preorder?.code}"?`)) {
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

// Cập nhật trạng thái campaigns mỗi khi campaigns thay đổi
watch(campaigns, () => {
    updateAllCampaignsStatus();
}, { deep: true });

// Lọc campaigns theo status
const filteredCampaigns = computed(() => {
    let filtered = campaigns.value || [];
    
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

const filteredVouchers = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'voucher');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const filteredPreorders = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'preorder_tier');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

onMounted(() => {
    showFlashMessage();
    // Cập nhật trạng thái cho tất cả campaigns khi load trang
    updateAllCampaignsStatus();
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
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'voucher').length }}</span>
                </button>
                <button 
                    @click="activeTab = 'preorder'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'preorder' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    ⏳ Pre-order
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'preorder_tier').length }}</span>
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
                        {{ campaigns.filter(c => status.value === 'all' || c.status === status.value).length }}
                    </span>
                </button>
            </div>

            <!-- ==================== CAMPAIGNS LIST ==================== -->
            <div v-if="activeTab === 'campaigns'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-48 bg-gradient-to-r from-gray-100 to-gray-200">
                        <img 
                            v-if="campaign.banner" 
                            :src="campaign.banner" 
                            :alt="campaign.name" 
                            class="w-full h-full object-cover"
                            @error="campaign.banner = null"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-5xl">campaign</span>
                        </div>
                        
                        <div class="absolute top-3 right-3 flex gap-1">
                            <span class="text-xs px-3 py-1 rounded-full font-medium" :class="getStatusClass(campaign.status)">
                                {{ getStatusLabel(campaign.status) }}
                            </span>
                            <span v-if="campaign.featured" class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">⭐ Nổi bật</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800">{{ campaign.name }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Loại: {{ campaign.type || 'Seasonal' }}</p>
                            </div>
                            <div class="flex gap-1 ml-2">
                                <button @click="openCampaignModal(campaign)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteCampaign(campaign.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ campaign.description || 'Không có mô tả' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="bg-orange-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Giảm giá</p>
                                <p class="text-lg font-bold text-orange-600">{{ campaign.discount || '0%' }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Số lượng tối thiểu</p>
                                <p class="text-lg font-bold text-blue-600">{{ campaign.quantity || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <span>{{ formatDate(campaign.startDate) }} - {{ formatDate(campaign.endDate) }}</span>
                        </div>

                        <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                            <span>{{ campaign.products?.length || 0 }} sản phẩm áp dụng</span>
                        </div>

                        <div v-if="getBannerByCampaign(campaign.id)" class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">image</span>
                            <span>Có banner liên kết</span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleCampaignStatus(campaign)" 
                                class="text-xs px-4 py-2 rounded-full transition-colors font-medium"
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
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="voucher in filteredVouchers" :key="voucher.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ voucher.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-xl text-gray-800 mt-2">{{ voucher.code }}</h3>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openVoucherModal(voucher)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deleteVoucher(voucher.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-3">{{ voucher.description }}</p>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">🎯</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ getTargetTypeLabel(voucher.target_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">💰</span>
                            <span class="text-orange-600 font-semibold">{{ getDiscountTypeLabel(voucher.discount_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📅</span>
                            <span class="text-sm">HSD: {{ formatDate(voucher.expiry) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📊</span>
                            <span class="text-sm">{{ voucher.used }}/{{ voucher.limit }}</span>
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-orange-500 rounded-full transition-all"
                                :style="{ width: Math.min((voucher.used / voucher.limit) * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(voucher)"
                            :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ voucher.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
                    </div>
                </div>

                <div v-if="filteredVouchers.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">local_offer</span>
                    <p class="text-lg font-medium">Không có mã giảm giá nào</p>
                    <button @click="openVoucherModal()" class="mt-3 text-orange-600 hover:underline font-medium">Thêm mã giảm giá</button>
                </div>
            </div>

            <!-- ==================== PRE-ORDERS LIST ==================== -->
            <div v-if="activeTab === 'preorder'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div v-for="preorder in filteredPreorders" :key="preorder.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ preorder.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-lg text-gray-800 mt-2">⏳ {{ preorder.code }}</h3>
                            <p class="text-xs text-gray-500">Sản phẩm: {{ getProductName(preorder.product_id) }}</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openPreorderModal(preorder)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deletePreorder(preorder.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <div class="mt-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium">Đã đặt trước: {{ preorder.current_buyers || 0 }} lượt</span>
                            <span class="text-sm font-bold text-blue-600">
                                Giảm hiện tại: 
                                {{ preorder.tiers?.find(t => (preorder.current_buyers || 0) >= t.from && (preorder.current_buyers || 0) <= t.to)?.discount || 0 }}%
                            </span>
                        </div>

                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all"
                                :style="{ width: Math.min(((preorder.current_buyers || 0) / (preorder.tiers?.[preorder.tiers.length - 1]?.to || 100)) * 100, 100) + '%' }"
                            ></div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            <div v-for="tier in preorder.tiers" :key="tier.from" 
                                class="text-center p-2 bg-white rounded border"
                                :class="(preorder.current_buyers || 0) >= tier.from && (preorder.current_buyers || 0) <= tier.to ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            >
                                <div class="font-bold">{{ tier.discount }}%</div>
                                <div class="text-gray-500">#{{ tier.from }}-{{ tier.to }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                        <span>📅 {{ formatDate(preorder.start_date) }} - {{ formatDate(preorder.end_date) }}</span>
                        <span v-if="preorder.min_order > 0">💰 Đơn tối thiểu: {{ formatPrice(preorder.min_order) }}</span>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(preorder)"
                            :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ preorder.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
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
                                :min="campaignForm.startDate || today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
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

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="campaignForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                @change="onBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedBanner && selectedBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedBanner.campaign_name || 'Banner #' + selectedBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="campaignForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onBannerUrlInput"
                            >
                        </div>

                        <div v-if="campaignForm.banner && !selectedBanner" class="mt-2 relative">
                            <img 
                                :src="campaignForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="campaignForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="campaignForm.banner = ''; campaignForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
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
                            <p class="text-xs text-gray-500 mt-1">Phần trăm giảm giá (0-100%)</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm áp dụng</label>
                        
                        <div v-if="productVariants.filter(v => !v.product?.is_preorder).length > 0" class="mb-3">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">📦 Sản phẩm thường</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => !v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => !v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'normal-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                    >
                                    <label :for="'normal-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.filter(v => v.product?.is_preorder).length > 0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded">⏳ Sản phẩm Pre-order</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'preorder-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                                    >
                                    <label :for="'preorder-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.length === 0" class="border border-gray-300 rounded-lg p-6 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl block mb-2">inventory_2</span>
                            <p>Không có sản phẩm nào</p>
                            <p class="text-xs mt-1">Vui lòng thêm sản phẩm và biến thể trước khi tạo chiến dịch</p>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-2">Tổng: <span class="font-semibold">{{ campaignForm.products.length }}</span> sản phẩm được chọn</div>
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá trị giảm *</label>
                        <input 
                            v-model.number="voucherForm.discount_value" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="50000"
                        >
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="preorderForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                                @change="onPreorderBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedPreorderBanner && selectedPreorderBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedPreorderBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedPreorderBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedPreorderBanner.campaign_name || 'Banner #' + selectedPreorderBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedPreorderBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedPreorderBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="preorderForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onPreorderBannerUrlInput"
                            >
                        </div>

                        <div v-if="preorderForm.banner && !selectedPreorderBanner" class="mt-2 relative">
                            <img 
                                :src="preorderForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="preorderForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="preorderForm.banner = ''; preorderForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns
            $campaigns = Campaign::with(['banners', 'configs', 'productVariants', 'productVariants.product', 'productVariants.color'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'banner' => $campaign->banner ?? '',
                        'banner_id' => $campaign->banner_id ?? null,
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'productVariants' => $campaign->productVariants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->product->name ?? 'Sản phẩm',
                                'color' => $variant->color->name ?? 'Không màu',
                                'price' => $variant->price ?? 0,
                            ];
                        }),
                        'banners' => $campaign->banners,
                    ];
                });

            // Lấy promotions (vouchers và preorder)
            $promotions = Promotion::with(['campaign', 'product'])->latest()->get();

            // Lấy tất cả banners để chọn
            $banners = Banner::with('campaign')->orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'campaign_id' => $banner->campaign_id,
                    'campaign_name' => $banner->campaign ? $banner->campaign->name : 'Chưa phân loại',
                    'status' => $banner->status,
                    'order' => $banner->order,
                ];
            });

            // Lấy tất cả products (cho voucher)
            $products = Product::with(['variants.color', 'category', 'brand'])->latest()->get();

            // Lấy product variants (cho campaign)
            $productVariants = ProductVariant::with(['product', 'color'])->latest()->get();

            // Lấy chỉ sản phẩm pre-order (cho pre-order) - Dùng is_preorder
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get();

            Log::info('Banners count: ' . $banners->count());
            Log::info('Preorder Products count: ' . $preorderProducts->count());

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CAMPAIGN METHODS ====================
    
    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner' => 'nullable|string|max:2048',
                'banner_id' => 'nullable|exists:banners,id',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            // Nếu chọn banner từ dropdown, lấy URL của banner đó
            $bannerUrl = $validated['banner'] ?? '';
            if (!empty($validated['banner_id'])) {
                $banner = Banner::find($validated['banner_id']);
                if ($banner) {
                    $bannerUrl = $banner->image;
                }
            }

            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'banner' => $bannerUrl,
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            if (!empty($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner' => 'nullable|string|max:2048',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'banner' => $validated['banner'] ?? $campaign->banner,
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteCampaign($id)
    {
        try {
            DB::beginTransaction();
            
            $campaign = Campaign::findOrFail($id);
            $campaign->configs()->delete();
            $campaign->productVariants()->detach();
            $campaign->delete();

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaignStatus(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $request->validate(['status' => 'required|in:scheduled,active,ended']);
            $campaign->update(['status' => $request->status]);
            
            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== VOUCHER METHODS ====================

    public function storePromotion(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code',
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion = Promotion::create([
                'code' => strtoupper($validated['code']),
                'type' => 'voucher',
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'used' => 0,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePromotion(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code,' . $id,
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion->update([
                'code' => strtoupper($validated['code']),
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update(['active' => !$promotion->active]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== PRE-ORDER METHODS ====================

    public function storePreorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion = Promotion::create([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'type' => 'preorder_tier',
                'target_type' => 'preorder',
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
                'current_buyers' => 0,
                'description' => "Giảm giá theo số lượt đặt trước",
                'discount_value' => 0,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePreorder(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion->update([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePreorder($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type', // voucher, preorder_tier
        'target_type', // retail, wholesale, preorder, all
        'discount_type', // fixed, percent, freeship
        'discount_value',
        'min_order',
        'limit',
        'used',
        'expiry',
        'active',
        'description',
        'product_id',
        'tiers',
        'current_buyers',
        'start_date',
        'end_date',
        'campaign_id'
    ];

    protected $casts = [
        'tiers' => 'array',
        'active' => 'boolean',
        'discount_value' => 'decimal:0',
        'min_order' => 'decimal:0',
        'expiry' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'image', 'link', 'status', 'order'];

    protected $casts = [
        'status' => 'integer',
        'order' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::with('campaign')->orderBy('order', 'asc')->get(),
            'campaigns' => Campaign::all()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::with('campaign')->orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'campaign_id' => 'required|exists:campaigns,id',
                'image' => 'nullable|url',
                'image_file' => 'nullable|image|max:2048',
                'link' => 'nullable|string|max:255',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ]);

            $data = $request->only(['campaign_id', 'link', 'status']);
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                $path = $request->file('image_file')->store('banners', 'public');
                $data['image'] = '/storage/' . $path;
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
            $newOrder = $request->input('order');
            $totalBanners = Banner::count();

            if (is_null($newOrder) || $newOrder > $totalBanners) {
                $newOrder = $totalBanners;
            }
            if ($newOrder < 0) {
                $newOrder = 0;
            }

            Banner::where('order', '>=', $newOrder)->increment('order');

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            // Cập nhật banner_id cho campaign
            if ($banner) {
                Campaign::where('id', $banner->campaign_id)->update(['banner_id' => $banner->id]);
            }

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            $request->validate([
                'campaign_id' => 'required|exists:campaigns,id',
                'image' => 'nullable|url',
                'image_file' => 'nullable|image|max:2048',
                'link' => 'nullable|string|max:255',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ]);

            $data = $request->only(['campaign_id', 'link', 'status']);
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                }
                $path = $request->file('image_file')->store('banners', 'public');
                $data['image'] = '/storage/' . $path;
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                $data['image'] = $banner->image;
            }

            // Xử lý thay đổi order
            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count() - 1;

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder > $totalBanners) $newOrder = $totalBanners;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            // Cập nhật banner_id cho campaign
            Campaign::where('id', $banner->campaign_id)->update(['banner_id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            // Xóa ảnh
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count() - 1;
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

Fix lỗi cho toi

﻿
?plugins=forms,container-queries:64 cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI: https://tailwindcss.com/docs/installation

vue.runtime.esm-bund….js?v=fa1f6f1c:1751 [Vue warn]: Unhandled error during execution of app errorHandler
promotions:1 Uncaught (in promise) Maximum recursive updates exceeded in component <Promotions>. This means you have a reactive effect that is mutating its own dependencies and thus recursively triggering itself. Possible sources include component template, render function, updated hook or watcher source function.
Banners.vue:268 
 PUT http://127.0.0.1:8000/admin/banners/1 500 (Internal Server Error)
Banners.vue:331 Lỗi lưu banner: AxiosError: Request failed with status code 500


## Assistant:
07/07/2026 07:16

Tôi thấy bạn gặp lỗi `Maximum recursive updates exceeded` trong component Promotions và lỗi 500 khi tạo/sửa banner. Dưới đây là cách fix:

## 1. Fix lỗi `Maximum recursive updates exceeded` trong Promotions.vue

Vấn đề là do `watch(campaigns, ...)` với `deep: true` đang gây ra vòng lặp vô hạn. Sửa lại như sau:

```vue
<script setup>
// ... phần imports và props giữ nguyên

// State
const campaigns = ref(props.campaigns || []);
const promotions = ref(props.promotions || []);
const banners = ref(props.banners || []);
const products = ref(props.products || []);
const productVariants = ref(props.productVariants || []);
const preorderProducts = ref(props.preorderProducts || []);

// Thêm flag để tránh vòng lặp
let isUpdatingStatus = false;

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
    }
    
    return 'active';
};

// Cập nhật trạng thái - KHÔNG gán trực tiếp vào campaigns.value để tránh vòng lặp
const updateAllCampaignsStatus = () => {
    if (isUpdatingStatus) return;
    isUpdatingStatus = true;
    
    try {
        // Tạo mảng mới thay vì mutate trực tiếp
        const updated = campaigns.value.map(campaign => ({
            ...campaign,
            status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
        }));
        
        // Chỉ cập nhật nếu có thay đổi
        const hasChanged = updated.some((item, index) => 
            item.status !== campaigns.value[index]?.status
        );
        
        if (hasChanged) {
            campaigns.value = updated;
        }
    } finally {
        isUpdatingStatus = false;
    }
};

// ==================== COMPUTED ====================

// Sử dụng computed thay vì watch để tính toán status
const processedCampaigns = computed(() => {
    return campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
});

// Lọc campaigns theo status
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

// Các computed khác giữ nguyên
const filteredVouchers = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'voucher');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const filteredPreorders = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'preorder_tier');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

// Xóa các watch gây lỗi
// watch(campaigns, () => {
//     updateAllCampaignsStatus();
// }, { deep: true });

// Chỉ cần xóa đoạn watch này

// onMounted
onMounted(() => {
    showFlashMessage();
    // Không cần gọi updateAllCampaignsStatus ở đây vì computed đã xử lý
});

// ==================== CÁC HÀM KHÁC GIỮ NGUYÊN ====================
// ... giữ nguyên các hàm còn lại
</script>
```

## 2. Fix lỗi 500 khi tạo/sửa Banner

Lỗi 500 thường do validation hoặc xử lý file upload. Sửa lại BannerController:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::with('campaign')->orderBy('order', 'asc')->get(),
            'campaigns' => Campaign::all()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::with('campaign')->orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'campaign_id' => 'required|exists:campaigns,id',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            // Chỉ validate image_file nếu có file
            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'campaign_id' => $validated['campaign_id'],
                'link' => $validated['link'] ?? null,
                'status' => $validated['status'] ?? 1,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
            $totalBanners = Banner::count();
            $newOrder = $request->input('order', $totalBanners);
            
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            // Cập nhật order cho các banner khác
            if ($newOrder < $totalBanners) {
                Banner::where('order', '>=', $newOrder)->increment('order');
            }

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            // Cập nhật banner_id cho campaign
            if ($banner) {
                Campaign::where('id', $banner->campaign_id)->update(['banner_id' => $banner->id]);
            }

            Log::info('Banner created successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Banner update request:', ['id' => $id, 'data' => $request->all()]);

            $banner = Banner::findOrFail($id);
            
            $rules = [
                'campaign_id' => 'required|exists:campaigns,id',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'campaign_id' => $validated['campaign_id'],
                'link' => $validated['link'] ?? $banner->link,
                'status' => $validated['status'] ?? $banner->status,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    // Xóa ảnh cũ nếu có
                    if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                    }
                    
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                $data['image'] = $banner->image;
            }

            // Xử lý thay đổi order
            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count();

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            // Cập nhật banner_id cho campaign
            Campaign::where('id', $banner->campaign_id)->update(['banner_id' => $banner->id]);

            Log::info('Banner updated successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Các hàm khác giữ nguyên
    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count();
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
```

## 3. Thêm migration cho bảng banners nếu chưa có:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
```

## 4. Chạy lại các lệnh:

```bash
php artisan migrate:fresh --seed
# hoặc
php artisan migrate:refresh --seed
```

Sau khi fix, bạn nên clear cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```


## User:
07/07/2026 07:20

đưa script vue của khuyến mãi hoàn chỉnh cho tôi


## Assistant:
07/07/2026 07:20

Dưới đây là file `Promotions.vue` hoàn chỉnh đã được fix lỗi:

```vue
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
    promotions: {
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
const promotions = ref(props.promotions || []);
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
    banner: '',
    banner_id: null,
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
    banner: '',
    banner_id: null,
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

const getBannerByCampaign = (campaignId) => {
    if (!campaignId) return null;
    return banners.value.find(b => b.campaign_id === campaignId);
};

const getProductName = (productId) => {
    if (!productId) return 'Chưa chọn';
    const product = products.value.find(p => p.id === productId);
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
    }
    
    return 'active';
};

// ==================== BANNER FUNCTIONS ====================

const selectedBanner = computed(() => {
    if (!campaignForm.value.banner_id) return null;
    return banners.value.find(b => b.id === campaignForm.value.banner_id);
});

const selectedPreorderBanner = computed(() => {
    if (!preorderForm.value.banner_id) return null;
    return banners.value.find(b => b.id === preorderForm.value.banner_id);
});

const onBannerSelect = () => {
    const bannerId = campaignForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            campaignForm.value.banner = selected.image;
        }
    } else {
        campaignForm.value.banner = '';
    }
};

const onPreorderBannerSelect = () => {
    const bannerId = preorderForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            preorderForm.value.banner = selected.image;
        }
    } else {
        preorderForm.value.banner = '';
    }
};

const onBannerUrlInput = () => {
    if (campaignForm.value.banner) {
        campaignForm.value.banner_id = null;
    }
};

const onPreorderBannerUrlInput = () => {
    if (preorderForm.value.banner) {
        preorderForm.value.banner_id = null;
    }
};

// ==================== DATE FUNCTIONS ====================

const today = new Date().toISOString().split('T')[0];

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
            banner: campaign.banner || '',
            banner_id: campaign.banner_id || null,
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
            banner: '',
            banner_id: null,
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
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        const status = calculateCampaignStatus(campaignForm.value.startDate, campaignForm.value.endDate);
        
        const data = {
            ...campaignForm.value,
            status: status,
            discountPercent: parseFloat(campaignForm.value.discountPercent) || 0,
            quantity: parseInt(campaignForm.value.quantity) || 1,
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
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
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
            active: voucher.active !== undefined ? voucher.active : true,
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
    
    if (voucherForm.value.discount_value <= 0) {
        errorMessage.value = 'Giá trị giảm giá phải lớn hơn 0';
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
    const voucher = promotions.value.find(p => p.id === id);
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
            name: preorder.code || '',
            product_id: preorder.product_id || null,
            banner: preorder.banner || '',
            banner_id: preorder.banner_id || null,
            tiers: preorder.tiers || [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: preorder.start_date || '',
            end_date: preorder.end_date || '',
            active: preorder.active !== undefined ? preorder.active : true,
            min_order: preorder.min_order || 0,
            campaign_id: preorder.campaign_id || null
        };
    } else {
        preorderForm.value = {
            id: null,
            name: '',
            product_id: null,
            banner: '',
            banner_id: null,
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
    const preorder = promotions.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chương trình "${preorder?.code}"?`)) {
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

// Sử dụng computed thay vì watch để tránh vòng lặp vô hạn
const processedCampaigns = computed(() => {
    return campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
});

// Lọc campaigns theo status
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

const filteredVouchers = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'voucher');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const filteredPreorders = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'preorder_tier');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
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
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'voucher').length }}</span>
                </button>
                <button 
                    @click="activeTab = 'preorder'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'preorder' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    ⏳ Pre-order
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'preorder_tier').length }}</span>
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
                        {{ campaigns.filter(c => status.value === 'all' || c.status === status.value).length }}
                    </span>
                </button>
            </div>

            <!-- ==================== CAMPAIGNS LIST ==================== -->
            <div v-if="activeTab === 'campaigns'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-48 bg-gradient-to-r from-gray-100 to-gray-200">
                        <img 
                            v-if="campaign.banner" 
                            :src="campaign.banner" 
                            :alt="campaign.name" 
                            class="w-full h-full object-cover"
                            @error="campaign.banner = null"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-5xl">campaign</span>
                        </div>
                        
                        <div class="absolute top-3 right-3 flex gap-1">
                            <span class="text-xs px-3 py-1 rounded-full font-medium" :class="getStatusClass(campaign.status)">
                                {{ getStatusLabel(campaign.status) }}
                            </span>
                            <span v-if="campaign.featured" class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">⭐ Nổi bật</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800">{{ campaign.name }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Loại: {{ campaign.type || 'Seasonal' }}</p>
                            </div>
                            <div class="flex gap-1 ml-2">
                                <button @click="openCampaignModal(campaign)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteCampaign(campaign.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ campaign.description || 'Không có mô tả' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="bg-orange-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Giảm giá</p>
                                <p class="text-lg font-bold text-orange-600">{{ campaign.discount || '0%' }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Số lượng tối thiểu</p>
                                <p class="text-lg font-bold text-blue-600">{{ campaign.quantity || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <span>{{ formatDate(campaign.startDate) }} - {{ formatDate(campaign.endDate) }}</span>
                        </div>

                        <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                            <span>{{ campaign.products?.length || 0 }} sản phẩm áp dụng</span>
                        </div>

                        <div v-if="getBannerByCampaign(campaign.id)" class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">image</span>
                            <span>Có banner liên kết</span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleCampaignStatus(campaign)" 
                                class="text-xs px-4 py-2 rounded-full transition-colors font-medium"
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
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="voucher in filteredVouchers" :key="voucher.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ voucher.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-xl text-gray-800 mt-2">{{ voucher.code }}</h3>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openVoucherModal(voucher)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deleteVoucher(voucher.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-3">{{ voucher.description }}</p>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">🎯</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ getTargetTypeLabel(voucher.target_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">💰</span>
                            <span class="text-orange-600 font-semibold">{{ getDiscountTypeLabel(voucher.discount_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📅</span>
                            <span class="text-sm">HSD: {{ formatDate(voucher.expiry) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📊</span>
                            <span class="text-sm">{{ voucher.used }}/{{ voucher.limit }}</span>
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-orange-500 rounded-full transition-all"
                                :style="{ width: Math.min((voucher.used / voucher.limit) * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(voucher)"
                            :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ voucher.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
                    </div>
                </div>

                <div v-if="filteredVouchers.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">local_offer</span>
                    <p class="text-lg font-medium">Không có mã giảm giá nào</p>
                    <button @click="openVoucherModal()" class="mt-3 text-orange-600 hover:underline font-medium">Thêm mã giảm giá</button>
                </div>
            </div>

            <!-- ==================== PRE-ORDERS LIST ==================== -->
            <div v-if="activeTab === 'preorder'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div v-for="preorder in filteredPreorders" :key="preorder.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ preorder.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-lg text-gray-800 mt-2">⏳ {{ preorder.code }}</h3>
                            <p class="text-xs text-gray-500">Sản phẩm: {{ getProductName(preorder.product_id) }}</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openPreorderModal(preorder)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deletePreorder(preorder.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <div class="mt-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium">Đã đặt trước: {{ preorder.current_buyers || 0 }} lượt</span>
                            <span class="text-sm font-bold text-blue-600">
                                Giảm hiện tại: 
                                {{ preorder.tiers?.find(t => (preorder.current_buyers || 0) >= t.from && (preorder.current_buyers || 0) <= t.to)?.discount || 0 }}%
                            </span>
                        </div>

                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all"
                                :style="{ width: Math.min(((preorder.current_buyers || 0) / (preorder.tiers?.[preorder.tiers.length - 1]?.to || 100)) * 100, 100) + '%' }"
                            ></div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            <div v-for="tier in preorder.tiers" :key="tier.from" 
                                class="text-center p-2 bg-white rounded border"
                                :class="(preorder.current_buyers || 0) >= tier.from && (preorder.current_buyers || 0) <= tier.to ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            >
                                <div class="font-bold">{{ tier.discount }}%</div>
                                <div class="text-gray-500">#{{ tier.from }}-{{ tier.to }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                        <span>📅 {{ formatDate(preorder.start_date) }} - {{ formatDate(preorder.end_date) }}</span>
                        <span v-if="preorder.min_order > 0">💰 Đơn tối thiểu: {{ formatPrice(preorder.min_order) }}</span>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(preorder)"
                            :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ preorder.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
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
                                :min="campaignForm.startDate || today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
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

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="campaignForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                @change="onBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedBanner && selectedBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedBanner.campaign_name || 'Banner #' + selectedBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="campaignForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onBannerUrlInput"
                            >
                        </div>

                        <div v-if="campaignForm.banner && !selectedBanner" class="mt-2 relative">
                            <img 
                                :src="campaignForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="campaignForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="campaignForm.banner = ''; campaignForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
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
                            <p class="text-xs text-gray-500 mt-1">Phần trăm giảm giá (0-100%)</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm áp dụng</label>
                        
                        <div v-if="productVariants.filter(v => !v.product?.is_preorder).length > 0" class="mb-3">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">📦 Sản phẩm thường</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => !v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => !v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'normal-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                    >
                                    <label :for="'normal-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.filter(v => v.product?.is_preorder).length > 0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded">⏳ Sản phẩm Pre-order</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'preorder-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                                    >
                                    <label :for="'preorder-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.length === 0" class="border border-gray-300 rounded-lg p-6 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl block mb-2">inventory_2</span>
                            <p>Không có sản phẩm nào</p>
                            <p class="text-xs mt-1">Vui lòng thêm sản phẩm và biến thể trước khi tạo chiến dịch</p>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-2">Tổng: <span class="font-semibold">{{ campaignForm.products.length }}</span> sản phẩm được chọn</div>
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá trị giảm *</label>
                        <input 
                            v-model.number="voucherForm.discount_value" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="50000"
                        >
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="preorderForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                                @change="onPreorderBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedPreorderBanner && selectedPreorderBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedPreorderBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedPreorderBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedPreorderBanner.campaign_name || 'Banner #' + selectedPreorderBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedPreorderBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedPreorderBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="preorderForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onPreorderBannerUrlInput"
                            >
                        </div>

                        <div v-if="preorderForm.banner && !selectedPreorderBanner" class="mt-2 relative">
                            <img 
                                :src="preorderForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="preorderForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="preorderForm.banner = ''; preorderForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
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
```

Đây là file hoàn chỉnh đã được fix lỗi `Maximum recursive updates exceeded`. Các thay đổi chính:

1. **Xóa `watch(campaigns, ...)`** - vốn là nguyên nhân gây vòng lặp vô hạn
2. **Sử dụng `processedCampaigns` computed** - để tính toán trạng thái campaign thay vì mutate trực tiếp
3. **Tất cả các computed khác** sử dụng `processedCampaigns` thay vì `campaigns` để lấy dữ liệu đã xử lý
4. **Giữ nguyên `campaigns` ref** để lưu dữ liệu gốc từ props


## User:
07/07/2026 07:32

tôi muốn quản lý  banner chỉ lưu ảnh thông tin banner (không cần chọn chiến dịch) bước đó sẽ được tạo khi setting chiến dịch ( tránh lỗi 2 bên không đồng nhất  "hiện giờ đang bị ") chỉnh lại banner và khuyến mãi cho tôi


## Assistant:
07/07/2026 07:32

Tôi sẽ chỉnh lại để tách biệt hoàn toàn giữa Banner và Campaign. Banner chỉ đơn thuần là quản lý ảnh, không liên kết với campaign. Khi tạo campaign, người dùng sẽ tự chọn banner từ danh sách có sẵn.

## 1. Migration cho bảng banners (sửa lại)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
```

## 2. Model Banner (sửa lại)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'image', 
        'link', 
        'description',
        'status', 
        'order'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];
}
```

## 3. BannerController (hoàn chỉnh)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::orderBy('order', 'asc')->get()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? 'Banner ' . now()->format('d/m/Y'),
                'link' => $validated['link'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? true,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
            $totalBanners = Banner::count();
            $newOrder = $request->input('order', $totalBanners);
            
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            if ($newOrder < $totalBanners) {
                Banner::where('order', '>=', $newOrder)->increment('order');
            }

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            Log::info('Banner created successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Banner update request:', ['id' => $id, 'data' => $request->all()]);

            $banner = Banner::findOrFail($id);
            
            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? $banner->title,
                'link' => $validated['link'] ?? $banner->link,
                'description' => $validated['description'] ?? $banner->description,
                'status' => $validated['status'] ?? $banner->status,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                    }
                    
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            }

            // Xử lý thay đổi order
            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count();

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            Log::info('Banner updated successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count();
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
```

## 4. Campaign Model (thêm quan hệ với banner)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner_id',  // Chỉ lưu ID của banner, không lưu URL
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_product_variants');
    }

    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'campaign_banners');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? $this->banner->image : null;
    }
}
```

## 5. Migration cho bảng campaign_banners (nếu cần nhiều banner cho 1 campaign)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaign_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('banner_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_banners');
    }
};
```

## 6. Sửa lại Campaign Model (đơn giản hơn - chỉ lưu banner_id)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner_id',
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_product_variants');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? $this->banner->image : null;
    }
}
```

## 7. Sửa lại PromotionController (phần index)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns - không cần load banners qua quan hệ nữa
            $campaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color', 'banner'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'banner' => $campaign->banner ? $campaign->banner->image : '', // Lấy URL từ banner
                        'banner_id' => $campaign->banner_id ?? null,
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'productVariants' => $campaign->productVariants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->product->name ?? 'Sản phẩm',
                                'color' => $variant->color->name ?? 'Không màu',
                                'price' => $variant->price ?? 0,
                            ];
                        }),
                    ];
                });

            // Lấy promotions (vouchers và preorder)
            $promotions = Promotion::with(['campaign', 'product'])->latest()->get();

            // Lấy tất cả banners - KHÔNG cần liên kết với campaign
            $banners = Banner::orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'Banner #' . $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'description' => $banner->description,
                    'status' => $banner->status ? 1 : 0,
                    'order' => $banner->order,
                ];
            });

            // Lấy tất cả products (cho voucher)
            $products = Product::with(['variants.color', 'category', 'brand'])->latest()->get();

            // Lấy product variants (cho campaign)
            $productVariants = ProductVariant::with(['product', 'color'])->latest()->get();

            // Lấy chỉ sản phẩm pre-order (cho pre-order)
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get();

            Log::info('Banners count: ' . $banners->count());
            Log::info('Preorder Products count: ' . $preorderProducts->count());

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CAMPAIGN METHODS ====================
    
    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            // KHÔNG lưu URL banner, chỉ lưu banner_id
            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'banner_id' => $validated['banner_id'] ?? null,
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            if (!empty($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'banner_id' => $validated['banner_id'] ?? $campaign->banner_id,
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    // ... Các hàm khác giữ nguyên
}
```

## 8. Cập nhật routes

```php
<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::put('/banners/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.status');
    Route::put('/banners/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.order');

    // Promotion routes
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions/campaign', [PromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
    Route::put('/promotions/campaign/{id}', [PromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
    Route::delete('/promotions/campaign/{id}', [PromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
    Route::put('/promotions/campaign/{id}/status', [PromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');

    Route::post('/promotions/voucher', [PromotionController::class, 'storePromotion'])->name('promotions.voucher.store');
    Route::put('/promotions/voucher/{id}', [PromotionController::class, 'updatePromotion'])->name('promotions.voucher.update');
    Route::delete('/promotions/voucher/{id}', [PromotionController::class, 'deletePromotion'])->name('promotions.voucher.delete');
    Route::put('/promotions/voucher/{id}/toggle', [PromotionController::class, 'togglePromotion'])->name('promotions.voucher.toggle');

    Route::post('/promotions/preorder', [PromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
    Route::put('/promotions/preorder/{id}', [PromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
    Route::delete('/promotions/preorder/{id}', [PromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
});
```

## 9. File Promotions.vue (phần sửa liên quan đến banner)

Trong file `Promotions.vue`, sửa phần chọn banner:

```vue
<!-- Trong campaign modal -->
<div>
    <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
    <div class="flex gap-3">
        <select 
            v-model="campaignForm.banner_id" 
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
            @change="onBannerSelect"
        >
            <option :value="null">-- Không chọn banner --</option>
            <option 
                v-for="banner in banners" 
                :key="banner.id" 
                :value="banner.id"
            >
                {{ banner.title || 'Banner #' + banner.id }} 
                {{ banner.image ? '📷' : '🚫' }}
                {{ banner.status === 1 ? '✅' : '⛔' }}
            </option>
        </select>
    </div>
    
    <!-- Preview banner đã chọn -->
    <div v-if="selectedBanner && selectedBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
        <img 
            :src="selectedBanner.image" 
            class="h-16 w-24 object-cover rounded border"
            @error="selectedBanner.image = null"
            alt="Banner preview"
        >
        <div class="flex-1">
            <p class="text-sm font-medium">{{ selectedBanner.title || 'Banner #' + selectedBanner.id }}</p>
            <p class="text-xs text-gray-500 truncate">{{ selectedBanner.image }}</p>
            <p class="text-xs text-gray-400">Trạng thái: {{ selectedBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
        </div>
        <button 
            @click="campaignForm.banner_id = null" 
            class="text-red-500 hover:text-red-700 text-sm"
        >
            ✕
        </button>
    </div>
    
    <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
</div>
```

## 10. Chạy migration

```bash
# Xóa bảng banners cũ và tạo lại
php artisan migrate:refresh --path=database/migrations/xxxx_xx_xx_create_banners_table.php

# Hoặc tạo migration mới
php artisan make:migration update_banners_table_remove_campaign_id

# Trong migration mới:
Schema::table('banners', function (Blueprint $table) {
    $table->dropForeign(['campaign_id']);
    $table->dropColumn('campaign_id');
    $table->string('title')->nullable()->after('id');
    $table->text('description')->nullable()->after('link');
});
```

Sau khi thay đổi, chạy:

```bash
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

Bây giờ Banner và Campaign đã tách biệt hoàn toàn:
- **Banner**: Chỉ quản lý ảnh, tiêu đề, link, mô tả
- **Campaign**: Khi tạo sẽ chọn banner từ danh sách có sẵn (chỉ lưu banner_id)


## User:
07/07/2026 07:38

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner',
        'banner_id',
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_products');
    }

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ với Banner - Một Campaign có nhiều Banner
    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    // Quan hệ với Banner chính
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('d/m/Y');
            $end = $this->end_time->format('d/m/Y');
            return "Chiến dịch {$start} - {$end}";
        }
        return 'Chiến dịch';
    }

    public function getStartDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getEndDateAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : null;
    }

    public function getDiscountAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->discount_percent . '%' : '0%';
    }

    public function getMinQuantityAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->quantity : 0;
    }

    public function getProductIdsAttribute()
    {
        return $this->productVariants->pluck('id')->toArray();
    }
}


## Assistant:
07/07/2026 07:38

Tôi thấy bạn đã có model Campaign với cả 2 trường `banner` và `banner_id`. Để tách biệt hoàn toàn, tôi sẽ sửa lại:

## 1. Sửa Model Campaign (bỏ trường `banner`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner_id',  // Chỉ giữ banner_id
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_products');
    }

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ với Banner - Một Campaign có nhiều Banner (nếu dùng quan hệ nhiều-nhiều)
    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'campaign_banners');
    }

    // Quan hệ với Banner chính - Một Campaign có một Banner chính
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    // Accessor để lấy URL banner
    public function getBannerUrlAttribute()
    {
        return $this->banner ? $this->banner->image : null;
    }

    // Accessor để lấy thông tin banner
    public function getBannerInfoAttribute()
    {
        if (!$this->banner) return null;
        return [
            'id' => $this->banner->id,
            'title' => $this->banner->title,
            'image' => $this->banner->image,
            'link' => $this->banner->link,
        ];
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('d/m/Y');
            $end = $this->end_time->format('d/m/Y');
            return "Chiến dịch {$start} - {$end}";
        }
        return 'Chiến dịch';
    }

    public function getStartDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getEndDateAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : null;
    }

    public function getDiscountAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->discount_percent . '%' : '0%';
    }

    public function getMinQuantityAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->quantity : 0;
    }

    public function getProductIdsAttribute()
    {
        return $this->productVariants->pluck('id')->toArray();
    }
}
```

## 2. Migration để xóa trường `banner` khỏi campaigns

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Xóa trường banner cũ (nếu có)
            if (Schema::hasColumn('campaigns', 'banner')) {
                $table->dropColumn('banner');
            }
            
            // Đảm bảo có banner_id
            if (!Schema::hasColumn('campaigns', 'banner_id')) {
                $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('banner')->nullable();
        });
    }
};
```

## 3. Sửa lại PromotionController - phần store và update campaign

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns - load banner
            $campaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color', 'banner'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'banner' => $campaign->banner ? $campaign->banner->image : '', // Lấy URL từ banner
                        'banner_id' => $campaign->banner_id ?? null,
                        'banner_title' => $campaign->banner ? $campaign->banner->title : null,
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'productVariants' => $campaign->productVariants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->product->name ?? 'Sản phẩm',
                                'color' => $variant->color->name ?? 'Không màu',
                                'price' => $variant->price ?? 0,
                            ];
                        }),
                    ];
                });

            // Lấy promotions (vouchers và preorder)
            $promotions = Promotion::with(['campaign', 'product'])->latest()->get();

            // Lấy tất cả banners - KHÔNG liên kết với campaign
            $banners = Banner::orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'Banner #' . $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'description' => $banner->description,
                    'status' => $banner->status ? 1 : 0,
                    'order' => $banner->order,
                ];
            });

            // Lấy tất cả products (cho voucher)
            $products = Product::with(['variants.color', 'category', 'brand'])->latest()->get();

            // Lấy product variants (cho campaign)
            $productVariants = ProductVariant::with(['product', 'color'])->latest()->get();

            // Lấy chỉ sản phẩm pre-order (cho pre-order)
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get();

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID banner
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            // Không lưu URL banner, chỉ lưu banner_id
            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'banner_id' => $validated['banner_id'] ?? null, // Chỉ lưu ID
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            if (!empty($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID banner
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'banner_id' => $validated['banner_id'] ?? $campaign->banner_id, // Chỉ lưu ID
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    // ... Các hàm khác giữ nguyên
}
```

## 4. Sửa trong Promotions.vue - phần hiển thị banner

```vue
<!-- Trong campaign card - hiển thị banner -->
<div class="relative h-48 bg-gradient-to-r from-gray-100 to-gray-200">
    <img 
        v-if="campaign.banner" 
        :src="campaign.banner" 
        :alt="campaign.name" 
        class="w-full h-full object-cover"
        @error="campaign.banner = null"
    >
    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        <span class="material-symbols-outlined text-5xl">campaign</span>
    </div>
    
    <!-- Hiển thị tên banner nếu có -->
    <div v-if="campaign.banner_title" class="absolute bottom-2 left-2">
        <span class="text-xs px-2 py-1 bg-black/50 text-white rounded-full">
            {{ campaign.banner_title }}
        </span>
    </div>
    
    <div class="absolute top-3 right-3 flex gap-1">
        <span class="text-xs px-3 py-1 rounded-full font-medium" :class="getStatusClass(campaign.status)">
            {{ getStatusLabel(campaign.status) }}
        </span>
        <span v-if="campaign.featured" class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">⭐ Nổi bật</span>
    </div>
</div>
```

## 5. Migration để xóa trường banner khỏi bảng campaigns

```bash
php artisan make:migration remove_banner_from_campaigns
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Kiểm tra và xóa trường banner nếu tồn tại
            if (Schema::hasColumn('campaigns', 'banner')) {
                $table->dropColumn('banner');
            }
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('banner')->nullable();
        });
    }
};
```

## 6. Chạy các lệnh

```bash
# Chạy migration
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Nếu cần refresh
php artisan migrate:fresh --seed
```

## 7. Kiểm tra dữ liệu cũ (nếu có)

Nếu bạn có dữ liệu cũ với trường `banner` đang lưu URL, bạn có thể tạo migration để chuyển đổi:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Campaign;
use App\Models\Banner;

return new class extends Migration
{
    public function up()
    {
        // Chuyển đổi dữ liệu cũ: tạo banner từ URL cũ
        $campaigns = Campaign::whereNotNull('banner')->whereNull('banner_id')->get();
        
        foreach ($campaigns as $campaign) {
            // Tạo banner mới từ URL cũ
            $banner = Banner::create([
                'title' => $campaign->name . ' - Banner',
                'image' => $campaign->banner,
                'status' => true,
                'order' => 0,
            ]);
            
            // Cập nhật campaign với banner_id mới
            $campaign->update(['banner_id' => $banner->id]);
        }
        
        // Xóa trường banner sau khi chuyển đổi
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'banner')) {
                $table->dropColumn('banner');
            }
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('banner')->nullable();
        });
    }
};
```

Sau khi hoàn thành, bạn sẽ có:
- **Banners**: Chỉ lưu ảnh và thông tin banner, hoàn toàn độc lập
- **Campaigns**: Chỉ lưu `banner_id` để tham chiếu đến banner đã chọn
- **Không có sự phụ thuộc 2 chiều** giữa banner và campaign


## User:
07/07/2026 07:43

Bạn xem chỉnh lại và đưa "code hoàn chỉnh" cho tôi.

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns - load banner
            $campaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color', 'banner'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'banner' => $campaign->banner ? $campaign->banner->image : '', // Lấy URL từ banner
                        'banner_id' => $campaign->banner_id ?? null,
                        'banner_title' => $campaign->banner ? $campaign->banner->title : null,
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'productVariants' => $campaign->productVariants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->product->name ?? 'Sản phẩm',
                                'color' => $variant->color->name ?? 'Không màu',
                                'price' => $variant->price ?? 0,
                            ];
                        }),
                    ];
                });

            // Lấy promotions (vouchers và preorder)
            $promotions = Promotion::with(['campaign', 'product'])->latest()->get();

            // Lấy tất cả banners - KHÔNG liên kết với campaign
            $banners = Banner::orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'Banner #' . $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'description' => $banner->description,
                    'status' => $banner->status ? 1 : 0,
                    'order' => $banner->order,
                ];
            });

            // Lấy tất cả products (cho voucher)
            $products = Product::with(['variants.color', 'category', 'brand'])->latest()->get();

            // Lấy product variants (cho campaign)
            $productVariants = ProductVariant::with(['product', 'color'])->latest()->get();

            // Lấy chỉ sản phẩm pre-order (cho pre-order)
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get();

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID banner
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            // Không lưu URL banner, chỉ lưu banner_id
            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'banner_id' => $validated['banner_id'] ?? null, // Chỉ lưu ID
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            if (!empty($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id', // Chỉ lưu ID banner
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'banner_id' => $validated['banner_id'] ?? $campaign->banner_id, // Chỉ lưu ID
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }


    public function deleteCampaign($id)
    {
        try {
            DB::beginTransaction();
            
            $campaign = Campaign::findOrFail($id);
            $campaign->configs()->delete();
            $campaign->productVariants()->detach();
            $campaign->delete();

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaignStatus(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $request->validate(['status' => 'required|in:scheduled,active,ended']);
            $campaign->update(['status' => $request->status]);
            
            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== VOUCHER METHODS ====================

    public function storePromotion(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code',
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion = Promotion::create([
                'code' => strtoupper($validated['code']),
                'type' => 'voucher',
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'used' => 0,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePromotion(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code,' . $id,
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion->update([
                'code' => strtoupper($validated['code']),
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update(['active' => !$promotion->active]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== PRE-ORDER METHODS ====================

    public function storePreorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion = Promotion::create([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'type' => 'preorder_tier',
                'target_type' => 'preorder',
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
                'current_buyers' => 0,
                'description' => "Giảm giá theo số lượt đặt trước",
                'discount_value' => 0,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePreorder(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion->update([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePreorder($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::orderBy('order', 'asc')->get()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? 'Banner ' . now()->format('d/m/Y'),
                'link' => $validated['link'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? true,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
            $totalBanners = Banner::count();
            $newOrder = $request->input('order', $totalBanners);
            
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            if ($newOrder < $totalBanners) {
                Banner::where('order', '>=', $newOrder)->increment('order');
            }

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            Log::info('Banner created successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Banner update request:', ['id' => $id, 'data' => $request->all()]);

            $banner = Banner::findOrFail($id);
            
            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? $banner->title,
                'link' => $validated['link'] ?? $banner->link,
                'description' => $validated['description'] ?? $banner->description,
                'status' => $validated['status'] ?? $banner->status,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                    }
                    
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            }

            // Xử lý thay đổi order
            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count();

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            Log::info('Banner updated successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count();
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'image', 
        'link', 
        'description',
        'status', 
        'order'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner_id',  // Chỉ giữ banner_id
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_products');
    }

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ với Banner - Một Campaign có nhiều Banner (nếu dùng quan hệ nhiều-nhiều)
    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'campaign_banners');
    }

    // Quan hệ với Banner chính - Một Campaign có một Banner chính
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    // Accessor để lấy URL banner
    public function getBannerUrlAttribute()
    {
        return $this->banner ? $this->banner->image : null;
    }

    // Accessor để lấy thông tin banner
    public function getBannerInfoAttribute()
    {
        if (!$this->banner) return null;
        return [
            'id' => $this->banner->id,
            'title' => $this->banner->title,
            'image' => $this->banner->image,
            'link' => $this->banner->link,
        ];
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('d/m/Y');
            $end = $this->end_time->format('d/m/Y');
            return "Chiến dịch {$start} - {$end}";
        }
        return 'Chiến dịch';
    }

    public function getStartDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getEndDateAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : null;
    }

    public function getDiscountAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->discount_percent . '%' : '0%';
    }

    public function getMinQuantityAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->quantity : 0;
    }

    public function getProductIdsAttribute()
    {
        return $this->productVariants->pluck('id')->toArray();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type', // voucher, preorder_tier
        'target_type', // retail, wholesale, preorder, all
        'discount_type', // fixed, percent, freeship
        'discount_value',
        'min_order',
        'limit',
        'used',
        'expiry',
        'active',
        'description',
        'product_id',
        'tiers',
        'current_buyers',
        'start_date',
        'end_date',
        'campaign_id'
    ];

    protected $casts = [
        'tiers' => 'array',
        'active' => 'boolean',
        'discount_value' => 'decimal:0',
        'min_order' => 'decimal:0',
        'expiry' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    banners: {
        type: Array,
        default: () => []
    },
    campaigns: {
        type: Array,
        default: () => []
    }
})

// Pagination - 5 items per page
const currentPage = ref(1)
const perPage = ref(5)

// Search
const search = ref('')

// Lọc banners theo tên chiến dịch hoặc link
const filteredBanners = computed(() => {
    if (!banners.value || banners.value.length === 0) return []
    if (!search.value) return banners.value
    
    const keyword = search.value.toLowerCase().trim()
    return banners.value.filter(banner => {
        const campaignName = (banner.campaign?.name || '').toLowerCase()
        const link = (banner.link || '').toLowerCase()
        return campaignName.includes(keyword) || link.includes(keyword)
    })
})

// Sắp xếp banners theo order
const sortedBanners = computed(() => {
    return [...filteredBanners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
})

// Pagination
const paginatedBanners = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return sortedBanners.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(sortedBanners.value.length / perPage.value)
})

// Hiển thị số trang (tối đa 5 trang)
const displayedPages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const maxDisplay = 5
    
    if (total <= maxDisplay) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }
    
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + maxDisplay - 1)
    
    if (end - start < maxDisplay - 1) {
        start = Math.max(1, end - maxDisplay + 1)
    }
    
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

// Reset về trang 1 khi tìm kiếm
watch(search, () => {
    currentPage.value = 1
})

const banners = ref(props.banners || [])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBanner = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')
const uploadSuccess = ref(false)

// Chọn phương thức nhập ảnh: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    campaign_id: '',
    image: '',
    link: '',
    status: 1,
    order: 0
})

// Xem trước ảnh
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.image) return form.value.image
    return null
})

// Format date
const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBanners = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/banners/data')
        if (response.data && Array.isArray(response.data)) {
            banners.value = response.data
        } else {
            banners.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách banner:', error)
        banners.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { 
        id: null, 
        campaign_id: '', 
        image: '', 
        link: '', 
        status: 1, 
        order: 0 
    }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    uploadSuccess.value = false
    showModal.value = true
}

const openEditModal = (banner) => {
    isEdit.value = true
    form.value = { 
        id: banner.id,
        campaign_id: banner.campaign_id || '', 
        image: banner.image || '', 
        link: banner.link || '', 
        status: banner.status !== undefined ? banner.status : 1, 
        order: banner.order || 0 
    }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    uploadSuccess.value = false
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) {
        return
    }
    // Kiểm tra định dạng file
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg, webp)'
        event.target.value = ''
        return
    }
    // Kiểm tra kích thước (2MB)
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        event.target.value = ''
        return
    }
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { 
        imagePreviewUrl.value = e.target.result 
    }
    reader.readAsDataURL(file)
    // Xóa URL cũ nếu có
    form.value.image = ''
}

const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = ''
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBanner = async () => {
    // Validate
    if (!form.value.campaign_id) {
        errorMessage.value = 'Vui lòng chọn chiến dịch'
        return
    }
    
    // Kiểm tra có ảnh không (URL hoặc file)
    if (!form.value.image && !selectedFile.value) {
        errorMessage.value = 'Vui lòng nhập URL ảnh hoặc tải ảnh lên'
        return
    }
    
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    uploadSuccess.value = false

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật banner
            if (selectedFile.value) {
                // Có file mới -> upload
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order || 0)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post(`/admin/banners/${form.value.id}`, formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                // Không có file mới, chỉ cập nhật thông tin
                const dataToSave = {
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order || 0,
                    image: form.value.image || null
                }
                response = await axios.put(`/admin/banners/${form.value.id}`, dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success) {
                // Cập nhật danh sách
                const index = banners.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    banners.value[index] = response.data.data
                } else {
                    // Reload để lấy dữ liệu mới
                    await fetchBanners()
                }
                uploadSuccess.value = true
                alert('Cập nhật banner thành công!')
                showModal.value = false
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra khi cập nhật'
            }
        } else {
            // Thêm mới banner
            if (selectedFile.value) {
                // Upload file
                const formData = new FormData()
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order || 0)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post('/admin/banners', formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                // Dùng URL
                const dataToSave = {
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order || 0,
                    image: form.value.image || null
                }
                response = await axios.post('/admin/banners', dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success && response.data.data) {
                banners.value.push(response.data.data)
                uploadSuccess.value = true
                alert('Thêm banner thành công!')
                showModal.value = false
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra khi thêm mới'
            }
        }
    } catch (error) {
        console.error('Lỗi lưu banner:', error)
        if (error.response) {
            errorMessage.value = error.response.data?.message || 'Có lỗi xảy ra'
        } else {
            errorMessage.value = 'Không thể kết nối đến server'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (banner) => {
    selectedBanner.value = banner
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBanner = async () => {
    if (!selectedBanner.value) return
    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    try {
        const response = await axios.delete(`/admin/banners/${selectedBanner.value.id}`, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = banners.value.findIndex(b => b.id === selectedBanner.value.id)
            if (index !== -1) {
                banners.value.splice(index, 1)
            }
            selectedBanner.value = null
            alert('Xóa banner thành công!')
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa banner:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const toggleStatus = async (banner) => {
    try {
        const newStatus = banner.status === 1 ? 0 : 1
        const response = await axios.patch(`/admin/banners/${banner.id}/status`, { 
            status: newStatus 
        }, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            banner.status = newStatus
        } else {
            alert('Cập nhật trạng thái thất bại')
        }
    } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error)
        alert('Có lỗi xảy ra')
    }
}

const updateOrder = async (banner, newOrder) => {
    try {
        const response = await axios.patch(`/admin/banners/${banner.id}/order`, { 
            order: newOrder 
        }, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            banner.order = newOrder
            // Sắp xếp lại danh sách
            banners.value = [...banners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
        }
    } catch (error) {
        console.error('Lỗi cập nhật thứ tự:', error)
        alert('Có lỗi xảy ra khi cập nhật thứ tự')
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBanner.value = null
    form.value = { id: null, campaign_id: '', image: '', link: '', status: 1, order: 0 }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    uploadSuccess.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (banners.value.length === 0) {
        fetchBanners()
    }
})
</script>

<template>
    <Head title="Quản lý Banner" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý Banner</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors" :disabled="isSaving">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm banner mới
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo chiến dịch hoặc link..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <div v-if="isLoading && banners.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left p-4 font-semibold text-gray-700 w-16 whitespace-nowrap">STT</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Hình ảnh</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Chiến dịch</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Link</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Trạng thái</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Thứ tự</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Ngày tạo</th>
                                <th class="text-center p-4 font-semibold text-gray-700 w-40 whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(banner, index) in paginatedBanners" :key="banner.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="p-4">
                                    <img v-if="banner.image" :src="banner.image" class="h-12 w-20 object-cover rounded" :alt="'Banner ' + banner.id" @error="banner.image = null">
                                    <span v-else class="text-gray-400">---</span>
                                </td>
                                <td class="p-4 text-gray-700 whitespace-nowrap">{{ banner.campaign?.name || 'Chưa phân loại' }}</td>
                                <td class="p-4 text-gray-500 text-sm max-w-xs truncate">{{ banner.link || '---' }}</td>
                                <td class="p-4">php artisan migrate
                                    <button @click="toggleStatus(banner)" class="px-2 py-1 text-xs rounded-full transition whitespace-nowrap" :class="banner.status === 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                        {{ banner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}
                                    </button>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1">
                                        <input type="number" :value="banner.order || 0" @change="updateOrder(banner, parseInt($event.target.value))" class="w-16 px-1 py-1 border rounded text-center text-sm" min="0">
                                        <span class="text-xs text-gray-400">#</span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ formatDate(banner.created_at) }}</td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(banner)" class="px-3 py-1.5 text-xs text-green-600 hover:bg-green-100 rounded-lg transition-colors font-medium" :disabled="isSaving">Sửa</button>
                                        <button @click="confirmDelete(banner)" class="px-3 py-1.5 text-xs text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium" :disabled="isSaving">Xóa</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedBanners.length === 0 && !isLoading">
                                <td colspan="8" class="p-8 text-center text-gray-400">Chưa có banner nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang căn giữa -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedBanners.length }} / {{ sortedBanners.length }} banner
                    </div>
                    
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

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Sửa banner' : 'Thêm banner mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiến dịch *</label>
                        <select v-model="form.campaign_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option value="">-- Chọn chiến dịch --</option>
                            <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">{{ camp.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh banner *</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">📁 Tải ảnh lên</button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.image" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="https://example.com/banner.jpg" :disabled="isSaving">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" class="w-full" :disabled="isSaving">
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1 hover:underline" type="button">✕ Xóa file đã chọn</button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG, WEBP. Kích thước tối đa 2MB</p>
                        </div>
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img :src="imagePreview" class="max-w-full max-h-full object-contain" @error="imagePreviewUrl = ''; form.image = ''" alt="Banner preview">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link (URL)</label>
                        <input v-model="form.link" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="https://example.com" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Đường dẫn khi người dùng click vào banner</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select v-model="form.status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm dừng</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                        <input v-model.number="form.order" type="number" min="0" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Số nhỏ hơn hiển thị trước</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                    
                    <div v-if="uploadSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-600">✅ Lưu thành công!</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="saveBanner" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2" :disabled="isSaving || !!fileError">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa banner này?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="deleteBanner" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
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
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

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
    promotions: {
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
const promotions = ref(props.promotions || []);
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
    banner: '',
    banner_id: null,
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
    banner: '',
    banner_id: null,
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

const getBannerByCampaign = (campaignId) => {
    if (!campaignId) return null;
    return banners.value.find(b => b.campaign_id === campaignId);
};

const getProductName = (productId) => {
    if (!productId) return 'Chưa chọn';
    const product = products.value.find(p => p.id === productId);
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
    }
    
    return 'active';
};

// ==================== BANNER FUNCTIONS ====================

const selectedBanner = computed(() => {
    if (!campaignForm.value.banner_id) return null;
    return banners.value.find(b => b.id === campaignForm.value.banner_id);
});

const selectedPreorderBanner = computed(() => {
    if (!preorderForm.value.banner_id) return null;
    return banners.value.find(b => b.id === preorderForm.value.banner_id);
});

const onBannerSelect = () => {
    const bannerId = campaignForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            campaignForm.value.banner = selected.image;
        }
    } else {
        campaignForm.value.banner = '';
    }
};

const onPreorderBannerSelect = () => {
    const bannerId = preorderForm.value.banner_id;
    if (bannerId) {
        const selected = banners.value.find(b => b.id === bannerId);
        if (selected && selected.image) {
            preorderForm.value.banner = selected.image;
        }
    } else {
        preorderForm.value.banner = '';
    }
};

const onBannerUrlInput = () => {
    if (campaignForm.value.banner) {
        campaignForm.value.banner_id = null;
    }
};

const onPreorderBannerUrlInput = () => {
    if (preorderForm.value.banner) {
        preorderForm.value.banner_id = null;
    }
};

// ==================== DATE FUNCTIONS ====================

const today = new Date().toISOString().split('T')[0];

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
            banner: campaign.banner || '',
            banner_id: campaign.banner_id || null,
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
            banner: '',
            banner_id: null,
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
    
    isSubmitting.value = true;
    errorMessage.value = '';
    
    try {
        const status = calculateCampaignStatus(campaignForm.value.startDate, campaignForm.value.endDate);
        
        const data = {
            ...campaignForm.value,
            status: status,
            discountPercent: parseFloat(campaignForm.value.discountPercent) || 0,
            quantity: parseInt(campaignForm.value.quantity) || 1,
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
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
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
            active: voucher.active !== undefined ? voucher.active : true,
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
    
    if (voucherForm.value.discount_value <= 0) {
        errorMessage.value = 'Giá trị giảm giá phải lớn hơn 0';
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
    const voucher = promotions.value.find(p => p.id === id);
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
            name: preorder.code || '',
            product_id: preorder.product_id || null,
            banner: preorder.banner || '',
            banner_id: preorder.banner_id || null,
            tiers: preorder.tiers || [
                { from: 1, to: 10, discount: 20 },
                { from: 11, to: 20, discount: 10 },
                { from: 21, to: 30, discount: 5 }
            ],
            start_date: preorder.start_date || '',
            end_date: preorder.end_date || '',
            active: preorder.active !== undefined ? preorder.active : true,
            min_order: preorder.min_order || 0,
            campaign_id: preorder.campaign_id || null
        };
    } else {
        preorderForm.value = {
            id: null,
            name: '',
            product_id: null,
            banner: '',
            banner_id: null,
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
    const preorder = promotions.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa chương trình "${preorder?.code}"?`)) {
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

// Sử dụng computed thay vì watch để tránh vòng lặp vô hạn
const processedCampaigns = computed(() => {
    return campaigns.value.map(campaign => ({
        ...campaign,
        status: calculateCampaignStatus(campaign.startDate, campaign.endDate)
    }));
});

// Lọc campaigns theo status
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

const filteredVouchers = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'voucher');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(v => 
            (v.code && v.code.toLowerCase().includes(query)) ||
            (v.description && v.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const filteredPreorders = computed(() => {
    let filtered = (promotions.value || []).filter(p => p.type === 'preorder_tier');
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => 
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }
    
    return filtered;
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
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'voucher').length }}</span>
                </button>
                <button 
                    @click="activeTab = 'preorder'" 
                    class="px-5 py-2.5 text-sm font-medium transition-all whitespace-nowrap"
                    :class="activeTab === 'preorder' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    ⏳ Pre-order
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ promotions.filter(p => p.type === 'preorder_tier').length }}</span>
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
                        {{ campaigns.filter(c => status.value === 'all' || c.status === status.value).length }}
                    </span>
                </button>
            </div>

            <!-- ==================== CAMPAIGNS LIST ==================== -->
            <div v-if="activeTab === 'campaigns'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <div class="relative h-48 bg-gradient-to-r from-gray-100 to-gray-200">
                        <img 
                            v-if="campaign.banner" 
                            :src="campaign.banner" 
                            :alt="campaign.name" 
                            class="w-full h-full object-cover"
                            @error="campaign.banner = null"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <span class="material-symbols-outlined text-5xl">campaign</span>
                        </div>
                        
                        <div class="absolute top-3 right-3 flex gap-1">
                            <span class="text-xs px-3 py-1 rounded-full font-medium" :class="getStatusClass(campaign.status)">
                                {{ getStatusLabel(campaign.status) }}
                            </span>
                            <span v-if="campaign.featured" class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">⭐ Nổi bật</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800">{{ campaign.name }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Loại: {{ campaign.type || 'Seasonal' }}</p>
                            </div>
                            <div class="flex gap-1 ml-2">
                                <button @click="openCampaignModal(campaign)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Sửa">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteCampaign(campaign.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ campaign.description || 'Không có mô tả' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="bg-orange-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Giảm giá</p>
                                <p class="text-lg font-bold text-orange-600">{{ campaign.discount || '0%' }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Số lượng tối thiểu</p>
                                <p class="text-lg font-bold text-blue-600">{{ campaign.quantity || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <span>{{ formatDate(campaign.startDate) }} - {{ formatDate(campaign.endDate) }}</span>
                        </div>

                        <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                            <span>{{ campaign.products?.length || 0 }} sản phẩm áp dụng</span>
                        </div>

                        <div v-if="getBannerByCampaign(campaign.id)" class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-sm">image</span>
                            <span>Có banner liên kết</span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                            <button 
                                @click="toggleCampaignStatus(campaign)" 
                                class="text-xs px-4 py-2 rounded-full transition-colors font-medium"
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
            <div v-if="activeTab === 'vouchers'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="voucher in filteredVouchers" :key="voucher.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ voucher.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-xl text-gray-800 mt-2">{{ voucher.code }}</h3>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openVoucherModal(voucher)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deleteVoucher(voucher.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-3">{{ voucher.description }}</p>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">🎯</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ getTargetTypeLabel(voucher.target_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">💰</span>
                            <span class="text-orange-600 font-semibold">{{ getDiscountTypeLabel(voucher.discount_type) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📅</span>
                            <span class="text-sm">HSD: {{ formatDate(voucher.expiry) }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="text-gray-500">📊</span>
                            <span class="text-sm">{{ voucher.used }}/{{ voucher.limit }}</span>
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-orange-500 rounded-full transition-all"
                                :style="{ width: Math.min((voucher.used / voucher.limit) * 100, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(voucher)"
                            :class="voucher.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ voucher.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
                    </div>
                </div>

                <div v-if="filteredVouchers.length === 0" class="col-span-full text-center py-16 text-gray-500">
                    <span class="material-symbols-outlined text-6xl mb-4 block">local_offer</span>
                    <p class="text-lg font-medium">Không có mã giảm giá nào</p>
                    <button @click="openVoucherModal()" class="mt-3 text-orange-600 hover:underline font-medium">Thêm mã giảm giá</button>
                </div>
            </div>

            <!-- ==================== PRE-ORDERS LIST ==================== -->
            <div v-if="activeTab === 'preorder'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div v-for="preorder in filteredPreorders" :key="preorder.id" class="bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs px-2 py-1 rounded-full" :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ preorder.active ? 'Đang hoạt động' : 'Đã tắt' }}
                            </span>
                            <h3 class="font-bold text-lg text-gray-800 mt-2">⏳ {{ preorder.code }}</h3>
                            <p class="text-xs text-gray-500">Sản phẩm: {{ getProductName(preorder.product_id) }}</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openPreorderModal(preorder)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors text-sm">✏️</button>
                            <button @click="deletePreorder(preorder.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors text-sm">🗑️</button>
                        </div>
                    </div>

                    <div class="mt-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium">Đã đặt trước: {{ preorder.current_buyers || 0 }} lượt</span>
                            <span class="text-sm font-bold text-blue-600">
                                Giảm hiện tại: 
                                {{ preorder.tiers?.find(t => (preorder.current_buyers || 0) >= t.from && (preorder.current_buyers || 0) <= t.to)?.discount || 0 }}%
                            </span>
                        </div>

                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all"
                                :style="{ width: Math.min(((preorder.current_buyers || 0) / (preorder.tiers?.[preorder.tiers.length - 1]?.to || 100)) * 100, 100) + '%' }"
                            ></div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            <div v-for="tier in preorder.tiers" :key="tier.from" 
                                class="text-center p-2 bg-white rounded border"
                                :class="(preorder.current_buyers || 0) >= tier.from && (preorder.current_buyers || 0) <= tier.to ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            >
                                <div class="font-bold">{{ tier.discount }}%</div>
                                <div class="text-gray-500">#{{ tier.from }}-{{ tier.to }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                        <span>📅 {{ formatDate(preorder.start_date) }} - {{ formatDate(preorder.end_date) }}</span>
                        <span v-if="preorder.min_order > 0">💰 Đơn tối thiểu: {{ formatPrice(preorder.min_order) }}</span>
                    </div>

                    <div class="mt-3 pt-2 flex justify-end">
                        <button 
                            @click="toggleVoucher(preorder)"
                            :class="preorder.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            class="text-xs px-3 py-1 rounded-full transition-colors"
                        >
                            {{ preorder.active ? '✅ Kích hoạt' : '⛔ Đã tắt' }}
                        </button>
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
                                :min="campaignForm.startDate || today"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
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

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="campaignForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                @change="onBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedBanner && selectedBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedBanner.campaign_name || 'Banner #' + selectedBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="campaignForm.banner_id = null; campaignForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="campaignForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onBannerUrlInput"
                            >
                        </div>

                        <div v-if="campaignForm.banner && !selectedBanner" class="mt-2 relative">
                            <img 
                                :src="campaignForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="campaignForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="campaignForm.banner = ''; campaignForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
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
                            <p class="text-xs text-gray-500 mt-1">Phần trăm giảm giá (0-100%)</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm áp dụng</label>
                        
                        <div v-if="productVariants.filter(v => !v.product?.is_preorder).length > 0" class="mb-3">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">📦 Sản phẩm thường</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => !v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => !v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'normal-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                    >
                                    <label :for="'normal-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.filter(v => v.product?.is_preorder).length > 0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded">⏳ Sản phẩm Pre-order</span>
                                <span class="text-xs text-gray-400">({{ productVariants.filter(v => v.product?.is_preorder).length }} biến thể)</span>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                                <div v-for="variant in productVariants.filter(v => v.product?.is_preorder)" :key="variant.id" class="flex items-center gap-3 py-1.5 hover:bg-gray-50 px-2 rounded">
                                    <input 
                                        type="checkbox" 
                                        :id="'preorder-' + variant.id"
                                        :checked="isProductSelected(variant.id)"
                                        @change="toggleProduct(variant.id)"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"
                                    >
                                    <label :for="'preorder-' + variant.id" class="text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ variant.product?.name || variant.name || 'Sản phẩm' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ variant.color?.name || 'Không màu' }})</span>
                                        <span class="text-gray-400 text-xs ml-1">- {{ formatPrice(variant.price) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="productVariants.length === 0" class="border border-gray-300 rounded-lg p-6 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl block mb-2">inventory_2</span>
                            <p>Không có sản phẩm nào</p>
                            <p class="text-xs mt-1">Vui lòng thêm sản phẩm và biến thể trước khi tạo chiến dịch</p>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-2">Tổng: <span class="font-semibold">{{ campaignForm.products.length }}</span> sản phẩm được chọn</div>
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá trị giảm *</label>
                        <input 
                            v-model.number="voucherForm.discount_value" 
                            type="number" 
                            min="0"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="50000"
                        >
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
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Chọn Banner</label>
                        <div class="flex gap-3">
                            <select 
                                v-model="preorderForm.banner_id" 
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                                @change="onPreorderBannerSelect"
                            >
                                <option :value="null">-- Chọn banner từ danh sách --</option>
                                <option 
                                    v-for="banner in banners" 
                                    :key="banner.id" 
                                    :value="banner.id"
                                >
                                    {{ banner.campaign_name || 'Banner #' + banner.id }} 
                                    {{ banner.image ? '📷' : '🚫' }}
                                    {{ banner.status === 1 ? '✅' : '⛔' }}
                                </option>
                            </select>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm transition-colors"
                                title="Xóa chọn banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        
                        <div v-if="selectedPreorderBanner && selectedPreorderBanner.image" class="mt-2 p-2 border border-gray-200 rounded-lg flex items-center gap-3">
                            <img 
                                :src="selectedPreorderBanner.image" 
                                class="h-16 w-24 object-cover rounded border"
                                @error="selectedPreorderBanner.image = null"
                                alt="Banner preview"
                            >
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ selectedPreorderBanner.campaign_name || 'Banner #' + selectedPreorderBanner.id }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ selectedPreorderBanner.image }}</p>
                                <p class="text-xs text-gray-400">Trạng thái: {{ selectedPreorderBanner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}</p>
                            </div>
                            <button 
                                @click="preorderForm.banner_id = null; preorderForm.banner = ''" 
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1">Chọn banner đã tạo từ trang Quản lý Banner</p>
                        
                        <div class="mt-2">
                            <label class="text-xs text-gray-500">Hoặc nhập URL trực tiếp</label>
                            <input 
                                v-model="preorderForm.banner" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 mt-1"
                                placeholder="https://example.com/banner.jpg"
                                @input="onPreorderBannerUrlInput"
                            >
                        </div>

                        <div v-if="preorderForm.banner && !selectedPreorderBanner" class="mt-2 relative">
                            <img 
                                :src="preorderForm.banner" 
                                class="h-32 w-full object-cover rounded-lg border"
                                @error="preorderForm.banner = ''"
                                alt="Banner preview"
                            >
                            <button 
                                @click="preorderForm.banner = ''; preorderForm.banner_id = null" 
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                title="Xóa banner"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
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


## Assistant:
07/07/2026 07:43

Tôi sẽ tổng hợp và chỉnh sửa toàn bộ code cho bạn. Dưới đây là các file hoàn chỉnh:

## 1. Migration cho bảng banners

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
```

## 2. Migration sửa bảng campaigns (xóa trường banner)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Xóa trường banner cũ nếu có
            if (Schema::hasColumn('campaigns', 'banner')) {
                $table->dropColumn('banner');
            }
            
            // Thêm banner_id nếu chưa có
            if (!Schema::hasColumn('campaigns', 'banner_id')) {
                $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('banner')->nullable();
            $table->dropForeign(['banner_id']);
            $table->dropColumn('banner_id');
        });
    }
};
```

## 3. Model Banner

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'image', 
        'link', 
        'description',
        'status', 
        'order'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];
}
```

## 4. Model Campaign

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner_id',
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_products');
    }

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ với Banner
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    // Accessor để lấy URL banner
    public function getBannerUrlAttribute()
    {
        return $this->banner ? $this->banner->image : null;
    }

    // Accessor để lấy thông tin banner
    public function getBannerInfoAttribute()
    {
        if (!$this->banner) return null;
        return [
            'id' => $this->banner->id,
            'title' => $this->banner->title,
            'image' => $this->banner->image,
            'link' => $this->banner->link,
        ];
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('d/m/Y');
            $end = $this->end_time->format('d/m/Y');
            return "Chiến dịch {$start} - {$end}";
        }
        return 'Chiến dịch';
    }

    public function getStartDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getEndDateAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : null;
    }

    public function getDiscountAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->discount_percent . '%' : '0%';
    }

    public function getMinQuantityAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->quantity : 0;
    }

    public function getProductIdsAttribute()
    {
        return $this->productVariants->pluck('id')->toArray();
    }
}
```

## 5. Model Promotion

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'target_type',
        'discount_type',
        'discount_value',
        'min_order',
        'limit',
        'used',
        'expiry',
        'active',
        'description',
        'product_id',
        'tiers',
        'current_buyers',
        'start_date',
        'end_date',
        'campaign_id'
    ];

    protected $casts = [
        'tiers' => 'array',
        'active' => 'boolean',
        'discount_value' => 'decimal:0',
        'min_order' => 'decimal:0',
        'expiry' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

## 6. BannerController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::orderBy('order', 'asc')->get()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? 'Banner ' . now()->format('d/m/Y'),
                'link' => $validated['link'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? true,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
            $totalBanners = Banner::count();
            $newOrder = $request->input('order', $totalBanners);
            
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            if ($newOrder < $totalBanners) {
                Banner::where('order', '>=', $newOrder)->increment('order');
            }

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            Log::info('Banner created successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Banner update request:', ['id' => $id, 'data' => $request->all()]);

            $banner = Banner::findOrFail($id);
            
            $rules = [
                'title' => 'nullable|string|max:255',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? $banner->title,
                'link' => $validated['link'] ?? $banner->link,
                'description' => $validated['description'] ?? $banner->description,
                'status' => $validated['status'] ?? $banner->status,
            ];
            
            // Xử lý ảnh
            if ($request->hasFile('image_file')) {
                try {
                    if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                    }
                    
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $data['image'] = $request->image;
            }

            // Xử lý thay đổi order
            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count();

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            Log::info('Banner updated successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count();
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
```

## 7. PromotionController (phần index và campaign methods)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns - load banner
            $campaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color', 'banner'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'banner' => $campaign->banner ? $campaign->banner->image : '',
                        'banner_id' => $campaign->banner_id ?? null,
                        'banner_title' => $campaign->banner ? $campaign->banner->title : null,
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'productVariants' => $campaign->productVariants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->product->name ?? 'Sản phẩm',
                                'color' => $variant->color->name ?? 'Không màu',
                                'price' => $variant->price ?? 0,
                            ];
                        }),
                    ];
                });

            // Lấy promotions (vouchers và preorder)
            $promotions = Promotion::with(['campaign', 'product'])->latest()->get();

            // Lấy tất cả banners - KHÔNG liên kết với campaign
            $banners = Banner::orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'Banner #' . $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'description' => $banner->description,
                    'status' => $banner->status ? 1 : 0,
                    'order' => $banner->order,
                ];
            });

            // Lấy tất cả products (cho voucher)
            $products = Product::with(['variants.color', 'category', 'brand'])->latest()->get();

            // Lấy product variants (cho campaign)
            $productVariants = ProductVariant::with(['product', 'color'])->latest()->get();

            // Lấy chỉ sản phẩm pre-order (cho pre-order)
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get();

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'banner_id' => $validated['banner_id'] ?? null,
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            if (!empty($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'banner_id' => 'nullable|exists:banners,id',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'banner_id' => $validated['banner_id'] ?? $campaign->banner_id,
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteCampaign($id)
    {
        try {
            DB::beginTransaction();
            
            $campaign = Campaign::findOrFail($id);
            $campaign->configs()->delete();
            $campaign->productVariants()->detach();
            $campaign->delete();

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaignStatus(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $request->validate(['status' => 'required|in:scheduled,active,ended']);
            $campaign->update(['status' => $request->status]);
            
            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== VOUCHER METHODS ====================
    // ... (giữ nguyên các method voucher và preorder của bạn)
}
```

## 8. Routes

```php
<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/banners/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.status');
    Route::patch('/banners/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.order');

    // Promotion routes
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions/campaign', [PromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
    Route::put('/promotions/campaign/{id}', [PromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
    Route::delete('/promotions/campaign/{id}', [PromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
    Route::put('/promotions/campaign/{id}/status', [PromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');

    Route::post('/promotions/voucher', [PromotionController::class, 'storePromotion'])->name('promotions.voucher.store');
    Route::put('/promotions/voucher/{id}', [PromotionController::class, 'updatePromotion'])->name('promotions.voucher.update');
    Route::delete('/promotions/voucher/{id}', [PromotionController::class, 'deletePromotion'])->name('promotions.voucher.delete');
    Route::put('/promotions/voucher/{id}/toggle', [PromotionController::class, 'togglePromotion'])->name('promotions.voucher.toggle');

    Route::post('/promotions/preorder', [PromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
    Route::put('/promotions/preorder/{id}', [PromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
    Route::delete('/promotions/preorder/{id}', [PromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
});
```

## 9. Chạy migration

```bash
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## Tóm tắt thay đổi:

1. **Banner**: Chỉ lưu thông tin ảnh, không liên kết với campaign
2. **Campaign**: Chỉ lưu `banner_id` để tham chiếu đến banner
3. **Không còn trường `banner` trong campaign** - chỉ dùng `banner_id`
4. **Tách biệt hoàn toàn** giữa Banner và Campaign
5. **Khi tạo campaign**, người dùng chọn banner từ danh sách có sẵn


## User:
07/07/2026 07:52

D:\LuanVanTotNghiep> php artisan migrate:fresh       

  Dropping all tables ................................................................................................... 444.26ms DONE

   INFO  Preparing database.  

  Creating migration table ............................................................................................... 29.53ms DONE

   INFO  Running migrations.  

  0001_01_01_000000_create_users_table ................................................................................... 75.58ms DONE
  0001_01_01_000001_create_cache_table ................................................................................... 43.61ms DONE
  0001_01_01_000002_create_jobs_table .................................................................................... 93.43ms DONE
  2026_06_04_163751_add_columns_to_users_table ........................................................................... 78.18ms DONE
  2026_06_04_164515_create_categories_table .............................................................................. 27.36ms DONE
  2026_06_04_164531_create_brands_table ................................................................................... 5.82ms DONE
  2026_06_04_164541_create_products_table ................................................................................ 76.02ms DONE
  2026_06_04_164616_create_colors_table ................................................................................... 5.03ms DONE
  2026_06_04_164625_create_product_variants_table ........................................................................ 49.88ms DONE
  2026_06_04_164639_create_discounts_table ................................................................................ 9.97ms DONE
  2026_06_04_164649_create_campaigns_table ............................................................................... 19.56ms FAIL

   Illuminate\Database\QueryException 

  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'web_ba_lo.campaigns' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: web_ba_lo, SQL: alter table `campaigns` add `banner_id` bigint unsigned null)

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:841
    837▕             $exceptionType = ($isUniqueConstraintError = $this->isUniqueConstraintError($e))
    838▕                 ? UniqueConstraintViolationException::class
    839▕                 : QueryException::class;
    840▕ 
  ➜ 841▕             $exception = new $exceptionType(
    842▕                 $this->getNameWithReadWriteType(),
    843▕                 $query,
    844▕                 $this->prepareBindings($bindings),
    845▕                 $e,

  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:587
      PDOException::("SQLSTATE[42S02]: Base table or view not found: 1146 Table 'web_ba_lo.campaigns' doesn't exist")

  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:587
      PDOStatement::execute()

PS D:\LuanVanTotNghiep>


## Assistant:
07/07/2026 07:52

Lỗi xảy ra vì migration `2026_06_04_164649_create_campaigns_table` đang cố thêm `banner_id` vào bảng `campaigns` nhưng bảng này chưa được tạo. Bạn cần tạo migration tạo bảng `campaigns` trước, sau đó mới tạo migration thêm cột.

## Cách fix:

### 1. Xóa migration thêm cột vào campaigns (nếu có)

Xóa file migration có tên `2026_06_04_164649_create_campaigns_table` hoặc sửa lại nội dung.

### 2. Tạo migration tạo bảng campaigns đúng cách

```bash
php artisan make:migration create_campaigns_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->default('seasonal');
            $table->text('description')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('status')->default('scheduled');
            $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
            $table->integer('priority')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
};
```

### 3. Tạo migration cho bảng campaign_configs

```bash
php artisan make:migration create_campaign_configs_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaign_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_configs');
    }
};
```

### 4. Tạo migration cho bảng campaign_products (quan hệ nhiều-nhiều)

```bash
php artisan make:migration create_campaign_products_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_products');
    }
};
```

### 5. Tạo migration cho bảng promotions

```bash
php artisan make:migration create_promotions_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('voucher'); // voucher, preorder_tier
            $table->string('target_type')->default('all'); // retail, wholesale, preorder, all
            $table->string('discount_type')->nullable(); // fixed, percent, freeship
            $table->decimal('discount_value', 15, 0)->default(0);
            $table->decimal('min_order', 15, 0)->default(0);
            $table->integer('limit')->default(100);
            $table->integer('used')->default(0);
            $table->timestamp('expiry')->nullable();
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->json('tiers')->nullable();
            $table->integer('current_buyers')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotions');
    }
};
```

### 6. Xóa migration đang gây lỗi

Xóa file `2026_06_04_164649_create_campaigns_table` nếu nó đang cố thêm cột vào bảng chưa tồn tại.

### 7. Chạy lại migration theo thứ tự đúng

```bash
# Xóa tất cả bảng (nếu cần)
php artisan migrate:fresh

# Hoặc chạy từ đầu
php artisan migrate
```

### 8. Nếu vẫn lỗi, hãy kiểm tra thứ tự migration

```bash
# Xem danh sách migration đã chạy
php artisan migrate:status

# Nếu có migration lỗi, rollback và chạy lại
php artisan migrate:rollback
php artisan migrate
```

### 9. Đảm bảo thứ tự migration đúng:

1. `create_banners_table` (tạo bảng banners trước)
2. `create_campaigns_table` (tạo bảng campaigns sau)
3. `create_campaign_configs_table`
4. `create_campaign_products_table`
5. `create_promotions_table`

Nếu migration vẫn lỗi, bạn có thể xóa toàn bộ file migration cũ và tạo lại từ đầu bằng cách:

```bash
# Xóa tất cả migration cũ
rm database/migrations/*.php

# Tạo migration mới theo thứ tự
php artisan make:migration create_banners_table
php artisan make:migration create_campaigns_table
php artisan make:migration create_campaign_configs_table
php artisan make:migration create_campaign_products_table
php artisan make:migration create_promotions_table

# Chạy migration
php artisan migrate
```


## User:
07/07/2026 07:55

PS D:\LuanVanTotNghiep> php artisan migrate

   INFO  Running migrations.  

  2026_06_04_164649_create_campaigns_table ............................................................................... 36.36ms DONE
  2026_06_04_164658_create_campaign_products_table ....................................................................... 45.19ms DONE
  2026_06_04_164706_create_campaign_configs_table ........................................................................ 24.90ms DONE
  2026_06_04_164719_create_orders_table .................................................................................. 65.73ms DONE
  2026_06_04_164745_create_order_details_table ........................................................................... 38.77ms DONE
  2026_06_04_164755_create_payments_table ................................................................................ 41.13ms DONE
  2026_06_04_164807_create_reviews_table ................................................................................. 53.94ms DONE
  2026_06_04_164817_create_news_table .................................................................................... 52.33ms DONE
  2026_06_04_164829_create_banners_table .................................................................................. 5.57ms DONE
  2026_06_04_164837_create_quote_requests_table .......................................................................... 28.10ms DONE
  2026_06_04_164848_create_quote_request_details_table ................................................................... 38.76ms DONE
  2026_06_04_164901_create_logo_print_requests_table ..................................................................... 23.44ms DONE
  2026_06_04_164918_create_chatbot_messages_table ........................................................................ 26.28ms DONE
  2026_06_07_130856_update_orders_column_type ............................................................................ 41.40ms DONE
  2026_06_07_131700_rename_type_to_order_code_in_orders_table ............................................................. 8.74ms DONE
  2026_06_08_143322_change_image_column_to_text_in_categories_table ...................................................... 18.66ms DONE
  2026_06_09_024229_add_slug_to_brands_table ............................................................................ 164.98ms DONE
  2026_06_09_084119_rename_color_column_to_name_in_colors_table ........................................................... 8.84ms DONE
  2026_06_09_091352_drop_sizes_table ...................................................................................... 2.44ms DONE
  2026_06_09_150113_create_settings_table ................................................................................ 33.10ms DONE
  2026_06_10_163246_add_code_to_colors_table ............................................................................. 24.03ms DONE
  2026_06_11_fix_color_names .............................................................................................. 1.01ms DONE
  2026_06_20_111820_add_customer_fields_to_orders_table .................................................................. 41.17ms DONE
  2026_06_27_020426_update_image_url_to_json_in_products_table ........................................................... 18.86ms DONE
  2026_07_03_135209_add_order_to_banners_table ............................................................................ 6.75ms FAIL

   Illuminate\Database\QueryException 

  SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'order' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: web_ba_lo, SQL: alter table `banners` add `order` int not null default '0' after `status`)

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:841
    837▕             $exceptionType = ($isUniqueConstraintError = $this->isUniqueConstraintError($e))
    838▕                 ? UniqueConstraintViolationException::class
    839▕                 : QueryException::class;
    840▕ 
  ➜ 841▕             $exception = new $exceptionType(
    842▕                 $this->getNameWithReadWriteType(),
    843▕                 $query,
    844▕                 $this->prepareBindings($bindings),
    845▕                 $e,

  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:587
      PDOException::("SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'order'")

  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:587
      PDOStatement::execute()

PS D:\LuanVanTotNghiep>


## Assistant:
07/07/2026 07:55

Lỗi xảy ra vì cột `order` đã tồn tại trong bảng `banners` từ migration tạo bảng, nhưng bạn lại có migration khác cố thêm cột này. Cần xóa hoặc sửa migration `2026_07_03_135209_add_order_to_banners_table`.

## Cách fix:

### 1. Xóa migration thừa

```bash
# Xóa file migration thừa
rm database/migrations/2026_07_03_135209_add_order_to_banners_table.php
```

### 2. Kiểm tra migration tạo bảng banners đã có cột order chưa

Xem file `2026_06_04_164829_create_banners_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(0); // Đã có cột order ở đây
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
```

### 3. Nếu bạn muốn giữ migration thêm cột, hãy sửa lại để kiểm tra cột đã tồn tại

Sửa file `2026_07_03_135209_add_order_to_banners_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            // Kiểm tra cột order đã tồn tại chưa
            if (!Schema::hasColumn('banners', 'order')) {
                $table->integer('order')->default(0)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
};
```

### 4. Hoặc đơn giản nhất là xóa migration thừa và chạy lại

```bash
# Xóa migration thừa
rm database/migrations/2026_07_03_135209_add_order_to_banners_table.php

# Rollback migration banners
php artisan migrate:rollback --path=database/migrations/2026_06_04_164829_create_banners_table.php

# Chạy lại migration
php artisan migrate
```

### 5. Nếu bạn muốn chạy tất cả từ đầu:

```bash
# Xóa tất cả bảng
php artisan migrate:fresh

# Hoặc rollback và migrate lại
php artisan migrate:rollback
php artisan migrate
```

### 6. Cách nhanh nhất: Sửa migration thêm cột thành kiểm tra

Sửa nội dung file `2026_07_03_135209_add_order_to_banners_table.php` thành:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Bỏ qua vì cột order đã có trong migration tạo bảng
        // Chỉ chạy nếu chưa có cột order
        if (!Schema::hasColumn('banners', 'order')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->integer('order')->default(0)->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('banners', 'order')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};
```

### 7. Chạy lại migration

```bash
# Nếu đã có lỗi, rollback và chạy lại
php artisan migrate:rollback
php artisan migrate

# Hoặc nếu đã rollback hết
php artisan migrate
```

### 8. Kiểm tra kết quả

```bash
# Kiểm tra các bảng đã tạo
php artisan db:table --table=banners
php artisan db:table --table=campaigns
```

Sau khi fix, bạn sẽ có cấu trúc bảng banners đúng với các cột:
- `id`
- `title`
- `image`
- `link`
- `description`
- `status`
- `order`
- `created_at`
- `updated_at`



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
