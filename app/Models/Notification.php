<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'client_id',
        'channel',
        'event',
        'status',
        'message',
        'response',
        'tracking_token',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function markAsSent(?string $response = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'response' => $response,
        ]);
    }

    public function markAsFailed(?string $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);
    }

    public function markAsLogged(): void
    {
        $this->update([
            'status' => 'logged',
            'sent_at' => now(),
        ]);
    }

    public static function generateTrackingToken(): string
    {
        return Str::random(32);
    }
}
