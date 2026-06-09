<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesReturnItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'sales_return_items';

    protected $fillable = [
        'tenant_id',
        'sales_return_id',
        'sale_item_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'refund_subtotal',
        'restock',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'refund_subtotal' => 'decimal:2',
            'restock' => 'boolean',
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function wasteRecord(): HasOne
    {
        return $this->hasOne(WasteRecord::class, 'sales_return_item_id');
    }
}
