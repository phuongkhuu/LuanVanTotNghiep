<?php
// app/Http/Controllers/Admin/BrandController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Absolute path to htdocs/image (or base_path('image'))
     */
    protected function imageDir(): string
    {
        return base_path('image');
    }

    /**
     * Ensure image directory exists
     */
    protected function ensureImageDir(): void
    {
        $dir = $this->imageDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Save raw image contents into image directory
     * Returns public URL path
     */
    protected function saveContentToImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();

        $filename = uniqid() . '.' . $ext;
        $path = $this->imageDir() . '/' . $filename;
        file_put_contents($path, $contents);

        return '/image/' . $filename;
    }

    /**
     * Delete image file if it exists in image directory
     */
    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $parsed = parse_url($imageUrl);
        $path = ltrim($parsed['path'] ?? $imageUrl, '/');

        // Only allow deletion inside image/
        if (!str_starts_with($path, 'image/')) return;

        $fullPath = base_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * Kiểm tra xem thương hiệu có đang được sử dụng trong sản phẩm không
     */
    protected function isBrandInUse($brandId): bool
    {
        // Kiểm tra trong bảng products (thương hiệu được gán cho sản phẩm)
        $productCount = \App\Models\Product::where('brand_id', $brandId)->count();
        
        if ($productCount > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Lấy số lượng sản phẩm đang sử dụng thương hiệu
     */
    protected function getBrandUsageCount($brandId): int
    {
        return \App\Models\Product::where('brand_id', $brandId)->count();
    }

    // Hiển thị trang danh sách
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->get();
        
        return Inertia::render('Admin/Brands', [
            'brands' => $brands
        ]);
    }

    // API: Lấy danh sách
    public function getBrands()
    {
        try {
            $brands = Brand::orderBy('id', 'desc')->get();
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
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'slug' => 'nullable|string|unique:brands,slug',
                'logo' => 'nullable|string|max:500',
                'description' => 'nullable|string',
                'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            // Tạo slug từ name nếu không có slug
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }
            
            // Đảm bảo slug không trùng
            $base = $validated['slug'];
            $i = 1;
            while (Brand::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $base . '-' . $i++;
            }

            // Xử lý logo từ file upload
            if ($request->hasFile('logo_file')) {
                $this->ensureImageDir();
                $file = $request->file('logo_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = uniqid() . '.' . $ext;
                $file->move($this->imageDir(), $filename);
                $validated['logo'] = '/image/' . $filename;
                unset($validated['logo_file']);
            }
            // Xử lý logo từ URL (nếu không có file)
            elseif (!empty($validated['logo'])) {
                try {
                    $res = Http::timeout(15)->get($validated['logo']);
                    if (!$res->ok()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không thể tải logo từ URL'
                        ], 400);
                    }
                    $type = $res->header('Content-Type', '');
                    if (!str_starts_with($type, 'image/')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'URL không phải ảnh'
                        ], 400);
                    }
                    $ext = explode('/', $type)[1] ?? 'png';
                    $validated['logo'] = $this->saveContentToImage($res->body(), $ext);
                } catch (\Exception $e) {
                    Log::error('Brand logo fetch failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi tải logo từ URL'
                    ], 400);
                }
            }

            $brand = Brand::create($validated);

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

    // API: Cập nhật (CÓ ràng buộc - không cho sửa khi có sản phẩm)
    public function update(Request $request, $id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            // Kiểm tra xem thương hiệu có đang được sử dụng trong sản phẩm không
            $productCount = $this->getBrandUsageCount($id);
            
            // Nếu có sản phẩm sử dụng, không cho phép sửa
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể sửa thương hiệu này vì đang có ' . $productCount . ' sản phẩm đang sử dụng! Vui lòng chuyển hoặc xóa các sản phẩm này trước khi sửa thương hiệu.'
                ], 400);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name,' . $id,
                'slug' => 'nullable|string|unique:brands,slug,' . $id,
                'logo' => 'nullable|string|max:500',
                'description' => 'nullable|string',
                'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            // Tạo slug từ name nếu không có slug
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }
            
            // Tránh trùng slug
            $base = $validated['slug'];
            $i = 1;
            while (Brand::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $base . '-' . $i++;
            }

            // Nếu có file upload mới
            if ($request->hasFile('logo_file')) {
                // Xóa logo cũ nếu có
                $this->deleteImageIfExists($brand->logo);

                $this->ensureImageDir();
                $file = $request->file('logo_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = uniqid() . '.' . $ext;
                $file->move($this->imageDir(), $filename);
                $validated['logo'] = '/image/' . $filename;
                unset($validated['logo_file']);
            }
            // Nếu có URL logo mới (và không có file)
            elseif (!empty($validated['logo'])) {
                // Nếu URL khác với logo cũ thì tải mới và xóa cũ
                if ($validated['logo'] !== $brand->logo) {
                    try {
                        $res = Http::timeout(15)->get($validated['logo']);
                        if (!$res->ok()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Không thể tải logo từ URL'
                            ], 400);
                        }
                        $type = $res->header('Content-Type', '');
                        if (!str_starts_with($type, 'image/')) {
                            return response()->json([
                                'success' => false,
                                'message' => 'URL không phải ảnh'
                            ], 400);
                        }

                        $this->deleteImageIfExists($brand->logo);
                        $ext = explode('/', $type)[1] ?? 'png';
                        $validated['logo'] = $this->saveContentToImage($res->body(), $ext);
                    } catch (\Exception $e) {
                        Log::error('Brand logo update failed', ['error' => $e->getMessage()]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Lỗi tải logo từ URL'
                        ], 400);
                    }
                }
            } else {
                // Nếu không có logo mới, giữ nguyên logo cũ
                unset($validated['logo']);
            }

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

    // API: Xóa (CÓ ràng buộc)
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            // Kiểm tra xem thương hiệu có đang được sử dụng trong sản phẩm không
            $productCount = $this->getBrandUsageCount($id);
            
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thương hiệu này vì đang có ' . $productCount . ' sản phẩm đang sử dụng! Vui lòng chuyển hoặc xóa các sản phẩm này trước.'
                ], 400);
            }

            // Xóa logo nếu có
            $this->deleteImageIfExists($brand->logo);
            
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

    // API: Tìm kiếm
    public function search(Request $request)
    {
        try {
            $keyword = $request->get('q', '');
            $brands = Brand::where('name', 'like', "%{$keyword}%")
                ->orWhere('slug', 'like', "%{$keyword}%")
                ->limit(10)
                ->get();
            
            return response()->json($brands);
        } catch (\Exception $e) {
            Log::error('Lỗi search brand: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}