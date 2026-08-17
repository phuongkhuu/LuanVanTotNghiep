<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\LogoPrintRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomizeRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $logoRequest;
    public $reason;

    public function __construct(Order $order, LogoPrintRequest $logoRequest, $reason)
    {
        $this->order = $order;
        $this->logoRequest = $logoRequest;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Yêu cầu tùy chỉnh #' . ($this->order->order_number ?? $this->order->id) . ' đã bị từ chối')
                    ->view('emails.customize-rejected');
    }
}