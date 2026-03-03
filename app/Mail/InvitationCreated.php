<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public string $acceptUrl;

    public string $appName;

    public function __construct(
        public Invitation $invitation
    ) {
        $this->appName = config('app.name', 'CarnetPro');

        // On construit une URL absolue depuis APP_URL pour éviter tout host codé en dur.
        $this->acceptUrl = rtrim((string) config('app.url'), '/').route('invitations.show', $invitation->token, false);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->appName}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation-created'
        );
    }
}
