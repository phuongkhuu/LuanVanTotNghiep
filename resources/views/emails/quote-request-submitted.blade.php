<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận yêu cầu báo giá</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f97316; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .highlight { color: #f97316; font-weight: bold; }
        .info-box { background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #f97316; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Premium Utility Carry Gear</h1>
    </div>
    <div class="content">
        <p>Xin chào <strong>{{ $quoteRequest->company_name }}</strong>,</p>
        <p>Cảm ơn bạn đã gửi yêu cầu báo giá tại BigBag. Chúng tôi đã nhận được yêu cầu của bạn và sẽ phản hồi trong thời gian sớm nhất.</p>

        <div class="info-box">
            <h3 style="margin-top: 0;">📋 Thông tin yêu cầu</h3>
            <p><strong>Mã yêu cầu:</strong> #{{ $quoteRequest->id }}</p>
            @php
                $firstDetail = $order->details->first();
                $productName = $firstDetail ? optional($firstDetail->productVariant)->product->name ?? 'N/A' : 'N/A';
            @endphp
            <p><strong>Sản phẩm:</strong> {{ $productName }}</p>
            <p><strong>Số lượng:</strong> {{ $quoteRequest->total_quantity }}</p>
            <p><strong>Tổng tiền (trước chiết khấu):</strong> {{ number_format($order->total_amount, 0, ',', '.') }}₫</p>
            @if($order->discount_amount > 0)
                <p><strong>Giảm giá:</strong> -{{ number_format($order->discount_amount, 0, ',', '.') }}₫</p>
                <p><strong>Thành tiền:</strong> <span class="highlight">{{ number_format($order->final_amount, 0, ',', '.') }}₫</span></p>
            @else
                <p><strong>Thành tiền:</strong> {{ number_format($order->final_amount, 0, ',', '.') }}₫</p>
            @endif
        </div>

        <p>Chúng tôi sẽ liên hệ lại với bạn qua email hoặc số điện thoại <strong>{{ $quoteRequest->phone }}</strong>.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>