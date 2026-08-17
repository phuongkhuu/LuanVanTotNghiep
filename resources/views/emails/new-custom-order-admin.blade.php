<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu tùy chỉnh mới</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e3a8a; padding: 20px; text-align: center; color: #fff; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background: #fff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #1e3a8a; }
        .logo-box { background: #f0fdf4; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #22c55e; margin: 10px 0; }
        .logo-image { max-width: 120px; max-height: 80px; border: 1px solid #e5e7eb; border-radius: 4px; padding: 4px; background: white; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
        .btn { display: inline-block; background: #1e3a8a; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BigBag Admin</h1>
    </div>
    <div class="content">
        <p>Xin chào Admin,</p>
        <p>Có một yêu cầu tùy chỉnh mới từ khách hàng.</p>

        <div class="info-box">
            <h3 style="margin-top: 0;">📋 Thông tin khách hàng</h3>
            <p><strong>Họ tên:</strong> {{ $order->customer_name }}</p>
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p><strong>SĐT:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Mã đơn:</strong> {{ $order->order_number ?? '#' . $order->id }}</p>
            <p><strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <h3>🖌️ Chi tiết yêu cầu tùy chỉnh</h3>
        @foreach($logoRequests as $logo)
            <div class="logo-box">
                <p><strong>Vị trí in:</strong> {{ $logo->print_position }}</p>
                <p><strong>Kích thước:</strong> {{ $logo->print_size }}</p>
                <p><strong>Ghi chú:</strong> {{ $logo->note ?? 'Không có' }}</p>
                @if($logo->logo_image)
                    <p><strong>File logo:</strong></p>
                    <img src="{{ asset('storage/' . $logo->logo_image) }}" alt="Logo thiết kế" class="logo-image">
                    <p style="margin-top: 4px;"><a href="{{ asset('storage/' . $logo->logo_image) }}" target="_blank">Xem file</a></p>
                @endif
            </div>
        @endforeach

        <h3>📦 Sản phẩm</h3>
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-right">Đơn giá</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $detail)
                    <tr>
                        <td>{{ optional($detail->productVariant->product)->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}₫</td>
                        <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}₫</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Tổng cộng</strong></td>
                    <td class="text-right"><strong>{{ number_format($order->final_amount, 0, ',', '.') }}₫</strong></td>
                </tr>
            </tfoot>
        </table>

        <p style="text-align: center; margin-top: 20px;">
            <a href="{{ url('/admin/orders/' . $order->id) }}" class="btn">Xem đơn hàng</a>
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
    </div>
</body>
</html>