<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'waste_records';

    protected $fillable = [
        'tenant_id',
        'sales_return_item_id',
        'product_id',
        'quantity',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function returnItem(): BelongsTo
    {
        return $this->belongsTo(SalesReturnItem::class, 'sales_return_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
