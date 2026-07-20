<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $notifTitle;
    public string $notifBody;
    public string $notifType;

    public function __construct(string $title, string $body, string $type = 'system')
    {
        $this->notifTitle = $title;
        $this->notifBody = $body;
        $this->notifType = $type;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notifTitle . ' – NAAP Lost & Found',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }
}
