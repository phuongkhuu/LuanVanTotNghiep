<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    initialProducts: {
        type: Array,
        default: () => []
    },
    type: {
        type: String,
        default: 'normal' // normal = sản phẩm thường, preorder = pre-order
    }
});

// Search and filters
const search = ref('');
const activeType = ref(['normal', 'preorder'].includes(props.type) ? props.type : 'normal');

// Product types tabs - chỉ 2 loại
const productTypes = [
    { value: 'normal', label: 'Sản phẩm thường', icon: '📦' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

// Products data - lấy từ server, không dùng mock data
const products = ref(props.initialProducts);

// Category options (giữ nguyên)
const categoryOptions = ['Balo', 'Cặp - Túi', 'Phụ kiện', 'Set sản phẩm', 'Túi xách', 'Ví da'];

// Modal state
const showModal = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

// Modal title
const modalTitle = computed(() => editingId.value ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới');

// Form data
const form = ref({
    name: '',
    category: 'Balo',
    type: 'normal', // mặc định normal
    price: 0,
    wholesalePrice: 0,
    stock: 0,
    image: '',
    status: 'active'
});

// Computed: filtered products
const filteredProducts = computed(() => {
    if (!products.value || products.value.length === 0) return [];
    return products.value.filter(product => {
        const matchType = product.type === activeType.value;
        const matchSearch = !search.value || 
            product.name.toLowerCase().includes(search.value.toLowerCase()) ||
            product.category.toLowerCase().includes(search.value.toLowerCase());
        return matchType && matchSearch;
    });
});

// Get count by type
const getTypeCount = (type) => {
    if (!products.value) return 0;
    return products.value.filter(p => p.type === type).length;
};

// Format price to VND
const formatPrice = (value) => {
    if (!value || value === 0) return '---';
    return value.toLocaleString('vi-VN') + '₫';
};

// Open modal for add/edit
const openModal = (product = null) => {
    editingId.value = product?.id || null;
    
    if (product) {
        form.value = { ...product };
    } else {
        form.value = {
            name: '',
            category: 'Balo',
            type: activeType.value,
            price: 0,
            wholesalePrice: 0,
            stock: 0,
            image: '',
            status: 'active'
        };
    }
    showModal.value = true;
};

// Edit product
const editProduct = (product) => {
    openModal(product);
};

// Save product
const saveProduct = async () => {
    if (!form.value.name) {
        alert('Vui lòng nhập tên sản phẩm');
        return;
    }
    if (form.value.price <= 0) {
        alert('Vui lòng nhập giá sản phẩm');
        return;
    }
    
    isSubmitting.value = true;
    
    try {
        if (editingId.value) {
            // Update
            const index = products.value.findIndex(p => p.id === editingId.value);
            if (index !== -1) {
                products.value[index] = { ...form.value, id: editingId.value };
            }
            await router.put(`/admin/products/${editingId.value}`, form.value, {
                preserveScroll: true,
                onSuccess: () => alert('Cập nhật sản phẩm thành công!'),
                onError: (errors) => {
                    console.error('Lỗi cập nhật:', errors);
                    alert('Có lỗi xảy ra khi cập nhật sản phẩm');
                }
            });
        } else {
            // Add new
            const newProduct = {
                ...form.value,
                id: Date.now(),
                image: form.value.image || `https://picsum.photos/40/40?${Date.now()}`
            };
            products.value.push(newProduct);
            await router.post('/admin/products', form.value, {
                preserveScroll: true,
                onSuccess: () => alert('Thêm sản phẩm thành công!'),
                onError: (errors) => {
                    console.error('Lỗi thêm mới:', errors);
                    alert('Có lỗi xảy ra khi thêm sản phẩm');
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

// Delete product
const deleteProduct = async (id) => {
    const product = products.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${product?.name}"?`)) return;
    
    try {
        await router.delete(`/admin/products/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                products.value = products.value.filter(p => p.id !== id);
                alert('Xóa sản phẩm thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi xóa:', errors);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
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

// Change tab and update URL
const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    router.get(route('admin.products.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Watch props.type từ URL
watch(() => props.type, (newType) => {
    if (newType && ['normal', 'preorder'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
    }
});

// Watch props.initialProducts để cập nhật khi dữ liệu thay đổi
watch(() => props.initialProducts, (newProducts) => {
    products.value = newProducts;
}, { immediate: true });
</script>

<template>
    <Head title="Quản lý sản phẩm - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý sản phẩm</h1>
                    <p class="text-gray-600 text-sm mt-1">Quản lý sản phẩm thường và pre-order</p>
                </div>
                <button 
                    @click="openModal()" 
                    class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm sản phẩm
                </button>
            </div>

            <!-- Tab loại sản phẩm - chỉ 2 tab -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in productTypes" 
                    :key="tab.value" 
                    @click="changeActiveType(tab.value)"
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
                        placeholder="Tìm sản phẩm theo tên hoặc danh mục..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SẢN PHẨM</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">DANH MỤC</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">GIÁ</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">GIÁ SỈ</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỒN KHO</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="product in filteredProducts" 
                                :key="product.id" 
                                class="border-b border-gray-200 hover:bg-orange-50 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden">
                                            <img :src="product.image" class="w-full h-full object-cover" :alt="product.name">
                                        </div>
                                        <span class="font-medium text-gray-800">{{ product.name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ product.category }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(product.price) }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ formatPrice(product.wholesalePrice) }}</td>
                                <td class="py-3 px-4" :class="product.stock < 10 ? 'text-yellow-600 font-semibold' : 'text-gray-600'">
                                    {{ product.stock }}
                                </td>
                                <td class="py-3 px-4">
                                    <span 
                                        class="text-xs px-2 py-1 rounded-full"
                                        :class="product.stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ product.stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="editProduct(product)" 
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                        title="Sửa sản phẩm"
                                    >Sửa</button>
                                    <button 
                                        @click="deleteProduct(product.id)" 
                                        class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg ml-1 transition-colors"
                                        title="Xóa sản phẩm"
                                    >Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="filteredProducts.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    Không có sản phẩm nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa sản phẩm -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ modalTitle }}</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >✕</button>
                </div>
                
                <div class="space-y-4">
                    <!-- Tên sản phẩm -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Tên sản phẩm</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập tên sản phẩm"
                        >
                    </div>
                    
                    <!-- Danh mục -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Danh mục</label>
                        <select 
                            v-model="form.category" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    
                    <!-- Loại sản phẩm (chỉ 2 lựa chọn) -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Loại sản phẩm</label>
                        <select 
                            v-model="form.type" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                        >
                            <option value="normal">📦 Sản phẩm thường</option>
                            <option value="preorder">⏳ Pre-order</option>
                        </select>
                    </div>
                    
                    <!-- Giá bán lẻ -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá bán lẻ</label>
                        <input 
                            v-model="form.price" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập giá bán lẻ"
                        >
                    </div>
                    
                    <!-- Giá bán sỉ (chỉ hiển thị khi chọn sản phẩm thường) -->
                    <div v-if="form.type === 'normal'">
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Giá bán sỉ (cho doanh nghiệp)</label>
                        <input 
                            v-model="form.wholesalePrice" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập giá bán sỉ"
                        >
                    </div>
                    
                    <!-- Số lượng tồn kho -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Số lượng tồn kho</label>
                        <input 
                            v-model="form.stock" 
                            type="number" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập số lượng"
                        >
                    </div>
                    
                    <!-- Hình ảnh URL -->
                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Hình ảnh URL</label>
                        <input 
                            v-model="form.image" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                            placeholder="Nhập URL hình ảnh sản phẩm"
                        >
                        <p class="text-xs text-gray-500 mt-1">Để trống sẽ sử dụng ảnh mặc định</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >Hủy</button>
                    <button 
                        @click="saveProduct" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                        :disabled="isSubmitting"
                    >{{ isSubmitting ? 'Đang lưu...' : 'Lưu' }}</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* No additional styles needed - using Tailwind classes */
</style>