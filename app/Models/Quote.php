<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['pendiente', 'enviada', 'aprobada']);
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

    public function reserveStock(): void
    {
        foreach ($this->quoteItems as $item) {
            if ($item->product_id && $item->product) {
                $item->product->increment('reserved_stock', $item->quantity);
            }
        }
    }

    public function releaseStock(): void
    {
        foreach ($this->quoteItems as $item) {
            if ($item->product_id && $item->product) {
                $item->product->decrement('reserved_stock', $item->quantity);
            }
        }
    }

    public function consumeStock(): void
    {
        foreach ($this->quoteItems as $item) {
            if ($item->product_id && $item->product) {
                $product = $item->product;
                $product->adjustStock(
                    $item->quantity,
                    'salida',
                    "Cotización #{$this->id} — OT {$this->workOrder->work_order_number}",
                    $this
                );
                $product->decrement('reserved_stock', $item->quantity);
            }
        }
    }

    public function approve(): void
    {
        DB::transaction(function () {
            $this->refresh();

            foreach ($this->quoteItems as $item) {
                if ($item->product_id && $item->product) {
                    if ($item->product->availableStock() < $item->quantity) {
                        throw new \RuntimeException(
                            "Stock insuficiente para {$item->product->name}. Disponible: {$item->product->availableStock()}, requerido: {$item->quantity}."
                        );
                    }
                }
            }

            $this->consumeStock();

            $this->update(['status' => 'aprobada']);
            $this->workOrder->update(['status' => 'cotizacion_aprobada']);
            $this->workOrder->addTimelineEvent('cotizacion_aprobada', auth()->user()->name, 'Cotización aprobada por el cliente');
        });
    }

    public function reject(?string $reason = null): void
    {
        DB::transaction(function () use ($reason) {
            $this->releaseStock();
            $this->update(['status' => 'rechazada', 'cancellation_reason' => $reason]);
            $this->workOrder->update(['status' => 'cancelada']);
            $this->workOrder->addTimelineEvent('cancelada', auth()->user()->name, 'Cotización rechazada. ' . ($reason ?? ''));
        });
    }

    public function cancel(string $reason): void
    {
        DB::transaction(function () use ($reason) {
            $this->releaseStock();
            $this->update(['status' => 'rechazada', 'cancellation_reason' => $reason]);
            $this->workOrder->addTimelineEvent('cancelada', auth()->user()->name, $reason);
            $this->workOrder->update(['status' => 'cancelada']);
        });
    }
}
