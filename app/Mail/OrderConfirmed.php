<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $paymentLink;

    public function __construct(Order $order, $paymentLink = null)
    {
        $this->order = $order;
        $this->paymentLink = $paymentLink;
    }

    public function build()
    {
        return $this->subject('[BigBag] Xác nhận đơn hàng #' . ($this->order->order_number ?? $this->order->id))
                    ->view('emails.order-confirmed');
    }
}