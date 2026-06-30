<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'features',
        'limits',
        'sort_order',
        'is_active',
        'is_highlight',
        'stripe_product_id',
        'stripe_price_id',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
