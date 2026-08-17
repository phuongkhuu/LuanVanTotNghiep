<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu tùy chỉnh bị từ chối</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #e11d48; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #e11d48; }
        .reason-box { background: #fef2f2; padding: 12px 15px; border-radius: 8px; border: 1px solid #fecaca; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
    </div>
    <div class="content">
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Yêu cầu tùy chỉnh <strong>#{{ $order->order_number ?? $order->id }}</strong> của bạn đã bị từ chối.</p>

        <div class="reason-box">
            <p><strong>Lý do từ chối:</strong></p>
            <p style="color: #991b1b;">{{ $reason }}</p>
        </div>

        <div class="info-box">
            <h4 style="margin-top: 0;">📋 Thông tin yêu cầu</h4>
            <p><strong>Mã đơn hàng:</strong> {{ $order->order_number ?? $order->id }}</p>
            <p><strong>Sản phẩm:</strong> {{ $order->details->first()->productVariant->product->name ?? 'N/A' }}</p>
            <p><strong>Số lượng:</strong> {{ $order->details->first()->quantity ?? 1 }}</p>
        </div>

        <p>Nếu bạn có thắc mắc, hãy liên hệ với chúng tôi qua email hoặc số điện thoại hỗ trợ.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>