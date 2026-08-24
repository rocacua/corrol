<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMailing extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $messageBody;
    public User $user;

    public function __construct(string $subjectText, string $messageBody, User $user)
    {
        $this->subjectText = $subjectText;
        $this->messageBody = $messageBody;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectText);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_mailing',
            with: [
                'user' => $this->user,
                'messageBody' => $this->messageBody,
            ]
        );
    }
}