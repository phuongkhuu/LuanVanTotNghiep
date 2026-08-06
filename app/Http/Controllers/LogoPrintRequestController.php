<?php

namespace App\Http\Controllers;

use App\Models\LogoPrintRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Product;

class LogoPrintRequestController extends Controller
{
    /**
     * Hiển thị form tùy chỉnh với thông tin sản phẩm đã chọn
     */
    public function create(Request $request)
    {
        $productId = $request->query('product_id');
        $product = null;

        if ($productId) {
            $product = Product::with(['variants.color', 'brand', 'category'])
                ->find($productId);
        }

        return Inertia::render('Web/Customize', [
            'selectedProduct' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $this->getProductImage($product),
                'price' => $product->variants->min('price') ?? 0,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'description' => $product->description,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'color' => $variant->color ? [
                            'id' => $variant->color->id,
                            'name' => $variant->color->name,
                            'code' => $variant->color->code,
                        ] : null,
                        'size_name' => $variant->size_name,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'is_on_sale' => $variant->is_on_sale,
                        'stock' => $variant->stock,
                    ];
                })->toArray(),
            ] : null,
        ]);
    }

    /**
     * Lấy ảnh đại diện của sản phẩm
     */
    private function getProductImage($product)
    {
        if (!empty($product->image_url)) {
            $image = $product->image_url;
            if (is_array($image) && !empty($image)) {
                return $image[0];
            }
            if (is_string($image) && $this->isJson($image)) {
                $images = json_decode($image, true);
                if (is_array($images) && !empty($images)) {
                    return $images[0];
                }
            }
            if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
        }
        if (!empty($product->thumbnail)) {
            return $product->thumbnail;
        }
        return '/images/default-product.jpg';
    }

    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Upload file logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo_file' => 'required|file|mimes:png,jpg,jpeg,ai,pdf|max:10240',
        ]);

        try {
            $file = $request->file('logo_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('Upload logo error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lưu yêu cầu tùy chỉnh từ khách hàng (public form) - KHÔNG CÒN DÙNG (chuyển sang luồng giỏ hàng)
     * Giữ lại để tương thích ngược nếu cần
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|in:front,back,side',
            'size' => 'required|string|in:small,medium,large',
            'note' => 'nullable|string|max:1000',
            'logo_file' => 'nullable|file|mimes:png,jpg,jpeg,ai,pdf|max:10240', // 10MB
        ]);

        // 2. Xử lý file logo nếu có
        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            try {
                $file = $request->file('logo_file');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $logoPath = $file->storeAs('logos', $filename, 'public');
            } catch (\Exception $e) {
                Log::error('Upload logo error: ' . $e->getMessage());
                return back()->withErrors(['logo_file' => 'Không thể tải lên file logo. Vui lòng thử lại.']);
            }
        }

        // 3. Tạo một đơn hàng "tùy chỉnh" (order_code = 'customize')
        $order = Order::create([
            'user_id' => auth()->id() ?? null,
            'order_code' => 'customize',
            'customer_name' => $validated['fullName'],
            'customer_phone' => $validated['phone'],
            'customer_email' => $validated['email'],
            'receiver_name' => $validated['fullName'],
            'receiver_phone' => $validated['phone'],
            'shipping_address' => 'Yêu cầu tùy chỉnh - chưa có địa chỉ',
            'total_amount' => 0,
            'discount_amount' => 0,
            'final_amount' => 0,
            'payment_status' => 'pending',
            'order_status' => 0,
            'shipping_fee' => 0,
            'note' => 'Yêu cầu tùy chỉnh in logo',
        ]);

        // Tạo OrderDetail giả
        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_variant_id' => 1,
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ]);

        // 4. Lưu yêu cầu tùy chỉnh
        LogoPrintRequest::create([
            'order_detail_id' => $orderDetail->id,
            'logo_image' => $logoPath,
            'print_position' => $validated['position'],
            'print_size' => $validated['size'],
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        // 5. Trả về phản hồi
        return redirect()->route('home')->with('success', 'Gửi yêu cầu thành công! Chúng tôi sẽ phản hồi trong vòng 24h.');
    }
}