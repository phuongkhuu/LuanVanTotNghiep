<?php
// app/Http/Controllers/Admin/BrandController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
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
     * Handle file upload: validate, move, return public URL
     */
    protected function handleFileUpload($file): string
    {
        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('File upload không hợp lệ.');
        }

        if ($file->getSize() === 0) {
            throw new \Exception('File rỗng, vui lòng chọn file hợp lệ.');
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('File không đúng định dạng ảnh. Chỉ chấp nhận: jpeg, png, jpg, gif, svg.');
        }

        $this->ensureImageDir();

        // Lấy extension an toàn
        $ext = $file->getClientOriginalExtension();
        if (!$ext) {
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/svg+xml' => 'svg'
            ];
            $ext = $mimeMap[$file->getMimeType()] ?? 'png';
        }

        $filename = uniqid() . '.' . $ext;
        $file->move($this->imageDir(), $filename);

        return '/image/' . $filename;
    }

    /**
     * Handle logo from URL: download, validate, save, return public URL
     */
    protected function handleLogoFromUrl(string $url): string
    {
        $res = Http::timeout(15)->get($url);
        if (!$res->ok()) {
            throw new \Exception('Không thể tải logo từ URL');
        }

        $type = $res->header('Content-Type', '');
        if (!str_starts_with($type, 'image/')) {
            throw new \Exception('URL không phải ảnh');
        }

        // Optional: check size? (max 2MB)
        $size = strlen($res->body());
        if ($size > 2 * 1024 * 1024) {
            throw new \Exception('Ảnh từ URL vượt quá 2MB');
        }

        $ext = explode('/', $type)[1] ?? 'png';
        return $this->saveContentToImage($res->body(), $ext);
    }

    /**
     * Kiểm tra xem thương hiệu có đang được sử dụng trong sản phẩm không
     */
    protected function isBrandInUse($brandId): bool
    {
        return $this->getBrandUsageCount($brandId) > 0;
    }

    /**
     * Lấy số lượng sản phẩm đang sử dụng thương hiệu
     */
    protected function getBrandUsageCount($brandId): int
    {
        return Product::where('brand_id', $brandId)->count();
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
                try {
                    $file = $request->file('logo_file');
                    $validated['logo'] = $this->handleFileUpload($file);
                    unset($validated['logo_file']);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                }
            }
            // Xử lý logo từ URL (nếu không có file)
            elseif (!empty($validated['logo'])) {
                try {
                    $validated['logo'] = $this->handleLogoFromUrl($validated['logo']);
                } catch (\Exception $e) {
                    Log::error('Brand logo fetch failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
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
            
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể sửa thương hiệu này vì đang có ' . $productCount . ' sản phẩm sử dụng! Vui lòng chuyển hoặc xóa các sản phẩm này trước khi sửa.'
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

            // Xử lý logo mới (nếu có file upload hoặc URL mới)
            $hasNewLogo = false;

            // Nếu có file upload mới
            if ($request->hasFile('logo_file')) {
                try {
                    $file = $request->file('logo_file');
                    $newLogo = $this->handleFileUpload($file);
                    $hasNewLogo = true;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                }
            }
            // Nếu có URL logo mới (và không có file)
            elseif (!empty($validated['logo']) && $validated['logo'] !== $brand->logo) {
                try {
                    $newLogo = $this->handleLogoFromUrl($validated['logo']);
                    $hasNewLogo = true;
                } catch (\Exception $e) {
                    Log::error('Brand logo update failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                }
            }

            // Nếu có logo mới (từ file hoặc URL)
            if ($hasNewLogo) {
                // Xóa logo cũ
                $this->deleteImageIfExists($brand->logo);
                $validated['logo'] = $newLogo;
            } else {
                // Không có logo mới: giữ nguyên logo cũ
                unset($validated['logo']);
            }

            // Xóa trường logo_file vì không có trong DB
            unset($validated['logo_file']);

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
            
            $productCount = $this->getBrandUsageCount($id);
            
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thương hiệu này vì đang có ' . $productCount . ' sản phẩm sử dụng! Vui lòng chuyển hoặc xóa các sản phẩm này trước.'
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