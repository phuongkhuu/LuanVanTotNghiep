<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BigBag - Cập nhật trạng thái yêu cầu tùy chỉnh</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f97316; padding: 20px; text-align: center; color: white; }
        .content { padding: 20px; background: #f9fafb; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BigBag.vn</h1>
        </div>
        <div class="content">
            <p>Xin chào,</p>
            <p>Yêu cầu tùy chỉnh của bạn đã được cập nhật trạng thái: <strong>{{ $status }}</strong></p>
            @if($feedback)
                <p><strong>Phản hồi từ chúng tôi:</strong></p>
                <p style="background: #fff; padding: 10px; border-left: 3px solid #f97316;">{{ $feedback }}</p>
            @endif
            <p>Cảm ơn bạn đã sử dụng dịch vụ của BigBag.</p>
            <p>Trân trọng,<br>Đội ngũ BigBag</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BigBag.vn. All rights reserved.
        </div>
    </div>
</body>
</html>