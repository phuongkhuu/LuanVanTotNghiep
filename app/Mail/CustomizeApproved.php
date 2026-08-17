<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\LogoPrintRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomizeApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $logoRequest;

    public function __construct(Order $order, LogoPrintRequest $logoRequest)
    {
        $this->order = $order;
        $this->logoRequest = $logoRequest;
    }

    public function build()
    {
        return $this->subject('Yêu cầu tùy chỉnh #' . ($this->order->order_number ?? $this->order->id) . ' đã được duyệt')
                    ->view('emails.customize-approved');
    }
}