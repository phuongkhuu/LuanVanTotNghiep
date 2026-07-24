<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteRequestController extends Controller
{
    /**
     * Lưu yêu cầu báo giá (B2B)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'ward' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'requirements' => 'nullable|string|max:1000',
            // Thông tin sản phẩm
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Tạo yêu cầu báo giá
            $quoteRequest = QuoteRequest::create([
                'user_id' => Auth::id(),
                'company_name' => $validated['company'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'total_quantity' => $validated['quantity'] ?? 0,
                'total' => 0, // Có thể tính sau
                'requirement' => $validated['requirements'] ?? null,
                'status' => 'pending',
                'logo_file' => null, // Có thể thêm upload logo sau
            ]);

            // Nếu có thông tin sản phẩm, tạo chi tiết
            if (!empty($validated['variant_id']) && !empty($validated['quantity'])) {
                QuoteRequestDetail::create([
                    'quote_request_id' => $quoteRequest->id,
                    'product_variant_id' => $validated['variant_id'],
                    'quantity' => $validated['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Yêu cầu báo giá đã được gửi thành công! Chúng tôi sẽ liên hệ với bạn trong vòng 30 phút.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi gửi yêu cầu báo giá: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ]);
        }
    }
}