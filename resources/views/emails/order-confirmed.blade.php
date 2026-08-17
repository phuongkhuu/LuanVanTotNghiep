<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f97316; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background: #f97316; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
    </div>
    <div class="content">
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Đơn hàng <strong>#{{ $order->order_number ?? $order->id }}</strong> của bạn đã được xác nhận.</p>

        @if($paymentLink)
            <p>Vui lòng hoàn tất thanh toán bằng cách nhấn nút bên dưới:</p>
            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ $paymentLink }}" class="btn">Thanh toán ngay</a>
            </p>
            <p style="font-size: 14px; color: #555;">Hoặc truy cập link: <a href="{{ $paymentLink }}">{{ $paymentLink }}</a></p>
        @else
            <p style="color: #e11d48;">⚠️ Chúng tôi không thể tạo link thanh toán. Vui lòng liên hệ hỗ trợ.</p>
        @endif

        <p>Nếu bạn có thắc mắc, hãy trả lời email này hoặc gọi hotline 1900xxxx.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>