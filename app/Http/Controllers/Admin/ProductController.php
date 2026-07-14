<?php
// app/Http/Controllers/Admin/ProductController.php

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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    const MAX_MEDIA = 10;

    protected function mediaDir(): string
    {
        return base_path('media');
    }

    protected function ensureMediaDir(): void
    {
        $dir = $this->mediaDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    protected function saveUploadedFile($file): string
    {
        $this->ensureMediaDir();

        $ext = strtolower($file->getClientOriginalExtension());
        $filename = uniqid() . '.' . $ext;

        $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', 'ogg'];
        $subDir = in_array($ext, $videoExtensions) ? 'video' : 'image';

        $fullDir = $this->mediaDir() . '/' . $subDir;
        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true);
        }

        $file->move($fullDir, $filename);
        return '/media/' . $subDir . '/' . $filename;
    }

    protected function deleteMediaIfExists(?string $path): void
    {
        if (empty($path)) return;

        $parsed = parse_url($path);
        $cleanPath = ltrim($parsed['path'] ?? $path, '/');

        // Chỉ xóa nếu đường dẫn bắt đầu bằng 'media/'
        if (!str_starts_with($cleanPath, 'media/')) {
            return;
        }

        // Lấy phần đường dẫn sau 'media/'
        $relative = substr($cleanPath, 6); // bỏ 'media/'

        // Kiểm tra thêm nếu cần: chỉ cho phép image/ video/
        if (!preg_match('#^(image|video)/#', $relative)) {
            return;
        }

        $fullPath = base_path('media/' . $relative);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    protected function determineThumbnail(array $media): ?string
    {
        if (empty($media)) return null;

        $first = $media[0];
        if (str_starts_with($first, '/video/')) {
            return null;
        }
        return $first;
    }

    /**
     * Tính giá sale cho variant (tính toán tại chỗ)
     */
    private function calculateSalePriceForVariant($variant)
    {
        $originalPrice = $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $discountType = null;
        $campaignId = null;
        
        // 1. Kiểm tra campaign (chỉ cho retail - không pre-order)
        if (!$variant->product || !$variant->product->is_preorder) {
            $campaign = \App\Models\Campaign::where('status', 'active')
                ->where('type', '!=', 'voucher')
                ->where('type', '!=', 'preorder')
                ->whereHas('productVariants', function($query) use ($variant) {
                    $query->where('product_variant_id', $variant->id);
                })
                ->first();
            
            if ($campaign) {
                $config = $campaign->configs()->first();
                $discountPercent = $config ? $config->discount_percent : 0;
                if ($discountPercent > 0) {
                    $salePrice = $originalPrice * (1 - $discountPercent / 100);
                    $salePrice = round($salePrice);
                    $discountType = 'campaign';
                    $campaignId = $campaign->id;
                }
            }
        }
        
        // 2. Kiểm tra pre-order
        if ($variant->product && $variant->product->is_preorder) {
            $preorder = \App\Models\Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $variant->product_id)
                ->first();
            
            if ($preorder) {
                $currentBuyers = $preorder->current_buyers ?? 0;
                $tiers = $preorder->tiers ?? [];
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $discountPercent = $tier['discount'] ?? 0;
                        break;
                    }
                }
                
                if ($discountPercent > 0) {
                    $salePrice = $originalPrice * (1 - $discountPercent / 100);
                    $salePrice = round($salePrice);
                    $discountType = 'preorder';
                    $campaignId = $preorder->id;
                }
            }
        }
        
        return [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'discount_type' => $discountType,
            'campaign_id' => $campaignId,
            'is_on_sale' => $discountPercent > 0,
        ];
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
                
                // Tính sale price cho từng variant
                $salePrices = [];
                $isOnSale = false;
                $salePercent = 0;
                $saleType = null;
                $variantSaleInfo = [];
                
                foreach ($product->variants as $variant) {
                    $saleInfo = $this->calculateSalePriceForVariant($variant);
                    $salePrices[] = $saleInfo['sale_price'];
                    $variantSaleInfo[$variant->id] = $saleInfo;
                    
                    if ($saleInfo['is_on_sale']) {
                        $isOnSale = true;
                        $salePercent = max($salePercent, $saleInfo['discount_percent']);
                        $saleType = $saleInfo['discount_type'];
                    }
                }
                
                $minSalePrice = !empty($salePrices) ? min($salePrices) : null;
                $displayPrice = $minSalePrice && $minSalePrice < $minPrice ? $minSalePrice : $minPrice;

                $media = $product->image_url ?? [];
                if (!is_array($media)) {
                    $media = [];
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'category' => $product->category->name ?? 'Chưa phân loại',
                    'brand_id' => $product->brand_id,
                    'brand' => $product->brand->name ?? '',
                    'material' => $product->material,
                    'price' => (int) $minPrice,
                    'sale_price' => $minSalePrice ? (int) $minSalePrice : null,
                    'display_price' => (int) $displayPrice,
                    'stock' => $totalStock,
                    'type' => $product->is_preorder ? 'preorder' : 'normal',
                    'image_url' => $media,
                    'thumbnail' => $product->thumbnail ?? null,
                    'status' => $product->status,
                    'is_on_sale' => $isOnSale,
                    'sale_type' => $saleType,
                    'sale_percent' => $salePercent,
                    'variants' => $product->variants->map(function($v) use ($variantSaleInfo) {
                        $info = $variantSaleInfo[$v->id] ?? $this->calculateSalePriceForVariant($v);
                        return [
                            'id' => $v->id,
                            'color_id' => $v->color_id,
                            'color' => $v->color->name ?? '',
                            'code' => $v->color->code ?? '',
                            'size_name' => $v->size_name,
                            'price' => $v->price,
                            'sale_price' => $info['sale_price'],
                            'is_on_sale' => $info['is_on_sale'],
                            'stock' => $v->stock,
                        ];
                    }),
                ];
            });

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);
        $colors = Color::orderBy('name')->get(['id', 'name', 'code']);

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
            'name' => 'required|string|max:255|unique:products,name',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => 'required|in:normal,preorder',
            'image_url' => 'nullable|array|max:' . self::MAX_MEDIA,
            'image_url.*' => 'nullable|url|max:2048',
            'image_files' => 'nullable|array|max:' . self::MAX_MEDIA,
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,mov,avi,wmv,flv,mkv|max:20480',
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_name' => 'nullable|string|max:100',
            'variants.*.price' => 'required|integer|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        $media = $validated['image_url'] ?? [];

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                $media[] = $this->saveUploadedFile($file);
            }
        }

        $media = array_slice($media, 0, self::MAX_MEDIA);
        $thumbnail = $this->determineThumbnail($media);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'is_preorder' => $validated['type'] === 'preorder',
            'image_url' => $media,
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
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => 'required|in:normal,preorder',
            'image_url' => 'nullable|array|max:' . self::MAX_MEDIA,
            'image_url.*' => 'nullable|string|max:2048',
            'image_files' => 'nullable|array|max:' . self::MAX_MEDIA,
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,mov,avi,wmv,flv,mkv|max:20480',
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

        $oldMedia = $product->image_url ?? [];
        $media = $validated['image_url'] ?? [];

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                $media[] = $this->saveUploadedFile($file);
            }
        }

        $media = array_slice($media, 0, self::MAX_MEDIA);
        $thumbnail = $this->determineThumbnail($media);

        $product->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'is_preorder' => $validated['type'] === 'preorder',
            'image_url' => $media,
            'thumbnail' => $thumbnail,
            'material' => $validated['material'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($oldMedia as $oldPath) {
            if (!in_array($oldPath, $media)) {
                $this->deleteMediaIfExists($oldPath);
            }
        }

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
                foreach ($product->image_url as $path) {
                    $this->deleteMediaIfExists($path);
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