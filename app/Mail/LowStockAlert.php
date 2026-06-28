<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $products;
    public Tenant $tenant;
    public User $admin;

    public function __construct(Collection $products, Tenant $tenant, User $admin)
    {
        $this->products = $products;
        $this->tenant = $tenant;
        $this->admin = $admin;
    }

    public function envelope(): Envelope
    {
        $count = $this->products->count();
        $subject = $count === 1
            ? "⚠️ Stock Bajo: {$this->products->first()->name} — {$this->tenant->name}"
            : "⚠️ {$count} productos con stock bajo — {$this->tenant->name}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inventory.low-stock');
    }

    public function attachments(): array
    {
        return [];
    }
}
