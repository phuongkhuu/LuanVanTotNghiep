<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Tính giá sale cho variant
     */
    private function calculateSalePrice($variant)
    {
        $originalPrice = $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $now = now();

        // Kiểm tra campaign (retail)
        $campaigns = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now);
                })->orWhere(function($q) {
                    $q->whereNull('start_time')
                      ->whereNull('end_time');
                });
            })
            ->whereHas('productVariants', function($query) use ($variant) {
                $query->where('product_variant_id', $variant->id);
            })
            ->with('configs')
            ->get();

        foreach ($campaigns as $campaign) {
            $config = $campaign->configs()->first();
            $currentDiscount = $config ? (float) $config->discount_percent : 0;
            if ($currentDiscount > $discountPercent) {
                $discountPercent = $currentDiscount;
            }
        }

        // Kiểm tra pre-order
        if ($variant->product && ($variant->product->is_preorder ?? false)) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $variant->product_id)
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->first();

            if ($preorder) {
                $currentBuyers = $preorder->current_buyers ?? 0;
                $tiers = $preorder->tiers ?? [];
                
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = $tier['discount'] ?? 0;
                        if ($preorderDiscount > $discountPercent) {
                            $discountPercent = $preorderDiscount;
                        }
                        break;
                    }
                }
                
                if ($discountPercent == 0 && !empty($tiers)) {
                    $discountPercent = $tiers[0]['discount'] ?? 0;
                }
            }
        }

        if ($discountPercent > 0) {
            $salePrice = $originalPrice * (1 - $discountPercent / 100);
            $salePrice = round($salePrice);
        }

        return [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $discountPercent > 0,
        ];
    }

    /**
     * Lấy giỏ hàng từ request (client gửi lên)
     * CHỈ LẤY SẢN PHẨM THƯỜNG, BỎ QUA PRE-ORDER
     */
    public function index(Request $request)
    {
        try {
            Log::info('CartController@index called', ['method' => $request->method()]);
            
            // Lấy cart từ request
            $cart = [];
            if ($request->isMethod('post')) {
                $cart = $request->input('cart', []);
            } else {
                $cartJson = $request->query('cart', '{}');
                $cart = json_decode($cartJson, true) ?: [];
            }
            
            if (empty($cart)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'total' => 0,
                    'count' => 0
                ]);
            }
            
            $items = [];
            $total = 0;
            $count = 0;

            foreach ($cart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if (!$variant) {
                    Log::warning("Variant not found: {$variantId}");
                    continue;
                }
                
                // ============ BỎ QUA PRE-ORDER (KHÔNG HIỂN THỊ TRONG GIỎ) ============
                if ($variant->product->is_preorder ?? false) {
                    Log::info("Skipping pre-order item in cart: {$variantId}");
                    continue;
                }

                // Tính giá sale
                $saleInfo = $this->calculateSalePrice($variant);
                $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

                // Lấy ảnh sản phẩm
                $image = '/images/default-product.jpg';
                if ($variant->product && $variant->product->image_url) {
                    if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                        $image = $variant->product->image_url[0];
                    } elseif (is_string($variant->product->image_url)) {
                        $image = $variant->product->image_url;
                    }
                }

                $items[] = [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'product_variant_id' => (int) $variantId,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $item['quantity'] ?? 1,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ];
                $total += $price * ($item['quantity'] ?? 1);
                $count += ($item['quantity'] ?? 1);
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'total' => $total,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Cart index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm vào giỏ hàng
     * CHỈ CHO PHÉP SẢN PHẨM THƯỜNG, TỪ CHỐI PRE-ORDER
     */
    public function add(Request $request)
    {
        try {
            Log::info('CartController@add called', $request->all());
            
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $variantId = $request->variant_id;
            $quantity = $request->quantity ?? 1;

            $variant = ProductVariant::with('product', 'color')->find($variantId);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            // ============ PRE-ORDER: KHÔNG CHO THÊM VÀO GIỎ HÀNG ============
            if ($variant->product->is_preorder ?? false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm Pre-order không thể thêm vào giỏ hàng. Vui lòng chọn "Mua ngay" để đặt hàng.'
                ], 400);
            }

            // Kiểm tra stock (chỉ cho sản phẩm thường)
            if ($variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm chỉ còn {$variant->stock} sản phẩm"
                ], 400);
            }

            // Tính giá sale
            $saleInfo = $this->calculateSalePrice($variant);
            $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

            // Lấy ảnh
            $image = '/images/default-product.jpg';
            if ($variant->product && $variant->product->image_url) {
                if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                    $image = $variant->product->image_url[0];
                } elseif (is_string($variant->product->image_url)) {
                    $image = $variant->product->image_url;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'item' => [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $quantity,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật giỏ hàng
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:0'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($variantId, Request $request)
    {
        try {
            Log::info("CartController@remove called: {$variantId}");
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            Log::info('CartController@clear called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra và áp dụng voucher
     */
    public function applyCoupon(Request $request)
    {
        try {
            Log::info('CartController@applyCoupon called', $request->all());
            
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0'
            ]);

            $code = strtoupper($request->code);
            $subtotal = $request->subtotal;

            $voucher = Campaign::where('code', $code)
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ'
                ], 400);
            }

            if ($voucher->expiry && $voucher->expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn'
                ], 400);
            }

            if ($voucher->limit && $voucher->used >= $voucher->limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết'
                ], 400);
            }

            if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ'
                ], 400);
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
                'coupon' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                ],
                'discount_amount' => $discountAmount,
                'message' => 'Áp dụng mã giảm giá thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCoupon(Request $request)
    {
        return response()->json([
            'success' => true,
            'coupon' => null,
            'discount_amount' => 0,
        ]);
    }

    public function removeCoupon(Request $request)
    {
        try {
            Log::info('CartController@removeCoupon called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            $request->session()->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mã giảm giá'
            ]);
        } catch (\Exception $e) {
            Log::error('Remove coupon error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}