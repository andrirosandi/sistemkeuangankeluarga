<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $messageStr;
    public $actionUrl;

    public function __construct(string $messageStr, ?string $actionUrl = null)
    {
        $this->messageStr = $messageStr;
        $this->actionUrl = $actionUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Sistem - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
