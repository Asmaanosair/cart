<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $salesData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Daily Sales Report - {$this->salesData['date']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-sales',
        );
    }
}
