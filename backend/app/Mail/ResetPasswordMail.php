<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

/**
 * Mailable para reset de contraseña
 * 
 * PROPÓSITO:
 * Enviar email con link para restablecer contraseña.
 */
class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Constructor
     * 
     * @param string $userName Nombre del usuario
     * @param string $resetUrl URL de reset con token
     */
    public function __construct(
        public string $userName,
        public string $resetUrl
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer Contraseña - Finan Core',
            from: new Address('noreply@finan-core.com', 'Finan Core'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'userName' => $this->userName,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}