<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $quoteRequest;
    public $order;

    public function __construct(QuoteRequest $quoteRequest, Order $order)
    {
        $this->quoteRequest = $quoteRequest;
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Xác nhận yêu cầu báo giá - BigBag')
                    ->view('emails.quote-request-submitted');
    }
}