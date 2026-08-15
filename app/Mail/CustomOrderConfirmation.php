<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Collection;

class CustomOrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $logoRequests;

    public function __construct(Order $order, Collection $logoRequests)
    {
        $this->order = $order;
        $this->logoRequests = $logoRequests;
    }

    public function build()
    {
        $displayCode = $this->order->order_number ?? '#' . $this->order->id;
        return $this->subject('Xác nhận yêu cầu tùy chỉnh #' . $displayCode . ' - BigBag')
                    ->view('emails.custom-order-confirmation');
    }
}