<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInstitutionRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $institutionUser;
    public string $npsn;
    public string $dashboardUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $institutionUser, string $npsn)
    {
        $this->institutionUser = $institutionUser;
        $this->npsn = $npsn;
        $this->dashboardUrl = url('/dashboard');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Pendaftaran Institusi Baru Menunggu Verifikasi: ' . $this->institutionUser->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_institution',
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
