<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'sales_returns';

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'user_id',
        'refund_total',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'refund_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }
}
