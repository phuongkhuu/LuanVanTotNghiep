<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Props từ controller
const props = defineProps({
    initialRequests: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    pagination: {
        type: Object,
        default: () => ({ total: 0, per_page: 5, current_page: 1, last_page: 1 })
    }
});

// State
const requests = ref(props.initialRequests);
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

// Pagination
const currentPage = ref(1);
const perPage = ref(5);

// Tất cả các trạng thái (dùng để tạo option)
const allStatuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];

// Filter options (hiển thị trên tab filter)
const filters = [
    { val: 'all', label: 'Tất cả' },
    { val: 'pending', label: 'Chờ duyệt' },
    { val: 'approved', label: 'Đã duyệt' },
    { val: 'rejected', label: 'Từ chối' },
    { val: 'processing', label: 'Đang SX' },
    { val: 'completed', label: 'Hoàn thành' }
];

// Modal states
const showDetailModal = ref(false);
const showQuoteModal = ref(false);
const selectedRequest = ref(null);
const isUpdating = ref(false);
const feedback = ref('');

// ===== REJECT REASON MODAL =====
const showRejectModal = ref(false);
const rejectRequest = ref(null);
const rejectReason = ref('');

// ===== LUỒNG TRẠNG THÁI CHO PHÉP =====
const allowedTransitions = {
    pending: ['approved', 'rejected'],
    approved: ['processing'],
    processing: ['completed'],
    rejected: [],
    completed: []
};

// Quote form
const quoteForm = ref({
    customerName: '',
    email: '',
    phone: '',
    product: '',
    quantity: 1,
    designDescription: '',
    estimatedPrice: 0,
    estimatedTime: ''
});

// Computed
const filteredRequests = computed(() => requests.value);

const paginatedRequests = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredRequests.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredRequests.value.length / perPage.value);
});

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

// Watch filter changes
watch([search, statusFilter], () => {
    currentPage.value = 1;
    fetchRequests();
});

// Fetch data from server
const fetchRequests = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (statusFilter.value !== 'all') params.append('status', statusFilter.value);
    params.append('page', currentPage.value);
    
    router.get('/admin/customize?' + params.toString(), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            requests.value = page.props.initialRequests || [];
        }
    });
};

// Get count by status
const getCount = (statusValue) => {
    if (statusValue === 'all') {
        return requests.value.length;
    }
    return requests.value.filter(r => r.status === statusValue).length;
};

// Status badge class
const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-emerald-100 text-emerald-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-600';
};

// Status label
const getStatusLabel = (status) => {
    const labels = {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        rejected: 'Từ chối',
        processing: 'Đang SX',
        completed: 'Hoàn thành'
    };
    return labels[status] || status;
};

// ===== KIỂM TRA TRẠNG THÁI CHUYỂN ĐỔI HỢP LỆ =====
const isTransitionAllowed = (currentStatus, newStatus) => {
    if (currentStatus === newStatus) return true;
    const allowed = allowedTransitions[currentStatus] || [];
    return allowed.includes(newStatus);
};

// ===== CẬP NHẬT TRẠNG THÁI =====
const updateStatus = async (request) => {
    const oldStatus = request._oldStatus || request.status;
    const newStatus = request.status;

    // Nếu không thay đổi
    if (oldStatus === newStatus) return;

    // Kiểm tra luồng (vẫn giữ để an toàn)
    if (!isTransitionAllowed(oldStatus, newStatus)) {
        // Reset về cũ
        request.status = oldStatus;
        return;
    }

    // Nếu chọn "Từ chối" -> hiện modal
    if (newStatus === 'rejected') {
        rejectRequest.value = request;
        rejectReason.value = '';
        showRejectModal.value = true;
        request.status = oldStatus; // reset lại
        return;
    }

    // Xác nhận các trạng thái khác
    if (!confirm(`Bạn có chắc muốn thay đổi trạng thái thành "${getStatusLabel(newStatus)}"?`)) {
        request.status = oldStatus;
        return;
    }

    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${request.id}/status`, {
            status: newStatus,
            feedback: feedback.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Cập nhật trạng thái thành công!');
                feedback.value = '';
                request._oldStatus = newStatus;
                fetchRequests();
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
                request.status = oldStatus;
                fetchRequests();
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
        request.status = oldStatus;
    } finally {
        isUpdating.value = false;
    }
};

// ===== XÁC NHẬN TỪ CHỐI VỚI LÝ DO =====
const confirmReject = async () => {
    if (!rejectRequest.value) return;
    
    if (!rejectReason.value.trim()) {
        alert('Vui lòng nhập lý do từ chối');
        return;
    }
    
    const request = rejectRequest.value;
    const oldStatus = request._oldStatus || request.status;
    const newStatus = 'rejected';
    
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${request.id}/status`, {
            status: newStatus,
            feedback: rejectReason.value.trim()
        }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Đã từ chối yêu cầu thành công!');
                showRejectModal.value = false;
                rejectReason.value = '';
                request._oldStatus = newStatus;
                rejectRequest.value = null;
                fetchRequests();
            },
            onError: (errors) => {
                console.error('Lỗi từ chối:', errors);
                alert('Có lỗi xảy ra khi từ chối yêu cầu');
                request.status = oldStatus;
                fetchRequests();
            }
        });
    } catch (error) {
        console.error('Từ chối thất bại:', error);
        alert('Có lỗi xảy ra khi từ chối yêu cầu');
        request.status = oldStatus;
    } finally {
        isUpdating.value = false;
    }
};

// ===== HỦY TỪ CHỐI =====
const cancelReject = () => {
    showRejectModal.value = false;
    rejectReason.value = '';
    if (rejectRequest.value) {
        rejectRequest.value.status = rejectRequest.value._oldStatus || rejectRequest.value.status;
        rejectRequest.value = null;
    }
};

// View detail
const viewDetail = (request) => {
    selectedRequest.value = { ...request };
    feedback.value = '';
    showDetailModal.value = true;
};

// Approve request (chỉ gọi từ modal, đã kiểm tra luồng trong confirm)
const approveRequest = async () => {
    if (!selectedRequest.value) return;
    
    const request = selectedRequest.value;
    const oldStatus = request.status;
    const newStatus = 'approved';
    
    if (!isTransitionAllowed(oldStatus, newStatus)) {
        alert(`Không thể duyệt từ trạng thái "${getStatusLabel(oldStatus)}".`);
        return;
    }
    
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${request.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                request.status = newStatus;
                request._oldStatus = newStatus;
                showDetailModal.value = false;
                alert('Đã duyệt yêu cầu thành công!');
                fetchRequests();
            },
            onError: (errors) => {
                console.error('Lỗi duyệt:', errors);
                alert('Có lỗi xảy ra khi duyệt yêu cầu');
            }
        });
    } catch (error) {
        console.error('Duyệt thất bại:', error);
        alert('Có lỗi xảy ra khi duyệt yêu cầu');
    } finally {
        isUpdating.value = false;
    }
};

// Open quote modal
const openQuoteModal = (request = null) => {
    if (request) {
        quoteForm.value = {
            customerName: request.customer || '',
            email: request.email || '',
            phone: request.phone || '',
            product: request.product || '',
            quantity: request.quantity || 1,
            designDescription: request.note || '',
            estimatedPrice: 0,
            estimatedTime: ''
        };
    } else {
        quoteForm.value = {
            customerName: '',
            email: '',
            phone: '',
            product: '',
            quantity: 1,
            designDescription: '',
            estimatedPrice: 0,
            estimatedTime: ''
        };
    }
    showQuoteModal.value = true;
};

// Send quote
const sendQuote = async () => {
    if (!quoteForm.value.customerName || !quoteForm.value.email) {
        alert('Vui lòng nhập đầy đủ thông tin khách hàng');
        return;
    }
    
    if (quoteForm.value.estimatedPrice <= 0) {
        alert('Vui lòng nhập giá dự kiến');
        return;
    }
    
    isUpdating.value = true;
    try {
        await router.post('/admin/customize/send-quote', quoteForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Đã gửi báo giá thành công!');
                showQuoteModal.value = false;
            },
            onError: (errors) => {
                console.error('Lỗi gửi báo giá:', errors);
                alert('Có lỗi xảy ra khi gửi báo giá');
            }
        });
    } catch (error) {
        console.error('Gửi báo giá thất bại:', error);
        alert('Có lỗi xảy ra khi gửi báo giá');
    } finally {
        isUpdating.value = false;
    }
};

// Download design file
const downloadFile = (fileName) => {
    if (!fileName) {
        alert('Không có file đính kèm');
        return;
    }
    window.open('/storage/' + fileName, '_blank');
};

// Format currency
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

// ====== HÀM HỖ TRỢ PREVIEW ẢNH ======
const isImageFile = (fileName) => {
    if (!fileName) return false;
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'];
    const ext = fileName.split('.').pop().toLowerCase();
    return imageExtensions.includes(ext);
};

const getDesignFileUrl = (fileName) => {
    if (!fileName) return '';
    return '/storage/' + fileName;
};

// ====== GÁN _oldStatus KHI NHẬN DỮ LIỆU ======
watch(requests, (newVal) => {
    if (Array.isArray(newVal)) {
        newVal.forEach(req => {
            if (!req._oldStatus) {
                req._oldStatus = req.status;
            }
        });
    }
}, { immediate: true, deep: true });

// Initial fetch
if (requests.value.length === 0) {
    fetchRequests();
}
</script>

<template>
    <Head title="Quản lý tùy chỉnh - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Yêu cầu tùy chỉnh</h1>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
                <button 
                    v-for="filter in filters" 
                    :key="filter.val" 
                    @click="statusFilter = filter.val" 
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="statusFilter === filter.val ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ filter.label }} 
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ getCount(filter.val) }}</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên, email, SĐT, sản phẩm hoặc vị trí in..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Danh sách yêu cầu -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">LOẠI KH</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">SẢN PHẨM</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">VỊ TRÍ IN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold whitespace-nowrap">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="request in paginatedRequests" 
                                :key="request.id" 
                                class="border-b border-gray-200 hover:bg-orange-50 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ request.customer }}</p>
                                        <p class="text-xs text-gray-500">{{ request.email }}</p>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span 
                                        class="text-xs px-2 py-1 rounded-full whitespace-nowrap"
                                        :class="request.customerType === 'business' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                                    >
                                        {{ request.customerType === 'business' ? 'Doanh nghiệp' : 'Khách lẻ' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ request.product }}</td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ request.position }} - {{ request.size }}
                                    <span v-if="request.quantity > 1" class="text-xs text-gray-400 ml-1">(x{{ request.quantity }})</span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ request.date }}</td>
                                <td class="py-3 px-4">
                                    <!-- ===== DROPDOWN VỚI OPTION DISABLED ===== -->
                                    <select 
                                        v-model="request.status" 
                                        @change="updateStatus(request)"
                                        class="text-xs px-2 py-1 rounded-full font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
                                        :class="getStatusClass(request.status)"
                                        :disabled="isUpdating"
                                    >
                                        <option 
                                            v-for="status in allStatuses" 
                                            :key="status"
                                            :value="status"
                                            :disabled="!isTransitionAllowed(request._oldStatus || request.status, status)"
                                        >
                                            {{ getStatusLabel(status) }}
                                        </option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button 
                                        @click="viewDetail(request)" 
                                        class="px-3 py-1.5 text-xs text-orange-600 hover:bg-orange-100 rounded-lg transition-colors font-medium"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                    <button 
                                        v-if="request.designFile"
                                        @click="downloadFile(request.designFile)" 
                                        class="px-3 py-1.5 text-xs text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors font-medium"
                                        title="Tải file thiết kế"
                                    >
                                        Tải file
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="paginatedRequests.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy yêu cầu tùy chỉnh nào' : 'Không có yêu cầu tùy chỉnh nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedRequests.length }} / {{ filteredRequests.length }} yêu cầu
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

        <!-- Modal chi tiết yêu cầu -->
        <div 
            v-if="showDetailModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showDetailModal = false"
        >
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Chi tiết yêu cầu tùy chỉnh</h3>
                    <button 
                        @click="showDetailModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Thông tin khách hàng -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Khách hàng</p>
                            <p class="font-medium text-gray-800">{{ selectedRequest?.customer }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-gray-600">{{ selectedRequest?.email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Số điện thoại</p>
                            <p class="text-gray-600">{{ selectedRequest?.phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Loại khách hàng</p>
                            <span 
                                class="text-xs px-2 py-1 rounded-full inline-block"
                                :class="selectedRequest?.customerType === 'business' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                            >
                                {{ selectedRequest?.customerType === 'business' ? '🏢 Doanh nghiệp' : '👤 Khách lẻ' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Thông tin sản phẩm -->
                    <div class="border-t border-gray-200 pt-3">
                        <h4 class="font-semibold text-gray-700 mb-2">Thông tin sản phẩm</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Sản phẩm</p>
                                <p class="text-gray-800">{{ selectedRequest?.product }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Số lượng</p>
                                <p class="text-gray-800">{{ selectedRequest?.quantity || 1 }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Vị trí in</p>
                                <p class="text-gray-800">{{ selectedRequest?.position }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kích thước</p>
                                <p class="text-gray-800">{{ selectedRequest?.size }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ghi chú -->
                    <div class="border-t border-gray-200 pt-3">
                        <h4 class="font-semibold text-gray-700 mb-2">Ghi chú khách hàng</h4>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">{{ selectedRequest?.note || 'Không có ghi chú thêm' }}</p>
                        </div>
                    </div>
                    
                    <!-- ===== FILE THIẾT KẾ - CÓ PREVIEW ẢNH ===== -->
                    <div v-if="selectedRequest?.designFile" class="border-t border-gray-200 pt-3">
                        <h4 class="font-semibold text-gray-700 mb-2">File thiết kế</h4>
                        
                        <!-- Preview ảnh nếu là file ảnh -->
                        <div v-if="isImageFile(selectedRequest.designFile)" class="mb-3">
                            <img 
                                :src="getDesignFileUrl(selectedRequest.designFile)" 
                                alt="Design preview"
                                class="max-w-full max-h-64 rounded border object-contain"
                                @error="(e) => e.target.style.display = 'none'"
                            />
                        </div>
                        
                        <!-- Đường dẫn tải file (luôn hiển thị) -->
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">attach_file</span>
                            <a href="#" @click.prevent="downloadFile(selectedRequest.designFile)" class="text-orange-600 hover:underline">
                                {{ selectedRequest.designFile }}
                            </a>
                        </div>
                    </div>
                    <!-- ===== KẾT THÚC FILE THIẾT KẾ ===== -->
                    
                    <!-- Trạng thái -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-xs text-gray-500">Trạng thái hiện tại</p>
                        <span class="inline-block text-xs px-2 py-1 rounded-full mt-1" :class="getStatusClass(selectedRequest?.status)">
                            {{ getStatusLabel(selectedRequest?.status) }}
                        </span>
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
                        v-if="selectedRequest?.status === 'pending'"
                        @click="approveRequest" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                        :disabled="isUpdating"
                    >
                        {{ isUpdating ? 'Đang xử lý...' : 'Duyệt yêu cầu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal tạo báo giá -->
        <div 
            v-if="showQuoteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showQuoteModal = false"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Tạo báo giá cho khách hàng</h3>
                    <button 
                        @click="showQuoteModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Tên khách hàng</label>
                        <input 
                            v-model="quoteForm.customerName" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập tên khách hàng"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Email</label>
                        <input 
                            v-model="quoteForm.email" 
                            type="email" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="customer@example.com"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Số điện thoại</label>
                        <input 
                            v-model="quoteForm.phone" 
                            type="tel" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="0901234567"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Sản phẩm</label>
                        <input 
                            v-model="quoteForm.product" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Tên sản phẩm"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Số lượng</label>
                        <input 
                            v-model="quoteForm.quantity" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Số lượng"
                            min="1"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mô tả yêu cầu thiết kế</label>
                        <textarea 
                            v-model="quoteForm.designDescription" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Mô tả chi tiết yêu cầu in ấn, logo, vị trí..."
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá dự kiến (VNĐ)</label>
                        <input 
                            v-model="quoteForm.estimatedPrice" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Giá dự kiến"
                            min="0"
                        >
                    </div>
                    
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Thời gian dự kiến</label>
                        <input 
                            v-model="quoteForm.estimatedTime" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="VD: 7-10 ngày làm việc"
                        >
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="showQuoteModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="sendQuote" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                        :disabled="isUpdating"
                    >
                        {{ isUpdating ? 'Đang gửi...' : 'Gửi báo giá' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== MODAL NHẬP LÝ DO TỪ CHỐI ===== -->
        <div 
            v-if="showRejectModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="cancelReject"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Từ chối yêu cầu</h3>
                    <button 
                        @click="cancelReject" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Lý do từ chối <span class="text-red-500">*</span></label>
                        <textarea 
                            v-model="rejectReason" 
                            rows="4" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập lý do từ chối yêu cầu này..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">Lý do sẽ được gửi đến email của khách hàng.</p>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                        <span class="material-symbols-outlined align-middle text-red-500">warning</span>
                        Hành động này sẽ gửi email thông báo từ chối đến khách hàng.
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="cancelReject" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="confirmReject" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        :disabled="!rejectReason.trim() || isUpdating"
                    >
                        {{ isUpdating ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
</style>