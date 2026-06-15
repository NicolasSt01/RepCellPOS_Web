<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'address',
        'phone',
        'email',
        'social_media',
        'tax_enabled',
        'tax_percentage',
        'tax_mode',
        'print_format',
        'work_order_prefix',
        'work_order_sequence',
        'is_active',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_encryption',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'whatsapp_webhook_url',
        'plan_id',
        'trial_ends_at',
        'subscription_status',
    ];

    protected function casts(): array
    {
        return [
            'social_media' => 'array',
            'tax_enabled' => 'boolean',
            'tax_percentage' => 'decimal:2',
            'work_order_sequence' => 'integer',
            'is_active' => 'boolean',
            'mail_password' => 'encrypted',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function clauses(): HasMany
    {
        return $this->hasMany(TenantClause::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function incrementWorkOrderSequence(): int
    {
        $this->increment('work_order_sequence');
        return $this->work_order_sequence;
    }
}
