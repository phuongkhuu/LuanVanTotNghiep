<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Campaign;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Định nghĩa danh sách tools cho Gemini Function Calling
     */
    public function getTools(): array
    {
        return [
            // Tool 1: Tìm kiếm sản phẩm theo bộ lọc
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_products_by_filters',
                    'description' => 'Tìm kiếm sản phẩm theo danh mục, thương hiệu, khoảng giá, chất liệu, tên, tình trạng tồn kho.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => [
                                'type' => 'string',
                                'description' => 'Tên danh mục (ví dụ: "Balo Laptop", "Balo Du lịch")'
                            ],
                            'brand' => [
                                'type' => 'string',
                                'description' => 'Tên thương hiệu (ví dụ: "BigBag", "Samsonite")'
                            ],
                            'min_price' => [
                                'type' => 'integer',
                                'description' => 'Giá thấp nhất (VNĐ)'
                            ],
                            'max_price' => [
                                'type' => 'integer',
                                'description' => 'Giá cao nhất (VNĐ)'
                            ],
                            'material' => [
                                'type' => 'string',
                                'description' => 'Chất liệu (ví dụ: "Nylon", "Polyester")'
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'Từ khóa trong tên sản phẩm'
                            ],
                            'in_stock' => [
                                'type' => 'boolean',
                                'description' => 'Chỉ lấy sản phẩm còn hàng (stock > 0)'
                            ],
                        ],
                    ],
                ],
            ],

            // Tool 2: Lấy khuyến mãi thường (không voucher, không preorder)
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_active_campaigns',
                    'description' => 'Lấy các chương trình khuyến mãi đang hoạt động (không bao gồm voucher và preorder).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
            ],

            // Tool 3: Lấy danh sách voucher
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_vouchers',
                    'description' => 'Lấy danh sách các mã giảm giá (voucher) đang hoạt động.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
            ],

            // Tool 4: Lấy thông tin preorder
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_preorder_info',
                    'description' => 'Lấy thông tin sản phẩm đặt trước (preorder) đang diễn ra.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
            ],

            // Tool 5: Tra cứu đơn hàng
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_order_status',
                    'description' => 'Tra cứu trạng thái đơn hàng theo mã đơn hàng. Yêu cầu người dùng cung cấp mã nếu chưa có.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'order_code' => [
                                'type' => 'string',
                                'description' => 'Mã đơn hàng (ID)'
                            ],
                            'user_id' => [
                                'type' => 'integer',
                                'description' => 'ID người dùng (sẽ được truyền từ session)'
                            ],
                        ],
                        'required' => ['order_code'],
                    ],
                ],
            ],

            // Tool 6: Lấy chi tiết sản phẩm theo slug
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_by_slug',
                    'description' => 'Lấy chi tiết một sản phẩm theo slug.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'slug' => [
                                'type' => 'string',
                                'description' => 'Slug của sản phẩm (ví dụ: "balo-laptop-bigbag-pro-15-6")'
                            ],
                        ],
                        'required' => ['slug'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Thực thi tool theo yêu cầu của LLM
     */
    public function executeTool(string $toolName, array $arguments): array
    {
        try {
            return match ($toolName) {
                'get_products_by_filters' => $this->getProductsByFilters($arguments),
                'get_active_campaigns' => $this->getActiveCampaigns(),
                'get_vouchers' => $this->getVouchers(),
                'get_preorder_info' => $this->getPreorderInfo(),
                'get_order_status' => $this->getOrderStatus($arguments),
                'get_product_by_slug' => $this->getProductBySlug($arguments),
                default => ['error' => "Tool '{$toolName}' không tồn tại."],
            };
        } catch (\Exception $e) {
            Log::error("Chatbot Service Error: " . $e->getMessage());
            return ['error' => 'Có lỗi xảy ra khi truy vấn dữ liệu. Vui lòng thử lại sau.'];
        }
    }

    // ==================== HÀM TÌM SẢN PHẨM ====================

    private function getProductsByFilters(array $filters): array
    {
        $query = Product::with(['variants.color', 'category', 'brand']);

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['category'] . '%');
            });
        }

        if (!empty($filters['brand'])) {
            $query->whereHas('brand', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['brand'] . '%');
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['material'])) {
            $query->where('material', 'like', '%' . $filters['material'] . '%');
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $query->whereHas('variants', function ($q) use ($filters) {
                if (isset($filters['min_price'])) {
                    $q->where('price', '>=', $filters['min_price']);
                }
                if (isset($filters['max_price'])) {
                    $q->where('price', '<=', $filters['max_price']);
                }
            });
        }

        if (!empty($filters['in_stock'])) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        // Giới hạn 5 sản phẩm để tránh tràn token
        $products = $query->limit(5)->get();

        if ($products->isEmpty()) {
            return ['message' => 'Không tìm thấy sản phẩm phù hợp với tiêu chí của bạn.'];
        }

        return $products->map(function ($product) {
            // Lấy variant có giá thấp nhất và giá khuyến mãi (nếu có)
            $minVariant = $product->variants->sortBy('price')->first();
            $priceMin = $minVariant ? $minVariant->price : 0;
            $salePriceMin = $minVariant && $minVariant->sale_price ? $minVariant->sale_price : null;

            // Lấy ảnh đại diện: ưu tiên thumbnail, nếu không có thì lấy ảnh đầu tiên từ image_url
            $image = $product->thumbnail;
            if (empty($image) && $product->image_url) {
                $images = is_array($product->image_url) ? $product->image_url : json_decode($product->image_url, true);
                if (is_array($images) && !empty($images)) {
                    $image = $images[0];
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand' => $product->brand->name ?? 'N/A',
                'category' => $product->category->name ?? 'N/A',
                'material' => $product->material,
                'description' => $product->description,
                'thumbnail' => $image,
                'price_min' => number_format($priceMin, 0, ',', '.') . ' VND',
                'sale_price_min' => $salePriceMin ? number_format($salePriceMin, 0, ',', '.') . ' VND' : null,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'color' => $variant->color->name ?? 'N/A',
                        'size' => $variant->size_name,
                        'price' => number_format($variant->price, 0, ',', '.') . ' VND',
                        'stock' => $variant->stock,
                        'sale_price' => $variant->sale_price ? number_format($variant->sale_price, 0, ',', '.') . ' VND' : null,
                    ];
                }),
            ];
        })->toArray();
    }

    // ==================== HÀM LẤY KHUYẾN MÃI THƯỜNG ====================

    private function getActiveCampaigns(): array
    {
        $campaigns = Campaign::where('status', 'active')
            ->whereNotIn('type', ['voucher', 'preorder'])
            ->where(function ($query) {
                $query->whereNull('end_time')
                      ->orWhere('end_time', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('expiry')
                      ->orWhere('expiry', '>', now()->toDateString());
            })
            ->get();

        if ($campaigns->isEmpty()) {
            return ['message' => 'Hiện không có chương trình khuyến mãi thường nào đang diễn ra.'];
        }

        return $campaigns->map(function ($campaign) {
            // Lấy cấu hình giảm giá theo số lượng nếu có
            $configs = $campaign->configs;
            $discountInfo = null;
            if ($configs->isNotEmpty()) {
                $config = $configs->first();
                $discountInfo = [
                    'min_quantity' => $config->quantity,
                    'discount_percent' => $config->discount_percent,
                ];
            }

            $discountValue = $campaign->discount_value ?? 0;
            $discountType = $campaign->discount_type;

            // Nếu discount_value = 0 nhưng có config, ưu tiên config
            if ($discountValue == 0 && $discountInfo) {
                $discountValue = $discountInfo['discount_percent'];
                $discountType = 'percent';
            }

            return [
                'id' => $campaign->id,
                'name' => $campaign->name ?? 'Chương trình không tên',
                'type' => $campaign->type,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_text' => $this->formatDiscountText($discountType, $discountValue),
                'min_order' => $campaign->min_order > 0 ? number_format($campaign->min_order, 0, ',', '.') . ' VND' : 'Không yêu cầu',
                'description' => $campaign->description,
                'start_date' => $campaign->start_time?->format('d/m/Y'),
                'end_date' => $campaign->end_time?->format('d/m/Y'),
                'expiry' => $campaign->expiry?->format('d/m/Y'),
                'has_quantity_config' => !is_null($discountInfo),
                'config_detail' => $discountInfo,
            ];
        })->toArray();
    }

    // ==================== HÀM LẤY VOUCHER ====================

    private function getVouchers(): array
    {
        $vouchers = Campaign::where('status', 'active')
            ->where('type', 'voucher')
            ->where(function ($query) {
                $query->whereNull('end_time')
                      ->orWhere('end_time', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('expiry')
                      ->orWhere('expiry', '>', now()->toDateString());
            })
            ->get();

        if ($vouchers->isEmpty()) {
            return ['message' => 'Hiện không có voucher nào đang hoạt động.'];
        }

        return $vouchers->map(function ($voucher) {
            return [
                'id' => $voucher->id,
                'code' => $voucher->code ?? 'Không có mã',
                'name' => $voucher->name ?? 'Voucher',
                'discount_type' => $voucher->discount_type,
                'discount_text' => $this->formatDiscountText($voucher->discount_type, $voucher->discount_value),
                'discount_value' => $voucher->discount_value,
                'min_order' => $voucher->min_order > 0 ? number_format($voucher->min_order, 0, ',', '.') . ' VND' : 'Không yêu cầu',
                'description' => $voucher->description,
                'expiry' => $voucher->expiry?->format('d/m/Y') ?? 'Không giới hạn',
                'used' => $voucher->used,
                'limit' => $voucher->limit,
                'remaining' => $voucher->limit - $voucher->used,
            ];
        })->toArray();
    }

    // ==================== HÀM LẤY PREORDER ====================

    private function getPreorderInfo(): array
    {
        $preorders = Campaign::where('status', 'active')
            ->where('type', 'preorder')
            ->where(function ($query) {
                $query->whereNull('end_time')
                      ->orWhere('end_time', '>', now());
            })
            ->with('product')
            ->get();

        if ($preorders->isEmpty()) {
            return ['message' => 'Hiện không có sản phẩm preorder nào đang diễn ra.'];
        }

        return $preorders->map(function ($preorder) {
            $tiers = $preorder->tiers ?? [];
            $currentBuyers = $preorder->current_buyers ?? 0;
            $currentDiscount = 0;
            foreach ($tiers as $tier) {
                $from = $tier['from'] ?? 0;
                $to = $tier['to'] ?? PHP_INT_MAX;
                if ($currentBuyers >= $from && $currentBuyers <= $to) {
                    $currentDiscount = $tier['discount'] ?? 0;
                    break;
                }
            }

            // Tính số lượng đặt trước tối thiểu để đạt các mức giảm tiếp theo
            $nextTier = null;
            foreach ($tiers as $tier) {
                if (($tier['from'] ?? 0) > $currentBuyers) {
                    $nextTier = $tier;
                    break;
                }
            }

            return [
                'product_name' => $preorder->product->name ?? 'Sản phẩm',
                'product_id' => $preorder->product_id,
                'product_slug' => $preorder->product->slug ?? null,
                'current_buyers' => $currentBuyers,
                'tiers' => $tiers,
                'current_discount' => $currentDiscount . '%',
                'next_tier' => $nextTier ? "Cần thêm " . ($nextTier['from'] - $currentBuyers) . " đơn hàng để đạt giảm " . $nextTier['discount'] . '%' : 'Đã đạt mức giảm cao nhất',
                'description' => $preorder->description,
                'end_date' => $preorder->end_time?->format('d/m/Y') ?? 'Không giới hạn',
            ];
        })->toArray();
    }

    // ==================== HÀM TRA CỨU ĐƠN HÀNG ====================

    private function getOrderStatus(array $params): array
    {
        $orderCode = $params['order_code'] ?? null;
        $userId = $params['user_id'] ?? null;

        if (!$orderCode) {
            return ['error' => 'Vui lòng cung cấp mã đơn hàng.'];
        }

        $query = Order::with(['orderDetails.productVariant.product']);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $order = $query->where('id', $orderCode)->first();

        if (!$order) {
            return ['error' => 'Không tìm thấy đơn hàng với mã này. Vui lòng kiểm tra lại.'];
        }

        $statusMap = [
            0 => 'Chờ xác nhận',
            1 => 'Đang xử lý',
            2 => 'Đang giao hàng',
            3 => 'Đã giao hàng',
            4 => 'Đã hủy',
        ];

        return [
            'order_id' => $order->id,
            'status' => $statusMap[$order->order_status] ?? 'Không xác định',
            'status_code' => $order->order_status,
            'total_amount' => number_format($order->final_amount, 0, ',', '.') . ' VND',
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'receiver_name' => $order->receiver_name,
            'shipping_address' => $order->shipping_address,
            'items' => $order->orderDetails->map(function ($detail) {
                return [
                    'product_name' => $detail->productVariant->product->name ?? 'N/A',
                    'quantity' => $detail->quantity,
                    'unit_price' => number_format($detail->unit_price, 0, ',', '.') . ' VND',
                    'subtotal' => number_format($detail->subtotal, 0, ',', '.') . ' VND',
                ];
            }),
        ];
    }

    // ==================== HÀM LẤY CHI TIẾT SẢN PHẨM THEO SLUG ====================

    private function getProductBySlug(array $params): array
    {
        $slug = $params['slug'] ?? null;

        if (!$slug) {
            return ['error' => 'Vui lòng cung cấp slug sản phẩm.'];
        }

        $product = Product::with(['variants.color', 'category', 'brand'])
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            return ['error' => 'Không tìm thấy sản phẩm.'];
        }

        // Lấy variant có giá thấp nhất và giá khuyến mãi (nếu có)
        $minVariant = $product->variants->sortBy('price')->first();
        $priceMin = $minVariant ? $minVariant->price : 0;
        $salePriceMin = $minVariant && $minVariant->sale_price ? $minVariant->sale_price : null;

        // Lấy ảnh đại diện: ưu tiên thumbnail, nếu không có thì lấy ảnh đầu tiên từ image_url
        $image = $product->thumbnail;
        if (empty($image) && $product->image_url) {
            $images = is_array($product->image_url) ? $product->image_url : json_decode($product->image_url, true);
            if (is_array($images) && !empty($images)) {
                $image = $images[0];
            }
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand->name ?? 'N/A',
            'category' => $product->category->name ?? 'N/A',
            'material' => $product->material,
            'description' => $product->description,
            'thumbnail' => $image,
            'price_min' => number_format($priceMin, 0, ',', '.') . ' VND',
            'sale_price_min' => $salePriceMin ? number_format($salePriceMin, 0, ',', '.') . ' VND' : null,
            'is_featured' => $product->is_featured,
            'is_preorder' => $product->is_preorder,
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color->name ?? 'N/A',
                    'size' => $variant->size_name,
                    'price' => number_format($variant->price, 0, ',', '.') . ' VND',
                    'stock' => $variant->stock,
                    'sale_price' => $variant->sale_price ? number_format($variant->sale_price, 0, ',', '.') . ' VND' : null,
                    'rating' => $variant->rating,
                ];
            }),
        ];
    }

    // ==================== HÀM TIỆN ÍCH ====================

    /**
     * Định dạng văn bản giảm giá
     */
    private function formatDiscountText(?string $type, $value): string
    {
        if ($type === 'fixed') {
            return number_format($value, 0, ',', '.') . ' VND';
        } elseif ($type === 'percent') {
            return $value . '%';
        } elseif ($type === 'freeship') {
            return 'Miễn phí vận chuyển';
        } elseif ($value > 0) {
            return (string) $value . ' (không rõ loại)';
        }
        return '0 (có thể là quà tặng kèm)';
    }
}