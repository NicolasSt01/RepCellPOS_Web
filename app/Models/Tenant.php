<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

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
        'configuracion',
        'plan_id',
        'trial_ends_at',
        'subscription_status',
    ];

    protected function casts(): array
    {
        return [
            'social_media' => 'array',
            'configuracion' => 'array',
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

    public function hasFeature(string $feature): bool
    {
        if ($this->subscription_status === 'trial') {
            return true;
        }
        return $this->plan?->features[$feature] ?? false;
    }

    public function hasLimit(string $limit): array
    {
        $limitValue = $this->plan?->limits[$limit] ?? 0;
        return [
            'unlimited' => $limitValue === -1,
            'value' => $limitValue,
        ];
    }

    public function canCreateUser(): bool
    {
        $limit = $this->hasLimit('max_users');
        if ($limit['unlimited']) {
            return true;
        }
        return $this->users()->count() < $limit['value'];
    }

    public function canCreateClient(): bool
    {
        $limit = $this->hasLimit('max_clients');
        if ($limit['unlimited']) {
            return true;
        }
        return $this->clients()->count() < $limit['value'];
    }

    public function canCreateWorkOrder(): bool
    {
        $limit = $this->hasLimit('max_monthly_work_orders');
        if ($limit['unlimited']) {
            return true;
        }
        $monthlyCount = $this->workOrders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        return $monthlyCount < $limit['value'];
    }

    // ========================
    // WHATSAPP METHODS
    // ========================

    public function getEvolutionConfig(): array
    {
        return $this->configuracion['evolution_api'] ?? [];
    }

    public function getWhatsappConfig(): array
    {
        $config = $this->getEvolutionConfig();
        return [
            'instance' => $config['instance'] ?? null,
            'connected' => $config['connected'] ?? false,
            'connected_at' => $config['connected_at'] ?? null,
            'whatsapp_plantilla' => $config['whatsapp_plantilla'] ?? 'breve',
            'whatsapp_plantilla_custom' => $config['whatsapp_plantilla_custom'] ?? null,
        ];
    }

    public function whatsappHabilitado(): bool
    {
        if (!$this->hasFeature('notifications_whatsapp')) {
            return false;
        }
        $evolutionConfig = $this->getEvolutionConfig();
        return !empty($evolutionConfig['instance'])
            && !empty($evolutionConfig['connected']);
    }

    public function getLimiteFuncionalidad(string $key): ?int
    {
        $limits = $this->configuracion['limites']['funcionalidades'] ?? [];
        return $limits[$key] ?? null;
    }

    public function getWhatsappUsadosMes(): int
    {
        $config = $this->configuracion ?? [];
        $func = $config['limites']['funcionalidades'] ?? [];
        $periodo = now()->format('Y-m');
        $periodoAlmacenado = $func['whatsapp_mes_periodo'] ?? null;

        if ($periodoAlmacenado !== $periodo) {
            return 0;
        }
        return $func['whatsapp_mes_count'] ?? 0;
    }

    public function canSendWhatsapp(): bool
    {
        $limite = $this->getLimiteFuncionalidad('whatsapp_mes');
        if ($limite === null || $limite === -1) {
            return true;
        }
        return $this->getWhatsappUsadosMes() < $limite;
    }

    public function incrementarConsumoWhatsapp(int $count = 1): void
    {
        DB::table('tenants')->where('id', $this->id)->update([
            'configuracion' => DB::raw("JSON_SET(
                COALESCE(configuracion, '{}'),
                '$.limites.funcionalidades.whatsapp_mes_count',
                COALESCE(
                    JSON_EXTRACT(configuracion, '$.limites.funcionalidades.whatsapp_mes_count'),
                    0
                ) + {$count},
                '$.limites.funcionalidades.whatsapp_mes_periodo',
                '\"{$this->freshTimestamp()->format('Y-m')}\"'
            )"),
        ]);
    }

    public function getPendingNotifications(?string $type = null): array
    {
        $pendientes = $this->configuracion['pending_notifications'] ?? [];
        if ($type) {
            return array_values(array_filter($pendientes, fn($p) => ($p['type'] ?? null) === $type));
        }
        return $pendientes;
    }

    public function addPendingNotification(string $type, array $data): void
    {
        $config = $this->configuracion ?? [];
        $pendientes = $config['pending_notifications'] ?? [];
        $pendientes[] = [
            'id' => 'pend_' . uniqid(),
            'type' => $type,
            'data' => $data,
            'created_at' => now()->toDateTimeString(),
        ];
        $config['pending_notifications'] = $pendientes;
        $this->update(['configuracion' => $config]);
    }

    public function removePendingNotification(string $id): void
    {
        $config = $this->configuracion ?? [];
        $pendientes = $config['pending_notifications'] ?? [];
        $config['pending_notifications'] = array_values(array_filter(
            $pendientes,
            fn($p) => ($p['id'] ?? null) !== $id
        ));
        $this->update(['configuracion' => $config]);
    }

    public function clearPendingNotifications(string $type): void
    {
        $config = $this->configuracion ?? [];
        $pendientes = $config['pending_notifications'] ?? [];
        $config['pending_notifications'] = array_values(array_filter(
            $pendientes,
            fn($p) => ($p['type'] ?? null) !== $type
        ));
        $this->update(['configuracion' => $config]);
    }
}
