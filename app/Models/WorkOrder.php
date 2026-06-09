<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'user_id',
        'assigned_to',
        'device_brand',
        'device_model',
        'device_serial',
        'device_imei',
        'unlock_pattern',
        'unlock_pin',
        'problem_description',
        'status',
        'priority',
        'timeline',
        'work_order_number',
    ];

    protected function casts(): array
    {
        return [
            'timeline' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public static function generateWorkOrderNumber(Tenant $tenant): string
    {
        $sequence = $tenant->incrementWorkOrderSequence();
        return $tenant->work_order_prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function addTimelineEvent(string $status, string $user, ?string $comment = null): void
    {
        $timeline = $this->timeline ?? [];
        
        $timeline[] = [
            'fecha' => now()->format('Y-m-d H:i:s'),
            'estado' => $status,
            'usuario' => $user,
            'comentario' => $comment,
        ];

        $this->update(['timeline' => $timeline]);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'recibida' => ['en_espera', 'cancelada'],
            'en_espera' => ['en_revision', 'cancelada'],
            'en_revision' => ['diagnosticada', 'cancelada'],
            'diagnosticada' => ['cotizacion_enviada', 'cancelada'],
            'cotizacion_enviada' => ['cotizacion_aprobada', 'cancelada'],
            'cotizacion_aprobada' => ['en_reparacion', 'cancelada'],
            'en_reparacion' => ['reparada', 'cancelada'],
            'reparada' => ['terminada'],
            'terminada' => [],
            'cancelada' => [],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}
