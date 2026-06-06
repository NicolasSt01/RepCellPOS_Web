<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'code',
        'part_number',
        'name',
        'description',
        'type',
        'stock',
        'min_stock',
        'purchase_price',
        'sale_price',
        'has_tax',
        'tax_percentage',
        'barcode',
        'compatible_brand',
        'compatible_model',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'min_stock' => 'integer',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'has_tax' => 'boolean',
            'tax_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function kardexMovements(): HasMany
    {
        return $this->hasMany(KardexMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->type === 'producto' && $this->stock <= $this->min_stock;
    }

    public function adjustStock(int $quantity, string $type, ?string $notes = null, $reference = null): KardexMovement
    {
        $previousStock = $this->stock;

        if ($type === 'entrada') {
            $this->stock += $quantity;
        } elseif ($type === 'salida') {
            $this->stock -= $quantity;
        } elseif ($type === 'ajuste') {
            $this->stock = $quantity;
        }

        $this->save();

        $movement = KardexMovement::create([
            'tenant_id' => $this->tenant_id,
            'product_id' => $this->id,
            'type' => $type,
            'quantity' => $type === 'ajuste' ? abs($this->stock - $previousStock) : $quantity,
            'previous_stock' => $previousStock,
            'resulting_stock' => $this->stock,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'user_id' => auth()->id(),
            'notes' => $notes,
        ]);

        return $movement;
    }
}
