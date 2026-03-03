<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
