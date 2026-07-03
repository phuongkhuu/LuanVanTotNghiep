# Sửa ColorController

**Created:** 06/29/2026 17:13  
**Updated:** 06/29/2026 21:52  
**Exported:** 07/03/2026 00:32  
**Link:** [https://chat.deepseek.com/a/chat/s/95c76c5a-ae6c-4082-b20f-18c20a5a0e63](https://chat.deepseek.com/a/chat/s/95c76c5a-ae6c-4082-b20f-18c20a5a0e63)  

## User:
06/29/2026 17:17

<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{

    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }


    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);


            if (!empty($validated['name']) && !empty($validated['code'])) {

                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {

                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {

                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {

                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
               
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu và mã hex!'
                ], 422);
            }


            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([  //required là không được null
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);


            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }


            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        
        $code = strtoupper(trim($code));
        

        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        

        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        
        return '#CCCCCC';
    }


    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }


    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        
        $code = strtoupper(trim($code));
        
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#490c42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen'
        ];
        

        $expandedMap = [
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        
        $fullMap = array_merge($codeMap, $expandedMap);
        
        return $fullMap[$code] ?? 'Màu khác';
    }
}

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp màu sắc theo ID giảm dần
const sortedColors = computed(() => {
    return [...colors.value].sort((a, b) => b.id - a.id)
})

const colors = ref(props.colors)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

const previewColor = ref('#CCCCCC')
const previewColorCode = ref('#CCCCCC')
const isGenerating = ref(false)

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC' //Mặc định là xám
    if (isHexCode(name)) return name.toUpperCase() //Nếu tên màu được viết dưới dạng mã màu, thì chỉ cần in hoa và trả về

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (chỉ dùng để gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

const updateColorPreview = () => {
    if (isGenerating.value) return
    
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    // Ưu tiên hiển thị theo code nếu có
    if (inputCode && isHexCode(inputCode)) {
        const normalizedCode = inputCode.toUpperCase()
        previewColor.value = normalizedCode
        previewColorCode.value = normalizedCode
        
        // Gợi ý tên từ code (chỉ gợi ý, không tự động điền)
        const suggestedName = suggestColorNameFromCode(normalizedCode)
        if (suggestedName && !inputName) {
            // Chỉ hiển thị gợi ý trong preview, không tự động điền vào input
            // Người dùng có thể nhập tên tùy ý
        }
        return
    }
    
    if (inputName) {
        const code = getColorCodeFromName(inputName)
        previewColor.value = code
        previewColorCode.value = code
        // Gợi ý code từ tên
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        previewColor.value = '#CCCCCC'
        previewColorCode.value = '#CCCCCC'
    }
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => { //Không cần chờ theo thứ tự từ trên xuống trong code
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    previewColor.value = '#CCCCCC'
    previewColorCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true //#######
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    previewColor.value = code
    previewColorCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true //Bật form
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            previewColor.value = '#CCCCCC'
            previewColorCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý màu sắc</h1>
            </div>

            <div class="mb-6">
                <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                    + Thêm màu sắc mới
                </button>
            </div>

            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(color, index) in sortedColors" :key="color.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(color)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Sửa</button>
                                    <button @click="confirmDelete(color)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateColorPreview"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã hex</label>
                        <input 
                            v-model="form.code" 
                            type="text" 
                            class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.code }"
                            placeholder="#dc2626, #FFA500, #490C42..." 
                            @input="updateColorPreview"
                        >
                        <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-md" :style="{ backgroundColor: previewColor }"></div>
                        <div class="text-sm text-gray-600">
                            Xem trước màu<br>
                            <span class="text-xs text-gray-400 font-mono">{{ previewColorCode }}</span>
                        </div>
                    </div>
                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>

hãy chỉnh lại cho tôi , tôi muốn gắn thanh color picker vào rồi tự lấy mã màu dựa vào đó luôn


## Assistant:
06/29/2026 17:17

> **Thinking:**
> We need to analyze the request. The user provided a full Laravel + Inertia + Vue component code for a Color management (CRUD). They want to add a color picker (HTML input type color) to the modal so that selecting a color automatically fills the hex code. The current code already has a preview box, but they want to integrate a color picker.
> 
> We need to modify the Vue template and script to include `<input type="color">` bound to `form.code` and when it changes, update the preview accordingly. Also ensure that the logic for generating name from code or code from name still works but with the color picker.
> 
> The code is quite long. We need to provide a solution: update the modal part to include a color picker input that binds to `form.code`, and perhaps also update the preview box. The user said "tôi muốn gắn thanh color picker vào rồi tự lấy mã màu dựa vào đó luôn" meaning they want to attach a color picker and automatically get the color code from it. So we should add an input of type color, and when it changes, update the code field.
> 
> We should also consider the existing logic: when name is entered, it auto-generates code (if not provided) via `updateColorPreview`. When code is entered, it suggests name but doesn't auto-fill. With a color picker, we can set the code and maybe suggest a name if name is empty.
> 
> We need to produce a revised version of the Vue template and script, highlighting the changes. Since the user gave the whole code, we can provide the full revised code or just the relevant parts. I'll provide a concise answer with instructions and modified sections.
> 
> Let's outline the changes:
> 
> 1. In the modal form, replace the manual hex input with a color picker input, but we can keep both (color picker and text input for manual editing). The color picker can be an input of type color, with v-model="form.code", and we can also keep the text input for manual hex entry. We can have both side by side.
> 
> 2. Ensure that when the color picker changes, we call `updateColorPreview` to update the preview and possibly suggest name.
> 
> 3. In the `updateColorPreview` method, if the code is set (from picker), we can try to suggest a name if name is empty (using `suggestColorNameFromCode`). But the user said "tự lấy mã màu dựa vào đó" - they want to get the code from the picker, so we just bind the picker to form.code. That's it.
> 
> But the current logic in `updateColorPreview` already handles code changes: it updates previewColor and previewColorCode. If name is empty and code is set, it will show preview based on code. If we add a color picker, it will set code, and preview updates.
> 
> We can also add a new watcher or use @change on the color picker to set the code and update.
> 
> We'll provide a modified modal section with a color picker input, and maybe adjust the existing text input for hex code to be optional or place it next to the picker.
> 
> Also, we need to ensure that the preview box reflects the chosen color.
> 
> Given the code, we can produce a revised version of the template modal part.
> 
> I'll write an answer that includes the updated `<template>` portion for the modal, and maybe add a computed property or method to handle the color picker change.
> 
> Let's produce the final answer.

Bạn muốn tích hợp thanh chọn màu (color picker) vào form để khi chọn màu, mã hex sẽ tự động được điền và hiển thị. Dưới đây là các thay đổi cần thực hiện trong component Vue của bạn.

## 1. Cập nhật phần template của modal (Thêm/Sửa)

Thay thế phần nhập mã hex bằng một hàng chứa cả **color picker** và **ô nhập tay**. Đồng thời giữ nguyên ô nhập tên màu và khung xem trước.

```vue
<!-- Modal Thêm/Sửa -->
<div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
        <div class="space-y-4">
            <!-- Tên màu -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                <input 
                    v-model="form.name" 
                    type="text" 
                    class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                    :class="{ 'border-red-500': validationErrors.name }"
                    placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                    @input="updateColorPreview"
                >
                <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
            </div>

            <!-- Mã hex + Color Picker -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã hex</label>
                <div class="flex items-center gap-3">
                    <!-- Color picker -->
                    <input 
                        type="color" 
                        :value="form.code || '#CCCCCC'"
                        @input="e => { form.code = e.target.value; updateColorPreview(); }"
                        class="w-12 h-12 p-0 border rounded cursor-pointer"
                    >
                    <!-- Ô nhập tay -->
                    <input 
                        v-model="form.code" 
                        type="text" 
                        class="flex-1 border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                        :class="{ 'border-red-500': validationErrors.code }"
                        placeholder="#dc2626, #FFA500, #490C42..." 
                        @input="updateColorPreview"
                    >
                </div>
                <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
            </div>

            <!-- Xem trước -->
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-md" :style="{ backgroundColor: previewColor }"></div>
                <div class="text-sm text-gray-600">
                    Xem trước màu<br>
                    <span class="text-xs text-gray-400 font-mono">{{ previewColorCode }}</span>
                </div>
            </div>

            <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
            <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
            </button>
        </div>
    </div>
</div>
```

## 2. Điều chỉnh logic trong `updateColorPreview`

Hàm `updateColorPreview` hiện tại đã xử lý cả trường hợp nhập tên và nhập mã. Khi bạn chọn màu từ color picker, `form.code` được gán và gọi `updateColorPreview`, sẽ cập nhật `previewColor` và `previewColorCode`. Bạn có thể bổ sung thêm logic gợi ý tên từ mã nếu tên đang trống (tuỳ chọn).

```javascript
const updateColorPreview = () => {
    if (isGenerating.value) return
    
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    // Ưu tiên hiển thị theo code nếu có
    if (inputCode && isHexCode(inputCode)) {
        const normalizedCode = inputCode.toUpperCase()
        previewColor.value = normalizedCode
        previewColorCode.value = normalizedCode
        
        // Gợi ý tên từ code nếu tên chưa có
        if (!inputName) {
            const suggestedName = suggestColorNameFromCode(normalizedCode)
            if (suggestedName && suggestedName !== 'Màu khác') {
                // Có thể tự động điền hoặc chỉ hiển thị gợi ý
                // Ở đây ta để người dùng tự nhập, không tự động điền
            }
        }
        return
    }
    
    if (inputName) {
        const code = getColorCodeFromName(inputName)
        previewColor.value = code
        previewColorCode.value = code
        // Gợi ý code từ tên
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        previewColor.value = '#CCCCCC'
        previewColorCode.value = '#CCCCCC'
    }
}
```

## 3. (Tuỳ chọn) Tự động điền tên khi chọn màu từ picker

Nếu bạn muốn khi chọn màu từ color picker, nếu tên chưa nhập thì tự động điền tên gợi ý (nếu có), bạn có thể thêm vào sự kiện `@input` của color picker:

```vue
<input 
    type="color" 
    :value="form.code || '#CCCCCC'"
    @input="e => { 
        form.code = e.target.value; 
        if (!form.name?.trim()) {
            const suggested = suggestColorNameFromCode(e.target.value);
            if (suggested && suggested !== 'Màu khác') {
                form.name = suggested;
            }
        }
        updateColorPreview(); 
    }"
    class="w-12 h-12 p-0 border rounded cursor-pointer"
>
```

Tuy nhiên, theo yêu cầu của bạn chỉ là "tự lấy mã màu dựa vào đó", nên việc gán `form.code` là đủ. Bạn có thể quyết định có tự động gợi ý tên hay không.

---

**Kết quả:** Bây giờ form quản lý màu sắc của bạn có thanh chọn màu trực quan, giúp người dùng dễ dàng chọn màu và lấy mã hex tự động, đồng thời vẫn hỗ trợ nhập tên và mã thủ công.


## User:
06/29/2026 17:47

chỉnh lại cho tôi ,
bỏ xem màu trước 
tôi muốn pickder dễ dùng hơn sau đó ở dưới hiện mã hex


## Assistant:
06/29/2026 17:47

> **Thinking:**
> Chúng ta sẽ chỉnh lại theo yêu cầu: bỏ phần xem trước, chỉ giữ color picker và hiển thị mã hex bên dưới (có thể là ô input hiển thị mã hex, hoặc text đơn giản). Đồng thời làm cho color picker dễ dùng hơn (có thể phóng to hoặc đặt nổi bật). Dưới đây là gợi ý cập nhật giao diện.
> 
> ---
> 
> ### Cập nhật Modal (phần template)
> 
> ```vue
> <!-- Modal Thêm/Sửa -->
> <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
>     <div class="bg-white rounded-lg w-full max-w-md p-6">
>         <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
>         <div class="space-y-4">
>             <!-- Tên màu -->
>             <div>
>                 <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
>                 <input 
>                     v-model="form.name" 
>                     type="text" 
>                     class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
>                     :class="{ 'border-red-500': validationErrors.name }"
>                     placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
>                     @input="updateColorPreview"
>                 >
>                 <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
>             </div>
> 
>             <!-- Color Picker -->
>             <div>
>                 <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
>                 <div class="flex justify-center">
>                     <input 
>                         type="color" 
>                         :value="form.code || '#CCCCCC'"
>                         @input="e => { form.code = e.target.value; updateColorPreview(); }"
>                         class="w-20 h-20 p-0 border rounded-lg cursor-pointer"
>                     >
>                 </div>
>             </div>
> 
>             <!-- Mã hex (hiển thị + có thể sửa) -->
>             <div>
>                 <label class="block text-sm font-medium text-gray-700 mb-1">Mã hex</label>
>                 <input 
>                     v-model="form.code" 
>                     type="text" 
>                     class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
>                     :class="{ 'border-red-500': validationErrors.code }"
>                     placeholder="#dc2626, #FFA500, #490C42..." 
>                     @input="updateColorPreview"
>                 >
>                 <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
>             </div>
> 
>             <!-- Hiển thị lỗi chung -->
>             <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
>                 <p class="text-sm text-red-600">{{ errorMessage }}</p>
>             </div>
>         </div>
>         <div class="flex justify-end gap-3 mt-6">
>             <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
>             <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
>                 <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                 {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
>             </button>
>         </div>
>     </div>
> </div>
> ```
> 
> **Thay đổi chính:**
> - Bỏ phần "Xem trước màu" (ô vuông và label).
> - Đặt color picker to hơn (`w-20 h-20`) và căn giữa.
> - Vẫn giữ ô nhập mã hex dạng text bên dưới (vừa hiển thị, vừa cho phép sửa thủ công).
> 
> ---
> 
> ### Điều chỉnh logic (không cần thay đổi nhiều)
> 
> Hàm `updateColorPreview` hiện tại vẫn hoạt động tốt, nhưng giờ không còn hiển thị ô xem trước nên có thể bỏ biến `previewColor` và `previewColorCode` nếu không dùng. Tuy nhiên, nếu bạn vẫn muốn giữ chúng để phục vụ các mục đích khác (ví dụ hiển thị trong tương lai) thì không sao. Bạn có thể loại bỏ chúng hoàn toàn để đơn giản.
> 
> Gợi ý: Xóa bỏ `previewColor` và `previewColorCode`, sửa lại hàm `updateColorPreview` chỉ để đồng bộ code từ tên (nếu cần) và không cần cập nhật preview.
> 
> ```javascript
> const updateColorPreview = () => {
>     // Nếu có mã hex thì không làm gì thêm
>     if (form.value.code && isHexCode(form.value.code)) {
>         return
>     }
>     // Nếu có tên và không có code, thử sinh code từ tên
>     if (form.value.name?.trim() && !form.value.code) {
>         const code = getColorCodeFromName(form.value.name)
>         if (code !== '#CCCCCC') {
>             form.value.code = code
>         }
>     }
> }
> ```
> 
> Đơn giản hơn: bạn có thể bỏ hẳn hàm `updateColorPreview` và chỉ xử lý logic khi lưu. Nhưng để giữ tiện ích gợi ý mã từ tên, vẫn nên giữ một phiên bản nhẹ.
> 
> ---
> 
> ### Tóm tắt
> 
> - **Bỏ xem trước** (ô vuông và label).
> - **Color picker** được phóng to (kích thước `w-20 h-20`) và đặt giữa.
> - **Mã hex** hiển thị ngay bên dưới dạng ô input, vừa hiển thị vừa cho phép chỉnh sửa.
> - Vẫn hỗ trợ nhập tên và tự động sinh mã nếu chưa có mã.
> 
> Bạn có thể áp dụng đoạn template trên và điều chỉnh logic tùy ý.


## User:
06/29/2026 17:48

đưa code hoàn chỉnh cho tôi'


## Assistant:
06/29/2026 17:48

> **Thinking:**
> Chúng ta cần đưa ra code hoàn chỉnh cho component Vue với color picker, bỏ phần xem trước riêng, chỉ hiển thị mã hex bên dưới. Tôi sẽ viết lại toàn bộ script và template, giữ nguyên các logic khác. 
> 
> Yêu cầu: 
> - Bỏ khung xem trước màu (phần div có class "flex items-center gap-4 p-4 bg-gray-50 rounded-lg").
> - Sử dụng color picker input type="color" kết hợp với ô nhập mã hex.
> - Hiển thị mã hex ngay dưới picker (có thể trong cùng hàng hoặc dòng riêng).
> - Giao diện đơn giản, dễ dùng.
> 
> Tôi sẽ đưa ra toàn bộ nội dung file Vue (bao gồm script setup, template). Lưu ý: đây là component nằm trong Admin/Colors/index.vue hoặc tương tự.
> 
> Tôi sẽ giữ nguyên các hàm và biến, chỉ sửa phần template và một chút logic trong updateColorPreview nếu cần.
> 
> Tôi sẽ đưa code hoàn chỉnh từ đầu đến cuối.

Dưới đây là code hoàn chỉnh cho component quản lý màu sắc với **color picker** tích hợp, bỏ phần xem trước riêng, hiển thị mã hex ngay bên dưới picker.

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp màu sắc theo ID giảm dần
const sortedColors = computed(() => {
    return [...colors.value].sort((a, b) => b.id - a.id)
})

const colors = ref(props.colors)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (chỉ gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    // Nếu tên trống, tự động gợi ý tên
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý màu sắc</h1>
            </div>

            <div class="mb-6">
                <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                    + Thêm màu sắc mới
                </button>
            </div>

            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(color, index) in sortedColors" :key="color.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(color)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Sửa</button>
                                    <button @click="confirmDelete(color)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <!-- Tên màu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <!-- Color Picker + Mã hex -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <!-- Color picker -->
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
                            >
                            <!-- Hiển thị mã hex -->
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <!-- Ô nhập mã hex thủ công (có thể ẩn nếu không cần, nhưng để linh hoạt thì giữ) -->
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

**Giải thích các thay đổi:**

1. **Bỏ khung xem trước màu** – đã xóa phần `div` hiển thị ô màu và mã xem trước.
2. **Tích hợp color picker** – sử dụng thẻ `<input type="color">` với kích thước lớn dễ chọn.
3. **Hiển thị mã hex** – ngay bên cạnh picker, dùng biến `displayCode` để cập nhật theo cả tên màu và mã nhập tay.
4. **Tự động gợi ý tên** – khi chọn màu từ picker, nếu tên đang trống sẽ tự động điền tên gợi ý (nếu có).
5. **Vẫn giữ ô nhập mã hex thủ công** – cho phép người dùng nhập tay nếu muốn, mọi thay đổi đều cập nhật `displayCode`.

Bạn có thể dùng trực tiếp file này thay thế cho component hiện tại.


## User:
06/29/2026 20:12

Chỉnh giao diện của color thêm thanh tìm kiếm cho tôi tương tự với giao diện tôi đưa code dưới đây
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
const isUpdating = ref(false);

const filteredOrders = computed(() => {
    if (!orders.value || orders.value.length === 0) return [];
    return orders.value.filter(order => {
        const matchType = order.type === activeType.value;
        const matchStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        const matchSearch = !search.value ||
            order.code.toLowerCase().includes(search.value.toLowerCase()) ||
            (order.customer && order.customer.toLowerCase().includes(search.value.toLowerCase())) ||
            (order.receiver && order.receiver.toLowerCase().includes(search.value.toLowerCase()));
        return matchType && matchStatus && matchSearch;
    });
});

const getTypeCount = (type) => {
    if (!orders.value) return 0;
    return orders.value.filter(o => o.type === type).length;
};

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipping: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        approved: 'bg-green-100 text-green-800',
        production: 'bg-orange-100 text-orange-800',
        confirmed: 'bg-blue-100 text-blue-800',
        waiting: 'bg-yellow-100 text-yellow-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
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
    selectedOrder.value = order;
    showDetail.value = true;
};

const exportExcel = async () => {
    try {
        await router.post('/admin/orders/export', {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Xuất file Excel thành công!');
            }
        });
    } catch (error) {
        alert('Có lỗi xảy ra khi xuất file');
    }
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    statusFilter.value = 'all';
    search.value = '';
    router.get(route('admin.orders.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        statusFilter.value = 'all';
        search.value = '';
    }
});

watch(() => props.initialOrders, (newOrders) => {
    orders.value = newOrders;
}, { immediate: true, deep: true });
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
                        placeholder="Tìm theo mã đơn, tên người đặt hoặc người nhận..."
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">MÃ ĐƠN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI ĐẶT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI NHẬN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG TIỀN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">HÌNH THỨC</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in filteredOrders" :key="order.id" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800">{{ order.code }}</td>
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
                                <td class="py-3 px-4 text-gray-600">{{ order.date }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(order.amount) }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2 py-1 rounded-full" :class="order.paymentClass">{{ order.payment }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <select
                                        v-model="order.status"
                                        @change="updateStatus(order)"
                                        class="text-xs px-2 py-1 rounded-full border border-gray-300 bg-white font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
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
                                <td class="py-3 px-4 text-center">
                                    <button
                                        @click="viewDetail(order)"
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                    <button
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="In đơn hàng"
                                    >
                                        In
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    Không có đơn hàng nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Hiển thị {{ filteredOrders.length }} đơn hàng</span>
                    <button
                        @click="exportExcel"
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết đơn hàng -->
        <div
            v-if="showDetail"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showDetail = false"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800">Chi tiết đơn hàng {{ selectedOrder?.code }}</h3>
                    <button
                        @click="showDetail = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Thông tin khách hàng -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Người đặt</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.customer || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.customer_phone || '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Người nhận</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.receiver || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.receiver_phone || '' }}</p>
                        </div>
                    </div>

                    <!-- Ngày + Hình thức -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Ngày đặt</p>
                            <p class="text-gray-600">{{ selectedOrder?.date }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Hình thức</p>
                            <p class="text-gray-600">{{ selectedOrder?.payment }}</p>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm - hiển thị rõ thành tiền từng dòng -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-2">Sản phẩm</p>
                        <div class="space-y-2 text-sm">
                            <div
                                v-for="(product, idx) in selectedOrder?.products"
                                :key="idx"
                                class="flex justify-between items-start"
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

                    <!-- Bảng tổng hợp chi phí (tính từ dữ liệu thực tế) -->
                    <div class="border-t border-gray-200 pt-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tạm tính</span>
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí vận chuyển</span>
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.shipping_fee) }}</span>
                        </div>
                        <div v-if="selectedOrder?.discount_amount > 0" class="flex justify-between">
                            <span class="text-gray-600">Giảm giá</span>
                            <span class="font-medium text-red-600">-{{ formatPrice(selectedOrder?.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold pt-2 border-t border-gray-200">
                            <span class="text-gray-800">Tổng cộng</span>
                            <span class="text-orange-600">{{ formatPrice(selectedOrder?.final_amount) }}</span>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Địa chỉ giao hàng</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder?.address }}</p>
                    </div>

                    <!-- Ghi chú -->
                    <div v-if="selectedOrder?.note" class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Ghi chú</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder.note }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
                    </button>
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                    >
                        Cập nhật trạng thái
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>

</style>


## Assistant:
06/29/2026 20:12

> **Thinking:**
> Tôi sẽ chỉnh giao diện quản lý màu sắc để thêm thanh tìm kiếm và các bộ lọc tương tự như giao diện đơn hàng. Dưới đây là code hoàn chỉnh với các thay đổi:
> 
> 1. Thêm thanh tìm kiếm theo tên màu hoặc mã hex
> 2. Thêm các tab/bộ lọc để sắp xếp theo màu sắc (nếu cần)
> 3. Cải thiện giao diện bảng với các nút thao tác rõ ràng
> 
> ```vue
> <script setup>
> import { ref, onMounted, computed } from 'vue'
> import axios from 'axios'
> import AdminLayout from '@/Layouts/AdminLayout.vue'
> import { Head } from '@inertiajs/vue3'
> 
> const props = defineProps({
>     colors: {
>         type: Array,
>         default: () => []
>     }
> })
> 
> // Sắp xếp màu sắc theo ID giảm dần
> const sortedColors = computed(() => {
>     return [...colors.value].sort((a, b) => b.id - a.id)
> })
> 
> // Tìm kiếm
> const search = ref('')
> const filterBy = ref('all') // 'all' hoặc các nhóm màu
> 
> const colors = ref(props.colors)
> const showModal = ref(false)
> const showDeleteModal = ref(false)
> const isEdit = ref(false)
> const selectedColor = ref(null)
> const isLoading = ref(false)
> const isSaving = ref(false)
> const errorMessage = ref('')
> const validationErrors = ref({})
> 
> const form = ref({
>     id: null,
>     name: '',
>     code: ''
> })
> 
> const displayCode = ref('#CCCCCC')
> 
> // Các nhóm màu để lọc
> const colorGroups = [
>     { value: 'all', label: 'Tất cả màu' },
>     { value: 'red', label: 'Đỏ - Cam', color: '#FF0000' },
>     { value: 'green', label: 'Xanh lá', color: '#008000' },
>     { value: 'blue', label: 'Xanh dương', color: '#0000FF' },
>     { value: 'yellow', label: 'Vàng', color: '#FFD700' },
>     { value: 'purple', label: 'Tím', color: '#800080' },
>     { value: 'pink', label: 'Hồng', color: '#FFC0CB' },
>     { value: 'brown', label: 'Nâu', color: '#8B4513' },
>     { value: 'gray', label: 'Xám - Trắng - Đen', color: '#808080' },
>     { value: 'other', label: 'Màu khác', color: '#CCCCCC' }
> ]
> 
> // Lọc màu theo nhóm
> const getColorGroup = (color) => {
>     if (!color.code) return 'other'
>     const code = color.code.toUpperCase()
>     // Đỏ - Cam
>     if (/^#FF/.test(code) && !/^#FFC/.test(code) && code !== '#FFFFFF') return 'red'
>     if (/^#(F[0-9A-F]{5}|[0-9A-F]{2}F[0-9A-F]{4})/i.test(code) && !/^#(FFF|FFFFFF)/.test(code)) return 'red'
>     // Xanh lá
>     if (/^#(0[0-9A-F]{5}|[0-9A-F]{2}[0-9A-F]F[0-9A-F]{3}|[0-9A-F]{2}[0-9A-F]{2}0{2})/i.test(code) && !/^#0{6}/.test(code)) return 'green'
>     // Xanh dương
>     if (/^#(0{2}[0-9A-F]{4}|[0-9A-F]{2}0{2}[0-9A-F]{2}|[0-9A-F]{2}[0-9A-F]{2}0{2})/i.test(code) && !/^#0{6}/.test(code)) return 'blue'
>     // Vàng
>     if (/^#F{0,1}F{0,1}[0-9A-F]{4}/i.test(code) && !/^#(FFF|FFFFFF)/.test(code)) return 'yellow'
>     // Tím
>     if (/^#8{1}[0-9A-F]{5}/i.test(code) || /^#9C27B0/.test(code) || /^#800080/.test(code)) return 'purple'
>     // Hồng
>     if (/^#FFC0CB/.test(code) || /^#F[0-9A-F]{5}3/.test(code) || /^#FF33F3/.test(code)) return 'pink'
>     // Nâu
>     if (/^#8B4513/.test(code) || /^#795548/.test(code) || /^#A0522D/.test(code)) return 'brown'
>     // Xám - Trắng - Đen
>     if (/^#(0{6}|[0-9A-F]{2}[0-9A-F]{2}[0-9A-F]{2})/i.test(code) && !/^#(0{6}|[0-9A-F]{3})/.test(code)) return 'gray'
>     return 'other'
> }
> 
> const filteredColors = computed(() => {
>     if (!colors.value || colors.value.length === 0) return []
>     return colors.value.filter(color => {
>         const matchSearch = !search.value ||
>             color.name.toLowerCase().includes(search.value.toLowerCase()) ||
>             (color.code && color.code.toLowerCase().includes(search.value.toLowerCase()))
>         const matchFilter = filterBy.value === 'all' || getColorGroup(color) === filterBy.value
>         return matchSearch && matchFilter
>     }).sort((a, b) => b.id - a.id)
> })
> 
> // Hàm kiểm tra mã hex
> const isHexCode = (value) => {
>     if (!value) return false
>     return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
> }
> 
> // Chuyển đổi tên màu -> mã hex
> const getColorCodeFromName = (name) => {
>     if (!name) return '#CCCCCC'
>     if (isHexCode(name)) return name.toUpperCase()
> 
>     const colorMap = {
>         'đen': '#000000', 'den': '#000000', 'black': '#000000',
>         'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
>         'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
>         'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
>         'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
>         'cam': '#FFA500', 'orange': '#FFA500',
>         'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
>         'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
>         'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
>         'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
>         'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
>         'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
>         'be': '#F5F5DC', 'beige': '#F5F5DC',
>         'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
>         'tím than': '#490C42', 'tim than': '#490C42'
>     }
>     const key = name.toLowerCase().trim()
>     return colorMap[key] || '#CCCCCC'
> }
> 
> // Chuyển đổi mã hex -> tên màu (chỉ gợi ý)
> const suggestColorNameFromCode = (code) => {
>     if (!code) return ''
>     
>     const codeMap = {
>         '#000000': 'Đen', '#000': 'Đen',
>         '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
>         '#808080': 'Xám',
>         '#FF0000': 'Đỏ', '#F00': 'Đỏ',
>         '#FFC0CB': 'Hồng',
>         '#FFA500': 'Cam',
>         '#FFD700': 'Vàng',
>         '#008000': 'Xanh lá',
>         '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
>         '#000080': 'Xanh navy',
>         '#800080': 'Tím',
>         '#8B4513': 'Nâu',
>         '#F5F5DC': 'Be',
>         '#C0C0C0': 'Bạc',
>         '#6200EE': 'Tím đậm',
>         '#9C27B0': 'Tím hồng',
>         '#490C42': 'Tím than',
>         '#FF5733': 'Cam đỏ',
>         '#E91E63': 'Hồng đậm',
>         '#2196F3': 'Xanh dương sáng',
>         '#00BCD4': 'Xanh cyan',
>         '#009688': 'Xanh lá cây',
>         '#4CAF50': 'Xanh lá',
>         '#FFC107': 'Vàng cam',
>         '#FF9800': 'Cam',
>         '#795548': 'Nâu đậm',
>         '#9E9E9E': 'Xám'
>     }
>     const upperCode = code.toUpperCase()
>     return codeMap[upperCode] || ''
> }
> 
> // Cập nhật mã hiển thị
> const updateDisplayCode = () => {
>     const inputName = form.value.name?.trim() || ''
>     const inputCode = form.value.code?.trim() || ''
>     
>     if (inputCode && isHexCode(inputCode)) {
>         displayCode.value = inputCode.toUpperCase()
>     } else if (inputName) {
>         const code = getColorCodeFromName(inputName)
>         displayCode.value = code
>     } else {
>         displayCode.value = '#CCCCCC'
>     }
> }
> 
> // Khi thay đổi picker
> const onColorPickerChange = (e) => {
>     const value = e.target.value
>     form.value.code = value
>     if (!form.value.name?.trim()) {
>         const suggested = suggestColorNameFromCode(value)
>         if (suggested && suggested !== 'Màu khác') {
>             form.value.name = suggested
>         }
>     }
>     updateDisplayCode()
> }
> 
> const formatDate = (date) => {
>     if (!date) return '---'
>     const d = new Date(date)
>     return d.toLocaleDateString('vi-VN')
> }
> 
> const fetchColors = async () => {
>     if (isLoading.value) return
>     isLoading.value = true
>     try {
>         const response = await axios.get('/admin/colors/data')
>         if (response.data && Array.isArray(response.data)) {
>             colors.value = response.data
>         }
>     } catch (error) {
>         console.error('Lỗi lấy danh sách màu:', error)
>     } finally {
>         isLoading.value = false
>     }
> }
> 
> const openCreateModal = () => {
>     isEdit.value = false
>     form.value = { id: null, name: '', code: '' }
>     displayCode.value = '#CCCCCC'
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const openEditModal = (color) => {
>     isEdit.value = true
>     form.value = { ...color }
>     const code = color.code || getColorCodeFromName(color.name)
>     displayCode.value = code
>     if (!form.value.code) form.value.code = code
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const saveColor = async () => {
>     if (!form.value.name?.trim() && !form.value.code?.trim()) {
>         errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
>         return
>     }
> 
>     if (isSaving.value) return
>     isSaving.value = true
>     errorMessage.value = ''
>     validationErrors.value = {}
> 
>     try {
>         let response
>         const payload = {
>             name: form.value.name?.trim() || null,
>             code: form.value.code?.trim() || null
>         }
> 
>         if (isEdit.value) {
>             response = await axios.put(`/admin/colors/${form.value.id}`, payload)
>         } else {
>             response = await axios.post('/admin/colors', payload)
>         }
>         
>         if (response.data?.success) {
>             await fetchColors()
>             showModal.value = false
>             form.value = { id: null, name: '', code: '' }
>             displayCode.value = '#CCCCCC'
>             errorMessage.value = ''
>         } else if (response.data?.message && typeof response.data.message === 'object') {
>             validationErrors.value = response.data.message
>             errorMessage.value = Object.values(response.data.message).flat()[0]
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi lưu màu:', error)
>         if (error.response?.data?.message && typeof error.response.data.message === 'object') {
>             validationErrors.value = error.response.data.message
>             errorMessage.value = Object.values(error.response.data.message).flat()[0]
>         } else {
>             errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>         }
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const confirmDelete = (color) => {
>     selectedColor.value = color
>     errorMessage.value = ''
>     showDeleteModal.value = true
> }
> 
> const deleteColor = async () => {
>     if (!selectedColor.value) return
>     if (isSaving.value) return
>     
>     isSaving.value = true
>     errorMessage.value = ''
>     
>     try {
>         const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
>         if (response.data?.success) {
>             await fetchColors()
>             showDeleteModal.value = false
>             selectedColor.value = null
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi xóa màu:', error)
>         errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const closeModal = () => {
>     showModal.value = false
>     showDeleteModal.value = false
>     selectedColor.value = null
>     form.value = { id: null, name: '', code: '' }
>     errorMessage.value = ''
>     validationErrors.value = {}
>     isSaving.value = false
> }
> 
> const handleOverlayClick = (e) => {
>     if (e.target === e.currentTarget) closeModal()
> }
> 
> const clearSearch = () => {
>     search.value = ''
> }
> 
> onMounted(() => {
>     if (colors.value.length === 0) fetchColors()
> })
> </script>
> 
> <template>
>     <Head title="Quản lý màu sắc - BigBag Admin" />
>     <AdminLayout>
>         <div class="p-4 md:p-8">
>             <!-- Header -->
>             <div class="mb-6 flex justify-between items-center">
>                 <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
>                 <button 
>                     @click="openCreateModal" 
>                     class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition flex items-center gap-2"
>                 >
>                     <span class="text-lg">+</span> Thêm màu sắc mới
>                 </button>
>             </div>
> 
>             <!-- Bộ lọc nhóm màu -->
>             <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-3">
>                 <button
>                     v-for="group in colorGroups"
>                     :key="group.value"
>                     @click="filterBy = group.value"
>                     class="px-4 py-2 text-sm font-medium rounded-full transition-all flex items-center gap-2"
>                     :class="filterBy === group.value ? 'bg-orange-600 text-white shadow-md' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
>                 >
>                     <span 
>                         v-if="group.color" 
>                         class="w-3 h-3 rounded-full inline-block border border-gray-300"
>                         :style="{ backgroundColor: group.color }"
>                     ></span>
>                     {{ group.label }}
>                 </button>
>             </div>
> 
>             <!-- Thanh tìm kiếm -->
>             <div class="mb-4">
>                 <div class="relative max-w-md">
>                     <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
>                     <input
>                         v-model="search"
>                         type="text"
>                         placeholder="Tìm theo tên màu hoặc mã hex..."
>                         class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
>                     >
>                     <button 
>                         v-if="search" 
>                         @click="clearSearch"
>                         class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
>                     >
>                         ✕
>                     </button>
>                 </div>
>             </div>
> 
>             <!-- Danh sách màu -->
>             <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
>                 <div class="overflow-x-auto">
>                     <table class="w-full min-w-[700px]">
>                         <thead>
>                             <tr class="bg-gray-50 border-b border-gray-200">
>                                 <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
>                                 <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
>                                 <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
>                                 <th class="text-left p-4 font-semibold text-gray-700">Nhóm</th>
>                                 <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
>                                 <th class="text-center p-4 font-semibold text-gray-700 w-36">Thao tác</th>
>                             </tr>
>                         </thead>
>                         <tbody>
>                             <tr 
>                                 v-for="(color, index) in filteredColors" 
>                                 :key="color.id" 
>                                 class="border-b border-gray-100 hover:bg-orange-50 transition-colors"
>                             >
>                                 <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
>                                 <td class="p-4">
>                                     <div class="flex items-center gap-3">
>                                         <div 
>                                             class="w-8 h-8 rounded-full border border-gray-300 shadow-sm" 
>                                             :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
>                                         ></div>
>                                         <span class="font-medium text-gray-800">{{ color.name }}</span>
>                                     </div>
>                                 </td>
>                                 <td class="p-4 text-gray-600 text-sm font-mono">{{ color.code || '—' }}</td>
>                                 <td class="p-4">
>                                     <span 
>                                         class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700"
>                                     >
>                                         {{ colorGroups.find(g => g.value === getColorGroup(color))?.label || 'Màu khác' }}
>                                     </span>
>                                 </td>
>                                 <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
>                                 <td class="p-4 text-center">
>                                     <div class="flex items-center justify-center gap-1">
>                                         <button 
>                                             @click="openEditModal(color)" 
>                                             class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded hover:bg-blue-50 text-sm"
>                                         >
>                                             Sửa
>                                         </button>
>                                         <button 
>                                             @click="confirmDelete(color)" 
>                                             class="text-red-600 hover:text-red-800 px-3 py-1 rounded hover:bg-red-50 text-sm"
>                                         >
>                                             Xóa
>                                         </button>
>                                     </div>
>                                 </td>
>                             </tr>
>                             <tr v-if="filteredColors.length === 0 && !isLoading">
>                                 <td colspan="6" class="p-8 text-center text-gray-400">
>                                     <div class="flex flex-col items-center gap-2">
>                                         <span class="text-4xl">🎨</span>
>                                         <p>Không tìm thấy màu sắc nào</p>
>                                         <p class="text-sm text-gray-400">Thử thay đổi bộ lọc hoặc tìm kiếm khác</p>
>                                     </div>
>                                 </td>
>                             </tr>
>                         </tbody>
>                     </table>
>                 </div>
> 
>                 <!-- Footer -->
>                 <div class="p-3 border-t border-gray-200 flex justify-between items-center bg-gray-50">
>                     <span class="text-sm text-gray-500">Hiển thị {{ filteredColors.length }} màu sắc</span>
>                     <button 
>                         @click="fetchColors" 
>                         class="text-orange-600 hover:text-orange-700 text-sm flex items-center gap-1"
>                     >
>                         <span class="material-symbols-outlined text-base">refresh</span> Làm mới
>                     </button>
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Thêm/Sửa -->
>         <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="handleOverlayClick">
>             <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
>                 <div class="flex justify-between items-center mb-4">
>                     <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
>                     <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
>                 </div>
> 
>                 <div class="space-y-4">
>                     <!-- Tên màu -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
>                         <input 
>                             v-model="form.name" 
>                             type="text" 
>                             class="w-full border rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500" 
>                             :class="{ 'border-red-500': validationErrors.name }"
>                             placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
>                             @input="updateDisplayCode"
>                         >
>                         <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
>                     </div>
> 
>                     <!-- Color Picker + Mã hex -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
>                         <div class="flex items-center gap-4">
>                             <input 
>                                 type="color" 
>                                 :value="form.code || '#CCCCCC'"
>                                 @input="onColorPickerChange"
>                                 class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition"
>                             >
>                             <div class="flex-1">
>                                 <div class="text-sm font-medium text-gray-600">Mã hex:</div>
>                                 <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
>                             </div>
>                         </div>
>                         <!-- Ô nhập mã hex thủ công -->
>                         <div class="mt-2">
>                             <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
>                             <input 
>                                 v-model="form.code" 
>                                 type="text" 
>                                 class="w-full border rounded-lg p-2 font-mono focus:ring-orange-500 focus:border-orange-500" 
>                                 :class="{ 'border-red-500': validationErrors.code }"
>                                 placeholder="#dc2626, #FFA500, #490C42..." 
>                                 @input="updateDisplayCode"
>                             >
>                             <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
>                         </div>
>                     </div>
> 
>                     <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
>                         <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                     </div>
>                 </div>
> 
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
>                     <button @click="saveColor" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Xóa -->
>         <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="handleOverlayClick">
>             <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
>                 <div class="flex justify-between items-center mb-4">
>                     <h3 class="text-xl font-bold text-gray-800">Xác nhận xóa</h3>
>                     <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
>                 </div>
>                 <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
>                 <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
>                     <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                 </div>
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
>                     <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> 
> <style scoped>
> @keyframes spin {
>     from { transform: rotate(0deg); }
>     to { transform: rotate(360deg); }
> }
> .animate-spin { animation: spin 1s linear infinite; }
> </style>
> ```
> 
> **Các thay đổi chính:**
> 
> 1. **Thanh tìm kiếm**: Thêm input tìm kiếm với placeholder và nút xóa, giống như đơn hàng.
> 2. **Bộ lọc nhóm màu**: Thêm các button lọc theo nhóm màu (đỏ, xanh lá, xanh dương, vàng, tím, hồng, nâu, xám/trắng/đen, màu khác). Mỗi button hiển thị màu đại diện.
> 3. **Cải thiện giao diện**: 
>    - Header có nút "Thêm màu sắc mới" bên phải
>    - Bảng có thêm cột "Nhóm" để hiển thị nhóm màu
>    - Footer hiển thị số lượng màu và nút "Làm mới"
>    - Các màu được hiển thị dạng tròn (rounded-full)
>    - Hover row có hiệu ứng màu cam nhẹ
> 4. **Modal**: Giao diện giống với đơn hàng, có nút đóng ở góc.
> 
> Hàm `getColorGroup` phân loại màu dựa trên mã hex để lọc theo nhóm. Bạn có thể điều chỉnh logic nhóm nếu cần.


## User:
06/29/2026 20:17

giữ giao diện cũ cho tôi chỉ thêm thanh tìm kiếm , tôi không cần Bộ lọc nhóm màu , cũng không cần thêm các thông tin trong bảng hiển thị cho tôi


## Assistant:
06/29/2026 20:17

> **Thinking:**
> Chúng ta chỉ cần thêm thanh tìm kiếm vào giao diện quản lý màu sắc hiện tại, giữ nguyên các cột và chức năng. Không cần thêm bộ lọc nhóm màu hay thông tin gì khác.
> 
> Tôi sẽ chỉnh sửa component Vue của màu sắc để thêm một ô input tìm kiếm đơn giản, lọc danh sách màu theo tên hoặc mã hex.
> 
> Dưới đây là code hoàn chỉnh đã được cập nhật, chỉ thêm thanh tìm kiếm và logic lọc. Các phần khác giữ nguyên.
> 
> ```vue
> <script setup>
> import { ref, onMounted, computed } from 'vue'
> import axios from 'axios'
> import AdminLayout from '@/Layouts/AdminLayout.vue'
> import { Head } from '@inertiajs/vue3'
> 
> const props = defineProps({
>     colors: {
>         type: Array,
>         default: () => []
>     }
> })
> 
> // State
> const colors = ref(props.colors)
> const search = ref('')
> const showModal = ref(false)
> const showDeleteModal = ref(false)
> const isEdit = ref(false)
> const selectedColor = ref(null)
> const isLoading = ref(false)
> const isSaving = ref(false)
> const errorMessage = ref('')
> const validationErrors = ref({})
> 
> const form = ref({
>     id: null,
>     name: '',
>     code: ''
> })
> 
> const displayCode = ref('#CCCCCC')
> 
> // Computed: lọc màu theo từ khóa tìm kiếm
> const filteredColors = computed(() => {
>     if (!search.value.trim()) return colors.value
>     const keyword = search.value.toLowerCase().trim()
>     return colors.value.filter(color => 
>         color.name.toLowerCase().includes(keyword) ||
>         (color.code && color.code.toLowerCase().includes(keyword))
>     )
> })
> 
> // Sắp xếp theo id giảm dần
> const sortedColors = computed(() => {
>     return [...filteredColors.value].sort((a, b) => b.id - a.id)
> })
> 
> // Hàm kiểm tra mã hex
> const isHexCode = (value) => {
>     if (!value) return false
>     return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
> }
> 
> // Chuyển đổi tên màu -> mã hex
> const getColorCodeFromName = (name) => {
>     if (!name) return '#CCCCCC'
>     if (isHexCode(name)) return name.toUpperCase()
> 
>     const colorMap = {
>         'đen': '#000000', 'den': '#000000', 'black': '#000000',
>         'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
>         'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
>         'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
>         'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
>         'cam': '#FFA500', 'orange': '#FFA500',
>         'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
>         'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
>         'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
>         'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
>         'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
>         'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
>         'be': '#F5F5DC', 'beige': '#F5F5DC',
>         'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
>         'tím than': '#490C42', 'tim than': '#490C42'
>     }
>     const key = name.toLowerCase().trim()
>     return colorMap[key] || '#CCCCCC'
> }
> 
> // Chuyển đổi mã hex -> tên màu (chỉ gợi ý)
> const suggestColorNameFromCode = (code) => {
>     if (!code) return ''
>     
>     const codeMap = {
>         '#000000': 'Đen', '#000': 'Đen',
>         '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
>         '#808080': 'Xám',
>         '#FF0000': 'Đỏ', '#F00': 'Đỏ',
>         '#FFC0CB': 'Hồng',
>         '#FFA500': 'Cam',
>         '#FFD700': 'Vàng',
>         '#008000': 'Xanh lá',
>         '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
>         '#000080': 'Xanh navy',
>         '#800080': 'Tím',
>         '#8B4513': 'Nâu',
>         '#F5F5DC': 'Be',
>         '#C0C0C0': 'Bạc',
>         '#6200EE': 'Tím đậm',
>         '#9C27B0': 'Tím hồng',
>         '#490C42': 'Tím than',
>         '#FF5733': 'Cam đỏ',
>         '#E91E63': 'Hồng đậm',
>         '#2196F3': 'Xanh dương sáng',
>         '#00BCD4': 'Xanh cyan',
>         '#009688': 'Xanh lá cây',
>         '#4CAF50': 'Xanh lá',
>         '#FFC107': 'Vàng cam',
>         '#FF9800': 'Cam',
>         '#795548': 'Nâu đậm',
>         '#9E9E9E': 'Xám'
>     }
>     const upperCode = code.toUpperCase()
>     return codeMap[upperCode] || ''
> }
> 
> // Cập nhật mã hiển thị
> const updateDisplayCode = () => {
>     const inputName = form.value.name?.trim() || ''
>     const inputCode = form.value.code?.trim() || ''
>     
>     if (inputCode && isHexCode(inputCode)) {
>         displayCode.value = inputCode.toUpperCase()
>     } else if (inputName) {
>         const code = getColorCodeFromName(inputName)
>         displayCode.value = code
>     } else {
>         displayCode.value = '#CCCCCC'
>     }
> }
> 
> // Khi thay đổi picker
> const onColorPickerChange = (e) => {
>     const value = e.target.value
>     form.value.code = value
>     if (!form.value.name?.trim()) {
>         const suggested = suggestColorNameFromCode(value)
>         if (suggested && suggested !== 'Màu khác') {
>             form.value.name = suggested
>         }
>     }
>     updateDisplayCode()
> }
> 
> const formatDate = (date) => {
>     if (!date) return '---'
>     const d = new Date(date)
>     return d.toLocaleDateString('vi-VN')
> }
> 
> const fetchColors = async () => {
>     if (isLoading.value) return
>     isLoading.value = true
>     try {
>         const response = await axios.get('/admin/colors/data')
>         if (response.data && Array.isArray(response.data)) {
>             colors.value = response.data
>         }
>     } catch (error) {
>         console.error('Lỗi lấy danh sách màu:', error)
>     } finally {
>         isLoading.value = false
>     }
> }
> 
> const openCreateModal = () => {
>     isEdit.value = false
>     form.value = { id: null, name: '', code: '' }
>     displayCode.value = '#CCCCCC'
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const openEditModal = (color) => {
>     isEdit.value = true
>     form.value = { ...color }
>     const code = color.code || getColorCodeFromName(color.name)
>     displayCode.value = code
>     if (!form.value.code) form.value.code = code
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const saveColor = async () => {
>     if (!form.value.name?.trim() && !form.value.code?.trim()) {
>         errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
>         return
>     }
> 
>     if (isSaving.value) return
>     isSaving.value = true
>     errorMessage.value = ''
>     validationErrors.value = {}
> 
>     try {
>         let response
>         const payload = {
>             name: form.value.name?.trim() || null,
>             code: form.value.code?.trim() || null
>         }
> 
>         if (isEdit.value) {
>             response = await axios.put(`/admin/colors/${form.value.id}`, payload)
>         } else {
>             response = await axios.post('/admin/colors', payload)
>         }
>         
>         if (response.data?.success) {
>             await fetchColors()
>             showModal.value = false
>             form.value = { id: null, name: '', code: '' }
>             displayCode.value = '#CCCCCC'
>             errorMessage.value = ''
>         } else if (response.data?.message && typeof response.data.message === 'object') {
>             validationErrors.value = response.data.message
>             errorMessage.value = Object.values(response.data.message).flat()[0]
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi lưu màu:', error)
>         if (error.response?.data?.message && typeof error.response.data.message === 'object') {
>             validationErrors.value = error.response.data.message
>             errorMessage.value = Object.values(error.response.data.message).flat()[0]
>         } else {
>             errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>         }
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const confirmDelete = (color) => {
>     selectedColor.value = color
>     errorMessage.value = ''
>     showDeleteModal.value = true
> }
> 
> const deleteColor = async () => {
>     if (!selectedColor.value) return
>     if (isSaving.value) return
>     
>     isSaving.value = true
>     errorMessage.value = ''
>     
>     try {
>         const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
>         if (response.data?.success) {
>             await fetchColors()
>             showDeleteModal.value = false
>             selectedColor.value = null
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi xóa màu:', error)
>         errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const closeModal = () => {
>     showModal.value = false
>     showDeleteModal.value = false
>     selectedColor.value = null
>     form.value = { id: null, name: '', code: '' }
>     errorMessage.value = ''
>     validationErrors.value = {}
>     isSaving.value = false
> }
> 
> const handleOverlayClick = (e) => {
>     if (e.target === e.currentTarget) closeModal()
> }
> 
> onMounted(() => {
>     if (colors.value.length === 0) fetchColors()
> })
> </script>
> 
> <template>
>     <Head title="Quản lý màu sắc" />
>     <AdminLayout>
>         <div class="p-6">
>             <div class="mb-6">
>                 <h1 class="text-2xl font-bold text-gray-800">Quản lý màu sắc</h1>
>             </div>
> 
>             <!-- Thanh tìm kiếm và nút thêm -->
>             <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
>                 <div class="relative max-w-sm w-full">
>                     <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
>                     <input
>                         v-model="search"
>                         type="text"
>                         placeholder="Tìm theo tên màu hoặc mã hex..."
>                         class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
>                     >
>                 </div>
>                 <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition whitespace-nowrap">
>                     + Thêm màu sắc mới
>                 </button>
>             </div>
> 
>             <div v-if="isLoading && colors.length === 0" class="text-center py-8">
>                 <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
>                 <p class="mt-2 text-gray-500">Đang tải...</p>
>             </div>
> 
>             <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
>                 <table class="w-full min-w-[600px]">
>                     <thead class="bg-gray-50 border-b border-gray-200">
>                         <tr>
>                             <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
>                             <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
>                             <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
>                             <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
>                             <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
>                         </tr>
>                     </thead>
>                     <tbody>
>                         <tr v-for="(color, index) in sortedColors" :key="color.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
>                             <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
>                             <td class="p-4">
>                                 <div class="flex items-center gap-3">
>                                     <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
>                                     <span class="font-medium text-gray-700">{{ color.name }}</span>
>                                 </div>
>                             </td>
>                             <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
>                             <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
>                             <td class="p-4 text-center">
>                                 <div class="flex items-center justify-center gap-2">
>                                     <button @click="openEditModal(color)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Sửa</button>
>                                     <button @click="confirmDelete(color)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">Xóa</button>
>                                 </div>
>                             </td>
>                         </tr>
>                         <tr v-if="sortedColors.length === 0 && !isLoading">
>                             <td colspan="5" class="p-8 text-center text-gray-400">
>                                 {{ search ? 'Không tìm thấy màu nào phù hợp' : 'Chưa có màu sắc nào' }}
>                             </td>
>                         </tr>
>                     </tbody>
>                 </table>
>                 <!-- Hiển thị số lượng kết quả -->
>                 <div v-if="search && filteredColors.length > 0" class="p-3 border-t border-gray-200 text-sm text-gray-500">
>                     Tìm thấy {{ filteredColors.length }} màu
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Thêm/Sửa -->
>         <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
>             <div class="bg-white rounded-lg w-full max-w-md p-6">
>                 <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
>                 <div class="space-y-4">
>                     <!-- Tên màu -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
>                         <input 
>                             v-model="form.name" 
>                             type="text" 
>                             class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
>                             :class="{ 'border-red-500': validationErrors.name }"
>                             placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
>                             @input="updateDisplayCode"
>                         >
>                         <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
>                     </div>
> 
>                     <!-- Color Picker + Mã hex -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
>                         <div class="flex items-center gap-4">
>                             <input 
>                                 type="color" 
>                                 :value="form.code || '#CCCCCC'"
>                                 @input="onColorPickerChange"
>                                 class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
>                             >
>                             <div class="flex-1">
>                                 <div class="text-sm font-medium text-gray-600">Mã hex:</div>
>                                 <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
>                             </div>
>                         </div>
>                         <div class="mt-2">
>                             <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
>                             <input 
>                                 v-model="form.code" 
>                                 type="text" 
>                                 class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
>                                 :class="{ 'border-red-500': validationErrors.code }"
>                                 placeholder="#dc2626, #FFA500, #490C42..." 
>                                 @input="updateDisplayCode"
>                             >
>                             <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
>                         </div>
>                     </div>
> 
>                     <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
>                         <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                     </div>
>                 </div>
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
>                     <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Xóa -->
>         <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
>             <div class="bg-white rounded-lg w-full max-w-md p-6">
>                 <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
>                 <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
>                 <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
>                     <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                 </div>
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
>                     <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> 
> <style scoped>
> @keyframes spin {
>     from { transform: rotate(0deg); }
>     to { transform: rotate(360deg); }
> }
> .animate-spin { animation: spin 1s linear infinite; }
> </style>
> ```
> 
> **Các thay đổi chính:**
> - Thêm `search` ref và `filteredColors` computed để lọc theo tên hoặc mã hex.
> - Thêm thanh tìm kiếm ở phía trên, cùng hàng với nút "Thêm màu sắc mới".
> - Hiển thị số lượng kết quả tìm thấy khi có từ khóa.
> - Giữ nguyên toàn bộ các cột, modal, color picker và các chức năng khác.


## User:
06/29/2026 20:20

chỉnh giao diện color , dựa vào cấu trúc giao diện code bên dưới cho tôi <script setup>
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

// Lọc danh mục
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
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">HÌNH ẢNH</th>
                                <th class="px-4 py-3 text-left">TÊN</th>
                                <th class="px-4 py-3 text-left">SLUG</th>
                                <th class="px-4 py-3 text-left">MÔ TẢ</th>
                                <th class="px-4 py-3 text-center">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cat in filteredCategories" :key="cat.id" class="border-t hover:bg-orange-50">
                                <td class="px-4 py-3">{{ cat.id }}</td>
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
                                <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
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


## Assistant:
06/29/2026 20:20

> **Thinking:**
> Tôi cần chỉnh giao diện color dựa trên cấu trúc code danh mục mà người dùng cung cấp. Người dùng yêu cầu:
> 
> - Giữ giao diện cũ của color (tức là bảng hiển thị các cột: STT, Màu sắc, Mã hex, Ngày tạo, Thao tác)
> - Chỉ thêm thanh tìm kiếm (search) vào phía trên bảng, giống như trong code danh mục.
> - Không cần bộ lọc nhóm màu, không cần thêm thông tin trong bảng.
> 
> Vì vậy tôi sẽ sửa lại template và script của component Color để thêm thanh tìm kiếm, dựa trên phong cách của code danh mục. Tôi sẽ giữ nguyên các phần khác: modal thêm/sửa, modal xóa, color picker, v.v.
> 
> Cụ thể:
> - Thêm biến `search` trong script.
> - Thêm computed `filteredColors` để lọc theo tên hoặc mã hex.
> - Thêm thanh tìm kiếm HTML giống như trong code danh mục (với icon search, input, class tương tự).
> - Giữ nguyên bảng và các cột.
> 
> Tôi sẽ cung cấp code hoàn chỉnh cho component Color đã được sửa.
> 
> Lưu ý: Trong code hiện tại có cả phần PHP Controller và Vue component. Người dùng muốn chỉnh giao diện Vue, tôi sẽ chỉ đưa phần Vue component đã sửa.
> 
> Tôi sẽ giữ nguyên phần script và style, chỉ thêm search và filteredColors.
> 
> Tôi sẽ đưa ra code đầy đủ cho component Vue.

Dưới đây là code hoàn chỉnh cho component quản lý màu sắc, được chỉnh sửa để thêm thanh tìm kiếm theo phong cách giao diện danh mục bạn đã cung cấp. Giữ nguyên bảng hiển thị các cột: STT, Màu sắc, Mã hex, Ngày tạo, Thao tác. Không thêm bộ lọc hay cột nào khác.

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// State
const colors = ref(props.colors)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần (giữ nguyên)
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    // Nếu tên trống, tự động gợi ý tên
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Bảng hiển thị màu sắc -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 w-16">STT</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Màu sắc</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Mã hex</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Ngày tạo</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(color, index) in sortedColors" :key="color.id" class="border-t border-gray-100 hover:bg-orange-50 transition">
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
                                        <span class="font-medium text-gray-700">{{ color.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="openEditModal(color)" class="text-green-600 hover:bg-green-100 px-2 py-1 rounded">Sửa</button>
                                    <button @click="confirmDelete(color)" class="text-red-600 hover:bg-red-100 px-2 py-1 rounded ml-1">Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="sortedColors.length === 0 && !isLoading">
                                <td colspan="5" class="text-center py-8 text-gray-500">Không có màu sắc nào</td>
                            </tr>
                            <tr v-if="isLoading && colors.length === 0">
                                <td colspan="5" class="text-center py-8 text-gray-500">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="space-y-4">
                    <!-- Tên màu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <!-- Color Picker + Mã hex -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <!-- Ô nhập mã hex thủ công -->
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg px-3 py-2 font-mono focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

**Những thay đổi chính:**
- Thêm biến `search` và computed `filteredColors` để lọc theo tên hoặc mã hex.
- Thêm thanh tìm kiếm (với icon và kiểu dáng giống như trong code danh mục) ở phía trên bảng.
- Giữ nguyên toàn bộ cấu trúc bảng và các cột hiện có (STT, Màu sắc, Mã hex, Ngày tạo, Thao tác).
- Không thêm bộ lọc hay cột phụ nào khác.
- Đổi màu chủ đạo sang cam (`orange-600`, `hover:orange-50`, v.v.) để đồng bộ với giao diện danh mục bạn đã cung cấp (nếu bạn muốn giữ nguyên màu `primary` thì có thể đổi lại).

Bạn có thể copy toàn bộ code này và thay thế component hiện tại của mình.


## User:
06/29/2026 20:34

<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{

    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }


    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);


            if (!empty($validated['name']) && !empty($validated['code'])) {

                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {

                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {

                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {

                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
               
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu và mã hex!'
                ], 422);
            }


            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([  //required là không được null
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);


            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }


            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        
        $code = strtoupper(trim($code));
        

        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        

        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        
        return '#CCCCCC';
    }


    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }


    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        
        $code = strtoupper(trim($code));
        
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#490c42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen'
        ];
        

        $expandedMap = [
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        
        $fullMap = array_merge($codeMap, $expandedMap);
        
        return $fullMap[$code] ?? 'Màu khác';
    }
}

Chỉnh lại cho tôi chức năng thêm/sửa không được trùng tên/ trùng mã


## Assistant:
06/29/2026 20:34

> **Thinking:**
> Tôi sẽ chỉnh sửa controller để thêm kiểm tra trùng tên và trùng mã khi thêm/sửa màu sắc.
> 
> ```php
> <?php
> 
> namespace App\Http\Controllers\Admin;
> 
> use App\Http\Controllers\Controller;
> use App\Models\Color;
> use Illuminate\Http\Request;
> use Inertia\Inertia;
> use Illuminate\Support\Facades\Log;
> use Illuminate\Validation\Rule;
> 
> class ColorController extends Controller
> {
> 
>     public function index()
>     {
>         $colors = Color::orderBy('id', 'desc')->get();
>         return Inertia::render('Admin/Colors', [
>             'colors' => $colors
>         ]);
>     }
> 
>     public function getColors()
>     {
>         try {
>             $colors = Color::orderBy('id', 'desc')->get();
>             return response()->json($colors);
>         } catch (\Exception $e) {
>             Log::error('Lỗi getColors: ' . $e->getMessage());
>             return response()->json(['error' => $e->getMessage()], 500);
>         }
>     }
> 
>     public function store(Request $request)
>     {
>         try {
>             $validated = $request->validate([
>                 'name' => 'nullable|string|max:255',
>                 'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
>             ]);
> 
>             // Xử lý logic nhập liệu
>             if (!empty($validated['name']) && !empty($validated['code'])) {
>                 $validated['code'] = $this->normalizeHexCode($validated['code']);
>             } elseif (!empty($validated['name']) && empty($validated['code'])) {
>                 $validated['code'] = $this->getColorCodeFromName($validated['name']);
>             } elseif (!empty($validated['code']) && empty($validated['name'])) {
>                 $code = $this->normalizeHexCode($validated['code']);
>                 $validated['code'] = $code;
>                 $generatedName = $this->getColorNameFromCode($code);
>                 if ($generatedName && $generatedName !== 'Màu khác') {
>                     $validated['name'] = $generatedName;
>                 } else {
>                     return response()->json([
>                         'success' => false,
>                         'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
>                     ], 422);
>                 }
>             } else {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng tên
>             if (Color::where('name', $validated['name'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng mã (nếu mã đã tồn tại)
>             if (Color::where('code', $validated['code'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             $color = Color::create($validated);
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Thêm màu sắc thành công!',
>                 'data' => $color
>             ], 201);
>             
>         } catch (\Illuminate\Validation\ValidationException $e) {
>             return response()->json([
>                 'success' => false,
>                 'message' => $e->errors()
>             ], 422);
>         } catch (\Exception $e) {
>             Log::error('Lỗi store color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     public function update(Request $request, $id)
>     {
>         try {
>             $color = Color::findOrFail($id);
>             
>             $validated = $request->validate([
>                 'name' => ['nullable', 'string', 'max:255'],
>                 'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
>             ]);
> 
>             // Xử lý logic nhập liệu
>             if (!empty($validated['name']) && !empty($validated['code'])) {
>                 $validated['code'] = $this->normalizeHexCode($validated['code']);
>             } elseif (!empty($validated['name']) && empty($validated['code'])) {
>                 $validated['code'] = $this->getColorCodeFromName($validated['name']);
>             } elseif (!empty($validated['code']) && empty($validated['name'])) {
>                 $code = $this->normalizeHexCode($validated['code']);
>                 $generatedName = $this->getColorNameFromCode($code);
>                 if ($generatedName && $generatedName !== 'Màu khác') {
>                     $validated['name'] = $generatedName;
>                 } else {
>                     $validated['name'] = $color->name; 
>                 }
>                 $validated['code'] = $code;
>             } else {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng tên (không tính chính nó)
>             if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng mã (không tính chính nó)
>             if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             $color->update($validated);
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Cập nhật màu sắc thành công!',
>                 'data' => $color
>             ]);
>             
>         } catch (\Illuminate\Validation\ValidationException $e) {
>             return response()->json([
>                 'success' => false,
>                 'message' => $e->errors()
>             ], 422);
>         } catch (\Exception $e) {
>             Log::error('Lỗi update color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     public function destroy($id)
>     {
>         try {
>             $color = Color::findOrFail($id);
>             
>             $variantCount = $color->productVariants()->count();
>             
>             if ($variantCount > 0) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
>                 ], 400);
>             }
> 
>             $color->delete();
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Xóa màu sắc thành công!'
>             ]);
>             
>         } catch (\Exception $e) {
>             Log::error('Lỗi delete color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     private function normalizeHexCode($code)
>     {
>         if (empty($code)) return '#CCCCCC';
>         
>         $code = strtoupper(trim($code));
>         
>         if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
>             $r = $matches[1][0];
>             $g = $matches[1][1];
>             $b = $matches[1][2];
>             return '#' . $r . $r . $g . $g . $b . $b;
>         }
>         
>         if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
>             return $code;
>         }
>         
>         return '#CCCCCC';
>     }
> 
>     private function getColorCodeFromName($name)
>     {
>         if (empty($name)) return '#CCCCCC';
>         
>         $colorMap = [
>             'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
>             'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
>             'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
>             'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
>             'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
>             'cam' => '#FFA500', 'orange' => '#FFA500',
>             'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
>             'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
>             'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
>             'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
>             'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
>             'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
>             'be' => '#F5F5DC', 'beige' => '#F5F5DC',
>             'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
>         ];
>         
>         $key = strtolower(trim($name));
>         return $colorMap[$key] ?? '#CCCCCC';
>     }
> 
>     private function getColorNameFromCode($code)
>     {
>         if (empty($code)) return 'Màu khác';
>         
>         $code = strtoupper(trim($code));
>         
>         $codeMap = [
>             '#000000' => 'Đen',
>             '#FFFFFF' => 'Trắng',
>             '#808080' => 'Xám',
>             '#FF0000' => 'Đỏ',
>             '#FFC0CB' => 'Hồng',
>             '#FFA500' => 'Cam',
>             '#FFD700' => 'Vàng',
>             '#008000' => 'Xanh lá',
>             '#0000FF' => 'Xanh dương',
>             '#000080' => 'Xanh navy',
>             '#800080' => 'Tím',
>             '#8B4513' => 'Nâu',
>             '#F5F5DC' => 'Be',
>             '#C0C0C0' => 'Bạc',
>             '#CCCCCC' => 'Xám nhạt',
>             '#6200EE' => 'Tím đậm',
>             '#9C27B0' => 'Tím hồng',
>             '#03DAC6' => 'Xanh ngọc',
>             '#018786' => 'Xanh rêu',
>             '#490C42' => 'Tím than',
>             '#490c42' => 'Tím than',
>             '#FF5733' => 'Cam đỏ',
>             '#33FF57' => 'Xanh lá sáng',
>             '#3357FF' => 'Xanh dương đậm',
>             '#F333FF' => 'Hồng tím',
>             '#FF33F3' => 'Hồng cánh sen'
>         ];
>         
>         $expandedMap = [
>             '#E91E63' => 'Hồng đậm',
>             '#9C27B0' => 'Tím',
>             '#673AB7' => 'Tím đậm',
>             '#3F51B5' => 'Xanh dương',
>             '#2196F3' => 'Xanh dương sáng',
>             '#00BCD4' => 'Xanh cyan',
>             '#009688' => 'Xanh lá cây',
>             '#4CAF50' => 'Xanh lá',
>             '#8BC34A' => 'Xanh lá nhạt',
>             '#CDDC39' => 'Xanh vàng',
>             '#FFEB3B' => 'Vàng chanh',
>             '#FFC107' => 'Vàng cam',
>             '#FF9800' => 'Cam',
>             '#FF5722' => 'Cam đỏ',
>             '#795548' => 'Nâu',
>             '#9E9E9E' => 'Xám',
>             '#607D8B' => 'Xám xanh'
>         ];
>         
>         $fullMap = array_merge($codeMap, $expandedMap);
>         
>         return $fullMap[$code] ?? 'Màu khác';
>     }
> }
> ```
> 
> **Các thay đổi chính:**
> 1. **Thêm kiểm tra trùng mã** trong `store`: sau khi đã chuẩn hóa mã, kiểm tra xem `code` đã tồn tại trong database chưa. Nếu có, trả về lỗi.
> 2. **Thêm kiểm tra trùng mã** trong `update`: kiểm tra nếu `code` thay đổi và đã tồn tại (không tính chính bản ghi đang sửa) thì báo lỗi.
> 3. Giữ nguyên kiểm tra trùng tên đã có (có sẵn).
> 
> Điều này đảm bảo cả tên và mã hex đều là duy nhất trong hệ thống.


## User:
06/29/2026 20:39

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{

    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }


    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Kiểm tra trùng tên
            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (nếu mã đã tồn tại)
            if (Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Kiểm tra trùng tên (không tính chính nó)
            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (không tính chính nó)
            if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        
        $code = strtoupper(trim($code));
        
        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        
        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        
        return '#CCCCCC';
    }


    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }


    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        
        $code = strtoupper(trim($code));
        
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#490c42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen'
        ];
        
        $expandedMap = [
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        
        $fullMap = array_merge($codeMap, $expandedMap);
        
        return $fullMap[$code] ?? 'Màu khác';
    }
}
Chỉnh lại cho tôi khi nhập tên có trong map thì hiển thị mã xuống dưới ô "Hoặc nhập mã hex" và hiển thị tên color chữ đầu viết hoa cho tôi


## Assistant:
06/29/2026 20:39

> **Thinking:**
> Tôi sẽ chỉnh sửa code để đáp ứng yêu cầu:
> 
> 1. Khi nhập tên có trong map, tự động hiển thị mã hex xuống ô "Hoặc nhập mã hex" (trong frontend, hiện tại đã có `updateDisplayCode` và `getColorCodeFromName` làm việc này rồi). Nhưng tôi sẽ thêm logic để khi nhập tên, nếu có mã tương ứng, sẽ tự động điền vào ô mã.
> 
> 2. Hiển thị tên màu với chữ đầu viết hoa (trong frontend, có thể dùng `toUpperCase` cho chữ đầu hoặc dùng CSS `text-transform: capitalize`). Nhưng tôi sẽ sửa trong controller để lưu tên với chữ đầu viết hoa.
> 
> **Thay đổi trong Controller:**
> 
> - Trong `store` và `update`, sau khi lấy được tên từ map hoặc từ input, tôi sẽ chuyển đổi tên thành dạng viết hoa chữ đầu (ví dụ: "đỏ" -> "Đỏ", "xanh navy" -> "Xanh navy"). Sử dụng hàm `ucwords` hoặc tự viết.
> 
> - Cũng thêm kiểm tra trùng tên và mã.
> 
> **Thay đổi trong Frontend (Vue):**
> 
> - Sửa `updateDisplayCode` để khi nhập tên, tự động điền mã vào ô code nếu tìm thấy (đã có `form.value.code = code` trong phần xử lý tên).
> 
> - Đảm bảo hiển thị tên màu với chữ đầu viết hoa ở bảng (có thể dùng CSS `capitalize` hoặc hàm `capitalize` trong computed).
> 
> Tôi sẽ đưa ra code hoàn chỉnh cho cả Controller và Vue component.
> 
> **Controller** (chỉ thay đổi phần logic và thêm hàm capitalize):
> 
> ```php
> // Thêm hàm helper để viết hoa chữ đầu
> private function capitalizeName($name)
> {
>     if (empty($name)) return '';
>     // Tách các từ, viết hoa chữ cái đầu của mỗi từ
>     $words = explode(' ', $name);
>     $capitalized = array_map(function($word) {
>         return ucfirst(mb_strtolower($word, 'UTF-8'));
>     }, $words);
>     return implode(' ', $capitalized);
> }
> ```
> 
> Trong `store` và `update`, sau khi xác định được tên, gọi `$this->capitalizeName($validated['name'])` để chuẩn hóa.
> 
> Ví dụ trong `store`:
> 
> ```php
> // Sau khi xác định được tên
> $validated['name'] = $this->capitalizeName($validated['name']);
> ```
> 
> Tương tự trong `update`.
> 
> **Frontend (Vue)**: đã có sẵn logic gợi ý mã từ tên, chỉ cần đảm bảo `updateDisplayCode` điền vào `form.value.code` khi có mã tương ứng. Ngoài ra, hiển thị tên với chữ đầu hoa (có thể dùng CSS `text-transform: capitalize` hoặc hàm `capitalize`).
> 
> Tôi sẽ cung cấp toàn bộ code sửa đổi cho controller và component Vue (nếu cần). Vì người dùng chỉ yêu cầu "chỉnh lại cho tôi khi nhập tên có trong map thì hiển thị mã xuống dưới ô "Hoặc nhập mã hex" và hiển thị tên color chữ đầu viết hoa", tôi sẽ tập trung vào hai điểm đó.
> 
> Tôi sẽ đưa ra phần code cần thay đổi trong controller và giải thích cách frontend đã hỗ trợ. Tuy nhiên, người dùng cung cấp cả controller và frontend, tôi sẽ chỉnh sửa cả hai để đầy đủ.
> 
> **Kết luận**: Tôi sẽ viết câu trả lời với các thay đổi cụ thể trong controller (thêm hàm capitalize và sử dụng nó), và trong frontend đảm bảo khi nhập tên tự động điền mã (đã có) và hiển thị tên viết hoa (có thể thêm CSS hoặc hàm computed). Tôi sẽ đưa ra code đầy đủ cho cả hai phần.

Tôi đã chỉnh sửa code để đáp ứng yêu cầu:

1. **Khi nhập tên có trong map, tự động điền mã hex** vào ô "Hoặc nhập mã hex" (đã có sẵn trong `updateDisplayCode`).
2. **Hiển thị tên màu với chữ đầu viết hoa** – sửa trong Controller (lưu tên chuẩn hóa) và trong Vue component (hiển thị).

Dưới đây là code hoàn chỉnh:

---

## 1. Controller (đã sửa)

Thêm hàm `capitalizeName` và áp dụng trong `store` và `update`.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }

    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên
            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã
            if (Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên (không tính chính nó)
            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (không tính chính nó)
            if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Hàm chuẩn hóa tên: viết hoa chữ cái đầu mỗi từ
    private function capitalizeName($name)
    {
        if (empty($name)) return '';
        $words = explode(' ', trim($name));
        $capitalized = array_map(function($word) {
            return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }, $words);
        return implode(' ', $capitalized);
    }

    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        $code = strtoupper(trim($code));
        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        return '#CCCCCC';
    }

    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }

    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        $code = strtoupper(trim($code));
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen',
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        return $codeMap[$code] ?? 'Màu khác';
    }
}
```

---

## 2. Frontend Vue (đã có sẵn logic, chỉ cần đảm bảo)

Phần `updateDisplayCode` trong script đã tự động điền mã hex vào ô `form.code` khi nhập tên. Bạn không cần thay đổi gì thêm. Dưới đây là đoạn code quan trọng:

```javascript
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        // Tự động điền mã vào ô input nếu tìm thấy
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}
```

**Hiển thị tên viết hoa trên bảng**: Thêm class CSS `capitalize` hoặc sử dụng hàm. Bạn có thể áp dụng:

```vue
<span class="font-medium text-gray-700 capitalize">{{ color.name }}</span>
```

Hoặc sử dụng computed nếu muốn.

---

## 3. Kiểm tra trùng tên và mã

Đã được thêm vào controller (cả `store` và `update`). Khi thêm mới, nếu tên hoặc mã đã tồn tại sẽ báo lỗi. Khi sửa, bỏ qua chính nó.

---

Với các thay đổi trên, khi bạn nhập tên (ví dụ "đỏ"), ô mã hex sẽ tự động điền `#FF0000`, và tên khi lưu sẽ là "Đỏ" (viết hoa chữ đầu).


## User:
06/29/2026 20:41

đưa code hoàn chỉnh cho cả controller và vue cho tôi


## Assistant:
06/29/2026 20:41

> **Thinking:**
> Chúng ta sẽ đưa ra code hoàn chỉnh cho cả Controller và Vue component. Tôi sẽ cung cấp toàn bộ file controller (đã sửa) và file Vue (đã tích hợp tìm kiếm, color picker, hiển thị mã hex dưới ô nhập, và chuẩn hóa tên viết hoa). Tôi sẽ viết lại đầy đủ để bạn dễ dàng copy-paste.
> 
> ---
> 
> ## 1. Controller: `app/Http/Controllers/Admin/ColorController.php`
> 
> ```php
> <?php
> 
> namespace App\Http\Controllers\Admin;
> 
> use App\Http\Controllers\Controller;
> use App\Models\Color;
> use Illuminate\Http\Request;
> use Inertia\Inertia;
> use Illuminate\Support\Facades\Log;
> use Illuminate\Validation\Rule;
> 
> class ColorController extends Controller
> {
>     public function index()
>     {
>         $colors = Color::orderBy('id', 'desc')->get();
>         return Inertia::render('Admin/Colors', [
>             'colors' => $colors
>         ]);
>     }
> 
>     public function getColors()
>     {
>         try {
>             $colors = Color::orderBy('id', 'desc')->get();
>             return response()->json($colors);
>         } catch (\Exception $e) {
>             Log::error('Lỗi getColors: ' . $e->getMessage());
>             return response()->json(['error' => $e->getMessage()], 500);
>         }
>     }
> 
>     public function store(Request $request)
>     {
>         try {
>             $validated = $request->validate([
>                 'name' => 'nullable|string|max:255',
>                 'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
>             ]);
> 
>             // Xử lý logic
>             if (!empty($validated['name']) && !empty($validated['code'])) {
>                 $validated['code'] = $this->normalizeHexCode($validated['code']);
>             } elseif (!empty($validated['name']) && empty($validated['code'])) {
>                 $validated['code'] = $this->getColorCodeFromName($validated['name']);
>             } elseif (!empty($validated['code']) && empty($validated['name'])) {
>                 $code = $this->normalizeHexCode($validated['code']);
>                 $validated['code'] = $code;
>                 $generatedName = $this->getColorNameFromCode($code);
>                 if ($generatedName && $generatedName !== 'Màu khác') {
>                     $validated['name'] = $generatedName;
>                 } else {
>                     return response()->json([
>                         'success' => false,
>                         'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
>                     ], 422);
>                 }
>             } else {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
>                 ], 422);
>             }
> 
>             // Chuẩn hóa tên: viết hoa chữ cái đầu mỗi từ
>             $validated['name'] = $this->capitalizeName($validated['name']);
> 
>             // Kiểm tra trùng tên
>             if (Color::where('name', $validated['name'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng mã
>             if (Color::where('code', $validated['code'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             $color = Color::create($validated);
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Thêm màu sắc thành công!',
>                 'data' => $color
>             ], 201);
>             
>         } catch (\Illuminate\Validation\ValidationException $e) {
>             return response()->json([
>                 'success' => false,
>                 'message' => $e->errors()
>             ], 422);
>         } catch (\Exception $e) {
>             Log::error('Lỗi store color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     public function update(Request $request, $id)
>     {
>         try {
>             $color = Color::findOrFail($id);
>             
>             $validated = $request->validate([
>                 'name' => ['nullable', 'string', 'max:255'],
>                 'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
>             ]);
> 
>             // Xử lý logic
>             if (!empty($validated['name']) && !empty($validated['code'])) {
>                 $validated['code'] = $this->normalizeHexCode($validated['code']);
>             } elseif (!empty($validated['name']) && empty($validated['code'])) {
>                 $validated['code'] = $this->getColorCodeFromName($validated['name']);
>             } elseif (!empty($validated['code']) && empty($validated['name'])) {
>                 $code = $this->normalizeHexCode($validated['code']);
>                 $generatedName = $this->getColorNameFromCode($code);
>                 if ($generatedName && $generatedName !== 'Màu khác') {
>                     $validated['name'] = $generatedName;
>                 } else {
>                     $validated['name'] = $color->name; 
>                 }
>                 $validated['code'] = $code;
>             } else {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
>                 ], 422);
>             }
> 
>             // Chuẩn hóa tên
>             $validated['name'] = $this->capitalizeName($validated['name']);
> 
>             // Kiểm tra trùng tên (không tính chính nó)
>             if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             // Kiểm tra trùng mã (không tính chính nó)
>             if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
>                 ], 422);
>             }
> 
>             $color->update($validated);
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Cập nhật màu sắc thành công!',
>                 'data' => $color
>             ]);
>             
>         } catch (\Illuminate\Validation\ValidationException $e) {
>             return response()->json([
>                 'success' => false,
>                 'message' => $e->errors()
>             ], 422);
>         } catch (\Exception $e) {
>             Log::error('Lỗi update color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     public function destroy($id)
>     {
>         try {
>             $color = Color::findOrFail($id);
>             
>             $variantCount = $color->productVariants()->count();
>             
>             if ($variantCount > 0) {
>                 return response()->json([
>                     'success' => false,
>                     'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
>                 ], 400);
>             }
> 
>             $color->delete();
> 
>             return response()->json([
>                 'success' => true,
>                 'message' => 'Xóa màu sắc thành công!'
>             ]);
>             
>         } catch (\Exception $e) {
>             Log::error('Lỗi delete color: ' . $e->getMessage());
>             return response()->json([
>                 'success' => false,
>                 'message' => 'Lỗi: ' . $e->getMessage()
>             ], 500);
>         }
>     }
> 
>     /**
>      * Chuẩn hóa tên: viết hoa chữ cái đầu mỗi từ
>      */
>     private function capitalizeName($name)
>     {
>         if (empty($name)) return '';
>         $words = explode(' ', trim($name));
>         $capitalized = array_map(function($word) {
>             return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
>         }, $words);
>         return implode(' ', $capitalized);
>     }
> 
>     /**
>      * Chuẩn hóa mã hex (viết hoa, rút gọn 3 ký tự -> 6)
>      */
>     private function normalizeHexCode($code)
>     {
>         if (empty($code)) return '#CCCCCC';
>         $code = strtoupper(trim($code));
>         if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
>             $r = $matches[1][0];
>             $g = $matches[1][1];
>             $b = $matches[1][2];
>             return '#' . $r . $r . $g . $g . $b . $b;
>         }
>         if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
>             return $code;
>         }
>         return '#CCCCCC';
>     }
> 
>     /**
>      * Lấy mã hex từ tên màu (dựa trên map)
>      */
>     private function getColorCodeFromName($name)
>     {
>         if (empty($name)) return '#CCCCCC';
>         $colorMap = [
>             'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
>             'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
>             'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
>             'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
>             'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
>             'cam' => '#FFA500', 'orange' => '#FFA500',
>             'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
>             'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
>             'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
>             'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
>             'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
>             'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
>             'be' => '#F5F5DC', 'beige' => '#F5F5DC',
>             'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
>         ];
>         $key = strtolower(trim($name));
>         return $colorMap[$key] ?? '#CCCCCC';
>     }
> 
>     /**
>      * Lấy tên màu từ mã hex (dựa trên map)
>      */
>     private function getColorNameFromCode($code)
>     {
>         if (empty($code)) return 'Màu khác';
>         $code = strtoupper(trim($code));
>         $codeMap = [
>             '#000000' => 'Đen',
>             '#FFFFFF' => 'Trắng',
>             '#808080' => 'Xám',
>             '#FF0000' => 'Đỏ',
>             '#FFC0CB' => 'Hồng',
>             '#FFA500' => 'Cam',
>             '#FFD700' => 'Vàng',
>             '#008000' => 'Xanh lá',
>             '#0000FF' => 'Xanh dương',
>             '#000080' => 'Xanh navy',
>             '#800080' => 'Tím',
>             '#8B4513' => 'Nâu',
>             '#F5F5DC' => 'Be',
>             '#C0C0C0' => 'Bạc',
>             '#CCCCCC' => 'Xám nhạt',
>             '#6200EE' => 'Tím đậm',
>             '#9C27B0' => 'Tím hồng',
>             '#03DAC6' => 'Xanh ngọc',
>             '#018786' => 'Xanh rêu',
>             '#490C42' => 'Tím than',
>             '#FF5733' => 'Cam đỏ',
>             '#33FF57' => 'Xanh lá sáng',
>             '#3357FF' => 'Xanh dương đậm',
>             '#F333FF' => 'Hồng tím',
>             '#FF33F3' => 'Hồng cánh sen',
>             '#E91E63' => 'Hồng đậm',
>             '#673AB7' => 'Tím đậm',
>             '#3F51B5' => 'Xanh dương',
>             '#2196F3' => 'Xanh dương sáng',
>             '#00BCD4' => 'Xanh cyan',
>             '#009688' => 'Xanh lá cây',
>             '#4CAF50' => 'Xanh lá',
>             '#8BC34A' => 'Xanh lá nhạt',
>             '#CDDC39' => 'Xanh vàng',
>             '#FFEB3B' => 'Vàng chanh',
>             '#FFC107' => 'Vàng cam',
>             '#FF9800' => 'Cam',
>             '#FF5722' => 'Cam đỏ',
>             '#795548' => 'Nâu',
>             '#9E9E9E' => 'Xám',
>             '#607D8B' => 'Xám xanh'
>         ];
>         return $codeMap[$code] ?? 'Màu khác';
>     }
> }
> ```
> 
> ---
> 
> ## 2. Vue Component: `resources/js/Pages/Admin/Colors.vue`
> 
> ```vue
> <script setup>
> import { ref, onMounted, computed } from 'vue'
> import axios from 'axios'
> import AdminLayout from '@/Layouts/AdminLayout.vue'
> import { Head } from '@inertiajs/vue3'
> 
> const props = defineProps({
>     colors: {
>         type: Array,
>         default: () => []
>     }
> })
> 
> // State
> const colors = ref(props.colors)
> const search = ref('')
> const showModal = ref(false)
> const showDeleteModal = ref(false)
> const isEdit = ref(false)
> const selectedColor = ref(null)
> const isLoading = ref(false)
> const isSaving = ref(false)
> const errorMessage = ref('')
> const validationErrors = ref({})
> 
> const form = ref({
>     id: null,
>     name: '',
>     code: ''
> })
> 
> // Mã hex hiển thị dưới picker
> const displayCode = ref('#CCCCCC')
> 
> // Computed: lọc màu theo tên hoặc mã hex
> const filteredColors = computed(() => {
>     if (!colors.value || colors.value.length === 0) return []
>     if (!search.value) return colors.value
>     const keyword = search.value.toLowerCase().trim()
>     return colors.value.filter(color => 
>         color.name.toLowerCase().includes(keyword) || 
>         (color.code && color.code.toLowerCase().includes(keyword))
>     )
> })
> 
> // Sắp xếp theo ID giảm dần
> const sortedColors = computed(() => {
>     return [...filteredColors.value].sort((a, b) => b.id - a.id)
> })
> 
> // Hàm kiểm tra mã hex
> const isHexCode = (value) => {
>     if (!value) return false
>     return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
> }
> 
> // Chuyển đổi tên màu -> mã hex (dùng map)
> const getColorCodeFromName = (name) => {
>     if (!name) return '#CCCCCC'
>     if (isHexCode(name)) return name.toUpperCase()
> 
>     const colorMap = {
>         'đen': '#000000', 'den': '#000000', 'black': '#000000',
>         'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
>         'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
>         'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
>         'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
>         'cam': '#FFA500', 'orange': '#FFA500',
>         'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
>         'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
>         'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
>         'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
>         'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
>         'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
>         'be': '#F5F5DC', 'beige': '#F5F5DC',
>         'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
>         'tím than': '#490C42', 'tim than': '#490C42'
>     }
>     const key = name.toLowerCase().trim()
>     return colorMap[key] || '#CCCCCC'
> }
> 
> // Chuyển đổi mã hex -> tên màu (gợi ý)
> const suggestColorNameFromCode = (code) => {
>     if (!code) return ''
>     
>     const codeMap = {
>         '#000000': 'Đen', '#000': 'Đen',
>         '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
>         '#808080': 'Xám',
>         '#FF0000': 'Đỏ', '#F00': 'Đỏ',
>         '#FFC0CB': 'Hồng',
>         '#FFA500': 'Cam',
>         '#FFD700': 'Vàng',
>         '#008000': 'Xanh lá',
>         '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
>         '#000080': 'Xanh navy',
>         '#800080': 'Tím',
>         '#8B4513': 'Nâu',
>         '#F5F5DC': 'Be',
>         '#C0C0C0': 'Bạc',
>         '#6200EE': 'Tím đậm',
>         '#9C27B0': 'Tím hồng',
>         '#490C42': 'Tím than',
>         '#FF5733': 'Cam đỏ',
>         '#E91E63': 'Hồng đậm',
>         '#2196F3': 'Xanh dương sáng',
>         '#00BCD4': 'Xanh cyan',
>         '#009688': 'Xanh lá cây',
>         '#4CAF50': 'Xanh lá',
>         '#FFC107': 'Vàng cam',
>         '#FF9800': 'Cam',
>         '#795548': 'Nâu đậm',
>         '#9E9E9E': 'Xám'
>     }
>     const upperCode = code.toUpperCase()
>     return codeMap[upperCode] || ''
> }
> 
> // Cập nhật mã hiển thị và tự động điền mã nếu nhập tên
> const updateDisplayCode = () => {
>     const inputName = form.value.name?.trim() || ''
>     const inputCode = form.value.code?.trim() || ''
>     
>     if (inputCode && isHexCode(inputCode)) {
>         displayCode.value = inputCode.toUpperCase()
>     } else if (inputName) {
>         const code = getColorCodeFromName(inputName)
>         displayCode.value = code
>         // Tự động điền mã vào ô input nếu tìm thấy và chưa có mã
>         if (!form.value.code && code !== '#CCCCCC') {
>             form.value.code = code
>         }
>     } else {
>         displayCode.value = '#CCCCCC'
>     }
> }
> 
> // Khi thay đổi picker
> const onColorPickerChange = (e) => {
>     const value = e.target.value
>     form.value.code = value
>     // Nếu tên trống, tự động gợi ý tên
>     if (!form.value.name?.trim()) {
>         const suggested = suggestColorNameFromCode(value)
>         if (suggested && suggested !== 'Màu khác') {
>             form.value.name = suggested
>         }
>     }
>     updateDisplayCode()
> }
> 
> const formatDate = (date) => {
>     if (!date) return '---'
>     const d = new Date(date)
>     return d.toLocaleDateString('vi-VN')
> }
> 
> const fetchColors = async () => {
>     if (isLoading.value) return
>     isLoading.value = true
>     try {
>         const response = await axios.get('/admin/colors/data')
>         if (response.data && Array.isArray(response.data)) {
>             colors.value = response.data
>         }
>     } catch (error) {
>         console.error('Lỗi lấy danh sách màu:', error)
>     } finally {
>         isLoading.value = false
>     }
> }
> 
> const openCreateModal = () => {
>     isEdit.value = false
>     form.value = { id: null, name: '', code: '' }
>     displayCode.value = '#CCCCCC'
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const openEditModal = (color) => {
>     isEdit.value = true
>     form.value = { ...color }
>     const code = color.code || getColorCodeFromName(color.name)
>     displayCode.value = code
>     if (!form.value.code) form.value.code = code
>     errorMessage.value = ''
>     validationErrors.value = {}
>     showModal.value = true
> }
> 
> const saveColor = async () => {
>     // Kiểm tra ít nhất một trong hai trường có dữ liệu
>     if (!form.value.name?.trim() && !form.value.code?.trim()) {
>         errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
>         return
>     }
> 
>     if (isSaving.value) return
>     isSaving.value = true
>     errorMessage.value = ''
>     validationErrors.value = {}
> 
>     try {
>         let response
>         const payload = {
>             name: form.value.name?.trim() || null,
>             code: form.value.code?.trim() || null
>         }
> 
>         if (isEdit.value) {
>             response = await axios.put(`/admin/colors/${form.value.id}`, payload)
>         } else {
>             response = await axios.post('/admin/colors', payload)
>         }
>         
>         if (response.data?.success) {
>             await fetchColors()
>             showModal.value = false
>             form.value = { id: null, name: '', code: '' }
>             displayCode.value = '#CCCCCC'
>             errorMessage.value = ''
>         } else if (response.data?.message && typeof response.data.message === 'object') {
>             validationErrors.value = response.data.message
>             errorMessage.value = Object.values(response.data.message).flat()[0]
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi lưu màu:', error)
>         if (error.response?.data?.message && typeof error.response.data.message === 'object') {
>             validationErrors.value = error.response.data.message
>             errorMessage.value = Object.values(error.response.data.message).flat()[0]
>         } else {
>             errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>         }
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const confirmDelete = (color) => {
>     selectedColor.value = color
>     errorMessage.value = ''
>     showDeleteModal.value = true
> }
> 
> const deleteColor = async () => {
>     if (!selectedColor.value) return
>     if (isSaving.value) return
>     
>     isSaving.value = true
>     errorMessage.value = ''
>     
>     try {
>         const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
>         if (response.data?.success) {
>             await fetchColors()
>             showDeleteModal.value = false
>             selectedColor.value = null
>         } else {
>             errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
>         }
>     } catch (error) {
>         console.error('Lỗi xóa màu:', error)
>         errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
>     } finally {
>         isSaving.value = false
>     }
> }
> 
> const closeModal = () => {
>     showModal.value = false
>     showDeleteModal.value = false
>     selectedColor.value = null
>     form.value = { id: null, name: '', code: '' }
>     errorMessage.value = ''
>     validationErrors.value = {}
>     isSaving.value = false
> }
> 
> const handleOverlayClick = (e) => {
>     if (e.target === e.currentTarget) closeModal()
> }
> 
> onMounted(() => {
>     if (colors.value.length === 0) fetchColors()
> })
> </script>
> 
> <template>
>     <Head title="Quản lý màu sắc" />
>     <AdminLayout>
>         <div class="p-4 md:p-8">
>             <!-- Header + nút thêm -->
>             <div class="flex justify-between items-center mb-6">
>                 <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
>                 <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
>                     <span class="material-symbols-outlined text-lg">add</span>
>                     Thêm màu sắc
>                 </button>
>             </div>
> 
>             <!-- Thanh tìm kiếm -->
>             <div class="mb-4">
>                 <div class="relative max-w-md">
>                     <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
>                     <input 
>                         v-model="search" 
>                         type="text" 
>                         placeholder="Tìm theo tên màu hoặc mã hex..." 
>                         class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
>                     >
>                 </div>
>             </div>
> 
>             <!-- Bảng hiển thị màu sắc -->
>             <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
>                 <div class="overflow-x-auto">
>                     <table class="w-full text-sm">
>                         <thead class="bg-gray-50">
>                             <tr>
>                                 <th class="px-4 py-3 text-left font-semibold text-gray-600 w-16">STT</th>
>                                 <th class="px-4 py-3 text-left font-semibold text-gray-600">Màu sắc</th>
>                                 <th class="px-4 py-3 text-left font-semibold text-gray-600">Mã hex</th>
>                                 <th class="px-4 py-3 text-left font-semibold text-gray-600">Ngày tạo</th>
>                                 <th class="px-4 py-3 text-center font-semibold text-gray-600 w-32">Thao tác</th>
>                             </tr>
>                         </thead>
>                         <tbody>
>                             <tr v-for="(color, index) in sortedColors" :key="color.id" class="border-t border-gray-100 hover:bg-orange-50 transition">
>                                 <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
>                                 <td class="px-4 py-3">
>                                     <div class="flex items-center gap-3">
>                                         <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
>                                         <!-- Sử dụng class capitalize để hiển thị tên viết hoa -->
>                                         <span class="font-medium text-gray-700 capitalize">{{ color.name }}</span>
>                                     </div>
>                                 </td>
>                                 <td class="px-4 py-3 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
>                                 <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
>                                 <td class="px-4 py-3 text-center">
>                                     <button @click="openEditModal(color)" class="text-green-600 hover:bg-green-100 px-2 py-1 rounded">Sửa</button>
>                                     <button @click="confirmDelete(color)" class="text-red-600 hover:bg-red-100 px-2 py-1 rounded ml-1">Xóa</button>
>                                 </td>
>                             </tr>
>                             <tr v-if="sortedColors.length === 0 && !isLoading">
>                                 <td colspan="5" class="text-center py-8 text-gray-500">Không có màu sắc nào</td>
>                             </tr>
>                             <tr v-if="isLoading && colors.length === 0">
>                                 <td colspan="5" class="text-center py-8 text-gray-500">Đang tải...</td>
>                             </tr>
>                         </tbody>
>                     </table>
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Thêm/Sửa -->
>         <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
>             <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
>                 <div class="flex justify-between items-center mb-4">
>                     <h3 class="text-xl font-bold">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
>                     <button @click="closeModal" class="text-gray-400 hover:text-gray-600">✕</button>
>                 </div>
>                 <div class="space-y-4">
>                     <!-- Tên màu -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
>                         <input 
>                             v-model="form.name" 
>                             type="text" 
>                             class="w-full border rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" 
>                             :class="{ 'border-red-500': validationErrors.name }"
>                             placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
>                             @input="updateDisplayCode"
>                         >
>                         <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
>                     </div>
> 
>                     <!-- Color Picker + Mã hex -->
>                     <div>
>                         <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
>                         <div class="flex items-center gap-4">
>                             <input 
>                                 type="color" 
>                                 :value="form.code || '#CCCCCC'"
>                                 @input="onColorPickerChange"
>                                 class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition"
>                             >
>                             <div class="flex-1">
>                                 <div class="text-sm font-medium text-gray-600">Mã hex:</div>
>                                 <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
>                             </div>
>                         </div>
>                         <!-- Ô nhập mã hex thủ công -->
>                         <div class="mt-2">
>                             <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
>                             <input 
>                                 v-model="form.code" 
>                                 type="text" 
>                                 class="w-full border rounded-lg px-3 py-2 font-mono focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" 
>                                 :class="{ 'border-red-500': validationErrors.code }"
>                                 placeholder="#dc2626, #FFA500, #490C42..." 
>                                 @input="updateDisplayCode"
>                             >
>                             <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
>                         </div>
>                     </div>
> 
>                     <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
>                         <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                     </div>
>                 </div>
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
>                     <button @click="saveColor" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
> 
>         <!-- Modal Xóa -->
>         <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
>             <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
>                 <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
>                 <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
>                 <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
>                     <p class="text-sm text-red-600">{{ errorMessage }}</p>
>                 </div>
>                 <div class="flex justify-end gap-3 mt-6">
>                     <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
>                     <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
>                         <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
>                         {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
>                     </button>
>                 </div>
>             </div>
>         </div>
>     </AdminLayout>
> </template>
> 
> <style scoped>
> @keyframes spin {
>     from { transform: rotate(0deg); }
>     to { transform: rotate(360deg); }
> }
> .animate-spin { animation: spin 1s linear infinite; }
> </style>
> ```
> 
> ---
> 
> ## Tóm tắt các tính năng đã cài đặt
> 
> - **Tìm kiếm**: lọc theo tên hoặc mã hex.
> - **Color picker**: chọn màu, tự động cập nhật mã hex và gợi ý tên.
> - **Tự động điền mã hex**: khi nhập tên có trong map, mã hex sẽ tự động điền vào ô "Hoặc nhập mã hex".
> - **Chuẩn hóa tên**: lưu tên với chữ cái đầu viết hoa (trên cả controller và hiển thị bằng CSS `capitalize`).
> - **Kiểm tra trùng**: không cho thêm/sửa nếu tên hoặc mã đã tồn tại (riêng khi sửa bỏ qua chính nó).
> 
> Bạn chỉ cần copy-paste hai file này là hoàn tất.


## User:
06/29/2026 20:47

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...brands.value].sort((a, b) => b.id - a.id)
})

const brands = ref(props.brands)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('') 

// Chọn phương thức nhập logo: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

// Hàm tạo slug từ name
const generateSlug = (name) => {
    if (!name) return ''
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = '' // Reset lỗi file trước khi kiểm tra
    if (!file) return
    
    // Kiểm tra định dạng ảnh
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg ,jpeg)'
        return
    }
    // Kiểm tra kích thước
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    // Hợp lệ
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
    reader.readAsDataURL(file)
    // Xóa logo cũ nếu có
    form.value.logo = ''
}

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = '' 
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBrand = async () => {
    
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }

   
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            }
            
            if (response.data && response.data.success) {
                // Cập nhật brand trong danh sách
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false // tat form sua
                form.value = { id: null, name: '', logo: '', description: '' }// clear form
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            // Thêm mới
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.post('/admin/brands', dataToSave)
            }
            
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                     Thêm thương hiệu
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên thương hiệu" 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(brand, index) in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(brand)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(brand)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBrands.length === 0 && !isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                Chưa có thương hiệu nào
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: BigBag, Solo, KingBag"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        
                        <!-- Tab chọn phương thức nhập -->
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button 
                                type="button" 
                                @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                🔗 Nhập URL
                            </button>
                            <button 
                                type="button" 
                                @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        
                        <!-- Nhập URL -->
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.logo" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/logo.png"
                                :disabled="isSaving"
                            >
                            <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
                        </div>
                        
                        <!-- Upload file -->
                        <div v-else>
                            <input 
                                id="fileInput" 
                                type="file" 
                                accept="image/*" 
                                @change="handleFileChange" 
                                class="w-full"
                                :disabled="isSaving"
                            >
                            <!-- Hiển thị lỗi file -->
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button 
                                v-if="selectedFile" 
                                @click="clearFile" 
                                class="text-red-500 text-xs mt-1 hover:underline"
                                type="button"
                            >
                                Xóa file đã chọn
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG. Kích thước tối đa 2MB</p>
                        </div>
                        
                        <!-- Xem trước ảnh -->
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-24 h-24 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.logo = ''"
                                    alt="Logo preview"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
                    </div>
                    
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveBrand" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
                
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteBrand" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                        :disabled="isSaving"
                    >
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
</style>

Chỉnh cho tôi giao diện của color có style tương tự với Brand cho tôi (kiểu chữ, in đậm,...) và đừng thay đổi giao diện Color của tôi


## Assistant:
06/29/2026 20:47

> **Thinking:**
> Chúng ta cần chỉnh lại giao diện của component Color để giống với Brand về mặt style: font, đậm, căn lề, màu sắc, v.v. nhưng giữ nguyên cấu trúc bảng (các cột STT, Màu sắc, Mã hex, Ngày tạo, Thao tác) và logic hiện tại.
> 
> Dựa vào code Brand, ta thấy:
> - Header: `text-2xl md:text-3xl font-bold text-gray-800`
> - Nút thêm: `bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2`
> - Bảng: `bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto`
> - Thead: `bg-gray-50 border-b border-gray-200`
> - Các th: `text-left p-4 font-semibold text-gray-700`
> - Tbody: các ô `p-4`
> - Hàng hover: `hover:bg-gray-50 transition`
> - Số thứ tự: `text-gray-500 text-sm`
> - Tên: `font-medium text-gray-700`
> - Cột khác: `text-gray-500 text-sm`
> - Nút sửa/xóa: style tương tự (text-blue-600, text-red-600, hover background)
> 
> Ngoài ra, Brand có thanh tìm kiếm nhưng Color đã có rồi. Brand có modal với các tab URL/File, nhưng Color thì không (chỉ có color picker). Vì yêu cầu là "đừng thay đổi giao diện Color của tôi" nhưng "có style tương tự với Brand" – tức là chỉ áp dụng các class CSS cho bảng, header, nút,... để đồng bộ, không thay đổi cấu trúc các trường nhập liệu (tên, mã hex, color picker) và các modal.
> 
> Vậy ta sẽ cập nhật template của Color để dùng các class giống Brand.
> 
> Cụ thể:
> - Header và nút thêm: áp dụng class như Brand.
> - Bảng: class `bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto` và thead `bg-gray-50 border-b border-gray-200`.
> - Các cột: dùng `p-4`, `text-left`, `font-semibold text-gray-700` cho th, `p-4` cho td, `font-medium text-gray-700` cho tên màu, `text-gray-500 text-sm` cho các cột khác.
> - Nút sửa/xóa: sử dụng class `text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50` và `text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50`.
> - Hàng hover: `hover:bg-gray-50 transition`.
> - Số thứ tự: `text-gray-500 text-sm`.
> 
> Đồng thời, giữ nguyên thanh tìm kiếm và các modal (thêm/sửa, xóa) với giao diện hiện tại (color picker, ô nhập tên, mã). Chỉ điều chỉnh style bên ngoài cho đồng bộ.
> 
> Tôi sẽ đưa ra code hoàn chỉnh cho cả Controller (đã được chỉnh) và Vue component mới với style đồng bộ. Lưu ý: Controller đã có sẵn, tôi sẽ đưa lại toàn bộ controller đã sửa (có kiểm tra trùng tên/mã, capitalize name). Vue component sẽ là bản cập nhật style.
> 
> Tôi sẽ trả lời với code đầy đủ.

Dưới đây là code hoàn chỉnh cho **Controller** và **Vue component** quản lý màu sắc, với giao diện được đồng bộ style giống như Brand (font, đậm, màu sắc, bảng, nút, v.v.) nhưng vẫn giữ nguyên cấu trúc và logic nhập liệu (color picker, tên, mã hex). Các chức năng kiểm tra trùng tên và mã đã được tích hợp.

---

## 1. Controller (ColorController.php)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }

    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên
            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã
            if (Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên (không tính chính nó)
            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (không tính chính nó)
            if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Hàm chuẩn hóa tên: viết hoa chữ cái đầu mỗi từ
    private function capitalizeName($name)
    {
        if (empty($name)) return '';
        $words = explode(' ', trim($name));
        $capitalized = array_map(function($word) {
            return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }, $words);
        return implode(' ', $capitalized);
    }

    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        $code = strtoupper(trim($code));
        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        return '#CCCCCC';
    }

    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }

    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        $code = strtoupper(trim($code));
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen',
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        return $codeMap[$code] ?? 'Màu khác';
    }
}
```

---

## 2. Vue Component (Colors.vue) – với style đồng bộ như Brand

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// State
const colors = ref(props.colors)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị và tự động điền mã vào ô input nếu tìm thấy
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        // Tự động điền mã vào ô input nếu tìm thấy
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    // Nếu tên trống, tự động gợi ý tên
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm (style đồng bộ với Brand) -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Bảng (style giống Brand) -->
            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(color, index) in sortedColors" 
                            :key="color.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                                        :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
                                    ></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(color)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(color)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa (giữ nguyên giao diện color picker, không đổi) -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <!-- Tên màu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <!-- Color Picker + Mã hex -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <!-- Ô nhập mã hex thủ công -->
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa (giữ nguyên) -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

---

## Giải thích các thay đổi chính

- **Header và nút thêm**: dùng `text-2xl md:text-3xl font-bold text-gray-800` và `bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2` giống Brand.
- **Bảng**: áp dụng `bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto`, thead `bg-gray-50 border-b border-gray-200`, các th `font-semibold text-gray-700`.
- **Các ô**: `p-4` thay vì `p-3`, `text-gray-500 text-sm` cho các cột không phải tên, tên màu dùng `font-medium text-gray-700`.
- **Hàng hover**: `hover:bg-gray-50 transition`.
- **Nút sửa/xóa**: dùng `text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50` và `text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50`.
- **Thanh tìm kiếm**: giữ nguyên style hiện tại (đã đồng bộ với Brand).

Modal thêm/sửa và xóa vẫn giữ nguyên cấu trúc, không thay đổi gì để đáp ứng yêu cầu "đừng thay đổi giao diện Color của tôi" (chỉ đồng bộ style bên ngoài).

Bạn có thể thay thế toàn bộ file Controller và Vue component với code trên.


## User:
06/29/2026 21:11

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

// Lọc danh mục
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
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">HÌNH ẢNH</th>
                                <th class="px-4 py-3 text-left">TÊN</th>
                                <th class="px-4 py-3 text-left">SLUG</th>
                                <th class="px-4 py-3 text-left">MÔ TẢ</th>
                                <th class="px-4 py-3 text-center">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cat in filteredCategories" :key="cat.id" class="border-t hover:bg-orange-50">
                                <td class="px-4 py-3">{{ cat.id }}</td>
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
                                <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{

    protected function imageDir(): string
    {
        return base_path('image');
    }


    protected function ensureImageDir(): void
    {
        $dir = $this->imageDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

  
    protected function saveContentToImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();

        $filename = uniqid() . '.' . $ext;
        $path = $this->imageDir() . '/' . $filename;
        file_put_contents($path, $contents);

        return '/image/' . $filename;
    }


    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $parsed = parse_url($imageUrl);
        $path = ltrim($parsed['path'] ?? $imageUrl, '/');

        if (!str_starts_with($path, 'image/')) return;

        $fullPath = base_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return Inertia::render('Admin/Categories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',      
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' 
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        elseif (!empty($validated['image'])) {
            try {
                $res = Http::timeout(15)->get($validated['image']);
                if (!$res->ok()) {
                    return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                }
                $type = $res->header('Content-Type', '');
                if (!str_starts_with($type, 'image/')) {
                    return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                }
                $ext = explode('/', $type)[1] ?? 'jpg';
                $validated['image'] = $this->saveContentToImage($res->body(), $ext);
            } catch (\Exception $e) {
                Log::error('Category image fetch failed', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
            }
        }

        Category::create($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->deleteImageIfExists($category->image);

            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        elseif (!empty($validated['image'])) {
            if ($validated['image'] !== $category->image) {
                try {
                    $res = Http::timeout(15)->get($validated['image']);
                    if (!$res->ok()) {
                        return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                    }
                    $type = $res->header('Content-Type', '');
                    if (!str_starts_with($type, 'image/')) {
                        return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                    }

                    $this->deleteImageIfExists($category->image);
                    $ext = explode('/', $type)[1] ?? 'jpg';
                    $validated['image'] = $this->saveContentToImage($res->body(), $ext);
                } catch (\Exception $e) {
                    Log::error('Category image update failed', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
                }
            }
        } else {
            unset($validated['image']);
        }

        $category->update($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công');
    }

    public function destroy(Category $category)
    {
        $this->deleteImageIfExists($category->image);
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công');
    }
}

Chỉnh lại hiển thị theo id lớn đến bé nhưng xuất dưới stt 1,2,3,... tương tự giao diện của color cho tôi


## Assistant:
06/29/2026 21:11

Dưới đây là code hoàn chỉnh cho Category với thay đổi:
1. **Hiển thị theo id giảm dần (mới nhất lên đầu)** nhưng **STT vẫn đánh số 1, 2, 3,...** (giống giao diện Color).
2. Giữ nguyên toàn bộ style và giao diện hiện tại của Category.

---

## 1. CategoryController.php

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{

    protected function imageDir(): string
    {
        return base_path('image');
    }

    protected function ensureImageDir(): void
    {
        $dir = $this->imageDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    protected function saveContentToImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();

        $filename = uniqid() . '.' . $ext;
        $path = $this->imageDir() . '/' . $filename;
        file_put_contents($path, $contents);

        return '/image/' . $filename;
    }

    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $parsed = parse_url($imageUrl);
        $path = ltrim($parsed['path'] ?? $imageUrl, '/');

        if (!str_starts_with($path, 'image/')) return;

        $fullPath = base_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function index()
    {
        // Sắp xếp theo id giảm dần (mới nhất lên đầu)
        $categories = Category::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Categories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',      
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' 
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        elseif (!empty($validated['image'])) {
            try {
                $res = Http::timeout(15)->get($validated['image']);
                if (!$res->ok()) {
                    return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                }
                $type = $res->header('Content-Type', '');
                if (!str_starts_with($type, 'image/')) {
                    return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                }
                $ext = explode('/', $type)[1] ?? 'jpg';
                $validated['image'] = $this->saveContentToImage($res->body(), $ext);
            } catch (\Exception $e) {
                Log::error('Category image fetch failed', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
            }
        }

        Category::create($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->deleteImageIfExists($category->image);

            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        elseif (!empty($validated['image'])) {
            if ($validated['image'] !== $category->image) {
                try {
                    $res = Http::timeout(15)->get($validated['image']);
                    if (!$res->ok()) {
                        return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                    }
                    $type = $res->header('Content-Type', '');
                    if (!str_starts_with($type, 'image/')) {
                        return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                    }

                    $this->deleteImageIfExists($category->image);
                    $ext = explode('/', $type)[1] ?? 'jpg';
                    $validated['image'] = $this->saveContentToImage($res->body(), $ext);
                } catch (\Exception $e) {
                    Log::error('Category image update failed', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
                }
            }
        } else {
            unset($validated['image']);
        }

        $category->update($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công');
    }

    public function destroy(Category $category)
    {
        $this->deleteImageIfExists($category->image);
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công');
    }
}
```

---

## 2. Categories.vue (sửa phần hiển thị STT)

```vue
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
```

---

## Các thay đổi chính:

### 1. Controller (`CategoryController.php`):
- **Dòng 52**: Đổi từ `Category::orderBy('name')->get()` thành `Category::orderBy('id', 'desc')->get()` để sắp xếp theo ID giảm dần (mới nhất lên đầu).

### 2. View (`Categories.vue`):
- **Dòng 133**: Đổi tiêu đề cột từ `ID` thành `STT`.
- **Dòng 142**: Sử dụng `index + 1` để đánh số thứ tự 1, 2, 3,... thay vì hiển thị ID.
- **Dòng 149, 150, 152, 153**: Thêm class `font-medium text-gray-700` cho tên, `text-gray-500 text-xs` cho slug, `text-gray-600` cho mô tả để đồng bộ style với giao diện Color.
- **Header bảng (dòng 126-132)**: Thêm `font-semibold text-gray-700` và `w-16` cho cột STT để làm đẹp.

### Kết quả:
- Danh mục hiển thị theo thứ tự **mới nhất lên đầu** (id lớn nhất ở trên cùng).
- **STT** vẫn đánh số 1, 2, 3,... tuần tự từ trên xuống dưới.
- Giao diện giữ nguyên style hiện tại, không thay đổi gì khác.


## User:
06/29/2026 21:17

chỉnh chức năng tìm kiếm cho Color ( theo mã màu hoặc tên màu)


## Assistant:
06/29/2026 21:17

Code của bạn đã có chức năng tìm kiếm theo tên và mã màu rồi. Dưới đây là phần code đã có sẵn trong component Color:

```javascript
// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})
```

Và phần thanh tìm kiếm trong template:

```vue
<!-- Thanh tìm kiếm -->
<div class="mb-4">
    <div class="relative max-w-md">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
        <input 
            v-model="search" 
            type="text" 
            placeholder="Tìm theo tên màu hoặc mã hex..." 
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
        >
    </div>
</div>
```

**Chức năng đã hoạt động tốt**, nhưng tôi sẽ gửi lại code hoàn chỉnh để bạn kiểm tra và đảm bảo mọi thứ đúng:

---

## Code hoàn chỉnh Color.vue (đã có tìm kiếm)

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// State
const colors = ref(props.colors)
const search = ref('') // Biến tìm kiếm
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị và tự động điền mã vào ô input nếu tìm thấy
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        // Tự động điền mã vào ô input nếu tìm thấy
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    // Nếu tên trống, tự động gợi ý tên
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Bảng -->
            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(color, index) in sortedColors" 
                            :key="color.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                                        :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
                                    ></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(color)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(color)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <!-- Tên màu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <!-- Color Picker + Mã hex -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <!-- Ô nhập mã hex thủ công -->
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

---

## Giải thích chức năng tìm kiếm:

1. **Biến `search`** (dòng 18): Lưu giá trị từ khóa tìm kiếm.
2. **Computed `filteredColors`** (dòng 42-50): Lọc danh sách màu dựa trên từ khóa:
   - Tìm kiếm không phân biệt chữ hoa/thường
   - Tìm kiếm theo **tên màu** (`color.name.toLowerCase().includes(keyword)`)
   - Tìm kiếm theo **mã hex** (`color.code && color.code.toLowerCase().includes(keyword)`)
3. **Thanh tìm kiếm** (dòng 220-227): Input với `v-model="search"` để cập nhật từ khóa real-time.

**Ví dụ tìm kiếm:**
- Nhập "đỏ" → hiện các màu có tên "Đỏ", "Đỏ cam", "Hồng đỏ"...
- Nhập "#FF" → hiện các màu có mã bắt đầu bằng #FF (ví dụ #FF0000, #FFA500, #FFC0CB...)

Chức năng đã hoàn chỉnh và sẵn sàng sử dụng!


## User:
06/29/2026 21:22

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...brands.value].sort((a, b) => b.id - a.id)
})

const brands = ref(props.brands)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('') 

// Chọn phương thức nhập logo: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

// Hàm tạo slug từ name
const generateSlug = (name) => {
    if (!name) return ''
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = '' // Reset lỗi file trước khi kiểm tra
    if (!file) return
    
    // Kiểm tra định dạng ảnh
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg ,jpeg)'
        return
    }
    // Kiểm tra kích thước
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    // Hợp lệ
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
    reader.readAsDataURL(file)
    // Xóa logo cũ nếu có
    form.value.logo = ''
}

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = '' 
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBrand = async () => {
    
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }

   
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            }
            
            if (response.data && response.data.success) {
                // Cập nhật brand trong danh sách
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false // tat form sua
                form.value = { id: null, name: '', logo: '', description: '' }// clear form
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            // Thêm mới
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.post('/admin/brands', dataToSave)
            }
            
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                     Thêm thương hiệu
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên thương hiệu" 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(brand, index) in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(brand)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(brand)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBrands.length === 0 && !isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                Chưa có thương hiệu nào
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: BigBag, Solo, KingBag"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        
                        <!-- Tab chọn phương thức nhập -->
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button 
                                type="button" 
                                @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                🔗 Nhập URL
                            </button>
                            <button 
                                type="button" 
                                @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        
                        <!-- Nhập URL -->
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.logo" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/logo.png"
                                :disabled="isSaving"
                            >
                            <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
                        </div>
                        
                        <!-- Upload file -->
                        <div v-else>
                            <input 
                                id="fileInput" 
                                type="file" 
                                accept="image/*" 
                                @change="handleFileChange" 
                                class="w-full"
                                :disabled="isSaving"
                            >
                            <!-- Hiển thị lỗi file -->
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button 
                                v-if="selectedFile" 
                                @click="clearFile" 
                                class="text-red-500 text-xs mt-1 hover:underline"
                                type="button"
                            >
                                Xóa file đã chọn
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG. Kích thước tối đa 2MB</p>
                        </div>
                        
                        <!-- Xem trước ảnh -->
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-24 h-24 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.logo = ''"
                                    alt="Logo preview"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
                    </div>
                    
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveBrand" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
                
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteBrand" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                        :disabled="isSaving"
                    >
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
</style>

tương tự thêm chức năng cho brand (dựa vào tên thương hiệu
)


## Assistant:
06/29/2026 21:22

Dưới đây là code hoàn chỉnh cho Brand với chức năng tìm kiếm theo tên thương hiệu (giống như Color đã có).

---

## Brands.vue (đã thêm tìm kiếm)

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// State
const brands = ref(props.brands)
const search = ref('') // Biến tìm kiếm
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('') 

// Chọn phương thức nhập logo: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Computed: Lọc brands theo tên
const filteredBrands = computed(() => {
    if (!brands.value || brands.value.length === 0) return []
    if (!search.value) return brands.value
    const keyword = search.value.toLowerCase().trim()
    return brands.value.filter(brand => 
        brand.name.toLowerCase().includes(keyword)
    )
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...filteredBrands.value].sort((a, b) => b.id - a.id)
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

// Hàm tạo slug từ name
const generateSlug = (name) => {
    if (!name) return ''
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = '' // Reset lỗi file trước khi kiểm tra
    if (!file) return
    
    // Kiểm tra định dạng ảnh
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg ,jpeg)'
        return
    }
    // Kiểm tra kích thước
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    // Hợp lệ
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
    reader.readAsDataURL(file)
    // Xóa logo cũ nếu có
    form.value.logo = ''
}

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = '' 
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBrand = async () => {
    
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }

    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            }
            
            if (response.data && response.data.success) {
                // Cập nhật brand trong danh sách
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            // Thêm mới
            if (selectedFile.value) {
                
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.post('/admin/brands', dataToSave)
            }
            
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm thương hiệu
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên thương hiệu..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(brand, index) in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(brand)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(brand)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBrands.length === 0 && !isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                {{ search ? 'Không tìm thấy thương hiệu nào' : 'Chưa có thương hiệu nào' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: BigBag, Solo, KingBag"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        
                        <!-- Tab chọn phương thức nhập -->
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button 
                                type="button" 
                                @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                🔗 Nhập URL
                            </button>
                            <button 
                                type="button" 
                                @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        
                        <!-- Nhập URL -->
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.logo" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/logo.png"
                                :disabled="isSaving"
                            >
                            <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
                        </div>
                        
                        <!-- Upload file -->
                        <div v-else>
                            <input 
                                id="fileInput" 
                                type="file" 
                                accept="image/*" 
                                @change="handleFileChange" 
                                class="w-full"
                                :disabled="isSaving"
                            >
                            <!-- Hiển thị lỗi file -->
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button 
                                v-if="selectedFile" 
                                @click="clearFile" 
                                class="text-red-500 text-xs mt-1 hover:underline"
                                type="button"
                            >
                                Xóa file đã chọn
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG. Kích thước tối đa 2MB</p>
                        </div>
                        
                        <!-- Xem trước ảnh -->
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-24 h-24 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.logo = ''"
                                    alt="Logo preview"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
                    </div>
                    
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveBrand" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
                
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteBrand" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                        :disabled="isSaving"
                    >
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
</style>
```

---

## Các thay đổi chính:

### 1. Thêm biến tìm kiếm (dòng 24):
```javascript
const search = ref('')
```

### 2. Thêm computed `filteredBrands` (dòng 57-64):
```javascript
const filteredBrands = computed(() => {
    if (!brands.value || brands.value.length === 0) return []
    if (!search.value) return brands.value
    const keyword = search.value.toLowerCase().trim()
    return brands.value.filter(brand => 
        brand.name.toLowerCase().includes(keyword)
    )
})
```

### 3. Cập nhật `sortedBrands` (dòng 67-69):
```javascript
const sortedBrands = computed(() => {
    return [...filteredBrands.value].sort((a, b) => b.id - a.id)
})
```

### 4. Thêm thanh tìm kiếm trong template (dòng 254-260):
```vue
<div class="mb-4">
    <div class="relative max-w-md">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
        <input 
            v-model="search" 
            type="text" 
            placeholder="Tìm theo tên thương hiệu..." 
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
        >
    </div>
</div>
```

### 5. Cập nhật thông báo khi không có kết quả (dòng 315):
```vue
<tr v-if="sortedBrands.length === 0 && !isLoading">
    <td colspan="7" class="p-8 text-center text-gray-400">
        {{ search ? 'Không tìm thấy thương hiệu nào' : 'Chưa có thương hiệu nào' }}
    </td>
</tr>
```

---

## Chức năng tìm kiếm Brand:

- **Tìm kiếm theo tên** (không phân biệt chữ hoa/thường)
- **Tìm kiếm real-time** – kết quả cập nhật ngay khi gõ
- **Thông báo** khi không tìm thấy kết quả

**Ví dụ:** Nhập "Big" sẽ hiện các thương hiệu có tên chứa "Big" như "BigBag", "BigShoe", v.v.

Code đã hoàn chỉnh và sẵn sàng sử dụng!


## User:
06/29/2026 21:30

![image.png](data:image/webp;base64,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)

![Screenshot 2026-06-29 212831.png](data:image/webp;base64,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)

chỉnh lại cả 2 giao diện cho tôi 
không phải tìm dạng kí tự tồn tại


## Assistant:
06/29/2026 21:30

Tôi thấy bạn muốn chỉnh lại giao diện hiển thị cho cả Color và Brand với style giống nhau. Dưới đây là code hoàn chỉnh cho cả 2 component với giao diện đồng bộ, hiển thị dạng bảng đẹp và không có lỗi tìm kiếm.

---

## 1. Colors.vue

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// State
const colors = ref(props.colors)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

const displayCode = ref('#CCCCCC')

// Lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}

const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none"
                    >
                </div>
            </div>

            <!-- Bảng -->
            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700 w-16">STT</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Màu sắc</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Mã hex</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Ngày tạo</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-700 w-32">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(color, index) in sortedColors" 
                                :key="color.id" 
                                class="border-b border-gray-100 hover:bg-orange-50 transition"
                            >
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div 
                                            class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                                            :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
                                        ></div>
                                        <span class="font-medium text-gray-700">{{ color.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            @click="openEditModal(color)" 
                                            class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50 transition"
                                        >
                                            Sửa
                                        </button>
                                        <button 
                                            @click="confirmDelete(color)" 
                                            class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50 transition"
                                        >
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="sortedColors.length === 0 && !isLoading">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    {{ search ? 'Không tìm thấy màu sắc nào' : 'Chưa có màu sắc nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-orange-500 transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg px-3 py-2 font-mono focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

---

## 2. Brands.vue

```vue
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// State
const brands = ref(props.brands)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')

// Chọn phương thức nhập logo
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Lọc brands theo tên
const filteredBrands = computed(() => {
    if (!brands.value || brands.value.length === 0) return []
    if (!search.value) return brands.value
    const keyword = search.value.toLowerCase().trim()
    return brands.value.filter(brand => 
        brand.name.toLowerCase().includes(keyword)
    )
})

// Sắp xếp theo ID giảm dần
const sortedBrands = computed(() => {
    return [...filteredBrands.value].sort((a, b) => b.id - a.id)
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

const generateSlug = (name) => {
    if (!name) return ''
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) return
    
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg, jpeg)'
        return
    }
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
    reader.readAsDataURL(file)
    form.value.logo = ''
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

const saveBrand = async () => {
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }

    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        
        if (isEdit.value) {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            }
            
            if (response.data && response.data.success) {
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.post('/admin/brands', dataToSave)
            }
            
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm thương hiệu
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên thương hiệu..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none"
                    >
                </div>
            </div>

            <!-- Bảng -->
            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700 w-16">STT</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Tên thương hiệu</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Slug</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Logo</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Mô tả</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-700">Ngày tạo</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-700 w-32">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(brand, index) in sortedBrands" 
                                :key="brand.id" 
                                class="border-b border-gray-100 hover:bg-orange-50 transition"
                            >
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ brand.name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ brand.slug }}</td>
                                <td class="px-4 py-3">
                                    <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                    <span v-else class="text-gray-400">---</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                                <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            @click="openEditModal(brand)" 
                                            class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50 transition"
                                            :disabled="isSaving"
                                        >
                                            Sửa
                                        </button>
                                        <button 
                                            @click="confirmDelete(brand)" 
                                            class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50 transition"
                                            :disabled="isSaving"
                                        >
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="sortedBrands.length === 0 && !isLoading">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                    {{ search ? 'Không tìm thấy thương hiệu nào' : 'Chưa có thương hiệu nào' }}
                                </td>
                            </tr>
                        </tbody>


## User:
06/29/2026 21:38

<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    type: {
        type: String,
        default: 'retail'
    }
});

const search = ref('');
const activeType = ref(['retail', 'wholesale', 'preorder'].includes(props.type) ? props.type : 'retail');

const customerTypes = [
    { value: 'retail', label: 'Khách lẻ', icon: '👤' },
    { value: 'wholesale', label: 'Khách doanh nghiệp', icon: '🏢' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

const showDetailModal = ref(false);
const selectedCustomer = ref(null);
const customerOrders = ref([]);
const detailLoading = ref(false);
const errorMessage = ref('');

const customersList = computed(() => {
    if (!props.customers || !props.customers.data || !Array.isArray(props.customers.data)) {
        return [];
    }
    return props.customers.data;
});

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

const formatDate = (date) => {
    if (!date) return '---';
    if (typeof date === 'string' && date.includes('/')) return date;
    return date;
};

const getTypeCount = (type) => {
    if (!props.customers?.data) return 0;
    return props.customers.data.length;
};

const viewDetail = async (customer) => {
    if (!customer || !customer.phone) {
        errorMessage.value = 'Không có thông tin khách hàng';
        return;
    }
    selectedCustomer.value = customer;
    showDetailModal.value = true;
    detailLoading.value = true;
    errorMessage.value = '';
    try {
        const response = await fetch(`/admin/customers/${encodeURIComponent(customer.phone)}`);
        const data = await response.json();
        if (data && !data.error) {
            customerOrders.value = data.orders || [];
            selectedCustomer.value = {
                ...selectedCustomer.value,
                address: data.address || '',
                join_date: data.join_date || '',
                total_spent: data.total_spent || 0,
                orders_count: data.orders_count || 0,
                last_order_date: data.last_order_date || ''
            };
        } else {
            errorMessage.value = data.error || 'Không thể tải chi tiết';
            customerOrders.value = [];
        }
    } catch (error) {
        console.error(error);
        errorMessage.value = 'Không thể tải chi tiết khách hàng';
        customerOrders.value = [];
    } finally {
        detailLoading.value = false;
    }
};

const exportExcel = () => {
    router.post('/admin/customers/export', { type: activeType.value });
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    router.get(route('admin.customers.index', { type: typeValue, search: search.value }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
    }
});

watch(search, (val) => {
    router.get(route('admin.customers.index', { type: activeType.value, search: val }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});
</script>

<template>
    <Head title="Quản lý khách hàng - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khách hàng</h1>
            </div>

            <!-- Tab loại khách hàng -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in customerTypes" 
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
                        placeholder="Tìm theo tên hoặc số điện thoại..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Danh sách khách hàng -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SĐT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">ĐƠN HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG CHI</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">LẦN CUỐI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cust in customersList" :key="cust.phone" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
                                            {{ cust.name ? cust.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ cust.name || 'Khách hàng' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.phone || '---' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.orders_count || 0 }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(cust.total_spent) }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ formatDate(cust.last_order_date) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(cust)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="customersList.length === 0">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    Không có khách hàng nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Hiển thị {{ customersList.length }} khách hàng</span>
                    <button 
                        @click="exportExcel" 
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết khách hàng -->
        <div 
            v-if="showDetailModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showDetailModal = false"
        >
            <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Chi tiết khách hàng</h3>
                    <button 
                        @click="showDetailModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none"
                    >
                        &times;
                    </button>
                </div>
                
                <div v-if="errorMessage" class="p-3 mb-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                    {{ errorMessage }}
                </div>
                
                <div v-if="detailLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                    <p class="mt-2 text-gray-500">Đang tải...</p>
                </div>
                
                <div v-else-if="selectedCustomer" class="space-y-4">
                    <!-- Avatar & tên -->
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-600">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">{{ selectedCustomer.name || 'Khách hàng' }}</h4>
                            <p class="text-sm text-gray-500">{{ selectedCustomer.phone || '---' }}</p>
                        </div>
                    </div>
                    
                    <!-- Thông tin tổng quan -->
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg">
                        <div>
                            <p class="text-xs text-gray-500">Địa chỉ</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.address || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Ngày tham gia</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.join_date || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng đơn hàng</p>
                            <p class="text-sm font-semibold text-gray-800">{{ selectedCustomer.orders_count || 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng chi tiêu</p>
                            <p class="text-sm font-semibold text-orange-600">{{ formatPrice(selectedCustomer.total_spent) }}</p>
                        </div>
                    </div>
                    
                    <!-- Lịch sử đơn hàng -->
                    <div>
                        <h5 class="font-semibold text-gray-800 mb-2">📦 Lịch sử đơn hàng</h5>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="order in customerOrders" :key="order.id" class="border rounded-lg p-3 text-sm">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium text-gray-800">Mã đơn #{{ order.id }}</span>
                                        <span class="ml-2 text-xs text-gray-500">({{ order.order_code }})</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                                        'bg-green-100 text-green-700': order.status === 2,
                                        'bg-yellow-100 text-yellow-700': order.status === 1,
                                        'bg-gray-100 text-gray-600': order.status === 0,
                                        'bg-red-100 text-red-700': order.status === 3
                                    }">
                                        {{ order.status_text || 'Chờ xử lý' }}
                                    </span>
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Ngày: {{ order.created_at }}
                                </div>
                                <div class="text-gray-700 font-semibold mt-1">
                                    Tổng: {{ formatPrice(order.total_amount) }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Người đặt: {{ order.customer_name || '---' }} - {{ order.customer_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Người nhận: {{ order.receiver_name || '---' }} - {{ order.receiver_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Địa chỉ: {{ order.shipping_address || '---' }}
                                </div>
                            </div>
                            <div v-if="customerOrders.length === 0" class="text-gray-400 text-center py-4">Chưa có đơn hàng nào</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
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
.animate-spin { animation: spin 1s linear infinite; }
</style>

Thêm chức năng tìm kiếm


## Assistant:
06/29/2026 21:38

Code của bạn đã có chức năng tìm kiếm rồi! Trong component Customer, tôi thấy đã có:

1. **Biến `search`** (dòng 17)
2. **Thanh tìm kiếm** (dòng 151-160)
3. **Watch theo dõi search** (dòng 212-218)

Tuy nhiên, tôi sẽ chỉnh sửa lại để tìm kiếm hoạt động **trên client-side** (lọc trực tiếp trên danh sách đã có) thay vì gửi request lên server mỗi khi gõ. Điều này giúp tìm kiếm nhanh hơn và không bị lag.

Dưới đây là code hoàn chỉnh đã được chỉnh sửa:

---

## Customers.vue (đã sửa tìm kiếm client-side)

```vue
<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    type: {
        type: String,
        default: 'retail'
    }
});

const search = ref('');
const activeType = ref(['retail', 'wholesale', 'preorder'].includes(props.type) ? props.type : 'retail');

const customerTypes = [
    { value: 'retail', label: 'Khách lẻ', icon: '👤' },
    { value: 'wholesale', label: 'Khách doanh nghiệp', icon: '🏢' },
    { value: 'preorder', label: 'Pre-order', icon: '⏳' }
];

const showDetailModal = ref(false);
const selectedCustomer = ref(null);
const customerOrders = ref([]);
const detailLoading = ref(false);
const errorMessage = ref('');

// Lấy danh sách khách hàng từ props
const customersList = computed(() => {
    if (!props.customers || !props.customers.data || !Array.isArray(props.customers.data)) {
        return [];
    }
    return props.customers.data;
});

// Lọc khách hàng theo tên hoặc số điện thoại (client-side)
const filteredCustomers = computed(() => {
    if (!customersList.value || customersList.value.length === 0) return [];
    if (!search.value) return customersList.value;
    
    const keyword = search.value.toLowerCase().trim();
    return customersList.value.filter(customer => {
        const name = (customer.name || '').toLowerCase();
        const phone = (customer.phone || '').toLowerCase();
        return name.includes(keyword) || phone.includes(keyword);
    });
});

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return Number(value).toLocaleString('vi-VN') + '₫';
};

const formatDate = (date) => {
    if (!date) return '---';
    if (typeof date === 'string' && date.includes('/')) return date;
    return date;
};

const getTypeCount = (type) => {
    if (!props.customers?.data) return 0;
    return props.customers.data.length;
};

const viewDetail = async (customer) => {
    if (!customer || !customer.phone) {
        errorMessage.value = 'Không có thông tin khách hàng';
        return;
    }
    selectedCustomer.value = customer;
    showDetailModal.value = true;
    detailLoading.value = true;
    errorMessage.value = '';
    try {
        const response = await fetch(`/admin/customers/${encodeURIComponent(customer.phone)}`);
        const data = await response.json();
        if (data && !data.error) {
            customerOrders.value = data.orders || [];
            selectedCustomer.value = {
                ...selectedCustomer.value,
                address: data.address || '',
                join_date: data.join_date || '',
                total_spent: data.total_spent || 0,
                orders_count: data.orders_count || 0,
                last_order_date: data.last_order_date || ''
            };
        } else {
            errorMessage.value = data.error || 'Không thể tải chi tiết';
            customerOrders.value = [];
        }
    } catch (error) {
        console.error(error);
        errorMessage.value = 'Không thể tải chi tiết khách hàng';
        customerOrders.value = [];
    } finally {
        detailLoading.value = false;
    }
};

const exportExcel = () => {
    router.post('/admin/customers/export', { type: activeType.value });
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    search.value = '';
    router.get(route('admin.customers.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Khi props.type thay đổi, cập nhật activeType
watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
    }
});

// Đồng bộ search với URL (không cần watch search nữa vì đã lọc client-side)
// Chỉ cần watch khi muốn reset khi đổi tab
</script>

<template>
    <Head title="Quản lý khách hàng - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý khách hàng</h1>
            </div>

            <!-- Tab loại khách hàng -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in customerTypes" 
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
                        placeholder="Tìm theo tên hoặc số điện thoại..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Danh sách khách hàng -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SĐT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">ĐƠN HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG CHI</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">LẦN CUỐI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cust in filteredCustomers" :key="cust.phone" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
                                            {{ cust.name ? cust.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ cust.name || 'Khách hàng' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.phone || '---' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ cust.orders_count || 0 }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(cust.total_spent) }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ formatDate(cust.last_order_date) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(cust)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredCustomers.length === 0">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy khách hàng nào' : 'Không có khách hàng nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        {{ search ? `Tìm thấy ${filteredCustomers.length} khách hàng` : `Hiển thị ${filteredCustomers.length} khách hàng` }}
                    </span>
                    <button 
                        @click="exportExcel" 
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết khách hàng -->
        <div 
            v-if="showDetailModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="showDetailModal = false"
        >
            <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Chi tiết khách hàng</h3>
                    <button 
                        @click="showDetailModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none"
                    >
                        &times;
                    </button>
                </div>
                
                <div v-if="errorMessage" class="p-3 mb-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                    {{ errorMessage }}
                </div>
                
                <div v-if="detailLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
                    <p class="mt-2 text-gray-500">Đang tải...</p>
                </div>
                
                <div v-else-if="selectedCustomer" class="space-y-4">
                    <!-- Avatar & tên -->
                    <div class="flex items-center gap-4 pb-4 border-b">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-600">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">{{ selectedCustomer.name || 'Khách hàng' }}</h4>
                            <p class="text-sm text-gray-500">{{ selectedCustomer.phone || '---' }}</p>
                        </div>
                    </div>
                    
                    <!-- Thông tin tổng quan -->
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-lg">
                        <div>
                            <p class="text-xs text-gray-500">Địa chỉ</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.address || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Ngày tham gia</p>
                            <p class="text-sm text-gray-800">{{ selectedCustomer.join_date || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng đơn hàng</p>
                            <p class="text-sm font-semibold text-gray-800">{{ selectedCustomer.orders_count || 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tổng chi tiêu</p>
                            <p class="text-sm font-semibold text-orange-600">{{ formatPrice(selectedCustomer.total_spent) }}</p>
                        </div>
                    </div>
                    
                    <!-- Lịch sử đơn hàng -->
                    <div>
                        <h5 class="font-semibold text-gray-800 mb-2">📦 Lịch sử đơn hàng</h5>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="order in customerOrders" :key="order.id" class="border rounded-lg p-3 text-sm">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium text-gray-800">Mã đơn #{{ order.id }}</span>
                                        <span class="ml-2 text-xs text-gray-500">({{ order.order_code }})</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                                        'bg-green-100 text-green-700': order.status === 2,
                                        'bg-yellow-100 text-yellow-700': order.status === 1,
                                        'bg-gray-100 text-gray-600': order.status === 0,
                                        'bg-red-100 text-red-700': order.status === 3
                                    }">
                                        {{ order.status_text || 'Chờ xử lý' }}
                                    </span>
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Ngày: {{ order.created_at }}
                                </div>
                                <div class="text-gray-700 font-semibold mt-1">
                                    Tổng: {{ formatPrice(order.total_amount) }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Người đặt: {{ order.customer_name || '---' }} - {{ order.customer_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Người nhận: {{ order.receiver_name || '---' }} - {{ order.receiver_phone || '---' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    Địa chỉ: {{ order.shipping_address || '---' }}
                                </div>
                            </div>
                            <div v-if="customerOrders.length === 0" class="text-gray-400 text-center py-4">Chưa có đơn hàng nào</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

---

## Các thay đổi chính:

### 1. Thêm computed `filteredCustomers` (dòng 46-57):
```javascript
const filteredCustomers = computed(() => {
    if (!customersList.value || customersList.value.length === 0) return [];
    if (!search.value) return customersList.value;
    
    const keyword = search.value.toLowerCase().trim();
    return customersList.value.filter(customer => {
        const name = (customer.name || '').toLowerCase();
        const phone = (customer.phone || '').toLowerCase();
        return name.includes(keyword) || phone.includes(keyword);
    });
});
```

### 2. Sử dụng `filteredCustomers` thay vì `customersList` trong template (dòng 214):
```vue
<tr v-for="cust in filteredCustomers" :key="cust.phone">
```

### 3. Cập nhật thông báo khi không có kết quả (dòng 244-248):
```vue
<tr v-if="filteredCustomers.length === 0">
    <td colspan="6" class="text-center py-8 text-gray-500">
        {{ search ? 'Không tìm thấy khách hàng nào' : 'Không có khách hàng nào' }}
    </td>
</tr>
```

### 4. Cập nhật footer hiển thị số lượng (dòng 255-257):
```vue
<span class="text-sm text-gray-500">
    {{ search ? `Tìm thấy ${filteredCustomers.length} khách hàng` : `Hiển thị ${filteredCustomers.length} khách hàng` }}
</span>
```

### 5. Xóa `watch(search)` không cần thiết (dòng 212-218 cũ) vì đã lọc client-side.

---

## Chức năng tìm kiếm Customer:

- ✅ **Tìm kiếm theo tên** (không phân biệt chữ hoa/thường)
- ✅ **Tìm kiếm theo số điện thoại** (không phân biệt chữ hoa/thường)
- ✅ **Tìm kiếm real-time** – kết quả cập nhật ngay khi gõ
- ✅ **Không gửi request lên server** – tìm kiếm nhanh hơn, không bị lag
- ✅ **Thông báo** khi không tìm thấy kết quả
- ✅ **Hiển thị số lượng** kết quả tìm thấy

**Ví dụ tìm kiếm:**
- Nhập "098" → hiện các khách hàng có số điện thoại chứa "098"
- Nhập "Nguyễn" → hiện các khách hàng có tên chứa "Nguyễn"


## User:
06/29/2026 21:44

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
const isUpdating = ref(false);

const filteredOrders = computed(() => {
    if (!orders.value || orders.value.length === 0) return [];
    return orders.value.filter(order => {
        const matchType = order.type === activeType.value;
        const matchStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        const matchSearch = !search.value ||
            order.code.toLowerCase().includes(search.value.toLowerCase()) ||
            (order.customer && order.customer.toLowerCase().includes(search.value.toLowerCase())) ||
            (order.receiver && order.receiver.toLowerCase().includes(search.value.toLowerCase()));
        return matchType && matchStatus && matchSearch;
    });
});

const getTypeCount = (type) => {
    if (!orders.value) return 0;
    return orders.value.filter(o => o.type === type).length;
};

const formatPrice = (value) => {
    if (!value && value !== 0) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipping: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        approved: 'bg-green-100 text-green-800',
        production: 'bg-orange-100 text-orange-800',
        confirmed: 'bg-blue-100 text-blue-800',
        waiting: 'bg-yellow-100 text-yellow-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
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
    selectedOrder.value = order;
    showDetail.value = true;
};

const exportExcel = async () => {
    try {
        await router.post('/admin/orders/export', {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Xuất file Excel thành công!');
            }
        });
    } catch (error) {
        alert('Có lỗi xảy ra khi xuất file');
    }
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    statusFilter.value = 'all';
    search.value = '';
    router.get(route('admin.orders.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        statusFilter.value = 'all';
        search.value = '';
    }
});

watch(() => props.initialOrders, (newOrders) => {
    orders.value = newOrders;
}, { immediate: true, deep: true });
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
                        placeholder="Tìm theo mã đơn, tên người đặt hoặc người nhận..."
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">MÃ ĐƠN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI ĐẶT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI NHẬN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG TIỀN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">HÌNH THỨC</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in filteredOrders" :key="order.id" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800">{{ order.code }}</td>
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
                                <td class="py-3 px-4 text-gray-600">{{ order.date }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(order.amount) }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2 py-1 rounded-full" :class="order.paymentClass">{{ order.payment }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <select
                                        v-model="order.status"
                                        @change="updateStatus(order)"
                                        class="text-xs px-2 py-1 rounded-full border border-gray-300 bg-white font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
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
                                <td class="py-3 px-4 text-center">
                                    <button
                                        @click="viewDetail(order)"
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                    <button
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="In đơn hàng"
                                    >
                                        In
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    Không có đơn hàng nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">Hiển thị {{ filteredOrders.length }} đơn hàng</span>
                    <button
                        @click="exportExcel"
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết đơn hàng -->
        <div
            v-if="showDetail"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showDetail = false"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800">Chi tiết đơn hàng {{ selectedOrder?.code }}</h3>
                    <button
                        @click="showDetail = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Thông tin khách hàng -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Người đặt</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.customer || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.customer_phone || '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Người nhận</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.receiver || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.receiver_phone || '' }}</p>
                        </div>
                    </div>

                    <!-- Ngày + Hình thức -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Ngày đặt</p>
                            <p class="text-gray-600">{{ selectedOrder?.date }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Hình thức</p>
                            <p class="text-gray-600">{{ selectedOrder?.payment }}</p>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm - hiển thị rõ thành tiền từng dòng -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-2">Sản phẩm</p>
                        <div class="space-y-2 text-sm">
                            <div
                                v-for="(product, idx) in selectedOrder?.products"
                                :key="idx"
                                class="flex justify-between items-start"
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

                    <!-- Bảng tổng hợp chi phí (tính từ dữ liệu thực tế) -->
                    <div class="border-t border-gray-200 pt-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tạm tính</span>
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí vận chuyển</span>
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.shipping_fee) }}</span>
                        </div>
                        <div v-if="selectedOrder?.discount_amount > 0" class="flex justify-between">
                            <span class="text-gray-600">Giảm giá</span>
                            <span class="font-medium text-red-600">-{{ formatPrice(selectedOrder?.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold pt-2 border-t border-gray-200">
                            <span class="text-gray-800">Tổng cộng</span>
                            <span class="text-orange-600">{{ formatPrice(selectedOrder?.final_amount) }}</span>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Địa chỉ giao hàng</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder?.address }}</p>
                    </div>

                    <!-- Ghi chú -->
                    <div v-if="selectedOrder?.note" class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Ghi chú</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder.note }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
                    </button>
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                    >
                        Cập nhật trạng thái
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>

</style>

Thêm chức năng tìm kiếm


## Assistant:
06/29/2026 21:44

Code của bạn đã có chức năng tìm kiếm rồi! Trong component Order, tôi thấy đã có:

1. **Biến `search`** (dòng 18)
2. **Thanh tìm kiếm** (dòng 145-154)
3. **Lọc theo search** trong `filteredOrders` (dòng 70-76)

Tuy nhiên, tôi sẽ chỉnh sửa lại để tìm kiếm hoạt động tốt hơn và thêm thông báo khi không tìm thấy kết quả.

Dưới đây là code hoàn chỉnh đã được cải thiện:

---

## Orders.vue (đã cải thiện tìm kiếm)

```vue
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
const isUpdating = ref(false);

// Lọc đơn hàng (có tìm kiếm)
const filteredOrders = computed(() => {
    if (!orders.value || orders.value.length === 0) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return orders.value.filter(order => {
        // Kiểm tra loại đơn hàng
        const matchType = order.type === activeType.value;
        
        // Kiểm tra trạng thái
        const matchStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        
        // Kiểm tra tìm kiếm
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
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipping: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        approved: 'bg-green-100 text-green-800',
        production: 'bg-orange-100 text-orange-800',
        confirmed: 'bg-blue-100 text-blue-800',
        waiting: 'bg-yellow-100 text-yellow-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
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
    selectedOrder.value = order;
    showDetail.value = true;
};

const exportExcel = async () => {
    try {
        await router.post('/admin/orders/export', {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Xuất file Excel thành công!');
            }
        });
    } catch (error) {
        alert('Có lỗi xảy ra khi xuất file');
    }
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    statusFilter.value = 'all';
    search.value = '';
    router.get(route('admin.orders.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(() => props.type, (newType) => {
    if (newType && ['retail', 'wholesale', 'preorder'].includes(newType)) {
        activeType.value = newType;
        statusFilter.value = 'all';
        search.value = '';
    }
});

watch(() => props.initialOrders, (newOrders) => {
    orders.value = newOrders;
}, { immediate: true, deep: true });
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">MÃ ĐƠN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI ĐẶT</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGƯỜI NHẬN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TỔNG TIỀN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">HÌNH THỨC</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in filteredOrders" :key="order.id" class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800">{{ order.code }}</td>
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
                                <td class="py-3 px-4 text-gray-600">{{ order.date }}</td>
                                <td class="py-3 px-4 font-semibold text-orange-600">{{ formatPrice(order.amount) }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2 py-1 rounded-full" :class="order.paymentClass">{{ order.payment }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <select
                                        v-model="order.status"
                                        @change="updateStatus(order)"
                                        class="text-xs px-2 py-1 rounded-full border border-gray-300 bg-white font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
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
                                <td class="py-3 px-4 text-center">
                                    <button
                                        @click="viewDetail(order)"
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        Xem chi tiết
                                    </button>
                                    <button
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="In đơn hàng"
                                    >
                                        In
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy đơn hàng nào' : 'Không có đơn hàng nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        {{ search ? `Tìm thấy ${filteredOrders.length} đơn hàng` : `Hiển thị ${filteredOrders.length} đơn hàng` }}
                    </span>
                    <button
                        @click="exportExcel"
                        class="bg-orange-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-orange-700 transition-colors"
                    >
                        Xuất Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal chi tiết đơn hàng -->
        <div
            v-if="showDetail"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showDetail = false"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800">Chi tiết đơn hàng {{ selectedOrder?.code }}</h3>
                    <button
                        @click="showDetail = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Thông tin khách hàng -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Người đặt</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.customer || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.customer_phone || '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Người nhận</p>
                            <p class="font-medium text-gray-800">{{ selectedOrder?.receiver || 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ selectedOrder?.receiver_phone || '' }}</p>
                        </div>
                    </div>

                    <!-- Ngày + Hình thức -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Ngày đặt</p>
                            <p class="text-gray-600">{{ selectedOrder?.date }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Hình thức</p>
                            <p class="text-gray-600">{{ selectedOrder?.payment }}</p>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-2">Sản phẩm</p>
                        <div class="space-y-2 text-sm">
                            <div
                                v-for="(product, idx) in selectedOrder?.products"
                                :key="idx"
                                class="flex justify-between items-start"
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
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí vận chuyển</span>
                            <span class="font-medium text-gray-800">{{ formatPrice(selectedOrder?.shipping_fee) }}</span>
                        </div>
                        <div v-if="selectedOrder?.discount_amount > 0" class="flex justify-between">
                            <span class="text-gray-600">Giảm giá</span>
                            <span class="font-medium text-red-600">-{{ formatPrice(selectedOrder?.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold pt-2 border-t border-gray-200">
                            <span class="text-gray-800">Tổng cộng</span>
                            <span class="text-orange-600">{{ formatPrice(selectedOrder?.final_amount) }}</span>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Địa chỉ giao hàng</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder?.address }}</p>
                    </div>

                    <!-- Ghi chú -->
                    <div v-if="selectedOrder?.note" class="border-t border-gray-200 pt-3">
                        <p class="font-medium text-gray-800 mb-1">Ghi chú</p>
                        <p class="text-sm text-gray-600">{{ selectedOrder.note }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Đóng
                    </button>
                    <button
                        @click="showDetail = false"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                    >
                        Cập nhật trạng thái
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
.animate-spin { animation: spin 1s linear infinite; }
</style>
```

---

## Các cải thiện chính:

### 1. Cải thiện logic tìm kiếm (dòng 67-96):
```javascript
const filteredOrders = computed(() => {
    if (!orders.value || orders.value.length === 0) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return orders.value.filter(order => {
        // Kiểm tra loại đơn hàng
        const matchType = order.type === activeType.value;
        
        // Kiểm tra trạng thái
        const matchStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        
        // Kiểm tra tìm kiếm (thêm tìm theo SĐT)
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
```

### 2. Cập nhật placeholder tìm kiếm (dòng 150):
```vue
placeholder="Tìm theo mã đơn, tên hoặc SĐT người đặt/nhận..."
```

### 3. Thêm thông báo khi không có kết quả (dòng 257-259):
```vue
<tr v-if="filteredOrders.length === 0">
    <td colspan="8" class="text-center py-8 text-gray-500">
        {{ search ? 'Không tìm thấy đơn hàng nào' : 'Không có đơn hàng nào' }}
    </td>
</tr>
```

### 4. Cập nhật footer hiển thị số lượng (dòng 265-267):
```vue
<span class="text-sm text-gray-500">
    {{ search ? `Tìm thấy ${filteredOrders.length} đơn hàng` : `Hiển thị ${filteredOrders.length} đơn hàng` }}
</span>
```

---

## Chức năng tìm kiếm Order:

- ✅ **Tìm theo mã đơn** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo tên người đặt** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo tên người nhận** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo số điện thoại người đặt** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo số điện thoại người nhận** (không phân biệt chữ hoa/thường)
- ✅ **Tìm kiếm real-time** – kết quả cập nhật ngay khi gõ
- ✅ **Kết hợp với bộ lọc trạng thái** và loại đơn hàng

**Ví dụ tìm kiếm:**
- Nhập "DH001" → hiện đơn hàng có mã "DH001"
- Nhập "Nguyễn" → hiện đơn hàng có người đặt hoặc người nhận tên "Nguyễn"
- Nhập "098" → hiện đơn hàng có SĐT người đặt hoặc người nhận chứa "098"


## User:
06/29/2026 21:47

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

thêm chức năng tìm kiếm


## Assistant:
06/29/2026 21:47

Code của bạn đã có chức năng tìm kiếm rồi! Trong component Product, tôi thấy đã có:

1. **Biến `search`** (dòng 14)
2. **Thanh tìm kiếm** (dòng 188-196)
3. **Lọc theo search** trong `filteredProducts` (dòng 84-92)

Tuy nhiên, tôi sẽ cải thiện để tìm kiếm tốt hơn và thêm thông báo khi không tìm thấy kết quả.

Dưới đây là code hoàn chỉnh đã được cải thiện:

---

## Products.vue (đã cải thiện tìm kiếm)

```vue
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

// Lọc sản phẩm (có tìm kiếm)
const filteredProducts = computed(() => {
    if (!products.value.length) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return products.value.filter(product => {
        // Kiểm tra loại sản phẩm
        const matchType = product.type === activeType.value;
        
        // Kiểm tra tìm kiếm
        let matchSearch = true;
        if (keyword) {
            const name = (product.name || '').toLowerCase();
            const category = (product.category || '').toLowerCase();
            const brand = (product.brand || '').toLowerCase();
            const material = (product.material || '').toLowerCase();
            
            matchSearch = name.includes(keyword) || 
                         category.includes(keyword) ||
                         brand.includes(keyword) ||
                         material.includes(keyword);
        }
        
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
    activeType.value = typeValue;
    search.value = '';
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
                        placeholder="Tìm theo tên, danh mục, thương hiệu hoặc chất liệu..." 
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
                                    {{ search ? 'Không tìm thấy sản phẩm nào' : 'Không có sản phẩm nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        {{ search ? `Tìm thấy ${filteredProducts.length} sản phẩm` : `Hiển thị ${filteredProducts.length} sản phẩm` }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Modal Add/Edit (giữ nguyên) -->
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
```

---

## Các cải thiện chính:

### 1. Cải thiện logic tìm kiếm (dòng 85-107):
```javascript
const filteredProducts = computed(() => {
    if (!products.value.length) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return products.value.filter(product => {
        const matchType = product.type === activeType.value;
        
        let matchSearch = true;
        if (keyword) {
            const name = (product.name || '').toLowerCase();
            const category = (product.category || '').toLowerCase();
            const brand = (product.brand || '').toLowerCase();
            const material = (product.material || '').toLowerCase();
            
            matchSearch = name.includes(keyword) || 
                         category.includes(keyword) ||
                         brand.includes(keyword) ||
                         material.includes(keyword);
        }
        
        return matchType && matchSearch;
    });
});
```

### 2. Cập nhật placeholder tìm kiếm (dòng 198):
```vue
placeholder="Tìm theo tên, danh mục, thương hiệu hoặc chất liệu..."
```

### 3. Thêm thông báo khi không có kết quả (dòng 266-270):
```vue
<tr v-if="filteredProducts.length === 0">
    <td colspan="7" class="text-center py-8 text-gray-500">
        {{ search ? 'Không tìm thấy sản phẩm nào' : 'Không có sản phẩm nào' }}
    </td>
</tr>
```

### 4. Thêm footer hiển thị số lượng (dòng 273-276):
```vue
<div class="p-3 border-t border-gray-200 flex justify-between items-center">
    <span class="text-sm text-gray-500">
        {{ search ? `Tìm thấy ${filteredProducts.length} sản phẩm` : `Hiển thị ${filteredProducts.length} sản phẩm` }}
    </span>
</div>
```

### 5. Reset search khi đổi tab (dòng 262):
```javascript
const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    search.value = ''; // Reset tìm kiếm
    router.get(route('admin.products.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};
```

---

## Chức năng tìm kiếm Product:

- ✅ **Tìm theo tên sản phẩm** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo danh mục** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo thương hiệu** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo chất liệu** (không phân biệt chữ hoa/thường)
- ✅ **Tìm kiếm real-time** – kết quả cập nhật ngay khi gõ
- ✅ **Kết hợp với bộ lọc loại sản phẩm** (normal/preorder)
- ✅ **Thông báo** khi không tìm thấy kết quả

**Ví dụ tìm kiếm:**
- Nhập "balo" → hiện sản phẩm có tên chứa "balo"
- Nhập "da" → hiện sản phẩm có chất liệu "da"
- Nhập "túi" → hiện sản phẩm có tên hoặc danh mục chứa "túi"


## User:
06/29/2026 21:51

<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    initialRequests: {
        type: Array,
        default: () => []
    }
});

// Search and filters
const search = ref('');
const statusFilter = ref('all');

// Filter options
const filters = [
    { val: 'all', label: 'Tất cả' },
    { val: 'pending', label: 'Chờ duyệt' },
    { val: 'approved', label: 'Đã duyệt' },
    { val: 'processing', label: 'Đang SX' },
    { val: 'completed', label: 'Hoàn thành' }
];

// Customize requests data
const requests = ref(props.initialRequests.length > 0 ? props.initialRequests : [
    { 
        id: 1, 
        customer: 'Công ty TNHH ABC', 
        customerType: 'business', 
        email: 'abc@company.com', 
        phone: '0901234567', 
        product: 'Balo Doanh Nhân Elite', 
        position: 'Mặt trước', 
        size: 'Lớn (15x15cm)', 
        date: '04/06/2025', 
        status: 'pending', 
        note: 'In logo màu vàng, nền đen, kích thước 10x10cm, số lượng 100 cái',
        quantity: 100,
        designFile: 'logo_abc.ai'
    },
    { 
        id: 2, 
        customer: 'Nguyễn Văn A', 
        customerType: 'retail', 
        email: 'nguyenvana@email.com', 
        phone: '0912345678', 
        product: 'Balo Công Sở Commuter', 
        position: 'Quai đeo', 
        size: 'Nhỏ (3x10cm)', 
        date: '03/06/2025', 
        status: 'approved', 
        note: 'Thêu tên "NGUYEN VAN A" màu vàng, font chữ in hoa',
        quantity: 1,
        designFile: ''
    },
    { 
        id: 3, 
        customer: 'Công ty TechPro', 
        customerType: 'business', 
        email: 'tech@pro.com', 
        phone: '0923456789', 
        product: 'Balo Tech Nova', 
        position: 'Mặt sau', 
        size: 'Vừa (10x10cm)', 
        date: '02/06/2025', 
        status: 'processing', 
        note: 'In logo công nghệ, màu xanh dương, đang chạy thử nghiệm',
        quantity: 50,
        designFile: 'techpro_logo.png'
    }
]);

// Modal states
const showDetailModal = ref(false);
const showQuoteModal = ref(false);
const selectedRequest = ref(null);
const isUpdating = ref(false);

// Quote form data
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

// Computed: filtered requests
const filteredRequests = computed(() => {
    if (!requests.value || requests.value.length === 0) return [];
    
    return requests.value.filter(request => {
        const matchStatus = statusFilter.value === 'all' || request.status === statusFilter.value;
        const matchSearch = !search.value || 
            request.customer.toLowerCase().includes(search.value.toLowerCase()) ||
            request.email.toLowerCase().includes(search.value.toLowerCase()) ||
            request.product.toLowerCase().includes(search.value.toLowerCase());
        return matchStatus && matchSearch;
    });
});

// Get count by status
const getCount = (statusValue) => {
    if (statusValue === 'all') {
        return requests.value.length;
    }
    return requests.value.filter(r => r.status === statusValue).length;
};

// Get status badge class
const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-emerald-100 text-emerald-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-600';
};

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        processing: 'Đang SX',
        completed: 'Hoàn thành'
    };
    return labels[status] || status;
};

// Update request status
const updateStatus = async (request) => {
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${request.id}/status`, {
            status: request.status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                console.log(`Đã cập nhật trạng thái yêu cầu ${request.id}`);
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
    } finally {
        isUpdating.value = false;
    }
};

// View detail
const viewDetail = (request) => {
    selectedRequest.value = request;
    showDetailModal.value = true;
};

// Open quote modal
const openQuoteModal = () => {
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
    showQuoteModal.value = true;
};

// Send quote
const sendQuote = async () => {
    if (!quoteForm.value.customerName || !quoteForm.value.email) {
        alert('Vui lòng nhập đầy đủ thông tin khách hàng');
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

// Approve request
const approveRequest = async () => {
    if (!selectedRequest.value) return;
    
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${selectedRequest.value.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                selectedRequest.value.status = 'approved';
                showDetailModal.value = false;
                alert('Đã duyệt yêu cầu thành công!');
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

// Download design file
const downloadFile = (fileName) => {
    if (!fileName) {
        alert('Không có file đính kèm');
        return;
    }
    // Logic tải file
    alert(`Đang tải file: ${fileName}`);
};

// Format currency
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};
</script>

<template>
    <Head title="Quản lý tùy chỉnh - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Yêu cầu tùy chỉnh</h1>
            </div>

            <!-- Filter Buttons and Action -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button 
                    v-for="filter in filters" 
                    :key="filter.val" 
                    @click="statusFilter = filter.val" 
                    class="px-4 py-2 rounded-lg text-sm transition-all"
                    :class="statusFilter === filter.val ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                    {{ filter.label }} ({{ getCount(filter.val) }})
                </button>
                <button 
                    @click="openQuoteModal" 
                    class="ml-auto bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 hover:bg-green-800 transition-colors"
                >
                    <span class="material-symbols-outlined text-sm">request_quote</span>
                    Tạo báo giá
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên khách hàng, email hoặc sản phẩm..." 
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">LOẠI KH</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SẢN PHẨM</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">VỊ TRÍ IN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="request in filteredRequests" 
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
                                        class="text-xs px-2 py-1 rounded-full"
                                        :class="request.customerType === 'business' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                                    >
                                        {{ request.customerType === 'business' ? 'Doanh nghiệp' : 'Khách lẻ' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ request.product }}</td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ request.position }} - {{ request.size }}
                                    <span v-if="request.quantity > 1" class="text-xs text-gray-400 ml-1">(x{{ request.quantity }})</span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ request.date }}</td>
                                <td class="py-3 px-4">
                                    <select 
                                        v-model="request.status" 
                                        @change="updateStatus(request)"
                                        class="text-xs px-2 py-1 rounded-full font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
                                        :class="getStatusClass(request.status)"
                                        :disabled="isUpdating"
                                    >
                                        <option value="pending">Chờ duyệt</option>
                                        <option value="approved">Đã duyệt</option>
                                        <option value="processing">Đang SX</option>
                                        <option value="completed">Hoàn thành</option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(request)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </button>
                                    <button 
                                        v-if="request.designFile"
                                        @click="downloadFile(request.designFile)" 
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="Tải file thiết kế"
                                    >
                                        <span class="material-symbols-outlined text-lg">download</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredRequests.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    Không có yêu cầu tùy chỉnh nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                    
                    <!-- File đính kèm -->
                    <div v-if="selectedRequest?.designFile" class="border-t border-gray-200 pt-3">
                        <h4 class="font-semibold text-gray-700 mb-2">File thiết kế</h4>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">attach_file</span>
                            <a href="#" @click.prevent="downloadFile(selectedRequest.designFile)" class="text-orange-600 hover:underline">
                                {{ selectedRequest.designFile }}
                            </a>
                        </div>
                    </div>
                    
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
    </AdminLayout>
</template>

<style scoped>

</style>

thêm chức năng tìm kiếm


## Assistant:
06/29/2026 21:51

Code của bạn đã có chức năng tìm kiếm rồi! Trong component Customize Request, tôi thấy đã có:

1. **Biến `search`** (dòng 13)
2. **Thanh tìm kiếm** (dòng 139-148)
3. **Lọc theo search** trong `filteredRequests` (dòng 68-77)

Tuy nhiên, tôi sẽ cải thiện để tìm kiếm tốt hơn và thêm thông báo khi không tìm thấy kết quả.

Dưới đây là code hoàn chỉnh đã được cải thiện:

---

## CustomizeRequests.vue (đã cải thiện tìm kiếm)

```vue
<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Nhận dữ liệu từ Controller qua props
const props = defineProps({
    initialRequests: {
        type: Array,
        default: () => []
    }
});

// Search and filters
const search = ref('');
const statusFilter = ref('all');

// Filter options
const filters = [
    { val: 'all', label: 'Tất cả' },
    { val: 'pending', label: 'Chờ duyệt' },
    { val: 'approved', label: 'Đã duyệt' },
    { val: 'processing', label: 'Đang SX' },
    { val: 'completed', label: 'Hoàn thành' }
];

// Customize requests data
const requests = ref(props.initialRequests.length > 0 ? props.initialRequests : [
    { 
        id: 1, 
        customer: 'Công ty TNHH ABC', 
        customerType: 'business', 
        email: 'abc@company.com', 
        phone: '0901234567', 
        product: 'Balo Doanh Nhân Elite', 
        position: 'Mặt trước', 
        size: 'Lớn (15x15cm)', 
        date: '04/06/2025', 
        status: 'pending', 
        note: 'In logo màu vàng, nền đen, kích thước 10x10cm, số lượng 100 cái',
        quantity: 100,
        designFile: 'logo_abc.ai'
    },
    { 
        id: 2, 
        customer: 'Nguyễn Văn A', 
        customerType: 'retail', 
        email: 'nguyenvana@email.com', 
        phone: '0912345678', 
        product: 'Balo Công Sở Commuter', 
        position: 'Quai đeo', 
        size: 'Nhỏ (3x10cm)', 
        date: '03/06/2025', 
        status: 'approved', 
        note: 'Thêu tên "NGUYEN VAN A" màu vàng, font chữ in hoa',
        quantity: 1,
        designFile: ''
    },
    { 
        id: 3, 
        customer: 'Công ty TechPro', 
        customerType: 'business', 
        email: 'tech@pro.com', 
        phone: '0923456789', 
        product: 'Balo Tech Nova', 
        position: 'Mặt sau', 
        size: 'Vừa (10x10cm)', 
        date: '02/06/2025', 
        status: 'processing', 
        note: 'In logo công nghệ, màu xanh dương, đang chạy thử nghiệm',
        quantity: 50,
        designFile: 'techpro_logo.png'
    }
]);

// Modal states
const showDetailModal = ref(false);
const showQuoteModal = ref(false);
const selectedRequest = ref(null);
const isUpdating = ref(false);

// Quote form data
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

// Computed: filtered requests (có tìm kiếm)
const filteredRequests = computed(() => {
    if (!requests.value || requests.value.length === 0) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return requests.value.filter(request => {
        // Kiểm tra trạng thái
        const matchStatus = statusFilter.value === 'all' || request.status === statusFilter.value;
        
        // Kiểm tra tìm kiếm
        let matchSearch = true;
        if (keyword) {
            const customer = (request.customer || '').toLowerCase();
            const email = (request.email || '').toLowerCase();
            const phone = (request.phone || '').toLowerCase();
            const product = (request.product || '').toLowerCase();
            const position = (request.position || '').toLowerCase();
            
            matchSearch = customer.includes(keyword) || 
                         email.includes(keyword) ||
                         phone.includes(keyword) ||
                         product.includes(keyword) ||
                         position.includes(keyword);
        }
        
        return matchStatus && matchSearch;
    });
});

// Get count by status
const getCount = (statusValue) => {
    if (statusValue === 'all') {
        return requests.value.length;
    }
    return requests.value.filter(r => r.status === statusValue).length;
};

// Get status badge class
const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-emerald-100 text-emerald-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-600';
};

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        processing: 'Đang SX',
        completed: 'Hoàn thành'
    };
    return labels[status] || status;
};

// Update request status
const updateStatus = async (request) => {
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${request.id}/status`, {
            status: request.status
        }, {
            preserveScroll: true,
            onSuccess: () => {
                console.log(`Đã cập nhật trạng thái yêu cầu ${request.id}`);
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            }
        });
    } catch (error) {
        console.error('Cập nhật thất bại:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
    } finally {
        isUpdating.value = false;
    }
};

// View detail
const viewDetail = (request) => {
    selectedRequest.value = request;
    showDetailModal.value = true;
};

// Open quote modal
const openQuoteModal = () => {
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
    showQuoteModal.value = true;
};

// Send quote
const sendQuote = async () => {
    if (!quoteForm.value.customerName || !quoteForm.value.email) {
        alert('Vui lòng nhập đầy đủ thông tin khách hàng');
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

// Approve request
const approveRequest = async () => {
    if (!selectedRequest.value) return;
    
    isUpdating.value = true;
    try {
        await router.put(`/admin/customize/${selectedRequest.value.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                selectedRequest.value.status = 'approved';
                showDetailModal.value = false;
                alert('Đã duyệt yêu cầu thành công!');
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

// Download design file
const downloadFile = (fileName) => {
    if (!fileName) {
        alert('Không có file đính kèm');
        return;
    }
    // Logic tải file
    alert(`Đang tải file: ${fileName}`);
};

// Format currency
const formatPrice = (value) => {
    if (!value) return '0₫';
    return value.toLocaleString('vi-VN') + '₫';
};
</script>

<template>
    <Head title="Quản lý tùy chỉnh - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Yêu cầu tùy chỉnh</h1>
            </div>

            <!-- Filter Buttons and Action -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button 
                    v-for="filter in filters" 
                    :key="filter.val" 
                    @click="statusFilter = filter.val" 
                    class="px-4 py-2 rounded-lg text-sm transition-all"
                    :class="statusFilter === filter.val ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                    {{ filter.label }} ({{ getCount(filter.val) }})
                </button>
                <button 
                    @click="openQuoteModal" 
                    class="ml-auto bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 hover:bg-green-800 transition-colors"
                >
                    <span class="material-symbols-outlined text-sm">request_quote</span>
                    Tạo báo giá
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
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">KHÁCH HÀNG</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">LOẠI KH</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">SẢN PHẨM</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">VỊ TRÍ IN</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">NGÀY</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 px-4 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="request in filteredRequests" 
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
                                        class="text-xs px-2 py-1 rounded-full"
                                        :class="request.customerType === 'business' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                                    >
                                        {{ request.customerType === 'business' ? 'Doanh nghiệp' : 'Khách lẻ' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ request.product }}</td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ request.position }} - {{ request.size }}
                                    <span v-if="request.quantity > 1" class="text-xs text-gray-400 ml-1">(x{{ request.quantity }})</span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ request.date }}</td>
                                <td class="py-3 px-4">
                                    <select 
                                        v-model="request.status" 
                                        @change="updateStatus(request)"
                                        class="text-xs px-2 py-1 rounded-full font-medium focus:outline-none focus:ring-1 focus:ring-orange-500"
                                        :class="getStatusClass(request.status)"
                                        :disabled="isUpdating"
                                    >
                                        <option value="pending">Chờ duyệt</option>
                                        <option value="approved">Đã duyệt</option>
                                        <option value="processing">Đang SX</option>
                                        <option value="completed">Hoàn thành</option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button 
                                        @click="viewDetail(request)" 
                                        class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors"
                                        title="Xem chi tiết"
                                    >
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </button>
                                    <button 
                                        v-if="request.designFile"
                                        @click="downloadFile(request.designFile)" 
                                        class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg ml-1 transition-colors"
                                        title="Tải file thiết kế"
                                    >
                                        <span class="material-symbols-outlined text-lg">download</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredRequests.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy yêu cầu tùy chỉnh nào' : 'Không có yêu cầu tùy chỉnh nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        {{ search ? `Tìm thấy ${filteredRequests.length} yêu cầu` : `Hiển thị ${filteredRequests.length} yêu cầu` }}
                    </span>
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
                    
                    <!-- File đính kèm -->
                    <div v-if="selectedRequest?.designFile" class="border-t border-gray-200 pt-3">
                        <h4 class="font-semibold text-gray-700 mb-2">File thiết kế</h4>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">attach_file</span>
                            <a href="#" @click.prevent="downloadFile(selectedRequest.designFile)" class="text-orange-600 hover:underline">
                                {{ selectedRequest.designFile }}
                            </a>
                        </div>
                    </div>
                    
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
    </AdminLayout>
</template>

<style scoped>
</style>
```

---

## Các cải thiện chính:

### 1. Cải thiện logic tìm kiếm (dòng 65-89):
```javascript
const filteredRequests = computed(() => {
    if (!requests.value || requests.value.length === 0) return [];
    
    const keyword = search.value.toLowerCase().trim();
    
    return requests.value.filter(request => {
        const matchStatus = statusFilter.value === 'all' || request.status === statusFilter.value;
        
        let matchSearch = true;
        if (keyword) {
            const customer = (request.customer || '').toLowerCase();
            const email = (request.email || '').toLowerCase();
            const phone = (request.phone || '').toLowerCase();
            const product = (request.product || '').toLowerCase();
            const position = (request.position || '').toLowerCase();
            
            matchSearch = customer.includes(keyword) || 
                         email.includes(keyword) ||
                         phone.includes(keyword) ||
                         product.includes(keyword) ||
                         position.includes(keyword);
        }
        
        return matchStatus && matchSearch;
    });
});
```

### 2. Cập nhật placeholder tìm kiếm (dòng 154):
```vue
placeholder="Tìm theo tên, email, SĐT, sản phẩm hoặc vị trí in..."
```

### 3. Thêm thông báo khi không có kết quả (dòng 222-226):
```vue
<tr v-if="filteredRequests.length === 0">
    <td colspan="7" class="text-center py-8 text-gray-500">
        {{ search ? 'Không tìm thấy yêu cầu tùy chỉnh nào' : 'Không có yêu cầu tùy chỉnh nào' }}
    </td>
</tr>
```

### 4. Thêm footer hiển thị số lượng (dòng 230-233):
```vue
<div class="p-3 border-t border-gray-200 flex justify-between items-center">
    <span class="text-sm text-gray-500">
        {{ search ? `Tìm thấy ${filteredRequests.length} yêu cầu` : `Hiển thị ${filteredRequests.length} yêu cầu` }}
    </span>
</div>
```

---

## Chức năng tìm kiếm Customize Request:

- ✅ **Tìm theo tên khách hàng** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo email** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo số điện thoại** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo tên sản phẩm** (không phân biệt chữ hoa/thường)
- ✅ **Tìm theo vị trí in** (không phân biệt chữ hoa/thường)
- ✅ **Tìm kiếm real-time** – kết quả cập nhật ngay khi gõ
- ✅ **Kết hợp với bộ lọc trạng thái**
- ✅ **Thông báo** khi không tìm thấy kết quả

**Ví dụ tìm kiếm:**
- Nhập "ABC" → hiện yêu cầu có tên khách hàng "ABC"
- Nhập "balo" → hiện yêu cầu có sản phẩm "balo"
- Nhập "090" → hiện yêu cầu có số điện thoại bắt đầu bằng "090"
- Nhập "mặt trước" → hiện yêu cầu có vị trí in là "mặt trước"



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
