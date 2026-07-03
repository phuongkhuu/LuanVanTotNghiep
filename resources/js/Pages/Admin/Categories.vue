<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] }
});

const search = ref('');

// Modal state
const showModal = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

// Chọn phương thức nhập ảnh: 'url' hoặc 'file'
const imageInputMode = ref('url');
const selectedFile = ref(null);
const imagePreviewUrl = ref('');

const form = ref({
    name: '',
    description: '',
    image: ''
});

const modalTitle = computed(() => editingId.value ? 'Sửa danh mục' : 'Thêm danh mục mới');

// Xử lý đường dẫn ảnh hiển thị (cho đường dẫn từ DB, đã được lưu dạng /image/...)
const getImageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    // Nếu đã bắt đầu bằng /image thì giữ nguyên
    if (path.startsWith('/image')) return path;
    // Nếu bắt đầu bằng image/ (không có slash đầu) thì thêm slash
    if (path.startsWith('image/')) return '/' + path;
    // Fallback: coi như đường dẫn tương đối từ gốc
    return '/' + path;
};

// Xem trước ảnh trong modal (ưu tiên preview từ file mới chọn, nếu không thì dùng form.image)
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value;
    if (form.value.image) return getImageUrl(form.value.image);
    return null;
});

// Lọc danh mục (giữ nguyên thứ tự từ props - đã được sắp xếp từ controller)
const filteredCategories = computed(() => {
    if (!props.categories.length) return [];
    if (!search.value) return props.categories;
    const kw = search.value.toLowerCase();
    return props.categories.filter(c => 
        c.name.toLowerCase().includes(kw) || 
        (c.description && c.description.toLowerCase().includes(kw))
    );
});

// Mở modal (thêm hoặc sửa)
const openModal = (category = null) => {
    editingId.value = category?.id || null;
    selectedFile.value = null;
    imagePreviewUrl.value = '';
    imageInputMode.value = 'url';
    
    if (category) {
        form.value = {
            name: category.name,
            description: category.description || '',
            image: category.image || ''
        };
    } else {
        form.value = { name: '', description: '', image: '' };
    }
    showModal.value = true;
};

const editCategory = (category) => openModal(category);

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('Vui lòng chọn file ảnh (jpg, png, ...)');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Kích thước ảnh không quá 2MB');
        return;
    }
    selectedFile.value = file;
    // Tạo preview
    const reader = new FileReader();
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result; };
    reader.readAsDataURL(file);
    // Xóa image cũ nếu có
    form.value.image = '';
};

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null;
    imagePreviewUrl.value = '';
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';
    }
};

// Lưu danh mục (hỗ trợ upload file)
const saveCategory = async () => {
    if (!form.value.name.trim()) {
        alert('Vui lòng nhập tên danh mục');
        return;
    }
    
    isSubmitting.value = true;
    
    let url, method, submitData, headers = {};
    
    if (editingId.value) {
        url = route('admin.categories.update', editingId.value);
        method = 'post';
        submitData = { _method: 'put', ...form.value };
    } else {
        url = route('admin.categories.store');
        method = 'post';
        submitData = { ...form.value };
    }
    
    // Nếu có file upload thì chuyển sang FormData
    if (selectedFile.value) {
        const formData = new FormData();
        formData.append('_method', editingId.value ? 'PUT' : 'POST');
        formData.append('name', form.value.name);
        formData.append('description', form.value.description);
        formData.append('image_file', selectedFile.value);
        // Không gửi trường image vì sẽ được xử lý từ file
        submitData = formData;
        headers = { 'Content-Type': 'multipart/form-data' };
    }
    
    try {
        await router[method](url, submitData, {
            preserveScroll: true,
            headers: headers,
            onSuccess: () => {
                alert(editingId.value ? 'Cập nhật thành công!' : 'Thêm danh mục thành công!');
                showModal.value = false;
                clearFile();
            },
            onError: (errors) => {
                console.error('Lỗi:', errors);
                const msg = errors.name?.[0] || errors.image_file?.[0] || 'Có lỗi xảy ra';
                alert(msg);
            }
        });
    } catch (error) {
        console.error(error);
        alert('Có lỗi xảy ra khi gửi dữ liệu');
    } finally {
        isSubmitting.value = false;
    }
};

// Xóa danh mục
const confirmDelete = (id, name) => {
    if (confirm(`Xóa danh mục "${name}"? Các sản phẩm liên quan sẽ mất danh mục.`)) {
        router.delete(route('admin.categories.destroy', id), { preserveScroll: true });
    }
};

const closeModal = () => {
    showModal.value = false;
    clearFile();
};
</script>

<template>
    <Head title="Quản lý danh mục" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý danh mục</h1>
                </div>
                <button @click="openModal()" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm danh mục
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input v-model="search" type="text" placeholder="Tìm danh mục..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                </div>
            </div>

            <!-- Bảng danh mục -->
            <div class="bg-white rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 w-16">STT</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">HÌNH ẢNH</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">TÊN</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">SLUG</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">MÔ TẢ</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sử dụng v-for với index để đánh STT 1,2,3,... -->
                            <tr v-for="(cat, index) in filteredCategories" :key="cat.id" class="border-t hover:bg-orange-50">
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                        <img 
                                            v-if="cat.image" 
                                            :src="getImageUrl(cat.image)" 
                                            @error="$event.target.src = 'https://placehold.co/400x400?text=No+Image'"
                                            class="w-full h-full object-cover"
                                        >
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                            No img
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ cat.name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ cat.slug }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ cat.description || '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="editCategory(cat)" class="text-green-600 hover:bg-green-100 px-2 py-1 rounded">Sửa</button>
                                    <button @click="confirmDelete(cat.id, cat.name)" class="text-red-600 hover:bg-red-100 px-2 py-1 rounded ml-1">Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="!filteredCategories.length">
                                <td colspan="6" class="text-center py-8 text-gray-500">Không có danh mục nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ modalTitle }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium block mb-1">Tên danh mục <span class="text-red-500">*</span></label>
                        <input v-model="form.name" class="w-full border rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" placeholder="Ví dụ: Balo, Túi xách...">
                        <p class="text-xs text-gray-500 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Mô tả</label>
                        <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2" placeholder="Mô tả ngắn (không bắt buộc)"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-2">Hình ảnh</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">📁 Tải ảnh lên</button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.image" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="https://example.com/image.jpg">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" class="w-full">
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1">Xóa file đã chọn</button>
                        </div>
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600">Xem trước:</p>
                            <div class="w-32 h-32 border rounded overflow-hidden bg-gray-100">
                                <img :src="imagePreview" class="w-full h-full object-cover" @error="imagePreviewUrl = ''; form.image = ''">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button @click="saveCategory" :disabled="isSubmitting" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        {{ isSubmitting ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>