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

            // Lấy các campaign đang diễn ra (active)
            $activeCampaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color'])
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
                    ];
                });

            // Lấy flash sale đang diễn ra
            $flashCampaigns = Campaign::with(['configs', 'productVariants', 'productVariants.product', 'productVariants.color'])
                ->where('status', 'active')
                ->where('type', 'flash_sale')
                ->latest()
                ->get();

            $flashProducts = [];
            foreach ($flashCampaigns as $campaign) {
                $config = $campaign->configs->first();
                $discountPercent = $config ? (float) $config->discount_percent : 0;
                
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
            
            return Inertia::render('Web/Promotion', [
                'banners' => [],
                'activeCampaigns' => [],
                'flashProducts' => [],
                'vouchers' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu'
            ]);
        }
    }
}