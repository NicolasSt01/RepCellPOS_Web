<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Console\Command;

class SyncPlansToStripe extends Command
{
    protected $signature = 'stripe:sync-plans';
    protected $description = 'Crea productos y precios en Stripe para planes que aún no tienen stripe_price_id';

    public function handle(StripeService $stripe): void
    {
        $plans = Plan::whereNull('stripe_price_id')->get();

        if ($plans->isEmpty()) {
            $this->info('Todos los planes ya están sincronizados con Stripe.');
            return;
        }

        foreach ($plans as $plan) {
            $this->line("Creando producto/precio en Stripe para: {$plan->name}...");
            try {
                $priceId = $stripe->getOrCreateStripePrice($plan);
                $this->info("  ✅ {$plan->name} → Price: {$priceId}");
            } catch (\Exception $e) {
                $this->error("  ❌ {$plan->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Sincronización completada.');
    }
}
