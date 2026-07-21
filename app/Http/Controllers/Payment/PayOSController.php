<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use PayOS\PayOS;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            clientId: env('PAYOS_CLIENT_ID'),
            apiKey: env('PAYOS_API_KEY'),
            checksumKey: env('PAYOS_CHECKSUM_KEY')
        );
    }

    /**
     * Tạo link thanh toán PayOS và chuyển hướng khách hàng (dùng cho PayOS)
     */
    public function createPayment($orderId)
    {
        $order = Order::with('details')->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($order->order_status == 2) {
            return redirect()->route('checkout.success')->with('info', 'Đơn hàng đã được thanh toán.');
        }

        $items = $this->buildItems($order);

        $paymentData = [
            'orderCode'   => $order->id,
            'amount'      => (int) $order->final_amount,
            'description' => 'Thanh toán đơn hàng #' . $order->id,
            'items'       => $items,
            'cancelUrl'   => env('PAYOS_CANCEL_URL'),
            'returnUrl'   => env('PAYOS_RETURN_URL'),
        ];

        try {
            $response = $this->payOS->paymentRequests->create($paymentData);

            // Sửa: $response là object, dùng -> thay vì []
            $orderCode = $response->orderCode;
            $checkoutUrl = $response->checkoutUrl;

            $this->updateOrCreatePayment($order, $orderCode, 'payos', 'pending');

            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            \Log::error('PayOS createPayment error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Không thể tạo link thanh toán. Vui lòng thử lại sau.']);
        }
    }

    /**
     * Tạo link thanh toán PayOS và trả về JSON (dùng cho bank_transfer)
     */
    public function getPaymentLink($orderId)
    {
        $order = Order::with('details')->find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->order_status == 2) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng đã được thanh toán'], 400);
        }

        $items = $this->buildItems($order);

        $paymentData = [
            'orderCode'   => $order->id,
            'amount'      => (int) $order->final_amount,
            'description' => 'Thanh toán đơn hàng #' . $order->id,
            'items'       => $items,
            'cancelUrl'   => env('PAYOS_CANCEL_URL'),
            'returnUrl'   => env('PAYOS_RETURN_URL'),
        ];

        try {
            $response = $this->payOS->paymentRequests->create($paymentData);

            $orderCode = $response->orderCode;
            $checkoutUrl = $response->checkoutUrl;

            $this->updateOrCreatePayment($order, $orderCode, 'bank_transfer', 'pending');

            return response()->json([
                'success'      => true,
                'checkout_url' => $checkoutUrl,
                'order_code'   => $orderCode,
            ]);
        } catch (\Exception $e) {
            \Log::error('PayOS getPaymentLink error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook nhận callback từ PayOS
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        try {
            $verified = $this->payOS->webhooks->verify($payload);

            if (!$verified) {
                \Log::warning('PayOS webhook: Invalid signature', $payload);
                return response('Invalid signature', 400);
            }

            $orderCode = $payload['data']['orderCode'] ?? null;
            $status = $payload['data']['status'] ?? null;

            if ($status === 'PAID' && $orderCode) {
                $order = Order::find($orderCode);

                if ($order && $order->order_status == 0) {
                    $payment = Payment::where('order_id', $order->id)->first();
                    if ($payment) {
                        $payment->status = 'success';
                        $payment->payment_date = now();
                        $payment->save();
                    }

                    $order->order_status = 2;
                    $order->save();

                    \Log::info("PayOS webhook: Đơn hàng #{$order->id} đã thanh toán thành công.");
                }
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            \Log::error('PayOS webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }
    }

    /**
     * Redirect sau khi thanh toán thành công (từ returnUrl)
     */
    public function success(Request $request)
    {
        $orderCode = $request->query('orderCode');
        $status = $request->query('status');

        if ($status === 'PAID' && $orderCode) {
            $order = Order::find($orderCode);
            if ($order && $order->order_status == 0) {
                $payment = Payment::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->status = 'success';
                    $payment->payment_date = now();
                    $payment->save();
                }
                $order->order_status = 2;
                $order->save();
            }

            return redirect()->route('checkout.success')->with('success', 'Thanh toán thành công!');
        }

        return redirect()->route('checkout.index')->with('error', 'Thanh toán thất bại hoặc bị hủy.');
    }

    /**
     * Redirect khi người dùng hủy thanh toán
     */
    public function cancel(Request $request)
    {
        return redirect()->route('checkout.index')->with('error', 'Bạn đã hủy thanh toán.');
    }

    // ============ PRIVATE HELPERS ============

    /**
     * Xây dựng danh sách sản phẩm cho PayOS
     */
    private function buildItems($order)
    {
        $items = [];
        foreach ($order->details as $detail) {
            $productName = $detail->productVariant->product->name ?? 'Sản phẩm';
            $variantName = '';
            if ($detail->productVariant->color) {
                $variantName .= $detail->productVariant->color->name;
            }
            if ($detail->productVariant->size_name) {
                $variantName .= ' - ' . $detail->productVariant->size_name;
            }
            if ($variantName) {
                $productName .= ' (' . $variantName . ')';
            }

            $items[] = [
                'name'     => $productName,
                'quantity' => $detail->quantity,
                'price'    => (int) $detail->unit_price,
            ];
        }
        return $items;
    }

    /**
     * Tạo hoặc cập nhật bản ghi Payment
     */
    private function updateOrCreatePayment($order, $transactionCode, $method, $status)
    {
        $payment = Payment::where('order_id', $order->id)->first();
        if ($payment) {
            $payment->transaction_code = (string) $transactionCode;
            $payment->payment_method = $method;
            $payment->status = $status;
            $payment->save();
        } else {
            Payment::create([
                'order_id'          => $order->id,
                'transaction_code'  => (string) $transactionCode,
                'payment_method'    => $method,
                'amount'            => $order->final_amount,
                'payment_date'      => now(),
                'status'            => $status,
            ]);
        }
    }
}