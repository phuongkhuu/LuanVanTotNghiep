<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use App\Models\CampaignConfig;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            // Lấy banners đang hoạt động và có campaign
            $banners = Banner::with(['campaign'])
                ->where('status', 1)
                ->whereNotNull('campaign_id')
                ->orderBy('order', 'asc')
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title ?? 'Banner',
                        'image' => $banner->image,
                        'link' => $banner->link,
                        'description' => $banner->description,
                        'campaign_id' => $banner->campaign_id,
                        'campaign' => $banner->campaign ? [
                            'id' => $banner->campaign->id,
                            'name' => $banner->campaign->name,
                            'type' => $banner->campaign->type,
                            'discount' => $banner->campaign->discount_value ?? 0,
                        ] : null,
                    ];
                });

            // Lấy các campaign đang diễn ra (active) kèm theo banners
            $activeCampaigns = Campaign::with([
                'configs', 
                'productVariants', 
                'productVariants.product', 
                'productVariants.color',
                'banners' // THÊM QUAN HỆ BANNER
            ])
                ->where('status', 'active')
                ->whereIn('type', ['seasonal', 'campaign', 'flash_sale', 'anniversary', 'holiday', 'product_launch', 'other'])
                ->latest()
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->configs->first();
                    $discountPercent = $config ? (float) $config->discount_percent : 0;
                    
                    // Lấy sản phẩm đầu tiên để hiển thị
                    $firstVariant = $campaign->productVariants->first();
                    $productImage = null;
                    $productName = $campaign->name;
                    
                    if ($firstVariant && $firstVariant->product) {
                        $productImage = $firstVariant->product->image ?? null;
                        $productName = $firstVariant->product->name;
                    }
                    
                    // Lấy banner đang hoạt động đầu tiên của campaign
                    $activeBanner = $campaign->banners
                        ->where('status', Banner::STATUS_ACTIVE)
                        ->first();
                    
                    // Nếu không có banner active, lấy banner đầu tiên bất kỳ
                    $banner = $activeBanner ?? $campaign->banners->first();
                    
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name ?? 'Chiến dịch #' . $campaign->id,
                        'type' => $campaign->type ?? 'seasonal',
                        'description' => $campaign->description ?? '',
                        'start_date' => $campaign->start_time ? $campaign->start_time->format('d/m/Y') : null,
                        'end_date' => $campaign->end_time ? $campaign->end_time->format('d/m/Y') : null,
                        'status' => $campaign->status,
                        'discount_percent' => $discountPercent,
                        'discount_text' => $discountPercent > 0 ? 'Giảm ' . $discountPercent . '%' : 'Ưu đãi đặc biệt',
                        'product_image' => $productImage,
                        'product_name' => $productName,
                        'product_count' => $campaign->productVariants->count(),
                        'featured' => $campaign->featured ?? false,
                        // THÊM BANNER
                        'banner' => $banner ? [
                            'id' => $banner->id,
                            'image' => $banner->image,
                            'title' => $banner->title,
                            'link' => $banner->link,
                            'status' => $banner->status,
                        ] : null,
                        // Fallback: dùng banner_url nếu có trong bảng campaigns
                        'banner_url' => $campaign->banner ?? null,
                    ];
                });

            // Lấy flash sale đang diễn ra
            $flashCampaigns = Campaign::with([
                'configs', 
                'productVariants', 
                'productVariants.product', 
                'productVariants.color',
                'banners'
            ])
                ->where('status', 'active')
                ->where('type', 'flash_sale')
                ->latest()
                ->get();

            $flashProducts = [];
            foreach ($flashCampaigns as $campaign) {
                $config = $campaign->configs->first();
                $discountPercent = $config ? (float) $config->discount_percent : 0;
                
                // Lấy banner cho flash sale
                $banner = $campaign->banners->where('status', Banner::STATUS_ACTIVE)->first() 
                    ?? $campaign->banners->first();
                $bannerImage = $banner ? $banner->image : null;
                
                foreach ($campaign->productVariants as $variant) {
                    $originalPrice = $variant->price ?? 0;
                    $salePrice = $originalPrice * (1 - $discountPercent / 100);
                    
                    $flashProducts[] = [
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'name' => $variant->product ? $variant->product->name : 'Sản phẩm',
                        'original_price' => $originalPrice,
                        'sale_price' => $salePrice,
                        'discount_percent' => $discountPercent,
                        'image' => $variant->product ? $variant->product->image : null,
                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,
                        'color' => $variant->color ? $variant->color->name : null,
                        'banner_image' => $bannerImage, // Thêm banner cho flash sale
                    ];
                }
            }

            // Lấy vouchers đang hoạt động
            $vouchers = Campaign::where('status', 'active')
                ->where('type', 'voucher')
                ->latest()
                ->get()
                ->map(function ($voucher) {
                    $discountText = '';
                    if ($voucher->discount_type === 'percent') {
                        $discountText = 'GIẢM ' . $voucher->discount_value . '%';
                    } elseif ($voucher->discount_type === 'fixed') {
                        $discountText = 'GIẢM ' . number_format($voucher->discount_value) . 'đ';
                    } elseif ($voucher->discount_type === 'freeship') {
                        $discountText = 'FREE SHIP';
                    }
                    
                    $conditionText = $voucher->min_order > 0 
                        ? 'Đơn từ ' . number_format($voucher->min_order) . 'đ' 
                        : 'Không yêu cầu tối thiểu';
                    
                    return [
                        'id' => $voucher->id,
                        'code' => $voucher->code,
                        'name' => $voucher->name,
                        'discount_text' => $discountText,
                        'condition_text' => $conditionText,
                        'expiry' => $voucher->expiry ? $voucher->expiry->format('d/m/Y') : null,
                        'description' => $voucher->description,
                    ];
                });

            return Inertia::render('Web/Promotion', [
                'banners' => $banners,
                'activeCampaigns' => $activeCampaigns,
                'flashProducts' => $flashProducts,
                'vouchers' => $vouchers,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi load trang khuyến mãi: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return Inertia::render('Web/Promotion', [
                'banners' => [],
                'activeCampaigns' => [],
                'flashProducts' => [],
                'vouchers' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function checkExpiredPreorders()
    {
        try {
            // Xử lý preorder hết hạn
            $expiredPreorders = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('end_time', '<', now())
                ->get();
            
            foreach ($expiredPreorders as $preorder) {
                $product = Product::find($preorder->product_id);
                if ($product) {
                    foreach ($product->variants as $variant) {
                        $variant->update([
                            'sale_price' => null,
                            'is_on_sale' => false,
                            'sale_type' => null,
                            'sale_campaign_id' => null,
                        ]);
                    }
                }
                
                $preorder->update(['status' => 'ended']);
                
                // Cập nhật banner liên quan sang trạng thái Đã khóa (-1)
                Banner::where('campaign_id', $preorder->id)
                    ->update(['status' => Banner::STATUS_INACTIVE]);
            }
            
            // Xử lý campaign thường hết hạn
            $expiredCampaigns = Campaign::where('type', '!=', 'preorder')
                ->where('type', '!=', 'voucher')
                ->where('status', 'active')
                ->where('end_time', '<', now())
                ->get();
            
            foreach ($expiredCampaigns as $campaign) {
                $this->resetRetailSalePrice($campaign);
                $campaign->update(['status' => 'ended']);
                
                // Cập nhật banner liên quan sang trạng thái Đã khóa (-1)
                Banner::where('campaign_id', $campaign->id)
                    ->update(['status' => Banner::STATUS_INACTIVE]);
            }
            
            return $expiredPreorders->count() + $expiredCampaigns->count();
            
        } catch (\Exception $e) {
            Log::error('Error checking expired campaigns: ' . $e->getMessage());
            return 0;
        }
    }

}