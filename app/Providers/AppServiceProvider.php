<?php

namespace App\Providers;

use App\Models\WorkOrder;
use App\Observers\WorkOrderObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // Forzar HTTPS en URLs generadas cuando la app corre detrás de un
        // reverse proxy que termina TLS (Traefik/Dokploy). Traefik reenvía
        // la petición al contenedor por HTTP, así que Laravel no detecta el
        // esquema original del cliente y genera assets con http://, lo que
        // provoca errores de Mixed Content en el navegador.
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        WorkOrder::observe(WorkOrderObserver::class);
    }
}
