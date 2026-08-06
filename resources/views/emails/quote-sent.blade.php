<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BigBag - Báo giá tùy chỉnh</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f97316; padding: 20px; text-align: center; color: white; }
        .content { padding: 20px; background: #f9fafb; }
        .quote-box { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BigBag.vn</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $customerName }}</strong>,</p>
            <p>Chúng tôi xin gửi báo giá cho yêu cầu tùy chỉnh của bạn:</p>
            
            <div class="quote-box">
                <p><strong>Sản phẩm:</strong> {{ $product }}</p>
                <p><strong>Số lượng:</strong> {{ $quantity }}</p>
                @if($designDescription)
                    <p><strong>Mô tả:</strong> {{ $designDescription }}</p>
                @endif
                <p><strong>Giá dự kiến:</strong> <span style="color: #f97316; font-size: 20px; font-weight: bold;">{{ number_format($estimatedPrice, 0, ',', '.') }}₫</span></p>
                @if($estimatedTime)
                    <p><strong>Thời gian dự kiến:</strong> {{ $estimatedTime }}</p>
                @endif
            </div>

            <p>Vui lòng liên hệ với chúng tôi để xác nhận hoặc điều chỉnh báo giá.</p>
            <p>Hotline: <strong>1900 1234</strong></p>
            <p>Email: <strong>b2b@bigbag.vn</strong></p>
            <p>Trân trọng,<br>Đội ngũ BigBag</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
        </div>
    </div>
</body>
</html>