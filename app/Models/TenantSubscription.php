<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_type',
        'amount',
        'start_date',
        'end_date',
        'status',
        'last_payment_date',
        'next_payment_date',
        'payment_history',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_payment_date' => 'date',
            'next_payment_date' => 'date',
            'payment_history' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'activa';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expirada' || ($this->end_date && $this->end_date->isPast() && $this->status !== 'cancelada');
    }

    public function markAsPaid(float $amount, string $reference = null): void
    {
        $history = $this->payment_history ?? [];
        $history[] = [
            'date' => now()->toDateString(),
            'amount' => $amount,
            'reference' => $reference,
        ];

        $this->update([
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => $this->plan_type === 'anual' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString(),
            'status' => 'activa',
            'payment_history' => $history,
        ]);
    }
}
