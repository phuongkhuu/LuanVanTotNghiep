<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    reviews: { type: Array, default: () => [] },
    pagination: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] }, // ⬅️ đổi từ products
    filters: { type: Object, default: () => ({}) }
});

// Search and filters
const search = ref(props.filters?.search || '');
const filterCategory = ref(props.filters?.category_id || ''); // ⬅️ đổi tên
const filterRating = ref(props.filters?.rating || '');
const filterStatus = ref(props.filters?.status || '');

// Pagination
const currentPage = ref(1);
const perPage = ref(5);

// Data
const reviewsList = ref(Array.isArray(props.reviews) ? props.reviews : []);
const paginationData = ref(props.pagination || {});
const categoriesList = ref(Array.isArray(props.categories) ? props.categories : []); // ⬅️ đổi tên

// Loading state
const isLoading = ref(false);
const isDeleting = ref(false);
const selectedReview = ref(null);
const showDeleteModal = ref(false);

// Phân trang client-side
const paginatedReviews = computed(() => {
    const list = Array.isArray(reviewsList.value) ? reviewsList.value : [];
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return list.slice(start, end);
});

const totalPages = computed(() => {
    const total = Array.isArray(reviewsList.value) ? reviewsList.value.length : 0;
    return Math.ceil(total / perPage.value) || 1;
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

// Reset về trang 1 khi filter thay đổi và gọi API
watch([search, filterCategory, filterRating, filterStatus], () => {
    currentPage.value = 1;
    applyFilters();
});

// Hàm format ngày
const formatDate = (date) => {
    if (!date) return '---';
    const d = new Date(date);
    return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

// Render sao
const renderStars = (rating) => {
    return '⭐'.repeat(rating) + '☆'.repeat(5 - rating);
};

// Áp dụng bộ lọc (gọi server)
const applyFilters = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (filterCategory.value) params.append('category_id', filterCategory.value); // ⬅️ đổi key
    if (filterRating.value) params.append('rating', filterRating.value);
    if (filterStatus.value) params.append('status', filterStatus.value);
    params.append('page', currentPage.value);
    
    isLoading.value = true;
    router.get('/admin/reviews?' + params.toString(), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            reviewsList.value = Array.isArray(page.props.reviews) ? page.props.reviews : [];
            paginationData.value = page.props.pagination || {};
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        }
    });
};

// Reset bộ lọc
const resetFilters = () => {
    search.value = '';
    filterCategory.value = '';
    filterRating.value = '';
    filterStatus.value = '';
    currentPage.value = 1;
    applyFilters();
};

// Chuyển trang
const goToPage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    if (props.filters && Object.keys(props.filters).length > 0) {
        applyFilters();
    }
};

// Xóa đánh giá
const confirmDelete = (review) => {
    selectedReview.value = review;
    showDeleteModal.value = true;
};

const deleteReview = async () => {
    if (!selectedReview.value) return;
    if (isDeleting.value) return;
    
    isDeleting.value = true;
    try {
        await router.delete(`/admin/reviews/${selectedReview.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                const index = reviewsList.value.findIndex(r => r.id === selectedReview.value.id);
                if (index !== -1) {
                    reviewsList.value.splice(index, 1);
                }
                if (paginationData.value.total) {
                    paginationData.value.total -= 1;
                }
                showDeleteModal.value = false;
                selectedReview.value = null;
                if (reviewsList.value.length === 0 && currentPage.value > 1) {
                    currentPage.value -= 1;
                    applyFilters();
                }
            },
            onError: (errors) => {
                alert('Xóa thất bại: ' + (errors.message || 'Lỗi không xác định'));
            }
        });
    } catch (error) {
        console.error('Lỗi xóa đánh giá:', error);
        alert('Có lỗi xảy ra khi xóa');
    } finally {
        isDeleting.value = false;
    }
};

// Đóng modal
const closeModal = () => {
    showDeleteModal.value = false;
    selectedReview.value = null;
};

// Handle overlay click
const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal();
    }
};

// Khởi tạo
onMounted(() => {
    if (reviewsList.value.length === 0 && props.filters) {
        applyFilters();
    }
});
</script>

<template>
    <Head title="Quản lý đánh giá - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý đánh giá</h1>
                    <p class="text-sm text-gray-500 mt-1">Quản lý tất cả đánh giá từ khách hàng</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Tổng: {{ paginationData.total || reviewsList.length }}</span>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        @keyup.enter="applyFilters"
                        type="text" 
                        placeholder="Tìm theo tên sản phẩm hoặc người dùng..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-4">
                <!-- ⬇️ Dropdown danh mục thay vì sản phẩm -->
                <div class="w-full sm:w-auto relative">
                    <select 
                        v-model="filterCategory" 
                        @change="applyFilters"
                        class="border rounded-lg px-3 py-2 text-sm bg-white w-48 appearance-none pr-8"
                    >
                        <option value="">Tất cả danh mục</option>
                        <option v-for="category in categoriesList" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">▼</span>
                </div>
                
                <div class="w-full sm:w-auto relative">
                    <select 
                        v-model="filterRating" 
                        @change="applyFilters"
                        class="border rounded-lg px-3 py-2 text-sm bg-white w-36 appearance-none pr-8"
                    >
                        <option value="">Tất cả sao</option>
                        <option value="5">5 sao ⭐⭐⭐⭐⭐</option>
                        <option value="4">4 sao ⭐⭐⭐⭐</option>
                        <option value="3">3 sao ⭐⭐⭐</option>
                        <option value="2">2 sao ⭐⭐</option>
                        <option value="1">1 sao ⭐</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">▼</span>
                </div>
                
                <button 
                    @click="resetFilters" 
                    class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
                >
                    Xóa lọc
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <!-- Reviews Table -->
            <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Sản phẩm</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Biến thể</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Người dùng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Đánh giá</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Nội dung</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Ngày</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="review in paginatedReviews" 
                                :key="review.id" 
                                class="border-b border-gray-100 hover:bg-orange-50 transition-colors"
                            >
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    {{ review.product_variant?.product?.name || 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span 
                                            v-if="review.product_variant?.color?.code"
                                            class="inline-block w-4 h-4 rounded-full border border-gray-300 flex-shrink-0"
                                            :style="{ backgroundColor: review.product_variant.color.code }"
                                        ></span>
                                        <span class="text-gray-600">
                                            {{ review.product_variant?.color?.name || '' }}
                                            <span v-if="review.product_variant?.size_name" class="text-gray-400">
                                                - {{ review.product_variant.size_name }}
                                            </span>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs font-bold flex-shrink-0">
                                            {{ review.user?.name?.charAt(0)?.toUpperCase() || 'K' }}
                                        </div>
                                        <span class="text-gray-700">{{ review.user?.name || 'Khách hàng' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-amber-400">
                                    <span class="text-sm whitespace-nowrap">{{ renderStars(review.rating) }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="max-w-xs truncate text-gray-600" :title="review.comment">
                                        {{ review.comment || 'Không có nội dung' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                    {{ formatDate(review.created_at) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button 
                                            @click="confirmDelete(review)" 
                                            class="px-3 py-1.5 text-xs text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium"
                                            title="Xóa đánh giá"
                                        >
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedReviews.length === 0 && !isLoading">
                                <td colspan="7" class="text-center py-8 text-gray-400">
                                    {{ search || filterCategory || filterRating ? 'Không tìm thấy đánh giá nào phù hợp' : 'Chưa có đánh giá nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedReviews.length }} / {{ reviewsList.length }} đánh giá
                    </div>
                    
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2">
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in displayedPages"
                                :key="page"
                                @click="goToPage(page)"
                                class="px-3.5 py-1.5 text-sm rounded-lg transition-colors font-medium"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Xác nhận xóa</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >✕</button>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600">
                        Bạn có chắc chắn muốn xóa đánh giá của 
                        <strong>{{ selectedReview?.user?.name || 'khách hàng' }}</strong> 
                        cho sản phẩm 
                        <strong>{{ selectedReview?.product_variant?.product?.name || 'N/A' }}</strong>?
                    </p>
                    <p class="text-sm text-red-500 mt-2">Hành động này không thể hoàn tác.</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        :disabled="isDeleting"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteReview" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2"
                        :disabled="isDeleting"
                    >
                        <span v-if="isDeleting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isDeleting ? 'Đang xóa...' : 'Xóa' }}
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