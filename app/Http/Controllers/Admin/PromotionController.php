<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy campaigns
            $campaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'startDate' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'endDate' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                        'status' => $campaign->status ?? 'scheduled',
                        'priority' => $campaign->priority ?? 0,
                        'featured' => $campaign->featured ?? false,
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                    ];
                });

            // Lấy tất cả promotions
            $promotions = Promotion::with(['campaign', 'product'])
                ->latest()
                ->get()
                ->map(function ($promotion) {
                    return [
                        'id' => $promotion->id,
                        'code' => $promotion->code,
                        'type' => $promotion->type,
                        'target_type' => $promotion->target_type,
                        'discount_type' => $promotion->discount_type,
                        'discount_value' => $promotion->discount_value,
                        'min_order' => $promotion->min_order,
                        'limit' => $promotion->limit,
                        'used' => $promotion->used ?? 0,
                        'expiry' => $promotion->expiry,
                        'active' => $promotion->active,
                        'description' => $promotion->description,
                        'campaign_id' => $promotion->campaign_id,
                        'campaign' => $promotion->campaign ? [
                            'id' => $promotion->campaign->id,
                            'name' => $promotion->campaign->name,
                        ] : null,
                        'product_id' => $promotion->product_id,
                        'tiers' => $promotion->tiers,
                        'current_buyers' => $promotion->current_buyers ?? 0,
                        'start_date' => $promotion->start_date,
                        'end_date' => $promotion->end_date,
                    ];
                });

            // Lấy tất cả banners
            $banners = Banner::with('campaign')->orderBy('order', 'asc')->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'Banner #' . $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'description' => $banner->description,
                    'status' => $banner->status ? 1 : 0,
                    'order' => $banner->order,
                    'campaign_id' => $banner->campaign_id,
                    'campaign' => $banner->campaign ? [
                        'id' => $banner->campaign->id,
                        'name' => $banner->campaign->name,
                    ] : null,
                ];
            });

            // Lấy tất cả products
            $products = Product::with(['variants.color', 'category', 'brand'])
                ->latest()
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'is_preorder' => $product->is_preorder,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                        ] : null,
                        'brand' => $product->brand ? [
                            'id' => $product->brand->id,
                            'name' => $product->brand->name,
                        ] : null,
                        'variants' => $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'color' => $variant->color ? [
                                    'id' => $variant->color->id,
                                    'name' => $variant->color->name,
                                ] : null,
                                'price' => $variant->price ?? 0,
                                'stock' => $variant->stock ?? 0,
                                'size_name' => $variant->size_name ?? '',
                            ];
                        }),
                    ];
                });

            // Lấy product variants
            $productVariants = ProductVariant::with(['product', 'color'])
                ->get()
                ->map(function ($variant) {
                    $productName = $variant->product ? $variant->product->name : 'Sản phẩm không xác định';
                    $isPreorder = $variant->product ? $variant->product->is_preorder : false;
                    
                    return [
                        'id' => $variant->id,
                        'name' => $variant->product ? $variant->product->name : 'Sản phẩm',
                        'product' => $variant->product ? [
                            'id' => $variant->product->id,
                            'name' => $variant->product->name,
                            'is_preorder' => $isPreorder,
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

            // Lấy chỉ sản phẩm pre-order
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color'])
                ->latest()
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image_url' => $product->image_url,
                        'description' => $product->description,
                        'variants' => $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'color' => $variant->color ? [
                                    'id' => $variant->color->id,
                                    'name' => $variant->color->name,
                                ] : null,
                                'price' => $variant->price ?? 0,
                                'stock' => $variant->stock ?? 0,
                                'size_name' => $variant->size_name ?? '',
                            ];
                        }),
                    ];
                });

            return Inertia::render('Admin/Promotions', [
                'campaigns' => $campaigns,
                'promotions' => $promotions,
                'banners' => $banners,
                'products' => $products,
                'productVariants' => $productVariants,
                'preorderProducts' => $preorderProducts,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang promotions: ' . $e->getMessage());
            return Inertia::render('Admin/Promotions', [
                'campaigns' => [],
                'promotions' => [],
                'banners' => [],
                'products' => [],
                'productVariants' => [],
                'preorderProducts' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate', // FIX: Cho phép ngày trùng
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100', // FIX: Cho phép 0
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            $campaign = Campaign::create([
                'name' => $validated['name'] ?? 'Chiến dịch ' . now()->format('d/m/Y'),
                'type' => $validated['type'] ?? 'seasonal',
                'description' => $validated['description'] ?? '',
                'start_time' => $validated['startDate'] ?? null,
                'end_time' => $validated['endDate'] ?? null,
                'status' => $validated['status'] ?? 'scheduled',
                'priority' => $validated['priority'] ?? 0,
                'featured' => $validated['featured'] ?? false,
            ]);

            // Tạo config
            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

            // Gán sản phẩm
            if (!empty($validated['products']) && is_array($validated['products'])) {
                $campaign->productVariants()->attach($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo chiến dịch: ' . $e->getMessage());
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

            $campaign = Campaign::findOrFail($id);

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate', // FIX: Cho phép ngày trùng
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100', // FIX: Cho phép 0
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            $campaign->update([
                'name' => $validated['name'] ?? $campaign->name,
                'type' => $validated['type'] ?? $campaign->type,
                'description' => $validated['description'] ?? $campaign->description,
                'start_time' => $validated['startDate'] ?? $campaign->start_time,
                'end_time' => $validated['endDate'] ?? $campaign->end_time,
                'status' => $validated['status'] ?? $campaign->status,
                'priority' => $validated['priority'] ?? $campaign->priority,
                'featured' => $validated['featured'] ?? $campaign->featured,
            ]);

            // Cập nhật config
            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                $config = $campaign->configs()->first();
                if ($config) {
                    $config->update([
                        'quantity' => $validated['quantity'] ?? $config->quantity,
                        'discount_percent' => $validated['discountPercent'] ?? $config->discount_percent,
                    ]);
                } else {
                    CampaignConfig::create([
                        'campaign_id' => $campaign->id,
                        'quantity' => $validated['quantity'] ?? 0,
                        'discount_percent' => $validated['discountPercent'] ?? 0,
                    ]);
                }
            }

            // Cập nhật sản phẩm
            if (isset($validated['products'])) {
                $campaign->productVariants()->sync($validated['products']);
            }

            DB::commit();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật chiến dịch: ' . $e->getMessage());
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
            
            $campaign = Campaign::findOrFail($id);
            
            // Giải phóng banner khỏi chiến dịch
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
            $campaign = Campaign::findOrFail($id);
            $request->validate(['status' => 'required|in:scheduled,active,ended']);
            $campaign->update(['status' => $request->status]);
            
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

    // ==================== VOUCHER METHODS ====================

    public function storePromotion(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code',
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

            $promotion = Promotion::create([
                'code' => strtoupper($validated['code']),
                'type' => 'voucher',
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'used' => 0,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePromotion(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:promotions,code,' . $id,
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

            $promotion->update([
                'code' => strtoupper($validated['code']),
                'target_type' => $validated['target_type'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_order' => $validated['min_order'] ?? 0,
                'limit' => $validated['limit'] ?? 100,
                'expiry' => $validated['expiry'] ?? null,
                'active' => $validated['active'] ?? true,
                'description' => $validated['description'] ?? "Giảm " . ($validated['discount_type'] === 'percent' ? $validated['discount_value'] . '%' : number_format($validated['discount_value']) . '₫'),
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật mã giảm giá: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

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

    public function togglePromotion($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update(['active' => !$promotion->active]);

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
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date', // FIX: Cho phép ngày trùng
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion = Promotion::create([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'type' => 'preorder_tier',
                'target_type' => 'preorder',
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
                'current_buyers' => 0,
                'description' => "Giảm giá theo số lượt đặt trước",
                'discount_value' => 0,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Thêm chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
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
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
                'tiers' => 'required|array|min:1',
                'tiers.*.from' => 'required|integer|min:1',
                'tiers.*.to' => 'required|integer|gt:tiers.*.from',
                'tiers.*.discount' => 'required|integer|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date', // FIX: Cho phép ngày trùng
                'active' => 'boolean',
                'min_order' => 'nullable|numeric|min:0',
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

            $promotion->update([
                'code' => strtoupper(str_replace(' ', '_', $validated['name'])),
                'product_id' => $validated['product_id'],
                'tiers' => $validated['tiers'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'active' => $validated['active'] ?? true,
                'min_order' => $validated['min_order'] ?? 0,
                'campaign_id' => $validated['campaign_id'] ?? null,
            ]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
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
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Xóa chương trình pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa pre-order: ' . $e->getMessage());
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function getCampaignsList()
    {
        try {
            $campaigns = Campaign::select('id', 'name', 'status', 'start_time', 'end_time')
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