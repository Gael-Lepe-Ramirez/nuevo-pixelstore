<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Variable pública para que la vista pueda acceder a ella

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibo de tu compra en PixelStore',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}