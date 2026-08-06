<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $msgSubject;
    public string $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $email, string $msgSubject, string $messageText)
    {
        $this->name = $name;
        $this->email = $email;
        $this->msgSubject = $msgSubject;
        $this->messageText = $messageText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✉️ Pesan Baru dari Kontak Landing Page: ' . $this->msgSubject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->email, $this->name)
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_support',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
