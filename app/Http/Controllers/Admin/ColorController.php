<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ColorController extends Controller
{
    // Hiển thị trang danh sách (Inertia)
    public function index()
    {
        $colors = Color::orderBy('name')->get();
        return Inertia::render('Admin/Colors', ['colors' => $colors]);
    }

    // API: Lấy danh sách (dùng cho fetch nếu cần)
    public function getColors()
    {
        try {
            $colors = Color::orderBy('name')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: Thêm mới
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:colors,name',
                'code' => 'nullable|string|max:50|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ]);

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Cập nhật
    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);

            // Kiểm tra ràng buộc với biến thể sản phẩm
            $variantCount = $color->productVariants()->count();
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể sửa màu này vì đang có ' . $variantCount . ' biến thể sản phẩm đang sử dụng!'
                ], 400);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:colors,name,' . $id,
                'code' => 'nullable|string|max:50|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ]);

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Xóa
    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);

            $variantCount = $color->productVariants()->count();
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' biến thể sản phẩm đang sử dụng!'
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
}