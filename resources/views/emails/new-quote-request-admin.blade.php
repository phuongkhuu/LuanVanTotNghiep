<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu báo giá mới</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e3a8a; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #1e3a8a; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
        .btn { display: inline-block; background: #1e3a8a; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Admin</h1>
    </div>
    <div class="content">
        <p>Xin chào Admin,</p>
        <p>Có một yêu cầu báo giá mới từ khách hàng.</p>

        <div class="info-box">
            <h3 style="margin-top: 0;">📋 Thông tin yêu cầu</h3>
            <p><strong>Công ty:</strong> {{ $quoteRequest->company_name }}</p>
            <p><strong>Email:</strong> {{ $quoteRequest->email }}</p>
            <p><strong>SĐT:</strong> {{ $quoteRequest->phone }}</p>
            <p><strong>Số lượng:</strong> {{ $quoteRequest->total_quantity }}</p>
            <p><strong>Tổng tiền (trước CK):</strong> {{ number_format($order->total_amount, 0, ',', '.') }}₫</p>
            @if($order->discount_amount > 0)
                <p><strong>Giảm giá:</strong> -{{ number_format($order->discount_amount, 0, ',', '.') }}₫</p>
                <p><strong>Thành tiền:</strong> {{ number_format($order->final_amount, 0, ',', '.') }}₫</p>
            @endif
            <p><strong>Ngày tạo:</strong> {{ $quoteRequest->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <p style="text-align: center;">
            <a href="{{ url('/admin/quote-requests/'.$quoteRequest->id) }}" class="btn">Xem chi tiết</a>
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>