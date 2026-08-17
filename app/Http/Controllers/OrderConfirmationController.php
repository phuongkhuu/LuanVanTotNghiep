<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderConfirmed;
use App\Mail\OrderCancelled;
use App\Http\Controllers\Payment\PayOSController;

class OrderConfirmationController extends Controller
{
    /**
     * Xác nhận đơn hàng – tạo link thanh toán và gửi email
     */
    public function confirm($token)
    {
        $order = Order::where('confirmation_token', $token)
                      ->where('token_expires_at', '>', now())
                      ->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Link không hợp lệ hoặc đã hết hạn.');
        }

        if ($order->is_confirmed) {
            return redirect()->route('home')->with('info', 'Đơn hàng đã được xác nhận trước đó.');
        }

        // Cập nhật trạng thái
        $order->is_confirmed = true;
        $order->order_status = 1; // Đang xử lý
        $order->save();

        // Lấy email hợp lệ (ưu tiên customer_email, nếu không có thì user email)
        $email = $order->customer_email ?? $order->user?->email ?? null;
        $email = trim($email);

        // Kiểm tra email hợp lệ
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Xác nhận đơn hàng thất bại: email không hợp lệ', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'user_email' => $order->user?->email,
                'order_number' => $order->order_number,
            ]);
            
            // Vô hiệu token nhưng không gửi email
            $order->confirmation_token = null;
            $order->save();

            return redirect()->route('home')->with('warning', 'Đơn hàng đã được xác nhận, nhưng không thể gửi email do thiếu địa chỉ email. Vui lòng liên hệ hỗ trợ để nhận link thanh toán.');
        }

        // ===== XÁC ĐỊNH SỐ TIỀN THANH TOÁN =====
        // Đối với đơn sỉ: thanh toán tiền cọc (50% = deposit_amount)
        // Đối với đơn thường/preorder: thanh toán toàn bộ (final_amount)
        if ($order->order_code === 'wholesale' && $order->deposit_amount > 0) {
            $amount = (int) $order->deposit_amount;
        } else {
            $amount = (int) $order->final_amount;
        }

        Log::info('OrderConfirmationController - Amount to pay', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'deposit_amount' => $order->deposit_amount,
            'final_amount' => $order->final_amount,
            'amount' => $amount,
        ]);

        // Tạo link thanh toán PayOS
        $payos = app(PayOSController::class);
        $response = $payos->getPaymentLink($order->id, $amount);

        $paymentLink = null;
        if ($response->getStatusCode() === 200) {
            $data = $response->getData();
            if ($data->success) {
                $paymentLink = $data->checkout_url;
            }
        }

        // Log nếu không tạo được link
        if (!$paymentLink) {
            Log::error('Không thể tạo link thanh toán PayOS cho đơn hàng', [
                'order_id' => $order->id,
                'amount' => $amount,
                'response_status' => $response->getStatusCode(),
                'response_data' => $response->getData(),
            ]);
        }

        // Gửi email chứa link thanh toán (nếu có link)
        try {
            Mail::to($email)->send(new OrderConfirmed($order, $paymentLink));
            Log::info('Email xác nhận đã gửi thành công', [
                'order_id' => $order->id,
                'email' => $email,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi email xác nhận đơn hàng', [
                'order_id' => $order->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            // Vẫn tiếp tục, không làm gián đoạn
        }

        // Vô hiệu token (không cho dùng lại)
        $order->confirmation_token = null;
        $order->save();

        return redirect()->route('home')->with('success', 'Đơn hàng đã được xác nhận! Vui lòng kiểm tra email để thanh toán.');
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel($token)
    {
        $order = Order::where('confirmation_token', $token)
                      ->where('token_expires_at', '>', now())
                      ->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Link không hợp lệ hoặc đã hết hạn.');
        }

        if ($order->is_confirmed) {
            return redirect()->route('home')->with('error', 'Đơn hàng đã được xác nhận, không thể hủy.');
        }

        // Lấy email hợp lệ
        $email = $order->customer_email ?? $order->user?->email ?? null;
        $email = trim($email);

        // Cập nhật trạng thái hủy
        $order->order_status = 5; // Đã hủy
        $order->is_confirmed = false;
        $order->confirmation_token = null;
        $order->save();

        // Gửi email thông báo hủy (nếu có email hợp lệ)
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($email)->send(new OrderCancelled($order));
                Log::info('Email hủy đơn hàng đã gửi thành công', [
                    'order_id' => $order->id,
                    'email' => $email,
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi gửi email hủy đơn hàng', [
                    'order_id' => $order->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Không thể gửi email hủy do email không hợp lệ', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'user_email' => $order->user?->email,
            ]);
        }

        return redirect()->route('home')->with('success', 'Đơn hàng đã được hủy thành công.');
    }
}