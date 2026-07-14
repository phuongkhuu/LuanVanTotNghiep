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
            return response()->json([
                'error' => 'Không thể tải danh sách màu sắc. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ], [
                'code.regex' => 'Mã màu phải là mã hex hợp lệ (ví dụ: #FF0000 hoặc #F00)',
                'code.max'   => 'Mã màu không được vượt quá 20 ký tự.',
                'name.max'   => 'Tên màu không được vượt quá 255 ký tự.'
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
                'message' => 'Đã xảy ra lỗi khi thêm màu. Vui lòng thử lại sau.'
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
            ], [
                'code.regex' => 'Mã màu phải là mã hex hợp lệ (ví dụ: #FF0000 hoặc #F00)',
                'code.max'   => 'Mã màu không được vượt quá 20 ký tự.',
                'name.max'   => 'Tên màu không được vượt quá 255 ký tự.'
            ]);

            // Chuẩn bị dữ liệu cần cập nhật
            $data = [];

            // Xử lý tên
            if (!empty($validated['name'])) {
                $data['name'] = $this->capitalizeName($validated['name']);
            }

            // Xử lý mã
            if (!empty($validated['code'])) {
                $data['code'] = $this->normalizeHexCode($validated['code']);
            }

            // Nếu chỉ có code mà không có name → tự tạo name từ code
            if (empty($validated['name']) && !empty($validated['code'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $data['name'] = $this->capitalizeName($generatedName);
                } else {
                    // Giữ name cũ nếu không tạo được
                    $data['name'] = $color->name;
                }
                $data['code'] = $code;
            }

            // Nếu chỉ có name mà không có code → giữ nguyên code cũ (không tự tạo)
            // Nếu cả hai đều rỗng → báo lỗi
            if (empty($validated['name']) && empty($validated['code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Kiểm tra trùng tên (loại trừ chính nó)
            if (isset($data['name']) && $data['name'] !== $color->name &&
                Color::where('name', $data['name'])->where('id', '!=', $color->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $data['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (loại trừ chính nó)
            if (isset($data['code']) && $data['code'] !== $color->code &&
                Color::where('code', $data['code'])->where('id', '!=', $color->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $data['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($data);

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
                'message' => 'Đã xảy ra lỗi khi cập nhật màu. Vui lòng thử lại sau.'
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
                'message' => 'Đã xảy ra lỗi khi xóa màu. Vui lòng thử lại sau.'
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