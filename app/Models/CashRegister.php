<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opening_amount',
        'closing_amount',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashRegisterMovement::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'abierta';
    }

    public function getTotalCashSales(): float
    {
        return $this->sales()->where('payment_method', 'efectivo')->sum('total');
    }

    public function getTotalCardSales(): float
    {
        return $this->sales()->where('payment_method', 'tarjeta_transferencia')->sum('total');
    }

    public function getTotalWithdrawals(): float
    {
        return $this->movements()->sum('amount');
    }

    public function getExpectedCash(): float
    {
        return $this->opening_amount + $this->getTotalCashSales() - $this->getTotalWithdrawals();
    }
}
