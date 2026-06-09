<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    // Hiển thị trang danh sách
    public function index()
    {
        $brands = Brand::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Admin/Brands', [
            'brands' => $brands
        ]);
    }

    // API: Lấy danh sách
    public function getBrands()
    {
        try {
            $brands = Brand::orderBy('name')->get();
            return response()->json($brands);
        } catch (\Exception $e) {
            Log::error('Lỗi getBrands: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: Thêm mới
    public function store(Request $request)
    {
        try {
            Log::info('Store brand request:', $request->all());
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'slug' => 'required|string|unique:brands,slug',
                'logo' => 'nullable|string|max:500',
                'description' => 'nullable|string'
            ]);

            $brand = Brand::create($validated);
            
            Log::info('Brand created successfully:', ['id' => $brand->id]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm thương hiệu thành công!',
                'data' => $brand
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Lỗi store brand: ' . $e->getMessage());
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
            Log::info('Update brand request:', ['id' => $id, 'data' => $request->all()]);
            
            $brand = Brand::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name,' . $id,
                'slug' => 'required|string|unique:brands,slug,' . $id,
                'logo' => 'nullable|string|max:500',
                'description' => 'nullable|string'
            ]);

            $brand->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thương hiệu thành công!',
                'data' => $brand
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi update brand: ' . $e->getMessage());
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
            Log::info('Delete brand request:', ['id' => $id]);
            
            $brand = Brand::findOrFail($id);
            
            // Kiểm tra ràng buộc
            if ($brand->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa vì có sản phẩm đang sử dụng thương hiệu này!'
                ], 400);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thương hiệu thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete brand: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}