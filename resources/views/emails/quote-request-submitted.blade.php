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
        .confirm-box { background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #22c55e; }
        .btn-confirm { display: inline-block; background: #f97316; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .btn-cancel { display: inline-block; background: #e11d48; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; }
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
            <h3 style="margin-top: 0;">Thông tin yêu cầu</h3>
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

        <!-- ============ PHẦN XÁC NHẬN ĐƠN HÀNG ============ -->
        @if(!empty($order->confirmation_token) && !$order->is_confirmed)
            <div class="confirm-box">
                <p style="font-weight: bold; margin-bottom: 10px;">Xác nhận đơn hàng</p>
                <p>Vui lòng nhấn vào một trong các nút bên dưới để xác nhận hoặc hủy đơn hàng:</p>
                <p style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 15px;">
                    <a href="{{ route('order.confirm', ['token' => $order->confirmation_token]) }}" 
                       class="btn-confirm">
                        Xác nhận đơn hàng
                    </a>
                    <a href="{{ route('order.cancel', ['token' => $order->confirmation_token]) }}" 
                       class="btn-cancel">
                        Hủy đơn hàng
                    </a>
                </p>
                <p style="font-size: 13px; color: #555; margin-top: 12px;">Link có hiệu lực trong 7 ngày. Nếu bạn không thực hiện, đơn hàng sẽ tự động hết hạn.</p>
            </div>
        @endif

        <p>Chúng tôi sẽ liên hệ lại với bạn qua email hoặc số điện thoại <strong>{{ $quoteRequest->phone }}</strong>.</p>
        <p>Trân trọng,<br>Đội ngũ BigBag</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>