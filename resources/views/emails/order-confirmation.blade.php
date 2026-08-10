<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #1a56db;
        }
        .header h1 {
            color: #1a56db;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
        }
        .content {
            padding: 20px 0;
        }
        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .order-info table {
            width: 100%;
        }
        .order-info td {
            padding: 5px 0;
        }
        .order-info .label {
            color: #666;
            width: 40%;
        }
        .order-info .value {
            font-weight: 500;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .items-table th {
            background: #f8f9fa;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            border-top: 2px solid #ddd;
        }
        .total-row td {
            padding: 10px;
        }
        .total-amount {
            color: #1a56db;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-shipping {
            background: #f3e8ff;
            color: #6b21a8;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .footer a {
            color: #1a56db;
            text-decoration: none;
        }
        .highlight {
            color: #1a56db;
            font-weight: bold;
        }
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
        .payment-info {
            background: #f0f9ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid #1a56db;
        }
        .payment-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
        <p>Xác nhận đơn hàng #{{ $displayCode }}</p>
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Cảm ơn bạn đã đặt hàng tại <strong>BigBag Premium Utility Carry Gear</strong>. Đơn hàng của bạn đã được xác nhận và đang được xử lý.</p>

        <div class="order-info">
            <h3 style="margin-top: 0;">📋 Thông tin đơn hàng</h3>
            <table>
                <tr>
                    <td class="label">Mã đơn hàng:</td>
                    <td class="value"><strong>{{ $displayCode }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Ngày đặt:</td>
                    <td class="value">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Trạng thái:</td>
                    <td class="value">
                        <span class="status-badge status-{{ $order->order_status == 0 ? 'pending' : ($order->order_status == 1 ? 'processing' : ($order->order_status == 2 ? 'shipping' : ($order->order_status == 3 ? 'completed' : 'cancelled'))) }}">
                            {{ $order->order_status == 0 ? 'Chờ xử lý' : ($order->order_status == 1 ? 'Đang xử lý' : ($order->order_status == 2 ? 'Đang giao' : ($order->order_status == 3 ? 'Hoàn thành' : 'Đã hủy'))) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Phương thức thanh toán:</td>
                    <td class="value">
                        @if($order->payment)
                            {{ $order->payment->payment_method == 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 
                               ($order->payment->payment_method == 'bank_transfer' ? 'Chuyển khoản ngân hàng' : 
                               ($order->payment->payment_method == 'vnpay' ? 'VNPay' : 
                               ($order->payment->payment_method == 'momo' ? 'MoMo' : 'Ví điện tử'))) }}
                        @else
                            Chưa xác định
                        @endif
                    </td>
                </tr>
                @if($order->order_code === 'wholesale')
                <tr>
                    <td class="label">Loại đơn:</td>
                    <td class="value"><span style="color: #1a56db; font-weight: bold;">Đơn hàng sỉ</span></td>
                </tr>
                <tr>
                    <td class="label">Tiền cọc (50%):</td>
                    <td class="value">{{ number_format($order->deposit_amount ?? 0, 0, ',', '.') }}₫</td>
                </tr>
                @endif
                @if($order->order_code === 'preorder')
                <tr>
                    <td class="label">Loại đơn:</td>
                    <td class="value"><span style="color: #f59e0b; font-weight: bold;">Pre-order</span></td>
                </tr>
                @endif
            </table>
        </div>

        <h3>📦 Chi tiết đơn hàng</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center">SL</th>
                    <th class="text-right">Đơn giá</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderDetails as $item)
                <tr>
                    <td>
                        <strong>{{ $item['name'] }}</strong>
                        @if(isset($item['color']) && $item['color'])
                        <br><small style="color: #666;">Màu: {{ $item['color'] }}</small>
                        @endif
                        @if(isset($item['size']) && $item['size'])
                        <small style="color: #666;"> | Size: {{ $item['size'] }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 0, ',', '.') }}₫</td>
                    <td class="text-right">{{ number_format($item['subtotal'], 0, ',', '.') }}₫</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Tạm tính</strong></td>
                    <td class="text-right">{{ number_format($order->total_amount, 0, ',', '.') }}₫</td>
                </tr>
                @if($order->shipping_fee > 0)
                <tr>
                    <td colspan="3" class="text-right">Phí vận chuyển</td>
                    <td class="text-right">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</td>
                </tr>
                @endif
                @if($order->discount_amount > 0)
                <tr>
                    <td colspan="3" class="text-right" style="color: #dc2626;">Giảm giá</td>
                    <td class="text-right" style="color: #dc2626;">-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>Tổng cộng</strong></td>
                    <td class="text-right total-amount"><strong>{{ number_format($order->final_amount, 0, ',', '.') }}₫</strong></td>
                </tr>
            </tfoot>
        </table>

        <hr class="divider">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">👤 Thông tin người đặt</h4>
                <p style="margin: 5px 0;"><strong>Họ tên:</strong> {{ $order->customer_name }}</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> {{ $customerEmail ?? $order->customer_email ?? $order->user?->email ?? 'N/A' }}</p>
                <p style="margin: 5px 0;"><strong>SĐT:</strong> {{ $order->customer_phone }}</p>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">🚚 Thông tin người nhận</h4>
                <p style="margin: 5px 0;"><strong>Họ tên:</strong> {{ $order->receiver_name }}</p>
                <p style="margin: 5px 0;"><strong>SĐT:</strong> {{ $order->receiver_phone }}</p>
                <p style="margin: 5px 0;"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
            </div>
        </div>

        <!-- Thông tin thanh toán chi tiết -->
        @if($order->payment)
        <div class="payment-info">
            <h4 style="margin-top: 0; color: #1a56db;">💳 Thông tin thanh toán</h4>
            <p><strong>Phương thức:</strong> 
                {{ $order->payment->payment_method == 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 
                   ($order->payment->payment_method == 'bank_transfer' ? 'Chuyển khoản ngân hàng' : 
                   ($order->payment->payment_method == 'vnpay' ? 'VNPay' : 
                   ($order->payment->payment_method == 'momo' ? 'MoMo' : 'Ví điện tử'))) }}
            </p>
            <p><strong>Trạng thái:</strong> 
                @if($order->payment->status == 'pending')
                    <span style="color: #f59e0b;">Chờ thanh toán</span>
                @elseif($order->payment->status == 'paid' || $order->payment->status == 'success')
                    <span style="color: #10b981;">Đã thanh toán</span>
                @elseif($order->payment->status == 'failed')
                    <span style="color: #ef4444;">Thất bại</span>
                @else
                    <span style="color: #6b7280;">{{ $order->payment->status }}</span>
                @endif
            </p>
            @if($order->payment->transaction_code)
            <p><strong>Mã giao dịch:</strong> {{ $order->payment->transaction_code }}</p>
            @endif
        </div>
        @endif

        @if($order->note)
        <div style="background: #fef3c7; padding: 12px 15px; border-radius: 8px; margin-top: 15px;">
            <p style="margin: 0; color: #92400e;"><strong>📝 Ghi chú:</strong> {{ $order->note }}</p>
        </div>
        @endif

        @if($order->order_code === 'wholesale' && isset($order->deposit_amount))
        <div style="background: #dbeafe; padding: 12px 15px; border-radius: 8px; margin-top: 15px;">
            <p style="margin: 0; color: #1e40af;">
                <strong>💳 Lưu ý:</strong> Đây là đơn hàng sỉ. Quý khách vui lòng thanh toán 
                <strong>{{ number_format($order->deposit_amount, 0, ',', '.') }}₫</strong> 
                (50% giá trị đơn hàng) để xác nhận đặt hàng.
                <br>Số tiền còn lại: <strong>{{ number_format($order->remaining_amount ?? 0, 0, ',', '.') }}₫</strong> sẽ thanh toán khi nhận hàng.
            </p>
        </div>
        @endif

        <div style="background: #ecfdf5; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #065f46;">✅ Chính sách hỗ trợ</h4>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li>📞 Hotline hỗ trợ: <strong>1900 1234</strong></li>
                <li>📧 Email: <a href="mailto:support@bigbag.vn" style="color: #065f46;">support@bigbag.vn</a></li>
                <li>🔄 Đổi trả trong vòng 90 ngày</li>
                <li>🔒 Bảo hành trọn đời</li>
            </ul>
        </div>

        <p style="text-align: center; margin: 20px 0;">
            <a href="{{ route('orders.history') }}" style="display: inline-block; padding: 12px 30px; background: #1a56db; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                Xem lịch sử đơn hàng
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Cảm ơn bạn đã tin tưởng và mua hàng tại <strong>BigBag Premium Utility Carry Gear</strong>!</p>
        <p style="margin: 5px 0;">
            <a href="{{ route('home') }}">BigBag.vn</a> | 
            <a href="#">Điều khoản dịch vụ</a> | 
            <a href="#">Chính sách bảo mật</a>
        </p>
        <p style="margin-top: 10px; color: #999; font-size: 11px;">
            Email này được gửi tự động từ hệ thống BigBag. Vui lòng không trả lời email này.
        </p>
    </div>
</body>
</html>