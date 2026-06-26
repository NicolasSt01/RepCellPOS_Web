<?php

namespace App\Providers;

use App\Models\WorkOrder;
use App\Observers\WorkOrderObserver;
use Illuminate\Support\Facades\Gate;
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

        WorkOrder::observe(WorkOrderObserver::class);
    }
}
