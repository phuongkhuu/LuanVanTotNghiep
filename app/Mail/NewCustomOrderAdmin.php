<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Collection;

class NewCustomOrderAdmin extends Mailable
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
        return $this->subject('[Admin] Yêu cầu tùy chỉnh mới #' . ($this->order->order_number ?? $this->order->id))
                    ->view('emails.new-custom-order-admin');
    }
}