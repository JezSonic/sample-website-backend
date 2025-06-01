<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable {
    use Queueable, SerializesModels;
    private ?string $reset_password_url;
    private ?string $valid_until;

    /**
     * Create a new message instance.
     */
    public function __construct(string $reset_password_url, $valid_until) {
        $this->reset_password_url = $reset_password_url;
        $this->valid_until = $valid_until;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Reset password for your ' . config('app.name') . ' account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.reset_password',
            with: [
                'reset_password_url' => $this->reset_password_url,
                'valid_until' => $this->valid_until,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
