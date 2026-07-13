<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
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
            // Lấy tất cả campaigns - phân loại theo type
            $allCampaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color', 'product'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal', // seasonal, voucher, preorder
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
                        'quantity' => $config ? (int) $config->quantity : 0,
                        'discountPercent' => $config ? (float) $config->discount_percent : 0,
                        'discount' => $config ? (float) $config->discount_percent . '%' : '0%',
                        'products' => $campaign->productVariants->pluck('id')->toArray(),
                        'product_id' => $campaign->product_id,
                        'tiers' => $campaign->tiers,
                        'current_buyers' => $campaign->current_buyers ?? 0,
                        'active' => $campaign->status === 'active',
                        'start_date' => $campaign->start_time ? $campaign->start_time->format('Y-m-d') : null,
                        'end_date' => $campaign->end_time ? $campaign->end_time->format('Y-m-d') : null,
                    ];
                });

            // Phân loại
            $campaigns = $allCampaigns->filter(function($item) {
                return $item['type'] === 'seasonal' || $item['type'] === 'campaign' || $item['type'] === 'flash_sale' || $item['type'] === 'anniversary' || $item['type'] === 'holiday' || $item['type'] === 'product_launch' || $item['type'] === 'other';
            })->values();

            $vouchers = $allCampaigns->filter(function($item) {
                return $item['type'] === 'voucher';
            })->values();

            $preorders = $allCampaigns->filter(function($item) {
                return $item['type'] === 'preorder';
            })->values();

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

            // Lấy product variants
            $productVariants = ProductVariant::with(['product', 'color'])
                ->get()
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->product ? $variant->product->name : 'Sản phẩm',
                        'product' => $variant->product ? [
                            'id' => $variant->product->id,
                            'name' => $variant->product->name,
                            'is_preorder' => $variant->product->is_preorder ?? false,
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

            // Lấy sản phẩm pre-order
            $preorderProducts = Product::where('is_preorder', true)
                ->with(['variants.color'])
                ->latest()
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
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
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CAMPAIGN METHODS ====================

    public function storeCampaign(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            // Xác định status nếu không có
            $status = $validated['status'] ?? 'scheduled';
            if ($validated['startDate'] && $validated['startDate'] <= now()->format('Y-m-d')) {
                if (!$validated['endDate'] || $validated['endDate'] >= now()->format('Y-m-d')) {
                    $status = 'active';
                } elseif ($validated['endDate'] < now()->format('Y-m-d')) {
                    $status = 'ended';
                }
            }

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

            if (isset($validated['quantity']) || isset($validated['discountPercent'])) {
                CampaignConfig::create([
                    'campaign_id' => $campaign->id,
                    'quantity' => $validated['quantity'] ?? 0,
                    'discount_percent' => $validated['discountPercent'] ?? 0,
                ]);
            }

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
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'status' => 'nullable|in:scheduled,active,ended',
                'priority' => 'nullable|integer|min:0',
                'featured' => 'boolean',
                'quantity' => 'nullable|integer|min:0',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'products' => 'nullable|array',
                'products.*' => 'exists:product_variants,id',
            ]);

            // Xác định status nếu không có
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

     public function checkPromotion(Request $request)
    {
        try {
            $code = $request->input('code');
            $orderType = $request->input('order_type', 'retail'); // retail, preorder
            $subtotal = $request->input('subtotal', 0);
            $productIds = $request->input('product_ids', []); // IDs của sản phẩm trong đơn
            
            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập mã khuyến mãi'
                ], 400);
            }

            // 1. TÌM MÃ GIẢM GIÁ (VOUCHER)
            $voucher = Campaign::where('code', strtoupper($code))
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if ($voucher) {
                // Kiểm tra voucher còn hiệu lực
                if ($voucher->expiry && $voucher->expiry < now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã giảm giá đã hết hạn'
                    ]);
                }

                // Kiểm tra số lượng đã dùng
                if ($voucher->used >= $voucher->limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã giảm giá đã được sử dụng hết'
                    ]);
                }

                // Kiểm tra điều kiện đơn hàng tối thiểu
                if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ để áp dụng mã'
                    ]);
                }

                // Kiểm tra target_type
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

                // Tính toán giảm giá
                $discountAmount = 0;
                $discountType = $voucher->discount_type;
                $discountValue = $voucher->discount_value;

                if ($discountType === 'percent') {
                    $discountAmount = ($subtotal * $discountValue) / 100;
                } elseif ($discountType === 'fixed') {
                    $discountAmount = min($discountValue, $subtotal);
                } elseif ($discountType === 'freeship') {
                    // Miễn phí ship - sẽ xử lý ở phần tính phí ship
                    $discountAmount = 0;
                    // Lưu flag freeship để xử lý sau
                    $voucher->is_freeship = true;
                }

                // Làm tròn
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

            // 2. TÌM CHƯƠNG TRÌNH PRE-ORDER
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $request->input('preorder_product_id'))
                ->first();

            // Nếu có pre-order và không có product_id cụ thể, tìm theo sản phẩm trong đơn
            if (!$preorder && !empty($productIds)) {
                $preorder = Campaign::where('type', 'preorder')
                    ->where('status', 'active')
                    ->whereIn('product_id', $productIds)
                    ->first();
            }

            if ($preorder) {
                // Kiểm tra pre-order còn hiệu lực
                if ($preorder->start_time && $preorder->start_time > now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chương trình pre-order chưa bắt đầu'
                    ]);
                }

                if ($preorder->end_time && $preorder->end_time < now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chương trình pre-order đã kết thúc'
                    ]);
                }

                // Tính discount dựa trên tiers
                $tiers = $preorder->tiers ?? [];
                $currentBuyers = $preorder->current_buyers ?? 0;
                $discountPercent = 0;

                // Sắp xếp tiers theo từ nhỏ đến lớn
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });

                // Tìm tier phù hợp
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $discountPercent = $tier['discount'] ?? 0;
                        break;
                    }
                }

                // Tính số tiền giảm
                $discountAmount = 0;
                if ($discountPercent > 0) {
                    $discountAmount = ($subtotal * $discountPercent) / 100;
                    $discountAmount = round($discountAmount);
                }

                // Kiểm tra điều kiện đơn hàng tối thiểu
                if ($preorder->min_order > 0 && $subtotal < $preorder->min_order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn hàng tối thiểu ' . number_format($preorder->min_order) . 'đ để áp dụng pre-order'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'type' => 'preorder',
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'current_buyers' => $currentBuyers,
                    'tiers' => $tiers,
                    'campaign_id' => $preorder->id,
                    'preorder_id' => $preorder->id,
                    'message' => 'Áp dụng giảm giá pre-order ' . $discountPercent . '%!',
                ]);
            }

            // Không tìm thấy
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

    /**
     * Lấy thông tin pre-order của sản phẩm
     */
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

            // Kiểm tra thời gian
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

            // Sắp xếp tiers
            usort($tiers, function($a, $b) {
                return ($a['from'] ?? 0) - ($b['from'] ?? 0);
            });

            // Tìm tier phù hợp
            foreach ($tiers as $tier) {
                $from = $tier['from'] ?? 0;
                $to = $tier['to'] ?? PHP_INT_MAX;
                if ($currentBuyers >= $from && $currentBuyers <= $to) {
                    $discountPercent = $tier['discount'] ?? 0;
                    break;
                }
            }

            // Tìm tier tiếp theo
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
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

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
                'campaign_id' => 'nullable|exists:campaigns,id',
            ]);

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
            $campaign = Campaign::findOrFail($id);
            $campaign->delete();

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

    public function togglePreorder($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $newStatus = $campaign->status === 'active' ? 'scheduled' : 'active';
            $campaign->update(['status' => $newStatus]);

            return redirect()->route('admin.promotions.index')->with([
                'success' => true,
                'message' => 'Cập nhật trạng thái pre-order thành công!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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