<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'tenant_id',
        'event',
        'channel',
        'subject',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function getTemplate(int $tenantId, string $event, string $channel): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('event', $event)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();
    }

    public function replacePlaceholders(WorkOrder $workOrder): string
    {
        $trackingUrl = url("/seguimiento/{$workOrder->tracking_token}");

        return str_replace(
            ['{work_order_number}', '{client_name}', '{tracking_url}'],
            [$workOrder->work_order_number, $workOrder->client?->name ?? '', $trackingUrl],
            $this->body
        );
    }
}
