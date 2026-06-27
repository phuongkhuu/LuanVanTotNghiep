<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    initialProducts: { type: Array, default: () => [] },
    type: { type: String, default: 'normal' },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    colors: { type: Array, default: () => [] }
});

// Search and filter
const search = ref('');
const activeType = ref(['normal', 'preorder'].includes(props.type) ? props.type : 'normal');

const productTypes = [
    { value: 'normal', label: 'Sản phẩm thường', icon: '📦' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

const products = ref(props.initialProducts);

// Modal
const showModal = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);
const modalTitle = computed(() => editingId.value ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới');

// Image handling
const imageInputMode = ref('url');
const fileError = ref('');

// Form data – hỗ trợ nhiều ảnh
const form = ref({
    name: '',
    category_id: null,
    brand_id: null,
    type: 'normal',
    imageUrls: [],
    imageFiles: [],
    material: '',
    description: '',
    variants: []
});

// Computed: hợp nhất URL và file để hiển thị preview
const allImagePreviews = computed(() => {
    const urls = form.value.imageUrls.map(url => ({ url, type: 'url' }));
    const files = form.value.imageFiles.map(file => ({
        url: URL.createObjectURL(file),
        type: 'file',
        file
    }));
    return [...urls, ...files];
});

// Hàm ngăn giá trị âm
const enforceNonNegative = (value) => {
    let num = parseFloat(value);
    if (isNaN(num)) return 0;
    return Math.max(0, num);
};

// Cập nhật giá trị price
const updatePrice = (variant, event) => {
    const raw = event.target.value;
    const newVal = enforceNonNegative(raw);
    variant.price = newVal;
    event.target.value = newVal;
};

// Cập nhật stock
const updateStock = (variant, event) => {
    const raw = event.target.value;
    const newVal = enforceNonNegative(raw);
    variant.stock = newVal;
    event.target.value = newVal;
};

// Thêm dòng variant
const addVariant = () => {
    form.value.variants.push({
        color_id: null,
        size_name: '',
        price: 0,
        stock: 0
    });
};

const removeVariant = (index) => {
    form.value.variants.splice(index, 1);
};

// Lọc sản phẩm
const filteredProducts = computed(() => {
    if (!products.value.length) return [];
    return products.value.filter(product => {
        const matchType = product.type === activeType.value;
        const matchSearch = !search.value ||
            product.name.toLowerCase().includes(search.value.toLowerCase()) ||
            (product.category && product.category.toLowerCase().includes(search.value.toLowerCase()));
        return matchType && matchSearch;
    });
});

const typeCounts = computed(() => ({
    normal: products.value.filter(p => p.type === 'normal').length,
    preorder: products.value.filter(p => p.type === 'preorder').length
}));

const getTypeCount = (type) => typeCounts.value[type] || 0;

const formatPrice = (value) => {
    if (!value || value === 0) return '---';
    return value.toLocaleString('vi-VN') + '₫';
};

// Thêm URL ảnh
const addImageUrl = () => {
    const input = document.getElementById('imageUrlInput');
    const url = input.value.trim();
    if (!url) {
        alert('Vui lòng nhập URL');
        return;
    }
    if (!url.match(/^https?:\/\/.+/)) {
        alert('URL không hợp lệ (phải bắt đầu bằng http:// hoặc https://)');
        return;
    }
    if (form.value.imageUrls.length + form.value.imageFiles.length >= 10) {
        alert('Tối đa 10 ảnh');
        return;
    }
    form.value.imageUrls.push(url);
    input.value = '';
};

// Xóa ảnh
const removeImage = (index, type) => {
    if (type === 'url') {
        form.value.imageUrls.splice(index, 1);
    } else if (type === 'file') {
        form.value.imageFiles.splice(index, 1);
    }
};

// Xử lý chọn file (multiple)
const handleFileChange = (event) => {
    const files = event.target.files;
    fileError.value = '';
    if (!files.length) return;

    const total = form.value.imageFiles.length + files.length;
    if (total > 10) {
        fileError.value = `Chỉ được tối đa 10 ảnh (hiện có ${form.value.imageFiles.length})`;
        event.target.value = '';
        return;
    }

    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            fileError.value = `File ${file.name} không phải ảnh`;
            continue;
        }
        if (file.size > 2 * 1024 * 1024) {
            fileError.value = `File ${file.name} vượt quá 2MB`;
            continue;
        }
        form.value.imageFiles.push(file);
    }
    event.target.value = '';
};

// Xóa tất cả file (khi đóng modal)
const clearFiles = () => {
    form.value.imageFiles = [];
    fileError.value = '';
    const input = document.getElementById('productImageInput');
    if (input) input.value = '';
};

// Mở modal
const openModal = (product = null) => {
    editingId.value = product?.id || null;
    imageInputMode.value = 'url';
    fileError.value = '';
    form.value.imageFiles = [];

    if (product) {
        form.value = {
            name: product.name,
            category_id: product.category_id,
            brand_id: product.brand_id,
            type: product.type,
            imageUrls: product.image_url || [],
            imageFiles: [],
            material: product.material || '',
            description: product.description || '',
            variants: product.variants ? product.variants.map(v => ({
                id: v.id,
                color_id: v.color_id,
                size_name: v.size_name || '',
                price: v.price,
                stock: v.stock
            })) : []
        };
    } else {
        form.value = {
            name: '',
            category_id: null,
            brand_id: null,
            type: activeType.value,
            imageUrls: [],
            imageFiles: [],
            material: '',
            description: '',
            variants: [{ color_id: null, size_name: '', price: 0, stock: 0 }]
        };
    }
    showModal.value = true;
};

const editProduct = (product) => openModal(product);

// Lưu sản phẩm
const saveProduct = async () => {
    // Kiểm tra tên
    if (!form.value.name.trim()) {
        alert('Vui lòng nhập tên sản phẩm');
        return;
    }

    // Kiểm tra chất liệu
    const material = form.value.material.trim();
    if (material && !/^[a-zA-ZÀ-ỹ\s\-]+$/.test(material)) {
        alert('Chất liệu chỉ được chứa chữ cái (có dấu), dấu cách và dấu gạch ngang.');
        return;
    }

    // Kiểm tra biến thể
    if (form.value.variants.length === 0) {
        alert('Vui lòng thêm ít nhất một biến thể');
        return;
    }
    for (let i = 0; i < form.value.variants.length; i++) {
        const v = form.value.variants[i];
        if (!v.color_id) {
            alert(`Vui lòng chọn màu cho biến thể thứ ${i + 1}`);
            return;
        }
        if (v.price <= 0) {
            alert(`Giá của biến thể ${i + 1} phải lớn hơn 0`);
            return;
        }
        if (v.stock < 0) {
            alert(`Tồn kho của biến thể ${i + 1} không hợp lệ`);
            return;
        }
    }

    if (fileError.value) {
        alert(fileError.value);
        return;
    }

    isSubmitting.value = true;

    const url = editingId.value
        ? route('admin.products.update', editingId.value)
        : route('admin.products.store');

    // Nếu có file upload => FormData
    if (form.value.imageFiles.length > 0) {
        const formData = new FormData();
        if (editingId.value) {
            formData.append('_method', 'PUT');
        }

        formData.append('name', form.value.name);
        formData.append('category_id', form.value.category_id ?? '');
        formData.append('brand_id', form.value.brand_id ?? '');
        formData.append('type', form.value.type);
        formData.append('material', form.value.material || '');
        formData.append('description', form.value.description || '');
        formData.append('image_url', JSON.stringify(form.value.imageUrls));

        form.value.imageFiles.forEach(file => {
            formData.append('image_files[]', file);
        });

        form.value.variants.forEach((variant, index) => {
            if (variant.id) formData.append(`variants[${index}][id]`, variant.id);
            formData.append(`variants[${index}][color_id]`, variant.color_id);
            formData.append(`variants[${index}][size_name]`, variant.size_name || '');
            formData.append(`variants[${index}][price]`, variant.price);
            formData.append(`variants[${index}][stock]`, variant.stock);
        });

        try {
            await router.post(url, formData, {
                preserveScroll: true,
                headers: { 'Content-Type': 'multipart/form-data' },
                onSuccess: () => {
                    alert(editingId.value ? 'Cập nhật thành công!' : 'Thêm sản phẩm thành công!');
                    showModal.value = false;
                    clearFiles();
                    router.reload({ only: ['initialProducts'] });
                },
                onError: (errors) => {
                    console.error(errors);
                    alert(errors.image_files?.[0] || errors.image_url?.[0] || 'Có lỗi xảy ra');
                }
            });
        } catch (error) {
            console.error(error);
            alert('Có lỗi xảy ra khi gửi dữ liệu');
        } finally {
            isSubmitting.value = false;
        }
    } else {
        // Không có file, gửi JSON bình thường
        const data = {
            ...form.value,
            image_url: form.value.imageUrls,
        };
        delete data.imageFiles;

        try {
            if (editingId.value) {
                await router.put(url, data, {
                    preserveScroll: true,
                    onSuccess: () => {
                        alert('Cập nhật thành công!');
                        showModal.value = false;
                        router.reload({ only: ['initialProducts'] });
                    },
                    onError: (errors) => {
                        console.error(errors);
                        alert(errors.image_url?.[0] || 'Có lỗi xảy ra');
                    }
                });
            } else {
                await router.post(url, data, {
                    preserveScroll: true,
                    onSuccess: () => {
                        alert('Thêm sản phẩm thành công!');
                        showModal.value = false;
                        router.reload({ only: ['initialProducts'] });
                    },
                    onError: (errors) => {
                        console.error(errors);
                        alert(errors.image_url?.[0] || 'Có lỗi xảy ra');
                    }
                });
            }
        } catch (error) {
            console.error(error);
            alert('Có lỗi xảy ra khi gửi dữ liệu');
        } finally {
            isSubmitting.value = false;
        }
    }
};

// Xóa sản phẩm
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
                console.error(errors);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        });
    } catch (error) {
        console.error(error);
        alert('Có lỗi xảy ra');
    }
};

const closeModal = () => {
    showModal.value = false;
    clearFiles();
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    router.get(route('admin.products.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.type, (newType) => {
    if (newType && ['normal', 'preorder'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
    }
});

watch(() => props.initialProducts, (val) => {
    products.value = val;
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

            <!-- Tabs -->
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

            <!-- Search -->
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

            <!-- Products Table -->
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
                                            <img 
                                                :src="product.thumbnail || ''" 
                                                class="w-full h-full object-cover" 
                                                :alt="product.name"
                                            >
                                        </div>
                                        <span class="font-medium text-gray-800">{{ product.name }}</span>
                                        <span class="text-xs text-gray-400 ml-1">({{ product.image_url?.length || 0 }})</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ product.category || '—' }}</td>
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

        <!-- Modal Add/Edit -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-4xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ modalTitle }}</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >✕</button>
                </div>
                
                <div class="space-y-4">
                    <!-- Thông tin cơ bản -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Tên sản phẩm</label>
                            <input v-model="form.name" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Nhập tên sản phẩm">
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Loại sản phẩm</label>
                            <select v-model="form.type" class="w-full border rounded-lg px-3 py-2">
                                <option value="normal">📦 Sản phẩm thường</option>
                                <option value="preorder">⏳ Pre-order</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Danh mục</label>
                            <select v-model="form.category_id" class="w-full border rounded-lg px-3 py-2">
                                <option :value="null">-- Chọn danh mục --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Thương hiệu</label>
                            <select v-model="form.brand_id" class="w-full border rounded-lg px-3 py-2">
                                <option :value="null">-- Chọn thương hiệu --</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Chất liệu</label>
                            <input v-model="form.material" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="VD: Canvas, Da, ...">
                        </div>
                        <!-- PHẦN HÌNH ẢNH MỚI -->
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Hình ảnh sản phẩm (tối đa 10 ảnh)</label>

                            <!-- Danh sách ảnh hiện có -->
                            <div v-if="allImagePreviews.length" class="flex flex-wrap gap-2 mb-3">
                                <div v-for="(img, idx) in allImagePreviews" :key="idx" class="relative w-20 h-20 border rounded overflow-hidden bg-gray-100 group">
                                    <img :src="img.url" class="w-full h-full object-cover" />
                                    <button 
                                        @click="removeImage(idx, img.type)"
                                        class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition"
                                        title="Xóa ảnh"
                                    >✕</button>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-400 mb-2">Chưa có ảnh</div>

                            <!-- Chọn chế độ nhập URL hoặc file -->
                            <div class="flex gap-2 border-b pb-2 mb-2">
                                <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">🔗 Nhập URL</button>
                                <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">📁 Tải ảnh lên</button>
                            </div>

                            <!-- Nhập URL -->
                            <div v-if="imageInputMode === 'url'" class="flex gap-2">
                                <input id="imageUrlInput" type="text" placeholder="Nhập URL ảnh" class="flex-1 border rounded-lg px-3 py-2 text-sm" />
                                <button @click="addImageUrl" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm">Thêm</button>
                            </div>

                            <!-- Upload file (multiple) -->
                            <div v-else>
                                <input id="productImageInput" type="file" accept="image/*" multiple @change="handleFileChange" class="w-full text-sm" />
                                <p class="text-xs text-gray-400 mt-1">Chọn nhiều ảnh (tối đa 2MB mỗi ảnh)</p>
                                <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mô tả</label>
                        <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Mô tả chi tiết sản phẩm"></textarea>
                    </div>

                    <!-- Biến thể (variants) -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-medium text-gray-700">Biến thể (Màu sắc, Kích thước, Giá, Tồn kho)</label>
                            <button type="button" @click="addVariant" class="text-sm text-blue-600 hover:text-blue-800">+ Thêm biến thể</button>
                        </div>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left">Màu</th>
                                        <th class="px-2 py-2 text-left">Kích thước</th>
                                        <th class="px-2 py-2 text-left">Giá (₫)</th>
                                        <th class="px-2 py-2 text-left">Tồn kho</th>
                                        <th class="px-2 py-2 text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(variant, idx) in form.variants" :key="idx">
                                        <td class="px-2 py-1">
                                            <select v-model="variant.color_id" class="w-full border rounded px-2 py-1">
                                                <option :value="null">-- Chọn màu --</option>
                                                <option v-for="color in colors" :key="color.id" :value="color.id">{{ color.name }}</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-1">
                                            <input type="text" v-model="variant.size_name" class="w-full border rounded px-2 py-1" placeholder="VD: S, M, L, XL, Free...">
                                        </td>
                                        <td class="px-2 py-1">
                                            <input 
                                                type="number" 
                                                :value="variant.price"
                                                @input="updatePrice(variant, $event)"
                                                class="w-28 border rounded px-2 py-1" 
                                                placeholder="Giá"
                                                min="0"
                                            >
                                        </td>
                                        <td class="px-2 py-1">
                                            <input 
                                                type="number" 
                                                :value="variant.stock"
                                                @input="updateStock(variant, $event)"
                                                class="w-20 border rounded px-2 py-1" 
                                                placeholder="Tồn"
                                                min="0"
                                            >
                                        </td>
                                        <td class="px-2 py-1 text-center">
                                            <button @click="removeVariant(idx)" class="text-red-500 hover:text-red-700" title="Xóa">✕</button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.variants.length === 0">
                                        <td colspan="5" class="text-center py-4 text-gray-400">Chưa có biến thể nào. Hãy nhấn "Thêm biến thể".</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button 
                        @click="saveProduct" 
                        :disabled="isSubmitting || !!fileError" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
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