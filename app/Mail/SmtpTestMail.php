<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $otp)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Pengaturan Email - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.smtp-test',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
