<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogoPrintRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogoRequestStatusUpdated;
use App\Mail\QuoteSent;
use App\Mail\CustomizeApproved;
use App\Mail\CustomizeRejected;
use App\Http\Controllers\Payment\PayOSController;
use Illuminate\Support\Facades\Log;

class CustomizeController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu tùy chỉnh (in logo)
     */
    public function index(Request $request)
    {
        $query = LogoPrintRequest::with(['orderDetail.order', 'orderDetail.productVariant.product']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('orderDetail.order', function ($sub) use ($search) {
                    $sub->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('receiver_name', 'like', "%{$search}%");
                })->orWhereHas('orderDetail.productVariant.product', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        $formatted = $requests->map(function ($item) {
            $order = $item->orderDetail->order ?? null;
            $product = $item->orderDetail->productVariant->product ?? null;
            return [
                'id' => $item->id,
                'customer' => $order ? $order->customer_name : 'N/A',
                'customerType' => $order && $order->order_code === 'wholesale' ? 'business' : 'retail',
                'email' => $order ? $order->customer_email : '',
                'phone' => $order ? $order->customer_phone : '',
                'product' => $product ? $product->name : 'N/A',
                'position' => $item->print_position ?? '',
                'size' => $item->print_size ?? '',
                'quantity' => $item->orderDetail->quantity ?? 1,
                'date' => $item->created_at->format('d/m/Y'),
                'status' => $item->status,
                'note' => $item->note,
                'designFile' => $item->logo_image,
                'order_id' => $order ? $order->id : null,
                'order_number' => $order ? $order->order_number : null,
                'total_amount' => $order ? $order->final_amount : 0,
            ];
        });

        return Inertia::render('Admin/Customize', [
            'initialRequests' => $formatted,
            'filters' => $request->only(['status', 'search']),
            'pagination' => [
                'total' => $requests->total(),
                'per_page' => $requests->perPage(),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
            ],
        ]);
    }

    /**
     * Cập nhật trạng thái yêu cầu (duyệt/từ chối)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,processing,completed',
            'feedback' => 'nullable|string|max:500',
        ]);

        $logoRequest = LogoPrintRequest::findOrFail($id);
        $oldStatus = $logoRequest->status;
        $order = $logoRequest->orderDetail->order ?? null;

        // Cập nhật logo request
        $logoRequest->status = $validated['status'];
        $logoRequest->save();

        // Nếu là approved, tạo link PayOS và gửi email
        if ($validated['status'] === 'approved' && $order) {
            // Cập nhật order_status = 1 (chờ thanh toán)
            $order->order_status = 1;
            $order->save();

            // Tạo link PayOS
            $payos = app(PayOSController::class);
            $response = $payos->getPaymentLink($order->id, $order->final_amount);

            $paymentLink = null;
            if ($response->getStatusCode() === 200) {
                $data = $response->getData();
                if ($data->success) {
                    $paymentLink = $data->checkout_url;
                }
            }

            // Gửi email với link thanh toán
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new CustomizeApproved($order, $logoRequest, $paymentLink));
                    Log::info('Email duyệt customize đã gửi với link PayOS', [
                        'order_id' => $order->id,
                        'email' => $order->customer_email,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gửi email duyệt customize thất bại: ' . $e->getMessage());
                }
            }
        } elseif ($validated['status'] === 'rejected' && $order) {
            // Từ chối: gửi email với lý do
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new CustomizeRejected($order, $logoRequest, $validated['feedback'] ?? 'Không có lý do cụ thể.'));
                } catch (\Exception $e) {
                    Log::error('Gửi email từ chối customize thất bại: ' . $e->getMessage());
                }
            }
        } else {
            // Các trạng thái khác: gửi email thông báo thông thường
            if ($order && $order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new LogoRequestStatusUpdated($logoRequest, $validated['feedback'] ?? null));
                } catch (\Exception $e) {
                    Log::error('Gửi email thông báo thất bại: ' . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công.');
    }

    /**
     * Duyệt yêu cầu (được gọi từ nút "Duyệt" trong modal)
     */
    public function approve($id)
    {
        $logoRequest = LogoPrintRequest::findOrFail($id);
        $order = $logoRequest->orderDetail->order ?? null;

        if (!$order) {
            return back()->with('error', 'Không tìm thấy đơn hàng.');
        }

        // Cập nhật logo request
        $logoRequest->status = 'approved';
        $logoRequest->save();

        // Cập nhật order_status
        $order->order_status = 1; // Chờ thanh toán
        $order->save();

        // Tạo link PayOS
        $payos = app(PayOSController::class);
        $response = $payos->getPaymentLink($order->id, $order->final_amount);

        $paymentLink = null;
        if ($response->getStatusCode() === 200) {
            $data = $response->getData();
            if ($data->success) {
                $paymentLink = $data->checkout_url;
            }
        }

        // Gửi email với link thanh toán
        if ($order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new CustomizeApproved($order, $logoRequest, $paymentLink));
                Log::info('Email duyệt customize đã gửi với link PayOS', [
                    'order_id' => $order->id,
                    'email' => $order->customer_email,
                ]);
            } catch (\Exception $e) {
                Log::error('Gửi email duyệt customize thất bại: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Đã duyệt yêu cầu và gửi link thanh toán thành công.');
    }

    /**
     * Gửi báo giá cho khách hàng qua email
     */
    public function sendQuote(Request $request)
    {
        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'product' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'designDescription' => 'nullable|string',
            'estimatedPrice' => 'required|numeric|min:0',
            'estimatedTime' => 'nullable|string|max:255',
        ]);

        try {
            Mail::to($validated['email'])->send(new QuoteSent($validated));
            return redirect()->back()->with('success', 'Đã gửi báo giá thành công.');
        } catch (\Exception $e) {
            Log::error('Gửi báo giá thất bại: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gửi báo giá thất bại. Vui lòng thử lại.');
        }
    }
}