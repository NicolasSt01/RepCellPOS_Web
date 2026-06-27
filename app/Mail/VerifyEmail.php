<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Tenant $tenant;

    public function __construct(User $user, Tenant $tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Verifica tu correo electrónico - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.verify-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
