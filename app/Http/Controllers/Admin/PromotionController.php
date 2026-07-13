<?php
// app/Http/Controllers/Admin/PromotionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    const DEFAULT_BANNER = '/images/default-campaign-banner.jpg';
    
    // ==================== INDEX ====================

    public function index()
    {
        try {
            $allCampaigns = Campaign::with([
                'configs', 
                'productVariants', 
                'productVariants.product', 
                'productVariants.color', 
                'product',
                'banners'
            ])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    
                    $activeBanner = $campaign->banners
                        ->where('status', Banner::STATUS_ACTIVE)
                        ->first();
                    
                    $banner = $activeBanner ?? $campaign->banners->first();
                    
                    $bannerImage = null;
                    if ($banner && $banner->image) {
                        $bannerImage = $banner->image;
                    } elseif ($campaign->banner_url) {
                        $bannerImage = $campaign->banner_url;
                    }
                    
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'campaign_type' => $campaign->campaign_type ?? 'campaign',
                        'code' => $campaign->code,
                        'target_type' => $campaign->target_type,
                        'discount_type' => $campaign->discount_type,
                        'discount_value' => $campaign->discount_value,
                        'min_order' => $campaign->min_order,
                        'limit' => $campaign->limit,
                        'used' => $campaign->used ?? 0,
                        'expiry' => $campaign->expiry,
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'product_id' => $campaign->product_id,
                        'tiers' => $campaign->tiers,
                        'current_buyers' => $campaign->current_buyers ?? 0,
                        'active' => $campaign->status === 'active',
                        'start_date' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'end_date' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'banner' => $banner ? [
                            'id' => $banner->id,
                            'image' => $banner->image,
                            'title' => $banner->title,
                            'link' => $banner->link,
                            'status' => $banner->status,
                        ] : null,
                        'banner_image' => $bannerImage,
                        'banner_url' => $campaign->banner_url,
                    ];
                });

            $campaigns = $allCampaigns->filter(function($item) {
                return $item['type'] === 'seasonal' || $item['type'] === 'campaign' || $item['type'] === 'flash_sale' || $item['type'] === 'anniversary' || $item['type'] === 'holiday' || $item['type'] === 'product_launch' || $item['type'] === 'other';
            })->values();

            $vouchers = $allCampaigns->filter(function($item) {
                return $item['type'] === 'voucher';
            })->values();

            $preorders = $allCampaigns->filter(function($item) {
                return $item['type'] === 'preorder';
            })->values();

            $banners = Banner::with('campaign')
                ->orderBy('order', 'asc')
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title ?? 'Banner #' . $banner->id,
                        'image' => $banner->image,
                        'link' => $banner->link,
                        'description' => $banner->description,
                        'status' => $banner->status,
                        'order' => $banner->order,
                        'campaign_id' => $banner->campaign_id,
                        'campaign' => $banner->campaign ? [
                            'id' => $banner->campaign->id,
                            'name' => $banner->campaign->name,
                        ] : null,
                    ];
                });

            $brands = Brand::orderBy('name')->get()->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ];
            });

            $categories = Category::orderBy('name')->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            });

            $productVariants = ProductVariant::with(['product', 'product.brand', 'product.category', 'color'])
                ->get()
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->product ? $variant->product->name : 'Sản phẩm',
                        'product' => $variant->product ? [
                            'id' => $variant->product->id,
                            'name' => $variant->product->name,
                            'is_preorder' => $variant->product->is_preorder ?? false,
                            'brand_id' => $variant->product->brand_id,
                            'brand_name' => $variant->product->brand ? $variant->product->brand->name : null,
                            'category_id' => $variant->product->category_id,
                            'category_name' => $variant->product->category ? $variant->product->category->name : null,
                        ] : null,
                        'color' => $variant->color ? [
                            'id' => $variant->color->id,
                            'name' => $variant->color->name,
                        ] : null,
                        'price' => $variant->price ?? 0,
                        'size_name' => $variant->size_name ?? '',
                        'stock' => $variant->stock ?? 0,
                    ];
                });

            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color', 'brand', 'category'])
                ->latest()
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'brand_id' => $product->brand_id,
                        'brand_name' => $product->brand ? $product->brand->name : null,
                        'category_id' => $product->category_id,
                        'category_name' => $product->category ? $product->category->name : null,
                        'variants' => $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'color' => $variant->color ? [
                                    'id' => $variant->color->id,
                                    'name' => $variant->color->name,
                                ] : null,
                                'price' => $variant->price ?? 0,
                                'size_name' => $variant->size_name ?? '',
                            ];
                        }),
                    ];
                });

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'vouchers' => $vouchers,
                'preorders' => $preorders,
                'banners' => $banners,
                'products' => [],
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
                'brands' => $brands,
                'categories' => $categories,
                'defaultBanner' => self::DEFAULT_BANNER,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'vouchers' => [],
                'preorders' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'brands' => [],
                'categories' => [],
                'defaultBanner' => '/images/default-campaign-banner.jpg',
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CAMPAIGN METHODS ====================

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('=== TẠO CAMPAIGN MỚI ===');
            Log::info('Data: ', $request->all());

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            $status = $validated['status'] ?? 'scheduled';
            if ($validated['startDate'] && $validated['startDate'] <= now()->format('Y-m-d')) {
                if (!$validated['endDate'] || $validated['endDate'] >= now()->format('Y-m-d')) {
                    $status = 'active';
                } elseif ($validated['endDate'] < now()->format('Y-m-d')) {
                    $status = 'ended';
                }
            }

            Log::info('Status: ' . $status);

            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'campaign_type' => 'campaign',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $status,
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            Log::info('Campaign created: ' . $campaign->id);

            // Tạo config
            CampaignConfig::create([
                'campaign_id' => $campaign->id,
                'quantity' => 0,
                'discount_percent' => $validated['discountPercent'] ?? 0,
            ]);

            // Gắn sản phẩm
            if (!empty($validated['products']) && is_array($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
                Log::info('Đã gắn ' . count($validated['products']) . ' sản phẩm');
            }

            DB::commit();
            
            Log::info('=== KẾT THÚC TẠO CAMPAIGN ===');

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi tạo chiến dịch: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaign(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            Log::info('=== CẬP NHẬT CAMPAIGN ID: ' . $id . ' ===');
            Log::info('Data: ', $request->all());

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            $status = $validated['status'] ?? $campaign->status;
            if ($validated['startDate'] ?? false) {
                if ($validated['startDate'] <= now()->format('Y-m-d')) {
                    if (!$validated['endDate'] || $validated['endDate'] >= now()->format('Y-m-d')) {
                        $status = 'active';
                    } elseif ($validated['endDate'] < now()->format('Y-m-d')) {
                        $status = 'ended';
                    }
                }
            }

            $oldConfig = $campaign->configs()->first();
            $oldDiscountPercent = $oldConfig ? $oldConfig->discount_percent : 0;
            $newDiscountPercent = $validated['discountPercent'] ?? $oldDiscountPercent;

            Log::info('Old discount: ' . $oldDiscountPercent . '%, New discount: ' . $newDiscountPercent . '%');
            Log::info('Old status: ' . $campaign->status . ', New status: ' . $status);

            // Cập nhật campaign
            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $status,
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            // Cập nhật config
            $config = $campaign->configs()->first();
            if ($config) {
                $config->update([
                    'quantity' => 0,
                    'discount_percent' => $newDiscountPercent,
                ]);
            } else {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => 0,
                    'discount_percent' => $newDiscountPercent,
                ]);
            }

            // Cập nhật sản phẩm
            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();
            
            Log::info('=== KẾT THÚC CẬP NHẬT CAMPAIGN ===');

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi cập nhật chiến dịch: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteCampaign($id)
    {
        try {
            DB::beginTransaction();
            
            Log::info('=== XÓA CAMPAIGN ID: ' . $id . ' ===');
            
            $campaign = Campaign::findOrFail($id);
            
            Banner::where('campaign_id', $campaign->id)->update(['campaign_id' => null]);
            $campaign->configs()->delete();
            $campaign->productVariants()->detach();
            $campaign->delete();

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa chiến dịch: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCampaignStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            Log::info('=== CẬP NHẬT STATUS CAMPAIGN ID: ' . $id . ' ===');
            Log::info('New status: ' . $request->status);
            
            $campaign = Campaign::findOrFail($id);
            $newStatus = $request->status;
            
            $campaign->update(['status' => $newStatus]);
            
            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi cập nhật status: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== VOUCHER METHODS ====================

    public function storeVoucher(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:campaigns,code',
                'name' => 'nullable|string|max:255',
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Voucher ' . $validated['code'],
                'type' => 'voucher',
                'code' => strtoupper($validated['code']),
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'used' => 0,
                'expiry' => $validated['expiry'] ?? null,
                'status' => ($validated['active'] ?? true) ? 'active' : 'scheduled',
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
            ]);

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateVoucher(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:campaigns,code,' . $id,
                'name' => 'nullable|string|max:255',
                'target_type' => 'required|in:retail,wholesale,preorder,all',
                'discount_type' => 'required|in:fixed,percent,freeship',
                'discount_value' => 'required|numeric|min:0',
                'min_order' => 'nullable|numeric|min:0',
                'limit' => 'nullable|integer|min:0',
                'expiry' => 'nullable|date',
                'active' => 'boolean',
                'description' => 'nullable|string',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'code' => strtoupper($validated['code']),
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'expiry' => $validated['expiry'] ?? null,
                'status' => ($validated['active'] ?? true) ? 'active' : 'scheduled',
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
            ]);

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteVoucher($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $campaign->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleVoucher($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $newStatus = $campaign->status === 'active' ? 'scheduled' : 'active';
            $campaign->update(['status' => $newStatus]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ==================== PRE-ORDER METHODS ====================

    public function storePreorder(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
            ]);

            // Kiểm tra sản phẩm phải là pre-order
            $product = Product::find($validated['product_id']);
            if (!$product || !$product->is_preorder) {
                throw new \Exception('Sản phẩm phải là pre-order');
            }

            $campaign = Campaign::create([
                'name' => $validated['name'],
                'type' => 'preorder',
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_time' => $validated['start_date'] ?? null,
                'end_time' => $validated['end_date'] ?? null,
                'status' => ($validated['active'] ?? true) ? 'active' : 'scheduled',
                'min_order' => $validated['min_order'] ?? 0,
                'current_buyers' => 0,
                'description' => "Giảm giá theo số lượt đặt trước",
            ]);

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePreorder(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
            ]);

            $product = Product::find($validated['product_id']);
            if (!$product || !$product->is_preorder) {
                throw new \Exception('Sản phẩm phải là pre-order');
            }

            $campaign->update([
                'name' => $validated['name'],
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_time' => $validated['start_date'] ?? null,
                'end_time' => $validated['end_date'] ?? null,
                'status' => ($validated['active'] ?? true) ? 'active' : 'scheduled',
                'min_order' => $validated['min_order'] ?? 0,
            ]);

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePreorder($id)
    {
        try {
            DB::beginTransaction();
            
            $campaign = Campaign::findOrFail($id);
            $campaign->delete();

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePreorder($id)
    {
        try {
            DB::beginTransaction();
            
            $campaign = Campaign::findOrFail($id);
            $newStatus = $campaign->status === 'active' ? 'scheduled' : 'active';
            $campaign->update(['status' => $newStatus]);
            
            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function incrementPreorderBuyers($preorderId)
    {
        try {
            DB::beginTransaction();
            
            $preorder = Campaign::findOrFail($preorderId);
            $preorder->increment('current_buyers');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số người đặt thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi tăng current_buyers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== CHECK PROMOTION ====================

    public function checkPromotion(Request $request)
    {
        try {
            $code = $request->input('code');
            $orderType = $request->input('order_type', 'retail');
            $subtotal = $request->input('subtotal', 0);
            
            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập mã khuyến mãi'
                ], 400);
            }

            $voucher = Campaign::where('code', strtoupper($code))
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if ($voucher) {
                if ($voucher->expiry && $voucher->expiry < now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã giảm giá đã hết hạn'
                    ]);
                }

                if ($voucher->used >= $voucher->limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã giảm giá đã được sử dụng hết'
                    ]);
                }

                if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ để áp dụng mã'
                    ]);
                }

                $targetType = $voucher->target_type;
                if ($targetType !== 'all' && $targetType !== $orderType) {
                    $typeLabels = [
                        'retail' => 'bán lẻ',
                        'preorder' => 'pre-order',
                        'wholesale' => 'bán sỉ'
                    ];
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã này chỉ áp dụng cho đơn hàng ' . ($typeLabels[$targetType] ?? $targetType)
                    ]);
                }

                $discountAmount = 0;
                $discountType = $voucher->discount_type;
                $discountValue = $voucher->discount_value;

                if ($discountType === 'percent') {
                    $discountAmount = ($subtotal * $discountValue) / 100;
                } elseif ($discountType === 'fixed') {
                    $discountAmount = min($discountValue, $subtotal);
                } elseif ($discountType === 'freeship') {
                    $discountAmount = 0;
                }

                $discountAmount = round($discountAmount);

                return response()->json([
                    'success' => true,
                    'code' => $voucher->code,
                    'type' => 'voucher',
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'is_freeship' => $discountType === 'freeship',
                    'message' => 'Áp dụng mã giảm giá thành công!',
                    'campaign_id' => $voucher->id,
                    'voucher_id' => $voucher->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Mã khuyến mãi không hợp lệ hoặc không áp dụng được'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi kiểm tra khuyến mãi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPreorderInfo(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm'
                ]);
            }

            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $productId)
                ->first();

            if (!$preorder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này không có chương trình pre-order'
                ]);
            }

            if ($preorder->start_time && $preorder->start_time > now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chương trình pre-order chưa bắt đầu',
                    'start_time' => $preorder->start_time->format('Y-m-d H:i:s')
                ]);
            }

            if ($preorder->end_time && $preorder->end_time < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chương trình pre-order đã kết thúc',
                    'end_time' => $preorder->end_time->format('Y-m-d H:i:s')
                ]);
            }

            $tiers = $preorder->tiers ?? [];
            $currentBuyers = $preorder->current_buyers ?? 0;
            $discountPercent = 0;

            usort($tiers, function($a, $b) {
                return ($a['from'] ?? 0) - ($b['from'] ?? 0);
            });

            foreach ($tiers as $tier) {
                $from = $tier['from'] ?? 0;
                $to = $tier['to'] ?? PHP_INT_MAX;
                if ($currentBuyers >= $from && $currentBuyers <= $to) {
                    $discountPercent = $tier['discount'] ?? 0;
                    break;
                }
            }

            $nextTier = null;
            $nextCount = 0;
            foreach ($tiers as $tier) {
                $from = $tier['from'] ?? 0;
                if ($currentBuyers < $from) {
                    $nextTier = $tier;
                    $nextCount = $from - $currentBuyers;
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $preorder->id,
                    'name' => $preorder->name,
                    'tiers' => $tiers,
                    'current_buyers' => $currentBuyers,
                    'current_discount' => $discountPercent,
                    'next_tier' => $nextTier,
                    'next_count' => $nextCount,
                    'min_order' => $preorder->min_order ?? 0,
                    'start_time' => $preorder->start_time ? $preorder->start_time->format('Y-m-d H:i:s') : null,
                    'end_time' => $preorder->end_time ? $preorder->end_time->format('Y-m-d H:i:s') : null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi lấy thông tin pre-order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCampaignsList()
    {
        try {
            $campaigns = Campaign::select('id', 'name', 'status', 'start_time', 'end_time')
                ->where('type', 'seasonal')
                ->orWhere('type', 'campaign')
                ->orWhere('type', 'flash_sale')
                ->orWhere('type', 'anniversary')
                ->orWhere('type', 'holiday')
                ->orWhere('type', 'product_launch')
                ->orWhere('type', 'other')
                ->orderBy('start_time', 'desc')
                ->get()
                ->map(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                        'status' => $campaign->status,
                        'start_time' => $campaign->start_time,
                        'end_time' => $campaign->end_time,
                    ];
                });
            
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}