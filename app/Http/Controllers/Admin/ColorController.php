<?php
// app/Http/Controllers/Admin/ColorController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ColorController extends Controller
{
    // Hiển thị trang danh sách
    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }

    // API: Lấy danh sách
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

    // API: Thêm mới
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:colors,name',
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

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:colors,name,' . $id,
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

    // API: Xóa - Kiểm tra ràng buộc với product_variant
    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            // Kiểm tra xem có biến thể sản phẩm nào đang sử dụng màu này không
            if ($color->productVariants()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $color->productVariants()->count() . ' biến thể sản phẩm đang sử dụng!'
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