<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu tùy chỉnh được duyệt</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f97316; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #f97316; }
        .btn { display: inline-block; background: #f97316; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: bold; color: #4b5563; width: 40%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
    </div>
    <div class="content">
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Yêu cầu tùy chỉnh <strong>#{{ $order->order_number ?? $order->id }}</strong> của bạn đã được duyệt.</p>

        <div class="info-box">
            <h3 style="margin-top: 0;">📋 Thông tin đơn hàng</h3>
            <table>
                <tr><td class="label">Mã đơn hàng:</td><td>{{ $order->order_number ?? $order->id }}</td></tr>
                <tr><td class="label">Sản phẩm:</td><td>{{ $order->details->first()->productVariant->product->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">Số lượng:</td><td>{{ $order->details->first()->quantity ?? 1 }}</td></tr>
                <tr><td class="label">Tổng tiền:</td><td>{{ number_format($order->final_amount) }}₫</td></tr>
                <tr><td class="label">Vị trí in:</td><td>{{ $logoRequest->print_position ?? '' }}</td></tr>
                <tr><td class="label">Kích thước:</td><td>{{ $logoRequest->print_size ?? '' }}</td></tr>
            </table>
        </div>

        @if($paymentLink)
            <div style="text-align: center; margin: 25px 0;">
                <p><strong>Vui lòng thanh toán để xác nhận đơn hàng:</strong></p>
                <a href="{{ $paymentLink }}" class="btn">Thanh toán ngay</a>
                <p style="font-size: 13px; color: #555; margin-top: 10px;">Hoặc truy cập link: <a href="{{ $paymentLink }}">{{ $paymentLink }}</a></p>
            </div>
        @else
            <div style="background: #fef3c7; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 15px 0;">
                <p style="margin: 0; color: #92400e;">⚠️ Không thể tạo link thanh toán. Vui lòng liên hệ hỗ trợ.</p>
            </div>
        @endif

        <p>Chúng tôi sẽ bắt đầu sản xuất ngay sau khi nhận được thanh toán.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>