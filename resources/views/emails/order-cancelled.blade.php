<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng đã hủy</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #e11d48; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
    </div>
    <div class="content">
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Đơn hàng <strong>#{{ $order->order_number ?? $order->id }}</strong> đã được <strong style="color: #e11d48;">hủy</strong> thành công.</p>
        <p>Nếu bạn có thắc mắc, vui lòng liên hệ với chúng tôi qua email hoặc số điện thoại hỗ trợ.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>