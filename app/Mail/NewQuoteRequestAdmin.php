<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewQuoteRequestAdmin extends Mailable
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
        return $this->subject('Yêu cầu báo giá mới - BigBag')
                    ->view('emails.new-quote-request-admin');
    }
}