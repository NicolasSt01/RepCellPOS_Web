<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'status',
        'subtotal',
        'tax_total',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($this->quoteItems as $item) {
            $subtotal += $item->subtotal;
            $taxTotal += $item->subtotal * ($item->tax_percentage / 100);
        }

        $this->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
        ]);
    }

    public function approve(): void
    {
        $this->update(['status' => 'aprobada']);
        $this->workOrder->update(['status' => 'cotizacion_aprobada']);
        $this->workOrder->addTimelineEvent('cotizacion_aprobada', auth()->user()->name, 'Cotización aprobada por el cliente');
    }

    public function reject(?string $reason = null): void
    {
        $this->update(['status' => 'rechazada']);
        $this->workOrder->update(['status' => 'cancelada']);
        $this->workOrder->addTimelineEvent('cancelada', auth()->user()->name, 'Cotización rechazada. ' . ($reason ?? ''));
    }
}
