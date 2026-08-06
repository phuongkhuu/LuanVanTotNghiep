<?php

namespace App\Mail;

use App\Models\LogoPrintRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LogoRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $logoRequest;
    public $feedback;

    public function __construct(LogoPrintRequest $logoRequest, $feedback = null)
    {
        $this->logoRequest = $logoRequest;
        $this->feedback = $feedback;
    }

    public function build()
    {
        $statusText = [
            'pending'   => 'đang chờ xử lý',
            'approved'  => 'đã được duyệt',
            'rejected'  => 'đã bị từ chối',
            'processing'=> 'đang sản xuất',
            'completed' => 'đã hoàn thành',
        ][$this->logoRequest->status] ?? $this->logoRequest->status;

        return $this->subject('Cập nhật trạng thái yêu cầu tùy chỉnh #' . $this->logoRequest->id)
                    ->view('emails.logo-status-updated')
                    ->with([
                        'status' => $statusText,
                        'feedback' => $this->feedback,
                    ]);
    }
}