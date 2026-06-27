<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
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

    protected function saveUploadedImage($file): string
    {
        $this->ensureImageDir();
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($this->imageDir(), $filename);
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

    public function index($type = 'normal')
    {
        $validTypes = ['normal', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'normal';

        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->latest()
            ->get()
            ->map(function ($product) {
                $totalStock = $product->variants->sum('stock');
                $minPrice = $product->variants->min('price') ?? 0;
                $wholesalePrice = $minPrice;

                // Lấy mảng ảnh
                $images = $product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'category' => $product->category->name ?? 'Chưa phân loại',
                    'brand_id' => $product->brand_id,
                    'brand' => $product->brand->name ?? '',
                    'price' => (int) $minPrice,
                    'wholesalePrice' => (int) $wholesalePrice,
                    'stock' => $totalStock,
                    'type' => $product->is_preorder ? 'preorder' : 'normal',
                    'image_url' => $images,             
                    'thumbnail' => $images[0] ?? null,   
                    'status' => $product->status,
                    'variants' => $product->variants->map(fn($v) => [
                        'id' => $v->id,
                        'color_id' => $v->color_id,
                        'color' => $v->color->name ?? '',
                        'size_name' => $v->size_name,
                        'price' => $v->price,
                        'stock' => $v->stock,
                    ]),
                ];
            });

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $colors = Color::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Products', [
            'type' => $type,
            'initialProducts' => $allProducts,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('image_url') && is_string($request->input('image_url'))) {
            $request->merge([
                'image_url' => json_decode($request->input('image_url'), true) ?? []
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => 'required|in:normal,preorder',
            'image_url' => 'nullable|array|max:10',
            'image_url.*' => 'nullable|url|max:2048',
            'image_files' => 'nullable|array|max:10',
            'image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_name' => 'nullable|string|max:100',
            'variants.*.price' => 'required|integer|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        $images = $validated['image_url'] ?? [];

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                $images[] = $this->saveUploadedImage($file);
            }
        }

        $images = array_slice($images, 0, 10);
        $thumbnail = $images[0] ?? null;

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'is_preorder' => $validated['type'] === 'preorder',
            'image_url' => $images,
            'thumbnail' => $thumbnail,
            'material' => $validated['material'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 1,
        ]);

        foreach ($validated['variants'] as $variantData) {
            ProductVariant::create([
                'product_id' => $product->id,
                'color_id' => $variantData['color_id'],
                'size_name' => $variantData['size_name'] ?? null,
                'price' => $variantData['price'],
                'stock' => $variantData['stock'],
                'rating' => 0,
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($request->has('image_url') && is_string($request->input('image_url'))) {
            $request->merge([
                'image_url' => json_decode($request->input('image_url'), true) ?? []
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => 'required|in:normal,preorder',
            'image_url' => 'nullable|array|max:10',
            'image_url.*' => 'nullable|url|max:2048',
            'image_files' => 'nullable|array|max:10',
            'image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*.id' => [
                'nullable',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_name' => 'nullable|string|max:100',
            'variants.*.price' => 'required|integer|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        $images = $validated['image_url'] ?? [];

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                $images[] = $this->saveUploadedImage($file);
            }
        }

        $images = array_slice($images, 0, 10);
        $thumbnail = $images[0] ?? null;
  
        $product->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'is_preorder' => $validated['type'] === 'preorder',
            'image_url' => $images,
            'thumbnail' => $thumbnail,
            'material' => $validated['material'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $existingVariantIds = $product->variants->pluck('id')->toArray();
        $submittedVariantIds = [];

        foreach ($validated['variants'] as $variantData) {
            if (isset($variantData['id'])) {
                $variant = ProductVariant::find($variantData['id']);
                if ($variant) {
                    $variant->update([
                        'color_id' => $variantData['color_id'],
                        'size_name' => $variantData['size_name'] ?? null,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                    ]);
                    $submittedVariantIds[] = $variant->id;
                }
            } else {
                $newVariant = ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $variantData['color_id'],
                    'size_name' => $variantData['size_name'] ?? null,
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'],
                    'rating' => 0,
                ]);
                $submittedVariantIds[] = $newVariant->id;
            }
        }

        $toDelete = array_diff($existingVariantIds, $submittedVariantIds);
        if (!empty($toDelete)) {
            ProductVariant::destroy($toDelete);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công');
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        try {
            if ($product->image_url) {
                foreach ($product->image_url as $imagePath) {
                    $this->deleteImageIfExists($imagePath);
                }
            }

            $product->variants()->delete();
            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Xóa sản phẩm thành công');
        } catch (\Exception $e) {
            Log::error('Delete product failed: ' . $e->getMessage(), ['product_id' => $product->id]);
            return redirect()->back()->withErrors(['error' => 'Không thể xóa sản phẩm: ' . $e->getMessage()]);
        }
    }
}