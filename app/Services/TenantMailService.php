<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class TenantMailService
{
    public function configureForTenant(Tenant $tenant): void
    {
        if (!$tenant->mail_host || !$tenant->mail_username || !$tenant->mail_password) {
            return;
        }

        Config::set('mail.mailers.smtp.host', $tenant->mail_host);
        Config::set('mail.mailers.smtp.port', $tenant->mail_port ?? '587');
        Config::set('mail.mailers.smtp.username', $tenant->mail_username);
        Config::set('mail.mailers.smtp.password', $tenant->mail_password);
        Config::set('mail.mailers.smtp.encryption', $tenant->mail_encryption ?? 'tls');
        Config::set('mail.from.address', $tenant->mail_from_address);
        Config::set('mail.from.name', $tenant->mail_from_name ?? $tenant->name);
    }
}
