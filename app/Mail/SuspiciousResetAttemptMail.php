<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuspiciousResetAttemptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Suspicious Reset Attempt');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.suspicious-reset',
            with: ['user' => $this->user]
        );
    }

    public function attachments(): array { return []; }
}