<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderDetails;
    public $displayCode;
    public $customerEmail;
    public $logoRequests; // Thêm property

    public function __construct($order, $orderDetails, $displayCode, $logoRequests = [])
    {
        $this->order = $order;
        $this->orderDetails = $orderDetails;
        $this->displayCode = $displayCode;
        $this->logoRequests = $logoRequests;
        
        $this->customerEmail = $order->customer_email ?? $order->user?->email ?? 'N/A';
        
        \Illuminate\Support\Facades\Log::info('OrderConfirmationMail constructed', [
            'order_id' => $order->id ?? null,
            'displayCode' => $displayCode,
            'details_count' => count($orderDetails),
            'logo_count' => count($logoRequests),
            'customer_email' => $this->customerEmail
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đơn hàng #' . $this->displayCode . ' - BigBag Premium Utility Carry Gear',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}