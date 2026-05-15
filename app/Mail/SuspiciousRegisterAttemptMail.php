<?php

namespace App\Mail;

use App\Models\PendingUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuspiciousRegisterAttemptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(PendingUser $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Suspicious Registration Attempt');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.suspicious-register',
            with: ['user' => $this->user]
        );
    }

    public function attachments(): array { return []; }
}