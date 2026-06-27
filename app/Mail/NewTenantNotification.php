<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTenantNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public User $admin;

    public function __construct(Tenant $tenant, User $admin)
    {
        $this->tenant = $tenant;
        $this->admin = $admin;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo registro: {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.new-tenant',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
